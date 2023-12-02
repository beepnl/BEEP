<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;

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
    protected $fillable = ['name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'device_id'];
    protected $appends  = ['input_abbr', 'output_abbr'];
    protected $hidden   = ['input_measurement', 'output_measurement', 'deleted_at'];

    public function getInputAbbrAttribute()
    {
        if ($this->input_measurement_id != null)
            return Cache::remember('meas-id-'.$this->input_measurement_id.'-abbr', env('CACHE_TIMEOUT_LONG'), function (){
                return $this->input_measurement->abbreviation;
            });

        return null;
    }

    public function getOutputAbbrAttribute()
    {
        if ($this->output_measurement_id != null)
            return Cache::remember('meas-id-'.$this->output_measurement_id.'-abbr', env('CACHE_TIMEOUT_LONG'), function (){
                return $this->output_measurement->abbreviation;
            });

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

            $this_om = $this->output_measurement;
            if (isset($this_om))
            {
                $oid    = $this->output_measurement_id;
                $iid    = $this->input_measurement_id;
                $om_min = Cache::remember('meas-id-'.$oid.'-min', env('CACHE_TIMEOUT_LONG'), function () use ($this_om){
                    if (isset($this_om->min_value))
                        return $m = $this_om->min_value;
                    else
                        return null;
                });
                $om_max = Cache::remember('meas-id-'.$oid.'-max', env('CACHE_TIMEOUT_LONG'), function () use ($this_om){
                    if (isset($this_om->max_value))
                        return $m = $this_om->max_value;
                    else
                        return null;
                });

                //die(print_r(['in'=>$inputValue, 'out'=>$outputValue, 'min'=>$om_min, 'max'=>$om_max]));

                if (isset($om_min) && $outputValue < $om_min)
                {
                    return null;
                }

                if (isset($om_max) && $outputValue > $om_max)
                {
                    return null;
                }
            }
        }

        return $outputValue;
    }
    
}
