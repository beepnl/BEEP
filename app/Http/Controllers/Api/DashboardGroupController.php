<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\MeasurementController;
use App\Http\Requests;

use App\Hive;
use App\Measurement;
use App\Models\DashboardGroup;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Validator;
use Str;
use Cache;

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
        $dgroup = $request->user()->dashboardGroups;
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
        $inputs         = $request->all();
        $inputs['code'] = strip_tags($code);

        $validator = Validator::make($inputs, [
            'code'    => 'required|string|min:6|exists:dashboard_groups,code',
            'hive_id' => 'nullable|integer|exists:hives,id',
        ]);
        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);

        // Create output
        $hive_id= $request->filled('hive_id') ? $request->input('hive_id') : null;
        
        $out = Cache::remember('dashboard-code'.$code.'-hive-'.$hive_id.'-data', env('CACHE_TIMEOUT_LONG'), function () use ($code, $hive_id)
        { 
            $out    = [];
            $dgroup = DashboardGroup::where('code', $code)->first();
            if ($dgroup)
            {
                $out  = [];
                
                if (is_array($dgroup->hive_ids) && count($dgroup->hive_ids) > 0)
                {
                    // only on first meta call
                    if ($hive_id === null)
                    {
                        $out = $dgroup->toArray();
                        $out['sensormeasurements'] = Measurement::all(); 
                    }
                    
                    $user = $dgroup->user;
                    $hives= $dgroup->hives;
                
                    $out['hives'] = [];
                    foreach($hives as $hive)
                    {
                        if ($hive && (!isset($hive_id) || $hive->id == $hive_id)) // all or only selected hive
                        {   
                            $hive_array                  = [];
                            
                            $device                      = $hive->hasDevices() ? $hive->devices->first() : null;
                            $hive_array['device_online'] = isset($device) ? $device->online : '';

                            if (isset($hive_id)) // request specific hive data
                            {
                                $hive_array['last_inspection_date'] = $dgroup->show_inspections ? $hive->last_inspection_date : ''; 
                                $hive_array['impression'] = $dgroup->show_inspections ? $hive->impression : ''; 
                                $hive_array['notes'] = $dgroup->show_inspections ? $hive->notes : '';

                                $apiary                      = isset($hive->location_id) ? $hive->location()->first() : null;
                                $hive_array['lat']           = isset($apiary) ? $apiary->coordinate_lat : ''; 
                                $hive_array['lon']           = isset($apiary) ? $apiary->coordinate_lon : '';
                                $hive_array['location_name'] = $hive->location; 

                                //$last_connection = $device->last_message_received;

                                if (isset($device))
                                {
                                    $data_request = [
                                        'id' => $device->id,
                                        'hive_id' => $hive->id,
                                        'index' => 0,
                                        'interval' => $dgroup->interval,
                                        'relative_interval' => 1,
                                    ];
                                    $request = new Request($data_request);
                                    $request->setUserResolver(function () use ($user) {
                                        return $user;
                                    });
                                    $result = (new MeasurementController)->data($request);
                                    if ($result->getStatusCode() == 200)
                                    {
                                        $m   = $result->original;
                                        $cnt = count($m['measurements']);
                                        if ($cnt > 0)
                                        {
                                            $hive_array['index']            = $m['index']; 
                                            $hive_array['interval']         = $m['interval']; 
                                            $hive_array['relative_interval']= $m['relative_interval']; 
                                            $hive_array['resolution']       = $m['resolution']; 
                                            $hive_array['sensorDefinitions']= $m['sensorDefinitions']; 
                                            $hive_array['measurements']     = $m['measurements']; 
                                            $hive_array['start']            = $m['measurements'][0]['time']; 
                                            $hive_array['end']              = $m['measurements'][$cnt-1]['time'];
                                        }
                                    }
                                }
                            }
                            else // request meta data
                            {
                                $hive_array['id']      = $hive->id;
                                $hive_array['name']    = $hive->name;
                                $hive_array['sensors'] = $hive->sensors;
                                $hive_array['layers']  = $hive->layers;
                            }

                            $out['hives'][] = $hive_array;
                        }
                    }
                }
            }
            return $out;
        });

        if (count($out) == 0)
            return response()->json(null, 404);
        
        return response()->json($out);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'hive_ids.*'        => 'required|exists:hives,id',
            'interval'          => ['required', Rule::in(array_keys(DashboardGroup::$intervals))],
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
            'interval'          => ['required', Rule::in(array_keys(DashboardGroup::$intervals))],
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

        // Empty cache
        $code = $dgroup->code;
        Cache::forget('dashboard-code'.$code.'-hive-null-data');
        foreach ($dgroup->hive_ids as $hive_id)
            Cache::forget('dashboard-code'.$code.'-hive-'.$hive_id.'-data');

        $dgroup->update($request->all());


        return response()->json($dgroup, 200);
    }


    public function destroy(Request $request, $id)
    {
        $g = $request->user()->dashboardGroups()->findOrFail($id);

        if ($g){
            $g->destroy($id);
            return response()->json(null, 204);
        }
        return response()->json(null, 404);
    }
}
