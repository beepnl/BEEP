<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Device;
use App\User;
use App\Research;
use App\Hive;
use App\Location;
use App\Models\FlashLog;

class DeviceController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keyword     = $request->get('search');
        $search_user = $request->get('user');
        $search_res  = $request->get('research');
        $perPage     = 100;
        $devices     = Device::where('id', '!=', null);

        $research_id = null;
        if (!empty($search_res)) 
        {
            $researches = Research::where('name', 'LIKE', "%$search_res%")
                            ->orWhere('id', 'LIKE', "%$search_res%")
                            ->orWhere('institution', 'LIKE', "%$search_res%")
                            ->orWhere('description', 'LIKE', "%$search_res%")
                            ->orWhere('type', 'LIKE', "%$search_res%")
                            ->get();

            if (count($researches) > 0)
            {
                $device_ids = [];
                foreach ($researches as $res)
                { 
                    foreach ($res->users as $user) 
                        $device_ids = array_merge($device_ids, $user->devices->pluck('id')->toArray());
                }
                $devices = $devices->whereIn('id', $device_ids);
            }
        }

        if (!empty($search_user)) 
        {
            $user_ids = User::where('name', 'LIKE', "%$search_user%")
                        ->orWhere('email', 'LIKE', "%$search_user%")
                        ->orWhere('locale', 'LIKE', "%$search_user%")
                        ->orWhere('id', 'LIKE', "%$search_user%")
                        ->pluck('id');
            
            if (count($user_ids) > 0)
                $devices = $devices->whereIn('user_id', $user_ids);

        }

        if (!empty($keyword)) 
        {
            $devices = $devices->where('hive_id', 'LIKE', "%$keyword%")
                                ->orWhere('id', 'LIKE', "%$keyword%")
                                ->orWhere('name', 'LIKE', "%$keyword%")
                                ->orWhere('key', 'LIKE', "%$keyword%")
                                ->orWhere('former_key_list', 'LIKE', "%$keyword%")
                                ->orWhere('last_message_received', 'LIKE', "%$keyword%")
                                ->orWhere('hardware_id', 'LIKE', "%$keyword%")
                                ->orWhere('firmware_version', 'LIKE', "%$keyword%")
                                ->orWhere('hardware_version', 'LIKE', "%$keyword%")
                                ->orWhere('measurement_interval_min', 'LIKE', "%$keyword%")
                                ->orWhere('battery_voltage', 'LIKE', "%$keyword%")
                                ->orWhere('datetime', 'LIKE', "%$keyword%")
                                ->orWhere('datetime_offset_sec', 'LIKE', "%$keyword%");
        }


        $sensors = $devices->orderByDesc('last_message_received')->paginate($perPage);

        return view('devices.index',compact('sensors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = Category::descendentsByRootParentAndName('hive', 'app', 'sensor')->pluck('name','id');
        $users = User::all()->sortBy('name')->pluck('name', 'id');

        return view('devices.create',compact('types','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'key' => 'required',
            'user_id' => 'required',
            'category_id' => 'required',
        ]);

        $data = $request->all();

        $firstUserHive = Hive::where('user_id', $request->input('user_id'))->first();

        if (isset($firstUserHive))
            $data['hive_id'] = $firstUserHive->id;
        else
            return redirect()->route('devices.index')->with('error','Device not created; because user has no hive to add it to');

        Device::create($data);

        return redirect()->route('devices.index')
                        ->with('success','Device created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Device::find($id);
        return view('devices.show',compact('item'));
    }


    public function flashlog(Request $request, $id, $fl_id)
    {
        $matches_min = $request->input('matches_min', env('FLASHLOG_MIN_MATCHES', 2)); // minimum amount of inline measurements that should be matched 
        $match_props = $request->input('match_props', env('FLASHLOG_MATCH_PROPS', 7)); // minimum amount of measurement properties that should match 
        $db_records  = $request->input('db_records', env('FLASHLOG_DB_RECORDS', 15));// amount of DB records to fetch to match each block
        $save_result = boolval($request->input('save_result', false));

        $item     = Device::find($id);
        $flashlog = FlashLog::find($fl_id);
        $log      = null;

        if ($flashlog)
        {
            if(isset($flashlog->log_file))
            {
                $data = $flashlog->getFileContent('log_file');
                if (isset($data))
                    $log = $flashlog->log($data, null, $save_result, true, true, $matches_min, $match_props, $db_records, $save_result); // $data, $log_bytes=null, $save=true, $fill=false, $show=false
                else
                    return redirect()->route('devices.show', $id)->with('error', 'Flashlog file \''.$flashlog->log_file.'\' not found');
            }
            else
            {
                return redirect()->route('devices.show', $id)->with('error', 'No flashlog file present, nothing to parse');
            }
        }

        return view('devices.show',compact('item','flashlog','log', 'matches_min', 'match_props', 'db_records', 'save_result'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Device::find($id);
        $types = Category::descendentsByRootParentAndName('hive', 'app', 'sensor')->pluck('name','id');
        $users = User::all()->sortBy('name')->pluck('name', 'id');
        $all_hives = Hive::where('user_id',$item->user_id)->where('name','!=','')->orderBy('name')->get();
        $hives = [];
        foreach ($all_hives as $val) 
        {
            $loc = Location::where('id',$val->location_id)->limit(1)->value('name');
            $hives[$val->id] = $loc != '' ? $loc.' - '.$val->name : $val->name;
        }
        asort($hives, SORT_NATURAL);
        //die(print_r($types));

        return view('devices.edit',compact('item','types','users','hives'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'key' => 'required',
            'user_id' => 'required',
            'category_id' => 'required',
            'hive_id' => 'nullable|integer',
        ]);

        $data   = $request->all();
        $sensor = Device::findOrFail($id);

        if ($sensor->user_id != $request->input('user_id'))
        {
            $firstUserHive = Hive::where('user_id', $request->input('user_id'))->first();
            if (isset($firstUserHive))
                $data['hive_id'] = $firstUserHive->id;
            else
                return redirect()->route('devices.index')->with('error','Device not edited; because new user has no hive to add it to');
        }

        $sensor->update($data);

        return redirect()->route('devices.index')
                        ->with('success','Device updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Device::find($id)->delete();
        return redirect()->route('devices.index')
                        ->with('success','Device deleted successfully');
    }
}