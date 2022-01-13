<?php

namespace App\Http\Controllers\Api;

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
     * @bodyParam id integer required Flashlog ID to parse
     * @bodyParam matches_min integer Flashlog minimum amount of inline measurements that should be matched. Default: 2. Example: 2  
     * @bodyParam match_props integer Flashlog minimum amount of measurement properties that should match. Default: 7. Example: 7  
     * @bodyParam db_records integer Flashlog minimum amount of inline measurements that should be matched. Default: 15. Example: 15 
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
        $block_id    = $request->input('block_id');
        
        $flashlog    = $request->user()->flashlogs()->find($id);
        $out         = ['error'=>'no_flashlog_found'];

        if ($flashlog)
        {
            if(isset($flashlog->log_file))
            {
                $data = $flashlog->getFileContent('log_file');
                if (isset($data))
                {
                    $out = $flashlog->log($data, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result, $from_cache);
                    
                    // get the data from a single Flashlog block
                    if (isset($block_id) && isset($out[$block_id]))
                        $out = $out[$block_id];
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
