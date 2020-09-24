<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\User;
use App\Research;
use App\Location;
use App\Hive;
use App\Inspection;
use App\Device;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use Str;
use Storage;
use InfluxDB;
use Moment\Moment;

class ResearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $research = Research::where('description', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('url', 'LIKE', "%$keyword%")
                ->orWhere('type', 'LIKE', "%$keyword%")
                ->orWhere('institution', 'LIKE', "%$keyword%")
                ->orWhere('type_of_data_used', 'LIKE', "%$keyword%")
                ->orWhere('start_date', 'LIKE', "%$keyword%")
                ->orWhere('end_date', 'LIKE', "%$keyword%")
                ->orWhere('checklist_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $research = Research::paginate($perPage);
        }

        return view('research.index', compact('research'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $research = new Research();
        return view('research.create', compact('research'));
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
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'checklist_ids' => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();

        if (isset($requestData['image']))
        {
            $image = Research::storeImage($requestData);
            if ($image)
            {
                $requestData['image_id'] = $image->id;
                unset($requestData['image']);
            }
        }

        $research = Research::create($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);

        return redirect('research')->with('flash_message', 'Research added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id, Request $request)
    {
        $research     = Research::findOrFail($id);
        $influx       = new \Influx;
        $download_url = null;

        // Make dates table
        $dates = [];

        $moment_start = new Moment($research->start_date);
        $moment_end   = new Moment($research->end_date);
        $moment_now   = new Moment();

        if ($moment_now < $moment_end)
            $moment_end = $moment_now;
            
        $moment_start = $moment_start->startof('day');
        $moment_end   = $moment_end->endof('day');

        // count user consents within dates
        $consent_users_select = DB::table('research_user')
                                    ->join('users', 'users.id', '=', 'research_user.user_id')
                                    ->select('users.name','users.id')
                                    ->selectRaw('sum(research_user.consent) as consents')
                                    ->where('research_user.research_id', $id)
                                    ->whereDate('research_user.updated_at', '<', $research->end_date)
                                    ->groupBy('research_user.user_id')
                                    ->having('consents', '>', 0)
                                    ->pluck('name','id')
                                    ->toArray();

        asort($consent_users_select, SORT_NATURAL);

        $consent_users_selected = null;

        // select users
        if ($request->has('user_ids'))
            $consent_users_selected = explode(',',$request->input('user_ids')[0]);
        else
            $consent_users_selected = [array_keys($consent_users_select)[0]];

        $consents = DB::table('research_user')
                            ->where('research_id', $id)
                            ->whereIn('user_id', $consent_users_selected)
                            ->whereDate('updated_at', '<', $research->end_date)
                            ->groupBy('user_id')
                            ->get();
        
        // Fill dates array
        $assets = ["users"=>0, "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "measurements"=>0];
        $moment = $moment_start;
        while($moment < $moment_end)
        {
            // make date
            $dates[$moment->format('Y-m-d')] = $assets;
            // next
            $moment = $moment->addDays(1);
        }

        // Fill dates array with counts of data
        foreach ($consents as $cu) 
        {
            $user_consents = DB::table('research_user')->where('research_id', $id)->where('user_id', $cu->user_id)->whereDate('updated_at', '<', $research->end_date)->orderBy('updated_at','asc')->get()->toArray();
            
            //die(print_r($consents));
            $user_consent      = $user_consents[0]->consent;
            $date_curr_consent = substr($user_consents[0]->updated_at, 0, 10);
            $date_next_consent = $moment_end->format('Y-m-d');
            $index             = 0;

            if (count($user_consents) > 1)
            {
                $date_next_consent = substr($user_consents[1]->updated_at, 0, 10);
                $index             = 1;
            }
            elseif ($user_consent === 0) // if only 1 and consent is false, continue to next user
            {
                continue;
            }

            // add user data
            $user_apiaries     = Location::where('user_id', $cu->user_id)->orderBy('created_at')->get();
            $user_hives        = Hive::where('user_id', $cu->user_id)->orderBy('created_at')->get();
            $user_inspections  = User::find($cu->user_id)->inspections()->orderBy('created_at')->get();
            $user_devices      = Device::where('user_id', $cu->user_id)->orderBy('created_at')->get();
            $user_measurements = [];

            if ($user_devices->count() > 0)
            {
                // get daily counts of sensor measurements
                $points           = [];
                $user_device_keys = [];
                foreach ($user_devices as $device) 
                    $user_device_keys[]= '"key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\'';
                
                $user_device_keys = '('.implode(' OR ', $user_device_keys).')';

                try{
                    $points = $influx::query('SELECT COUNT("bv") as "count" FROM "sensors" WHERE '.$user_device_keys.' AND time >= \''.$user_consents[0]->updated_at.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d) fill(null)')->getPoints();
                } catch (InfluxDB\Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                } catch (Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                }
                if (count($points) > 0)
                {
                    foreach ($points as $point) 
                        $user_measurements[substr($point['time'],0,10)] = $point['count'];
                }
            }

            // go over dates, compare consent dates
            foreach ($dates as $d => $v) 
            {
                if ($d >= $date_next_consent && $index > 0 && $index < count($user_consents)-1) // change user_consent if multiple user_consents exist and check date is past the active consent date 
                {
                    // take current user_consent
                    $user_consent       = $user_consents[$index]->consent;
                    $date_curr_consent  = substr($user_consents[$index]->updated_at, 0, 10);
                    //fill up to next consent date
                    $date_next_consent  = substr($user_consents[$index+1]->updated_at, 0, 10);
                    $index++;
                }

                if ($user_consent)
                {
                    $dates[$d]['users']       = $v['users'] + $user_consent;
                    $dates[$d]['apiaries']    = $v['apiaries'] + $user_apiaries->where('created_at', '<=', $d)->count();
                    $dates[$d]['hives']       = $v['hives'] + $user_hives->where('created_at', '<=', $d)->count();
                    $dates[$d]['inspections'] = $v['inspections'] + $user_inspections->where('created_at', '>=', $d.' 00:00:00')->where('created_at', '<=', $d.' 23:59:59')->count();
                    $dates[$d]['devices']     = $v['devices'] + $user_devices->where('created_at', '<=', $d)->count();
                    if (in_array($d, array_keys($user_measurements)))
                        $dates[$d]['measurements']= $v['measurements'] + $user_measurements[$d];
                }
            }
            //die(print_r([$user_consent, $date_next_consent, $user_consents, $dates]));
        }

        // reverse array for display
        krsort($dates);

        // Export data, show download link
        if ($request->has('download'))
        {
            $download_url = $this->export($research, $consent_users_selected);
        }

        return view('research.show', compact('research', 'dates', 'consent_users_select', 'consent_users_selected', 'download_url'));
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
        $research = Research::findOrFail($id);

        return view('research.edit', compact('research'));
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
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'checklist_ids' => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();
        
        if (isset($requestData['image']))
        {
            $image = Research::storeImage($requestData);
            if ($image)
            {
                $requestData['image_id'] = $image->id;
                unset($requestData['image']);
            }
        }

        $research = Research::findOrFail($id);
        $research->update($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);

        return redirect('research')->with('flash_message', 'Research updated!');
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
        Research::destroy($id);

        return redirect('research')->with('flash_message', 'Research deleted!');
    }


    /* Data export functions */

    private function export(Research $research, $user_ids=null, $start_date=null, $end_date=null, $fileType='xlsx')
    {
        $users = User::whereIn('id', $user_ids)->get();

        // first combine all user's itemnames
        $item_ancs  = [];
        $item_names = [];
        foreach ($users as $user) 
        {
            $ins = Inspection::item_names($user->inspections()->get());
            foreach ($ins as $in) 
            {
                $name = $in['anc'].$in['name'];
                if (!in_array($name, $item_ancs))
                {
                    $item_ancs[]  = $name;
                    $item_names[] = $in; 
                }
            }
        }

        // set start and end date of data
        if ($start_date == null || $start_date < $research->start_date)
            $start_date = $research->start_date;

        if ($end_date == null || $end_date > $research->end_date)
            $end_date = $research->end_date;

        // Define header rows of tabs
        $userExport = [
                       ['User_id',
                        __('export.name'),
                        __('export.email'),
                        __('export.avatar'),
                        __('export.created_at'),
                        __('export.updated_at'),
                        __('export.last_login')]
                    ];

        $locaExport = [
                       ['User_id',
                        'Location_id',
                        __('export.name'),
                        __('export.type'),
                        __('export.hives'),
                        __('export.coordinate_lat'),
                        __('export.coordinate_lon'),
                        __('export.address'),
                        __('export.postal_code'),
                        __('export.city'),
                        __('export.country_code'),
                        __('export.continent'),
                        __('export.created_at'),
                        __('export.deleted_at')]
                    ];

        $hiveExport = [
                       ['User_id',
                        'Hive_id',
                        __('export.name'),
                        __('export.type'),
                        'Location_id',
                        __('export.color'),
                        __('export.queen'),
                        __('export.queen_color'),
                        __('export.queen_born'),
                        __('export.queen_fertilized'),
                        __('export.queen_clipped'),
                        __('export.brood_layers'),
                        __('export.honey_layers'),
                        __('export.frames'),
                        __('export.created_at'),
                        __('export.deleted_at')]
                    ];

        $inspExport = [
                       ['User_id',
                        'Inspection_id',
                        __('export.created_at'),
                        'Hive_id',
                        'Location_id',
                        __('export.impression'),
                        __('export.attention'),
                        __('export.reminder'),
                        __('export.reminder_date'),
                        __('export.notes')]
                    ];

        // Add item names to header row of inspections
        foreach ($item_ancs as $name) 
            $inspExport[0][] = $name;

        $inspExport[0][] = __('export.deleted_at');

        // add user data to sheet data arrays
        foreach ($users as $user) 
        {
            $userExport[] = $this->getUser($user);
            
            $locas = $this->getLocations($user, $start_date, $end_date);
            foreach ($locas as $loca)
                $locaExport[] = $loca;

            $hives = $this->getHives($user, $start_date, $end_date);
            foreach ($hives as $hive)
                $hiveExport[] = $hive;

            $insps = $this->getInspections($user, $item_ancs, $start_date, $end_date);
            foreach ($insps as $insp)
                $inspExport[] = $insp;
        }

        //die(print_r($locaExport));
        $spreadsheet = new Spreadsheet();
        
        // fill sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('export.users'));
        $sheet->fromArray($userExport);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(__('export.locations'));
        $sheet->fromArray($locaExport);

        // TODO: Fill headers and data below
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(__('export.hives'));
        $sheet->fromArray($hiveExport);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle(__('export.inspections'));
        $sheet->fromArray($inspExport);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Data');

        // save sheet
        $fileName = strtolower(env('APP_NAME')).'-export-'.$research->name.'-'.Str::random(40);
        $filePath = 'exports/'.$fileName.'.'.$fileType;
        $writer = new Xlsx($spreadsheet);
        //$writer->setOffice2003Compatibility(true);

        ob_start();
        $writer->save('php://output');
        $file_content = ob_get_contents();
        ob_end_clean();

        $disk = env('EXPORT_STORAGE', 'public');
        if (Storage::disk($disk)->put($filePath, $file_content))
            return Storage::disk($disk)->url($filePath);

        return null;
    }
    
    private function getUser(User $user)
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->avatar,
            $user->created_at,
            $user->updated_at,
            $user->last_login
        ];
    }

    private function getLocations(User $user, $start_date=null, $end_date=null)
    {
        return $user->locations()->withTrashed()->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('deleted_at')->orderBy('name')->get()->map(function($item) use ($user)
        {
            return [
                $user->id,
                $item->id,
                $item->name,
                $item->type,
                $item->hives()->count(),
                $item->coordinate_lat,
                $item->coordinate_lon,
                $item->street.' '.$item->street_no,
                $item->postal_code,
                $item->city,
                strtoupper($item->country_code),
                $item->continent,
                $item->created_at,
                $item->deleted_at,
            ];
        });
    }
    
    private function getHives(User $user, $start_date=null, $end_date=null)
    {
        return $user->hives()->withTrashed()->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->orderBy('deleted_at')->orderBy('location_id')->orderBy('name')->get()->map(function($item) use ($user)
        {
            $queen = $item->queen;

            return [
                $user->id,
                $item->id, 
                $item->name,
                $item->type,
                $item->location_id,
                $item->color,
                isset($queen) ? $queen->name : '',
                isset($queen) ? $queen->color : '',
                isset($queen) ? $queen->created_at : '',
                isset($queen) ? $queen->fertilized : '',
                isset($queen) ? $queen->clipped : '',
                $item->getBroodlayersAttribute(),
                $item->getHoneylayersAttribute(),
                $item->frames()->count(),
                $item->created_at,
                $item->deleted_at,
            ];
        });
    }

    private function getInspections(User $user, $item_names, $start_date=null, $end_date=null)
    {
        // array of inspection items and data
        $inspection_data = array_fill_keys($item_names, '');

        $inspections = $user->inspections()->withTrashed()->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->with('items')->orderBy('deleted_at')->orderByDesc('created_at')->get();


        $table = $inspections->map(function($inspection) use ($inspection_data, $user)
        {
            if (isset($inspection->items))
            {
                foreach ($inspection->items as $inspectionItem)
                {
                    $array_key                   = $inspectionItem->anc.$inspectionItem->name;
                    $inspection_data[$array_key] = $inspectionItem->humanReadableValue();
                }
            }
            $locationId = ($inspection->locations()->count() > 0 ? $inspection->locations()->first()->id : ($inspection->hives()->count() > 0 ? $inspection->hives()->first()->location()->first()->id : ''));
            
            $reminder_date= '';
            if (isset($inspection->reminder_date) && $inspection->reminder_date != null)
            {
                $reminder_mom  = new Moment($inspection->reminder_date);
                $reminder_date = $reminder_mom->format('Y-m-d H:i:s');
            }

            $smileys  = __('taxonomy.smileys');
            $boolean  = __('taxonomy.boolean');
            
            // add general inspection data columns
            $pre = [
                'user_id' => $user->id,
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

    public function generate_csv(Request $request)
    {
        $device_id    = $request->input('device_id');
        $start        = $request->input('start');
        $end          = $request->input('end');
        $separator    = $request->input('separator', ';');
        $measurements = $request->input('measurements', '*');
        $device       = $request->user()->allDevices()->find($device_id);


        if ($device == null)
            return Response::json('invalid-user-device', 500);

        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        
        if ($measurements == null || $measurements == '' || $measurements === '*')
            $sensor_measurements = '*';
        else
            $sensor_measurements = '"'.implode('","',$measurements).'"';

        $query = 'SELECT '.$sensor_measurements.' FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$start.'\' AND time < \''.$end.'\'';
        
        try{
            $client = new \Influx; 
            $data   = $client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return Response::json('influx-query-error: '.$query, 500);
        }

        if (count($data) == 0)
            return Response::json('influx-query-empty', 500);

        // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";

        $csv_sens = array_diff(array_keys($data[0]),["sensor","key"]);
        $csv_head = [];
        foreach ($csv_sens as $sensor_name) 
        {
            $meas       = Measurement::where('abbreviation', $sensor_name)->first();
            $csv_head[] = $meas ? $meas->pq_name_unit() : $sensor_name;
        }
        $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

        // format CSV file body
        $csv_body = [];
        foreach ($data as $sensor_values) 
        {
            $csv_body[] = implode($separator, array_diff_key($sensor_values,["sensor"=>0,"key"=>0]));
        }
        $csv_file = $csv_head.implode("\r\n", $csv_body);

        // return the CSV file content in a file on disk
        // $fileName = $device->name.'_'.$start.'_'.$end.'.csv';
        // Storage::disk('public')->put('/exports/'.$fileName, $csv_file);

        return response($csv_file)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
