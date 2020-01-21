<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Device extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sensors';
 
    protected $fillable = ['user_id', 'hive_id', 'category_id', 'name', 'key', 'last_message_received', 'hardware_id', 'firmware_version', 'hardware_version', 'boot_count', 'measurement_interval_min', 'measurement_transmission_ratio', 'ble_pin'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'category_id', 'deleted_at'];
    protected $appends  = ['type','hive_name', 'location_name', 'owner', 'sensor_definition'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

    public function getHiveNameAttribute()
    {
        if (isset($this->hive))
            return $this->hive->name;

        return '';
    }

    public function getLocationNameAttribute()
    {
        if (isset($this->hive))
            return $this->hive->getLocationAttribute();

        return '';
    }

    public function getOwnerAttribute()
    {
        if (Auth::check() && $this->user_id == Auth::user()->id)
            return true;
        
        return false;
    }

    public function getSensorDefinitionAttribute()
    {
        return $this->sensorDefinitions;
    }


    
    public function sensorDefinitions()
    {
        return $this->hasMany(SensorDefinition::class);
    }

	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

	public function user()
    {
        return $this->belongsTo(User::class);
    }
}
