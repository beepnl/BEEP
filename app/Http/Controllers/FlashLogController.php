<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FlashLog;
use App\Models\CalculationModel;
use App\User;
use App\Device;
use Storage;

class FlashLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $bytes       = $request->filled('mb') ? intval($request->get('mb')*1024*1024) : null;
        $log_parsed  = $request->filled('log_parsed') ? boolval($request->get('log_parsed')) : null;
        $log_has_ts  = $request->filled('log_has_timestamps') ? boolval($request->get('log_has_timestamps')) : null;
        $log_csv_url = $request->filled('csv_url') ? (boolval($request->get('csv_url')) ? '!=' : '=') : null; // != null / = null
        $search_user = $request->get('user');
        $search_dev  = $request->get('device');
        $device_id   = $request->get('device_id');
        $perPage     = 50;

        $flashlogs    = FlashLog::where('id', '!=', null);

        if (!empty($device_id)) 
        {
            $flashlogs = $flashlogs->where('device_id', $device_id);
        }
        if (!empty($search_dev)) 
        {
            $device_ids = Device::where('id', 'LIKE', "%$search_dev%")
                            ->orWhere('name', 'LIKE', "%$search_dev%")
                            ->orWhere('key', 'LIKE', "%$search_dev%")
                            ->orWhere('hardware_id', 'LIKE', "%$search_dev%")
                            ->pluck('id');

            if (count($device_ids) > 0)
            {
                $flashlogs = $flashlogs->whereIn('device_id', $device_ids);
            }
        }

        if (!empty($search_user)) 
        {
            $user_ids = User::where('name', 'LIKE', "%$search_user%")
                        ->orWhere('email', 'LIKE', "%$search_user%")
                        ->orWhere('id', 'LIKE', "%$search_user%")
                        ->pluck('id');
            
            if (count($user_ids) > 0)
                $flashlogs = $flashlogs->whereIn('user_id', $user_ids);

        }

        if (isset($bytes)) 
            $flashlogs = $flashlogs->where('bytes_received', '>', $bytes);

        if (isset($log_parsed)) 
            $flashlogs = $flashlogs->where('log_parsed', '=', $log_parsed);

        if (isset($log_has_ts)) 
            $flashlogs = $flashlogs->where('log_has_timestamps', '=', $log_has_ts);

        if (isset($log_csv_url)) 
            $flashlogs = $flashlogs->where('csv_url', $log_csv_url, null);

        //dd($bytes, $log_parsed);

        $flashlog = $flashlogs->orderByDesc('created_at')->paginate($perPage);


        return view('flash-log.index', compact('flashlog'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $flashlog = new FlashLog();
        return view('flash-log.create');
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
        
        $requestData = $request->all();
        
        FlashLog::create($requestData);

        return redirect('flash-log')->with('flash_message', 'FlashLog added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $flashlog = FlashLog::findOrFail($id);

        return view('flash-log.show', compact('flashlog'));
    }

    /**
     * Re-parse the specified flashlog.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function parse(Request $request, $id)
    {
        $fill_time= $request->filled('no_fill') && $request->input('no_fill') == 1 ? false : true;
        $fill_sdef= $request->filled('no_sensor_def') && $request->input('no_sensor_def') == 1 ? false : true;
        $fill_csv = $request->filled('csv') && $request->input('csv') == 1 ? true : false;
        $fill_meta= $request->filled('add_meta') && $request->input('add_meta') == 1 ? true : false;
        $load_show= $request->filled('load_show') && $request->input('load_show') == 1 ? true : false;
        $flashlog = FlashLog::findOrFail($id);
        $out      = [];
        
        $query_par= array_diff_key($request->query(), ['no_fill'=>0,'no_sensor_def'=>0,'csv'=>0, 'add_meta'=>0,'load_show'=>0]); // do not copy the command to the url params to prevent pressing wrong button
        if ($load_show)
            $query_par = $id;

        $route    = $load_show ? 'flash-log.show' : 'flash-log.index';
        
        //dd($query_par);
        if(isset($flashlog->log_file))
        {
            // Update bytes received if 0
            if ($flashlog->bytes_received == 0 && $flashlog->log_messages > 0)
            {
                $flashlog->bytes_received = $flashlog->getFileSizeBytes('log_file');
                $flashlog->save();
            }

            if (($fill_csv || $fill_meta) && isset($flashlog->log_parsed)) // use parsed log file to generate CSV
            {
                $flashlog_parsed_text = $flashlog->getFileContent('log_file_parsed');
                if (empty($flashlog_parsed_text))
                    return redirect()->route($route, $query_par)->with('error', "FlashLog $id log_file_parsed is empty");

                $flashlog_parsed_json = json_decode($flashlog_parsed_text, true);

                if ($fill_meta)
                    $fl_saved = $flashlog->addMetaToFlashlog($flashlog_parsed_json);
                else
                    $fl_saved = $flashlog->addCsvToFlashlog($flashlog_parsed_json);

                $type = $fill_meta ? 'Meta' : 'CSV';
                
                if ($fl_saved)
                {
                    $meta_str = CalculationModel::arrayToString($flashlog->meta_data, ', ', '', ['valid_data_points']);
                    return redirect()->route($route, $query_par)->with('success', "FlashLog $id $type set, Meta data: ".$meta_str);
                }

                return redirect()->route($route, $query_par)->with('error', "FlashLog $id $type save error");

            }
            else
            {

                $data = $flashlog->getFileContent('log_file');
                if (isset($data))
                {
                    // log($data='', $log_bytes=null, $save=true, $fill=?, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=false, $match_days_offset=0, $add_sensordefinitions=?)
                    $res  = $flashlog->log($data, null, true, $fill_time, false, null, null, null, false, false, 0, $fill_sdef);

                    foreach ($res as $key => $value) {
                        $out[] = "$key=$value"; 
                    }

                }
                else
                {
                    return redirect()->route($route, $query_par)->with('error', "FlashLog $id file '$flashlog->log_file' not found");
                }
            }
        }
        else
        {
            return redirect()->route($route, $query_par)->with('error', "FlashLog $id No flashlog file present, nothing to parse");
        }
        return redirect()->route($route, $query_par)->with('success', "FlashLog $id parsed again: ".implode(', ',$out));
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
        $flashlog = FlashLog::findOrFail($id);

        return view('flash-log.edit', compact('flashlog'));
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
        
        $requestData = $request->all();
        
        $flashlog = FlashLog::findOrFail($id);
        $flashlog->update($requestData);

        return redirect('flash-log')->with('flash_message', 'FlashLog updated!');
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
        FlashLog::destroy($id);

        return redirect('flash-log')->with('flash_message', 'FlashLog deleted!');
    }
}
