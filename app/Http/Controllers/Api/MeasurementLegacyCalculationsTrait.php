<?php
namespace App\Http\Controllers\Api;

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
use Moment\Moment;
use League\Fractal;
use App\Http\Requests\PostSensorRequest;

/**
 * @group Api\MeasurementLegacyCalculationsTrait
 * Legacy weight sensor calculations
 */
trait MeasurementLegacyCalculationsTrait
{

    // Sensor measurement functions
    protected function convertSensorStringToArray($data_string)
    {
        $out = [];
        $arr = explode("|", $data_string);
        foreach ($arr as $str) 
        {
            $str_arr = explode(":",$str);
            if (count($str_arr) > 1)
                $out[$str_arr[0]] = $str_arr[1];
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

                $name_offset = $this->last_sensor_measurement_time_value($device, $name.'_offset');
                $name_factor = $this->last_sensor_measurement_time_value($device, $name.'_kg_per_val');
                
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

    private function decode_simpoint_payload($data)
    {
        $out = [];
        
        if (isset($data['payload_hex']) == false)
            return $out;

        // distighuish BEEP base v2 and v3 payload

        $payload = $data['payload_hex'];
        $port    = $data['FPort'];

        if ($port != 1) // BEEP base v3 firmware
        {
            $p  = strtolower($payload);
            $pu = strtoupper($payload);

            if ($port == 2)
            {
                if (substr($p, 0, 2) == '01' && (strlen($p) == 52 || strlen($p) == 60)) // BEEP base fw 1.3.3+ start-up message)
                {
                    $out['beep_base'] = true;
                    // 0100010003000502935cbdd3ffff94540e0123af9aed3527beee1d000001
                    // 0100010003000402935685E6FFFF94540E01237A26A67D24D8EE1D000001
                    //                                                 0e01236dada5c40a28ee
                    // 01 00 01 00 03 00 04 02 93 56 85 E6 FF FF 94 54 0E 01 23 7A 26 A6 7D 24 D8 EE 1D 00 00 01 
                    // 0  1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 
                    //    pl fw version     hw version                 ATTEC ID (14)                 app config

                    $out['firmware_version'] = hexdec(substr($p, 2, 4)).'.'.hexdec(substr($p, 6, 4)).'.'.hexdec(substr($p, 10, 4)); // 2-13
                    // $out['hardware_version'] = hexdec(substr($p, 16, 16)); // 14-31
                    $out['hardware_id']      = substr($p, 32, 20); // 32-52
                    
                    if (strlen($p) > 52)
                    {
                        $out['measurement_transmission_ratio'] = hexdec(substr($p, 54, 2)); 
                        $out['measurement_interval_min']       = hexdec(substr($p, 56, 4)); 
                    }
                }
            }
            else if ($port == 3 || $port == 4)
            {
                if (($port == 3 && substr($pu, 0, 2) == '1B') || ($port == 4 && substr($pu, 2, 2) == '1B'))  // BEEP base fw 1.2.0+ measurement message, and alarm message
                {
                    $out['beep_base'] = true;
                    //              1B 0C 1B 0C 0E 64  0A 01 FF F6 98  04 02 0A D7 0A DD  0C 0A 00 FF 00 58 00 12 00 10 00 0C 00 0D 00 0A 00 0A 00 09 00 08 00 07  07 00 00 00 00 00 00
                    // pl incl fft: 1B 0D 15 0D 0A 64  0A 01 00 00 93  04 00              0C 0A 00 FF 00 20 00 05 00 0C 00 03 00 05 00 09 00 04 00 11 00 06 00 02  07 00 00 00 00 00 00
                    //              0  1  2  3  4  5   6  7  8  9  10  11 12              13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36  37 38 39 40 41 42 43
                    //              0  2  4  6  8  10  12 14 16 18 20  22 24              26 28 30 32 34 36 38 40 42 44 46 48 50 52 54 56 58 60 62 64 66 68 70 72  74 76 78 80 82 84 86
                    //                 Batt            Weight          Temp               FFT                                                                      BME280
                    // raw pl  1B0C4B0C44640A01012D2D040107D6
                    // Payload 1B 0C4B0C4464 0A 01 012D2D 04 01 07D6
                    //         6  batt       5  1 weight  5 1-5 temp (1 to 5)

                    $sb = $port == 4 ? 2 : 0; // start byte

                    // add battery
                    $out['bv']       = hexdec(substr($p, $sb+2, 4))/1000;
                    $out['bat_perc'] = hexdec(substr($p, $sb+8, 2));

                    // add weight
                    $weight_amount   = hexdec(substr($p, $sb+14, 2));
                    $out['weight_sensor_amount'] = $weight_amount;
                    
                    if ($weight_amount == 1)
                    {
                        $out['w_v'] = hexdec(substr($p, $sb+16, 6));
                    }
                    else if ($weight_amount > 1)
                    {
                        for ($i=0; $i < $weight_amount; $i++)
                        { 
                            $out['w_v_'.$i] = hexdec(substr($p, $sb+16+($i*6), 6));
                        }
                    }

                    // add temperature
                    $sb            = 16 + $weight_amount * 6;
                    $temp_amount   = hexdec(substr($p, $sb+2, 2));
                    $out['ds18b20_sensor_amount'] = $temp_amount;
                    
                    if ($temp_amount == 1)
                    {
                        $out['t_i'] = hexdec(substr($p, $sb+4, 4))/100;
                    }
                    else if ($temp_amount > 1)
                    {
                        for ($i=0; $i < $temp_amount; $i++)
                        { 
                            $out['t_'.$i] = hexdec(substr($p, $sb+4+($i*4), 4))/100;
                        }
                    }

                    // add sound
                    $sb           = $sb + 4 + $temp_amount * 4;
                    $fft_amount   = hexdec(substr($p, $sb+2, 2));

                    $fft_bin_freq           = 3.937752016; // = about 2000 / 510
                    $out['fft_bin_amount']  = $fft_amount;
                    $out['fft_start_bin']   = hexdec(substr($sb+4, 2));
                    $out['fft_stop_bin']    = hexdec(substr($sb+6, 2));
                    $fft_bin_total          = $out['fft_stop_bin'] - $out['fft_start_bin'];
                    $fft_sb                 = $sb + 8;
                    
                    // if ($fft_amount > 0)
                    // {
                    //     for ($i=0; $i < $fft_amount; $i++)
                    //     { 
                    //         $out['s_bin_'.$i] = hexdec(substr($p, $sb+4+($i*4), 4));
                    //     }
                    // }

                    // // add bme
                    // $sb           = $sb + 4 + $temp_amount * 4;
                    // $fft_amount   = hexdec(substr($p, $sb+2, 2));
                    // $fft_start_bin=
                    // $out['ds18b20_sensor_amount'] = $fft_amount;
                    
                    // if ($fft_amount > 0)
                    // {
                    //     for ($i=0; $i < $fft_amount; $i++)
                    //     { 
                    //         $out['s_bin_'.$i] = hexdec(substr($p, $sb+4+($i*4), 4));
                    //     }
                    // }

                }
            }
        }
        else // BEEP base v2 firmware
        {
            $beep_sensors = [
                't'  , // 0
                'h'  , // 1
                'w_v',
                't_i',
                'a_i',
                'bv' ,
                's_tot',
                's_fan_4',
                's_fan_6',
                's_fan_9',
                's_fly_a',
                'w_fl_hb',
                'w_fl_lb',
                'w_fr_hb',
                'w_fr_lb',
                'w_bl_hb',
                'w_bl_lb',
                'w_br_hb',       
                'w_br_lb', // 18  
            ];

            $minLength = min(strlen($payload)/2, count($beep_sensors));

            for ($i=0; $i < $minLength; $i++) 
            { 
                if (strlen($payload) > count($beep_sensors)*2)
                {
                    $index = $i * 4 + 2; 
                }
                else
                {
                    $index = $i * 2;
                }
                $sensor = $beep_sensors[$i];
                $hexval = substr($payload, $index, 2);

                if (strpos($sensor, '_hb') !== false) // step 1 of 2 byte value
                {
                    $sensor = substr($sensor, 0, strpos($sensor, '_hb'));
                    $out[$sensor] = $hexval;
                } 
                else if (strpos($sensor, '_lb') !== false) // step 2 of 2 byte value
                {
                    $sensor = substr($sensor, 0, strpos($sensor, '_lb'));
                    $totalHexVal  = $out[$sensor].$hexval;
                    $out[$sensor] = hexdec($totalHexVal);
                }
                else
                {
                    $out[$sensor] = hexdec($hexval);
                }
            }
        }
        die(print_r($out));
        return $out;
    }

  

    private function calibrate_weight_sensors($device, $weight_kg, $next_measurement=true, $data_array=null)
    {
        if ($weight_kg == 0)
            return 'no-calibration-weight-error';

        if ($next_measurement)
        {
            $store  = ['calibrating_weight'=>$weight_kg];
            //die(print_r($store));
            $stored = $this->storeInfluxData($store, $device->key, time());
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
                    $last_value = $this->last_sensor_measurement_time_value($device, $sens);
                
                $sens_offset = floatval($this->last_sensor_measurement_time_value($device, $sens.'_offset'));
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
                $combined_value = $this->last_sensor_measurement_time_value($device, 'w_v');

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

            $stored = $this->storeInfluxData($calibrate, $device->key, time());
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
            $value = $this->last_sensor_measurement_time_value($device, $key);
            if ($value != null)
                $offset[$key.'_offset'] = $value;
        }

        if (count($offset) == 0)
            return 'no-weight-values-error';

        //die(print_r(['cal'=>$offset,'key'=>$device->key]));

        // create or update the new SensorDefinition
        if (isset($offset['w_v_offset']))
            $this->createOrUpdateDefinition($device, 'w_v', 'weight_kg', $offset['w_v_offset']);
        
        $stored = $this->storeInfluxData($offset, $device->key, time());
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

        $device           = $this->get_user_device($request, true); // requires id, key, hive_id, or nothing (if only one sensor) to be set
        $next_measurement = $request->filled('next_measurement') ? $request->input('next_measurement') : true;
        $weight_kg        = floatval($request->filled('weight_kg') ? $request->input('weight_kg') : $this->last_sensor_measurement_time_value($device, 'calibrating_weight'));
        $calibrated       = $this->calibrate_weight_sensors($device, $weight_kg, $next_measurement);

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
        $offset = $this->offset_weight_sensors($device);

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
        $output = $this->last_sensor_values_array($device, implode('","',$weight));

        if ($output === false)
            return Response::json('sensor-get-error', 500);
        else if ($output !== null)
            return Response::json($output);
    
        return Response::json('error', 404);
    }

}