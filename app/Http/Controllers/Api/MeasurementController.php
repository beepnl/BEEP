<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use App\Measurement;
// use App\Transformer\SensorTransformer;
use Validator;
use InfluxDB;
use Response;
use Moment\Moment;
use League\Fractal;
use App\Http\Requests\PostSensorRequest;

use App\Http\Controllers\Api\MeasurementLegacyCalculationsTrait;

/**
 * @group Api\MeasurementController
 * Store and retreive sensor data (both LoRa and direct API POSTs)
 */
class MeasurementController extends Controller
{
    use MeasurementLegacyCalculationsTrait;

    protected $respose;
    protected $valid_sensors  = [];
    protected $output_sensors = [];
    protected $precision      = 's';
    protected $timeFormat     = 'Y-m-d H:i:s';
    protected $maxDataPoints  = 5000;
 
    public function __construct()
    {
        // make sure to add to the measurements DB table w_v_kg_per_val, w_fl_kg_per_val, etc. and w_v_offset, w_fl_offset to let the calibration functions function correctly
        $this->valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $this->output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();
        //die(print_r($this->valid_sensors));
    }
   

    // Sensor measurement functions

    protected function get_user_sensor(Request $request, $mine = false)
    {
        $this->validate($request, [
            'id'        => 'nullable|integer|exists:sensors,id',
            'key'       => 'nullable|integer|exists:sensors,key',
            'hive_id'   => 'nullable|integer|exists:hives,id',
        ]);
        
        $devices = $request->user()->allDevices($mine); // inlude user Group hive sensors ($mine == editable)
        if ($devices->count() > 0)
        {
            if ($request->filled('id') && $request->input('id') != 'null')
            {
                $id = $request->input('id');
                $check_sensor = $devices->findOrFail($id);
            }
            else if ($request->filled('key') && $request->input('key') != 'null')
            {
                $key = $request->input('key');
                $check_sensor = $devices->where('key', $key)->first();
            }
            else if ($request->filled('hive_id') && $request->input('hive_id') != 'null')
            {
                $hive_id = $request->input('hive_id');
                $check_sensor = $devices->where('hive_id', $hive_id)->first();
            }
            else
            {
                $check_sensor = $devices->first();
            }
            
            if(isset($check_sensor))
                return $check_sensor;
        }
        return Response::json('No key found for user', 404);
    }

    
    private function last_sensor_values_array($sensor, $fields='*', $limit=1)
    {
        $fields = $fields != '*' ? '"'.$fields.'"' : '*';
        $groupby= $fields == '*' || strpos(',' ,$fields) ? 'GROUP BY "name,time"' : '';
        $output = null;
        try
        {
            $client = new \Influx;
            $query  = 'SELECT '.$fields.' from "sensors" WHERE "key" = \''.$sensor->key.'\' AND time > now() - 365d '.$groupby.' ORDER BY time DESC LIMIT '.$limit;
            //die(print_r($query));
            $result = $client::query($query);
            $values = $result->getPoints();
            //die(print_r($values));
            $output = $limit == 1 ? $values[0] : $values;
        }
        catch(\Exception $e)
        {
            return false;
        }
        return $output;
    }

    private function last_sensor_measurement_time_value($sensor, $name)
    {
        $arr = $this->last_sensor_values_array($sensor, $name);

        if ($arr && count($arr) > 0 && in_array($name, array_keys($arr)))
            return $arr[$name];

        return null;
    }

