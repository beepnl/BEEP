<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use App\Measurement;
use Validator;
use InfluxDB;
use Response;

/**
 * @group Traits\MeasurementLegacyCalculationsTrait
 * Legacy weight sensor calculations
 */
trait MeasurementLegacyCalculationsTrait
{

    // Sensor measurement functions
    protected function convertSensorStringToArray($data_string)
    {
        $out = [];
        if (strpos($data_string, "|") !== false)
        {
            $arr = explode("|", $data_string);
            foreach ($arr as $str) 
            {
                $str_arr = explode(":",$str);
                if (count($str_arr) > 1)
                    $out[$str_arr[0]] = $str_arr[1];
            }
        }
        return $out;
    }

    protected function calculateWeightKg($device, $data_array)
    {
        $totalWeight    = 0;
        $use_separate   = false;
        $separate_names = ['w_fl', 'w_fr', 'w_bl', 'w_br'];

        foreach ($separate_names as $name) 
        {
            if (isset($data_array[$name]) && $data_array[$name] > 0)
                $use_separate = true;
        }
        
        foreach($data_array as $name => $value)
        {
            if (($use_separate && in_array($name, $separate_names)) || (!$use_separate && $name == 'w_v')) // 4 names w_fl, w_fr, w_bl, w_br || 1 combined name w_v
            {
                // TODO: Make dependent of SensorDefinition

                $name_offset = $device->last_sensor_measurement_time_value($name.'_offset');
                $name_factor = $device->last_sensor_measurement_time_value($name.'_kg_per_val');
                
                if ($name_factor === 0)
                {
                    // exclude from calculation
                }
                else if ($name_factor !== null) // factor available
                {
                    $offset = isset($name_offset) ? floatval($name_offset) : 0;
                    $factor = floatval($name_factor);
                    $weight = $factor * (floatval($value) - $offset);
                    $totalWeight += $weight;
                    
                    //die("offset=$offset factor=$factor weight=$weight totalWeight=$totalWeight");
                }
                else
                {
                    $totalWeight += floatval($value);
                }
            }
        }
        //die(print_r($log));
        return $totalWeight;
    }

    private function floatify_sensor_val($arr, $key)
    {
        if (isset($arr[$key]))
        {
            $value = $arr[$key];

            if ($value == 0)
            {
                unset($arr[$key]);
            }
            else
            {
                switch($key)
                {
                    case 't':
                    case 't_i':
                        $arr[$key] = ($value / 5) - 10; // de-tempfy
                        break;
                    case 'h':
                        $arr[$key] = $value / 2;
                        break;
                    case 'bv':
                        $arr[$key] = $value / 10;
                        break;
                    case 'w_v':
                        $arr[$key] = $value;
                        break;
                    case 'w_fl':
                    case 'w_fr':
                    case 'w_bl':
                    case 'w_br':
                        $arr[$key] = $value / 300; // de-weightify
                        break;
                }
            }
        }

        return $arr;
    }
  

