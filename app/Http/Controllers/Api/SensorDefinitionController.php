<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\SensorDefinition;
use App\Measurement;
use Illuminate\Http\Request;

/**
 * @group Api\SensorDefinitionController
 */
class SensorDefinitionController extends Controller
{
    private function getDeviceFromRequest(Request $request)
    {
        
        if ($request->filled('device_id'))
        {
            return $request->user()->devices()->findOrFail($request->input('device_id'));
        }
        else if ($request->filled('device_hardware_id'))
        {
            return $request->user()->devices()->where('device_hardware_id', strtolower($request->input('device_hardware_id')))->first();
        }
        return null;
    }

    private function getMeasurementFromRequestKey(Request $request, $output=true)
    {
        $request_id  = $output ? 'output_measurement_id' : 'input_measurement_id';
        $request_key = $output ? 'output_measurement_abbreviation' : 'input_measurement_abbreviation';

        if ($request->filled($request_id))
        {
            return Measurement::findOrFail($request->input($request_id));
        }
        else if ($request->filled($request_key))
        {
            return Measurement::where('abbreviation', $request->input($request_key))->first();
        }
        return null;
    }

    private function makeRequestDataArray(Request $request)
    {
        $measurement_in   = $this->getMeasurementFromRequestKey($request, false); 
        $measurement_out  = $this->getMeasurementFromRequestKey($request, true);    

        $request_data = $request->only('name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'device_id');

        $request_data['input_measurement_id']  = isset($measurement_in) ? $measurement_in->id : null;
        $request_data['output_measurement_id'] = isset($measurement_out) ? $measurement_out->id : null;

        return $request_data;
    }


    /**
     * api/sensordefinition GET
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sensordefinitions = $this->getDeviceFromRequest($request)->sensorDefinitions()->all();

        return $sensordefinitions;
    }

    /**
     * api/sensordefinition POST
     * Store a newly created resource in storage.
     *
     * @authenticated
     * @bodyParam name string Name of the sensorinstance (e.g. temperature frame 1)
     * @bodyParam inside boolean True is measured inside, false if measured outside
     * @bodyParam offset float Measurement value that defines 0
     * @bodyParam multiplier float Amount of units (calibration figure) per delta Measurement value to multiply withy (value - offset)
     * @bodyParam input_measurement_id integer Measurement that represents the input Measurement value (e.g. 5, 3). Example: 5
     * @bodyParam input_measurement_abbreviation string Abbreviation of the Measurement that represents the input value (e.g. w_v, or t_i). Example: w_v
     * @bodyParam output_measurement_id integer Measurement that represents the output Measurement value (e.g. 6, 3). Example: 6
     * @bodyParam output_measurement_abbreviation string Abbreviation of the Measurement that represents the output (calculated with (raw_value - offset) * multiplier) value (e.g. weight_kg, or t_i), Example: t_i
     * @bodyParam device_id integer Device that the Measurement value belongs to
     * @bodyParam device_hardware_id string required Device that the Measurement values belong to
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $device = $this->getDeviceFromRequest($request);

        if ($device)
        {
            $request_data     = new SensorDefinition($this->makeRequestDataArray($request));
            $sensordefinition = $device->sensorDefinitions()->save($request_data);
            return response()->json($sensordefinition, 201);
        }

        return response()->json('no_device_found', 404);
    }

    /**
     * api/sensordefinition/{id} GET
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $device = $this->getDeviceFromRequest($request);
        if ($device)
        {
            return response()->json($this->getDeviceFromRequest($request)->sensorDefinitions()->findOrFail($id), 200);
        }

        return response()->json('no_device_found', 404);
    }

    /**
     * api/sensordefinition PATCH
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $device = $this->getDeviceFromRequest($request);
        if ($device)
        {
            $sensordefinition = $device->sensorDefinitions()->findOrFail($id);
            $request_data     = $this->makeRequestDataArray($request);
            $sensordefinition->update($request_data);
            return response()->json($sensordefinition, 200);
        }
        return response()->json('no_device_found', 404);
    }

    /**
     * api/sensordefinition DELETE
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $device = $this->getDeviceFromRequest($request);
        if ($device)
        {
            $sensordefinition = $device->sensorDefinitions()->findOrFail($id);
            $sensordefinition->delete();
            return response()->json('sensor_definition_deleted', 204);
        }
        return response()->json('no_device_found', 404);
    }
}
