<?php
 
namespace App\Transformer;
use App\Measurement;
use App\Device;
use InfluxDB;


class HighWeightFix {
 
    public static function cacheRequestRate($name)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count');
        else
            Cache::put($name.'-count', 1);

    }

    private static function storeInfluxData($data_array, $dev_eui, $unix_timestamp, $valid_sensors)
    {
        // store posted data
        $client    = new \Influx;
        $points    = [];
        $unix_time = isset($unix_timestamp) ? $unix_timestamp : time();

        $valid_sensor_keys = array_keys($valid_sensors);

        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, $valid_sensor_keys) )
            {
                array_push($points, 
                    new InfluxDB\Point(
                        'sensors',                  // name of the measurement
                        null,                       // the measurement value
                        ['key' => $dev_eui],     // optional tags
                        ["$key" => floatval($value)], // key value pairs
                        $unix_time                  // Time precision has to be set to InfluxDB\Database::PRECISION_SECONDS!
                    )
                );
            }
        }
        //die(print_r($points));
        $stored = false;
        if (count($points) > 0)
        {
            try
            {
                HighWeightFix::cacheRequestRate('influx-write');
                $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                // gracefully do nothing
            }
        }
        return $stored;
    }

    public static function getInfluxQuery($query)
    {
        $client  = new \Influx;
        $options = ['precision'=> 's', 'epoch'=>'s'];
        $values  = [];

        try{
            HighWeightFixcacheRequestRate('influx-get');
            HighWeightFixcacheRequestRate('influx-weight');
            $result  = $client::query($query, $options);
            $values  = $result->getPoints();
        } catch (InfluxDB\Exception $e) {
            // return Response::json('influx-group-by-query-error', 500);
        }
        return $values;
    }

    public static function fix($device_key=null, $values_per_device=10)
    {
        $cor_cnt= 0;
        date_default_timezone_set('UTC');

        $start  = '2021-06-02 00:00:00';
        $end    = '2021-06-18 18:00:00';
        $where  = '"w_v" = "weight_kg" AND "w_v" > 0';
        $where .= $device_key != null ? ' AND ("key" = \''.$device_key.'\' OR "key" = \''.strtolower($device_key).'\' OR "key" = \''.strtoupper($device_key).'\')' : '';
        $limit  = ' LIMIT 1';
        $query  = 'SELECT * FROM "sensors" WHERE '.$where.' AND time >= \''.$start.'\' AND time < \''.$end.'\' GROUP BY "key" ORDER BY time ASC'.$limit;

        $result = HighWeightFix::getInfluxQuery($query);
        $keys   = [];
        
        foreach ($result as $values)
        {
            if(isset($values['key']))
                $keys[] = strtolower($values['key']);
        }        

        $all_devices = Device::whereIn('key',$keys)->get();

        foreach ($all_devices as $d)
        {
            $key = $d->key;
            if ($device_key == null || strtolower($device_key) == strtolower($key))
            {
                $sensor_def_count = $d->sensorDefinitions->where('input_measurement_id', 7)->where('output_measurement_id', 20)->count();
                if ($sensor_def_count > 0)
                {
                    $where  = '"w_v" = "weight_kg" AND "w_v" > 0';
                    $where .= ' AND ("key" = \''.$key.'\' OR "key" = \''.strtolower($key).'\' OR "key" = \''.strtoupper($key).'\')';
                    $limit  = $values_per_device != null ? ' LIMIT '.$values_per_device : '';
                    $query  = 'SELECT * FROM "sensors" WHERE '.$where.' AND time >= \''.$start.'\' AND time < \''.$end.'\' GROUP BY "key" ORDER BY time ASC'.$limit;

                    $valid_sensors  = Measurement::getValidMeasurements();

                    $data = HighWeightFix::getInfluxQuery($query);

                    foreach ($data as $v) 
                    {
                        $v = array_filter($v, function($value) { return !is_null($value) && $value !== ''; }); // filter out empty values
                        
                        $time = $v['time'];
                        $date = date('Y-m-d H:i:s', $time);
                        $v    = $d->addSensorDefinitionMeasurements($v, $v['w_v'], 7, $date);

                        // store corrected value (+ other unchanged values)
                        if (isset($v['weight_kg']) && $v['weight_kg'] != null && $v['w_v'] != $v['weight_kg'])
                        {
                            print_r('device '.$key.' corrected weight_kg from '.$v['w_v'].' to '.$v['weight_kg']." @ $date ($time) \n");
                            $stored = HighWeightFix::storeInfluxData($v, $key, $time, $valid_sensors);

                            if ($stored)
                                $cor_cnt++;
                        }
                        else
                        {
                            print_r('device '.$key." Not saving weight_kg value: ".(isset($v['weight_kg']) ? $v['weight_kg'] : 'null')."\n");
                        }
                    }
                }
                else
                {
                    print_r('device '.$key." has $sensor_def_count weight sensorDefinitions.\n");
                }
            }

        }
        return "Corrected $cor_cnt weight_kg values";
    }
}