<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;

use App\User;
use App\Research;
use App\Location;
use App\Hive;
use App\Inspection;
use App\Device;
use App\Measurement;
use App\Models\FlashLog;
use App\Models\CalculationModel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Cache;
use DB;
use Auth;
use Str;
use Storage;
use InfluxDB;
use Moment\Moment;

class ResearchController extends Controller
{
    protected $valid_sensors  = [];
    protected $output_sensors = [];

    public function __construct()
    {
        $this->valid_sensors  = Measurement::getValidMeasurements();
        $this->output_sensors = Measurement::getValidMeasurements(true);
        $this->client         = new \Influx;
        //die(print_r($this->valid_sensors));
    }

    private function cacheRequestRate($name)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count');
        else
            Cache::put($name.'-count', 1);

    }

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

    public function consent($id, Request $request)
    {
        $this->checkAuthorization($request);

        if ($request->user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research = $request->user()->allResearches()->find($id);
        
        $consents = DB::table('research_user')
                    ->where('research_id', $id)
                    ->whereDate('updated_at', '<=', $research->end_date)
                    ->orderBy('user_id')
                    ->orderBy('updated_at','asc')
                    ->get();

        foreach ($consents as $c)
        {
            $c->user_name = User::find($c->user_id)->name;
        }

        return view('research.consent', compact('research', 'consents'));
    }

    public function consent_edit($id, $c_id, Request $request)
    {
        $this->checkAuthorization($request);

        if ($request->user()->hasRole('superadmin') == false)
            return redirect('research.index')->with('error', 'Unauthorized');
        
        $research = Research::findOrFail($id);
        $item     = DB::table('research_user')->where('id', $c_id)->first();

        // Convert to arrays for selection
        $item->consent_sensor_ids = explode(',', $item->consent_sensor_ids);
        $item->consent_location_ids = explode(',', $item->consent_location_ids);
        $item->consent_hive_ids = explode(',', $item->consent_hive_ids);

        $item->user_name = User::find($item->user_id)->name;

        if ($item)
        {
            if ($request->filled('delete') && $request->input('delete') == 1)
            {
                DB::delete('delete from research_user where id = ?', [$c_id]);
                return redirect()->route('research.consent', $id)->with('success', 'Consent deleted');
            }
            else if ($request->filled('consent_sensor_ids') || $request->filled('consent_location_ids') || $request->filled('consent_hive_ids'))
            {
                if ($request->filled('consent_sensor_ids') && is_array($request->input('consent_sensor_ids')))
                    DB::update('update research_user set consent_sensor_ids = ? where id = ?', [implode(',',$request->input('consent_sensor_ids')), $c_id]);
                
                if ($request->filled('consent_location_ids') && is_array($request->input('consent_location_ids')))
                    DB::update('update research_user set consent_location_ids = ? where id = ?', [implode(',',$request->input('consent_location_ids')), $c_id]);
                
                if ($request->filled('consent_hive_ids') && is_array($request->input('consent_hive_ids')))
                    DB::update('update research_user set consent_hive_ids = ? where id = ?', [implode(',',$request->input('consent_hive_ids')), $c_id]);

                return redirect()->route('research.consent', $id)->with('success', 'Consent updated');
            }

        }

        return view('research.consent_edit', compact('research', 'item'));
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
            'default_user_ids.*' => 'nullable|exists:users,id',
            'viewer_ids.*'    => 'nullable|exists:users,id',
            'checklist_ids.*' => 'nullable|exists:checklists,id',
            'user_id'       => 'nullable|exists:users,id',
            'visible'       => 'boolean',
            'on_invite_only'=> 'boolean',
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

        if (!isset($requestData['default_user_ids']))
            $requestData['default_user_ids'] = null;
        
        $research = Research::create($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);
        else
            $research->checklists()->sync([]);

        if (isset($requestData['viewer_ids']))
            $research->viewers()->sync($requestData['viewer_ids']);
        else
            $research->viewers()->sync([]);

        return redirect('research')->with('flash_message', 'Research added!');
    }

    /**
     * Display Research overview.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id, Request $request)
    {
        $this->checkAuthorization($request);

        $this->validate($request, [
            'date_start'    => 'nullable|date',
            'date_until'    => 'nullable|date|after:date_start',
            'user_ids.*'    => 'nullable|exists:users,id',
            'device_ids.*'  => 'nullable|exists:sensors,id',
        ]);

        //die(print_r($request->all()));

        if ($request->user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research = $request->user()->allResearches()->find($id);

        $download_url = null;
        $sensor_urls  = [];
        $download     = $request->has('download-meta') || $request->has('download-all') ? true : false;
        $dl_sensordata= $request->has('download-all');
        $device_ids   = $request->input('device_ids');
        $devices_all  = collect();
        $initial_days = 14;

        // Make dates table
        $dates  = [];

        $date_today   = date('Y-m-d');
        $date_last    = isset($research->end_date) ? $research->end_date : $date_today;
        $date_start   = max($research->start_date, $request->input('date_start', $research->start_date));
        $date_until   = min($research->end_date, $request->input('date_until', $date_last));

        $moment_now   = new Moment();
        $moment_start = new Moment($date_start);
        $moment_until = new Moment($date_until);
        $moment_end   = new Moment($research->end_date);
        $moment_now   = $moment_now->startof('day');
        $moment_until = $moment_until->endof('day');
        $moment_end   = $moment_end->endof('day');

        $research_days= $moment_start->from($moment_end)->getDays();
        $days_from_now= $moment_start->from($moment_now)->getDays();
        $days_from_end= $moment_now->from($moment_end)->getDays();

        $initial_days = min($research_days+1, $initial_days);
        $day_diff     = 0;

        if ($days_from_now <= 0) // research has not started yet
        {
            $moment_select = new Moment($date_start);
            $date_start = $moment_select->startof('day')->format('Y-m-d');
            $moment_select = new Moment($date_start);
            $date_until = min($date_until, $moment_select->endof('day')->addDays($initial_days-1)->format('Y-m-d'));
        }
        else if ($days_from_end <= 0) // research has already finished
        {
            $moment_select = new Moment($date_until);
            $date_start = $moment_select->startof('day')->addDays(-$initial_days-1)->format('Y-m-d');
            $moment_select = new Moment($date_until);
            $date_until = min($date_until, $moment_select->endof('day')->format('Y-m-d'));

        }
        else // now is within research start end end date
        {
            if ($request->filled('date_start') == false)
            {
                $days_from_sta = max(0, $initial_days - $days_from_now);
                $moment_select = new Moment();
                $date_start    = $moment_select->startof('day')->addDays(-$initial_days-1-$days_from_sta)->format('Y-m-d');
            }

            if ($request->filled('date_until') == false)
            {
                $moment_select = new Moment();
                $date_until    = min($date_until, $moment_select->endof('day')->addDays(-$days_from_sta)->format('Y-m-d'));
            }
        }
        //dd($date_start, $research->start_date, $date_until, $date_last, $research->end_date, $days_from_now, $days_from_end);

        // Cap to research
        if ($date_start < $research->start_date)
            $date_start = $research->start_date;

        if ($date_until > $research->end_date)
            $date_until = $research->end_date;

        $moment_start = new Moment($date_start);
        $moment_end   = new Moment($date_until);

        //dd($date_start, $date_until, $research_days, $days_from_now, $days_from_end, $day_diff, $moment_now, $moment_start, $moment_end);
        
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
        else if (isset($research->default_user_ids))
            $consent_users_selected = $research->default_user_ids;
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
        $assets = ["users"=>0, "user_names"=>[], "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "devices_online"=>0, "device_names"=>[], "devices_offline"=>[], "measurements"=>0, "measurements_imported"=>0, "measurements_total"=>0, "data_completeness"=>0, "data_completeness_online"=>0, "weather"=>0, "flashlogs"=>0, "samplecodes"=>0, "sensor_definitions"=>0, "alert_rules"=>0, "alerts"=>0];

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
                            'Queen_id',
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

            $spreadsheet_array['Sample codes'] = [
                           ['User_id',
                            'Sample code',
                            'Sample date',
                            'Country code',
                            'Location_id',
                            'Location_name',
                            'Hive_id',
                            'Hive_name',
                            'Queen_id',
                            'Sample note',
                            'Test type',
                            'Test date',
                            'Test result',
                            'Test lab name',
                            __('export.updated_at'),
                            __('export.created_at')]
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
                            'rtc_installed',
                            'log_data_valid',
                            'log_data_state',
                            'log_data_csv',
                            'log_data_note',
                            'log_data_info',
                            'last_message_received',
                            'device_datetime_last_received',
                            'device_datetime_offset_sec',
                            'sensor_definition_count',
                            __('export.created_at'),
                            __('export.deleted_at')]
                        ];

            $spreadsheet_array[__('export.sensordefs')] = [
                           ['Name',
                            'Device_id',
                            'inside_hive',
                            'offset',
                            'multiplier',
                            'input_measurement',
                            'output_measurement',
                            __('export.created_at'),
                            __('export.deleted_at')]
                        ];

            if ($dl_sensordata)
                $spreadsheet_array['Sensor data'] = [
                            ['User_id',
                            'Device_id',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            $spreadsheet_array['Sensor Flashlogs'] = [
                            ['User_id',
                            'Device_id',
                            __('export.created_at'),
                            'Hive_id',
                            'Number of messages in file',
                            'Log saved to disk',
                            'Log parsed correctly',
                            'Log erased from device',
                            'Log has timestamps',
                            'Time percentage (%)',
                            'Bytes received',
                            'Persisted days of data',
                            'Persisted measurements',
                            'Persisted blocks',
                            'Log date start',
                            'Log date end',
                            'Logs per day',
                            'Log time complete (%)',
                            'Log valid',
                            'Log meta',
                            'CSV file',
                            'Raw log file',
                            'Stripped log file',
                            'Parsed log file',
                            __('export.deleted_at')]
                        ];

            if ($dl_sensordata)
                $spreadsheet_array['Weather data'] = [
                            ['User_id',
                            'Device_id',
                            'Location_id',
                            'Location_lat',
                            'Location_lon',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            $spreadsheet_array['Alert rules'] = [
                            [
                                'User_id',
                                __('export.created_at'),
                                'Name',
                                'Function',
                                'Calculation minutes',
                                'Last calculated',
                                'Last evaluated'
                            ]
                        ];

            $spreadsheet_array['Alerts'] = [
                            [
                                'User_id',
                                'Device_id',
                                'Location_id',
                                'Hive_id',
                                __('export.created_at'),
                                'Function',
                                'Value',
                                'Count'
                            ]
                        ];

            // Add item names to header row of inspections
            // first combine all user's itemnames
            $item_ancs  = [];
            $item_names = [];
            foreach ($users as $user) 
            {
                $ins = Inspection::item_names($user->allInspections()->get());
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
            $user          = User::find($user_id);
            $user_consents = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();
            
            if (!isset($user) || !isset($user_consents) || count($user_consents) == 0)
                continue;

            $user_consent_obj  = $user_consents[0];
            $user_consent      = $user_consent_obj->consent;
            $date_curr_consent = $date_start > $user_consent_obj->updated_at ? $date_start : $user_consent_obj->updated_at;
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
            if (isset($user_consent_obj->consent_location_ids)) // Set consent based on specific items
            {
                $loc_array     = explode(',', $user_consent_obj->consent_location_ids);
                $user_apiaries = $user->locations()->withTrashed()->whereIn('id', $loc_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_apiaries = $user->locations()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            if (isset($user_consent_obj->consent_hive_ids)) // Set consent based on specific items
            {
                $hive_array    = explode(',', $user_consent_obj->consent_hive_ids);
                $user_hives    = $user->hives()->withTrashed()->whereIn('id', $hive_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_hives    = $user->hives()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            if (isset($user_consent_obj->consent_sensor_ids)) // Set consent based on specific items
            {
                $device_array     = explode(',', $user_consent_obj->consent_sensor_ids);
                $user_devices_all = $user->devices()->withTrashed()->whereIn('id', $device_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_devices_all = $user->devices()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            $devices_all       = $devices_all->merge($user_devices_all);
            $user_devices      = isset($device_ids) ? $user_devices_all->whereIn('id', $device_ids) : $user_devices_all;
            $user_devices_online = isset($device_ids) ? $user_devices_all->whereIn('id', $device_ids)->where('last_message_received', '>=', $date_start) : $user_devices_all->where('last_message_received', '>=', $date_start);
            $user_flashlogs    = FlashLog::where('user_id', $user_id)->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            $user_samplecodes  = $user->samplecodes()->where('sample_date', '>=', $date_start)->where('sample_date', '<', $date_until)->orderBy('sample_date')->get();
            $user_measurements = [];
            $user_meas_import  = [];
            $user_weather_data = [];
            $user_sensor_defs  = [];
            $user_alert_rules  = $user->alert_rules()->where('default_rule', 0)->where('active', 1)->get();
            $user_alerts       = isset($device_ids) ? $user->alerts()->whereIn('device_id', $device_ids)->where('show', 1)->get() : $user->alerts()->where('show', 1)->get();
            
            //die(print_r($user_devices->toArray()));

            // add hive inspections (also from collaborators)
            $hive_inspection_ids = [];
            foreach ($user_hives as $hive)
            {
                $hive_inspections = $hive->inspections()->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->get();
                foreach ($hive_inspections as $ins) 
                    $hive_inspection_ids[] = $ins->id;
                
            }
            $hive_inspections  = Inspection::whereIn('id', $hive_inspection_ids)->with('items')->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();

            //die(print_r([$date_until, $hive_inspections->toArray(), $user_hives->toArray()]));

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
                    $user_device_keys[]= $device->influxWhereKeys();
                    $loc = $device->location();
                    if ($loc !== null && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                        $user_dloc_coords[] = '("lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\')';

                    // Add sensor definitions
                    $sensor_defs = $this->getSensorDefinitions($device, $date_next_consent);
                    foreach ($sensor_defs as $sdef)
                        $user_sensor_defs[] = $sdef;

                }

                if (count($user_device_keys) > 0)
                {
                    $user_device_keys = '('.implode(' OR ', $user_device_keys).')';

                    try{
                        $this->cacheRequestRate('influx-get');
                        $this->cacheRequestRate('influx-research');
                        $query  = 'SELECT COUNT("bv") as "count" FROM "sensors" WHERE '.$user_device_keys.' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d),from_flashlog';
                        //die($query); 
                        $points = $this->client::query($query)->getPoints();
                    } catch (InfluxDB\Exception $e) {
                        Log::error('Research data query error: '.$e->getMessage());
                        // return Response::json('influx-group-by-query-error', 500);
                    }
                    if (count($points) > 0)
                    {
                        foreach ($points as $point)
                        {
                            if ($point['from_flashlog'] === '1')
                                $user_meas_import[substr($point['time'],0,10)] = $point['count'];
                            else
                                $user_measurements[substr($point['time'],0,10)] = $point['count'];
                        }
                    }
                }

                // Add weather data
                $user_location_coord_where = '('.implode(' OR ', $user_dloc_coords).')';
                if (count($user_dloc_coords) > 0 && isset($date_curr_consent))
                {
                    try{
                        $weather = $this->client::query('SELECT COUNT("temperature") as "count" FROM "weather" WHERE '.$user_location_coord_where.' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d)')->getPoints(); // get first weather date
                    } catch (InfluxDB\Exception $e) {
                        Log::error('Research weather query error: '.$e->getMessage());
                        // return Response::json('influx-group-by-query-error', 500);
                    }
                    if (count($weather) > 0)
                    {
                        foreach ($weather as $point) 
                            $user_weather_data[substr($point['time'],0,10)] = $point['count'];
                    }
                }
            }

            // go over dates, compare consent dates
            $i = 0;
            $user_data_counts    = $assets;
            $last_devices_online = [];

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

                    // hide this data from dataset, because earlier than requested
                    if ($date_next_consent < $date_start || $date_curr_consent > $date_until)
                    {
                        $index++;
                        continue;
                    }
                    
                    // minimize consent dates to start/unit date
                    if ($date_curr_consent < $date_start)
                        $date_curr_consent = $date_start.' 00:00:00';

                    if ($date_next_consent > $date_until)
                        $date_next_consent = $date_until.' 23:59:59';

                    //print_r([$index, $user_consent, $date_start, $date_until, $date_curr_consent, $date_next_consent, $moment_end]);

                    $index++;
                }
                
            

                // Fill objects for consent period
                if ($user_consent && ($next_consent || $i == 0))
                {    
                    // add 
                    $user_data_counts['users']              = $user_consent;
                    $user_data_counts['apiaries']           = $user_apiaries->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['hives']              = $user_hives->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['devices']            = $user_devices->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['devices_online']     = $user_devices_online->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['flashlogs']          = $user_flashlogs->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['alert_rules']        = $user_alert_rules->where('created_at', '<=', $date_next_consent)->count();
                    $user_data_counts['sensor_definitions'] = count($user_sensor_defs);

                    //print_r([$i, $index, $user_consent, $date_curr_consent, $date_next_consent, $user_data_counts, $user_devices->toArray()]);

                    if ($download)
                    {

                        $locas = $this->getLocations($user_id, $user_apiaries, $date_curr_consent, $date_next_consent);
                        foreach ($locas as $loca)
                            $spreadsheet_array[__('export.locations')][] = $loca;

                        $hives = $this->getHives($user_id, $user_hives, $date_curr_consent, $date_next_consent);
                        foreach ($hives as $hive)
                            $spreadsheet_array[__('export.hives')][] = $hive;

                        $insps = $this->getInspections($user_id, $hive_inspections, $item_ancs, $date_curr_consent, $date_next_consent);
                        foreach ($insps as $insp)
                            $spreadsheet_array[__('export.inspections')][] = $insp;

                        $flash = $this->getFlashlogs($user_id, $user_flashlogs, $date_curr_consent, $date_next_consent);
                        foreach ($flash as $fla)
                            $spreadsheet_array['Sensor Flashlogs'][] = $fla;
                        
                        $sampe = $this->getSampleCodes($user_id, $user_samplecodes, $date_curr_consent, $date_next_consent);
                        foreach ($sampe as $sam)
                            $spreadsheet_array['Sample codes'][] = $sam;

                        if (count($user_sensor_defs) > 0)
                            foreach ($user_sensor_defs as $sdef) 
                                $spreadsheet_array[__('export.sensordefs')][] = $sdef;

                        $alert_rules = $this->getAlertRules($user_id, $user_alert_rules, $date_curr_consent, $date_next_consent);
                        foreach ($alert_rules as $ar)
                            $spreadsheet_array['Alert rules'][] = $ar;

                        $alerts = $this->getAlerts($user_id, $user_alerts, $date_curr_consent, $date_next_consent);
                        foreach ($alerts as $al)
                            $spreadsheet_array['Alerts'][] = $al;

                        if ($user_devices->count() > 0)
                        {
                            foreach ($user_devices as $device)
                            {
                                // Add device to spreadsheet
                                if ($device->created_at < $date_next_consent)
                                {
                                    $spreadsheet_array[__('export.devices')][] = $this->getDevice($user_id, $device);
                                    
                                    if ($dl_sensordata)
                                    {
                                        // Export data to file per device / period
                                        $where    = $device->influxWhereKeys().' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                        $fileName = strtolower(env('APP_NAME')).'-export-research-'.$research->id.'-device-id-'.$device->id.'-sensor-data-'.substr($date_curr_consent,0,10).'-'.substr($date_next_consent,0,10).'-'.Str::random(10).'.csv';
                                        $filePath = $this->exportCsvFromInflux($where, $fileName, '*', 'sensors');
                                        if ($filePath)
                                        {
                                            $spreadsheet_array['Sensor data'][] = [$user_id, $device->id, $date_curr_consent, $date_next_consent, "$filePath"];
                                            $sensor_urls[$fileName] = $filePath;
                                        }

                                        // Export data to file per device location / period
                                        $loc = $device->location();
                                        if ($loc !== null && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                                        {
                                            $where    = '"lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                            $fileName = strtolower(env('APP_NAME')).'-export-research-'.$research->id.'-device-id-'.$device->id.'-weather-data-'.substr($date_curr_consent,0,10).'-'.substr($date_next_consent,0,10).'-'.Str::random(10).'.csv';
                                            $filePath = $this->exportCsvFromInflux($where, $fileName, '*', 'weather');
                                            if ($filePath)
                                            {
                                                $spreadsheet_array['Weather data'][] = [$user_id, $device->id, $loc->id, $loc->coordinate_lat, $loc->coordinate_lon, $date_curr_consent, $date_next_consent, "$filePath"];
                                                $sensor_urls[$fileName] = $filePath;
                                            }
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
                    $dates[$d]['user_names'][]= $user->name;
                    $dates[$d]['apiaries']   += $user_data_counts['apiaries'];
                    $dates[$d]['hives']      += $user_data_counts['hives'];
                    $dates[$d]['devices']    += $user_data_counts['devices'];
                    $dates[$d]['devices_online']+= $user_devices_online->where('last_message_received', '>=', $d_start)->count();
                    $dates[$d]['device_names']= array_merge($dates[$d]['device_names'], $user_devices_online->where('last_message_received', '>=', $d_start)->pluck('name')->toArray());
                    
                    if (count($last_devices_online) > 0)
                    {
                        $dates[$d]['devices_offline'] = array_diff($last_devices_online, $dates[$d]['device_names']);
                    }
                    $last_devices_online      = $dates[$d]['device_names'];
                    
                    $dates[$d]['sensor_definitions'] += $user_data_counts['sensor_definitions'];
                    $dates[$d]['alert_rules']+= $user_data_counts['alert_rules'];

                    $inspections_today        = $hive_inspections->where('created_at', '>=', $d_start)->where('created_at', '<=', $d_end)->count();
                    $dates[$d]['inspections'] = $v['inspections'] + $inspections_today;
                    
                    $flashlogs_today          = $user_flashlogs->where('created_at', '>=', $d_start)->where('created_at', '<=', $d_end)->count();
                    $dates[$d]['flashlogs']   = $v['flashlogs'] + $flashlogs_today;

                    $samplecodes_today        = $user_samplecodes->where('sample_date', '>=', $d_start)->where('sample_date', '<=', $d_end)->count();
                    $dates[$d]['samplecodes'] = $v['samplecodes'] + $samplecodes_today;

                    $alerts_today             = $user_alerts->where('created_at', '>=', $d_start)->where('created_at', '<=', $d_end)->count();
                    $dates[$d]['alerts']      = $v['alerts'] + $alerts_today;
                    
                    if (in_array($d, array_keys($user_measurements)))
                        $dates[$d]['measurements'] = $v['measurements'] + $user_measurements[$d];

                    if (in_array($d, array_keys($user_meas_import)))
                        $dates[$d]['measurements_imported'] = $v['measurements_imported'] + $user_meas_import[$d];

                    $dates[$d]['measurements_total']    = $dates[$d]['measurements'] + $dates[$d]['measurements_imported'];
                    $dates[$d]['data_completeness']     = $dates[$d]['devices'] > 0 ? round(100 * $dates[$d]['measurements_total'] / ($dates[$d]['devices'] * (60*24/15))) : '';
                    $dates[$d]['data_completeness_online'] = $dates[$d]['devices_online'] > 0 ? min(100, round(100 * $dates[$d]['measurements_total'] / ($dates[$d]['devices_online'] * (60*24/15)))) : '';

                    if (in_array($d, array_keys($user_weather_data)))
                        $dates[$d]['weather']= $v['weather'] + $user_weather_data[$d];

                }

                $i++;
            } // end day loop
        } // end user loop

        // reverse array for display
        krsort($dates);

        // Count totals
        $totals    = $assets;
        $data_days = 0;
        foreach ($dates as $day => $day_arr) 
        {
            foreach ($day_arr as $asset => $count) 
            {
                if (is_numeric($count))
                {
                    if ($asset == 'inspections' || $asset == 'measurements' || $asset == 'measurements_imported' || $asset == 'measurements_total' || $asset == 'data_completeness' || $asset == 'data_completeness_online' || $asset == 'weather' || $asset == 'samplecodes' || $asset == 'flashlogs' || $asset == 'alerts')
                        $totals[$asset] += $count;
                    else
                        $totals[$asset] = max($totals[$asset], $count);

                    if ($asset == 'measurements_total' && $count > 0)
                        $data_days++;
                }
            }
        }

        // Average data completenes
        if ($data_days > 0)
        {
            $totals['data_completeness']        = round($totals['data_completeness'] / $data_days);
            $totals['data_completeness_online'] = round($totals['data_completeness_online'] / $data_days);
        }

        // Export data, show download link
        if ($download)
        {
            //die(print_r([$consents, $totals, $spreadsheet_array[__('export.devices')], $spreadsheet_array[__('export.sensordefs')]]));
            $fileName     = strtolower(env('APP_NAME')).'-export-research-'.$research->id;
            $download_url = $this->export($spreadsheet_array, $fileName, $date_start, $date_until);
        }

        // Make readable devices select list 
        $devices_select = [];
        $devices_sorted = $devices_all->sortBy('user_id')->sortBy('name');
        foreach ($devices_sorted as $d)
            $devices_select[$d->id] = $d->name.' - ('.$d->user->name.')'; 

        return view('research.show', compact('research', 'dates', 'consent_users_select', 'consent_users_selected', 'download_url', 'sensor_urls', 'totals', 'date_start', 'date_until', 'devices_select', 'device_ids'));
    }


/**
     * Display Research data per device.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function data($id, Request $request)
    {
        $this->checkAuthorization($request);

        $this->validate($request, [
            'date_start'            => 'nullable|date',
            'date_until'            => 'nullable|date|after:date_start',
            'user_ids.*'            => 'nullable|exists:users,id',
            'device_ids.*'          => 'nullable|exists:sensors,id',
            'add_flashlogs'         => 'nullable|boolean',
            'until_last_fl'         => 'nullable|boolean',
            'invalid_log_prognose'  => 'nullable|boolean',
            'log_device_id'         => 'nullable|exists:sensors,id',
            'log_device_note'       => 'nullable|string',
            'only_change'           => 'nullable|string',
            'only_change_value'     => 'nullable|string',
        ]);

        $demo = $request->filled('demo') && boolval($request->input('demo')) ? true : false;

        //die(print_r($request->all()));

        if ($request->user()->hasRole('superadmin'))
            $research = Research::findOrFail($id);
        else
            $research = $request->user()->allResearches()->find($id);

        $device_ids       = $request->input('device_ids');
        $log_device_id    = intval($request->input('log_device_id'));
        $log_device_note  = $request->input('log_device_note');
        $log_device_change= $request->input('only_change');
        $log_device_value = $request->input('only_change_value');
        $add_flashlogs    = boolval($request->input('add_flashlogs', 1));
        $until_last_fl    = boolval($request->input('until_last_fl', 1));
        $invalid_log_prognose = boolval($request->input('invalid_log_prognose', 0));
        $devices_all      = collect();
        $devices_show     = collect();
        $initial_days     = 30;

        // Make dates table:
        /* [date] => [
              key1 => [points, imported]
              key2 => [points, imported]
           ]
        
        */
        $dates        = [];

        $date_today   = date('Y-m-d');
        $date_last    = isset($research->end_date) ? $research->end_date : $date_today;
        $date_start   = max($research->start_date, $request->input('date_start', $research->start_date));
        $date_until   = min($research->end_date, $request->input('date_until', $date_last));

        $moment_now   = new Moment();
        $moment_start = new Moment($date_start);
        $moment_until = new Moment($date_until);
        $moment_end   = new Moment($research->end_date);
        $moment_now   = $moment_now->startof('day');
        $moment_until = $moment_until->endof('day');
        $moment_end   = $moment_end->endof('day');

        $research_days= $moment_start->from($moment_end)->getDays();
        $days_from_now= $moment_start->from($moment_now)->getDays();
        $days_from_end= $moment_now->from($moment_end)->getDays();

        $initial_days = min($research_days+1, $initial_days);
        $day_diff     = 0;

        if ($days_from_now <= 0) // research has not started yet
        {
            $moment_select = new Moment($date_start);
            $date_start = $moment_select->startof('day')->format('Y-m-d');
            $moment_select = new Moment($date_start);
            $date_until = min($date_until, $moment_select->endof('day')->addDays($initial_days-1)->format('Y-m-d'));
        }
        else if ($days_from_end <= 0) // research has already finished
        {
            $moment_select = new Moment($date_until);
            $date_start = $moment_select->startof('day')->addDays(-$initial_days-1)->format('Y-m-d');
            $moment_select = new Moment($date_until);
            $date_until = min($date_until, $moment_select->endof('day')->format('Y-m-d'));

        }
        else // now is within research start end end date
        {
            if ($request->filled('date_start') == false)
            {
                $days_from_sta = max(0, $initial_days - $days_from_now);
                $moment_select = new Moment();
                $date_start    = $moment_select->startof('day')->addDays(-$initial_days-1-$days_from_sta)->format('Y-m-d');
            }

            if ($request->filled('date_until') == false)
            {
                $moment_select = new Moment();
                $date_until    = min($date_until, $moment_select->endof('day')->addDays(-$days_from_sta)->format('Y-m-d'));
            }
        }
        //dd($date_start, $research->start_date, $date_until, $date_last, $research->end_date, $days_from_now, $days_from_end);

        // Cap to research
        if ($date_start < $research->start_date)
            $date_start = $research->start_date;

        if ($date_until > $research->end_date)
            $date_until = $research->end_date;

        $moment_start   = new Moment($date_start);
        $moment_end     = new Moment($date_until);
        $last_data_date = $date_until;

        //dd($date_start, $date_until, $research_days, $days_from_now, $days_from_end, $day_diff, $moment_now, $moment_start, $moment_end);
        
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
        else if (isset($research->default_user_ids))
            $consent_users_selected = $research->default_user_ids;
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
        $assets = ["users"=>0, "user_names"=>[], "devices"=>[], "devices_online"=>0, "device_names"=>[], "devices_offline"=>[], "measurements"=>0, "measurements_imported"=>0, "measurements_total"=>0, "data_completeness"=>0, "data_completeness_online"=>0, "flashlogs"=>0];

        $moment = $moment_start;
        
        // Fill $dates array
        while($moment < $moment_end)
        {
            // make date
            $dates[$moment->format('Y-m-d')] = $assets;
            // next
            $moment = $moment->addDays(1);
        }


        // Fill dates array with counts of data, and select the data for each user by consent
        foreach ($users as $u) 
        {
            $user_id       = $u->id;
            $user          = User::find($user_id);
            $user_consents = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();
            
            if (!isset($user) || !isset($user_consents) || count($user_consents) == 0)
                continue;

            $user_consent_obj  = $user_consents[0];
            $user_consent      = $user_consent_obj->consent;
            $date_curr_consent = $date_start > $user_consent_obj->updated_at ? $date_start : $user_consent_obj->updated_at;
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
            if (isset($user_consent_obj->consent_location_ids)) // Set consent based on specific items
            {
                $loc_array     = explode(',', $user_consent_obj->consent_location_ids);
                $user_apiaries = $user->locations()->withTrashed()->whereIn('id', $loc_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_apiaries = $user->locations()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            if (isset($user_consent_obj->consent_hive_ids)) // Set consent based on specific items
            {
                $hive_array    = explode(',', $user_consent_obj->consent_hive_ids);
                $user_hives    = $user->hives()->withTrashed()->whereIn('id', $hive_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_hives    = $user->hives()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            if (isset($user_consent_obj->consent_sensor_ids)) // Set consent based on specific items
            {
                $device_array     = explode(',', $user_consent_obj->consent_sensor_ids);
                $user_devices_all = $user->devices()->withTrashed()->whereIn('id', $device_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }
            else
            {
                $user_devices_all = $user->devices()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
            }

            $devices_all       = $devices_all->merge($user_devices_all);
            $user_devices      = isset($device_ids) ? $user_devices_all->whereIn('id', $device_ids) : $user_devices_all;
            $devices_show      = $devices_show->merge($user_devices);
            $user_devices_online = isset($device_ids) ? $user_devices_all->whereIn('id', $device_ids)->where('last_message_received', '>=', $date_start) : $user_devices_all->where('last_message_received', '>=', $date_start);
            
            $user_flashlogs    = collect();
            if ($add_flashlogs)
            {
                if (isset($device_ids))
                    $user_flashlogs= FlashLog::where('user_id', $user_id)->whereIn('device_id', $device_ids)->where('log_date_end', '>=', $date_start)->where('log_date_start', '<', $date_until)->orderBy('log_date_start')->get(); // only parsed and checked Flashlogs
                else
                    $user_flashlogs= FlashLog::where('user_id', $user_id)->where('log_date_end', '>=', $date_start)->where('log_date_start', '<', $date_until)->orderBy('log_date_start')->get(); // only parsed and checked Flashlogs
                
                // cap start time to last flashlog creted_at
                if ($until_last_fl && $user_flashlogs->count() > 0)
                {
                    $last_flashlog_upload_date = $user_flashlogs->sortByDesc('created_at')->first()->created_at; // Carbon
                    if ($last_flashlog_upload_date->timestamp < $moment_end->format('U'))
                    {
                        $moment_end = new Moment($last_flashlog_upload_date->timestamp);
                        $last_data_date = $moment_end->format('Y-m-d H:i:s');
                    }
                }
            }



            $user_weather_data = [];
            // $user_sensor_defs  = [];
            // $user_alert_rules  = $user->alert_rules()->where('default_rule', 0)->where('active', 1)->get();
            // $user_alerts       = isset($device_ids) ? $user->alerts()->whereIn('device_id', $device_ids)->where('show', 1)->get() : $user->alerts()->where('show', 1)->get();
            
            //die(print_r($user_devices->toArray()));

            if ($user_devices->count() > 0)
            {
                // get daily counts of sensor measurements
                $points           = [];
                $weather          = [];
                $user_device_keys = [];
                $device_key_looup = [];
                $user_dloc_coords = [];
                $device_key_mpday = []; // key => measurements per day (= 1440 / interval_min)

                // Add sensor data
                foreach ($user_devices as $device) 
                {
                    $device_all_keys    = $device->allKeys();
                    $current_key        = $device->key;
                    // create lookup table to combine former device key log data to current device key
                    foreach ($device_all_keys as $key)
                        $device_key_looup[$key]  = $current_key;
                    
                    // Define measurements per day per device
                    $device_interval_min = isset($device->measurement_interval_min) && $device->measurement_interval_min > 0 ? $device->measurement_interval_min : 15;
                    $device_key_mpday[$current_key] = 1440 / $device_interval_min;

                    $loc = $device->location();
                    if ($loc !== null && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                        $user_dloc_coords[] = '("lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\')';

                    // Add sensor definitions
                    // $sensor_defs = $this->getSensorDefinitions($device, $date_next_consent);
                    // foreach ($sensor_defs as $sdef)
                    //     $user_sensor_defs[] = $sdef;

                    $user_device_keys[] = $device->influxWhereKeys();
                }

                // Get Influx data from device keys (also former) and combine them later to the current device key
                if (count($user_device_keys) > 0)
                {
                    $user_device_keys = '('.implode(' OR ', $user_device_keys).')';

                    try{
                        $this->cacheRequestRate('influx-get');
                        $this->cacheRequestRate('influx-research');
                        $query  = 'SELECT COUNT("w_v") as "count", MIN("bv") as "bv" FROM "sensors" WHERE '.$user_device_keys.' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY "key",time(1d),from_flashlog';
                        //Log::debug($query);
                        $points = $this->client::query($query)->getPoints();
                        
                        // $points = [];
                        // if (isset($point_data->results))
                        //     $points = $point_data->getPoints();

                    } catch (InfluxDB\Exception $e) {
                        Log::error('Research data query error: '.$e->getMessage());
                        // return Response::json('influx-group-by-query-error', 500);
                    }
                    //Log::debug("Points: ".count($points)); 
                    if (count($points) > 0)
                    {
                        // TODO: remove dd
                        //dd($points);
                        foreach ($points as $point)
                        {
                            $date = substr($point['time'],0,10);
                            $key  = isset($device_key_looup[$point['key']]) ? $device_key_looup[$point['key']] : $point['key']; // lookup current device for former keys 
                            $fl   = isset($point['from_flashlog']) && $point['from_flashlog'] === '1' ? true : false;

                            if (isset($dates[$date]))
                            {
                                if (!isset($dates[$date]['devices'][$key]))
                                    $dates[$date]['devices'][$key] = ['points'=>0, 'from_flashlog'=>0, 'total'=>0, 'perc'=>0, 'first_date'=>$date];

                                $meas_per_day = isset($device_key_mpday[$key]) ? $device_key_mpday[$key] : 96;
                                if ($meas_per_day != 96 && $meas_per_day > 0)
                                    $dates[$date]['devices'][$key]['err'] = 'measurement interval: '.(1440/$meas_per_day).' min';

                                $dates[$date]['devices'][$key]['total'] += $point['count'];
                                $dates[$date]['devices'][$key]['bv']   = $point['bv'];
                                $dates[$date]['devices'][$key]['perc'] = min(100, round(100 * $dates[$date]['devices'][$key]['total'] / $meas_per_day)); // data once each 15 min 
                                
                                if ($fl)
                                {
                                    $dates[$date]['devices'][$key]['from_flashlog'] = $point['count'];
                                }
                                else
                                {
                                    $dates[$date]['devices'][$key]['points'] = $point['count'];
                                }
                                    
                            }
                            else
                            {
                                Log::debug("Date not set: ".$date); 
                            }
                        }
                    }
                    else if ($demo) // DEMO
                    {
                        foreach ($dates as $date => $value)
                        {
                            foreach ($user_devices as $d)
                            {
                                $dates[$date]['devices'][$d->key]['first_date'] = $date;
                                $dates[$date]['devices'][$d->key]['points'] = 0;
                                $dates[$date]['devices'][$d->key]['from_flashlog'] = 0;
                                $dates[$date]['devices'][$d->key]['total'] = 0;
                                $dates[$date]['devices'][$d->key]['perc'] = random_int(0, 100);
                                $dates[$date]['devices'][$d->key]['bv']   = random_int(240, 380)/100;

                            }
                        }
                    }

                }

                // // Add weather data
                // $user_location_coord_where = '('.implode(' OR ', $user_dloc_coords).')';
                // if (count($user_dloc_coords) > 0 && isset($date_curr_consent))
                // {
                //     try{
                //         $weather = $this->client::query('SELECT COUNT("temperature") as "count" FROM "weather" WHERE '.$user_location_coord_where.' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$moment_end->format('Y-m-d H:i:s').'\' GROUP BY time(1d)')->getPoints(); // get first weather date
                //     } catch (InfluxDB\Exception $e) {
                //         Log::error('Research weather query error: '.$e->getMessage());
                //         // return Response::json('influx-group-by-query-error', 500);
                //     }
                //     if (count($weather) > 0)
                //     {
                //         foreach ($weather as $point) 
                //             $user_weather_data[substr($point['time'],0,10)] = $point['count'];
                //     }
                // }
            

                // Add possible validated Flashlog data blocks as flashlog_prognose
                if ($user_flashlogs->count() > 0)
                {
                    foreach ($user_flashlogs as $fl)
                    {
                        $fl_dev_key = $fl->getDeviceKeyAttribute();
                        $key        = isset($device_key_looup[$fl_dev_key]) ? $device_key_looup[$fl_dev_key] : null;

                        if ($key && ($fl->validLog() || $invalid_log_prognose))
                        {
                            $valid_data_p = null;
                            $use_data_days= false;

                            if (isset($fl->meta_data['valid_data_points']))
                            {
                                $valid_data_p = $fl->meta_data['valid_data_points'];
                                $use_data_days= true;
                            }

                            $meas_per_day = isset($fl->device) ? $fl->device->getMeasurementsPerDay() : 96;
                            $start_u      = strtotime($fl->log_date_start);
                            $days_total   = $fl->getLogDays(false); // full period
                            $days_log     = $fl->getLogDays();
                            $logpd        = $fl->getLogPerDay();
                            $logperc      = $fl->getTimeLogPercentage($logpd);
                            $logperc_arr  = [];

                            // Walk through data days in Flashlog
                            for ($d=0; $d < $days_total; $d++)
                            { 
                                $date = date('Y-m-d', $start_u + $d * 24 * 3600);
                                
                                if (!$use_data_days || isset($valid_data_p[$date]))
                                {
                                    $logpd_date    = isset($valid_data_p[$date]) ? $valid_data_p[$date] : $logpd; // specific day count, or general count
                                    $logperc       = $fl->getTimeLogPercentage($logpd_date);
                                    $logperc_arr[] = $logperc;
                                    $logp_id       = $fl->id.': '.$logperc;

                                    // Only add logs that have weight and time data
                                    if ($logpd_date > 0 && isset($dates[$date]))
                                    {
                                        if (!isset($dates[$date]['devices'][$key]))
                                        {
                                            $dates[$date]['devices'][$key] = ['points'=>$logpd, 'from_flashlog'=>$logpd, 'flashlog_prognose'=>$logp_id, 'total'=>$logpd, 'perc'=>$logperc, 'first_date'=>$date];
                                        }
                                        else if (isset($dates[$date]['devices'][$key]['total']) && $logpd >= $dates[$date]['devices'][$key]['total']) // replace total with prognose, because Flashlog should contain all
                                        {
                                            $dates[$date]['devices'][$key]['flashlog_prognose'] = $logp_id; // id: %
                                            $dates[$date]['devices'][$key]['total'] = $logpd;
                                            // Calculate new perc
                                            $dates[$date]['devices'][$key]['perc'] = $logperc;
                                        }
                                    }
                                    // Indicate the flashlogs in already available data
                                    if (isset($dates[$date]['devices'][$key]))
                                        $dates[$date]['devices'][$key]['flashlog'] = $fl->id;
                                }
                            }

                            if (count($logperc_arr) > 0)
                                $logperc = min(100, round(array_sum($logperc_arr) / count($logperc_arr)));

                            // Indicate upload date with arrow in table
                            $created_date = substr($fl->created_at, 0, 10);
                            if (isset($dates[$created_date]['devices'][$key]))
                                $dates[$created_date]['devices'][$key]['flashlog_created'] = "$fl->id uploaded at $fl->created_at, containing $days_log days ($fl->log_date_start - $fl->log_date_end) of $logperc% ".($fl->validLog()?'':'NOT YET')." validated weight/time data";

                        }
                        //dd($fl, $dates);
                    }
                }
            } // end device loop

        } // end user loop

        // reverse array for display
        krsort($dates);
        //dd ($dates);

        // Count totals
        $totals    = $assets;
        $data_days = count($dates);

        foreach ($dates as $day => $day_arr) 
        {
            foreach ($day_arr as $asset => $items) 
            {
                // if (is_numeric($items))
                // {
                //     if ($asset == 'inspections' || $asset == 'measurements' || $asset == 'measurements_imported' || $asset == 'measurements_total' || $asset == 'data_completeness' || $asset == 'data_completeness_online' || $asset == 'weather' || $asset == 'samplecodes' || $asset == 'flashlogs' || $asset == 'alerts')
                //         $totals[$asset] += $items;
                //     else
                //         $totals[$asset] = max($totals[$asset], $items);

                //     // if ($asset == 'measurements_total' && $items > 0)
                //     //     $data_days++;
                // }
                if ($asset == 'devices')
                {
                    foreach ($items as $key => $device_data_array)
                    {
                        if (!isset($totals['devices'][$key]))
                            $totals['devices'][$key] = ['total'=>0, 'from_flashlog'=>0, 'points'=>0, 'perc'=>null];

                        $totals['devices'][$key]['total'] += $device_data_array['total'];
                        $totals['devices'][$key]['points'] += $device_data_array['points'];
                        $totals['devices'][$key]['from_flashlog'] += $device_data_array['from_flashlog'];
                        $totals['devices'][$key]['perc'] += $device_data_array['perc'];
                        $totals['devices'][$key]['first_date'] = $device_data_array['first_date'];
                        $totals['devices'][$key]['last_date'] = $last_data_date;
                    }
                }
            }
        }

        // Average data completenes
        $data_completeness_array = [];
        if ($data_days > 0)
        {
            foreach ($totals['devices'] as $key => $totals_data_array)
            {
                $device_data_days         = isset($totals_data_array['first_date']) && isset($totals_data_array['last_date']) ? round((strtotime($totals_data_array['last_date']) - strtotime($totals_data_array['first_date'])) / (24 * 3600)) : $data_days;
                $device_data_completeness = round($totals_data_array['perc'] / $device_data_days, 2);

                $totals['devices'][$key]['data_days'] = $device_data_days;

                // Add to average if > 2%
                if ($device_data_completeness > 2)
                {
                    $totals['devices'][$key]['data_completeness'] = round($device_data_completeness);
                    $data_completeness_array[] = $device_data_completeness;
                }
            }

            $data_completeness_count = count($data_completeness_array);
            $data_completeness       = $data_completeness_count > 0 ? round(array_sum($data_completeness_array) / $data_completeness_count) : '';
        }
        //dd($totals);

        // Make readable devices select list 
        $devices_select = [];
        $devices_sorted = $devices_all->sortBy('user_id')->sortBy('name');
        foreach ($devices_sorted as $d)
            $devices_select[$d->id] = $d->name.' - ('.$d->user->name.')'; 


        // Create device log_file_info (CSV)
        $device_log = null;
        if (in_array($log_device_id, $devices_all->pluck('id')->toArray()))
        {
            //dd($log_device_id);
            $device_log= $devices_all->where('id', '=', $log_device_id)->first();
            
            // Only change 1 log_file_info value
            if (isset($log_device_change) && isset($log_device_value))
            {
                $log_file_info = $device_log->log_file_info;

                if (empty($log_file_info))
                    $log_file_info = [];

                $log_file_info["$log_device_change"] = "$log_device_value";

                $device_log->log_file_info = $log_file_info;
                $device_log->save();
            }
            else // Export CSV and update all log_file_info
            {
                $flashlogs = $device_log->flashlogs()->where('log_date_end', '>=', $date_start)->where('log_date_start', '<', $date_until)->orderBy('log_date_start')->get();
                $data_array= [];
                foreach ($flashlogs as $flashlog)
                {
                    if (isset($flashlog->log_parsed) && ($invalid_log_prognose || $flashlog->validLog()))
                    {
                        $flashlog_parsed_text = $flashlog->getFileContent('log_file_parsed');
                        if (!empty($flashlog_parsed_text))
                        {
                            $flashlog_data_array = json_decode($flashlog_parsed_text, true);
                            $flashlog->addMetaData($flashlog_data_array, true);
                            $data_array = array_merge($data_array, $flashlog_data_array);
                        }
                    }
                }
                if (count($data_array) > 0)
                {
                    $min_unix_ts = strtotime($date_start);
                    $max_unix_ts = strtotime($date_until);

                    // filter time: remove first values < min time, or > max time
                    $delete_until_index = 0;
                    foreach ($data_array as $i => $value_array)
                    {
                        if (isset($value_array['time']))
                        {
                            $data_ts = strtotime($value_array['time']);
                            if ($data_ts < $min_unix_ts || $data_ts > $max_unix_ts)
                                $delete_until_index = $i;
                            else
                                break;
                        }
                    }
                    if ($delete_until_index > 0)
                        $data_array = array_slice($data_array, $delete_until_index+1); // remove before index

                    if (count($data_array) > 0)
                    {
                        $csv_file_name  = "device-$log_device_id-flashlog-data";
                        $save_output    = FlashLog::exportData($data_array, $csv_file_name, true, ',', true, true, $min_unix_ts, $max_unix_ts); // Research data is also exported with , as separator

                        if (isset($save_output['link']))
                        {
                            // Get meta of complete (concatenated) dataset
                            $dummy_flashlog = new FlashLog; // required for addMetaData
                            $meta_data      = $dummy_flashlog->addMetaData($data_array, true, true);
                            
                            $device_log->log_file_info = array_merge(['note'=>$log_device_note, 'created_date'=>date('Y-m-d H:i:s'), 'csv_url'=>$save_output['link']], $meta_data);

                            $device_log->save();
                        }
                    }
                }
            }
        }
        if ($device_log)
            $devices_all->merge($device_log); // add for display of newly added log values


        return view('research.data', compact('research', 'devices_all', 'devices_show', 'data_days', 'dates', 'totals', 'data_completeness', 'data_completeness_count', 'consent_users_select', 'consent_users_selected', 'devices_select', 'device_ids', 'date_start', 'date_until', 'add_flashlogs', 'until_last_fl', 'invalid_log_prognose'));
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
            'default_user_ids.*' => 'nullable|exists:users,id',
            'viewer_ids.*'    => 'nullable|exists:users,id',
            'checklist_ids.*' => 'nullable|exists:checklists,id',
            'visible'       => 'boolean',
            'on_invite_only'=> 'boolean',
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

        if (!isset($requestData['default_user_ids']))
            $requestData['default_user_ids'] = null;

        $research->update($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);
        else
            $research->checklists()->sync([]);

        if (isset($requestData['viewer_ids']))
            $research->viewers()->sync($requestData['viewer_ids']);
        else
            $research->viewers()->sync([]);


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
        if (Storage::disk($disk)->put($filePath, $file_content, ['mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']))
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
                $item->hives()->withTrashed()->count(),
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
                isset($queen) ? $queen->id : '',
                isset($queen) ? $queen->name : '',
                isset($queen) ? $queen->color : '',
                isset($queen) ? $queen->birth_date : '',
                isset($queen) ? $queen->fertilized : '',
                isset($queen) ? $queen->clipped : '',
                $item->getBroodlayersAttribute(),
                $item->getHoneylayersAttribute(),
                $item->frames()->count(),
                $item->created_at,
                $item->deleted_at
            ];
        });
    }

    private function getSampleCodes($user_id, $samplecodes, $date_start=null, $date_until=null)
    {
        return $samplecodes->where('sample_date', '<=', $date_until)->sortByDesc('sample_date')->sortByDesc('sample_date')->sortBy('hive_id')->map(function($item) use ($user_id)
        {
            $hive      = $item->hive;
            $hive_name = isset($hive) ? $hive->name : '';
            $loc_id    = '';
            $loc_name  = '';
            $loc_cc    = '';

            if (isset($hive->location_id))
            {
                $loc_id    = $hive->location_id;
                $loc_name  = $hive->location;
                $loc       = Location::withTrashed()->find($hive->location_id);
                $loc_cc    = strtoupper($loc->country_code);
            }

            return [
                $user_id,
                $item->sample_code, 
                $item->sample_date,
                $loc_cc,
                $loc_id,
                $loc_name,
                $item->hive_id,
                $hive_name,
                $item->queen_id,
                $item->smple_note,
                $item->test,
                $item->test_date,
                $item->test_result,
                $item->test_lab_name,
                $item->created_at,
                $item->updated_at
            ];
        });
    }

    private function getDevice($user_id, $item)
    {
        $log_data_valid     = isset($item->log_file_info['valid']) && boolval($item->log_file_info['valid']) ? true : false;
        $log_data_state     = $log_data_valid && isset($item->log_file_info['log_date_end']) ? "Validated until: ".$item->log_file_info['log_date_end'] : ($item->flashlogs()->count() == 0 ? "No flashlogs uploaded yet" : "Validation ongoing");
        $log_data_note      = isset($item->log_file_info['note']) ? $item->log_file_info['note'] : null;
        $log_data_csv       = isset($item->log_file_info['csv_url']) ? $item->log_file_info['csv_url'] : null;
        $log_data_info      = isset($item->log_file_info) ? CalculationModel::arrayToString($item->log_file_info, ' | ', '', ['csv_url','valid_data_points','note']) : null;

        isset($item->log_file_info['note']) ? $item->log_file_info['note'] : null;
        return [
            $user_id,
            $item->id, 
            $item->name, 
            $item->hive_id,
            $item->location_id,
            $item->getTypeAttribute(),
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
            $item->rtc,
            $log_data_valid,
            $log_data_state,
            $log_data_csv,
            $log_data_note,
            $log_data_info,
            $item->last_message_received,
            $item->datetime,
            $item->datetime_offset_sec,
            $item->sensorDefinitions()->count(),
            $item->created_at,
            $item->deleted_at
        ];
    }

    private function getSensorDefinitions($device, $date_until=null)
    {
        return $device->sensorDefinitions()->where('created_at', '<=', $date_until)->orderBy('created_at')->get()->map(function($item)
        {
            return [
                $item->name,
                $item->device_id, 
                $item->inside,
                $item->offset,
                $item->multiplier,
                $item->input_abbr,
                $item->output_abbr,
                $item->created_at,
                $item->deleted_at
            ];
        });
    }

    private function getFlashlogs($user_id, $flashlogs, $date_start=null, $date_until=null)
    {
        return $flashlogs->where('created_at', '<=', $date_until)->sortBy('device_id')->sortByDesc('created_at')->map(function($item) use ($user_id)
        {
            $meta_data = isset($item->meta_data) ? CalculationModel::arrayToString($item->meta_data, ' | ', '', ['valid_data_points']) : null;
            
            return [
                $user_id,
                $item->device_id, 
                $item->created_at,
                $item->hive_id,
                $item->log_messages,
                $item->log_saved,
                $item->log_parsed,
                $item->log_erased,
                $item->log_has_timestamps,
                $item->time_percentage,
                $item->bytes_received,
                $item->persisted_days,
                $item->persisted_measurements,
                $item->persisted_block_ids,
                $item->log_date_start,
                $item->log_date_end,
                $item->logs_per_day,
                $item->getTimeLogPercentage(),
                $item->validLog(),
                $meta_data,
                $item->csv_url,
                $item->log_file,
                $item->log_file_stripped,
                $item->log_file_parsed,
                $item->deleted_at
            ];
        });
    }

    /*
    $spreadsheet_array['Alert rules'] = [
        [
            'User_id',
            'Date',
            'Name',
            'Formula',
            'Calculation minutes',
            'Last calculated',
            'Last evaluated'
        ]
    ];
    */
    private function getAlertRules($user_id, $alert_rules, $date_start=null, $date_until=null)
    {
        return $alert_rules->where('created_at', '<=', $date_until)->sortByDesc('created_at')->map(function($item) use ($user_id)
        {
            return [
                $user_id,
                $item->created_at,
                $item->name, 
                $item->readableFunction(),
                $item->calculation_minutes,
                $item->last_calculated_at,
                $item->last_evaluated_at
            ];
        });
    }

    /*
    $spreadsheet_array['Alerts'] = [
        [
            'User_id',
            'Device_id',
            'Location_id',
            'Hive_id',
            'Date',
            'Function',
            'Value',
            'Count'
        ]
    ];
    */
    private function getAlerts($user_id, $alerts, $date_start=null, $date_until=null)
    {
        return $alerts->where('created_at', '>=', $date_start)->where('created_at', '<=', $date_until)->sortByDesc('created_at')->sortBy('hive_id')->map(function($item) use ($user_id)
        {
            return [
                $user_id,
                $item->device_id, 
                $item->location_id,
                $item->hive_id,
                $item->created_at,
                $item->alert_function,
                $item->alert_value,
                $item->count
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
            $locationId = ($inspection->locations()->withTrashed()->count() > 0 ? $inspection->locations()->withTrashed()->first()->id : ($inspection->hives()->withTrashed()->count() > 0 ? $inspection->hives()->withTrashed()->first()->location()->first()->id : ''));
            
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
                'user_id' => $user_id,
                'inspection_id' => $inspection->id,
                __('export.created_at') => $inspection->created_at,
                __('export.hive') => $inspection->hives()->withTrashed()->count() > 0 ? $inspection->hives()->withTrashed()->first()->id : '', 
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
        
        if ($database == 'sensors')
        {
            if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
                $names = $measurements;
            else
                $names = $this->output_sensors;

            $queryList = Device::getAvailableSensorNamesNoCache($names, $where, $database);
            
            if (isset($queryList) && gettype($queryList) == 'array' && count($queryList) > 0)
                $groupBySelect = implode(', ', $queryList);
            else 
                $groupBySelect = '"'.implode('","',$names).'"';

            $query = 'SELECT '.$groupBySelect.',"from_flashlog" FROM "'.$database.'" WHERE '.$where;
        }
        else // i.e. weather data
        {
            if ($measurements == null || $measurements == '' || $measurements === '*')
                $sensor_measurements = '*';
            else
                $sensor_measurements = $measurements;

            $query = 'SELECT '.$sensor_measurements.' FROM "'.$database.'" WHERE '.$where;
        }
        
        try{
            $data   = $this->client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return null;
        }

        if (count($data) == 0)
            return null;

        $csv_file = $data;

        //format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";

        $csv_sens = array_keys($data[0]);
        $csv_head = [];
        foreach ($csv_sens as $sensor_name) 
        {
            if ($sensor_name == 'from_flashlog')
            {
                $csv_head[] = 'Imported from device flash log';
            }
            else
            {
                $meas       = Measurement::where('abbreviation', $sensor_name)->first();
                $csv_head[] = $meas ? $meas->pq_name_unit().' ('.$sensor_name.')' : $sensor_name;
            }
        }
        $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

        // format CSV file body
        $csv_body = [];
        foreach ($data as $sensor_values) 
        {
            $csv_body[] = implode($separator, $sensor_values);
        }
        $csv_file = $csv_head.implode("\r\n", $csv_body);

        // return the CSV file content in a file on disk
        $filePath = 'exports/'.$fileName;
        $disk     = env('EXPORT_STORAGE', 'public');

        if (Storage::disk($disk)->put($filePath, $csv_file, ['mimetype' => 'text/csv']))
            return Storage::disk($disk)->url($filePath);

        return null;
    }

}
