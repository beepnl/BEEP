<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use DB;
use Cache;
use Auth;
use InfluxDB;
use App\Models\Alert;
use App\Models\FlashLog;
use Moment\Moment;

class Device extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table          = 'sensors';
    protected $cascadeDeletes = ['sensorDefinitions'];
    protected $fillable       = ['user_id', 'hive_id', 'category_id', 'name', 'key', 'last_message_received', 'hardware_id', 'firmware_version', 'hardware_version', 'boot_count', 'measurement_interval_min', 'measurement_transmission_ratio', 'ble_pin', 'battery_voltage', 'next_downlink_message', 'last_downlink_result', 'datetime', 'datetime_offset_sec', 'former_key_list', 'rtc', 'log_file_info'];
	protected $guarded 	      = ['id'];
    protected $hidden         = ['user_id', 'category_id', 'deleted_at', 'hive', 'former_key_list'];
    protected $appends        = ['type','hive_name', 'location_name', 'owner', 'online'];
    protected $casts          = ['log_file_info'=>'array'];

    public $timestamps        = false;

    public static function boot()
    {
        parent::boot();

        static::created(function ($d) {
            //Log::info("Created Device $d->id $d->name");
            $d->empty_cache();
        });

        static::updated(function ($d) {
            $empty_cache = false;
            if ($d->wasChanged('deleted_at'))
            {
                Log::info("Updated Device id=$d->id deleted_at to $d->deleted_at");
                $empty_cache = true;
            }
            if ($d->wasChanged('user_id'))
            {
                Log::info("Updated Device id=$d->id user_id to $d->user_id");
                $empty_cache = true;
            }
            if ($d->wasChanged('firmware_version'))
            {
                Log::info("Updated Device id=$d->id firmware_version to $d->firmware_version");
                $empty_cache = true;
            }
            if ($d->wasChanged('hive_id'))
            {
                Log::info("Updated Device id=$d->id hive_id to $d->hive_id");
                $empty_cache = true;
            }
            if ($d->wasChanged('key'))
            {
                Log::info("Updated Device id=$d->id key to $d->key");
                $empty_cache = true;
            }
            if ($d->wasChanged('name'))
            {
                Log::info("Updated Device id=$d->id name to $d->name");
                $empty_cache = true;
            }
            if ($empty_cache)
                $d->empty_cache();
        });

        static::deleted(function ($d) {
            //Log::info("Deleted Device $d->id $d->name");
            $d->empty_cache();
        });
    }

    public function empty_cache($clear_users=true)
    {
        Cache::forget('device-'.$this->id.'-hive-'.$this->hive_id.'-user-ids');
        Cache::forget('device-'.$this->id.'-hive-'.$this->hive_id.'-rule-ids');
        Cache::forget('device-'.$this->id.'-calibrations-measurement-types');
        
        Log::debug("Device ID $this->id cache emptied");

        if ($clear_users)
            User::emptyIdCache($this->user_id, 'device');

    }


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

    public function getLocationIdAttribute()
    {
        if (isset($this->hive))
            return $this->hive->location_id;

        return null;
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

    public function getOnlineAttribute()
    {
        $refresh_min  = max(15, $this->getRefreshMin() * 2);
        $min_msg_date = date('Y-m-d H:i:s', time()-(60*$refresh_min)); // at least
        if (isset($this->last_message_received) && $this->last_message_received > $min_msg_date)
            return true;

        return false;
    }


    public function researchNames()
    {
        $res = $this->researches();
        if ($res->count() > 0)
            return implode(', ', $res->pluck('name')->toArray());

        return '';
    }

    // former Dev EUIs (created at redoing automatic LoRa config)
    public function allKeys()
    {
        $keys = [$this->key];

        if (isset($this->former_key_list))
        {
            $keys = array_merge($keys, explode(',', $this->former_key_list));
        }
        // add upper or lowercase key
        $add_keys = [];
        foreach ($keys as $key)
        {
            $key_low = strtolower($key);
            $key_upp = strtoupper($key);

            if ($key_low !== $key)
                $add_keys[] = $key_low;

            if ($key_upp !== $key)
                $add_keys[] = $key_upp;
        }
        if (count($add_keys) > 0)
            $keys = array_merge($keys, $add_keys);

        //die(print_r($where_keys));
        return $keys;
    }

    public function influxWhereKeys()
    {
        $keys = $this->allKeys();

        $where_keys = '("key" = \''.implode('\' OR "key" = \'', $keys).'\')';
        //die(print_r($where_keys));
        return $where_keys;
    }

    public function addFormerKey($key)
    {
        if (!isset($key) || $key == '' || $key == null)
            return false;

        $keys = [];
        if (isset($this->former_key_list))
            $keys = explode(',', $this->former_key_list);

        if (in_array($key, $keys) === false)
            $keys[] = $key;

        if (count($keys) > 0)
        {
            $this->former_key_list = implode(',', $keys);
            $this->save();
            return true;
        }
        return false;
    }


    public function sensorDefinitions()
    {
        return $this->hasMany(SensorDefinition::class);
    }

    public function activeSensorDefinitions()
    {
        return Cache::rememberForever('device-'.$this->id.'-active-calibrations', function () {
            return $this->sensorDefinitions()->orderBy('updated_at', 'desc')->get()->unique('input_measurement_id', 'output_measurement_id');
        });
    }

    // provide sensorfdefinitions in descending in order
    public function activeTypeDateSensorDefinitions($input_measurement_id, $output_measurement_id, $start, $end)
    {
        // Check if multiple during timespan
        $sd_ids = [];
        $io_sds = $this->sensorDefinitions()->where('input_measurement_id', $input_measurement_id)->where('output_measurement_id', $output_measurement_id)->get();

        // If empty collection, or only one, return sensor_defs collection for all data
        if ($io_sds->count() < 2)
            return $io_sds;

        // If more than 1, chech which one to use
        $sd_during = $io_sds->whereBetween('updated_at', [$start, $end])->sortByDesc('updated_at')->unique('input_measurement_id', 'output_measurement_id');

        if ($sd_during->count() > 0)
        {
            foreach($sd_during as $sd)
                $sd_ids[] = $sd->id;
        }

        // Add first before or at start
        $sd_before = $io_sds->where('updated_at', '<=', $start)->sortByDesc('updated_at')->first();

        if ($sd_before)
            $sd_ids[] = $sd_before->id;

        // If none, add first after end
        if (count($sd_ids) == 0)
        {
            $sd_next = $io_sds->where('updated_at', '>=', $end)->sortBy('updated_at')->first();

            if ($sd_next)
                $sd_ids[] = $sd_next->id;
        }

        return $this->sensorDefinitions()->whereIn('id', $sd_ids)->orderBy('updated_at', 'desc')->get();
    }

	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

    public function location()
    {
        if (isset($this->hive))
            return $this->hive->location()->first();

        return null;
    }

	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function researches()
    {
        return $this->user->researches();
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function flashLogs()
    {
        return $this->hasMany(FlashLog::class);
    }

    public function getMeasurementsPerDay()
    {
        $device_interval_min = isset($this->measurement_interval_min) && $this->measurement_interval_min > 0 ? $this->measurement_interval_min : 15;
        return round(1440 / $device_interval_min);
    }

    public function getFlashLogsHtml($start_date='2019-01-01')
    {
        $flogs       = $this->flashlogs()->where('created_at', '>', $start_date)->orderByDesc('created_at')->get(); // get files after start, because can only be filled with data about start date
        $flogs_count = $flogs->count();
        $flogs_valid = 0;

        $html        = '<div style="display: inline-block; max-height: 45px; overflow-x: hidden; overflow-y: scroll;"><ul style="padding:0; margin:0;">';

        $meas_per_day = isset($this->interval_min) && $this->interval_min > 0 ? round(1440/$this->interval_min) : 96;
        foreach ($flogs as $fl)
        {
            $logpd = $fl->logs_per_day;
            $logperc = min(100, round(100 * $logpd / $meas_per_day));
            $days  = $fl->getLogDays();
            $logd  = $days ? round($days).' days x '.$logperc.'%' : '';
            $pers  = $fl->persisted_days ? ', '.round($fl->persisted_days).' days persisted' : '';
            $post  = $pers || $logd ? ' ('.$logd.$pers.')' : ''; 
            $name  = $fl->id.'. '.substr($fl->created_at, 0, 10).$post;
            $valid = $fl->validLog();
            $flogs_valid += $valid;
            $color = $valid ? 'style="color: green;" title="Flash log id:'.$fl->id.' has '.$logd.' valid weight/time data"' : 'title="Flash log '.$fl->id.' should be checked for valid data: has '.$logd.' of data, but end date might not be within 1 hour of the upload date."';
            $html .= '<li style="padding:0; margin:0;"><a href="/flash-log/'.$fl->id.'" '.$color.'>'.$name.'</a></li>';
        }

        $html .= '</ul></div>';

        $all_valid   = $flogs_count > 0 && $flogs_count == $flogs_valid ? true : false;
        $html_valid  = '<div style="display: inline-block; vertical-align:top; height: 30px; margin:0; margin-top:7px; padding: 5px; border-radius: 5px; border: 1px solid grey; background-color: '.($all_valid ? 'green' : 'red').';">'.$flogs_valid.'/'.$flogs_count.'</div>';

        return "<div style=\"max-height: 42px;\">$html_valid $html</div>";
    }


    public function getRefreshMin()
    {
        $int_min = isset($this->measurement_interval_min) ? $this->measurement_interval_min : 0;
        $int_mul = isset($this->measurement_transmission_ratio) ? $this->measurement_transmission_ratio : 1;
        return $int_min * $int_mul;
    }

    public function hiveUserIds()
    {
        $hive_id = $this->hive_id;
        return Cache::remember('device-'.$this->id.'-hive-'.$hive_id.'-user-ids', env('CACHE_TIMEOUT_LONG'), function () use ($hive_id)
        {
            $user_ids = [$this->user_id];
            if (isset($hive_id))
            {
                $group_user_ids = DB::table('group_user')
                                ->join('group_hive', function ($join) use ($hive_id) {
                                $join->on('group_user.group_id' , '=', 'group_hive.group_id')
                                     ->where('group_hive.hive_id', '=', $hive_id);
                                })
                                ->pluck('group_user.user_id')
                                ->toArray();

                $user_ids = array_unique(array_merge($user_ids, $group_user_ids));
            }
            return $user_ids;
        });
    }

    public function hiveUserRuleIds()
    {
        $hive_id = $this->hive_id;
        return Cache::remember('device-'.$this->id.'-hive-'.$hive_id.'-rule-ids', env('CACHE_TIMEOUT_LONG'), function () use ($hive_id)
        {
            $rule_ids = $this->user->alert_rules()->pluck('id')->toArray();
            if (isset($hive_id))
            {
                $group_rule_ids = DB::table('alert_rules')
                                ->join('group_user', 'group_user.user_id', '=', 'alert_rules.user_id')
                                ->join('group_hive', function ($join) use ($hive_id) {
                                $join->on('group_user.group_id' , '=', 'group_hive.group_id')
                                     ->where('group_hive.hive_id', '=', $hive_id);
                                })
                                ->pluck('alert_rules.id')
                                ->toArray();
                $rule_ids = array_unique(array_merge($rule_ids, $group_rule_ids));
            }
            return $rule_ids;
        });
    }

    /* getSensorValues returns most recent Influx sensor values:
    Array
    (
        [0] => Array
            (
                [time] => 2021-05-05T14:30:00Z
                [t_i] => 35.6
            )

        [1] => Array
            (
                [time] => 2021-05-05T14:00:00Z
                [t_i] => 34.9
            )

    )
    */
    public function getAlertSensorValues($measurement_abbr, $influx_func='MEAN', $interval_min=null, $limit=null, $start=null, $table='sensors')
    {
        //die(print_r([$names, $valid_sensors]));
        $device_int_min= isset($this->measurement_interval_min) ? $this->measurement_interval_min : 15;
        $time_int_min  = isset($interval_min) && $interval_min > $device_int_min ? $interval_min : $device_int_min;
        $val_min_ago   = null;

        // Get values from cache
        if ($table == 'sensors' && $limit == 1 && $interval_min <= $time_int_min)
        {
            $cached_time = Cache::get('set-measurements-device-'.$this->id.'-time');
            $cached_data = Cache::get('set-measurements-device-'.$this->id.'-data');
            $val_min_ago = round((time() - intval($cached_time)) / 60);
            if ($cached_data && $val_min_ago < $time_int_min && isset($cached_data['time']) && isset($cached_data[$measurement_abbr]))
                return ['values'=>[["time"=>$cached_data['time'], "$measurement_abbr"=>$cached_data[$measurement_abbr]]], 'query'=>'', 'from'=>'cache', 'min_ago'=>$val_min_ago];
        }

        // Get values from Influx
        $where_limit   = isset($limit) ? ' LIMIT '.$limit : '';
        $where         = $this->influxWhereKeys();
        $where_time    = isset($start) ? 'AND time >= \''.$start.'\' ' : '';
        $group_by_time = 'GROUP BY time('.$time_int_min.'m) ';

        $deriv_time    = '';
        if ($influx_func == 'DERIVATIVE') // don't groupby time, but set derivative time
        {
            $group_by_time = '';
            $deriv_time    = ','.$time_int_min.'m';
        }

        $query   = 'SELECT '.$influx_func.'("'.$measurement_abbr.'"'.$deriv_time.') AS "'.$measurement_abbr.'" FROM "'.$table.'" WHERE '.$where.' '.$where_time.$group_by_time.'ORDER BY time DESC'.$where_limit;
        $values  = Device::getInfluxQuery($query, 'alert');

        if (count($values) > 0)
        {
            $last_vals = $values[0];
            if ($last_vals['time'])
            {
                $last_mom    = new Moment($last_vals['time']);
                $val_min_ago = round($last_mom->fromNow()->getMinutes());
            }
        }

        return ['values'=>$values,'query'=>$query, 'from'=>'influx', 'min_ago'=>$val_min_ago];
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


    public static function getInfluxQuery($query, $from='device')
    {
        Device::cacheRequestRate('influx-get');
        Device::cacheRequestRate('influx-'.$from);

        $client  = new \Influx;
        $options = ['precision'=> 's'];
        $values  = [];

        try{
            $result  = $client::query($query, $options);
            $values  = $result->getPoints();
        } catch (\Exception $e) {
            // return Response::json('influx-group-by-query-error', 500);
            //die($e->getMessage());
        }
        return $values;
    }

    // Provide a list of sensor names that exist within the $where clase and $table
    public static function getAvailableSensorNamesNoCache($names, $where, $table='sensors', $output_sensors_only=true, $cache_name='names-nocache')
    {
        $weather        = $table == 'weather' ? true : false;
        $valid_sensors  = Measurement::getValidMeasurements();
        $output_sensors = Measurement::getValidMeasurements(true, $weather);

        $out           = [];
        $valid_sensors = $output_sensors_only ? $output_sensors : array_keys($valid_sensors);
        $valid_sensors = array_intersect($valid_sensors, $names);

        if (count($valid_sensors) == 0)
            return $out;

        $fields = [];
        foreach ($valid_sensors as $field)
        {
            $fields[] = 'count("'.$field.'") as "'.$field.'"';
        }
        $valid_fields = implode(', ', $fields);

        $query  = 'SELECT '.$valid_fields.' FROM "'.$table.'" WHERE '.$where.' GROUP BY "name,time" ORDER BY time DESC LIMIT 1';
        $values = Device::getInfluxQuery($query, $cache_name);

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

    // Provide a list of sensor names that exist within the $where clase and $table (cached)
    public static function getAvailableSensorNamesFromData($device_name, $names, $where, $table='sensors', $output_sensors_only=true, $cache=true)
    {
        $output_name   = $output_sensors_only ? 'output' : 'valid';
        $names_name    = gettype($names) == 'array' ? implode('-', $names) : $names;

        $cache_string  = 'device-'.$device_name.'-'.$table.'-measurement-names-'.$names_name.'-'.$output_name;
        $cache_array   = Cache::get($cache_string);

        $forget = 0;
        if (gettype($cache_array) != 'array' || count($cache_array) == 0 || $cache == false)
        {
            $forget = 1;
            Cache::forget($cache_string);
        }

        //die(print_r(['forget'=>$forget, 'key'=>$cache_string, 'data'=>$cache_array]));

        return Cache::remember($cache_string, env('CACHE_TIMEOUT_LONG', 3600), function () use ($names, $where, $table, $output_sensors_only)
        {
            return Device::getAvailableSensorNamesNoCache($names, $where, $table, $output_sensors_only, 'names');
        });
    }


    public function last_sensor_values_array($fields='*', $limit=1)
    {
        if (gettype($fields) == 'array')
            $cache_fields = implode('-', $fields);
        else
            $cache_fields = $fields;

        $cache_name    = 'device-'.$this->id.'-fields-'.$cache_fields.'-limit-'.$limit;
        $last_dev_time = Cache::get('set-measurements-device-'.$this->id.'-time'); // not fields and limit based, set in MeasurementController::storeMeasurements
        $last_req_time = Cache::get('last-values-'.$cache_name.'-request-time');
        $last_req_vals = Cache::get('last-values-'.$cache_name);

        if ($last_req_vals != null && $last_dev_time < $last_req_time) // only request Influx if newer data is available
        {
            $last_req_vals['from_cache'] = true;
            return $last_req_vals;
        }

        $fields = $fields != '*' ? '"'.$fields.'"' : '*';
        $groupby= $fields == '*' || strpos(',' ,$fields) ? 'GROUP BY "name,time"' : '';
        $output = null;
        try
        {
            $query  = 'SELECT '.$fields.' from "sensors" WHERE '.$this->influxWhereKeys().' AND time > now() - 365d '.$groupby.' ORDER BY time DESC LIMIT '.$limit;
            //die(print_r($query));
            $values = Device::getInfluxQuery($query, 'last');
            //die(print_r($values));
            $output = $limit == 1 ? $values[0] : $values;
            $output = array_filter($output, function($value) { return !is_null($value) && $value !== ''; });
        }
        catch(\Exception $e)
        {
            return false;
        }

        Cache::put('last-values-'.$cache_name.'-request-time', time(), 86400);
        Cache::put('last-values-'.$cache_name, $output, 86400);

        return $output;
    }

    public function getMeasurementCount($from='2019-01-01 00:00:00', $to=null)
    {
        $where       = $this->influxWhereKeys();
        $where_time  = isset($from) ? 'AND time >= \''.$from.'\' ' : '';
        $where_time .= isset($to) ?  'AND time <= \''.$to.'\' ' : 'AND time <= \''.date('Y-m-d H:i:s').'\' ';
        $group_by_time = 'GROUP BY time(24h) ';

        $query = 'SELECT count("bv") AS "cnt" FROM "sensors" WHERE '.$where.' '.$where_time;
        $cnt = Device::getInfluxQuery($query, 'measurement_count');
        //die(print_r($cnt));
        if (isset($cnt) && isset($cnt[0]['cnt']))
            return intval($cnt[0]['cnt']);

        return 0;
    }

    public function addSensorDefinitionMeasurements($data_array, $value, $input_measurement_id=null, $date=null, $sensor_defs=null)
    {

        if ($input_measurement_id != null)
        {
            // Get the right sensordefinition
            $sensor_def  = null;

            if ($sensor_defs == null)
                $sensor_defs = $this->sensorDefinitions->where('input_measurement_id', $input_measurement_id); // get appropriate sensor definitions
            else
                $sensor_defs = $sensor_defs->where('input_measurement_id', $input_measurement_id); // get appropriate sensor definitions

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
                $before_date = isset($date) ? $date : date('Y-m-d H:i:s');
                if ($sensor_defs->where('updated_at', '<=', $before_date)->count() == 0) // not found before $date, but there are after, so get the first (earliest)
                    $sensor_def = $sensor_defs->first();
                else
                    $sensor_def = $sensor_defs->where('updated_at', '<=', $before_date)->last(); // be aware that last() gets the last value of the ASCENDING list (so most recent)
            }

            // Calculate the extra value based on the sensor definition
            if (isset($sensor_def))
            {
                $measurement_abbr_o = $sensor_def->output_abbr;
                if (!isset($data_array[$measurement_abbr_o]) || $sensor_def->input_measurement_id == $sensor_def->output_measurement_id) // only add value to $data_array if it does not yet exist, or input and output are the same
                {
                    $calibrated_measurement_val = $sensor_def->calibrated_measurement_value($value);
                    if ($calibrated_measurement_val !== null) // do not add sensor measurement is outside measurement min/max value
                        $data_array[$measurement_abbr_o] = $calibrated_measurement_val;
                }
            }
        }
        return $data_array;
    }

    // CLEANED WEIGHT FUNCTIONS
    // get the resolution in minutes
    private function translateResolutionToMinutes($resolution){
        $index = strlen($resolution) -1;
        $value = substr($resolution, 0, $index);
        $unit = substr($resolution, $index);
        $minutes = null;
        if($unit=="m"){
            $minutes = $value;
        }
        elseif($unit=="h"){
            $minutes = $value*60;
        }
        elseif($unit=="d"){
            $minutes = $value*60*24;
        }

        return $minutes;
    }

    // if there is only a small time frame between inspections, this time frame should be queried in a smaller resolution
    private function mapToSmallerResolution($resolution){
        $index = strlen($resolution) -1;
        $unit = substr($resolution, $index);
        if($unit=="m"){
            $resolution = "1m";
        }
        elseif($unit=="h"){
            $resolution = "15m";
        }
        elseif($unit=="d"){
            $resolution = "1h";
        }
        return $resolution;
    }

    public function getCleanedWeightQuery($resolution, $start_date, $end_date, $limit=5000, $threshold=0.75, $frame=2, $timeZone='UTC')
    {
        $fill                 = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
        $whereTime            = 'time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';
        $groupByResolution    = 'GROUP BY time('.$resolution.') FILL('.$fill.')';
        $groupByKeyResolution = 'GROUP BY "key",time('.$resolution.') FILL('.$fill.')';
        $groupBySelectOuter   = 'CUMULATIVE_SUM(SUM(weight_delta)) as net_weight_kg';
        $innerQuery           = $this->getInnerCleanQuery($resolution, $start_date, $end_date, $limit, $threshold, $frame, $timeZone);
        
        if ($innerQuery === null)
            return null;

        $sensorQuery          = 'SELECT '.$groupBySelectOuter.' FROM '.$innerQuery.' WHERE '.$whereTime.' '.$groupByKeyResolution.' LIMIT '.$limit;
        $cleanedWeightQuery   = 'SELECT mean(net_weight_kg) as net_weight_kg FROM ('.$sensorQuery.') WHERE '.$whereTime.' '.$groupByResolution.' LIMIT '.$limit; // this is necessary to fill with null values when data is missing
        
        return $cleanedWeightQuery;
    }

    public function getInnerCleanQuery($resolution, $start_date, $end_date, $limit=5000, $threshold=0.75, $frame=2, $timeZone='UTC')
    {

        $wherekeys=$this->influxWhereKeys();


        $whereTreshold = 'weight_delta < '.$threshold.' AND weight_delta >'.-1*$threshold;

        $inspections = [];
        
        if ($this->hive)
        {
            $inspections = $this -> hive -> getAllInspectionDates();

            sort($inspections);

            // choose inspections in time frame only and convert to utc
            $filteredInspections = [];
            foreach($inspections as $inspection){
                $inspection_stamp = new Moment($inspection, $timeZone);
                $inspection_utc = $inspection_stamp->setTimezone('UTC')->format($this->timeFormat);
                if($inspection_utc >= $start_date & $inspection_utc <= $end_date){
                    array_push($filteredInspections, $inspection_utc);
                }
            }
            $inspections = $filteredInspections;
        }
        #return Response::json( ['status'=>$inspections] );

        // array for time frames shortly before and after inspections
        $inspectionTuples = [];
        // array for other time frames
        $periodTuples = [];

        $inspectionFrame = $frame;

        // create first tuple / or the only tuple needed
        if(count($inspections) != 0){
            $periodTuples[0] = ['\''.$start_date.'\'', '\''.$inspections[0].'\''.' - '.$inspectionFrame.'h', $resolution];
        }else{
            $periodTuples[0] = ['\''.$start_date.'\'', '\''.$end_date.'\'', $resolution];
        }

        // create all tuples
        $length = count($inspections);
        $i = 0;
        while($i <= $length-1){
            $cur = current($inspections);
            if(($i != $length-1)){
                $nex = next($inspections);
            }else{
                $nex = $end_date;
            }
            $i++;

            // check if two or more inspection time frames should be merged into one. update $nex in that case
            $inter = $cur;
            while(($i <= $length-1) && ((strtotime($nex) - strtotime($inter))/(60*60)<= 2*$inspectionFrame )){
                $inter = $nex;
                if(($i != $length-1)){
                    $nex = next($inspections);
                } else{
                    $nex = $end_date;
                }
                $i++;
            }
            // add inspection tuple
            $inspectionTuples[$i] = ['\''.$cur.'\' - '.$inspectionFrame.'h', '\''.$inter.'\' + '.$inspectionFrame.'h'];
            // calculate resolution/ offset needed for period tuple
            // therefore check if period time frame would be smaller than the resolution
            // in that case, the resolution should be smaller than the one used for the outer query
            $difference = round(abs(strtotime($nex) - strtotime($inter)) / 60);
            $transRes = $this->translateResolutionToMinutes($resolution);
            $useRes = $resolution;
            if(!is_null($transRes) & (($difference - 2*60*$inspectionFrame)< $transRes)){
                $useRes = $this -> mapToSmallerResolution($resolution);
            }
            // add period tuple
            if($i <= $length-1){
                $periodTuples[$i+1] = ['\''.$inter.'\' + '.$inspectionFrame.'h + '.$useRes, '\''.$nex.'\' - '.$inspectionFrame.'h', $useRes];
            }else{
                $periodTuples[$i+1] = ['\''.$inter.'\' + '.$inspectionFrame.'h + '.$useRes, '\''.$end_date.'\'', $useRes];
            }
        }

        $whereKeyAndTime  = $wherekeys.' AND time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';

        if($resolution != null)
        {
            $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
            $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
            $groupInspection = 'GROUP BY time(15m)';

            $groupBySelectOuter = 'CUMULATIVE_SUM(SUM(weight_delta)) as weight_kg_noOutlier';
            $groupBySelectInnerInspection = 'DERIVATIVE(MEAN(weight_kg), 15m) as weight_delta';
            #$groupBySelectInnerPeriod = 'derivative(mean(weight_kg), '.$resolution.') as weight_delta';
        }


        $sensors_out = [];

        if ($groupBySelectOuter != null && $groupBySelectOuter != '')
        {
            $inspectionQueries = [];
            foreach($inspectionTuples as $i => $tuple){
                $inspectionQueries[$i] = '(SELECT * FROM ( SELECT '.$groupBySelectInnerInspection.' FROM "sensors" WHERE '.$wherekeys.
                ' AND time >= '.$tuple[0].' AND time <= '.$tuple[1].' '.$groupInspection.' FILL(linear) LIMIT '.$limit.') WHERE '.$whereTreshold.')';
            }
            $periodQueries = [];
            foreach($periodTuples as $i => $tuple){
                 $periodQueries[$i] = '(SELECT DERIVATIVE(MEAN(weight_kg), '.$tuple[2].') as weight_delta FROM "sensors" WHERE '.$wherekeys.
                ' AND time >= '.$tuple[0].' AND time <= '.$tuple[1].' GROUP BY time('.$tuple[2].') FILL(linear) LIMIT '.$limit.')';
            }
               $allQueries = array_merge($periodQueries, $inspectionQueries);
            $innerQuery = implode(', ', $allQueries);
        }
        return $innerQuery;
    }
    // END OF CLEANED WEIGHT FUNCTIONS

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

    public function calibrationsMeasurementAbbreviations()
    {
        return Cache::rememberForever('device-'.$this->id.'-calibrations-measurement-types', function () {
            $out = [];
            $cals = $this->sensorDefinitions()->where('recalculate', '=', true)->whereColumn('input_measurement_id', '!=', 'output_measurement_id')->orderBy('updated_at', 'asc')->get();
            foreach ($cals as $cal) {
                if (isset($cal->input_abbr)) {
                    $m_abbr = $cal->input_abbr;
                    $c_unix = strtotime($cal->updated_at);
                    if (isset($out[$m_abbr])) {
                        $out[$m_abbr][$c_unix] = ['id' => $cal->id, 'output_abbr' => $cal->output_abbr, 'multiplier' => $cal->multiplier, 'offset' => $cal->offset];
                    } else {
                        $out[$m_abbr] = [$c_unix => ['id' => $cal->id, 'output_abbr' => $cal->output_abbr, 'multiplier' => $cal->multiplier, 'offset' => $cal->offset]];
                    }
                }
            }

            return $out;
        });
    }

}
