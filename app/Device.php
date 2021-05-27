<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use InfluxDB;
use App\Models\Alert;

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
        return $this->hasMany(SensorDefinition::class);
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

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    /* returns most recent Influx sensor values:
    Array
    (
        [0] => Array
            (
                [time] => 2021-05-05T14:30:00Z
                [t_i] => 
            )

        [1] => Array
            (
                [time] => 2021-05-05T14:00:00Z
                [t_i] => 
            )

    )
    */
    public function getSensorValues($measurement_abbr, $comparison='MEAN', $interval_min=null, $limit=null, $start=null, $table='sensors', $output_sensors_only=false)
    {
        //die(print_r([$names, $valid_sensors]));
        $client         = new \Influx;
        $valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();

        $out           = [];
        $valid_sensors = $output_sensors_only ? $output_sensors : array_keys($valid_sensors);
        $options       = ['precision'=> 's'];

        $where_limit   = isset($limit) ? 'LIMIT '.$limit : '';
        $where         = '("key" = \''.$this->key.'\' OR "key" = \''.strtolower($this->key).'\' OR "key" = \''.strtoupper($this->key).'\')';
        $where_time    = isset($start) ? 'AND time >= \''.$start.'\'' : '';
        $device_int_min= isset($this->measurement_interval_min) ? $this->measurement_interval_min : 15;
        $time_interval = isset($interval_min) && $interval_min > $device_int_min ? $interval_min.'m' : $device_int_min.'m';
        $group_by_time = 'GROUP BY time('.$time_interval.')';
        
        $query   = 'SELECT '.$comparison.'("'.$measurement_abbr.'") AS "'.$measurement_abbr.'" FROM "'.$table.'" WHERE '.$where.' '.$where_time.' '.$group_by_time.' ORDER BY time DESC '.$where_limit;

        if (in_array($measurement_abbr, $valid_sensors))
        {
            $sensors = [];

            try{
                $result  = $client::query($query, $options);
                $sensors = $result->getPoints();
            } catch (InfluxDB\Exception $e) {
                // return Response::json('influx-group-by-query-error', 500);
            }
            if (count($sensors) > 0)
            {
                return $sensors;
            }
        }
        
        return $out;
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

    public static function getInfluxQuery($query)
    {
        $client  = new \Influx;
        $options = ['precision'=> 's'];
        $values  = [];
        try{
            $result  = $client::query($query, $options);
            $values  = $result->getPoints();
        } catch (InfluxDB\Exception $e) {
            // return Response::json('influx-group-by-query-error', 500);
        }
        return $values;
    }

    public static function getAvailableSensorNamesFromData($names, $table, $where, $output_sensors_only=true)
    {
        //die(print_r([$names, $valid_sensors]));
        $client         = new \Influx;
        $valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();

        $out           = [];
        $valid_sensors = $output_sensors_only ? $output_sensors : array_keys($valid_sensors);
        $valid_sensors = array_intersect($valid_sensors, $names);
        $values        = Device::getInfluxQuery('SELECT * FROM "'.$table.'" WHERE '.$where.' GROUP BY "name,time" ORDER BY time DESC LIMIT 1');

        if (count($values) > 0)
            $sensors = $values[0];
        else
            return $out;

        $sensors = array_filter($sensors, function($value) { return !is_null($value) && $value !== ''; });

        $out = array_keys($sensors);
        $out = array_intersect($out, $valid_sensors);
        $out = array_values($out);

        return $out;
    }
}
