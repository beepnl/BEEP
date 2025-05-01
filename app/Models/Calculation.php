<?php

namespace App\Models;

use App\Measurement;
use App\PhysicalQuantity;
use Auth;
use Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Calculation extends Model
{

	public static function addDeviceMeasurementCalibrations($device, $data_array, $calibration_m_abbr)
    {
        /** $calibration_m_abbr:
        ['w_v' => 
			[
			    1743801780 => 
				    [
				      'id' => 1338,
				      'output_abbr' => 'weight_kg',
				      'multiplier' => 1.0,
				      'offset' => 100.0,
				    ]
			]
        ]
        ordered in ascending updated order
        **/

        if (count($calibration_m_abbr) > 0) {
        	
        	Log::debug($calibration_m_abbr);
        	
        	$measurement_min_max = Measurement::minMaxValuesArray();
            $data_meas = array_keys($data_array);
            $cali_meas = array_keys($calibration_m_abbr);
            $cali_match = array_intersect($cali_meas, $data_meas);
			
			Log::debug($cali_match);
            
            if (isset($data_array['time']) && count($cali_match) > 0) {
                foreach ($cali_match as $m_in_abbr) {
                    $data_value = $data_array[$m_in_abbr];

                    if ($data_value === null) {
                        continue;
                    } // only calibrate existing values

                    $calis  = $calibration_m_abbr[$m_in_abbr];
                    $time   = $data_array['time'];
                    $unix_d = strtotime($time);
                    $unixes = array_keys($calis); // unix timestamps
                    $length = count($unixes);

                    Log::debug(['tine'=>$time, 'unix_d'=>$unix_d, 'length'=>$length]);

                    for ($i = 0; $i < $length; $i++) {
                        $unix_curr = $unixes[$i];
                        $unix_next = $i == $length - 1 ? time() : $unixes[$i + 1];

                        if ($unix_d >= $unix_curr && $unix_d < $unix_next) {
                            $cali_curr = $calis[$unix_curr];
                            $cali_output = isset($cali_curr['output_abbr']) ? $cali_curr['output_abbr'] : $m_in_abbr;
                            $cali_multi = isset($cali_curr['multiplier']) ? $cali_curr['multiplier'] : 1;
                            $cali_offset = isset($cali_curr['offset']) ? $cali_curr['offset'] : 0;

                            $meas_min = isset($measurement_min_max[$cali_output]['min']) ? $measurement_min_max[$cali_output]['min'] : null;
                            $meas_max = isset($measurement_min_max[$cali_output]['max']) ? $measurement_min_max[$cali_output]['max'] : null;

                            // Calibrate value
                            $data_array[$cali_output.'_raw'] = $data_value;
                            $calibrated_value = ($data_value * $cali_multi) + $cali_offset;

                            // Hide values outside value min/max
                            if (isset($meas_min) && $calibrated_value < $meas_min) {
                                if ($meas_min === 0) {
                                    $data_array[$cali_output] = $meas_min; // only output calibrated_value 0 if below zero and meas_min === 0
                                } else {
                                    // do not output too low value
                                    $data_array[$cali_output] = null;
                                }
                            } elseif (isset($meas_max) && $calibrated_value > $meas_max) {
                                // do not output too high value
                                $data_array[$cali_output] = null;
                            } else {
                                // Set output value
                                $data_array[$cali_output] = $calibrated_value;

                                // Also update _min and _max value in data array
                                if (isset($data_array[$cali_output.'_min'])) {
                                    $calibrated_value_min = ($data_array[$m_in_abbr.'_min'] * $cali_multi) + $cali_offset;

                                    if (isset($meas_min)) {
                                        $calibrated_value_min = max($calibrated_value_min, $meas_min);
                                    }

                                    if (isset($meas_max)) {
                                        $calibrated_value_min = min($calibrated_value_min, $meas_max);
                                    }

                                    $data_array[$cali_output.'_min'] = $calibrated_value_min;
                                }

                                if (isset($data_array[$cali_output.'_max'])) {
                                    $calibrated_value_max = ($data_array[$m_in_abbr.'_max'] * $cali_multi) + $cali_offset;

                                    if (isset($meas_min)) {
                                        $calibrated_value_max = max($calibrated_value_max, $meas_min);
                                    }

                                    if (isset($meas_max)) {
                                        $calibrated_value_max = min($calibrated_value_max, $meas_max);
                                    }

                                    $data_array[$cali_output.'_max'] = $calibrated_value_max;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data_array;
    }


}