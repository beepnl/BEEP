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


    private function parse(Request $request, $id, $persist=false)
    {
        $matches_min = $request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 2)); // minimum amount of inline measurements that should be matched 
        $match_props = $request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7)); // minimum amount of measurement properties that should match 
        $db_records  = $request->input('db_records', env('FLASHLOG_DB_RECORDS', 15));// amount of DB records to fetch to match each block
        
        $save_result = boolval($request->input('save_result', false));
        $from_cache  = boolval($request->input('from_cache', true));
        $block_id    = $request->input('block_id');
        $block_data_i= intval($request->input('block_data_index', -1));
        $data_minutes= intval($request->input('data_minutes', 10080));
        
        $flashlog    = $request->user()->flashlogs()->find($id);
        $out         = ['error'=>'no_flashlog_found'];

        //$measurements= Measurement::getValidMeasurements(true);
        $measurements = Measurement::getMatchingMeasurements();

        if ($flashlog)
        {
            if(isset($flashlog->log_file))
            {
                $data = $flashlog->getFileContent('log_file');
                if (isset($data))
                {
                    $out = $flashlog->log($data, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache); // $data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0

                    // get the data from a single Flashlog block
                    if (isset($block_id))
                    {
                        if (isset($out['log'][$block_id]) && isset($out['log'][$block_id]['matches']))
                        {
                            $block         = $out['log'][$block_id];
                            $match_index   = $block['fl_i'];
                            $interval_min  = $block['interval_min'];
                            $index_amount  = round($data_minutes / $interval_min);
                            
                            $block_start_i = $block['start_i'];
                            $block_end_i   = $block['end_i'];
                            $data_i_max    = floor(($block_end_i - $block_start_i) / $index_amount);
                            
                            if ($block_data_i == -1)
                                $block_data_i= floor($match_index / $index_amount);

                            // select portion of the data
                            $start_index = $block_start_i + ($index_amount * $block_data_i);
                            $end_index   = min($block_end_i, $block_start_i + ($index_amount * ($block_data_i+1)));

                            $out         = ['block_start_i'=>$block_start_i, 'block_end_i'=>$block_end_i, 'match_index'=>$match_index, 'block_data_index'=>$block_data_i, 'block_data_index_max'=>$data_i_max, 'block_data_index_amount'=>$index_amount, 'block_data_start'=>$start_index, 'block_data_end'=>$end_index, 'flashlog'=>[], 'database'=>[]];

                            // Add flashlog measurement data
                            $block_data  = json_decode($flashlog->getFileContent('log_file_parsed'));
                            for ($i=$start_index; $i<$end_index; $i++) 
                            { 
                                $block_data_item = $block_data[$i];
                                $block_data_item->time .= 'Z'; // display as UTC
                                unset($block_data_item->payload_hex);
                                unset($block_data_item->pl);
                                unset($block_data_item->len);
                                unset($block_data_item->vcc);
                                unset($block_data_item->pl_bytes);
                                unset($block_data_item->beep_base);
                                unset($block_data_item->weight_sensor_amount);
                                unset($block_data_item->ds18b20_sensor_amount);
                                unset($block_data_item->port);
                                unset($block_data_item->minute_interval);
                                unset($block_data_item->bat_perc);
                                unset($block_data_item->fft_bin_amount);
                                unset($block_data_item->fft_start_bin);
                                unset($block_data_item->fft_stop_bin);
                                //unset($block_data_item->i);

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
                            }
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
                $out = ['error'=>'no_flashlog_file'];
            }
        }
        return $out;
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['error'=>'destroy_not_yet_implemented']);
        //return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
