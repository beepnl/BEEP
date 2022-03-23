<?php

namespace App\Http\Controllers\Api;

use App\Measurement;
use App\Device;
use App\Models\FlashLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Storage;
use Cache;
use Auth;
use Str;

/**
 * @group Api\FlashLogController
 */
class FlashLogController extends Controller
{
    
    protected $precision      = 's';
    protected $timeFormat     = 'Y-m-d H:i:s';
    protected $maxDataPoints  = 5000;

    public function __construct()
    {
        $this->client         = new \Influx;
        $this->valid_sensors  = Measurement::getValidMeasurements();
    }

    private function cacheRequestRate($name, $amount=1)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count', $amount);
        else
            Cache::put($name.'-count', $amount);

    }


    private function getUserFlashlogs($id=null)
    {
        if (Auth::check() === false)
            return [];

        if (Auth::user()->hasRole('superadmin'))
        {
            if (isset($id))
                return Flashlog::find($id);

            return Flashlog::orderByDesc('id')->get();
        }
        else
        {
            if (Auth::user()->researchesOwned->count() > 0) // research owners: show flashlogs from all owned researches default_user_ids
            {
                // get own + consented users->devices->flashlogs
                $user_ids = [Auth::user()->id];
                foreach (Auth::user()->allResearches()->get() as $research) 
                {
                    if (isset($research->default_user_ids))
                        $user_ids = array_merge($user_ids, $research->default_user_ids);
                 
                }
                $user_ids = array_unique($user_ids);

                if (isset($id))
                    return Flashlog::whereIn('user_id', $user_ids)->find($id);

                return Flashlog::whereIn('user_id', $user_ids)->orderByDesc('id')->get();
            }
            else // normal users, show your own flashlogs
            {
                if (isset($id))
                    return Auth::user()->flashlogs()->find($id);

                return Auth::user()->flashlogs()->orderByDesc('id')->get();
            }
        }
    }

    /**
     * api/flashlogs GET
     * Provide a list of the available flashlogs
     * @authenticated
     */
    public function index(Request $request)
    {
        return response()->json($this->getUserFlashlogs());
    }

    /**
     * api/flashlogs/{id} GET
     * Provide the contents of the log_file_parsed property of the flashlog
     * @authenticated
     * @queryParam id integer required Flashlog ID to parse
     * @bodyParam matches_min integer Flashlog minimum amount of inline measurements that should be matched. Default: 5. Example: 2  
     * @bodyParam match_props integer Flashlog minimum amount of measurement properties that should match. Default: 9. Example: 7  
     * @bodyParam db_records integer Flashlog minimum amount of inline measurements that should be matched. Default: 80. Example: 15 
     * @bodyParam block_id integer Flashlog block index to get both Flashlog as database data from. Example: 1
     * @bodyParam block_data_index integer Flashlog data index to get both Flashlog as database data from. Example: 0
     * @bodyParam data_minutes integer Flashlog data amount of minutes to show data from. Default: 10080 (1 week).
     * @bodyParam from_cache boolean get Flashlog parse result from cache (24 hours). Default: true. Example: false
     * @bodyParam save_result boolean Flashlog save the parsed result as new log_file_parsed. Default: false. Example: false
     * @bodyParam csv integer Save the Flashlog block_id data as a CSV file (1) and return a link. Default: 0. Example: 0
     * @bodyParam json integer Save the Flashlog block_id data as a JSON file (1) and return a link. Default: 0. Example: 0
     */
    public function show(Request $request, $id)
    {
        $out = $this->parse($request, $id);
        return response()->json($out, isset($out['error']) ? 500 : 200);
    }


    /**
     * api/flashlogs/{id} POST
     * Fill the missing database values with Flashlog values that match
     * @authenticated
     * @queryParam id integer required Flashlog ID to parse
     * @bodyParam matches_min integer Flashlog minimum amount of inline measurements that should be matched. Default: 5. Example: 2  
     * @bodyParam match_props integer Flashlog minimum amount of measurement properties that should match. Default: 9. Example: 7  
     * @bodyParam db_records integer Flashlog minimum amount of inline measurements that should be matched. Default: 80. Example: 15 
     * @bodyParam block_id integer Flashlog block index to get both Flashlog as database data from. Example: 1
     * @bodyParam from_cache boolean get Flashlog parse result from cache (24 hours). Default: true. Example: false
     * @bodyParam save_result boolean Flashlog save the parsed result as new log_file_parsed. Default: false. Example: false
     */
    public function persist(Request $request, $id)
    {
        $out = $this->parse($request, $id, true);
        return response()->json($out, isset($out['error']) ? 500 : 200);
    }

    /**
     * api/flashlogs/{id} DELETE
     * Delete a block of data (block_id filled), or the whole Flashlog file
     * @authenticated
     * @queryParam id integer required Flashlog ID to delete the complete Flashlog file
     * @bodyParam block_id integer Flashlog block index to delete (only the) previously persisted data from the database
     */
    public function delete(Request $request, $id)
    {
        if ($request->filled('block_id'))
        {
            $out = $this->parse($request, $id, false, true);
            return response()->json($out, isset($out['error']) ? 500 : 200);
        }
        else
        {
            return response()->json(["error"=>"deleting complete flashlog file ($id) not yet supported"], 500);
            //return $this->destroy($request, $id);
        }
    }

    // requires at least ['name'=>value] to be set
    private function storeInfluxDataArrays($data_arrays, $device)
    {
        // store posted data
        $client      = $this->client;
        $points      = [];
        $sensor_tags = ['key' => strtolower($device->key), 'device_name' => $device->name, 'hardware_id' => strtolower($device->hardware_id), 'user_id' => $device->user_id, 'from_flashlog'=>1]; 
        
        foreach ($data_arrays as $data_array) 
        {
            if (isset($data_array['time']))
            {
                $time  = strtotime($data_array['time']);
                $array = array_intersect_key($data_array, $this->valid_sensors);

                foreach ($array as $key => $value) {
                    $array[$key] = floatval($value);
                }

                if (count($array) > 0)
                {
                    array_push($points, 
                        new \InfluxDB\Point(
                            'sensors',             // name of the measurement
                            null,                  // the measurement value
                            $sensor_tags,          // optional tags
                            $array,                // key value pairs
                            $time                  // Time precision has to be set to InfluxDB\Database::PRECISION_SECONDS!
                        )
                    );
                }
            }
        }
        //die(print_r($points));
        $stored = false;
        if (count($points) > 0)
        {
            try
            {
                $this->cacheRequestRate('influx-write');
                $stored = $client::writePoints($points, \InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                Log::error($e->getMessage());
                //die(print_r($e->getMessage()));
            }
        }
        return $stored;
    }

    private function cleanFlashlogItem($data_array, $unset_time=true)
    {
        unset(
            $data_array['payload_hex'],
            $data_array['pl'],
            $data_array['len'],
            $data_array['vcc'],
            $data_array['pl_bytes'],
            $data_array['beep_base'],
            $data_array['weight_sensor_amount'],
            $data_array['ds18b20_sensor_amount'],
            $data_array['port'],
            $data_array['bat_perc'],
            $data_array['fft_bin_amount'],
            $data_array['fft_start_bin'],
            $data_array['fft_stop_bin']
        );
        
        if ($unset_time)
            unset(
                $data_array['i'],
                $data_array['minute_interval'],
                $data_array['minute']
            );

        return $data_array;
    }

    private function matchPercentage($array1, $array2, $match_props=9)
    {
        //$matches       = [];
        $secDiff       = [];
        $match_count   = 0;
        $array2_index  = 0;
        $array1_length = count($array1);
        $array2_length = count($array2);
        $array_min_len = min($array1_length, $array2_length);
        $errors        = [];

        if ($array1_length > 0 && $array2_length > 0)
        {
            for ($i=0; $i < $array1_length; $i++) 
            {
                $f = (array)$array1[$i];
                
                for ($j=$array2_index; $j < $array2_length; $j++) 
                {   
                    $d           = $array2[$j];
                    $d_time      = $d['time'];
                    unset($d['time']); // remove for count
                    $d_val_count = count($d);
                    
                    if ($d_val_count < $match_props)
                    {
                        $array2_index = $j;
                        continue;
                    }

                    if (isset($f['bv']) && isset($d['bv']) && $f['bv'] == $d['bv']) // first fast check
                    {
                        $match = array_intersect_assoc($d, $f);

                        if ($match !== null && count($match) >= $d_val_count-1)
                        {
                            $should_match = ['weight_kg','t_i','t_0','t_1'];
                            $match_ok     = true;
                            foreach ($should_match as $m_key)
                            {
                                // reject match, because weight_kg, t_i, t_0, or t_1 does not match
                                if (isset($d[$m_key]) && isset($f[$m_key]) && round($d[$m_key], 1) !== round($f[$m_key], 1))
                                {
                                    if ($m_key == 'weight_kg' && $d[$m_key] > 200 && $f[$m_key] < 200) // can still be ok, because uncalibrated db values can be replaced
                                    {
                                        if (in_array($m_key.'_uncalibrated', $errors) == false)
                                            $errors[] = $m_key.'_uncalibrated';
                                    }
                                    else
                                    {
                                        $match_ok = false;
                                        
                                        if (in_array($m_key.'_different', $errors) == false)
                                            $errors[] = $m_key.'_different';
                                    }
                                }
                            }

                            if ($match_ok)
                            {
                                $d['time'] = $d_time; // put back tima
                                //$matches[] = ['d'=>$d, 'f'=>$f, 'm'=>$match];
                                $secDiff[] = strtotime($f['time']) - strtotime($d['time']);
                                $match_count++;
                            }

                            $array2_index = $j;
                            continue 2; // next foreach loop to continue with the next database item
                            
                        }
                    }
                }
            }
        }
        $secDiffAvg = count($secDiff) > 0 ? round(array_sum($secDiff)/count($secDiff)) : null;
        $percMatch  = $array_min_len > 0 ? round(100 * ($match_count / $array_min_len), 1): 0;
        //die(print_r([$percMatch, $secDiffAvg, $matches]));
        return ['sec_diff'=>$secDiffAvg, 'perc_match'=>$percMatch, 'errors'=>implode(', ', $errors)];
    }

    private function exportData($data, $name, $csv=true, $separator=';')
    {
        $disk     = env('EXPORT_STORAGE', 'public');
        $file_ext = $csv ? '.csv' : '.json';
        $file_mime= $csv ? 'text/csv' : 'application/json';
        $filePath = 'exports/flashlog/beep-base-log-export-'.$name.'-'.Str::random(20).$file_ext;
        $filePath = str_replace(' ', '', $filePath);
        $fileBody = '';

        if ($data && gettype($data) == 'array' && count($data) > 0)
        {
            if ($csv)
            {
                // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
                $header_item = null;
                for ($i=0; $i < count($data); $i++) 
                { 
                    $data_item = $data[$i];
                    if (isset($data_item['port']) && $data_item['port'] == 3)
                    {
                        $header_item = $this->cleanFlashlogItem($data_item, false);
                        break;
                    }
                }

                if (isset($header_item) && gettype($header_item) == 'array')
                {
                    $csv_sens = array_keys($header_item);
                    $csv_head = [];
                    foreach ($csv_sens as $header) 
                    {
                        $meas       = Measurement::where('abbreviation', $header)->first();
                        $col_head   = $meas ? $meas->pq_name_unit() : $header;
                        if (in_array($col_head, $csv_head) && $col_head != $header) // two similar heads, so add $header
                            $col_head .= ' - '.$header;

                        $csv_head[] = $col_head;
                    }
                    $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

                    // format CSV file body
                    $csv_body = [];
                    foreach ($data as $data_item) 
                    {
                        if (isset($data_item['port']) && $data_item['port'] == 3)
                            $csv_body[] = implode($separator, $this->cleanFlashlogItem($data_item, false));
                    }
                    $fileBody = $csv_head.implode("\r\n", $csv_body);
                }
            }
            else // JSON
            {
                $fileBody = json_encode($data);
            }
        
            // return the file content in a file on disk
            if ($fileBody !== '' && Storage::disk($disk)->put($filePath, $fileBody, ['mimetype' => $file_mime]))
                return ['link'=>Storage::disk($disk)->url($filePath)];
            
            return ['error'=>'export_not_saved'];
        }
    }

    private function parse(Request $request, $id, $persist=false, $delete=false)
    {
        $flashlog_id = intval($id);
        $matches_min = intval($request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 2))); // minimum amount of inline measurements that should be matched 
        $match_props = intval($request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7))); // minimum amount of measurement properties that should match 
        $db_records  = intval($request->input('db_records', env('FLASHLOG_DB_RECORDS', 15)));// amount of DB records to fetch to match each block
        
        $save_result = boolval($request->input('save_result', false));
        $from_cache  = boolval($request->input('from_cache', true));
        $export_csv  = boolval($request->input('csv', false)); // if filled, only save data and return a download link 
        $export_json = boolval($request->input('json', false));  // if filled, only save data and return a download link 
        $block_id    = $request->filled('block_id') ? intval($request->input('block_id')) : -1;
        $block_data_i= intval($request->input('block_data_index', -1));
        $data_minutes= intval($request->input('data_minutes', 10080));
        
        $out = ['error'=>'no_flashlog_found'];

        $flashlog = $this->getUserFlashlogs($id);

        if ($flashlog)
        {
            $device = $flashlog->device;
            
            if ($device)
            {
                $device_id   = $flashlog->device_id;
                $device_name = $flashlog->device_name;
                $hive_id     = $flashlog->hive_id;
                $hive_name   = $flashlog->hive_name;
                $user_id     = $flashlog->user_id;
                $user_name   = $flashlog->user_name;
                $measurements= Measurement::getMatchingMeasurements();
                $add_weight  = true; //$from_cache === false && isset($flashlog->log_messages) && $flashlog->log_messages > 50000 ? false : true; // prevent server crash
                
                if(isset($flashlog->log_file))
                {
                    $out = $flashlog->log(null, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache, 0, $add_weight); // $data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0, $add_sensordefinitions=true

                    // get the data from a single Flashlog block
                    if ($block_id > -1 && isset($out['log'][$block_id]))
                    {
                        $block        = $out['log'][$block_id];
                        $interval_min = $block['interval_min'];
                        $block_data   = json_decode($flashlog->getFileContent('log_file_parsed'), true);
                        $block_start_i= $block['start_i'];
                        $block_end_i  = $block['end_i'];
                        $block_length = $block_end_i - $block_start_i;
                        $has_matches  = isset($out['log'][$block_id]['matches']) ? true : false;

                        if ($export_csv || $export_json)
                            return $this->exportData(array_slice($block_data, $block_start_i, $block_length), "user-$user_id-$device_name-log-file-$id-block-$block_id-matches-$has_matches", $export_csv);

                        // Check if there are matches
                        if ($has_matches)
                        {
                            $block_start_t= $block['time_start'];
                            $block_end_t  = $block['time_end'];
                            $block_start_u= strtotime($block_start_t);  
                            $block_end_u  = strtotime($block_end_t);  

                            if ($delete)
                            {
                                $data_influx_deleted= false;
                                $data_deleted       = 'no_data_to_delete';
                                $delete_count_query = 'SELECT COUNT(*) FROM "sensors" WHERE "from_flashlog" = \'1\' AND "key" = \''.strtolower($device->key).'\' AND time >= \''.$block_start_t.'\' AND time <= \''.$block_end_t.'\'';
                                $delete_count       = Device::getInfluxQuery($delete_count_query, 'flashlog');
                                $delete_count_sum   = isset($delete_count[0]) ? array_sum($delete_count[0]) : 0;
                                $delete_count_bv    = isset($delete_count[0]['bv']) ? $delete_count[0]['bv'] : 0;
                                $deleted_days       = round(($block_end_u - $block_start_u)/86400, 1); 

                                //die(print_r(['q'=>$delete_count_query, 'sum'=>$delete_count_sum, 'delete_count_sum'=>$delete_count_sum, 'deleted_days'=>$deleted_days]));

                                if ($delete_count_sum > 0 && $deleted_days > 0)
                                {
                                    $delete_query        = 'DELETE FROM "sensors" WHERE "from_flashlog"=\'1\' AND "key"=\''.strtolower($device->key).'\' AND time >= \''.$block_start_t.'\' AND time <= \''.$block_end_t.'\'';
                                    //die(print_r($delete_query));
                                    $data_deleted        = $this->client::query($delete_query);
                                    $data_influx_deleted = true;
                                    $flashlog->persisted_block_ids_array = array_diff($flashlog->persisted_block_ids_array, [$block_id]);
                                    $flashlog->persisted_measurements -= $delete_count_bv;
                                    $flashlog->save();
                                }
                                
                                $out = ['data_deleted'=>$data_influx_deleted, 'deleted_measurements'=>$delete_count_sum, 'deleted_days'=>$deleted_days, 'data_deleted'=>$data_deleted];    
                            }
                            else if ($persist) // Save missing data to DB
                            {
                                $persist_count= 0;
                                $interval_db  = 15; // db request minute interval
                                $rows_per_db  = $interval_db / $interval_min; // amount of flashlog items in 1 database interval
                                $req_points_db= $block_length / $rows_per_db;  
                                $req_cnt_db   = ceil($req_points_db / $this->maxDataPoints);
                                $points_p_req = round($req_points_db / $req_cnt_db);
                                $secs_per_req = $points_p_req * $interval_db * 60;

                                $count_measurements = [];
                                foreach ($measurements as $key => $value) 
                                    $count_measurements['count_'.$value] = $key; // compare keys with 'count_' included, alse everything in excluded and sum is always 0

                                for ($req_ind=0; $req_ind <= $req_cnt_db; $req_ind++) 
                                { 
                                    $req_start_unix = $block_start_u + ($secs_per_req * $req_ind);
                                    $req_start_time = date('Y-m-d H:i:s', $req_start_unix);
                                    $req_end_unix   = $block_start_u + ($secs_per_req * ($req_ind+1)) -1;
                                    $req_end_time   = date('Y-m-d H:i:s', $req_end_unix);
                                    
                                    // run through the db data array to define which data to add 
                                    $count_query  = 'SELECT COUNT(*) FROM "sensors" WHERE '.$device->influxWhereKeys().' AND time >= \''.$req_start_time.'\' AND time <= \''.$req_end_time.'\' GROUP BY time('.$interval_db.'m) ORDER BY time ASC LIMIT '.$points_p_req;
                                    
                                    //print_r(['interval_db'=>$interval_db, 'req_start_unix'=>$req_start_unix, 'req_start_time'=>$req_start_time, 'req_end_unix'=>$req_end_unix, 'req_end_time'=>$req_end_time, 'count_query'=>$count_query]);
                                    
                                    $data_per_int       = Device::getInfluxQuery($count_query, 'flashlog');
                                    $data_per_int_d     = [];

                                    $data_per_int_max_i = count($data_per_int) - 1;
                                    $missing_data       = [];
                                    
                                    //die(print_r($data_per_int));

                                    // per Influx time group (15 min)  
                                    for($db_count_i=0 ; $db_count_i < $data_per_int_max_i; $db_count_i++) 
                                    {
                                        $db_count      = $data_per_int[$db_count_i];
                                        $db_count_next = $data_per_int[$db_count_i+1];
                                        $time_start    = $db_count['time'];
                                        $time_end      = $db_count_next['time'];

                                        //print_r(['db_count'=>$db_count, 'm'=>$count_measurements]);

                                        $db_count      = array_intersect_key($db_count, $count_measurements); // only keep the key counts from valid matching measurements
                                        $count_sum     = array_sum($db_count);
                                        
                                        //die(print_r(['db_count'=>$db_count, 'c'=>$count_sum]));

                                        $data_per_int_d[$time_start] = $count_sum;
                                        
                                        if ($count_sum < $match_props) // Database data has less data than flashlog 
                                        {
                                            // define index start-end of day
                                            $secOfCountStart = strtotime($time_start);
                                            $secOfCountEnd   = strtotime($time_end);
                                            $minDifWithStart = round(($secOfCountStart - $block_start_u) / 60);
                                            $indexFlogStart  = $block_start_i + ceil($minDifWithStart / $interval_min);
                                            $indexFlogEnd    = min($block_end_i, $indexFlogStart + $rows_per_db);
                                            $indexFlogStart  = max(0, $indexFlogStart);

                                            for ($i=$indexFlogStart; $i < $indexFlogEnd; $i++)
                                            {
                                                $data_item   = $block_data[$i];

                                                if (isset($data_item['time']))
                                                {
                                                    $secDataItem = strtotime($data_item['time']);
                                                    
                                                    if (isset($data_item['port']) && $data_item['port'] == 3 && $secDataItem >= $secOfCountStart && $secDataItem < $secOfCountEnd) // time from flashlog should be between start and end of this interval
                                                    {
                                                        $missing_data[] = $this->cleanFlashlogItem($data_item);
                                                        
                                                        // print_r(['count_sum'=>$count_sum, 'time_start'=>$time_start, 'secStart'=>$secOfCountStart, 'time_end'=>$time_end, 'secEnd'=>$secOfCountEnd, 'minDifWithStart'=>$minDifWithStart, 'indexFlogStart'=>$indexFlogStart, 'indexFlogEnd'=>$indexFlogEnd, 'indexFlog'=>$i, 'secFlog'=>$secDataItem, 'missing_data'=>$missing_data, 'data_item'=>$data_item]);
                                                        // die();
                                                    }
                                                }
                                            }
                                        }

                                        $missing_data_count = count($missing_data);
                                        if ($missing_data_count > 100 || ($missing_data_count > 0 && $db_count_i == $data_per_int_max_i)) // persist at every 100 items, or at last item
                                        {
                                            //die(print_r(['missing_data_count'=>$missing_data_count, 'block_start_t'=>$block_start_t, 'device'=>$device->toArray(), 'data_per_int_d'=>$data_per_int_d, 'missing_data'=>$missing_data]));
                                            
                                            $stored = $this->storeInfluxDataArrays($missing_data, $device);
                                            if ($stored)
                                                $persist_count += $missing_data_count;
                                            
                                            $missing_data = [];
                                        }
                                    }
                                }

                                //die(print_r([$persist_count, $block_start_t, $data_per_int_d, $missing_data]));
                                
                                if ($persist_count > 0)
                                {
                                    //Cache::forget($flashlog->getLogCacheName(true, true, $matches_min, $match_props, $db_records)); // remove cached result, because import has changed it
                                    
                                    $persist_days = round($persist_count*$interval_min/(60*24), 1);

                                    if (isset($flashlog->persisted_days))
                                        $flashlog->persisted_days += $persist_days;
                                    else
                                        $flashlog->persisted_days = $persist_days;

                                    if (isset($flashlog->persisted_measurements))
                                        $flashlog->persisted_measurements += $persist_count;
                                    else
                                        $flashlog->persisted_measurements = $persist_count;

                                    $persisted_block_ids = $flashlog->persisted_block_ids_array;

                                    if (!in_array($block_id, $persisted_block_ids)) // add persisted block id
                                    {
                                        $persisted_block_ids[] = $block_id;
                                        $flashlog->persisted_block_ids_array = $persisted_block_ids;
                                    }

                                    $out = ['data_stored'=>true, 'persisted_measurements'=>$persist_count, 'persisted_days'=>$persist_days];
                                    $flashlog->save();
                                }
                                else
                                {
                                    $out = ['data_stored'=>false, 'persisted_measurements'=>$persist_count, 'persisted_days'=>0, 'error'=>'no_data_stored'];
                                }
                            }
                            else // Show data content per week
                            {
                                // select portion of the data
                                $match_index   = $block['fl_i'];
                                $index_amount  = round($data_minutes / $interval_min);
                                $data_i_max    = floor(($block_end_i - $block_start_i) / $index_amount);
                                
                                if ($block_data_i == -1)
                                    $block_data_i = round( $data_i_max * (($match_index - $block_start_i) / $block_length) );

                                if ($block_data_i < 0)
                                    $block_data_i = 0;

                                if ($block_data_i > $data_i_max)
                                    $block_data_i = $data_i_max;

                                $start_index = $block_start_i + ($index_amount * $block_data_i);
                                $end_index   = min($block_end_i, $block_start_i + ($index_amount * ($block_data_i+1)));

                                $out = ['block_start_i'=>$block_start_i, 'block_end_i'=>$block_end_i, 'match_index'=>$match_index, 'block_data_index'=>$block_data_i, 'block_data_index_max'=>$data_i_max, 'block_data_index_amount'=>$index_amount, 'block_data_start'=>$start_index, 'block_data_end'=>$end_index, 'flashlog'=>[], 'database'=>[]];

                                // Add flashlog measurement data
                                for ($i=$start_index; $i<$end_index; $i++) 
                                { 
                                    $block_data_item = $this->cleanFlashlogItem($block_data[$i]);
                                    $block_data_item['time'] .= 'Z'; // display as UTC
                                    $out['flashlog'][] = $block_data_item;
                                }

                                $data_values = count($out['flashlog']);
                                // Add Database data
                                if ($data_values > 0)
                                {
                                    $first_obj   = $out['flashlog'][0];
                                    $last_obj    = $out['flashlog'][$data_values-1];
                                    $start_time  = substr($first_obj['time'], 0, 19); // cut off Z
                                    $end_time    = substr($last_obj['time'], 0, 19); // cut off Z
                                    $query       = 'SELECT "'.implode('","', $measurements).'" FROM "sensors" WHERE '.$flashlog->device->influxWhereKeys().' AND time >= \''.$start_time.'\' AND time <= \''.$end_time.'\' ORDER BY time ASC LIMIT '.$index_amount;
                                    $db_data_week= Device::getInfluxQuery($query, 'flashlog');
                                    $db_data_cln = [];
                                    foreach ($db_data_week as $db_value)
                                        $db_data_cln[] = array_filter($db_value);
                                    
                                    $out['database'] = $db_data_cln;

                                    // Run through the data to see how many % of the data matches
                                    $match_percentage = $this->matchPercentage($out['flashlog'], $db_data_cln, $match_props);
                                    $out['block_data_match_percentage']  = $match_percentage['perc_match'];
                                    $out['block_data_flashlog_sec_diff'] = $match_percentage['sec_diff'];
                                    $out['block_data_match_errors']      = $match_percentage['errors'];
                                }

                            }
                            // Add properties
                            $out['block_id'] = $block_id;
                        }
                        else // Show only flashlog data without matched time
                        {
                            // select portion of the data
                            $match_index   = $block['fl_i'];
                            $index_amount  = round($data_minutes / $interval_min);
                            $data_i_max    = floor(($block_end_i - $block_start_i) / $index_amount);
                            
                            if ($block_data_i == -1)
                                $block_data_i = round( $data_i_max * (($match_index - $block_start_i) / $block_length) );

                            if ($block_data_i < 0)
                                $block_data_i = 0;

                            if ($block_data_i > $data_i_max)
                                $block_data_i = $data_i_max;

                            $start_index = $block_start_i + ($index_amount * $block_data_i);
                            $end_index   = min($block_end_i, $block_start_i + ($index_amount * ($block_data_i+1)));

                            $out = ['block_start_i'=>$block_start_i, 'block_end_i'=>$block_end_i, 'match_index'=>$match_index, 'block_data_index'=>$block_data_i, 'block_data_index_max'=>$data_i_max, 'block_data_index_amount'=>$index_amount, 'block_data_start'=>$start_index, 'block_data_end'=>$end_index, 'flashlog'=>[], 'database'=>[]];

                            // Add flashlog measurement data
                            for ($i=$start_index; $i<$end_index; $i++) 
                            { 
                                $data_item = $block_data[$i];
                                if (isset($data_item['port']) && $data_item['port'] == 3)
                                {
                                    $block_data_item = $this->cleanFlashlogItem($data_item, false);

                                    if (isset($block_data_item['minute']))
                                        $block_data_item['time'] = date('Y-m-d\TH:i:s\Z', 946681200 + $block_data_item['minute'] * 60); // display as UTC from 2000-01-01 00:00:00
                                    
                                    $out['flashlog'][] = $block_data_item;
                                }
                            }
                            $out['block_data_match_percentage']  = 0;
                            $out['block_data_flashlog_sec_diff'] = '? ';
                            $out['block_data_match_errors']      = '';
                        }
                    }
                    else // no block_id set, so show all blocks, or download all data in the flashlog
                    {
                        if ($export_csv || $export_json)
                        {
                            $all_log_data = json_decode($flashlog->getFileContent('log_file_parsed'), true);
                            return $this->exportData($all_log_data, "user-$user_id-$device_name-log-file-$id-all-data", $export_csv);
                        }
                    }
                }
                else
                {
                    $out = ['error'=>'no_flashlog_data'];
                }

                // Add properties
                $out['device_id']   = $device_id;
                $out['device_name'] = $device_name;
                $out['hive_id']     = $hive_id;
                $out['hive_name']   = $hive_name;
                $out['user_id']     = $user_id;
                $out['user_name']   = $user_name;
            }
            else
            {
                $out = ['error'=>'no_device'];
            }

        }
        
        // Add properties
        $out['matches_min'] = $matches_min;
        $out['match_props'] = $match_props;
        $out['db_records']  = $db_records;
        $out['flashlog_id'] = $flashlog_id;
        $out['persisted_block_ids_array'] = $flashlog->persisted_block_ids_array;
        
        return $out;
    }

    public function destroy(Request $request, $id)
    {
        return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
