<?php

namespace App\Http\Controllers\Api;

use App\Models\FlashLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;

/**
 * @group Api\FlashLogController
 */
class FlashLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $flashlogs = $request->user()->flashlogs()->orderByDesc('created_at')->get();
        return response()->json($flashlogs);
    }


    public function try(Request $request, $id)
    {
        /** Check
         * 0. Is the date of the last message somewhere near the upload date? 
         * 1. Start and end date of blocks (between port 2 messages)
         * 2. Is there any data in the database for these blocks of time
         * 3. Is the amount of data in the database less?
         * 4. 
         */
        $flashlog = FlashLog::findOrFail($id);
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
                    $data_array = json_decode($data_array);
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
