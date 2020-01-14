<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\SensorDefinition;
use Illuminate\Http\Request;

/**
 * @group Api\SensorDefinitionController
 */
class SensorDefinitionController extends Controller
{
    /**
     * api/sensordefinition GET
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sensordefinition = SensorDefinition::paginate(25);

        return $sensordefinition;
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
     * @bodyParam input_measurement_id Measurement that represents the input Measurement value (e.g. 5, 3). Example: 5
     * @bodyParam input_measurement_name Measurement that represents the input Measurement value (e.g. w_v, or t_i). Example: w_v
     * @bodyParam output_measurement_id Measurement that represents the output Measurement value (e.g. 6, 3). Example: 6
     * @bodyParam output_measurement_name Measurement that represents the output Measurement value (e.g. weight_kg, or t_i), Example: t_i
     * @bodyParam device_id integer Device that the Measurement value belongs to
     * @bodyParam device_hardware_id required Device that the Sensor and Measurement values belong to
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $sensordefinition = SensorDefinition::create($request->all());

        return response()->json($sensordefinition, 201);
    }

    /**
     * api/sensordefinition/{id} GET
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sensordefinition = SensorDefinition::findOrFail($id);

        return $sensordefinition;
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
        
        $sensordefinition = SensorDefinition::findOrFail($id);
        $sensordefinition->update($request->all());

        return response()->json($sensordefinition, 200);
    }

    /**
     * api/sensordefinition DELETE
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        SensorDefinition::destroy($id);

        return response()->json(null, 204);
    }
}
