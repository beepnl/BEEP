<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Device;
use App\User;
use App\Hive;
use App\Location;

class DeviceController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sensors = Device::all(); //->paginate(10);
        return view('devices.index',compact('sensors'));
            // ->with('i', ($request->input('page', 1) - 1) * 10);
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