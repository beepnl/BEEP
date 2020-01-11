<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\DeviceMeasurement;
use Illuminate\Http\Request;

/**
 * @group Api\DeviceMeasurementController
 */
class DeviceMeasurementController extends Controller
{
    /**
     * api/device-measurement GET
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $devicemeasurement = DeviceMeasurement::paginate(25);

        return $devicemeasurement;
    }

    /**
     * api/device-measurement POST
     * Store a newly created resource in storage.
     *
     * @authenticated
     * @bodyParam name string Name of the sensorinstance (e.g. temperature frame 1)
     * @bodyParam inside boolean True is measured inside, false if measured outside
     * @bodyParam zero_value float Measurement value that defines 0
     * @bodyParam unit_per_value float Amount of units (calibration figure) per delta Measurement value to multiply withy (value - zero_value)
     * @bodyParam measurement_id Measurement id that the Measurment value belongs to
     * @bodyParam physical_quantity_id PhysicalQuantity that the Measurment value belongs to
     * @bodyParam sensor_id integer required Sensor that the Measurement value belongs to
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $devicemeasurement = DeviceMeasurement::create($request->all());

        return response()->json($devicemeasurement, 201);
    }

    /**
     * api/device-measurement/{id} GET
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $devicemeasurement = DeviceMeasurement::findOrFail($id);

        return $devicemeasurement;
    }

    /**
     * api/device-measurement PATCH
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $devicemeasurement = DeviceMeasurement::findOrFail($id);
        $devicemeasurement->update($request->all());

        return response()->json($devicemeasurement, 200);
    }

    /**
     * api/device-measurement DELETE
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DeviceMeasurement::destroy($id);

        return response()->json(null, 204);
    }
}
