<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SensorDefinition extends Model
{
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
    protected $fillable = ['name', 'inside', 'offset', 'multiplier', 'input_measurement_id', 'output_measurement_id', 'sensor_id'];

    public function input_measurement()
    {
        return $this->belongsTo('Measurement::class');
    }
    public function output_measurement()
    {
        return $this->belongsTo('Measurement::class');
    }
    public function sensor()
    {
        return $this->belongsTo('Sensor::class');
    }

    public function calibrated_measurement_value($inputValue)
    {
        $outputValue = $inputValue;

        if( (isset($this->offset) || isset($this->multiplier)) && isset($this->input_measurement_id) && isset($this->output_measurement_id))
        {
            $offset = isset($this->offset) ? $this->offset : 0;
            $multi  = isset($this->multiplier) ? $this->multiplier : 1;

            $outputValue = ($inputValue - $offset) * $multi;
        }

        return $outputValue;
    }
    
}
