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

/**
 * @group Api\FlashLogController
 */
class FlashLogController extends Controller
{
    
    protected $timeFormat = 'Y-m-d H:i:s';

    /**
     * api/flashlogs GET
     * Provide a list of the available flashlogs
     * @authenticated
     */
    public function index(Request $request)
    {
        $flashlogs = $request->user()->flashlogs()->orderByDesc('created_at')->get();
        return response()->json($flashlogs);
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
        // $out      = [];
        // if(isset($flashlog->log_file_parsed))
        // {
        //     $file = 'flashlog/'.last(explode('/',$flashlog->log_file_parsed));
        //     //die(print_r($file));
        //     if (Storage::disk($disk)->exists($file))
        //     {
        //         $data_json = Storage::disk($disk)->get($file);
        //         if ($data_json)
        //             return response($data_json)->header('Content-Type', 'application/json');
        //     }
        //     else
        //     {
        //         return response()->json(['error'=>1,'message'=>'Flashlog file \''.$flashlog->log_file_parsed.'\' not found']);
        //     }
        // }
        // else
        // {
        //     return response()->json(['error'=>1,'message'=>'No parsed file present, first try parsing the flashlog file']);
        // }
    }


    private function parse(Request $request, $id)
    {
        $matches_min = $request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 2)); // minimum amount of inline measurements that should be matched 
        $match_props = $request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7)); // minimum amount of measurement properties that should match 
        $db_records  = $request->input('db_records', env('FLASHLOG_DB_RECORDS', 15));// amount of DB records to fetch to match each block
        
        $save_result = boolval($request->input('save_result', false));
        $from_cache  = boolval($request->input('from_cache', true));
        $block_id    = intval($request->input('block_id'));
        $block_data_i= intval($request->input('block_data_index', 0));
        $data_minutes= intval($request->input('data_minutes', 10080));
        
        $flashlog    = $request->user()->flashlogs()->find($id);
        $out         = ['error'=>'no_flashlog_found'];

        //$measurements= Measurement::getValidMeasurements(true);
        $measurements= Measurement::getMatchingMeasurements();

        if ($flashlog)
        {
            if(isset($flashlog->log_file))
            {
                $data = $flashlog->getFileContent('log_file');
                if (isset($data))
                {
                    $out = $flashlog->log($data, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache); // $data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0

                    // get the data from a single Flashlog block
                    if (isset($block_id) && isset($out['log'][$block_id]) && isset($out['log'][$block_id]['matches']))
                    {
                        $block       = $out['log'][$block_id];
                        $match_date  = $block['db_time'];
                        $match_index = $block['fl_i'];
                        $interval_min= $block['interval_min'];

                        $interval_tra= isset($block['transmission_ratio']) ? $block['transmission_ratio'] : 1;
                        $interval_tot= $interval_min * $interval_tra;
                        $index_amount= round($data_minutes / $interval_tot);
                        
                        // select portion of the data
                        $start_index = $match_index + ($index_amount * $block_data_i);
                        $end_index   = $match_index + ($index_amount * ($block_data_i+1));
                        $out         = ['match_date'=>$match_date, 'block_data_index'=>$block_data_i, 'index_amount'=>$index_amount, 'block_data_i'=>$block_data_i, 'match_index'=>$match_index, 'start_index'=>$start_index, 'end_index'=>$end_index, 'flashlog'=>[], 'database'=>[]];

                        // Add flashlog measurement data
                        $block_data  = json_decode($flashlog->getFileContent('log_file_parsed'));
                        for ($i=$start_index; $i<$end_index; $i++) 
                        { 
                            $block_data_item = $block_data[$i];
                            unset($block_data_item->payload_hex);
                            unset($block_data_item->pl);
                            unset($block_data_item->len);
                            unset($block_data_item->pl_bytes);
                            $out['flashlog'][] = $block_data_item;
                        }

                        // Add Database data
                        $start_mom   = new Moment($match_date);
                        $end_mom     = new Moment($match_date);
                        $start_time  = $start_mom->addMinutes($block_data_i * $data_minutes)->format($this->timeFormat);
                        $end_time    = $end_mom->addMinutes($block_data_i+1 * $data_minutes)->format($this->timeFormat);
                        $query       = 'SELECT "'.implode('","', $measurements).'" FROM "sensors" WHERE '.$flashlog->device->influxWhereKeys().' AND time >= \''.$start_time.'\' AND time >= \''.$end_time.'\' ORDER BY time ASC LIMIT '.$index_amount;
                        $out['database'] = Device::getInfluxQuery($query, 'flashlog');
                    }
                    else
                    {
                        $out = ['error'=>'no_matches_for_block_'.$block_id];
                    }
                }
                else
                {
                    $out = ['error'=>'no_flashlog_data'];
                }
            }
            else
            {
                $out = ['error'=>'no_flashlog_file'];
            }
        }
        return $out;
    }




    /**
     * api/flashlogs/{id}/try POST
     * Provide the contents of the log_file_parsed property of the flashlog
     * @authenticated
     */
    public function try(Request $request, $id)
    {
        /** Check
         * 0. Is the date of the last message somewhere near the upload date? 
         * 1. Start and end date of blocks (between port 2 messages)
         * 2. Is there any data in the database for these blocks of time
         * 3. Is the amount of data in the database less?
         * 4. 
         */
        $flashlog = $request->user()->flashlogs()->find($id);
        $disk     = env('FLASHLOG_STORAGE', 'public');
        $out      = [];
        if(isset($flashlog->log_file_parsed))
        {
            $file = 'flashlog/'.last(explode('/',$flashlog->log_file_parsed));
            //die(print_r($file));
            if (Storage::disk($disk)->exists($file))
            {
                $data_json = Storage::disk($disk)->get($file);
                if ($data_json)
                {
                    $data_array = json_decode($data_json);
                    $data_length= count($data_array)-1;
                    
                    if ($data_length < 2)
                        return response()->json(['error'=>1,'message'=>'Log file \''.$flashlog->log_file_parsed.'\' contains no data']);

                    // 0. Is the date of the last message somewhere near the upload date? 
                    $first_data = $data_array[0];
                    $last_data  = $data_array[$data_length];
                    $first_date = null; 
                    $last_date  = null; 
                    if (isset($first_data['time']))
                    {
                        $first_date = $first_data['time'];
                    }
                    if (isset($last_data['time']))
                    {
                        $last_date = $last_data['time'];
                        $last_mom  = new Moment($last_date);
                        $ul_dif_min= $last_mom->from($flashlog->created_at)->getMinutes();
                        if ($ul_dif_min > 60)
                            return response()->json(['error'=>1,'message'=>'Last date in log file \''.$flashlog->log_file_parsed.'\' differs '.$ul_dif_min.' minutes from upload date. It seems that your time setting was off']);
                    }

                    // 1. Start and end date of blocks (between port 2 messages)
                    $blocks    = [];
                    $block_i   = 0;
                    $block_end = null;
                    foreach ($data_array as $d) 
                    {
                        if (isset($d['port']))
                        {
                            if ($d['port'] == 2)
                            {
                                $blocks[$block_i] = ['start'=>$d['time']];
                                $block_i++;
                            }
                            else if ($d['port'] == 3 && isset($blocks[$block_i]))
                            {
                                $blocks[$block_i]['end'] = $d['time'];
                            }
                        }
                    }
                    return response()->json(['error'=>0,'message'=>'You can commit '.count($blocks).' data between '.$first_date.' and '.$last_date.' in log file \''.$flashlog->log_file_parsed.'\' to the database']);
                }
            }
            else
            {
                return response()->json(['error'=>1,'message'=>'Flashlog file \''.$flashlog->log_file_parsed.'\' not found']);
            }
        }
        else
        {
            return response()->json(['error'=>1,'message'=>'No flashlog file present, nothing to parse']);
        }
        return response()->json(['error'=>1,'message'=>'try_not_yet_implemented']);
    }

    public function commit(Request $request, $id)
    {
        return response()->json(['error'=>1,'message'=>'commit_not_yet_implemented']);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['error'=>1,'message'=>'destroy_not_yet_implemented']);
        //return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
