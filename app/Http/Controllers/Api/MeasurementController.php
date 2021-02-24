<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use App\Measurement;
use App\Models\FlashLog;
use App\Models\Webhook;
// use App\Transformer\SensorTransformer;
use Validator;
use InfluxDB;
use Response;
use Moment\Moment;
use League\Fractal;
use App\Http\Requests\PostSensorRequest;
use App\Http\Controllers\Api\MeasurementLegacyCalculationsTrait;
use App\Http\Controllers\Api\MeasurementLoRaDecoderTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * @group Api\MeasurementController
 * Store and retreive sensor data (both LoRa and API POSTs) from a Device
 */
class MeasurementController extends Controller
{
    use MeasurementLegacyCalculationsTrait, MeasurementLoRaDecoderTrait;

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
        $this->client         = new \Influx;
        //die(print_r($this->valid_sensors));
    }
   
    private function doPostHttpRequest($url, $data)
    {
        $guzzle   = new Client();
        try
        {
            $response = $guzzle->post($url, [\GuzzleHttp\RequestOptions::JSON => $data]);
        }
        catch(ClientException $e)
        {
            return $e;
        }

        return $response;
    }

    // Sensor measurement functions

    protected function get_user_device(Request $request, $mine = false)
    {
        $this->validate($request, [
            'id'        => 'nullable|integer|exists:sensors,id',
            'key'       => 'nullable|integer|exists:sensors,key',
            'hive_id'   => 'nullable|integer|exists:hives,id',
        ]);
        
        $devices = $request->user()->allDevices($mine); // inlude user Group hive sensors ($mine == false)

        if ($devices->count() > 0)
        {
            if ($request->filled('id') && $request->input('id') != 'null')
            {
                $id = $request->input('id');
                $check_device = $devices->findOrFail($id);
            }
            else if ($request->filled('device_id') && $request->input('device_id') != 'null')
            {
                $id = $request->input('device_id');
                $check_device = $devices->findOrFail($id);
            }
            else if ($request->filled('key') && $request->input('key') != 'null')
            {
                $key = $request->input('key');
                $check_device = $devices->where('key', $key)->first();
            }
            else if ($request->filled('hive_id') && $request->input('hive_id') != 'null')
            {
                $hive_id = $request->input('hive_id');
                $check_device = $devices->where('hive_id', $hive_id)->first();
            }
            else
            {
                $check_device = $devices->first();
            }
            
            if(isset($check_device))
                return $check_device;
        }
        return Response::json('no_device_found', 404);
    }

    
    private function last_sensor_values_array($device, $fields='*', $limit=1)
    {
        $fields = $fields != '*' ? '"'.$fields.'"' : '*';
        $groupby= $fields == '*' || strpos(',' ,$fields) ? 'GROUP BY "name,time"' : '';
        $output = null;
        try
        {
            $client = $this->client;
            $query  = 'SELECT '.$fields.' from "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time > now() - 365d '.$groupby.' ORDER BY time DESC LIMIT '.$limit;
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

    private function last_sensor_measurement_time_value($device, $name)
    {
        $arr = $this->last_sensor_values_array($device, $name);

        if ($arr && count($arr) > 0 && in_array($name, array_keys($arr)))
            return $arr[$name];

        return null;
    }

    private function last_sensor_increment_values($device, $data_array=null)
    {
        $output = [];
        $limit  = 2;

        if ($data_array != null)
        {
            $output[0] = $data_array;
            $output[1] = $this->last_sensor_values_array($device, implode('","',array_keys($data_array)), 1);
        }
        else
        {
            $output = $this->last_sensor_values_array($device, implode('","',$this->output_sensors), $limit);
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
        $client    = $this->client;
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
            $def = $device->sensorDefinitions->where('input_measurement_id', $measurement_in->id)->where('output_measurement_id', $measurement_out->id)->last();
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
            $battery_voltage = isset($data_array['bv']) ? floatval($data_array['bv']) : null;
            $this->storeDeviceMeta($dev_eui, 'battery_voltage', $battery_voltage);
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
                $sensor_def = $device->sensorDefinitions->where('input_measurement_id', $measurement->id)->last();

                if ($sensor_def)
                {
                    $abbr_o = $sensor_def->output_abbr;
                    $data_array[$abbr_o] = $sensor_def->calibrated_measurement_value($val);
                }
                else if ($abbr == 'w_v') // make new calibration values based on stored ones
                {
                    $influx_offset = floatval($this->last_sensor_measurement_time_value($device, $abbr.'_offset'));
                    $influx_multi  = floatval($this->last_sensor_measurement_time_value($device, $abbr.'_kg_per_val'));

                    if ($influx_offset != 0 || $influx_multi != 0)
                        $this->createOrUpdateDefinition($device, 'w_v', 'weight_kg', $influx_offset, $influx_multi);
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

            if (isset($data_array['weight_kg']) == false)
            {
                // take into account offset and multi
                $weight_kg = $this->calculateWeightKg($device, $data_array);
                $data_array['weight_kg'] = $weight_kg;

                // check if we need to compensate weight for temp (legacy)
                //$data_array = $this->add_weight_kg_corrected_with_temperature($device, $data_array);
            }
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
            //die(print_r($data_array));
            Storage::disk('local')->put('sensors/sensor_write_error.log', json_encode($data_array));
            return Response::json('sensor-write-error', 500);
        }
    }

    private function getAvailableSensorNamesFromData($names, $table, $where, $limit='', $output_sensors_only=true, $output_group_by=false)
    {
        $out           = [];
        $valid_sensors = $output_sensors_only ? $this->output_sensors : array_keys($this->valid_sensors);
        $options       = ['precision'=> $this->precision];
        
        //die(print_r([$names, $valid_sensors]));

        for ($i = 0; $i < count($names); $i++) 
        {
            $name = $names[$i];
            if (in_array($name, $valid_sensors))
            {
                $sensors = [];
                $query = 'SELECT COUNT("'.$name.'") AS "count" FROM "'.$table.'" WHERE '.$where.' '.$limit;
                try{
                    $result  = $this->client::query($query, $options);
                    $sensors = $result->getPoints();
                } catch (InfluxDB\Exception $e) {
                    // return Response::json('influx-group-by-query-error', 500);
                }
                if (count($sensors) > 0 && $sensors[0]['count'] > 0)
                    $out[] = $name;
            }
        }

        if ($output_group_by)
        {
            foreach ($out as $i => $name) 
                $out[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';
                
            $out = implode(', ', $queryList);
        }

        return $out;
    }


    public function sensor_measurement_types_available(Request $request)
    {
        $device_id           = $request->input('device_id');
        $device              = $this->get_user_device($request);

        if ($device)
        {
            $start       = $request->input('start');
            $end         = $request->input('end');
            
            $tz          = $request->input('timezone', 'Europe/Amsterdam');
            $startMoment = new Moment($start, 'UTC');
            $startString = $startMoment->setTimezone($tz)->format($this->timeFormat); 
            $endMoment   = new Moment($end, 'UTC');
            $endString   = $endMoment->setTimezone($tz)->format($this->timeFormat);
            
            $sensors             = $request->input('sensors', $this->output_sensors);
            $where               = '("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$startString.'\' AND time <= \''.$endString.'\'';

            $sensor_measurements = $this->getAvailableSensorNamesFromData($sensors, 'sensors', $where, '', false);
            //die(print_r([$device->name, $device->key]));
            if ($sensor_measurements)
            {
                $measurement_types   = Measurement::all()->sortBy('pq')->whereIn('abbreviation', $sensor_measurements)->pluck('abbr_named_object','abbreviation')->toArray();
                return Response::json($measurement_types, 200);
            }
            else
            {
                return Response::json('influx-query-empty', 500);
            }
        }
        return Response::json('invalid-user-device', 500);
    }

    /**
    api/sensors/lastvalues GET
    Request last measurement values of all sensor measurements from a Device
    @authenticated
    @bodyParam key string DEV EUI to look up the Device
    @bodyParam id integer ID to look up the Device
    @bodyParam hive_id integer Hive ID to look up the Device
    */
    public function lastvalues(Request $request)
    {
        $sensor = $this->get_user_device($request);
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
            $data_array = array_merge($data_array, $this->decode_simpoint_payload($request_data['DevEUI_uplink']));

        //die(print_r($data_array));
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

        if ($device == null && $field == 'hardware_id' && $value !== null && env('ALLOW_DEVICE_CREATION') == 'true' && Auth::user() && Auth::user()->hasRole('sensor-data')) // no device with this key available, so create new device by hardware id
        {
            $device = Device::where('hardware_id', $value)->first();

            if (!isset($device) && strlen($value) == 18) // TODO: remove if TTN and app fix and DB change have been implemented
                $device = Device::where('hardware_id', '0e'.$value)->first();
            
            if ($device)
            {
                $device->key = $key; // update device key of hardware id to prevent double hardware id's
            }
            else
            {
                $category_id = Category::findCategoryIdByParentAndName('sensor', 'beep');
                $device_name = 'BEEPBASE-'.strtoupper(substr($key, -4, 4));
                $device      = Device::create(['name'=> $device_name, 'key'=>$key, 'hardware_id'=>$value, 'user_id'=>1, 'category_id'=>$category_id]);
            }
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

    private function sendDeviceDownlink($key, $url)
    {
        $device = Device::where('key', $key)->first();

        if($device && isset($url) && isset($device->next_downlink_message)) // && Auth::user()->hasRole('sensor-data')
        {
            $msg = $device->next_downlink_message;
            $downlink_array = [
                'dev_id' => $key,
                'port' => 5,
                'confirmed' => true,
                'payload_raw' => base64_encode($msg),
            ];
            $result = $this->doPostHttpRequest($url, $downlink_array);

            // store waiting message for sensor
            if ($result instanceof ClientException)
            {
                $device->last_downlink_result  = 'Error (no result): last downlink ('.$msg.') tried to sent @ '.date('Y-m-d H:i:s').'. Error message: '.substr($result->getMessage(), 0, 150);
                $device->save();
            }
            else if ($result)
            {
                if ($result->getStatusCode() == 200)
                {
                    $device->next_downlink_message = null;
                    $device->last_downlink_result  = 'Last downlink ('.$msg.') sent @ '.date('Y-m-d H:i:s').', waiting for result...';
                    $device->save();
                }
                else
                {
                    $device->last_downlink_result  = 'Error (status '.$result->getStatusCode().'): last downlink ('.$msg.') tried to @ '.date('Y-m-d H:i:s');
                    $device->save();
                }
            }
        }
    }

    private function parse_ttn_payload($request_data)
    {
        $data_array = [];

        // parse payload
        if (isset($request_data['payload_fields']))
            $data_array = $request_data['payload_fields'];
        else if (isset($request_data['payload_raw']))
            $data_array = $this->decode_ttn_payload($request_data);

        if (isset($request_data['hardware_serial']) && !isset($data_array['key']))
            $data_array['key'] = $request_data['hardware_serial']; // LoRa WAN = Device EUI
        if (isset($request_data['metadata']['gateways'][0]['rssi']))
            $data_array['rssi'] = $request_data['metadata']['gateways'][0]['rssi'];
        if (isset($request_data['metadata']['gateways'][0]['snr']))
            $data_array['snr']  = $request_data['metadata']['gateways'][0]['snr'];
        if (isset($request_data['metadata']['gateways'][0]['channel']))
            $data_array['lora_channel']  = $request_data['metadata']['gateways'][0]['channel'];
        if (isset($request_data['metadata']['data_rate']))
            $data_array['data_rate']  = $request_data['metadata']['data_rate'];


        // store device metadata
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

        // process downlink
        if (isset($data_array['key']) && isset($request_data['downlink_url']))
            $this->sendDeviceDownlink($data_array['key'], $request_data['downlink_url']);

        if (isset($data_array['key']) && isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($request_data['port']) && $request_data['port'] == 6) // downlink response
        {
            $device = Device::where('key', $data_array['key'])->first();
            if($device) // && Auth::user()->hasRole('sensor-data')
            {
                $device->last_downlink_result = json_encode($data_array);
                $device->save();
            }
        }

        return $data_array;
    }

    /**
    api/lora_sensors POST
    Store sensor measurement data (see BEEP sensor data API definition) from TTN or KPN (Simpoint)
    When Simpoint payload is supplied, the LoRa HEX to key/value pairs decoding is done within function $this->parse_ttn_payload() 
    When TTN payload is supplied, the TTN HTTP integration decoder/converter is assumed to have already converted the payload from LoRa HEX to key/value conversion

    @bodyParam key string required DEV EUI of the Device to enable storing sensor data
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
        else if (($request->filled('payload_fields') || $request->filled('payload_raw')) && $request->filled('hardware_serial')) // TTN HTTP POST
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

    @bodyParam key string required DEV EUI of the Device to enable storing sensor data
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
    api/sensors/flashlog
    POST data from BEEP base fw 1.5.0+ FLASH log (with timestamp), interpret data and store in InlfuxDB (overwriting existing data). BEEP base BLE cmd: when the response is 200 OK and erase_mx_flash > -1, provide the ERASE_MX_FLASH BLE command (0x21) to the BEEP base with the last byte being the HEX value of the erase_mx_flash value (0 = 0x00, 1 = 0x01, i.e.0x2100, or 0x2101)
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam data string MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
    @bodyParam file binary File with MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
    @queryParam show integer 1 for displaying info in result JSON, 0 for not displaying (default).
    @queryParam save integer 1 for saving the data to a file (default), 0 for not save log file.
    @response{
          "lines_received": 20039,
          "bytes_received": 9872346,
          "log_saved": true,
          "log_parsed": false,
          "log_messages":29387823
          "erase_mx_flash": -1,
          "erase":false,
          "erase_type":"fatfs"
        }
    */
    public function flashlog(Request $request)
    {
        $user= $request->user();
        $inp = $request->all();
        $sid = isset($inp['id']) ? $inp['id'] : null;
        $key = null;
        
        if (isset($inp['key']) && !isset($inp['hardware_id']) && !isset($inp['id']) )
        {
            $key = strtolower($inp['key']);
            $dev = $user->devices()->whereRaw('lower(`key`) = \''.$key.'\'')->first(); // check for case insensitive device key before validation
            if ($dev)
            {
                $inp['id'] = $dev->id;
                $sid       = $inp['id'];
            }
        }
        
        $hwi = null;
        if (isset($inp['hardware_id']))
        {
            $hwi = strtolower($inp['hardware_id']);
            $inp['hardware_id'] = $hwi;
        }

        $validator = Validator::make($inp, [
            'id'                => ['required_without_all:key,hardware_id','integer','exists:sensors,id'],
            'hardware_id'       => ['required_without_all:key,id','string','exists:sensors,hardware_id'],
            'key'               => ['required_without_all:id,hardware_id','string','min:4','exists:sensors,key'],
            'data'              => 'required_without:file',
            'file'              => 'required_without:data|file',
            'show'              => 'nullable|boolean',
            'save'              => 'nullable|boolean'
        ]);

        $result   = null;
        $parsed   = false;
        $saved    = false;
        $files    = false;
        $messages = 0;

        if ($validator->fails())
        {
            return Response::json(['errors'=>$validator->errors()], 400);
        }
        else
        {
            $device = null;

            if (isset($sid))
                $device = $user->devices()->where('id', $sid)->first();
            else if (isset($key) && !isset($sid) && isset($hwi))
                $device = $user->devices()->where('hardware_id', $hwi)->where('key', $key)->first();
            else if (isset($hwi))
                $device = $user->devices()->where('hardware_id', $hwi)->first();
            else if (isset($key) && !isset($sid) && !isset($hwi))
                $device = $user->devices()->where('key', $key)->first();
            
            if ($device == null)
                return Response::json(['errors'=>'device_not_found'], 400);

            $out   = [];
            $disk  = env('FLASHLOG_STORAGE', 'public');
            $f_dir = 'flashlog';
            $data  = '';
            $lines = 0; 
            $bytes = 0; 
            $logtm = false;
            $erase = -1;
            $show  = $request->filled('show') ? $inp['show'] : false;
            $save  = $request->filled('save') ? $inp['save'] : true;
            $f_log = null;
            $f_str = null;
            $f_par = null;
            
            if ($device && ($request->filled('data') || $request->hasFile('file')))
            {
                $sid  = $device->id; 
                $time = date("Ymdhis");
                
                if ($request->hasFile('file') && $request->file('file')->isValid())
                {
                    $files= true;
                    $file = $request->file('file');
                    $name = "sensor_".$sid."_flash_$time.log";
                    $f_log= Storage::disk($disk)->putFileAs($f_dir, $file, $name); 
                    $saved= $f_log ? true : false; 
                    $data = Storage::disk($disk)->get($f_dir.'/'.$name);
                    if ($save == false) // check if file needs to be saved
                    {
                        $saved = Storage::disk($disk)->delete($f_dir.'/'.$name) ? false : true;
                        $f_log = null;
                    }
                }
                else
                {
                    $data = $request->input('data');
                    if ($save)
                    {
                        $logFileName = $f_dir."/sensor_".$sid."_flash_$time.log";
                        $saved = Storage::disk($disk)->put($logFileName, $data);
                        $f_log = Storage::disk($disk)->url($logFileName); 
                    }
                }

                $data= preg_replace('/[\r\n|\r|\n]+/', ',', $data);
                $data= preg_replace('/[^A-Fa-f0-9,]/', '', $data);

                // interpret every line as a standard LoRa message (with time (13 characters) cut off at the end)
                $in      = explode(",", $data);
                $lines   = count($in);
                $alldata = "";

                foreach ($in as $line)
                    $alldata .= substr($line,4);

                // Split data by 0A02 and 0A03
                $data  = preg_replace('/0A02/', "0A\n02", $alldata);
                $data  = preg_replace('/0A03/', "0A\n03", $data);

                if ($save)
                {
                    $logFileName =  $f_dir."/sensor_".$sid."_flash_stripped_$time.log";
                    $saved = Storage::disk($disk)->put($logFileName, $data);
                    $f_str = Storage::disk($disk)->url($logFileName); 
                }

                $in = explode("\n", $data);
                foreach ($in as $line)
                {
                    $data_array = $this->decode_flashlog_payload($line, $show);
                    if (in_array('time', array_keys($data_array)))
                    {
                        $logtm = true;
                        $unix  = $data_array['time'];
                        unset($data_array['time']);
                        $out[$unix] = $data_array; 
                    }
                    else
                    {
                        $out[] = $data_array;
                    }
                }

                $messages = count($out);
                if ($messages > 0)
                {
                    $parsed = true;
                    if ($save)
                    {
                        $logFileName = $f_dir."/sensor_".$sid."_flash_parsed_$time.json";
                        $saved = Storage::disk($disk)->put($logFileName, json_encode($out));
                        $f_par = Storage::disk($disk)->url($logFileName); 
                    }
                }
            }
            $result = [
                'lines_received'=>$lines,
                'bytes_received'=>$bytes,
                'log_has_timestamps'=>$logtm,
                'log_saved'=>$saved,
                'log_parsed'=>$parsed,
                'log_messages'=>$messages,
                'erase_mx_flash'=>$saved ? 0 : -1,
                'erase'=>$saved,
                'erase_type'=>$saved ? 'fatfs' : null // fatfs, or full
            ];

            // create Flashlog entity
            $flashlog = [
                'user_id'=>$user->id,
                'device_id'=>$device->id,
                'hive_id'=>$device->hive_id,
                'bytes_received'=>$bytes,
                'log_has_timestamps'=>$logtm,
                'log_saved'=>$saved,
                'log_parsed'=>$parsed,
                'log_messages'=>$messages,
                'log_file'=>$f_log,
                'log_file_stripped'=>$f_str,
                'log_file_parsed'=>$f_par
            ];
            FlashLog::create($flashlog);
            Webhook::sendNotification("Flashlog from ".$user->name." device: ".$device->name." parsed:".$parsed." messages:".$messages." saved:".$saved." to disk:".$disk.'/'.$f_dir);

            if ($show)
            {
                $result['fields'] = array_keys($inp);
                $result['output'] = $out;
            }
        }

        if ($parsed)
        return Response::json($result, $parsed ? 200 : 500);
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
        $device  = $this->get_user_device($request);
        $location= $device->location();
        
        $client = $this->client;
        $first  = $client::query('SELECT * FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') ORDER BY time ASC LIMIT 1')->getPoints(); // get first sensor date
        $first_w= [];
        if ($location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
            $first_w = $client::query('SELECT * FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' ORDER BY time ASC LIMIT 1')->getPoints(); // get first weather date
        
        if (count($first) == 0 && count($first_w) == 0)
            return Response::json('sensor-none-error', 500);
        
        $names = array_keys($this->valid_sensors);

        if ($request->filled('names'))
            $names = explode(",", $request->input('names'));

        if (count($names) == 0)
            return Response::json('sensor-none-defined', 500);

        // add sensorDefinition names
        $sensorDefinitions = [];
        foreach ($names as $name)
        {
            $measurement_id   = Measurement::getIdByAbbreviation($name);
            $sensordefinition = $device->sensorDefinitions->where('output_measurement_id', $measurement_id)->sortByDesc('updated_at')->first();
            if ($sensordefinition)
                $sensorDefinitions["$name"] = ['name'=>$sensordefinition->name, 'inside'=>$sensordefinition->inside];
        }

        // select appropriate interval
        $deviceMaxResolutionMinutes = 1;
        if (isset($device->measurement_interval_min))
            $deviceMaxResolutionMinutes = $device->measurement_interval_min * max(1,$device->measurement_transmission_ratio);

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
                    $resolution = $deviceMaxResolutionMinutes > 180 ? $deviceMaxResolutionMinutes.'m' : '3h';
                    $staTimestamp->subtractMonths($index);
                    $endTimestamp->subtractMonths($index);
                    break;
                case 'week':
                    $requestInterval = 'week';
                    $resolution = $deviceMaxResolutionMinutes > 60 ? $deviceMaxResolutionMinutes.'m' : '1h';
                    $staTimestamp->subtractWeeks($index);
                    $endTimestamp->subtractWeeks($index);
                    break;
                case 'day':
                    $resolution = $deviceMaxResolutionMinutes > 10 ? $deviceMaxResolutionMinutes.'m' : '10m';
                    $staTimestamp->subtractDays($index);
                    $endTimestamp->subtractDays($index);
                    break;
                case 'hour':
                    $resolution = $deviceMaxResolutionMinutes > 2 ? $deviceMaxResolutionMinutes.'m' : '2m';
                    $staTimestamp->subtractHours($index);
                    $endTimestamp->subtractHours($index);
                    break;
            }
        //}
        $staTimestampString = $staTimestamp->startOf($requestInterval)->setTimezone('UTC')->format($this->timeFormat);
        $endTimestampString = $endTimestamp->endOf($requestInterval)->setTimezone('UTC')->format($this->timeFormat);    
        $groupBySelect        = null;
        $groupBySelectWeather = null;
        $groupByResolution  = '';
        $limit              = 'LIMIT '.$this->maxDataPoints;
        $options            = ['precision'=> $this->precision];
        
        if($resolution != null)
        {
            if ($device)
            {
                $groupByResolution = 'GROUP BY time('.$resolution.') fill(null)';
                $queryList         = $this->getAvailableSensorNamesFromData($names, 'sensors', '("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\'', $limit, true);

                foreach ($queryList as $i => $name) 
                    $queryList[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';
                
                $groupBySelect = implode(', ', $queryList);
            }
            // Add weather
            if ($location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
            {
                $queryListWeather     = $this->getAvailableSensorNamesFromData($names, 'weather', '"lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\'', $limit, true);
                
                foreach ($queryListWeather as $i => $name) 
                    $queryListWeather[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';

                $groupBySelectWeather = implode(', ', $queryListWeather);
            }
        }
        
        $sensors_out = [];
        $weather_out = [];
        $old_values  = false;
        
        if ($groupBySelect != null) 
        {
            $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;
            $result      = $client::query($sensorQuery, $options);
            $sensors_out = $result->getPoints();
        }

        // Add weather data
        if ($groupBySelectWeather != null && $location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
        {
            $weatherQuery = 'SELECT '.$groupBySelectWeather.' FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;
            $result       = $client::query($weatherQuery, $options);
            $weather_out  = $result->getPoints();

            if ($groupBySelect == null)
            {
                $sensors_out = $weather_out;
            }
            else if (count($weather_out) == count($sensors_out))
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

        return Response::json( ['id'=>$device->id, 'interval'=>$interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'old_values'=>$old_values, 'sensorDefinitions'=>$sensorDefinitions] );
    }
}