<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

use App\SensorDefinition;
use App\Measurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @group Api\SensorDefinitionController
 * Manage your sensor definitions
 * @authenticated
 */
class SensorDefinitionController extends Controller
{
    private function getDeviceFromRequest(Request $request)
    {
        
        if ($request->filled('device_id'))
        {
            return $request->user()->devices()->findOrFail($request->input('device_id'));
        }
        else if ($request->filled('hardware_id'))
        {
            return $request->user()->devices()->where('hardware_id', strtolower($request->input('hardware_id')))->first();
        }
        else if ($request->filled('device_hardware_id'))
        {
            return $request->user()->devices()->where('hardware_id', strtolower($request->input('device_hardware_id')))->first();
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

        // Change output weight measurement for React native app
        if ($request->hasHeader('X-ClientId') && ($request->header('X-ClientId') == 'android' || $request->header('X-ClientId') == 'ios')) 
        {
            if (isset($measurement_in) && $measurement_in->abbreviation == 'w_v')
            {
                if (isset($measurement_out) == false || $measurement_out->abbreviation == 'w_v')
                    $measurement_out = Measurement::where('abbreviation', 'weight_kg')->first();
            }
        }


        $request_data = $request->only('name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'device_id');

        $request_data['input_measurement_id']  = isset($measurement_in) ? $measurement_in->id : null;
        $request_data['output_measurement_id'] = isset($measurement_out) ? $measurement_out->id : (isset($measurement_in) ? $measurement_in->id : null);

        if (isset($measurement_in) && $measurement_in->abbreviation == 'w_v' && isset($request_data['offset']) && isset($request_data['multiplier']) && $request_data['offset'] == 0 && $request_data['multiplier'] == 0)
        {
            $in_abbr  = $measurement_in->abbreviation;
            $out_abbr = isset($measurement_out) ? $measurement_out->abbreviation : 'null';
            Log::error("SensorDefinition request in: $in_abbr, out: $out_abbr, weight has empty multiplier and offset:");
            Log::error(json_encode($request_data));
            Log::error('Set multiplier to: 1');
            $request_data['multiplier'] = 1;
        }

        if ($request->filled('inside'))
        {
            if ($request_data['inside'] === -1)
                $request_data['inside'] = null;
            else
                $request_data['inside'] = $request_data['inside'] === "true" || $request_data['inside'] === true || $request_data['inside'] == 1 ? 1 : 0;
        }

        if ( (!isset($request_data['name']) || empty($request_data['name'])) )
        {
            if (isset($measurement_out))
                $request_data['name'] = $measurement_out->pq_name().' '.__('beep.calibrated');
            else if (isset($measurement_in))
                $request_data['name'] = $measurement_in->pq_name().' '.__('beep.calibrated');
        }

        if ($request->filled('updated_at')) {
            $updated_date_utc = $request->input('updated_at');
            $updated_date_db  = substr($updated_date_utc, 0, 10).' '.substr($updated_date_utc, 11, 8); // strip timezone
            $request_data['updated_at'] = date('Y-m-d H:i:s', strtotime($updated_date_db));
            //Log::debug("CalibrationController makeRequestDataArray updated_date_utc: $updated_date_utc, updated_date_db: $updated_date_db");
        } elseif (isset($calibration['updated_at'])) {
            $request_data['updated_at'] = $calibration->updated_at; // do NOT update the calibration date, by setting the 'updated_at' to the available date
        }

        return $request_data;
    }


    /**
     * api/sensordefinition GET
     * Display a listing of all sensordefinitions that belong to a device
     * @authenticated
     * @bodyParam device_id integer Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
     * @bodyParam hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
     * @bodyParam device_hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
     * @bodyParam input_measurement_abbreviation string Filter sensordefinitions by provided input abbreviation.
     * @bodyParam limit integer If input_abbr is set, limit the amount of results provided by more than 1 to get all historic sensordefinitions of this type.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $device = $this->getDeviceFromRequest($request);

        if ($device)
        {
            
            if ($request->filled('input_measurement_abbreviation'))
            {
                $measurement_in    = $this->getMeasurementFromRequestKey($request, false);
                $limit             = $request->filled('limit') ? intVal($request->input('limit')) : 1;

                if (isset($measurement_in))
                    $sensordefinitions = $device->sensorDefinitions->where('input_measurement_id', $measurement_in->id)->sortByDesc('updated_at')->take($limit)->values();
                else
                    $sensordefinitions = $device->sensorDefinitions->sortByDesc('updated_at')->take($limit)->values();
            }
            else
            {
                $sensordefinitions = $device->sensorDefinitions->sortBy('updated_at')->values(); // Bugfix iOS app: sort by Asc to get last in iOS app. 
            }

            if ($sensordefinitions)
                return response()->json($sensordefinitions);
        }
        else
        {
            return response()->json('no_device_found', 404);
        }

        return response()->json('no_definitions_found', 404);
    }

    /**
     * api/sensordefinition POST
     * Store a newly created sensordefinition
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
     * @bodyParam device_id integer Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
     * @bodyParam hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
     * @bodyParam device_hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Log::debug('sensordefinition_post');
        //Log::debug($request->input());
        
        $device = $this->getDeviceFromRequest($request);

        if ($device)
        {
            $request_data     = new SensorDefinition($this->makeRequestDataArray($request));
            $sensordefinition = $device->sensorDefinitions()->save($request_data);
            return response()->json($sensordefinition, 201);
        }

        Log::error('sensordefinition_storage_error: '.json_encode($request->input()));

        return response()->json('no_device_found', 404);
    }

    /**
     * api/sensordefinition/{id} GET
     * Display the specified sensordefinition
     * @authenticated
     * @urlParam id required Sensordefinition ID
     * @bodyParam device_id integer Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
     * @bodyParam hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
     * @bodyParam device_hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
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
     * api/sensordefinition/{id} PATCH
     * Update the specified sensordefinition
     * @authenticated
     * @urlParam id required Sensordefinition ID
     * @bodyParam device_id integer Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
     * @bodyParam hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
     * @bodyParam device_hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
     */
    public function update(Request $request, $id)
    {
        $device = $this->getDeviceFromRequest($request);
        if ($device)
        {
            $sensordefinition = $device->sensorDefinitions()->findOrFail($id);
            $request_data     = $this->makeRequestDataArray($request);
            // $request_data = $request->only('name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'device_id'); 
            
            // if ($request->filled('inside'))
            // {
            //     if ($request_data['inside'] === -1)
            //         $request_data['inside'] = null;
            //     else
            //         $request_data['inside'] = $request_data['inside'] === "true" || $request_data['inside'] === true || $request_data['inside'] == 1 ? 1 : 0;
            // }
            if (isset($request_data['updated_at']))
                $updated_at = $request_data['updated_at'];

            $sensordefinition->update($request_data);
            
            if (isset($request_data['updated_at']))
            {
                $sensordefinition->updated_at = $updated_at;
                $sensordefinition->save(['timestamps' => false]);
            }

            return response()->json($sensordefinition, 200);
        }
        return response()->json('no_device_found', 404);
    }

    /**
     * api/sensordefinition/{id} DELETE
     * Remove the specified sensordefinition
     * @authenticated
     * @urlParam id required Sensordefinition ID
     * @bodyParam device_id integer Device ID that the Sensordefinition belongs to. Required if hardware_id, and device_hardware_id are not set.
     * @bodyParam hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if device_id, and device_hardware_id are not set.
     * @bodyParam device_hardware_id string Device hardware ID that the Sensordefinition belongs to. Required if hardware_id, and device_id are not set.
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
