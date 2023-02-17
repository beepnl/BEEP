<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Mail;
use Auth;
use Storage;
use Session;
use App\SampleCode;
use App\Mail\SampleCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use LaravelLocalization;
use Moment\Moment;
use App\Category;
use App\Inspection;
use App\InspectionItem;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SampleCodeController extends Controller
{
    // Open routes
    public function code()
    {
        return view('sample-code.code');
    }

    // Get excel template to fill out
    public function upload()
    {
        $template_url = $this->createExcelTemplate();
        $data         = Session::get('data');
        $col_names    = Session::get('col_names');

        return view('sample-code.upload', compact('template_url', 'data', 'col_names'));
    }

    // Upload filled excel template to input
    public function upload_store(Request $request)
    {
        $msg            = 'No file found';
        $res            = 'error';
        $items_replaced = 0; 
        $items_added    = 0; 
        $items_removed  = 0; 
        $inspection_cnt = 0; 
        $data           = [];
        $col_names      = [];
        $col_input_types= [];
        $sample_code_id = Category::findCategoryIdByParentAndName('laboratory_test', 'sample_code');

        if ($request->has('checked') && $request->has('data')) // Persist checked data
        {
            $data = json_decode($request->input('data'), true);
            $checked_ids = $request->input('checked');
            $emails_sent = 0; 

            // Add this data as inspection items to the inspection where the corresponding sample code has been generated
            foreach ($data as $checked_id => $inspection_items) // $inspection_items is array of ['cat_id'=>value]
            {

                if (in_array($checked_id, $checked_ids) && isset($inspection_items[$sample_code_id])) // only store inspections of checked entries
                {
                    $sample_code = $inspection_items[$sample_code_id];
                    unset($inspection_items[0]); // remove initial checked item
                    
                    // look up inspection
                    $inspection_id = InspectionItem::where('category_id', $sample_code_id)->where('value', $sample_code)->value('inspection_id');
                    $inspection    = Inspection::find($inspection_id);
                    
                    if ($inspection)
                    {
                        $inspection_cnt++;
                        $items_changed = 0;

                        foreach ($inspection_items as $category_id => $value)
                        {
                            $inspection_item_exists = $inspection->items()->where('category_id', $category_id);
                            $value_filled = filled($value);

                            if ($inspection_item_exists->count() > 0)
                            {
                                $inspection_item_exists->delete();
                                
                                if ($value_filled)
                                    $items_replaced++;
                                else
                                    $items_removed++;
                            }
                            else
                            {
                                if ($value_filled)
                                    $items_added++;
                            }

                            if ($value_filled)
                            {
                                $itemData = 
                                [
                                    'category_id'   => $category_id,
                                    'inspection_id' => $inspection_id,
                                    'value'         => $value,
                                ];
                                InspectionItem::create($itemData);
                            }
                            
                            $items_changed++;
                        }

                        // send e-mail to user
                        if ($items_changed > 0 && $inspection->users()->count() > 0)
                        {
                            $hive_id   = null;
                            $hive_name = null;

                            if (isset($inspection->hives) && $inspection->hives->count() > 0)
                            {
                                $hive      = $inspection->hives->first();
                                $hive_id   = $hive->id;
                                $hive_name = $hive->name;
                            }

                            $link = null;
                            if (isset($hive_id))
                                $link = "hives/$hive_id/inspections?search=id%3D$inspection_id";

                            foreach ($inspection->users as $u) 
                            {
                                Log::debug("Sample result upload for code $sample_code sending email to user $u->email");
                                Mail::to($u->email)->send(new SampleCodeMail($u->name, $sample_code, $hive_name, $link));
                                $emails_sent++;
                            }
                        }

                    }
                }
            }
            // show result
            $msg = "Added data for $inspection_cnt inspections. Added $items_added, removed $items_removed, replaced $items_replaced inspection items in total. Sent $emails_sent data upload notification emails to the creators of the sample codes.";
            if ($inspection_cnt > 0 && $items_added + $items_replaced > 0)
            {
                $res  = 'success';
                $data = [];
            }

        }
        else if ($request->has('sample-code-excel') && $request->hasFile('sample-code-excel')) // Check and visualize Excel input  
        {
            $file = $request->file('sample-code-excel');
            if ($file->isValid())
            {
                $msg  = 'File uploaded';
                $res  = 'success';
                $path = $request->file('sample-code-excel')->getRealPath();
                
                $reader = IOFactory::createReader('Xlsx');
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
                $sheet  = $reader->load($path);
                //dd($sheet);

                $sheets = $sheet->getSheetCount();
                $wsheet = $sheet->getSheet(0); // get first sheet

                $template = $this->createExcelTemplate(true); // array[row[0 -> 6]=>[col_0, ..., col_n]] (col0 being the explanation column)
                $t_sheet  = reset($template); // [0=>CATEGORY ID, 1=>HIERACHY, 2=>NAME, 3=>PHYSICAL QUANTITY, 4=>UNIT, 5=>INPUT TYPE, 6=>INPUT RANGE, 7=>First empty row for entry]
                //dd($t_sheet);
                $t_headers= count($t_sheet)-1; // one blank row is the first entry row
                $t_types  = $t_sheet[5]; 
                $cat_ids  = $t_sheet[0];

                if ($cat_ids[0] == 'CATEGORY ID')
                    unset($cat_ids[0]);

                // Create data array to show and persist
                $cat_ids_valid     = [];
                $col_names_valid   = [];
                $input_types_valid = [];
                $col_names[0]      = 'Ok?';

                foreach ($wsheet->getRowIterator() as $row_num => $row) 
                {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    // map header rows to (possibly different) template category_id column order in Excel
                    if ($row_num == 1) // map category_id header row in Excel
                    {
                        $col_index = 0;
                        foreach ($cellIterator as $col_name => $cell)
                        {
                            $cat_id = $cell->getValue();
                            if (isset($cat_id) && in_array($cat_id, $cat_ids))
                            {
                                $cat_ids_valid[$col_name] = $cat_id; // Cat id per col name: ["B"=>1371, "C"=>1425, "Col_name"=>cat_id, etc]
                                $col_names[$cat_id]       = $t_sheet[1][$col_index].$t_sheet[2][$col_index]; // [cat_id => "Hierachy + Name"]
                                $col_input_types[$cat_id] = $t_sheet[5][$col_index]; // [cat_id => "Input type"]
                            }
                            $col_index++;
                        }
                        $col_names_valid = array_keys($cat_ids_valid);  // [0=>"B", 1=>"C", etc]
                        //dd($col_names_valid, $cat_ids_valid, $col_names, $col_input_types);
                    }
                    else if ($row_num > $t_headers) // entered values
                    {
                        foreach ($cellIterator as $col_name => $cell)
                        {
                            if (in_array($col_name, $col_names_valid)) // valid cat_id col:  $col_names_valid = [0=>"B", 1=>"C", etc]
                            {
                                $value = trim($cell->getValue()); // remove whitespace from beginning and end of the string
                                $cat_id= $cat_ids_valid[$col_name];

                                if (isset($value))
                                {
                                    if ($value === '')
                                    {
                                        $data[$row_num][$cat_id] = ''; // do not store, but remove this value at persist stage
                                    }
                                    else
                                    {

                                        // Convert known fields to the type of data
                                        $corrected_value = $value;

                                        // Check if there is a formula, if so, try to parse it
                                        if (substr($value,0,1) == '=') 
                                        {
                                            try {
                                                $corrected_value = $cell->getCalculatedValue();
                                            } catch (Exception $e) {
                                                Log::error("SampleCodeController.upload_store formula ($value) parse error: ".$e->getMessage());
                                                $corrected_value = $value;
                                            }
                                        }

                                        // Try to force values in the requested format
                                        $input_type = $col_input_types[$cat_id];
                                        switch($input_type)
                                        {
                                            case 'sample_code':
                                                $corrected_value = strtoupper(substr(str_replace(' ', '', $value), 0, 8));
                                                break;
                                            case 'date':
                                                if (is_numeric($value))
                                                {
                                                    $unix_timestamp = ($value - 25569) * 86400;
                                                    $corrected_value= date("Y-m-d H:i:s", $unix_timestamp);
                                                }
                                                else
                                                {
                                                    $corrected_value= date("Y-m-d H:i:s", strtotime($value));
                                                }
                                                break;
                                            case 'text':
                                                $corrected_value = (string)$value;
                                                break;
                                            case 'boolean':
                                            case 'boolean_yes_red':
                                                $corrected_value = intval(boolval($value));
                                                break;
                                            case 'score_amount':
                                                $intval = intval($value);
                                                $corrected_value = $intval > 4 || $intval < 0 ? 0 : $intval;
                                                break;
                                            case 'number':
                                            case 'number_0_decimals':
                                            case 'number_1_decimals':
                                            case 'number_2_decimals':
                                            case 'number_3_decimals':
                                            case 'number_positive':
                                            case 'number_negative':
                                            case 'number_percentage':
                                                if (strpos($value, ',') !== false) // replace , with .
                                                    $value = str_replace(',', '.', $value);

                                                $corrected_value = (float)$value;

                                                if ($input_type == 'number_0_decimals')
                                                    $corrected_value = round($corrected_value, 0);
                                                else if ($input_type == 'number_1_decimals')
                                                    $corrected_value = round($corrected_value, 1);
                                                else if ($input_type == 'number_2_decimals')
                                                    $corrected_value = round($corrected_value, 2);
                                                else if ($input_type == 'number_3_decimals')
                                                    $corrected_value = round($corrected_value, 3);
                                                else if ($input_type == 'number_positive')
                                                    $corrected_value = abs($corrected_value);
                                                else if ($input_type == 'number_negative')
                                                    $corrected_value = -1 * abs($corrected_value);
                                                else if ($input_type == 'number_percentage')
                                                    $corrected_value = min(100, max(0, $corrected_value));
                                                
                                                break;
                                        }

                                        if (!isset($data[$row_num]))
                                            $data[$row_num] = [];

                                        if ($cat_id == $sample_code_id) // check if sample code exists in DB
                                            $data[$row_num][0] = SampleCode::where('sample_code',$corrected_value)->count();

                                        $data[$row_num][$cat_id] = $corrected_value;
                                    }
                                }
                            }
                        }
                    }
                }
                $cols   = $wsheet->getHighestColumn();
                $rows   = $wsheet->getHighestRow();
                $entries= count($data);
                $editor = $sheet->getProperties()->getLastModifiedBy();
                $lastmo = date('Y-m-d H:i:s', $sheet->getProperties()->getModified());
                $msg   .= ". $sheets Tabs, First tab: $entries entries ($rows rows up to col $cols), Last modified by: $editor @ $lastmo";
            }
            else
            {
                $msg  = 'File uploaded, but invalid';
            }
        }

        //dd($col_names, $data);

        return redirect('code-upload')->with(["$res"=>$msg, 'data'=>$data, 'col_names'=>$col_names, 'col_input_types'=>$col_input_types]);
    }

    
    private function createExcelTemplate($array_only = false)
    {
        $locale            = LaravelLocalization::getCurrentLocale();
        $spreadsheet_array = [];
        $today             = date('Y-m-d');
        $sheet_title       = 'Sample code template '.$today;

        // Add item names to header row of inspections
        // first combine all user's itemnames
        $lab_cats = Category::descendentsByRootParentAndName('disorder', 'disorder', 'laboratory_test');
        $rows     = [
            ['CATEGORY ID'],
            ['HIERACHY'],
            ['NAME'],
            ['PHYSICAL QUANTITY'],
            ['UNIT'],
            ['INPUT TYPE'],
            ['INPUT RANGE']
        ]; // 7 rows

        $rows[] = []; // 2nd column is for sample code

        foreach ($lab_cats as $c) 
        {
            $hidden = ['image','label','sample_code'];

            if ($c->input == 'sample_code')
            {
                $rows[0][1] = $c->id;
                $rows[1][1] = $c->ancName($locale);
                $rows[2][1] = $c->transName($locale);
                $rows[3][1] = $c->getPhysicalQuantityNameAttribute();
                $rows[4][1] = $c->getUnitAttribute('unit');
                $rows[5][1] = $c->input;
                $rows[6][1] = 'Enter all sample codes in this column';
            }

            if (in_array($c->input, $hidden))
                continue;

            $rows[0][] = $c->id;
            $rows[1][] = $c->ancName($locale);
            $rows[2][] = $c->transName($locale);
            $rows[3][] = $c->getPhysicalQuantityNameAttribute();
            $rows[4][] = $c->getUnitAttribute('unit');
            $rows[5][] = $c->input;
            $rows[6][] = $c->inputRange();
        }

        $spreadsheet_array[$sheet_title] = $rows;

        if ($array_only)
            return $spreadsheet_array;

        // $spreadsheet_array[$sheet_title][0][] = __('export.deleted_at');

        // Fill with insp[ection data
        // $lab_catsps = $this->getInspections($user_id, $hive_inspections, $item_ancs, $date_user_created, $date_until_today);
        // foreach ($lab_catsps as $lab_catsp)
        //     $spreadsheet_array[$sheet_title][] = $lab_catsp;

        //dd($spreadsheet_array);        

        $template_excel = $this->export($spreadsheet_array, $sheet_title);

        return isset($template_excel) ? $template_excel['url'] : null;

    }

    private function num2alpha($n)
    {
        for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n%26 + 0x41) . $r;
        return $r;
    }

    private function export($spreadsheetArray, $fileName='filename')
    {
        //dd($spreadsheetArray);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Fill sheet with tabs and data
        $col  = 1;
        $cols = 1;
        $rows = 0;
        $lines= [];
        $head = null;
        foreach ($spreadsheetArray as $title => $sheet_array) 
        {
            $sheet->setTitle($title);
            $sheet->fromArray($sheet_array);
            $rows = count($sheet_array);

            foreach ($sheet_array as $row_index => $col_array) 
            {
                if ($row_index == 1)
                {
                    if ($head == null)
                    {
                        foreach($col_array as $col_index => $header)
                        {
                            if ($header != $head)
                            {
                                if ($head != null)
                                    $lines[] = $col_index;

                                $head = $header;
                            }
                        }
                    }

                }

                $cols = max($cols, count($col_array));
            }
        }
        //dd($lines);

        // style the header
        $header_lst_col = $this->num2alpha($cols);
        $header_cells   = 'A1:'.$this->num2alpha($cols).$rows;
        $styleArrayHeaderBlock = [
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'protection' => [
                'locked' => true,
            ],
        ];

        $sheet->getStyle($header_cells)->applyFromArray($styleArrayHeaderBlock);
        
        // protect sheet
        // $sheet->getProtection()->setSheet(true);
        // $protection = $sheet->getProtection();
        // $protection->setPassword('');
        // $protection->setSheet(true);
        // $protection->setSort(true);
        // $protection->setInsertRows(true);
        // $protection->setFormatCells(true);
        $sheet->freezePane('C'.$rows);

        // bottom border
        $styleArrayBottomBorder = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $bottomHeaderRow = 'A'.($rows-1).':'.$header_lst_col.($rows-1);
        $sheet->getStyle($bottomHeaderRow)->applyFromArray($styleArrayBottomBorder);

        // vertical borders 
        $styleArrayVertLine = [
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        foreach ($lines as $col)
        {
            $colChar  = $this->num2alpha($col);
            $colCells = $colChar.'1:'.$colChar.'500'; 
            $sheet->getStyle($colCells)->applyFromArray($styleArrayVertLine);
        }
        
        // Set cell width and bold first col
        $firstColCells = 'A1:A500'; 
        $secondColCells = 'B1:B500'; 
        $styleArrayFirstCol = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFA0A0A0',
                ],
            ],
        ];
        $styleArraySampleCodeCol = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFEEEEEE',
                ],
                'endColor' => [
                    'argb' => 'FFEEEEEE',
                ],
            ],
        ];
        $sheet->getStyle($firstColCells)->applyFromArray($styleArrayFirstCol);
        $sheet->getStyle($secondColCells)->applyFromArray($styleArraySampleCodeCol);

        for ($c=0; $c <= $cols; $c++) 
        { 
            $colChar = $this->num2alpha($c);
            $sheet->getColumnDimension($colChar)->setWidth(15);
        }
        
        // save sheet
        $filePath = 'exports/'.$fileName.'.xlsx';
        $writer   = new Xlsx($spreadsheet);
        //$writer->setOffice2003Compatibility(true);

        ob_start();
        $writer->save('php://output');
        $file_content = ob_get_contents();
        ob_end_clean();

        $disk = env('EXPORT_STORAGE', 'public');
        if (Storage::disk($disk)->put($filePath, $file_content, ['mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], 'public'))
            return ['url'=>Storage::disk($disk)->url($filePath), 'path'=>$filePath];

        return null;
    }


    private function getInspections($sample_code, $inspections, $item_names, $date_start=null, $date_until=null)
    {
        // array of inspection items and data
        $inspection_data = array_fill_keys($item_names, '');

        $inspections = $inspections->where('created_at', '>=', $date_start)->where('created_at', '<=', $date_until)->sortByDesc('created_at');


        $table = $inspections->map(function($inspection) use ($inspection_data, $user_id)
        {
            if (isset($inspection->items))
            {
                foreach ($inspection->items as $inspectionItem)
                {
                    $array_key                   = $inspectionItem->anc.$inspectionItem->name;
                    $inspection_data[$array_key] = $inspectionItem->humanReadableValue();
                }
            }
            $locationId = ($inspection->locations()->count() > 0 ? $inspection->locations()->first()->id : ($inspection->hives()->count() > 0 ? $inspection->hives()->first()->location_id : ''));
            
            $reminder_date= '';
            if (isset($inspection->reminder_date) && $inspection->reminder_date != null)
            {
                $reminder_mom  = new Moment($inspection->reminder_date);
                $reminder_date = $reminder_mom->format('Y-m-d H:i:s');
            }

            $smileys  = __('taxonomy.smileys_3');
            $boolean  = __('taxonomy.boolean');
            
            // add general inspection data columns
            $pre = [
                'Sample code' => $sample_code,
                'inspection_id' => $inspection->id,
                __('export.created_at') => $inspection->created_at,
                __('export.hive') => $inspection->hives()->count() > 0 ? $inspection->hives()->first()->id : '', 
                __('export.location') => $locationId, 
                __('export.impression') => $inspection->impression > -1 &&  $inspection->impression < count($smileys) ? $smileys[$inspection->impression] : '',
                __('export.attention') => $inspection->attention > -1 &&  $inspection->attention < count($boolean) ? $boolean[$inspection->attention] : '',
                __('export.reminder') => $inspection->reminder,
                __('export.reminder_date') => $reminder_date,
                __('export.notes') => $inspection->notes,
            ];

            $dat = array_merge($pre, $inspection_data, [__('export.deleted_at') => $inspection->deleted_at]);

            return array_values($dat);
        });
        //die(print_r($table));
        return $table;
    }


    public function check(Request $request)
    {
        $samplecode = SampleCode::where('sample_code', $request->input('samplecode'))->first();

        if ($samplecode)
            return view('sample-code.result', compact('samplecode'));

        return redirect('code')->with('error', 'Sample code not found');
    }

    public function resultsave(Request $request)
    {
        $samplecode = SampleCode::where('sample_code', $request->input('samplecode'))->first();

        if ($samplecode)
        {
            if ($request->filled('test_lab_name'))
                $samplecode->test_lab_name = $request->input('test_lab_name');

            if ($request->filled('test_date'))
                $samplecode->test_date = $request->input('test_date');

            if ($request->filled('test'))
                $samplecode->test = $request->input('test');

            if ($request->filled('test_result'))
                $samplecode->test_result = $request->input('test_result');

            $samplecode->save();

            return redirect('code')->with('success', 'Sample code results saved');

        }
        return redirect('code')->with('error', 'Sample code not found');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $samplecode = SampleCode::all();
        
        return view('sample-code.index', compact('samplecode'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $samplecode = new SampleCode();
        $samplecode->sample_code = SampleCode::generate_code();
        $samplecode->user_id = Auth::user()->id;
        $samplecode->hive_id = Auth::user()->hives->first()->id;
        $samplecode->queen_id = Auth::user()->queens->first()->id;
        return view('sample-code.create', compact('samplecode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $this->validate($request, [
			'sample_code' => 'required',
			'hive_id' => 'required'
		]);
        $requestData = $request->all();
        
        SampleCode::create($requestData);

        return redirect('sample-code')->with('flash_message', 'SampleCode added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $samplecode = SampleCode::findOrFail($id);

        return view('sample-code.show', compact('samplecode'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $samplecode = SampleCode::findOrFail($id);

        return view('sample-code.edit', compact('samplecode'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
			'sample_code' => 'required',
			'hive_id' => 'required'
		]);
        $requestData = $request->all();
        
        $samplecode = SampleCode::findOrFail($id);
        $samplecode->update($requestData);

        return redirect('sample-code')->with('flash_message', 'SampleCode updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        SampleCode::destroy($id);

        return redirect('sample-code')->with('flash_message', 'SampleCode deleted!');
    }
}
