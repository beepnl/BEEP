<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Storage;
use App\SampleCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use LaravelLocalization;
use Moment\Moment;
use App\Category;
use App\Inspection;
use App\InspectionItem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SampleCodeController extends Controller
{
    // Open routes
    public function code()
    {
        return view('sample-code.code');
    }

    public function upload()
    {
        $template_url = $this->createExcelTemplate();
        return view('sample-code.upload', compact('template_url'));
    }

    public function upload_store(Request $request)
    {
        
        return view('sample-code.upload');
    }

    
    private function createExcelTemplate()
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
