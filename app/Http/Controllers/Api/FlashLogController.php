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
 * @authenticated
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
            else // normal users, show your own, and editable group hive flashlogs
            {
                if (isset($id))
                    return Auth::user()->allFlashlogs()->find($id);

                return Auth::user()->allFlashlogs()->orderByDesc('id')->get();
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

    

    private function diff_percentage($val1, $val2, $round_decimals=1)
    {
        if ($val1 === $val2)
            return 0;

        $rval1= round($val1,$round_decimals);
        $rval2= round($val2,$round_decimals);
        $diff = abs($rval1 - $rval2);
        $ave  = ($rval1 + $rval2) / 2;
        return $ave != 0 ? min(100, max(0, round(100 * $diff / $ave, 1))) : 0;
    }


    private function matchPercentage($array1, $array2, $match_props=9, $max_diff_percentage=0) // flashlog_array, database_array
    {
        //$matches       = [];
        $secDiff       = [];
        $match_count   = 0;
        $array2_index  = 0;
        $array1_length = count($array1);
        $array2_length = count($array2);
        $array_min_len = min($array1_length, $array2_length);
        $errors        = [];
        $perc_diff_sum = 0;
        $perc_diff_cnt = 0;
        $should_match  = ['weight_kg'=>0,'t_i'=>0,'t_0'=>0,'t_1'=>0]; // reject match, because weight_kg, t_i, t_0, or t_1 do not match
        $should_m_keys = array_keys($should_match); // reject match, because weight_kg, t_i, t_0, or t_1 do not match
                            
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

                    if (isset($f['bv']) && isset($d['bv']) && $this->diff_percentage($f['bv'], $d['bv'], 3) <= $max_diff_percentage) // first fast check on similar battery voltages
                    {
                        // loop through both array measurements values
                        $matches           = 0; 
                        $should_match_diff = [];
                        foreach (array_keys($d) as $m_key)
                        {
                            if (isset($d[$m_key]) && isset($f[$m_key]))
                            {
                                $diff_perc = $this->diff_percentage($d[$m_key], $f[$m_key]);
                                $perc_diff_sum += $diff_perc;
                                $perc_diff_cnt++;

                                if ($diff_perc <= $max_diff_percentage) // m_key matches
                                    $matches++;
                                else if (in_array($m_key, $should_m_keys))
                                    $should_match_diff[$m_key] = $diff_perc;
                                
                            }
                        }
                        // check validity of this match
                        if ($matches >= $d_val_count-1)
                        {
                            $match_ok = true;
                            foreach ($should_match_diff as $m_key => $diff)
                            {
                                $should_match[$m_key] = $should_match[$m_key] + 1;
                                if ($m_key == 'weight_kg' && abs($d[$m_key]) > 200 && abs($f[$m_key]) < 200) // are still be ok, because uncalibrated db values can be replaced
                                {
                                    $errors[$m_key] = "$should_match[$m_key] $m_key values uncalibrated";
                                }
                                else
                                {
                                    // reject match, because weight_kg, t_i, t_0, or t_1 does not match
                                    $match_ok = false;
                                    $errors[$m_key] = "$should_match[$m_key] $m_key values differ";
                                }
                            }
                            if ($match_ok) // count this measurement as a match
                            {
                                $secDiff[] = strtotime($f['time']) - strtotime($d_time);
                                $match_count++;
                            }

                            $array2_index = $j;
                            continue 2;
                        }
                    }
                }
            }
        }
        $secDiffAvg   = count($secDiff) > 0 ? round(array_sum($secDiff)/count($secDiff)) : null;
        $matchDiffAvg = $perc_diff_cnt > 0 ? round($perc_diff_sum/$perc_diff_cnt, 1) : null;
        $percMatch    = $array_min_len > 0 ? round(100 * ($match_count / $array_min_len), 1): 0;
        //die(print_r([$percMatch, $secDiffAvg, $matches]));
        return ['sec_diff'=>$secDiffAvg, 'perc_match'=>$percMatch, 'match_count'=>$match_count, 'avg_diff'=>$matchDiffAvg, 'errors'=>implode(', ', $errors)];
    }

    
    private function parse(Request $request, $id, $persist=false, $delete=false)
    {
        $flashlog_id = intval($id);
        $matches_min = intval($request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 5))); // minimum amount of inline measurements that should be matched 
        $match_props = intval($request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7))); // minimum amount of measurement properties that should match 
        $db_records  = intval($request->input('db_records', env('FLASHLOG_DB_RECORDS', 15)));// amount of DB records to fetch to match each block
        
        $save_result = boolval($request->input('save_result', false));
        $from_cache  = boolval($request->input('from_cache', true));
        $show_log    = boolval($request->input('show_log', false));
        $export_csv  = boolval($request->input('csv', false)); // if filled, only save data and return a download link 
        $export_json = boolval($request->input('json', false));  // if filled, only save data and return a download link 
        $block_id    = $request->filled('block_id') ? intval($request->input('block_id')) : -1;
        $block_data_i= intval($request->input('block_data_index', -1));
        $data_minutes= intval($request->input('data_minutes', env('FLASHLOG_WINDOW_MINUTES', 10080))); // default 1 week 
        
        $out         = ['error'=>'no_flashlog_found'];
        $out_log     = [];

        $flashlog = $this->getUserFlashlogs($id);

        
        if ($flashlog)
        {
            $device = $flashlog->device;
            
            if ($device)
            {
                $device_id   = $flashlog->device_id;
                $device_name = $flashlog->device_name;
                $device_intm = $device->measurement_interval_min;
                $hive_id     = $flashlog->hive_id;
                $hive_name   = $flashlog->hive_name;
                $user_id     = $flashlog->user_id;
                $user_name   = $flashlog->user_name;
                $measurements= Measurement::getMatchingMeasurements();
                $add_weight  = true; //$from_cache === false && isset($flashlog->log_messages) && $flashlog->log_messages > 50000 ? false : true; // prevent server crash
                
                if(isset($flashlog->log_file))
                {
                    $out = $flashlog->log(null, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache, 0, $add_weight); // $data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0, $add_sensordefinitions=true
                    //die(print_r($out));

                    $log_blocks = [];

                    if (isset($out['log']))
                    {
                        $out_log = $out['log'];
                        foreach ($out_log as $out_log_block)
                        {
                            $log_blocks[$out_log_block['block']] = $out_log_block;
                        }
                    }

                    // Log::debug("FlashLogController parse id: $id, block_id: $block_id, block_data_i: $block_data_i");
                    // Log::debug($log_blocks);

                    // get the data from a single Flashlog block
                    if ($block_id > -1 && isset($log_blocks[$block_id]))
                    {
                        $block        = $log_blocks[$block_id];
                        $interval_min = isset($block['interval_min']) ? $block['interval_min'] : $device_intm;
                        $block_data   = json_decode($flashlog->getFileContent('log_file_parsed'), true);
                        $block_start_i= $block['start_i'];
                        $block_end_i  = $block['end_i'];
                        $block_length = $block_end_i - $block_start_i;
                        $has_matches  = isset($log_blocks[$block_id]['matches']) ? true : false;

                        $interval_db  = 15; // db request minute interval
                        $fl_per_db_int= $interval_db / $interval_min; // amount of flashlog items in 1 database interval

                        // show only portion of the data (for charting in ChartJS)
                        $interval_multi = 1;
                        if ($data_minutes > 43200) // month
                            $interval_multi = 12;
                        else if ($data_minutes > 10080) // week
                            $interval_multi = 8;
                        else if ($data_minutes > 1440)
                            $interval_multi = 4;

                        $interval_multi = $interval_multi * $fl_per_db_int;

                        if ($export_csv || $export_json)
                            return FlashLog::exportData(array_slice($block_data, $block_start_i, $block_length), "user-$user_id-$device_name-log-file-$id-block-$block_id-matches-$has_matches", $export_csv);

                        // Check if there are matches (NB: Bug: persisted measurements now can only be deleted in a block with matches)
                        if ($has_matches)
                        {
                            $block_start_t= $block['time_start'];
                            $block_end_t  = $block['time_end'];
                            $block_start_u= strtotime($block_start_t);  
                            $block_end_u  = strtotime($block_end_t);  
                            
                            if ($delete || $persist)
                            {
                                Log::debug("");
                                $persist_log = $persist ? '1' : '0';
                                $delete_log  = $delete ? '1' : '0';
                                Log::debug("FlashLogController parse device=$device_name, persist=$persist_log, del=$delete_log, fl_id=$flashlog_id, bl_id=$block_id, bl_len=$block_length, bl_st_tm=$block_start_t, bl_st_i=$block_start_i, bl_end_tm=$block_end_t, bl_end_i=$block_end_i, bl_data_i=$block_data_i");
                            }

                            if ($delete)
                            {
                                $data_influx_deleted= false;
                                $data_delete_errors = [];
                                $data_deleted       = 'no_data_to_delete';
                                $delete_count_query = 'SELECT COUNT("bv") AS "count" FROM "sensors" WHERE "from_flashlog" = \'1\' AND '.$device->influxWhereKeys().' AND time >= \''.$block_start_t.'\' AND time <= \''.$block_end_t.'\'';
                                $delete_count       = Device::getInfluxQuery($delete_count_query, 'flashlog');
                                $delete_count_sum   = isset($delete_count[0]['count']) ? $delete_count[0]['count'] : 0;
                                $deleted_days       = round($delete_count_sum*$interval_min/(60*24), 1);

                                Log::debug("delete before: delete_count_sum=$delete_count_sum deleted_days=$deleted_days");

                                if ($delete_count_sum > 0 && $deleted_days > 0)
                                {
                                    // Delete for each key separately since OR statements do not work in DELETE statements
                                    foreach ($device->allKeys() as $device_key) 
                                    {
                                        $delete_query = 'DELETE FROM "sensors" WHERE "key" = \''.$device_key.'\' AND "from_flashlog" = \'1\' AND time >= \''.$block_start_t.'\' AND time <= \''.$block_end_t.'\'';
                                        try
                                        {
                                            $data_deleted = $this->client::query($delete_query);
                                            $data_influx_deleted = true;
                                            Log::debug($delete_query);
                                        }
                                        catch(\Exception $e)
                                        {
                                            $data_delete_errors[] = $e->getMessage();
                                            Log::error($e->getMessage());
                                            Log::error($delete_query);
                                            $delete_count_sum = 0;
                                            $deleted_days = 0;
                                        }
                                    }
                                    
                                    if ($data_influx_deleted)
                                    {
                                        $persisted_block_ids_array = $flashlog->persisted_block_ids_array;
                                        if (count($persisted_block_ids_array) == 0 || (count($persisted_block_ids_array) == 1 && $persisted_block_ids_array[0] == $block_id))
                                            $flashlog->persisted_block_ids = null;
                                        else
                                            $flashlog->persisted_block_ids_array = array_diff($flashlog->persisted_block_ids_array, [$block_id]);

                                        $flashlog->persisted_measurements = max(0, $flashlog->persisted_measurements - $delete_count_sum);
                                        $flashlog->save();
                                    }
                                }
                                
                                if (count($data_delete_errors) > 0)
                                    $out = ['data_deleted'=>$data_influx_deleted, 'deleted_measurements'=>$delete_count_sum, 'deleted_days'=>$deleted_days, 'errors'=>$data_delete_errors];
                                else
                                    $out = ['data_deleted'=>$data_influx_deleted, 'deleted_measurements'=>$delete_count_sum, 'deleted_days'=>$deleted_days];

                                if ($data_influx_deleted)
                                    Cache::forget($flashlog->getLogCacheName(true, true, $matches_min, $match_props, $db_records)); // remove cached result, because import has changed it
                                
                                Log::debug("delete finished: ".json_encode($out)); 

                            }
                            else if ($persist) // Save missing data to DB
                            {
                                $persist_count= 0;
                                $db_insert_rec= 4999; // chuck size of records to insert at one POST request
                                $req_points_db= $block_length / $fl_per_db_int;
                                $req_cnt_db   = ceil($req_points_db / $this->maxDataPoints);
                                $points_p_req = round($req_points_db / $req_cnt_db);
                                $secs_per_req = $points_p_req * $interval_db * 60;

                                $count_measurements = [];
                                foreach ($measurements as $key => $value) 
                                    $count_measurements['count_'.$value] = $key; // compare keys with 'count_' included, else everything in excluded and sum is always 0

                                //Log::debug(['count_measurements'=>$count_measurements]);

                                // split the amount of requests in sensible maximum amount of measurements from DB
                                for ($req_ind=0; $req_ind <= $req_cnt_db; $req_ind++) 
                                { 
                                    $req_start_unix = $block_start_u + ($secs_per_req * $req_ind);
                                    $req_start_time = date('Y-m-d H:i:s', $req_start_unix);
                                    $req_end_unix   = $block_start_u + ($secs_per_req * ($req_ind+1)) -1;
                                    $req_end_time   = date('Y-m-d H:i:s', $req_end_unix);
                                    
                                    // run through the db data array to define which data to add 
                                    $count_query        = 'SELECT COUNT(*) FROM "sensors" WHERE '.$device->influxWhereKeys().' AND time >= \''.$req_start_time.'\' AND time <= \''.$req_end_time.'\' GROUP BY time('.$interval_db.'m) ORDER BY time ASC LIMIT '.$points_p_req;
                                    $data_per_int       = Device::getInfluxQuery($count_query, 'flashlog');
                                    $data_per_int_d     = [];
                                    $data_per_int_max_i = count($data_per_int)-1;
                                    $missing_data       = [];
                                    
                                    Log::debug("persist_check chunk $req_ind/$req_cnt_db of $points_p_req interval_db min values: req_start_time=$req_start_time, req_end_time=$req_end_time, query_results=$data_per_int_max_i");
                                    //die(print_r($data_per_int));

                                    // Persist all non-existing Flashlog data to InfluxDB
                                    if ($data_per_int_max_i == -1) // import data where there is NO database data available
                                    {
                                        $indexFlogStart  = round($block_start_i + ($req_ind * $points_p_req * $fl_per_db_int));
                                        $indexFlogEnd    = round($block_start_i + (($req_ind+1) * $points_p_req * $fl_per_db_int));
                                        
                                        Log::debug("persist_with_no_db_values: indexFlogStart=$indexFlogStart, indexFlogEnd=$indexFlogEnd");

                                        for ($i=$indexFlogStart; $i < $indexFlogEnd; $i++)
                                        {
                                            if (isset($block_data[$i]))
                                            {
                                                $data_item = $block_data[$i];

                                                if (isset($data_item['time']) && isset($data_item['port']) && $data_item['port'] == 3) // time from flashlog should be between start and end of this interval
                                                    $missing_data[] = FlashLog::cleanFlashlogItem($data_item);

                                            }
                                            // Store batches of data to InfluxDB
                                            $missing_data_count = count($missing_data);
                                            if ($missing_data_count > $db_insert_rec || ($missing_data_count > 0 && $i == $indexFlogEnd - 1)) // persist at every 100 items, or at last item
                                            {
                                                $stored = $this->storeInfluxDataArrays($missing_data, $device);
                                                if ($stored)
                                                    $persist_count += $missing_data_count;
                                                
                                                $logMissingDates = $missing_data[0]['time'].' -> '.$missing_data[$missing_data_count-1]['time'];
                                                $missing_data    = [];

                                                Log::debug("persist_with_no_db_values: stored=$stored, missing_data_count=$missing_data_count, persist_count=$persist_count, dates=$logMissingDates");
                                            }
                                        }
                                    }
                                    else // import data where there is database data available
                                    {
                                        $dbStartDate = $data_per_int[0]['time'];
                                        $dbEndDate   = $data_per_int[$data_per_int_max_i]['time'];
                                        Log::debug("persist_in_between_db_values: dbStartDate=$dbStartDate, dbEndDate=$dbEndDate");
                                        // Run through DB data per Influx time group (15 min)  
                                        for($db_count_i=0 ; $db_count_i < $data_per_int_max_i; $db_count_i++) 
                                        {
                                            $db_count      = $data_per_int[$db_count_i];
                                            $db_count_next = $data_per_int[$db_count_i+1];
                                            $time_start    = $db_count['time'];
                                            $time_end      = $db_count_next['time'];

                                            //print_r(['db_count'=>$db_count, 'm'=>$count_measurements]);

                                            $db_count      = array_intersect_key($db_count, $count_measurements); // only keep the key counts from valid matching measurements
                                            $count_sum     = array_sum($db_count) / $fl_per_db_int;                                        
                                            $data_per_int_d[$time_start] = $count_sum;
                                            

                                            if ($count_sum < $match_props) // Database data has less data than flashlog 
                                            {
                                                // define index start-end of day
                                                $secOfCountStart = strtotime($time_start);
                                                $secOfCountEnd   = strtotime($time_end);
                                                $minDifWithStart = round(($secOfCountStart - $block_start_u) / 60);
                                                $indexFlogStart  = $block_start_i + ceil($minDifWithStart / $interval_min);
                                                $indexFlogEnd    = min($block_end_i, $indexFlogStart + $fl_per_db_int);
                                                $indexFlogStart  = max(0, $indexFlogStart);
                                                
                                                for ($i=$indexFlogStart; $i < $indexFlogEnd; $i++)
                                                {
                                                    if (isset($block_data[$i]))
                                                    {
                                                        $data_item = $block_data[$i];

                                                        if (isset($data_item['time']))
                                                        {
                                                            $secDataItem = strtotime($data_item['time']);
                                                            
                                                            if (isset($data_item['port']) && $data_item['port'] == 3 && $secDataItem >= $secOfCountStart && $secDataItem < $secOfCountEnd) // time from flashlog should be between start and end of this interval
                                                                $missing_data[] = FlashLog::cleanFlashlogItem($data_item);

                                                        }
                                                    }
                                                }
                                            }

                                            // Store batches of data to InfluxDB
                                            $missing_data_count = count($missing_data);
                                            if ($missing_data_count > $db_insert_rec || ($missing_data_count > 0 && $db_count_i == $data_per_int_max_i - 1)) // persist at every $db_insert_rec items, or at last item
                                            {
                                                $stored = $this->storeInfluxDataArrays($missing_data, $device);
                                                if ($stored)
                                                    $persist_count += $missing_data_count;
                                                
                                                $logMissingDates = $missing_data[0]['time'].' -> '.$missing_data[$missing_data_count-1]['time'];
                                                $missing_data    = [];

                                                Log::debug("persist_in_between_db_values stored=$stored, missing_data_count=$missing_data_count, persist_count=$persist_count, missing_dates=$logMissingDates");
                                            }
                                        }
                                    }
                                }

                                //die(print_r([$persist_count, $block_start_t, $data_per_int_d, $missing_data]));
                                
                                if ($persist_count > 0)
                                {
                                    Cache::forget($flashlog->getLogCacheName(true, true, $matches_min, $match_props, $db_records)); // remove cached result, because import has changed it
                                    
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
                                Log::debug("persist finished: ".json_encode($out)); 
                            }
                            else // Show data content per $data_minutes
                            {
                                $fl_i_modulo   = $interval_multi;
                                $match_index   = $block['fl_i'];
                                $index_amount  = round($data_minutes / $interval_min);
                                $data_i_max    = floor(($block_end_i - $block_start_i) / $index_amount);
                                
                                if ($block_data_i == -1)
                                    $block_data_i = round( $data_i_max * (($match_index - $block_start_i) / $block_length) );

                                if ($block_data_i < 0)
                                    $block_data_i = 0;

                                if ($block_data_i > $data_i_max)
                                    $block_data_i = $data_i_max;

                                $start_index   = $block_start_i + ($index_amount * $block_data_i);
                                $end_index     = min($block_end_i, $block_start_i + ($index_amount * ($block_data_i+1)));
                                    
                                $out = ['interval_min'=>$interval_min*$interval_multi, 'data_point_modulo'=>$fl_i_modulo, 'block_start_i'=>$block_start_i, 'block_end_i'=>$block_end_i, 'match_index'=>$match_index, 'block_data_index'=>$block_data_i, 'block_data_index_max'=>$data_i_max, 'block_data_index_amount'=>$index_amount, 'block_data_start'=>$start_index, 'block_data_end'=>$end_index, 'flashlog'=>[], 'database'=>[]];

                                // Add flashlog measurement data
                                $fl_data_cln    = [];
                                for ($i=$start_index; $i<$end_index; $i++) 
                                { 
                                    $block_data_item = FlashLog::cleanFlashlogItem($block_data[$i]);
                                    
                                    if (isset($block_data_item['time']))
                                    {
                                        $block_data_item['time'] .= 'Z'; // display as UTC
                                        $fl_data_cln[] = $block_data_item;
                                    }
                                }
                                $data_val_count = count($fl_data_cln);
                                
                                // Add Database data
                                if ($data_val_count > 0)
                                {
                                    $first_obj     = $fl_data_cln[0];
                                    $last_obj      = $fl_data_cln[$data_val_count-1];
                                    $start_time    = substr($first_obj['time'], 0, 19); // cut off Z
                                    $end_time      = substr($last_obj['time'], 0, 19); // cut off Z
                                    $query         = 'SELECT "'.implode('","', $measurements).'" FROM "sensors" WHERE '.$device->influxWhereKeys().' AND time >= \''.$start_time.'\' AND time <= \''.$end_time.'\' ORDER BY time ASC LIMIT '.$index_amount;
                                    $db_data_block = Device::getInfluxQuery($query, 'flashlog');
                                    $db_data_len   = count($db_data_block);
                                    $db_data_cln   = [];
                                    
                                    for ($i=0; $i<$db_data_len; $i++) 
                                        $db_data_cln[] = array_filter($db_data_block[$i]);
                                
                                    // Run through the data to see how many % of the data matches
                                    if ($device->rtc)
                                    {
                                        $out['block_data_match_percentage']  = 100;
                                        $out['block_data_flashlog_sec_diff'] = 0;
                                        $out['block_data_match_errors']      = '';
                                        $out['block_data_diff_percentage']   = 0;
                                        $out['block_data_match_count']       = $index_amount;
                                    }
                                    else
                                    {
                                        if ($data_minutes <= 43200)
                                        {
                                            $match_percentage = $this->matchPercentage($fl_data_cln, $db_data_cln, $match_props);
                                            $out['block_data_match_percentage']  = $match_percentage['perc_match'];
                                            $out['block_data_flashlog_sec_diff'] = $match_percentage['sec_diff'];
                                            $out['block_data_match_errors']      = $match_percentage['errors'];
                                            $out['block_data_diff_percentage']   = $match_percentage['avg_diff'];
                                            $out['block_data_match_count']       = $match_percentage['match_count'];
                                        }
                                        else
                                        {
                                            $out['block_data_match_percentage']  = 0;
                                            $out['block_data_flashlog_sec_diff'] = '?';
                                            $out['block_data_match_errors']      = 'Cannot calculate matches for periods >30 days';
                                            $out['block_data_diff_percentage']   = 0;
                                            $out['block_data_match_count']       = 0;
                                        }
                                    }

                                    // Add min start / max end time for ChartJS view
                                    $time_start_db = strtotime($first_obj['time']);
                                    $time_end_db   = strtotime($last_obj['time']);
                                    $time_start_fl = strtotime($block_data[$start_index]['time']);
                                    $time_end_fl   = strtotime($block_data[$end_index-1]['time']);

                                    $out['start_date'] = date($this->timeFormat, min($time_start_db, $time_start_fl)).'Z';
                                    $out['end_date']   = date($this->timeFormat, max($time_end_db, $time_end_fl)).'Z';
                                    // Add DB data
                                    $out['database']   = $db_data_cln;

                                    // Remove values for ease of charting (in frontend) if $interval_multi > 1 
                                    if ($fl_i_modulo > 1)
                                    {
                                        for ($i=0; $i < $data_val_count; $i++) 
                                        { 
                                            if ($i % $fl_i_modulo != 0)
                                            {
                                                unset($fl_data_cln[$i]);
                                                unset($db_data_cln[$i]);
                                            }
                                        }
                                    }
                                }

                                // Add data te response
                                $out['flashlog'] = array_values($fl_data_cln);
                                $out['database'] = array_values($db_data_cln);

                            }
                            // Add properties
                            $out['block_id'] = $block_id;
                        }
                        else if ($delete) // no matches but delete button pressed
                        {
                            $out = ['data_deleted'=>false, 'deleted_measurements'=>0, 'deleted_days'=>0, 'error'=>'no_data_deleted_because_no_matches_found'];  
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

                            // check if time is set
                            $device_time_set = false;
                            if (isset($block_data[$start_index]['time']) && isset($block_data[$end_index-1]['time']))
                                $device_time_set = true;

                            // Add flashlog measurement data
                            $out['start_date'] = null;
                            $out['end_date']   = null;
                            $fl_i_modulo       = $interval_multi;
                            $modulo_counter    = 0; // counter that starts at 0 (like DB $i)
                                
                            for ($i=$start_index; $i<$end_index; $i++) 
                            { 
                                if ($fl_i_modulo < 2 || $modulo_counter % $fl_i_modulo == 0)
                                {
                                    $data_item = $block_data[$i];
                                    if (isset($data_item['port']) && $data_item['port'] == 3)
                                    {
                                        $block_data_item = FlashLog::cleanFlashlogItem($data_item, false);

                                        if ($device_time_set == false && isset($block_data_item['minute']))
                                            $block_data_item['time'] = date('Y-m-d\TH:i:s\Z', 946681200 + $block_data_item['minute'] * 60); // display as UTC from 2000-01-01 00:00:00
                                        
                                        $out['flashlog'][] = $block_data_item;

                                        if (empty($out['start_date'])) // first item
                                            $out['start_date'] = $block_data_item['time'];

                                        $out['end_date'] = $block_data_item['time']; // last port 3 item
                                    }
                                }
                                $modulo_counter++;
                                
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
                            return FlashLog::exportData($all_log_data, "user-$user_id-$device_name-log-file-$id-all-data", $export_csv);
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
        
        if ($show_log)
            $out['log'] = $out_log;

        return $out;
    }

    public function destroy(Request $request, $id)
    {
        return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
