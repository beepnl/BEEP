<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Sensor;
use App\Setting;
// use App\Transformer\SensorTransformer;
use InfluxDB;
use Response;
use Moment\Moment;
use League\Fractal;
use EllipseSynergie\ApiResponse\Contracts\Response as TransformerResponse;

class SensorController extends Controller
{
    protected $respose;
    protected $valid_sensors = [
            't'         => 'temperature',
            'h'         => 'humidity',
            'p'         => 'air_pressure',
            'w'         => 'weight_sum',
            'l'         => 'light',
            'bv'        => 'bat_volt',
            'w_v'       => 'weight_combined_kg',
            'w_fl'      => 'weight_front_left',
            'w_fr'      => 'weight_front_right',
            'w_bl'      => 'weight_back_left',
            'w_br'      => 'weight_back_right',
            's_fan_4'   => 'sound_fanning_4days',
            's_fan_6'   => 'sound_fanning_6days',
            's_fan_9'   => 'sound_fanning_9days',
            's_fly_a'   => 'sound_flying_adult',
            's_tot'     => 'sound_total',
            't_i'       => 'temp_inside',
            'bc_i'      => 'bee_count_in',
            'bc_o'      => 'bee_count_out',
            'weight_kg' => 'weight_kg',
            'weight_kg_corrected' => 'weight_kg_corrected',
            'rssi'      => 'rssi',
            'snr'       => 'snr',
            'lat'       => 'lat',
            'lon'       => 'lon',
            's_bin098_146Hz' => '098_146Hz',
            's_bin146_195Hz' => '146_195Hz',
            's_bin195_244Hz' => '195_244Hz',
            's_bin244_293Hz' => '244_293Hz',
            's_bin293_342Hz' => '293_342Hz',
            's_bin342_391Hz' => '342_391Hz',
            's_bin391_439Hz' => '391_439Hz',
            's_bin439_488Hz' => '439_488Hz',
            's_bin488_537Hz' => '488_537Hz',
            's_bin537_586Hz' => '537_586Hz',        
    ];
    protected $output_sensors = [
            't',
            'h',
            'p',
            'l',
            'bv',
            's_fan_4',
            's_fan_6',
            's_fan_9',
            's_fly_a',
            's_tot',
            't_i',
            'bc_i',
            'bc_o',
            'weight_kg',
            'weight_kg_corrected',
            'rssi',
            'snr',
            'lat',
            'lon',
            's_bin098_146Hz',
            's_bin146_195Hz',
            's_bin195_244Hz',
            's_bin244_293Hz',
            's_bin293_342Hz',
            's_bin342_391Hz',
            's_bin391_439Hz',
            's_bin439_488Hz',
            's_bin488_537Hz',
            's_bin537_586Hz',     
        ];
    protected $precision   = 's';
    protected $timeFormat  = 'Y-m-d H:i:s';
    protected $maxDataPoints = 5000;
 
    public function __construct(TransformerResponse $response)
    {
        $this->response = $response;
    }
    
