<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Sensor;
use App\User;
use App\Hive;
use App\Location;

class SensorController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sensors = Sensor::all(); //->paginate(10);
        return view('sensors.index',compact('sensors'));
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

        return view('sensors.create',compact('types','users'));
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
            return redirect()->route('sensors.index')->with('error','Sensor not created; because user has no hive to add it to');

        Sensor::create($data);

        return redirect()->route('sensors.index')
                        ->with('success','Sensor created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Sensor::find($id);
        return view('sensors.show',compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = Sensor::find($id);
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

        return view('sensors.edit',compact('item','types','users','hives'));
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
        $sensor = Sensor::findOrFail($id);

        if ($sensor->user_id != $request->input('user_id'))
        {
            $firstUserHive = Hive::where('user_id', $request->input('user_id'))->first();
            if (isset($firstUserHive))
                $data['hive_id'] = $firstUserHive->id;
            else
                return redirect()->route('sensors.index')->with('error','Sensor not edited; because new user has no hive to add it to');
        }

        $sensor->update($data);

        return redirect()->route('sensors.index')
                        ->with('success','Sensor updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Sensor::find($id)->delete();
        return redirect()->route('sensors.index')
                        ->with('success','Sensor deleted successfully');
    }
}