<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceMeasurement extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'device_measurements';

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
    protected $fillable = ['name', 'inside', 'zero_value', 'unit_per_value', 'measurement_id', 'physical_quantity_id', 'sensor_id'];

    public function measurement()
    {
        return $this->belongsTo('Measurement::class');
    }
    public function physical_quantity()
    {
        return $this->belongsTo('PhysicalQuantity::class');
    }
    public function sensor()
    {
        return $this->belongsTo('Sensor::class');
    }
    
}
