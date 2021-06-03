<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            return $this->input_measurement->abbreviation;

        return null;
    }

    public function getOutputAbbrAttribute()
    {
        if ($this->output_measurement_id != null)
            return $this->output_measurement->abbreviation;

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

        if( (isset($this->offset) || isset($this->multiplier)) && isset($this->input_measurement_id) && isset($this->output_measurement_id))
        {
            $offset = isset($this->offset) ? floatval($this->offset) : 0;
            $multi  = isset($this->multiplier) ? floatval($this->multiplier) : 1;

            $outputValue = (floatval($inputValue) - $offset) * $multi;
            
            if (isset($this->output_measurement->min_value) && $outputValue < $this->output_measurement->min_value)
                return null;

            if (isset($this->output_measurement->max_value) && $outputValue > $this->output_measurement->max_value)
                return null;
        }

        return $outputValue;
    }
    
}
