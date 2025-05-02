<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Measurement;
use Cache;

use Illuminate\Support\Facades\Log;

class SensorDefinition extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sensor_definitions';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */

    // recalculate: true => on the fly correction + at sensor value storage, false (default) => only calculate at sensor value storage
    protected $fillable = ['name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'device_id', 'updated_at', 'recalculate']; 
    protected $appends  = ['input_abbr', 'output_abbr'];
    protected $hidden   = ['input_measurement', 'output_measurement', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($c) {
            $c->forgetCache();
        });

        self::updated(function ($c) {
            $c->forgetCache();
        });

        self::deleted(function ($c) {
            $c->forgetCache();
        });
    }

    public function forgetCache()
    {
        Cache::forget('device-'.$this->device_id.'-active-calibrations');
        Cache::forget('device-'.$this->device_id.'-calibrations-measurement-types');
    }

    public function getInputAbbrAttribute()
    {
        if ($this->input_measurement_id != null)
            return Cache::rememberForever('meas-id-'.$this->input_measurement_id.'-abbr', function (){
                return $this->input_measurement->abbreviation;
            });

        return null;
    }

    public function getOutputAbbrAttribute()
    {
        if ($this->output_measurement_id != null)
            return Cache::rememberForever('meas-id-'.$this->output_measurement_id.'-abbr', function (){
                return $this->output_measurement->abbreviation;
            });

        // Fallback
        if ($this->input_measurement_id != null) {
            return $this->getInputAbbrAttribute();
        } 

        return null;
    }

    // transform bool output into real boolean value 
    public function getInsideAttribute($value)
    {
        if (isset($value))
            return $value == 1 ? true : false;

        return null;
    }

    public function input_measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function output_measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function calibrated_measurement_value($inputValue)
    {
        $outputValue = $inputValue;

        if( (!empty($this->offset) || !empty($this->multiplier)) && isset($this->input_measurement_id) && isset($this->output_measurement_id))
        {
            $offset = !empty($this->offset) ? floatval($this->offset) : 0;
            $multi  = !empty($this->multiplier) ? floatval($this->multiplier) : 1;

            $outputValue = (floatval($inputValue) - $offset) * $multi;

            $outAbbr = $this->output_abbr;
            if (isset($outAbbr))
            {
                $mima   = Measurement::minMaxValuesArray();
                $om_min = $mima[$outAbbr]['min'];
                $om_max = $mima[$outAbbr]['max'];

                //die(print_r(['in'=>$inputValue, 'out'=>$outputValue, 'min'=>$om_min, 'max'=>$om_max]));

                if (isset($om_min) && $outputValue < $om_min)
                    return null;

                if (isset($om_max) && $outputValue > $om_max)
                    return null;
                
            }
        }

        return $outputValue;
    }

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
            
            //Log::debug($calibration_m_abbr);
            
            $measurement_min_max = Measurement::minMaxValuesArray();
            $data_meas = array_keys($data_array);
            $cali_meas = array_keys($calibration_m_abbr);
            $cali_match = array_intersect($cali_meas, $data_meas);
            
            //Log::debug(['data'=>$data_array, 'cali'=>$cali_meas, 'match'=>$cali_match]);
            
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

                    //Log::debug(['time'=>$time, 'unix_d'=>$unix_d, 'length'=>$length]);

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
                            $calibrated_value = ($data_value - $cali_offset) * $cali_multi; 

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
