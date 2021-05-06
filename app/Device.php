<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use InfluxDB;

class Device extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table    = 'sensors';
 
    protected $cascadeDeletes = ['sensorDefinitions'];
    protected $fillable = ['user_id', 'hive_id', 'category_id', 'name', 'key', 'last_message_received', 'hardware_id', 'firmware_version', 'hardware_version', 'boot_count', 'measurement_interval_min', 'measurement_transmission_ratio', 'ble_pin', 'battery_voltage', 'next_downlink_message', 'last_downlink_result', 'datetime', 'datetime_offset_sec'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'category_id', 'deleted_at', 'hive'];
    protected $appends  = ['type','hive_name', 'location_name', 'owner'];

    public $timestamps  = false;

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

    public function sensorDefinitions()
    {
        return $this->hasMany(SensorDefinition::class)->orderByDesc('updated_at');
    }

	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

    public function location()
    {
        if (isset($this->hive))
            return Auth::user()->locations()->find($this->hive->location_id);

        return null;
    }

	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function selectList()
    {
        $list = [];
        
        if (Auth::user()->hasRole(['superadmin','admin']))
            $list = Device::all();
        else
            $list = Auth::user()->devices;

        $list_out     = [];

        foreach($list as $i)
        {
            $id = $i->id;
            $label = $i->name.' ('.$i->key.')';

            $list_out[$id] = $label;

        }
        return $list_out;
    }

    public static function getAvailableSensorNamesFromData($names, $table, $where, $limit='', $output_sensors_only=true)
    {
        //die(print_r([$names, $valid_sensors]));
        $client         = new \Influx;
        $valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();

        $out           = [];
        $valid_sensors = $output_sensors_only ? $output_sensors : array_keys($valid_sensors);
        $options       = ['precision'=> 's'];
        
        for ($i = 0; $i < count($names); $i++) 
        {
            $name = $names[$i];
            if (in_array($name, $valid_sensors))
            {
                $sensors = [];
                $query = 'SELECT COUNT("'.$name.'") AS "count" FROM "'.$table.'" WHERE '.$where.' '.$limit;
                try{
                    $result  = $client::query($query, $options);
                    $sensors = $result->getPoints();
                } catch (InfluxDB\Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                }
                if (count($sensors) > 0 && $sensors[0]['count'] > 0)
                    $out[] = $name;
            }
        }

        return $out;
    }
}
