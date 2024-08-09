<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Response;
use Storage;
use Mail;
use Cache;
use App\Device;
use App\Setting;
use App\Hive;
use App\User;
use App\Category;
use App\Inspection;
use App\InspectionItem;
use App\Measurement;
use App\Models\FlashLog;
use App\Mail\DataExport;
use App\Exports\HiveExport;
use Moment\Moment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Validator;

/**
 * @group Api\ExportController
 * Export all data to an Excel file by email (GDPR)
 * @authenticated
 */
class ExportController extends Controller
{
    protected $valid_sensors  = [];
    protected $output_sensors = [];
    protected $output_weather = [];

    public function __construct()
    {
        $this->valid_sensors  = Measurement::getValidMeasurements();
        $this->output_sensors = Measurement::getValidMeasurements(true);
        $this->output_weather = Measurement::getValidMeasurements(true, true);
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


    /**
    api/export GET
    Generate an Excel file with all user data and send by e-mail or as download link
    @authenticated
    @bodyParam groupdata boolean 1: also include group data in export. 0, of not filled: only export my own data. Default: 0. Example: 0
    @bodyParam sensordata boolean 1: also include measurement data in export. 0, of not filled: do not add measurement data. Default: set in environment settings. Example: 0
    @bodyParam link boolean 1: Save the export to a file and provide the link, 0, or not filled means: send the Excel as an attachment to an email to the user's email address. Default: 0. Example: 1
    **/
    public function all(Request $request)
    {
        $fileName     = strtolower(env('APP_NAME')).'-export-user-'.$request->user()->id;
        $user         = $request->user();

        $sensor_urls  = [];
        $download_url = null;
        $download     = true;
        $sensordata   = boolval($request->input('sensordata', env('EXPORT_INFLUX_SENSORDATA', false)));
        $group_data   = boolval($request->input('groupdata', false)); // include group data
        $return_link  = boolval($request->input('link', false));

        $date_start        = $user->created_at;
        $date_until        = date('Y-m-d H:i:s');
        $date_user_created = $date_start;
        $date_until_today  = $date_until;

        // Fill dates array
        $assets = ["users"=>0, "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "measurements"=>0, "weather"=>0, "flashlogs"=>0];

        $spreadsheet_array = [];

        if ($download)
        {
            // Fill export array
            
            // Define header rows of tabs
            $spreadsheet_array[__('export.users')] = [
                           [__('export.name'),
                            __('export.email'),
                            __('export.avatar'),
                            __('export.created_at'),
                            __('export.updated_at'),
                            __('export.last_login')]
                        ];

            // add user data to sheet data arrays
            //foreach ($users as $user) 
                $spreadsheet_array[__('export.users')][] = $this->getUser($user);


            $spreadsheet_array[__('export.locations')] = [
                           ['Location_id',
                            __('export.owner'),
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
                           ['Hive_id',
                            __('export.owner'),
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
                           ['Inspection_id',
                            __('export.owner'),
                            __('export.created_at'),
                            'Hive_id',
                            __('export.hive_name'),
                            'Location_id',
                            __('export.impression'),
                            __('export.attention'),
                            __('export.reminder'),
                            __('export.reminder_date'),
                            __('export.notes')]
                        ];

            $spreadsheet_array[__('export.devices')] = [
                           ['Device_id',
                            __('export.owner'),
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
                            ['Device_id',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            if ($sensordata)
                $spreadsheet_array['Device Flashlogs'] = [
                            ['Device_id',
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

            if ($sensordata)
                $spreadsheet_array['Weather data'] = [
                            ['Device_id',
                            'Date from',
                            'Date to',
                            'Data file']
                        ];

            // Add item names to header row of inspections
            // first combine all user's itemnames
            $item_ancs  = [];
            $item_names = [];

            $u_inspections = $group_data ? $user->allInspections()->get() : $user->inspections()->get();
            $ins = Inspection::item_names($u_inspections);
            foreach ($ins as $in) 
            {
                $name = $in['anc'].$in['name'];
                if (!in_array($name, $item_ancs))
                {
                    $item_ancs[]  = $name;
                    $item_names[] = $in; 
                }
            }

            foreach ($item_ancs as $name) 
                $spreadsheet_array[__('export.inspections')][0][] = $name;

            $spreadsheet_array[__('export.inspections')][0][] = __('export.deleted_at');


            // Fill dates array with counts of data, and select the data for each user by consent
            $u = $user; 
            $user_id = $u->id;
            
            // add user data
            $u_apiaries        = $group_data ? $user->allLocations() : $user->locations();
            $user_apiaries     = $u_apiaries->where('created_at', '<', $date_until)->orderBy('created_at')->get();

            $u_hives           = $group_data ? $user->allHives() : $user->hives();
            $user_hives        = $u_hives->where('created_at', '<', $date_until)->orderBy('created_at')->get();

            $u_devices         = $group_data ? $user->allDevices() : $user->devices();
            $user_devices      = $u_devices->where('created_at', '<', $date_until)->orderBy('created_at')->get();

            $u_flashlogs       = $group_data ? $user->allFlashlogs() : $user->flashlogs();
            $user_flashlogs    = $u_flashlogs->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();

            $user_measurements = [];
            $user_weather_data = [];
            
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
                    if ($loc && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                        $user_dloc_coords[] = '("lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\')';
                }
                
                $user_device_keys = '('.implode(' OR ', $user_device_keys).')';

                try{
                    $this->cacheRequestRate('influx-get');
                    $this->cacheRequestRate('influx-export');
                    $points = $this->client::query('SELECT COUNT("bv") as "count" FROM "sensors" WHERE '.$user_device_keys.' AND time >= \''.$date_user_created.'\' AND time <= \''.$date_until_today.'\' GROUP BY time(1d)')->getPoints();
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
                if (count($user_dloc_coords) > 0 && isset($date_user_created))
                {
                    try{
                        $weather = $this->client::query('SELECT COUNT("temperature") as "count" FROM "weather" WHERE '.$user_location_coord_where.' AND time >= \''.$date_user_created.'\' AND time <= \''.$date_until_today.'\' GROUP BY time(1d)')->getPoints(); // get first weather date
                    } catch (InfluxDB\Exception $e) {
                        // return Response::json('influx-group-by-query-error', 500);
                    }
                    if (count($weather) > 0)
                    {
                        foreach ($weather as $point) 
                            $user_weather_data[substr($point['time'],0,10)] = $point['count'];
                    }
                }
            }


                
            // Fill objects for consent period
            $locas = $this->getLocations($user_id, $user_apiaries, $date_user_created, $date_until_today);
            foreach ($locas as $loca)
                $spreadsheet_array[__('export.locations')][] = $loca;

            $hives = $this->getHives($user_id, $user_hives, $date_user_created, $date_until_today);
            foreach ($hives as $hive)
                $spreadsheet_array[__('export.hives')][] = $hive;

            $insps = $this->getInspections($user_id, $hive_inspections, $item_ancs, $date_user_created, $date_until_today);
            foreach ($insps as $insp)
                $spreadsheet_array[__('export.inspections')][] = $insp;


            if ($sensordata && $user_devices->count() > 0)
            {
                
                $flash = $this->getFlashlogs($user_id, $user_flashlogs, $date_user_created, $date_until_today);
                foreach ($flash as $fla)
                    $spreadsheet_array['Device Flashlogs'][] = $fla;
                
                
                foreach ($user_devices as $device)
                {
                    // Add device to spreadsheet
                    if ($device->created_at < $date_until_today)
                    {
                        $spreadsheet_array[__('export.devices')][] = $this->getDevice($user_id, $device);
                    
                        // Export data to file per device / period
                        $where    = $device->influxWhereKeys().' AND time >= \''.$date_user_created.'\' AND time <= \''.$date_until_today.'\'';
                        $dataName = strtolower(env('APP_NAME')).'-export-device-id-'.$device->id.'-sensor-data-'.substr($date_user_created,0,10).'-'.substr($date_until_today,0,10).'-'.Str::random(10).'.csv';
                        $filePath = $this->exportCsvFromInflux($where, $dataName, '*', 'sensors');
                        if ($filePath)
                        {
                            $spreadsheet_array['Sensor data'][] = [$device->id, $date_user_created, $date_until_today, $filePath];
                            $sensor_urls[$dataName] = $filePath;
                        }

                        // Export data to file per device location / period
                        $loc = $device->location();
                        if ($loc && isset($loc->coordinate_lat) && isset($loc->coordinate_lon)) 
                        {
                            $where    = '"lat" = \''.$loc->coordinate_lat.'\' AND "lon" = \''.$loc->coordinate_lon.'\' AND time >= \''.$date_user_created.'\' AND time <= \''.$date_until_today.'\'';
                            $dataName = strtolower(env('APP_NAME')).'-export-device-id-'.$device->id.'-weather-data-'.substr($date_user_created,0,10).'-'.substr($date_until_today,0,10).'-'.Str::random(10).'.csv';
                            $filePath = $this->exportCsvFromInflux($where, $dataName, '*', 'weather');
                            if ($filePath)
                            {
                                $spreadsheet_array['Weather data'][] = [$device->id, $date_user_created, $date_until_today, $filePath];
                                $sensor_urls[$dataName] = $filePath;
                            }
                        }
                    }
                }
            }

            $export_result = $this->export($spreadsheet_array, $fileName, $date_start, $date_until, $group_data);

        }

        // send e-mail with attachment
        if ($export_result && isset($export_result['path']))
        {
            //$path = $file->storagePath.'/'.$fileName.'.'.$fileType;
            $path = $export_result['path'];
            $link = $export_result['url'];
            $disk = env('EXPORT_STORAGE', 'public');
            
            if ($return_link === false)
            {
                Mail::to($user->email)->send(new DataExport($user, $disk, $path));
                $del = Storage::disk($disk)->delete($path);
                return response()->json(['link'=>null, 'del'=>$del, 'delf'=>$path, 'email'=>1],200);
            }
            else
            {
                return response()->json(['link'=>$link, 'email'=>0],200);
            }
        }
        else
        {
            return response()->json(['error'=>'export-error'],404);
        }
    }

    private function export($spreadsheetArray, $fileName='export', $date_start='Account created', $date_until='Today', $group_data=false)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $title = 'BEEP account data export';
        $group = $group_data ? __('export.incl_group') : '';

        // Set meta data
        $sheet->setTitle('Meta data');
        $sheet->setCellValue('A1', $title.$group);
        $sheet->setCellValue('A3', env('APP_NAME').' data export');
        $sheet->setCellValue('C3', date('Y-m-d H:i:s'));
        $sheet->setCellValue('A4', 'Start date');
        $sheet->setCellValue('C4', $date_start);
        $sheet->setCellValue('A5', 'End date');
        $sheet->setCellValue('C5', $date_until);
        $sheet->setCellValue('A6', 'Tabs');
        $sheet->setCellValue('C6', count($spreadsheetArray));

        $row = 8;
        foreach ($spreadsheetArray as $title => $data)
        {
            $row_title = $row > 8 ? $title.$group : $title;
            $sheet->setCellValue('A'.$row, $row_title);
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
        $fileName = $fileName.'-'.Str::random(20);
        $filePath = 'exports/'.$fileName.'.xlsx';
        $writer = new Xlsx($spreadsheet);
        //$writer->setOffice2003Compatibility(true);

        ob_start();
        $writer->save('php://output');
        $file_content = ob_get_contents();
        ob_end_clean();

        $disk = env('EXPORT_STORAGE', 'public');
        if (Storage::disk($disk)->put($filePath, $file_content, ['mimetype' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']))
            return ['url'=>Storage::disk($disk)->url($filePath), 'path'=>$filePath];

        return null;
    }

    
    private function getUser(User $user)
    {
        return [
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
                $item->id,
                $item->owner,
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
                $item->id, 
                $item->owner,
                $item->name,
                $item->type,
                $item->location_id,
                $item->color,
                isset($queen) ? $queen->name : '',
                isset($queen) ? $queen->color : '',
                isset($queen) ? $queen->birth_date : '',
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
            $item->id, 
            $item->owner,
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
                'inspection_id' => $inspection->id,
                __('export.owner') => $inspection->owner,
                __('export.created_at') => $inspection->created_at,
                __('export.hive') => $inspection->hives()->count() > 0 ? $inspection->hives()->first()->id : '', 
                __('export.hive_name') => $inspection->hives()->count() > 0 ? $inspection->hives()->first()->name : '', 
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


    private function exportCsvFromInflux($where, $fileName='device-export-', $measurements='*', $database='sensors', $separator=',')
    {
        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        $groupByTime = env('EXPORT_INFLUX_GROUPBY_TIME', '6h');
        $groupBy= 'GROUP BY time('.$groupByTime.') fill(none)';

        if ($database == 'sensors')
        {
            if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
                $names = $measurements;
            else
                $names = $this->output_sensors;
            
            $queryList = Device::getAvailableSensorNamesNoCache($names, $where, $database);
            if (count($queryList) == 0)
                $queryList = $names;
            
            foreach ($queryList as $i => $name) 
                $queryList[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';

            $groupBySelect = implode(', ', $queryList);

            $query = 'SELECT '.$groupBySelect.' FROM "'.$database.'" WHERE '.$where.' '.$groupBy;
        }
        else // i.e. weather data
        {
            if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
                $names = $measurements;
            else
                $names = $this->output_weather;

            foreach ($names as $i => $name) 
                $names[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';

            $groupBySelectWeather = implode(', ', $names);

            $query = 'SELECT '.$groupBySelectWeather.' FROM "'.$database.'" WHERE '.$where.' '.$groupBy;
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
            $meas       = Measurement::where('abbreviation', $sensor_name)->first();
            $csv_head[] = $meas ? $meas->pq_name_unit().' ('.$sensor_name.')' : $sensor_name;
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



    /**
    api/export/csv POST
    Generate a CSV measurement data export from InfluxDB. Make sure not to load a too large timespan (i.e. > 30 days), because the call will not succeed due to memory overload.
    @authenticated
    @bodyParam device_id required Device id to download data from
    @bodyParam start date required Date for start of data export. Example: 2020-05-27 16:16
    @bodyParam end date required Date for end of data export. Example: 2020-05-30 00:00
    @bodyParam separator string Symbol that should be used to separate columns in CSV file. Example: ;
    @bodyParam measurements string Comma separated list of measurement types to load. If you want a lot of data (i.e. > 30 days), make sure not to load more than one measurement. Example: 'am2315_t,am2315_h,mhz_co2'
    @bodyParam link boolean filled means: save the export to a file and provide the link, not filled means: output a text/html header with text containing the .csv content. Example:
    **/
    public function generate_csv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:sensors,id',
            'start'     => 'required|date',
            'end'       => 'required|date',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);
        
        $device_id    = $request->input('device_id');
        $start        = $request->input('start');
        $end          = $request->input('end');
        $separator    = $request->input('separator', ';');

        $measurements = $request->input('measurements', '*');
        $return_link  = boolval($request->input('link', false));
        $device       = $request->user()->allDevices()->find($device_id);

        if ($device == null)
            return Response::json('invalid-user-device', 500);

        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        
        if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
            $names = $measurements;
        else
            $names = $this->output_sensors;
        
        $whereDeviceTime = $device->influxWhereKeys().' AND time >= \''.$start.'\' AND time < \''.$end.'\'';
        $queryList       = Device::getAvailableSensorNamesNoCache($names, $whereDeviceTime); // ($names, $table, $where, $limit='', $output_sensors_only=true)

        if (isset($queryList) && gettype($queryList) == 'array' && count($queryList) > 0)
                $groupBySelect = implode(', ', $queryList);
            else 
                $groupBySelect = '"'.implode('","',$names).'"';

        $query = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE '.$whereDeviceTime;
        
        try{
            $this->cacheRequestRate('influx-get');
            $this->cacheRequestRate('influx-csv');
            $data   = $this->client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return Response::json('influx-query-error: '.$query, 500);
        }

        if (count($data) == 0)
            return Response::json('influx-query-empty', 500);

        // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";
        $csv_sens = array_keys($data[0]);
        $csv_head = [];
        foreach ($csv_sens as $sensor_name) 
        {
            $meas       = Measurement::where('abbreviation', $sensor_name)->first();
            $col_head   = $meas ? $meas->pq_name_unit() : $sensor_name;
            if (in_array($col_head, $csv_head) && $col_head != $sensor_name) // two similar heads, so add $sensor_name
                $col_head .= ' - '.$sensor_name;

            $csv_head[] = $col_head;
        }
        $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

        // format CSV file body
        $csv_body = [];
        foreach ($data as $sensor_values) 
        {
            $csv_body[] = implode($separator, $sensor_values);
        }
        $csv_file = $csv_head.implode("\r\n", $csv_body);

        if ($return_link)
        {
            // return the CSV file content in a file on disk
            $disk     = env('EXPORT_STORAGE', 'public');
            $filePath = 'exports/beep-export-user-'.$request->user()->id.'-device-'.$device->name.'-'.$start.'-'.$end.'-'.Str::random(20).'.csv';
            $filePath = str_replace(' ', '', $filePath);
            if (Storage::disk($disk)->put($filePath, $csv_file, ['mimetype' => 'text/csv']))
                return Response::json(['link'=>Storage::disk($disk)->url($filePath)]);
            else
                return Response::json(['message'=>'export_not_saved'], 500);
        }

        return response($csv_file)->header('Content-Type', 'text/html; charset=UTF-8');
    }

}