    private function last_sensor_increment_values($sensor, $data_array=null)
    {
        $output = [];
        $limit  = 2;

        if ($data_array != null)
        {
            $output[0] = $data_array;
            $output[1] = $this->last_sensor_values_array($sensor, implode('","',array_keys($data_array)), 1);
        }
        else
        {
            $output = $this->last_sensor_values_array($sensor, implode('","',$this->output_sensors), $limit);
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


    // requires at least ['name'=>value] to be set
    private function storeInfluxData($data_array, $dev_eui, $unix_timestamp)
    {
        // store posted data
        $client    = new \Influx;
        $points    = [];
        $unix_time = isset($unix_timestamp) ? $unix_timestamp : time();

        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, array_keys($this->valid_sensors)) )
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

    private function createOrUpdateDefinition($device, $abbr_in, $abbr_out, $offset=null, $multiplier=null)
    {
        $measurement_in  = Measurement::where('abbreviation',$abbr_in)->first();
        $measurement_out = Measurement::where('abbreviation',$abbr_out)->first();
        if ($measurement_in && $measurement_out)
        {
            $def = $device->sensorDefinitions()->where('input_measurement_id', $measurement_in->id)->where('output_measurement_id', $measurement_out->id)->first();
            if ($def && (isset($offset) || isset($multiplier)) ) 
            {
                if (isset($offset))
                    $def->offset = $offset;

                if (isset($multiplier))
                    $def->multiplier = $multiplier;

                $def->save();
            }
            else
            {
                $device->sensorDefinitions()->create(['input_measurement_id'=>$measurement_in->id, 'output_measurement_id'=>$measurement_out->id, 'offset'=>$offset, 'multiplier'=>$multiplier]);
            }
        }
    }

    private function storeMeasurements($data_array)
    {
        if (!in_array('key', array_keys($data_array)) || $data_array['key'] == '' || $data_array['key'] == null)
        {
            Storage::disk('local')->put('sensors/sensor_no_key.log', json_encode($data_array));
            return Response::json('No key provided', 400);
        }

        // Check if key is valid
        $dev_eui = $data_array['key']; // save sensor data under sensor key
        $device  = Device::where('key', $dev_eui)->first();
        if($device)
        {
            $this->storeDeviceMeta($dev_eui);
        }
        else
        {
            Storage::disk('local')->put('sensors/sensor_invalid_key.log', json_encode($data_array));
            return Response::json('No valid key provided', 401);
        }


        unset($data_array['key']);

        // New sensor data calculations
        foreach ($data_array as $abbr => $val) 
        {
            $measurement = Measurement::where('abbreviation',$abbr)->first();
            if ($measurement)
            {
                $sensor_defs = $device->sensorDefinitions()->where('input_measurement_id', $measurement->id)->get();
                if ($sensor_defs)
                {
                    foreach ($sensor_defs as $def) 
                    {
                        $abbr_o = $def->output_abbr;
                        $data_array[$abbr_o] = $def->calibrated_measurement_value($val);
                    }
                }
                else if ($abbr == 'w_v') // make new calibration values based on stored ones
                {
                    $influx_offset = floatval($this->last_sensor_measurement_time_value($device, $abbr.'_offset'));
                    $influx_multi  = floatval($this->last_sensor_measurement_time_value($device, $abbr.'_kg_per_val'));

                    if ($influx_offset != 0 || $influx_multi != 0)
                        $this->createOrUpdateDefinition($device, $abbr, 'weight_kg', $influx_offset, $influx_multi);
                }
            }
            
        }

        // Legacy weight calculation from 2-4 load cells
        if (isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br']) || isset($data_array['w_v'])) 
        {
            // check if calibration is required
            $calibrate = $this->last_sensor_measurement_time_value($device, 'calibrating_weight');
            if (floatval($calibrate) > 0)
                $this->calibrate_weight_sensors($device, $calibrate, false, $data_array);

            // take into account offset and multi
            $weight_kg = $this->calculateWeightKg($device, $data_array);
            $data_array['weight_kg'] = $weight_kg;

            // check if we need to compensate weight for temp
            $data_array = $this->add_weight_kg_corrected_with_temperature($device, $data_array);
        }
        
        
        $time = time();
        if (isset($data_array['time']))
            $time = intVal($data_array['time']);

        $stored = $this->storeInfluxData($data_array, $dev_eui, $time);
        if($stored) 
        {
            return Response::json("saved", 201);
        } 
        else
        {
            Storage::disk('local')->put('sensors/sensor_write_error.log', json_encode($data_array));
            return Response::json('sensor-write-error', 500);
        }
    }

    /**
    api/sensors/lastvalues GET
    Request last measurement values of all sensor measurements from a sensor (Device)
    @authenticated
    @bodyParam key string DEV EUI to look up the sensor (Device)
    @bodyParam id integer ID to look up the sensor (Device)
    @bodyParam hive_id integer Hive ID to look up the sensor (Device)
    */
    public function lastvalues(Request $request)
    {
        $sensor = $this->get_user_sensor($request);
        $output = $this->last_sensor_values_array($sensor, implode('","',$this->output_sensors));

        if ($output === false)
            return Response::json('sensor-get-error', 500);
        else if ($output !== null)
            return Response::json($output);

        return Response::json('error', 404);
    }


    private function parse_kpn_payload($request_data)
    {
        $data_array = [];
        //die(print_r($request_data));
        if (isset($request_data['LrnDevEui'])) // KPN Simpoint msg
            if (Device::all()->where('key', $request_data['LrnDevEui'])->count() > 0)
                $data_array['key'] = $request_data['LrnDevEui'];

        if (isset($request_data['DevEUI_uplink']['DevEUI'])) // KPN Simpoint msg
            if (Device::where('key', $request_data['DevEUI_uplink']['DevEUI'])->count() > 0)
                $data_array['key'] = $request_data['DevEUI_uplink']['DevEUI'];

        if (isset($request_data['DevEUI_location']['DevEUI'])) // KPN Simpoint msg
            if (Device::where('key', $request_data['DevEUI_location']['DevEUI'])->count() > 0)
                $data_array['key'] = $request_data['DevEUI_location']['DevEUI'];


        if (isset($request_data['DevEUI_uplink']['LrrRSSI']))
            $data_array['rssi'] = $request_data['DevEUI_uplink']['LrrRSSI'];
        if (isset($request_data['DevEUI_uplink']['LrrSNR']))
            $data_array['snr']  = $request_data['DevEUI_uplink']['LrrSNR'];
        if (isset($request_data['DevEUI_uplink']['LrrLAT']))
            $data_array['lat']  = $request_data['DevEUI_uplink']['LrrLAT'];
        if (isset($request_data['DevEUI_uplink']['LrrLON']))
            $data_array['lon']  = $request_data['DevEUI_uplink']['LrrLON'];

        if (isset($request_data['DevEUI_uplink']['payload_hex']))
            $data_array = array_merge($data_array, $this->decode_simpoint_payload($request_data['DevEUI_uplink']['payload_hex']));

        if (isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br'])) // v7 firmware
        {
            // - H   -> *2 (range 0-200)
            // - T   -> -10 -> +40 range (+10, *5), so 0-250 is /5, -10
            // - W_E -> -20 -> +80 range (/2, +10, *5), so 0-250 is /5, -10, *2
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
        return $data_array;
    }
    
    private function storeDeviceMeta($key, $field=null, $value=null)
    {
        $device = Device::where('key', $key)->first();

        if ($device == null && $field == 'hardware_id' && $value !== null && env('ALLOW_DEVICE_CREATION') == 'true') // no device with this key available
        {
            $category_id = Category::findCategoryIdByParentAndName('sensor', 'beep');
            $device_name = 'BEEPBASE-'.strtoupper(substr($key, -4, 4));
            $device      = Device::create(['name'=> $device_name, 'key'=>$key, 'hardware_id'=>$value, 'user_id'=>1, 'category_id'=>$category_id]);
        }

        if($device)
        {
            if ($field != null && $value != null)
            {
                switch($field)
                {
                    case 'hardware_id':
                        if (isset($device->hardware_id)) 
                            return;
                        else
                            $device->hardware_id = $value;
                        break;
                    default:
                        $device->{$field} = $value;
                        break;

                }
            }
            // store metadata from sensor
            $device->last_message_received = date('Y-m-d H:i:s');
            $device->save();
        }
    }

    private function parse_ttn_payload($request_data)
    {
        $data_array = $request_data['payload_fields'];


        if (isset($request_data['hardware_serial']) && !isset($data_array['key']))
            $data_array['key'] = $request_data['hardware_serial']; // LoRa WAN = Device EUI
        if (isset($request_data['metadata']['gateways'][0]['rssi']))
            $data_array['rssi'] = $request_data['metadata']['gateways'][0]['rssi'];
        if (isset($request_data['metadata']['gateways'][0]['snr']))
            $data_array['snr']  = $request_data['metadata']['gateways'][0]['snr'];

        if (isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($data_array['key']) && isset($data_array['hardware_id'])) // store hardware id
        {
            $this->storeDeviceMeta($data_array['key'], 'hardware_id', $data_array['hardware_id']);
            if (isset($data_array['measurement_transmission_ratio']))
                $this->storeDeviceMeta($data_array['key'], 'measurement_transmission_ratio', $data_array['measurement_transmission_ratio']);
            if (isset($data_array['measurement_interval_min']))
                $this->storeDeviceMeta($data_array['key'], 'measurement_interval_min', $data_array['measurement_interval_min']);
            if (isset($data_array['hardware_version']))
                $this->storeDeviceMeta($data_array['key'], 'hardware_version', $data_array['hardware_version']);
            if (isset($data_array['firmware_version']))
                $this->storeDeviceMeta($data_array['key'], 'firmware_version', $data_array['firmware_version']);
            if (isset($data_array['bootcount']))
                $this->storeDeviceMeta($data_array['key'], 'bootcount', $data_array['bootcount']);
        }

        return $data_array;
    }

    /**
    api/lora_sensors POST
    Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
    When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this->parse_ttn_payload() 
    When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

    @bodyParam key string required DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    @bodyParam payload_fields array TTN Measurement data
    @bodyParam DevEUI_uplink array KPN Measurement data
    */
    public function lora_sensors(Request $request)
    {
        $data_array   = [];
        $request_data = $request->input();
        $payload_type = '';

        // distinguish type of data
        if ($request->filled('LrnDevEui') && $request->filled('DevEUI_uplink.payload_hex')) // KPN/Simpoint
        {
            $data_array = $this->parse_kpn_payload($request_data);
            $payload_type = 'kpn';
        }
        else if ($request->filled('payload_fields') && $request->filled('hardware_serial')) // TTN HTTP POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $payload_type = 'ttn';
        }

        //die(print_r($data_array));
        $logFileName = isset($data_array['key']) ? 'lora_sensor_'.$data_array['key'].'.json' : 'lora_sensor_no_key.json';
        Storage::disk('local')->put('sensors/'.$logFileName, '[{"payload_type":"'.$payload_type.'"},{"request_input":'.json_encode($request_data).'},{"data_array":'.json_encode($data_array).'}]');

        return $this->storeMeasurements($data_array);
    }

   /**
    api/sensors POST
    Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. In case of using api/unsecure_sensors, this is used for legacy measurement devices that do not have the means to encrypt HTTPS cypher

    @bodyParam key string required DEV EUI of the sensor (Device in Domain model) to enable storing sensor data
    @bodyParam data array TTN Measurement data
    @bodyParam payload_fields array TTN Measurement data
    */
    public function storeMeasurementData(Request $request)
    {
        $request_data = $request->input();
        // Check for valid data 
        if ($request->filled('payload_fields')) // TTN HTTP POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
        }
        else if ($request->filled('data')) // Check for sensor string (colon and pipe devided) fw v1-3
        {
            $data_array = $this->convertSensorStringToArray($request_data['data']);
        }
        else // Assume post data input
        {
            $data_array = $request_data;
        }
        
        //die(print_r($data_array));
        return $this->storeMeasurements($data_array);
    }

    /**
    api/sensors/measurements GET
    Request all sensor measurements from a certain interval (hour, day, week, month, year) and index (0=until now, 1=previous interval, etc.)
    @authenticated
    @bodyParam key string DEV EUI to look up the sensor (Device)
    @bodyParam id integer ID to look up the sensor (Device)
    @bodyParam hive_id integer Hive ID to look up the sensor (Device)
    @bodyParam names string comma separated list of Measurement abbreviations to filter request data (weight_kg, t, h, etc.)
    @bodyParam interval string Data interval for interpolation of measurement values: hour (2min), day (10min), week (1 hour), month (3 hours), year (1 day). Default: day.
    @bodyParam index integer Interval index (>=0; 0=until now, 1=previous interval, etc.). Default: 0.
    */
    public function data(Request $request)
    {
        //Get the sensor
        $device  = $this->get_user_sensor($request);
        $location= $device->location();
        
        $client = new \Influx;
        $first  = $client::query('SELECT * FROM "sensors" WHERE "key" = \''.$device->key.'\' ORDER BY time ASC LIMIT 1')->getPoints(); // get first sensor date
        
        if (count($first) == 0)
            Response::json('sensor-none-error', 500);
        
        $all_names = array_keys($this->valid_sensors);
        $names     = $request->input('names', $all_names);

        if (count($names) == 0)
            Response::json('sensor-none-error', 500);

        $interval  = $request->input('interval','day');
        $index     = $request->input('index',0);
        $timeGroup = $request->input('timeGroup','day');
        $timeZone  = $request->input('timezone','Europe/Amsterdam');
        
        $durationInterval = $interval.'s';
        $requestInterval  = $interval;
        $resolution       = null;
        $staTimestamp = new Moment();
        $staTimestamp->setTimezone($timeZone);
        $endTimestamp = new Moment();
        $endTimestamp->setTimezone($timeZone);
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
        $groupBySelectWeather = null;
        $groupByResolution  = '';
        $limit              = 'LIMIT '.$this->maxDataPoints;
        $options            = ['precision'=> $this->precision];
        
        if($resolution != null)
        {
            $groupByResolution = 'GROUP BY time('.$resolution.') fill(null)';
            $queryList         = [];
            $queryListWeather  = [];
            for ($i = 0; $i < count($names); $i++) 
            {
                $name = $names[$i];
                if (in_array($name, $this->output_sensors))
                {
                    $query = 'SELECT COUNT("'.$name.'") AS "count" FROM "sensors" WHERE "key" = \''.$device->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$limit;
                    $result  = $client::query($query, $options);
                    $sensors = $result->getPoints();
                    if (count($sensors) > 0 && $sensors[0]['count'] > 0)
                        $queryList[] = 'MEAN("'.$name.'") AS "'.$name.'"';

                    if ($location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
                    {
                        $query = 'SELECT COUNT("'.$name.'") AS "count" FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$limit;
                        $result  = $client::query($query, $options);
                        $sensors = $result->getPoints();
                        if (count($sensors) > 0 && $sensors[0]['count'] > 0)
                            $queryListWeather[] = 'MEAN("'.$name.'") AS "'.$name.'"';
                    }
                }
            }
            $groupBySelect = implode(', ', $queryList);
            $groupBySelectWeather = implode(', ', $queryListWeather);
        }
        
        // try
        // {
        $sensors_out = [];
        $weather_out = [];
        $old_values  = false;
        
        if ($groupBySelect != null) 
        {
            $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE "key" = \''.$device->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;
            $result      = $client::query($sensorQuery, $options);
            $sensors_out = $result->getPoints();

            //die(print_r($location->toArray()));
            if ($groupBySelectWeather && $location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
            {
                $weatherQuery = 'SELECT '.$groupBySelectWeather.' FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;
                $result       = $client::query($weatherQuery, $options);
                $weather_out  = $result->getPoints();

                if (count($weather_out) == count($sensors_out))
                {
                    foreach ($sensors_out as $key => $value) 
                    {
                        foreach ($weather_out[$key] as $weather_name => $weather_value) 
                        {
                            if ($weather_name != 'time')
                                $sensors_out[$key][$weather_name] =  $weather_value;
                        }
                    }
                }
            }
        }
        // else
        // {
        //     // check if values are stored in the new (column), or the old (name) way.
        //     $old_vals = $client::query('SELECT COUNT("value") FROM "sensors" WHERE "key" = \''.$device->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' LIMIT 1')->getPoints();
        //     if (count($old_vals) > 0)
        //     {
        //         $old_values = true;
        //         for ($i = 0; $i < count($names); $i++) 
        //         {
        //             $name = $names[$i];
        //             if (in_array($name, $this->output_sensors))
        //             {
        //                 $sensor_vals = $client::query('SELECT MEAN("value") AS "'.$name.'" FROM "sensors" WHERE "name" = \''.$name.'\' AND "key" = \''.$device->key.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit)->getPoints();
        //                 if (count($sensor_vals) > 0)
        //                 {
        //                     if (count($sensors_out) == 0)
        //                     {
        //                         $sensors_out = $sensor_vals;
        //                     }
        //                     else
        //                     {
        //                         foreach ($sensors_out as $ind => $value) 
        //                         {
        //                             if ($value['time'] == $sensor_vals[$ind]['time'])
        //                                 $sensors_out[$ind][$name] = $sensor_vals[$ind][$name];
        //                         }   
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
        return Response::json( ['id'=>$device->id, 'interval'=>$interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'old_values'=>$old_values] );
        // }
        // catch(\Exception $e)
        // {
        // }
    }
}