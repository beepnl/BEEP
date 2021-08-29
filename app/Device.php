<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use InfluxDB;
use Cache;

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

    public static function cacheRequestRate($name, $retention_sec=86400)
    {
        Cache::remember($name.'-time', $retention_sec, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count');
        else
            Cache::put($name.'-count', 1);

    }

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


    public function last_sensor_measurement_time_value($name)
    {
        $arr = $this->last_sensor_values_array($name);

        if ($arr && count($arr) > 0 && in_array($name, array_keys($arr)))
            return $arr[$name];

        return null;
    }
    

    public static function getInfluxQuery($query)
    {
        Device::cacheRequestRate('influx-get');
        Device::cacheRequestRate('influx-device');

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

    // Provide a list of sensor names that exist within the $where clase and $table
    public static function getAvailableSensorNamesFromData($names, $where, $table='sensors', $output_sensors_only=true)
    {
        //die(print_r([$names, $valid_sensors]));
        $valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();

        $out           = [];
        $valid_sensors = $output_sensors_only ? $output_sensors : array_keys($valid_sensors);
        $valid_sensors = array_intersect($valid_sensors, $names);
        
        $fields = [];
        foreach ($valid_sensors as $field)
        {
            $fields[] = 'count("'.$field.'") as "'.$field.'"';
        }
        $valid_fields = implode(', ', $fields);

        $query  = 'SELECT '.$valid_fields.' FROM "'.$table.'" WHERE '.$where.' GROUP BY "name,time" ORDER BY time DESC LIMIT 1';
        $values = Device::getInfluxQuery($query);

        if (count($values) > 0)
            $sensors = $values[0];
        else
            return $out;

        $sensors = array_filter($sensors, function($value) { return !is_null($value) && $value !== '' && $value > 0; });

        $out = array_keys($sensors);
        $out = array_intersect($out, $valid_sensors);
        $out = array_values($out);

        return $out;
    }

    
    public function last_sensor_values_array($fields='*', $limit=1)
    {
        Device::cacheRequestRate('influx-get');
        Device::cacheRequestRate('influx-last');

        $fields = $fields != '*' ? '"'.$fields.'"' : '*';
        $groupby= $fields == '*' || strpos(',' ,$fields) ? 'GROUP BY "name,time"' : '';
        $output = null;
        try
        {
            $client = new \Influx;
            $query  = 'SELECT '.$fields.' from "sensors" WHERE ("key" = \''.$this->key.'\' OR "key" = \''.strtolower($this->key).'\' OR "key" = \''.strtoupper($this->key).'\') AND time > now() - 365d '.$groupby.' ORDER BY time DESC LIMIT '.$limit;
            //die(print_r($query));
            $result = $client::query($query);
            $values = $result->getPoints();
            //die(print_r($values));
            $output = $limit == 1 ? $values[0] : $values;
            $output = array_filter($output, function($value) { return !is_null($value) && $value !== ''; });
        }
        catch(\Exception $e)
        {
            return false;
        }
        return $output;
    }

    public function addSensorDefinitionMeasurements($data_array, $value, $measurement_abbr, $date=null)
    {
        $before_date = isset($date) ? $date : date('Y-m-d H:i:s'); 
        
        $measurement_id = Cache::remember('measurement-abbr-id-'.$measurement_abbr, env('CACHE_TIMEOUT_LONG'), function () use ($measurement_abbr){
            $m = Measurement::where('abbreviation',$measurement_abbr)->first();
            if ($m)
                return $m->id;
            else
                return null;
        });

        if ($measurement_id != null)
        {
            // Get the right sensordefinition
            $sensor_def  = null;
            $sensor_defs = $this->sensorDefinitions->where('input_measurement_id', $measurement_id); // get appropriate sensor definitions

            if ($sensor_defs->count() == 0)
            {
                // add nothing to $data_array
            }
            else if ($sensor_defs->count() == 1)
            {
                $sensor_def = $sensor_defs->last(); // get the only sensor definition, before or after setting
            }
            else // there are multiple, so get the one appropriate for the $date
            {
                if ($sensor_defs->where('updated_at', '<=', $before_date)->count() == 0) // not found before $date, but there are after, so get the first
                    $sensor_def = $sensor_defs->first();
                else
                    $sensor_def = $sensor_defs->where('updated_at', '<=', $before_date)->last(); // be aware that last() gets the last value of the ASCENDING list
            }

            // Calculate the extra value based on the sensor definition
            if (isset($sensor_def))
            {
                $measurement_abbr_o              = $sensor_def->output_abbr;
                $calibrated_measurement_val      = $sensor_def->calibrated_measurement_value($value);
                if ($calibrated_measurement_val !== null) // do not add sensor measurement is outside measurement min/max value
                    $data_array[$measurement_abbr_o] = $calibrated_measurement_val;

                //die(print_r([$calibrated_measurement_val, $data_array, $sensor_def->toArray()]));
            }
            // else if ($measurement_abbr == 'w_v') // Legacy app: make new calibration values based on stored ones
            // {
            //     $influx_offset = floatval($device->last_sensor_measurement_time_value($measurement_abbr.'_offset'));
            //     $influx_multi  = floatval($device->last_sensor_measurement_time_value($measurement_abbr.'_kg_per_val'));

            //     if ($influx_offset != 0 || $influx_multi != 0)
            //         $this->createOrUpdateDefinition($device, 'w_v', 'weight_kg', $influx_offset, $influx_multi);
            // }
        }
        return $data_array;
    }

    private function last_sensor_increment_values($data_array=null)
    {
        $output = [];
        $limit  = 2;

        if ($data_array != null)
        {
            $output[0] = $data_array;
            $output[1] = $this->last_sensor_values_array(implode('","',array_keys($data_array)), 1);
        }
        else
        {
            $output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();
            $output = $this->last_sensor_values_array(implode('","',$output_sensors), $limit);
        }
        $out_arr= [];

        if (count($output) < $limit)
            return null;

        for ($i=0; $i < $limit; $i++) 
        { 
            if (isset($output[$i]) && gettype($output[$i]) == 'array')
            {
                foreach ($output[$i] as $key => $val) 
                {
                    if ($val != null)
                    {
                        $value = $key == 'time' ? strtotime($val) : floatval($val);

                        if ($i == 0) // desc array, so most recent value: $i == 0
                        {
                            $out_arr[$key] = $value; 
                        }
                        else if (isset($out_arr[$key]))
                        {
                            $out_arr[$key] = $out_arr[$key] - $value;
                        }
                    }
                }
            }
        }
        //die(print_r($out_arr));

        return $out_arr; 
    }

}