    protected function get_user_sensor(Request $request)
    {
        
        $sensors = $request->user()->sensors();
        if ($sensors->count() > 0)
        {
            if ($request->has('id') && $request->input('id') != 'null')
            {
                $id = $request->input('id');
                $check_sensor = $sensors->findOrFail($id);
            }
            else if ($request->has('key') && $request->input('key') != 'null')
            {
                $key = $request->input('key');
                $check_sensor = $sensors->where('key', $key)->first();
            }
            else if ($request->has('hive_id') && $request->input('hive_id') != 'null')
            {
                $hive_id = $request->input('hive_id');
                $check_sensor = $sensors->where('hive_id', $hive_id)->first();
            }
            else
            {
                $check_sensor = $sensors->first();
            }
            
            if(isset($check_sensor))
                return $check_sensor;
        }
        return Response::json('No key found for user', 404);
    }
    protected function convertSensorStringToArray($data_string)
    {
        $out = [];
        $arr = explode("|", $data_string);
        foreach ($arr as $str) 
        {
            $str_arr = explode(":",$str);
            if (count($str_arr) > 1)
                $out[$str_arr[0]] = $str_arr[1];
        }
        return $out;
    }
    protected function calculateWeightKg($data_array, $user_id, $sensor_id)
    {
        $totalWeight = 0;
        //$log = [];
        $userSensorIds = Sensor::where('user_id', $user_id)->get()->pluck('id')->toArray();

        foreach($data_array as $sensor => $value)
        {
            if (strpos($sensor, "w_") !== false && (strlen($sensor) == 4 || $sensor == "w_v")) // 4 sensors w_fl, w_fr, w_bl, w_br || 1 combined sensor w_v
            {
                $sensor_offset = null;
                if (Setting::where('user_id', $user_id)->whereIn('number', $userSensorIds)->count() > 0) // calibrations per sensor
                {
                    $sensor_offset = Setting::where('user_id', $user_id)->where('name', $sensor)->where('number', $sensor_id)->orderByDesc('created_at')->first();
                    $sensor_factor = Setting::where('user_id', $user_id)->where('name', $sensor.'_kg_per_val')->where('number', $sensor_id)->orderByDesc('created_at')->first();
                }
                else if (Setting::where('user_id', $user_id)->count() > 0)
                {
                    $sensor_offset = Setting::where('user_id', $user_id)->where('name', $sensor)->orderByDesc('created_at')->first();
                    $sensor_factor = Setting::where('user_id', $user_id)->where('name', $sensor.'_kg_per_val')->orderByDesc('created_at')->first();
                }

                if ($sensor_offset) // offset available
                {
                    $factor = $sensor_factor ? floatval($sensor_factor->value) : 1;
                    $weight = (floatval($value) - floatval($sensor_offset->value)) * $factor;
                    $totalWeight += $weight;
                    //$log[] = ('user: '.$user_id.' hive_id:'.$hive_id.' sensor name: '.$sensor.' s='.$value.' s_o='.$sensor_offset->value.' f='.$factor.' w='.$weight.' tot='.$totalWeight);
                    //die("sensor_offset=$sensor_offset sensor_factor=$sensor_factor factor=$factor weight=$weight totalWeight=$totalWeight");
                }
                else
                {
                    $totalWeight += floatval($value);
                }
            }
        }
        //die(print_r($log));
        return $totalWeight;
    }

    // Public functions
    public function index(Request $request)
    {
        $sensor_amount = $request->user()->sensors()->count();
        if ($sensor_amount == 0)
            return Response::json('No sensors found', 404);

        $sensors = $request->user()->sensors()->get();

        // if ($request->has('with_values'))
        // {
        //     foreach ($sensors  as $sensor) 
        //     {
        //         try
        //         {
        //             $client = new \Influx;
        //             $result = $client::query('SELECT "name",* from "sensors" WHERE "key" = \''.$sensor->key.'\' AND time > now() - 365d GROUP BY "name" ORDER BY time DESC LIMIT 1');
        //             $values = $result->getPoints();
        //             $sensor['values'] = new Fractal\Resource\Collection($values, new SensorTransformer);
        //         }
        //         catch(\Exception $e)
        //         {
        //             //return Response::json('sensor-get-error', 500);
        //         }
        //     }
        // }
        
        return Response::json($sensors);
    }

    public function lastvalues(Request $request)
    {
        $sensor = $this->get_user_sensor($request);
        $output = null;

        // Add distiguishing betw
        try
        {
            $client = new \Influx;
            $result = $client::query('SELECT * from "sensors" WHERE "key" = \''.$sensor->key.'\' AND time > now() - 365d GROUP BY "name,time" ORDER BY time DESC LIMIT 1');
            $values = $result->getPoints();
            //die(print_r($values));
            $output = $values[0];
            //$output = new Fractal\Resource\Collection($values, new SensorTransformer);
            //die(print_r($output));
        }
        catch(\Exception $e)
        {
            return Response::json('sensor-get-error', 500);
        }

        if ($output)
            return Response::json($output);

        return Response::json('error', 404);
    }

    
    private function floatify_sensor_val($arr, $key)
    {
        if (isset($arr[$key]))
        {
            $value = $arr[$key];

            if ($value == 0)
            {
                unset($arr[$key]);
            }
            else
            {
                switch($key)
                {
                    case 't':
                    case 't_i':
                        $arr[$key] = ($value / 5) - 10; // de-tempfy
                        break;
                    case 'h':
                        $arr[$key] = $value / 2;
                        break;
                    case 'bv':
                        $arr[$key] = $value / 10;
                        break;
                    case 'w_v':
                        $arr[$key] = $value;
                        break;
                    case 'w_fl':
                    case 'w_fr':
                    case 'w_bl':
                    case 'w_br':
                        $arr[$key] = $value / 300; // de-weightify
                        break;
                }
            }
        }

        return $arr;
    }

