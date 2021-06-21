<?php
 
namespace App\Transformer;
use App\Measurement;
use App\Device;
use InfluxDB;


class HighWeightFix {
 
    

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
                $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                // gracefully do nothing
            }
        }
        return $stored;
    }

    public static function fix($device_key=null, $values_per_device=10)
    {

        //$device = Device::findOrFail($sensor_id); 
        //$key    = 
        $start  = '2021-06-02 09:00:00';
        $end    = '2021-06-18 18:00:00';
        $where  = '"w_v" = "weight_kg" AND "w_v" > 0';
        $where .= $device_key != null ? ' AND ("key" = \''.$device_key.'\' OR "key" = \''.strtolower($device_key).'\' OR "key" = \''.strtoupper($device_key).'\')' : '';
        $limit  = $values_per_device != null ? ' LIMIT '.$values_per_device : '';
        $query  = 'SELECT * FROM "sensors" WHERE '.$where.' AND time >= \''.$start.'\' AND time < \''.$end.'\' GROUP BY "key" ORDER BY time ASC'.$limit;
        $options= ['precision'=>'s', 'epoch'=>'s']; // get 

        $valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();

        $data   = [];
        try{
            $client = new \Influx; 
            $data   = $client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return 'influx-query-error: '.$query;
        }

        $key    = null;
        $device = null;
        $cor_cnt= 0;

        date_default_timezone_set('UTC');
        
        foreach ($data as $v) 
        {

            $v = array_filter($v, function($value) { return !is_null($value) && $value !== ''; }); // filter out empty values

            if ($v['key'] != $key)
            {
                $key    = $v['key'];
                $device = Device::where('key', $key)->orderBy('created_at', 'desc')->first();
            }

            if ($device)
            {
                $time = $v['time'];
                $date = date('Y-m-d H:i:s', $time);
                $v    = $device->addSensorDefinitionMeasurements($v, $v['w_v'], 'w_v', $date);

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
                    print_r('device '.$key.' has '.$device->sensorDefinitions->where('output_measurement_id', 20)->where('updated_at', '<', $date)->count()." sensorDefinitions < $date. Not saving weight_kg value: ".(isset($v['weight_kg']) ? $v['weight_kg'] : 'null')." \n");
                }

            }
            //die(print_r($v));

        }
        return "Corrected $cor_cnt weight_kg values";
    }
}