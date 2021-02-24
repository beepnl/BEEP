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
use App\Measurement;
use App\Models\FlashLog;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;
use Auth;
use Str;
use Storage;
use InfluxDB;
use Moment\Moment;

class ResearchController extends Controller
{
    private function checkAuthorization(Request $request)
    {
        if ($request->user()->researchMenuOption() == false)
            return redirect('dashboard')->with('error', 'Unauthorized');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $this->checkAuthorization($request);

        if ($request->user()->hasRole('superadmin'))
            $research = Research::all();
        else
            $research = $request->user()->allResearches()->get();

        return view('research.index', compact('research'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if(Auth::user()->hasRole('superadmin') == false)
            return redirect('dashboard')->with('error', 'Unauthorized');

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
        $this->checkAuthorization($request);

        $this->validate($request, [
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'checklist_ids' => 'nullable|exists:checklists,id',
            'viewer_ids'    => 'nullable|exists:users,id',
            'user_id'       => 'nullable|exists:users,id',
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

        if (isset($requestData['viewer_ids']))
            $research->viewers()->sync($requestData['viewer_ids']);

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
        $this->checkAuthorization($request);

        if ($request->user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research = $request->user()->allResearches()->find($id);

        $influx       = new \Influx;
        $download_url = null;
        $sensor_urls  = [];
        $download     = $request->has('download');
        $sensordata   = true; //$request->has('sensordata');

        // Make dates table
        $dates  = [];

        $moment_now   = new Moment();
        $moment_2wk   = $moment_now->startof('day')->addDays(-13)->format('Y-m-d');
        $moment_now   = new Moment();
        $moment_ytd   = $moment_now->endof('day')->addDays(0)->format('Y-m-d');

        $date_start   = $request->input('date_start', $moment_2wk);
        $date_until   = $request->input('date_until', $moment_ytd);

        $moment_start = new Moment($date_start);
        $moment_end   = new Moment($date_until);

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
                                    ->whereDate('research_user.updated_at', '<', $date_until)
                                    ->groupBy('research_user.user_id')
                                    ->having('consents', '>', 0)
                                    ->pluck('name','id')
                                    ->toArray();

        asort($consent_users_select, SORT_NATURAL);

        $consent_users_selected = [];

        // select users
        if ($request->has('user_ids'))
            $consent_users_selected = $request->input('user_ids');
        else if (count($consent_users_select) > 0)
            $consent_users_selected = [array_keys($consent_users_select)[0]];

        $consents = DB::table('research_user')
                            ->where('research_id', $id)
                            ->whereIn('user_id', $consent_users_selected)
                            ->whereDate('updated_at', '<', $date_until)
                            ->groupBy('user_id')
                            ->get();

        $users = User::whereIn('id', $consent_users_selected)->get();

        //die(print_r([$request->input('user_ids'), $consent_users_selected, $users]));
        // Fill dates array
        $assets = ["users"=>0, "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "measurements"=>0, "weather"=>0, "flashlogs"=>0];

        $moment = $moment_start;
        while($moment < $moment_end)
        {
            // make date
            $dates[$moment->format('Y-m-d')] = $assets;
            // next
            $moment = $moment->addDays(1);
        }

        $spreadsheet_array = [];
        if ($download)
        {
            // Fill export array
            
            // Define header rows of tabs
            $spreadsheet_array['User consents'] = [
                           ['User_id',
                           __('export.name'),
                           'Consent date',
                           'Consent (0=no, 1=yes)',
                            ]
                        ];

            $consents_export = DB::table('research_user')
                                ->join('users', 'users.id', '=', 'research_user.user_id')
                                ->select('users.id','users.name','research_user.updated_at','research_user.consent')
                                ->where('research_user.research_id', $id)
                                ->whereIn('user_id', $consent_users_selected)
                                ->whereDate('research_user.updated_at', '<', $date_until)
                                ->get()
                                ->toArray();

            foreach($consents_export as $cex)
                $spreadsheet_array['User consents'][] = [$cex->id, $cex->name, $cex->updated_at, "$cex->consent"];


            $spreadsheet_array[__('export.users')] = [
                           ['User_id',
                            __('export.name'),
                            __('export.email'),
                            __('export.avatar'),
                            __('export.created_at'),
                            __('export.updated_at'),
                            __('export.last_login')]
                        ];

            // add user data to sheet data arrays
            foreach ($users as $user) 
                $spreadsheet_array[__('export.users')][] = $this->getUser($user);


            $spreadsheet_array[__('export.locations')] = [
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

            $spreadsheet_array[__('export.hives')] = [
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

            $spreadsheet_array[__('export.inspections')] = [
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

            $spreadsheet_array[__('export.devices')] = [
                           ['User_id',
                            'Device_id',
                            __('export.name'),
                            'Hive_id',
                            'Location_id',
                            'Type',
                            'last_message_received',
                            'hardware_id',
                            'firmware_version',
                            'hardware_version',
                            'boot_count',
                            'measurement_interval_min',
                            'measurement_transmission_ratio',
                            'ble_pin',
                            'battery_voltage',
                            'next_downlink_message',
                            'last_downlink_result',
                            __('export.created_at'),
                            __('export.deleted_at')]
                        ];

            if ($sensordata)
                $spreadsheet_array['Sensor data'] = [
                            ['User_id',
                            'Device_id',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            if ($sensordata)
                $spreadsheet_array['Sensor Flashlogs'] = [
                            ['User_id',
                            'Device_id',
                            'Hive_id',
                            'Number of messages in file',
                            'Log saved to disk',
                            'Log parsed correctly',
                            'Log has timestamps',
                            'Bytes received',
                            'Raw log file',
                            'Stripped log file',
                            'Parsed log file',
                            __('export.created_at'),
                            __('export.deleted_at')]
                        ];

            $spreadsheet_array['Weather data'] = [
                            ['User_id',
                            'Device_id',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            // Add item names to header row of inspections
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

            foreach ($item_ancs as $name) 
                $spreadsheet_array[__('export.inspections')][0][] = $name;

            $spreadsheet_array[__('export.inspections')][0][] = __('export.deleted_at');
        }

        // Fill dates array with counts of data, and select the data for each user by consent
        foreach ($users as $u) 
        {
            $user_id       = $u->id;
            $user_consents = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();
            
            $user_consent      = $user_consents[0]->consent;
            $date_curr_consent = $date_start > $user_consents[0]->updated_at ? $date_start : $user_consents[0]->updated_at;
            $date_next_consent = $moment_end->format('Y-m-d H:i:s');
            $index             = 0;

            if (count($user_consents) > 1)
            {
                $date_next_consent = $user_consents[1]->updated_at;
                $index             = 1;
            }
            elseif ($user_consent === 0) // if only 1 and consent is false, continue to next user
            {
                continue;
            }
            //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

            // add user data
            $user_apiaries     = Location::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_hives        = Hive::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_devices      = Device::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_inspections  = User::find($user_id)->inspections()->with('items')->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_flashlogs    = FlashLog::where('user_id', $user_id)->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_measurements = [];
            $user_weather_data = [];

            //die(print_r([$date_until, $user_flashlogs->toArray(), $user_hives->toArray()]));

            if ($user_devices->count() > 0)
            {
                // get daily counts of sensor measurements
                $points           = [];
                $weather          = [];
                $user_device_keys = [];
                $user_dloc_coords = [];

                // Add sensor data
                foreach ($user_devices as $device) 
                {
                    $user_device_keys[]= '"key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\'';
                    $loc = $device->location();
                    if ($loc && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                        $user_dloc_coords[] = '("lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\')';
                }
                
                $user_device_keys = '('.implode(' OR ', $user_device_keys).')';

                try{
                    $points = $influx::query('SELECT COUNT("bv") as "count" FROM "sensors" WHERE '.$user_device_keys.' AND time >= \''.$user_consents[0]->updated_at.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d)')->getPoints();
                } catch (InfluxDB\Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                }
                if (count($points) > 0)
                {
                    foreach ($points as $point) 
                        $user_measurements[substr($point['time'],0,10)] = $point['count'];
                }

                // Add weather data
                $user_location_coord_where = '('.implode(' OR ', $user_dloc_coords).')';
                try{
                    $weather = $influx::query('SELECT COUNT("temperature") as "count" FROM "weather" WHERE '.$user_location_coord_where.' AND time >= \''.$user_consents[0]->updated_at.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d)')->getPoints(); // get first weather date
                } catch (InfluxDB\Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                }
                if (count($weather) > 0)
                {
                    foreach ($weather as $point) 
                        $user_weather_data[substr($point['time'],0,10)] = $point['count'];
                }
            }

            // go over dates, compare consent dates
            $i = 0;
            //print_r([$index, $user_consent, $date_curr_consent, $date_next_consent]);
            $user_data_counts = $assets;

            foreach ($dates as $d => $v) 
            {
                $d_start      = $d.' 00:00:00';
                $d_end        = $d.' 23:59:59';
                $next_consent = false;

                if ($d_end >= $date_next_consent && $index > 0 && $index < count($user_consents)) // change user_consent if multiple user_consents exist and check date is past the active consent date 
                {
                    $next_consent = true;

                    // take current user_consent
                    $user_consent       = $user_consents[$index]->consent;
                    $date_curr_consent  = $user_consents[$index]->updated_at;
                    //fill up to next consent date
                    if ($index < count($user_consents)-1)
                        $date_next_consent  = $user_consents[$index+1]->updated_at;
                    else
                        $date_next_consent = $moment_end->format('Y-m-d H:i:s');

                    //print_r([$index, $user_consent, $date_curr_consent, $date_next_consent]);
                    $index++;
                }

                // Fill objects for consent period
                if ($user_consent && ($next_consent || $i == 0))
                {    
                    // add 
                    $user_data_counts['users']    = $user_consent;
                    $user_data_counts['apiaries'] = $user_apiaries->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['hives']    = $user_hives->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['devices']  = $user_devices->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['flashlogs']= $user_flashlogs->where('created_at', '<=', $date_next_consent)->count();

                    if ($download)
                    {

                        $locas = $this->getLocations($user_id, $user_apiaries, $date_curr_consent, $date_next_consent);
                        foreach ($locas as $loca)
                            $spreadsheet_array[__('export.locations')][] = $loca;

                        $hives = $this->getHives($user_id, $user_hives, $date_curr_consent, $date_next_consent);
                        foreach ($hives as $hive)
                            $spreadsheet_array[__('export.hives')][] = $hive;

                        $insps = $this->getInspections($user_id, $user_inspections, $item_ancs, $date_curr_consent, $date_next_consent);
                        foreach ($insps as $insp)
                            $spreadsheet_array[__('export.inspections')][] = $insp;

                        $flash = $this->getFlashlogs($user_id, $user_flashlogs, $date_curr_consent, $date_next_consent);
                        foreach ($flash as $fla)
                            $spreadsheet_array['Sensor Flashlogs'][] = $fla;
                        

                        if ($sensordata && $user_devices->count() > 0)
                        {
                            foreach ($user_devices as $device)
                            {
                                // Add device to spreadsheet
                                if ($device->created_at < $date_next_consent)
                                {
                                    $spreadsheet_array[__('export.devices')][] = $this->getDevice($user_id, $device);
                                
                                    // Export data to file per device / period
                                    $where    = 'WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                    $fileName = strtolower(env('APP_NAME')).'-export-'.$research->name.'-device-id-'.$device->id.'-sensor-data-'.substr($date_curr_consent,0,10).'-'.substr($date_next_consent,0,10).'-'.Str::random(10).'.csv';
                                    $filePath = $this->exportCsvFromInflux($where, $fileName, '*', 'sensors');
                                    if ($filePath)
                                    {
                                        $spreadsheet_array['Sensor data'][] = [$user_id, $device->id, $date_curr_consent, $date_next_consent, $filePath];
                                        $sensor_urls[$fileName] = $filePath;
                                    }

                                    // Export data to file per device / period
                                    $loc = $device->location();
                                    if ($loc && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                                    {
                                        $where    = 'WHERE "lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                        $fileName = strtolower(env('APP_NAME')).'-export-'.$research->name.'-device-id-'.$device->id.'-weather-data-'.substr($date_curr_consent,0,10).'-'.substr($date_next_consent,0,10).'-'.Str::random(10).'.csv';
                                        $filePath = $this->exportCsvFromInflux($where, $fileName, '*', 'weather');
                                        if ($filePath)
                                        {
                                            $spreadsheet_array['Weather data'][] = [$user_id, $device->id, $date_curr_consent, $date_next_consent, $filePath];
                                            $sensor_urls[$fileName] = $filePath;
                                        }
                                    }
                                }
                            }
                        }
                    }

                }

                // Fill day array
                if ($user_consent && $d_start > $date_curr_consent)
                {
                    // Count
                    $dates[$d]['users']      += $user_data_counts['users'];
                    $dates[$d]['apiaries']   += $user_data_counts['apiaries'];
                    $dates[$d]['hives']      += $user_data_counts['hives'];
                    $dates[$d]['devices']    += $user_data_counts['devices'];

                    $inspections_today        = $user_inspections->where('created_at', '>=', $d_start)->where('created_at', '<=', $d_end)->count();
                    $dates[$d]['inspections'] = $v['inspections'] + $inspections_today;
                    
                    $flashlogs_today          = $user_flashlogs->where('created_at', '>=', $d_start)->where('created_at', '<=', $d_end)->count();
                    $dates[$d]['flashlogs']   = $v['flashlogs'] + $flashlogs_today;
                    
                    if (in_array($d, array_keys($user_measurements)))
                        $dates[$d]['measurements']= $v['measurements'] + $user_measurements[$d];

                    if (in_array($d, array_keys($user_weather_data)))
                        $dates[$d]['weather']= $v['weather'] + $user_weather_data[$d];

                }

                $i++;
            }
            //die();
        }

        // reverse array for display
        krsort($dates);

        // Count totals
        $totals = $assets;
        foreach ($dates as $day => $day_arr) 
        {
            foreach ($day_arr as $asset => $count) 
            {
                if ($asset == 'inspections' || $asset == 'measurements' || $asset == 'weather')
                    $totals[$asset] += $count;
                else
                    $totals[$asset] = max($totals[$asset], $count);
            }
        }

        // Export data, show download link
        if ($download)
        {
            //die(print_r([$consents, $spreadsheet_array[__('export.devices')], $spreadsheet_array['Sensor data']]));
            $fileName     = strtolower(env('APP_NAME')).'-export-'.$research->name;
            $download_url = $this->export($spreadsheet_array, $fileName, $date_start, $date_until);
        }

        return view('research.show', compact('research', 'dates', 'consent_users_select', 'consent_users_selected', 'download_url', 'sensor_urls', 'totals', 'date_start', 'date_until'));
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
        if (Auth::user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research = Auth::user()->researchesOwned()->findOrFail($id);

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
        $this->checkAuthorization($request);

        $this->validate($request, [
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'user_id'       => 'nullable|exists:users,id',
            'viewer_ids'    => 'nullable|exists:users,id',
            'checklist_ids' => 'nullable|exists:checklists,id',
        ]);

        if (Auth::user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research =$request->user()->researchesOwned()->findOrFail($id);

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


        $research->update($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);

        if (isset($requestData['viewer_ids']))
            $research->viewers()->sync($requestData['viewer_ids']);

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
        if (Auth::user()->hasRole('superadmin'))
        {
            Research::destroy($id);
        }
        else
        {
            $research = Auth::user()->researchesOwned()->findOrFail($id);
            $research->delete();
        }

        return redirect('research')->with('flash_message', 'Research deleted!');
    }


    /* Data export functions */

    private function export($spreadsheetArray, $fileName='export', $date_start='Research start', $date_until='Research end')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set meta data
        $sheet->setTitle('Meta data');
        $sheet->setCellValue('A1', 'Meta data');
        $sheet->setCellValue('A3', env('APP_NAME').' data export');
        $sheet->setCellValue('C3', date('Y-m-d H:i:s'));
        $sheet->setCellValue('A4', 'Start date');
        $sheet->setCellValue('C4', $date_start);
        $sheet->setCellValue('A5', 'End date');
        $sheet->setCellValue('C5', $date_until);
        $sheet->setCellValue('A6', 'Sheets');
        $sheet->setCellValue('C6', count($spreadsheetArray));

        $row = 8;
        foreach ($spreadsheetArray as $title => $data)
        {
            $sheet->setCellValue('A'.$row, $title);
            $sheet->setCellValue('C'.$row, count($data)-1);
            $row++;
        }
        
        // Fill sheet with tabs and data
        foreach ($spreadsheetArray as $title => $data) 
        {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($title);
            $sheet->fromArray($data);
        }
        
        // save sheet
        $fileName = $fileName.'-'.Str::random(40);
        $filePath = 'exports/'.$fileName.'.xlsx';
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

    private function getLocations($user_id, $locations, $date_start=null, $date_until=null)
    {
        return $locations->where('created_at', '<=', $date_until)->sortBy('name')->map(function($item) use ($user_id)
        {
            return [
                $user_id,
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
    
    private function getHives($user_id, $hives, $date_start=null, $date_until=null)
    {
        return $hives->where('created_at', '<=', $date_until)->sortBy('name')->map(function($item) use ($user_id)
        {
            $queen = $item->queen;

            return [
                $user_id,
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

    private function getDevice($user_id, $item)
    {
        return [
            $user_id,
            $item->id, 
            $item->name, 
            $item->hive_id,
            $item->location_id,
            $item->getTypeAttribute(),
            $item->last_message_received,
            $item->hardware_id,
            $item->firmware_version,
            $item->hardware_version,
            $item->boot_count,
            $item->measurement_interval_min,
            $item->measurement_transmission_ratio,
            $item->ble_pin,
            $item->battery_voltage,
            $item->next_downlink_message,
            $item->last_downlink_result,
            $item->created_at,
            $item->deleted_at
        ];
    }

    private function getFlashlogs($user_id, $flashlogs, $date_start=null, $date_until=null)
    {
        return $flashlogs->where('created_at', '<=', $date_until)->sortBy('name')->map(function($item) use ($user_id)
        {
            return [
                $user_id,
                $item->device_id, 
                $item->hive_id,
                $item->log_messages,
                $item->log_saved,
                $item->log_parsed,
                $item->log_has_timestamps,
                $item->bytes_received,
                $item->log_file,
                $item->log_file_stripped,
                $item->log_file_parsed,
                $item->created_at,
                $item->deleted_at,
            ];
        });
    }

    private function getInspections($user_id, $inspections, $item_names, $date_start=null, $date_until=null)
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
                'user_id' => $user_id,
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

    private function exportCsvFromInflux($where, $fileName='research-export-', $measurements='*', $database='sensors', $separator=',')
    {
        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        
        if ($measurements == null || $measurements == '' || $measurements === '*')
            $sensor_measurements = '*';
        else
            $sensor_measurements = $measurements;

        $query = 'SELECT '.$sensor_measurements.' FROM "'.$database.'" '.$where;
        
        try{
            $client = new \Influx; 
            $data   = $client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return null;
        }

        if (count($data) == 0)
            return null;

        // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";

        $csv_sens = array_diff(array_keys($data[0]),["sensor","key"]);
        $csv_head = [];
        foreach ($csv_sens as $sensor_name) 
        {
            $meas       = Measurement::where('abbreviation', $sensor_name)->first();
            $csv_head[] = $meas ? $meas->pq_name_unit().' ('.$sensor_name.')' : $sensor_name;
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
        $filePath = 'exports/'.$fileName;
        $disk     = env('EXPORT_STORAGE', 'public');

        if (Storage::disk($disk)->put($filePath, $csv_file))
            return Storage::disk($disk)->url($filePath);

        return null;
    }

}