    private function calibrate_weight_sensors($device, $weight_kg, $next_measurement=true, $data_array=null)
    {
        if ($weight_kg == 0)
            return 'no-calibration-weight-error';

        if ($next_measurement)
        {
            $store  = ['calibrating_weight'=>$weight_kg];
            //die(print_r($store));
            $stored = $this->storeInfluxData($store, $device, time());
            if ($stored)
                return true;
        }
        else
        {
            $search_for    = ['w_fl'=>0, 'w_fr'=>0, 'w_bl'=>0, 'w_br'=>0, 'w_v'=>0];
            $calibrate     = [];
            $value_counter = 0;

            // set and count values to use
            foreach ($search_for as $sens => $value) 
            {
                $last_value = 0;

                if (isset($data_array[$sens]))
                    $last_value = $data_array[$sens];
                else
                    $last_value = $device->last_sensor_measurement_time_value($sens);
                
                $sens_offset = floatval($device->last_sensor_measurement_time_value($sens.'_offset'));
                $last_value = $last_value - $sens_offset;

                if (isset($last_value) && $last_value > 1)
                {
                    $calibrate[$sens.'_kg_per_val'] = $last_value;
                    $search_for[$sens]              = $last_value;
                    if ($sens != 'w_v')
                        $value_counter++;
                }
                else
                {
                    $calibrate[$sens.'_kg_per_val'] = 0;
                }
            }

            if (count($calibrate) == 0)
                return 'no-weight-values-error';

            $weight_part = $weight_kg / max(1, $value_counter);

            foreach ($calibrate as $sens => $value) 
                $calibrate[$sens] = $value != 0 ? $weight_part / $value : 0;

            // check if w_v should be added
            $combined_value = 0;
            if (isset($data_array['w_v']))
                $combined_value = $data_array['w_v'];
            else
                $combined_value = $device->last_sensor_measurement_time_value('w_v');

            if ($combined_value > 0)
            {
                if (isset($calibrate['w_v_kg_per_val']))
                    $this->createOrUpdateDefinition($device, 'w_v', 'weight_kg', null, $calibrate['w_v_kg_per_val']);
                else
                    $calibrate['w_v_kg_per_val'] = $weight_kg / $combined_value;
            }

            // switch 'off' calibrating mode
            $calibrate['calibrating_weight'] = 0;

            //die(print_r(['weight_kg'=>$weight_kg, 'values'=>$search_for, 'cal'=>$calibrate,'key'=>$device->key]));

            $stored = $this->storeInfluxData($calibrate, $device, time());
            if ($stored)
                return true;
        }

        return 'calibrate-error';
    }

    private function offset_weight_sensors($device)
    {
        $search_for    = ['w_fl', 'w_fr', 'w_bl', 'w_br', 'w_v'];
        $offset        = [];
        
        foreach ($search_for as $key) 
        {
            $value = $device->last_sensor_measurement_time_value($key);
            if ($value != null)
                $offset[$key.'_offset'] = $value;
        }

        if (count($offset) == 0)
            return 'no-weight-values-error';

        //die(print_r(['cal'=>$offset,'key'=>$device->key]));

        // create or update the new SensorDefinition
        if (isset($offset['w_v_offset']))
            $this->createOrUpdateDefinition($device, 'w_v', 'weight_kg', $offset['w_v_offset']);
        
        $stored = $this->storeInfluxData($offset, $device, time());
        if ($stored)
            return true;

        return 'offset-error';
    }


    private function add_weight_kg_corrected_with_temperature($device, $data_array)
    {
        if (isset($data_array['t']))
        {
            $sensor_names    = ['w_fl', 'w_fr', 'w_bl', 'w_br'];
            $temp_weight_arr = [];
            foreach ($sensor_names as $key) 
            {
                if(isset($data_array[$key]))
                {
                    $temp_weight_arr[$key] = $data_array[$key];
                }
            }
            
            if (count($temp_weight_arr) == 0) // no value available, so do not add
                return $data_array; 

            $diff_arr = $this->last_sensor_increment_values($device, $data_array);

            if (!isset($diff_arr['t']) || $data_array['weight_kg'] == 0)
                return $data_array;

            $diff_temp = $diff_arr['t'];
            
            // temp -> raw weight value correlation is 2 -> 1 = 0.5 W/T
            // So apply '- diff_arr['t'] * 0.5' to raw values and then calculate the total weight again (incl. offset and factor)
            $weight_per_temp = 0.35;
            
            // foreach ($temp_weight_arr as $sens => $val) 
            // {
            //     if (isset($diff_arr[$sens]))
            //     {
            //         // check if the value is dependent in the same direction, otherwise do not correct
            //         $sign                   = (($diff_temp >= 0 && $diff_arr[$sens] >= 0) || ($diff_temp < 0 && $diff_arr[$sens] < 0)) ? -1 : 0; 
            //         $temp_weight_arr[$sens] = $val + ($sign * $weight_per_temp * $diff_temp);
            //     }
            // }
            // // calculate corrected total weight
            // $temp_corr_weight = $this->calculateWeightKg($device, $temp_weight_arr);;
            //die(print_r(['tw'=>$temp_weight_arr, 'diff'=>$diff_arr, 'corr'=>$temp_corr_weight,'uncorr'=>$data_array['weight_kg']]));

            $sign             = (($diff_temp >= 0 && $diff_arr['weight_kg'] >= 0) || ($diff_temp < 0 && $diff_arr['weight_kg'] < 0)) ? -1 : 0; 
            $temp_corr_weight = $data_array['weight_kg'] + ($sign * $weight_per_temp * $diff_temp);

            if ($temp_corr_weight > 0)
                $data_array['weight_kg_corrected'] = $temp_corr_weight;
        }
        return $data_array;
    }


