<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Hive;
use App\Models\DashboardGroup;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Validator;
use Str;

/**
 * @group Api\DashboardGroupController
 * Store and retreive DashboardGroups to create public dashboard with a fixed set of measurements
 * @authenticated
 */
class DashboardGroupController extends Controller
{
    /**
    api/dashboardgroups GET
    List all user Dashboard groups
    @authenticated
    **/
    public function index(Request $request)
    {
        $dgroup = $request->user()->dashboardGroups();
        return response()->json($dgroup);
    }

    /**
    api/dashboard/{sode} GET
    Get public user Dashboard groups
    @urlParam hive_id integer Hive ID of which the data should be loaded
    @authenticated
    **/
    public function public(Request $request, string $code)
    {
        $inputs         = $request->inputs();
        $inputs['code'] = strip_tags($code);

        $validator = Validator::make($inputs, [
            'code'    => 'required|string|min:6|exists:dashboardgroups,code',
            'hive_id' => 'required|integer|exists:dashboardgroups,hive_ids',
        ]);
        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);

        $dgroup = $request->user()->dashboardGroups();

        if ($request->filled('hive_id'))
        {
            $hive = $dgroup::hives()->find($request->input('hive_id'));
            if ($hive)
            {
                $has_devices = $hive->hasDevices();

                $hive_array = [];
                $hive_array['name'] = $hive->name;
                $hive_array['location_name'] = $hive->location; 
                $hive_array['lon'] = isset($hive->location) ? $hive->location->coordinate_lon : ''; 
                $hive_array['lat'] = isset($hive->location) ? $hive->location->coordinate_lat : ''; 
                $hive_array['last_inspection_date'] = $dgroup->show_inspections ? $hive->last_inspection_date : ''; 
                $hive_array['impression'] = $dgroup->show_inspections ? $hive->impression : ''; 
                $hive_array['notes'] = $dgroup->show_inspections ? $hive->notes : ''; 
                $hive_array['device_online'] = $has_devices ? $hive->devices->first()->online : ''; 
                $hive_array['measurements'] = []; 
                $hive_array['inspections'] = []; 

                return response()->json($hive_array);
            }
        }

        return response()->json($dgroup);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'hive_ids.*'        => 'required|exists:hives,id',
            'interval'          => ['required', Rule::in(DashboardGroup::$intervals)],
            'speed'             => 'required|integer|min:1|max:84600',
            'name'              => 'nullable|string',
            'description'       => 'nullable|string',
            'logo_url'          => 'nullable|url',
            'show_inspections'  => 'boolean',
            'show_all'          => 'boolean',
            'hide_measurements' => 'boolean',
        ]);
        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);

        $dgArray         = $request->all();
        $dgArray['code'] = strtoupper(Str::random(6));

        $dgroup = $request->user()->dashboardGroups()->create($dgArray);

        return response()->json($dgroup, 201);
    }


    public function show($id)
    {
        $dgroup = $request->user()->dashboardGroups()->findOrFail($id);
        return $dgroup;
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->input(), [
            'hive_ids.*'        => 'required|exists:hives,id',
            'interval'          => ['required', Rule::in(DashboardGroup::$intervals)],
            'speed'             => 'required|integer|min:1|max:84600',
            'name'              => 'nullable|string',
            'description'       => 'nullable|string',
            'logo_url'          => 'nullable|url',
            'show_inspections'  => 'boolean',
            'show_all'          => 'boolean',
            'hide_measurements' => 'boolean',
        ]);
        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);

        $dgroup = $request->user()->dashboardGroups()->findOrFail($id);
        $dgroup->update($request->all());

        return response()->json($dgroup, 200);
    }


    public function destroy($id)
    {
        $request->user()->dashboardGroups()->destroy($id);

        return response()->json(null, 204);
    }
}