    public function lora_sensors(Request $request)
    {
        $data_array = [];
        
        if ($request->has('LrnDevEui')) // KPN Simpoint msg
            $data_array['key'] = $request->input('LrnDevEui');
        if ($request->has('DevEUI_uplink.LrrRSSI'))
            $data_array['rssi'] = $request->input('DevEUI_uplink.LrrRSSI');
        if ($request->has('DevEUI_uplink.LrrSNR'))
            $data_array['snr']  = $request->input('DevEUI_uplink.LrrSNR');
        if ($request->has('DevEUI_uplink.LrrLAT'))
            $data_array['lat']  = $request->input('DevEUI_uplink.LrrLAT');
        if ($request->has('DevEUI_uplink.LrrLON'))
            $data_array['lon']  = $request->input('DevEUI_uplink.LrrLON');

        if ($request->has('DevEUI_uplink.payload_hex'))
            $data_array = array_merge($data_array, $this->decode_simpoint_payload($request->input('DevEUI_uplink.payload_hex')));

        if (isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br'])) // v7 firmware
        {
            // - H   -> *2 (range 0-200)
            // - T   -> -10 -> +40 range (+10, *5), so 0-250 is /5, -10
            // - W_E -> *1
            $data_array = $this->floatify_sensor_val($data_array, 't');
            $data_array = $this->floatify_sensor_val($data_array, 't_i');
            $data_array = $this->floatify_sensor_val($data_array, 'h');
            $data_array = $this->floatify_sensor_val($data_array, 'bv');
            $data_array = $this->floatify_sensor_val($data_array, 'w_v');
            $data_array = $this->floatify_sensor_val($data_array, 'w_fl');
            $data_array = $this->floatify_sensor_val($data_array, 'w_fr');
            $data_array = $this->floatify_sensor_val($data_array, 'w_bl');
            $data_array = $this->floatify_sensor_val($data_array, 'w_br');
        }        

        //die(print_r($data_array));

        Storage::disk('local')->put('lora_sensors.log', '['.json_encode($request->input()).','.json_encode($data_array).']');
        return $this->storeMeasurements($data_array);
    }

    private function decode_simpoint_payload($payload)
    {
        $out = [];
        $beep_sensors = [
            't'  , // 0
            'h'  , // 1
            'w_v',
            't_i',
            'a_i',
            'bv' ,
            's_tot',
            's_fan_4',
            's_fan_6',
            's_fan_9',
            's_fly_a',
            'w_fl_hb',
            'w_fl_lb',
            'w_fr_hb',
            'w_fr_lb',
            'w_bl_hb',
            'w_bl_lb',
            'w_br_hb',       
            'w_br_lb', // 18  
        ];

        $minLength = min(strlen($payload)/2, count($beep_sensors));

        for ($i=0; $i < $minLength; $i++) 
        { 
            if (strlen($payload) > count($beep_sensors)*2)
            {
                $index = $i * 4 + 2; 
            }
            else
            {
                $index = $i * 2;
            }
            $sensor = $beep_sensors[$i];
            $hexval = substr($payload, $index, 2);

            if (strpos($sensor, '_hb') !== false) // step 1 of 2 byte value
            {
                $sensor = substr($sensor, 0, strpos($sensor, '_hb'));
                $out[$sensor] = $hexval;
            } 
            else if (strpos($sensor, '_lb') !== false) // step 2 of 2 byte value
            {
                $sensor = substr($sensor, 0, strpos($sensor, '_lb'));
                $totalHexVal  = $out[$sensor].$hexval;
                $out[$sensor] = hexdec($totalHexVal);
            }
            else
            {
                $out[$sensor] = hexdec($hexval);
            }
        }
        //die(print_r($minLength));
        return $out;
    }


    public function store(Request $request)
    {
        // Check for valid data 
        if ($request->has('payload_fields')) // TTN HTTP POST
        {
            $data_array = $request->input('payload_fields');
            if (isset($data_array['key']))
            {
                // keep $data_array['key']
            }
            else if ($request->has('hardware_serial'))
            {
                $data_array['key'] = $request->input('hardware_serial'); // LoRa WAN = Device EUI
            }

            if ($request->has('metadata.gateways.0.rssi'))
                $data_array['rssi'] = $request->input('metadata.gateways.0.rssi');
            if ($request->has('metadata.gateways.0.snr'))
                $data_array['snr']  = $request->input('metadata.gateways.0.snr');
        }
        else if ($request->has('data')) // Check for sensor string (colon and pipe devided) fw v1-3
        {
            $data_array = $this->convertSensorStringToArray($request->input('data'));
        }
        else // Assume post data input
        {
            $data_array = $request->input();
        }
        
        //die(print_r($data_array));
        return $this->storeMeasurements($data_array);
    }



    private function storeMeasurements($data_array)
    {
        if (!in_array('key', array_keys($data_array)) || $data_array['key'] == '' || $data_array['key'] == null)
            return Response::json('No key provided', 400);

        // Check if key is valid
        $sensor_key     = $data_array['key']; // save sensor data under sensor key
        $check_sensor   = Sensor::where('key', $sensor_key)->first();
        if(!$check_sensor)
             return Response::json('No valid key provided', 401);

        $client = new \Influx;
        unset($data_array['key']);

        $sensor_user_id = $check_sensor->user_id;
        $sensor_id      = $check_sensor->id;
        
        if (isset($data_array['w_v']) || isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br'])) 
        {
            $weight_kg = $this->calculateWeightKg($data_array, $sensor_user_id, $sensor_id);
            $data_array['weight_kg'] = $weight_kg;

            if (isset($data_array['t']) && isset($data_array['w_v']) == false)
                $data_array['weight_kg_corrected'] = $weight_kg - (0.0746 * $data_array['t']);

        }
        // store posted data
        $sensors = [];
        $points  = [];
        $sensor_time = time();
        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, array_keys($this->valid_sensors)) )
            {
                $sensor = new \stdClass();
                $sensor->name   = $key;
                $sensor->value  = floatval($value);  
                array_push($points, 
                    new InfluxDB\Point(
                        'sensors', // name of the measurement
                        null, // the measurement value
                        ['key' => $sensor_key], // optional tags
                        ["$key" => $sensor->value], // key value pairs
                        $sensor_time // Time precision has to be set to seconds!
                    )
                );
            }
        }
        //die(print_r($points));
        $stored = null;
        try
        {
            $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
        }
        catch(\Exception $e)
        {
            // gracefully do nothing
        }
        if($stored) 
        {
            return Response::json("saved", 201);
        } 
        else
        {
            return Response::json('sensor-write-error', 500);
        }
    }
    // public function data(Request $request, $name)
    // {
    //     $client = new \Influx;
    //     //Get the sensor
    //     $sensor  = $this->get_user_sensor($request);
        
    //     $sensor_name = array_search($name, $this->valid_sensors);
    //     if ($sensor_name)
    //     {
    //         try
    //         {
    //             $result  = $client::query('SELECT "name",* from "sensors" WHERE "key" = \''.$sensor->key.'\' AND "name" = \''.$sensor_name.'\' AND time > now() - 24h GROUP BY "name" ORDER BY time DESC LIMIT 1000');
    //             $sensors = $result->getPoints();
    //             if ($sensors) 
    //             {
    //                 return $this->response->withCollection($sensors, new SensorTransformer());
    //             }
    //         }
    //         catch(\Exception $e)
    //         {
    //             return Response::json('sensor-get-error', 500);
    //         }
    //     }
    //     return Response::json('sensor-none-error', 500);
    // }

   
    public function data(Request $request)
    {
        //Get the sensor
        $sensor  = $this->get_user_sensor($request);
        
        $client = new \Influx;
        $first  = $client::query('SELECT * FROM "sensors" WHERE "key" = \''.$sensor->key.'\' ORDER BY time ASC LIMIT 1')->getPoints(); // get first sensor date
        
        if (count($first) == 0)
            Response::json('sensor-none-error', 500);
        
        //$firstSensorMoment = new Moment(substr($first[0]['time'],0,10));
        
        $all_names = array_keys($this->valid_sensors);
        $names     = $request->input('names', $all_names);
        $interval  = $request->input('interval','day');
        $index     = $request->input('index',0);
        $timeGroup = $request->input('timeGroup','day');
       
        if (count($names) == 0)
            Response::json('sensor-none-error', 500);
        $durationInterval = $interval + 's';
        $requestInterval  = $interval;
        $resolution       = null;
        $staTimestamp = new Moment();
        $staTimestamp->setTimezone('Europe/Amsterdam');
        $endTimestamp = new Moment();
        $endTimestamp->setTimezone('Europe/Amsterdam');
        // if (timeGroup != null)
        // {
            switch($interval)
            {
                case 'year':
                    $resolution = '1d';
                    $staTimestamp->subtractYears($index);
                    $endTimestamp->subtractYears($index);
                    break;
                case 'month':
                    $resolution = '3h';
                    $staTimestamp->subtractMonths($index);
                    $endTimestamp->subtractMonths($index);
                    break;
                case 'week':
                    $requestInterval = 'week';
                    $resolution = '1h';
                    $staTimestamp->subtractWeeks($index);
                    $endTimestamp->subtractWeeks($index);
                    break;
                case 'day':
                    $resolution = '10m';
                    $staTimestamp->subtractDays($index);
                    $endTimestamp->subtractDays($index);
                    break;
                case 'hour':
                    $resolution = '2m';
                    $staTimestamp->subtractHours($index);
                    $endTimestamp->subtractHours($index);
                    break;
            }
        //}
        $staTimestampString = $staTimestamp->startOf($requestInterval)->setTimezone('UTC')->format($this->timeFormat);
        $endTimestampString = $endTimestamp->endOf($requestInterval)->setTimezone('UTC')->format($this->timeFormat);    
        $groupBySelect      = null;
        $groupByResolution  = '';
        $limit              = 'LIMIT '.$this->maxDataPoints;
        $options            = ['precision'=> $this->precision];
        
        if($resolution != null)
        {
            $groupByResolution = 'GROUP BY time('.$resolution.') fill(null)';
            $queryList = [];
            for ($i = 0; $i < count($names); $i++) 
            {
                $name = $names[$i];
                if (in_array($name, $this->output_sensors))
                {
                    $query = 'SELECT COUNT("'.$name.'") AS "count" FROM "sensors" WHERE "key" = \''.$sensor->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$limit;
                    $result  = $client::query($query, $options);
                    $sensors = $result->getPoints();
                    if (count($sensors) > 0 && $sensors[0]['count'] > 0)
                        $queryList[] = 'MEAN("'.$name.'") AS "'.$name.'"';
                }
            }
            $groupBySelect = implode(', ', $queryList);
        }
        
        // try
        // {
        $sensors_out = [];
        $old_values  = false;
        
        if ($groupBySelect != null) 
        {
            $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE "key" = \''.$sensor->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;
            $result      = $client::query($sensorQuery, $options);
            $sensors_out = $result->getPoints();
        }
        else
        {
            // check if values are stored in the new (column), or the old (name) way.
            $old_vals = $client::query('SELECT COUNT("value") FROM "sensors" WHERE "key" = \''.$sensor->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' LIMIT 1')->getPoints();
            if (count($old_vals) > 0)
            {
                $old_values = true;
                for ($i = 0; $i < count($names); $i++) 
                {
                    $name = $names[$i];
                    if (in_array($name, $this->output_sensors))
                    {
                        $sensor_vals = $client::query('SELECT MEAN("value") AS "'.$name.'" FROM "sensors" WHERE "name" = \''.$name.'\' AND "key" = \''.$sensor->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit)->getPoints();
                        if (count($sensor_vals) > 0)
                        {
                            if (count($sensors_out) == 0)
                            {
                                $sensors_out = $sensor_vals;
                            }
                            else
                            {
                                foreach ($sensors_out as $ind => $value) 
                                {
                                    if ($value['time'] == $sensor_vals[$ind]['time'])
                                        $sensors_out[$ind][$name] = $sensor_vals[$ind][$name];
                                }   
                            }
                        }
                    }
                }
            }
        }
        return Response::json( ['id'=>$sensor->id, 'interval'=>$interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'old_values'=>$old_values] );
        // }
        // catch(\Exception $e)
        // {
        // }
    }
}