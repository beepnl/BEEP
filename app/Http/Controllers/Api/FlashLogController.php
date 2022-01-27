<?php

namespace App\Http\Controllers\Api;

use App\Measurement;
use App\Device;
use App\Models\FlashLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Storage;
use Cache;
use Auth;

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
                //die(print_r($e->getMessage()));
            }
        }
        return $stored;
    }

    private function cleanFlashlogItem($object)
    {
        unset($object->payload_hex);
        unset($object->pl);
        unset($object->len);
        unset($object->vcc);
        unset($object->pl_bytes);
        unset($object->beep_base);
        unset($object->weight_sensor_amount);
        unset($object->ds18b20_sensor_amount);
        unset($object->port);
        unset($object->minute_interval);
        unset($object->bat_perc);
        unset($object->fft_bin_amount);
        unset($object->fft_start_bin);
        unset($object->fft_stop_bin);
        //unset($object->i);
        return $object;
    }

    private function matchPercentage($array1, $array2, $match_props=9)
    {
        $matches       = [];
        $secDiff       = [];
        $match_count   = 0;
        $array2_index  = 0;
        $array1_length = count($array1);
        $array2_length = count($array2);
        $array_min_len = min($array1_length, $array2_length);

        if ($array1_length > 0 && $array2_length > 0)
        {
            for ($i=0; $i < $array1_length; $i++) 
            {
                $f = (array)$array1[$i];
                
                for ($j=$array2_index; $j < $array2_length; $j++) 
                {   
                    $d           = array_filter($array2[$j]);
                    $d_time      = $d['time'];
                    unset($d['time']); // remove time for count
                    $d_val_count = count($d);
                    
                    if ($d_val_count < $match_props)
                    {
                        $array2_index = $j;
                        continue;
                    }

                    if (isset($f['bv']) && isset($d['bv']) && $f['bv'] == $d['bv']) // first fast check
                    {
                        $match = array_intersect_assoc($d, $f);

                        if ($match != null && count($match) >= $d_val_count-1)
                        {
                            $d['time'] = $d_time; // put back tima
                            $matches[] = ['d'=>$d, 'f'=>$f, 'm'=>$match];
                            $secDiff[] = strtotime($f['time']) - strtotime($d['time']);
                            $array2_index = $j;
                            $match_count++;
                            continue 2; // next foreach loop to continue with the next database item
                        }
                    }
                }
            }
        }
        $secDiffAvg = count($secDiff) > 0 ? round(array_sum($secDiff)/count($secDiff)) : null;
        $percMatch  = $array_min_len > 0 ? round(100 * ($match_count / $array_min_len), 1): 0;
        //die(print_r([$percMatch, $secDiffAvg, $matches]));
        return ['sec_diff'=>$secDiffAvg, 'perc_match'=>$percMatch];
    }

    private function parse(Request $request, $id, $persist=false)
    {
        $flashlog_id = intval($id);
        $matches_min = intval($request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 2))); // minimum amount of inline measurements that should be matched 
        $match_props = intval($request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7))); // minimum amount of measurement properties that should match 
        $db_records  = intval($request->input('db_records', env('FLASHLOG_DB_RECORDS', 15)));// amount of DB records to fetch to match each block
        
        $save_result = boolval($request->input('save_result', false));
        $from_cache  = boolval($request->input('from_cache', true));
        $block_id    = intval($request->input('block_id', -1));
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
                //$measurements= Measurement::getValidMeasurements(true);
                $measurements = Measurement::getMatchingMeasurements();
                
                if(isset($flashlog->log_file))
                {
                    $data = $flashlog->getFileContent('log_file');
                    if (isset($data))
                    {
                        $out = $flashlog->log($data, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache); // $data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0

                        // get the data from a single Flashlog block
                        if ($block_id > -1)
                        {
                            // Check if there are matches
                            if (isset($out['log'][$block_id]) && isset($out['log'][$block_id]['matches']))
                            {
                                $block        = $out['log'][$block_id];
                                $interval_min = $block['interval_min'];
                                $block_data   = json_decode($flashlog->getFileContent('log_file_parsed'));
                                $block_start_i= $block['start_i'];
                                $block_end_i  = $block['end_i'];
                                $block_length = $block_end_i - $block_start_i;

                                if ($persist) // Save missing data to DB
                                {
                                    $block_start_t= $block['time_start'];
                                    $block_end_t  = $block['time_end'];
                                    $block_start_u= strtotime($block_start_t);
                                    
                                    $interval_db  = 15; // min
                                    $rows_per_db  = $interval_db / $interval_min; // amount of flashlog items in 1 database interval
                                    $req_points_db= $block_length / $rows_per_db;  
                                    $req_cnt_db   = ceil($req_points_db / $this->maxDataPoints);
                                    $points_p_req = round($req_points_db / $req_cnt_db);
                                    $secs_per_req = $points_p_req * $interval_db * 60;


                                    for ($req_ind=0; $req_ind <= $req_cnt_db; $req_ind++) 
                                    { 
                                        $req_start_unix = $block_start_u + ($secs_per_req * $req_ind);
                                        $req_start_time = date('Y-m-d H:i:s', $req_start_unix);
                                        $req_end_unix   = $block_start_u + ($secs_per_req * ($req_ind+1)) -1;
                                        $req_end_time   = date('Y-m-d H:i:s', $req_end_unix);
                                        

                                        // run through the db data array to define which data to add 
                                        $count_query  = 'SELECT COUNT(*) FROM "sensors" WHERE '.$device->influxWhereKeys().' AND time >= \''.$req_start_time.'\' AND time <= \''.$req_end_time.'\' GROUP BY time('.$interval_db.'m) ORDER BY time ASC LIMIT '.$points_p_req;
                                        
                                        //die(print_r([$req_start_unix, $req_start_time, $req_end_unix, $req_end_time, $count_query]));
                                        
                                        $data_per_int = Device::getInfluxQuery($count_query, 'flashlog');
                                        
                                        $persist_count= 0;
                                        $data_per_int_d = [];
                                        
                                        $data_per_int_max_i = count($data_per_int) - 1;
                                        $missing_data       = [];
                                        
                                        foreach ($data_per_int as $db_count_i => $db_count) 
                                        {
                                            $time_start   = $db_count['time'];
                                            unset($db_count['time']); // don't include time in sum
                                            $count_sum     = array_sum($db_count);
                                            $data_per_int_d[$time_start] = $count_sum;
                                            
                                            if ($count_sum < $match_props)
                                            {
                                                // define index start-end of day
                                                $secOfDayStart   = strtotime($time_start);
                                                $minDifWithStart = round(($secOfDayStart - $block_start_u) / 60);
                                                $indexFlogStart  = $block_start_i + round($minDifWithStart / $interval_min);
                                                $indexFlogEnd    = min($block_end_i, $indexFlogStart + $rows_per_db);
                                                $indexFlogStart  = max(0, $indexFlogStart);

                                                //print_r([$time_start, $indexFlogStart, $indexFlogEnd, $block_data[$indexFlogEnd-1]]);

                                                for ($i=$indexFlogStart; $i < $indexFlogEnd; $i++)
                                                {
                                                    $data_item = $block_data[$i];
                                                    if (isset($data_item->port) && $data_item->port == 3)
                                                        $missing_data[] = (array)$this->cleanFlashlogItem($data_item);
                                                }
                                            }

                                            $missing_data_count = count($missing_data);
                                            if ($missing_data_count > 100 || ($missing_data_count > 0 && $db_count_i == $data_per_int_max_i)) // persist at every 100 items, or at last item
                                            {
                                                //die(print_r([$missing_data_count, $block_start_t, $data_per_int_d, $data_per_int, $missing_data]));
                                                
                                                $stored = $this->storeInfluxDataArrays($missing_data, $device);
                                                if ($stored)
                                                    $persist_count += $missing_data_count;
                                                
                                                $missing_data = [];
                                            }
                                        }
                                    }

                                    //die(print_r([$persist_count, $persist_days.' days', $block_start_t, $data_per_int_d, $missing_data]));
                                    
                                    if ($persist_count > 0)
                                    {
                                        Cache::forget($flashlog->getLogCacheName(true, true, $matches_min, $match_props, $db_records)); // remove cached result, because import has changed it
                                        
                                        $persist_days = round($persist_count*$interval_min/(60*24), 1);
                                        $out          = ['data_stored'=>true, 'persisted_measurements'=>$persist_count, 'persisted_days'=>$persist_days];

                                        if (isset($flashlog->persisted_days))
                                            $flashlog->persisted_days += $persist_days;
                                        else
                                            $flashlog->persisted_days = $persist_days;

                                        if (isset($flashlog->persisted_measurements))
                                            $flashlog->persisted_measurements += $persist_count;
                                        else
                                            $flashlog->persisted_measurements = $persist_count;

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
                                        $block_data_item->time .= 'Z'; // display as UTC
                                        $out['flashlog'][] = $block_data_item;
                                    }

                                    $data_values = count($out['flashlog']);
                                    // Add Database data
                                    if ($data_values > 0)
                                    {
                                        $first_obj   = $out['flashlog'][0];
                                        $last_obj    = $out['flashlog'][$data_values-1];
                                        $start_time  = substr($first_obj->time, 0, 19); // cut off Z
                                        $end_time    = substr($last_obj->time, 0, 19); // cut off Z
                                        $query       = 'SELECT "'.implode('","', $measurements).'" FROM "sensors" WHERE '.$flashlog->device->influxWhereKeys().' AND time >= \''.$start_time.'\' AND time <= \''.$end_time.'\' ORDER BY time ASC LIMIT '.$index_amount;
                                        $out['database'] = Device::getInfluxQuery($query, 'flashlog');

                                        // Run through the data to see how many % of the data matches
                                        $match_percentage = $this->matchPercentage($out['flashlog'], $out['database'], $match_props);
                                        $out['block_data_match_percentage']  = $match_percentage['perc_match'];
                                        $out['block_data_flashlog_sec_diff'] = $match_percentage['sec_diff'];
                                    }

                                }
                                // Add properties
                                $out['block_id'] = $block_id;
                            }
                            else
                            {
                                $out = ['error'=>'no_matches_for_block_'.$block_id];
                            }
                        }
                    }
                    else
                    {
                        $out = ['error'=>'no_flashlog_data'];
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
        
        return $out;
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['error'=>'destroy_not_yet_implemented']);
        //return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