    // Public functions

    /**
    * api/sensors/calibrateweight
    * Legacy method, used by legacy webapp to store weight calibration value e.g.[w_v_kg_per_val] in Influx database, to lookup and calculate [weight_kg] at incoming measurement value storage
    *
    * At the next measurement coming in, calibrate each weight sensor with it's part of a given weight.
    * Because the measurements can come in only each hour/ 3hrs, set a value to trigger the calculation on next measurement
    *
    * 1. If $next_measurement == true: save 'calibrating' = true in Influx with the sensor key
    * 2. If $next_measurement == false: save 'calibrating' = false in Influx with the sensor key and...
    * 3.   Get the last measured weight values for this sensor key, 
    *      Divide the given weight (in kg) with the amount of sensor values > 1.0 (assuming the weight is evenly distributed)
    *      Calculate the multiplier per sensor by dividing the multiplier = weight_part / (value - offset)
    *      Save the multiplier as $device_name.'_kg_per_val' in Influx
    */
    public function calibrateweight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'next_measurement' => 'nullable|boolean',
            'weight_kg'        => 'numeric|required_if:next_measurement,true',
        ]);

        if ($validator->fails())
            return Response::json('validation-error', 500);

        $device     = $this->get_user_device($request, true); // requires id, key, hive_id, or nothing (if only one sensor) to be set
        $calibrated = false;

        if ($device)
        {
            $next_measurement = $request->filled('next_measurement') ? $request->input('next_measurement') : true;
            $weight_kg        = floatval($request->filled('weight_kg') ? $request->input('weight_kg') : $device->last_sensor_measurement_time_value('calibrating_weight'));
            $calibrated       = $this->calibrate_weight_sensors($device, $weight_kg, $next_measurement);
        }
        else
        {
            $calibrated = 'no_device_found';
        }
        if($calibrated === true)
        {
            if ($next_measurement)
                return Response::json("calibrating_weight", 200);
            else
                return Response::json("calibrated_weight", 201);
        }
        
        return Response::json($calibrated, 500);
    }

    public function offsetweight(Request $request)
    {
        $device = $this->get_user_device($request, true); // requires id, key, hive_id, or nothing (if only one sensor) to be set
        
        if ($device)
            $offset = $this->offset_weight_sensors($device);
        else
            $offset = 'no_device_found';

        if($offset === true)
            return Response::json("offset_weight", 201);
        
        return Response::json($offset, 500);
    }

    /**
    api/sensors/lastweight GET
    Request last weight related measurement values from a sensor (Device), used by legacy webapp to show calibration data: ['w_fl', 'w_fr', 'w_bl', 'w_br', 'w_v', 'weight_kg', 'weight_kg_corrected', 'calibrating_weight', 'w_v_offset', 'w_v_kg_per_val', 'w_fl_offset', 'w_fr_offset', 'w_bl_offset', 'w_br_offset']
    @authenticated
    @bodyParam key string DEV EUI to look up the sensor (Device)
    @bodyParam id integer ID to look up the sensor (Device)
    @bodyParam hive_id integer Hive ID to look up the sensor (Device)
    */
    public function lastweight(Request $request)
    {
        $weight = ['w_fl', 'w_fr', 'w_bl', 'w_br', 'w_v', 'weight_kg', 'weight_kg_corrected', 'calibrating_weight', 'w_v_offset', 'w_v_kg_per_val', 'w_fl_offset', 'w_fr_offset', 'w_bl_offset', 'w_br_offset'];
        $device = $this->get_user_device($request);

        if ($device)
            $output = $device->last_sensor_values_array(implode('","',$weight));
        else
            $output = false;

        if ($output === false)
            return Response::json('sensor-get-error', 500);
        else if ($output !== null)
            return Response::json($output);
    
        return Response::json('error', 404);
    }

}