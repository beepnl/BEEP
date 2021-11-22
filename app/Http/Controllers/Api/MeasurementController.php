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
use App\Models\AlertRule;
// use App\Transformer\SensorTransformer;
use Validator;
use InfluxDB;
use Response;
use Moment\Moment;
use League\Fractal;
use App\Http\Requests\PostSensorRequest;
use App\Traits\MeasurementLegacyCalculationsTrait;
use App\Traits\MeasurementLoRaDecoderTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use Illuminate\Support\Facades\Cache;

/**
 * @group Api\MeasurementController
 * Store and retreive sensor data (both LoRa and API POSTs) from a Device
 */
class MeasurementController extends Controller
{
    use MeasurementLegacyCalculationsTrait, MeasurementLoRaDecoderTrait;

    protected $respose;
    protected $valid_sensors  = [];
    protected $valid_weather  = [];
    protected $output_sensors = [];
    protected $output_weather = [];
    protected $precision      = 's';
    protected $timeFormat     = 'Y-m-d H:i:s';
    protected $maxDataPoints  = 5000;
 
    public function __construct()
    {
        // make sure to add to the measurements DB table w_v_kg_per_val, w_fl_kg_per_val, etc. and w_v_offset, w_fl_offset to let the calibration functions function correctly
        $this->valid_sensors  = Measurement::getValidMeasurements();
        $this->valid_weather  = Measurement::getValidMeasurements(false, true);
        $this->output_sensors = Measurement::getValidMeasurements(true);
        $this->output_weather = Measurement::getValidMeasurements(true, true);
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

    
    

    // requires at least ['name'=>value] to be set
    private function storeInfluxData($data_array, $device, $unix_timestamp)
    {
        // store posted data
        $client      = $this->client;
        $points      = [];
        $unix_time   = isset($unix_timestamp) ? $unix_timestamp : time();
        $sensor_tags = ['key' => strtolower($device->key), 'device_name' => $device->name, 'hardware_id' => strtolower($device->hardware_id), 'user_id' => $device->user_id]; 

        $valid_sensor_keys = array_keys($this->valid_sensors);
        
        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, $valid_sensor_keys) )
            {
                array_push($points, 
                    new InfluxDB\Point(
                        'sensors',                  // name of the measurement
                        null,                       // the measurement value
                        $sensor_tags,               // optional tags
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
                $this->cacheRequestRate('influx-write');
                $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                // gracefully do nothing
            }
        }
        return $stored;
    }

    private function cacheRequestRate($name, $amount=1)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count', $amount);
        else
            Cache::put($name.'-count', $amount);

    }

    private function cacheRequestArray($name, $val=null)
    {
        if (isset($val))
        {
            $val   = date('H:i:s').' - '.$val;
            $array = [];

            if (Cache::has($name.'-array'))
            {
                $array = Cache::get($name.'-array');
                if (gettype($array) == 'array')
                {
                    array_unshift($array, $val); // put at first value
                    if (count($array) > 50)
                        array_pop($array); // remove last value

                }
                Cache::forget($name.'-array');
            }
            else
            {
                $array[] = $val;
            }
            Cache::put($name.'-array', $array, 3600);  
        }
    }

    private function storeMeasurements($data_array)
    {
        if (!in_array('key', array_keys($data_array)) || $data_array['key'] == '' || $data_array['key'] == null)
        {
            Storage::disk('local')->put('sensors/sensor_no_key.log', json_encode($data_array));
            $this->cacheRequestRate('store-measurements-400');
            $this->cacheRequestArray('store-measurements-400', json_encode($data_array));
            return Response::json('No key provided', 400);
        }

        // Check if key is valid
        $dev_eui = $data_array['key']; // save sensor data under sensor key
        $device  = Device::where('key', $dev_eui)->first();

        if($device)
        {
            // store device metadata
            if (isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($data_array['hardware_id'])) // store hardware id
            {
                $device = $this->storeDeviceMeta($device, 'hardware_id', $data_array['hardware_id']);
                if (isset($data_array['measurement_transmission_ratio']))
                    $device = $this->storeDeviceMeta($device, 'measurement_transmission_ratio', $data_array['measurement_transmission_ratio']);
                if (isset($data_array['measurement_interval_min']))
                    $device = $this->storeDeviceMeta($device, 'measurement_interval_min', $data_array['measurement_interval_min']);
                if (isset($data_array['hardware_version']))
                    $device = $this->storeDeviceMeta($device, 'hardware_version', $data_array['hardware_version']);
                if (isset($data_array['firmware_version']))
                    $device = $this->storeDeviceMeta($device, 'firmware_version', $data_array['firmware_version']);
                if (isset($data_array['bootcount']))
                    $device = $this->storeDeviceMeta($device, 'bootcount', $data_array['bootcount']);
                if (isset($data_array['time_device']))
                    $device = $this->storeDeviceMeta($device, 'time_device', $data_array['time_device']);
            }
            // store metadata from sensor
            $device->last_message_received = date('Y-m-d H:i:s');
            $device->save();
        }
        else
        {
            if (isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($data_array['hardware_id'])) // store hardware id
                $device = $this->storeDeviceMeta($device, 'hardware_id', $data_array['hardware_id']); // create device if ALLOW_DEVICE_CREATION == 'true'

            if ($device) // new device created from hw id?
            {
                $device->last_message_received = date('Y-m-d H:i:s');
                $device->save();
            } 
            else
            {
                Storage::disk('local')->put('sensors/sensor_invalid_key.log', json_encode($data_array));
                $this->cacheRequestRate('store-measurements-401');
                $this->cacheRequestArray('store-measurements-401', $dev_eui);
                return Response::json('No valid key provided', 401);
            }
        }

        // Save data
        unset($data_array['key']);

        $time = time();
        if (isset($data_array['time']))
            $time = intVal($data_array['time']);


        // Add senaor data based on available device sensorDefinitions
        $date            = date($this->timeFormat, $time); 
        $sensor_defs     = $device->activeSensorDefinitions();
        $sensor_defs_all = $device->sensorDefinitions;
        foreach ($sensor_defs as $sd) 
        {
            if (isset($sd->output_abbr) && isset($data_array[$sd->input_abbr]))
                $data_array = $device->addSensorDefinitionMeasurements($data_array, $data_array[$sd->input_abbr], $sd->input_measurement_id, $date, $sensor_defs_all);
        }
        // store battery voltage after applying sensor defs
        if (isset($data_array['bv']))
        {
            $battery_voltage = floatval($data_array['bv']);
            if ($battery_voltage > 100)
            {
                $battery_voltage = $battery_voltage / 1000;
                $data_array['bv'] = $battery_voltage;
            }
            $device = $this->storeDeviceMeta($device, 'battery_voltage', $battery_voltage);
        } 
        
        // Legacy weight calculation from 2-4 load cells
        if (!isset($data_array['weight_kg']) && (isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br'])) ) 
        {
            // check if calibration is required
            $calibrate = $device->last_sensor_measurement_time_value('calibrating_weight');
            if (floatval($calibrate) > 0)
                $this->calibrate_weight_sensors($device, $calibrate, false, $data_array);

            // take into account offset and multi
            $weight_kg = $this->calculateWeightKg($device, $data_array);
            if (!isset($data_array['w_v']) || $data_array['w_v'] != $weight_kg) // do not save too big value
                $data_array['weight_kg'] = $weight_kg;

            // check if we need to compensate weight for temp (legacy)
            //$data_array = $this->add_weight_kg_corrected_with_temperature($device, $data_array);
        }
        $stored = $this->storeInfluxData($data_array, $device, $time);
        
        
        // Remember the last date/data that this device stored measurements from (and previous to calculate diff)
        $cached_time   = Cache::get('set-measurements-device-'.$device->id.'-time');
        $cached_data   = Cache::get('set-measurements-device-'.$device->id.'-data');
        $has_prev_data = false;
        if ($cached_time && $cached_data)
        {
            Cache::put('set-measurements-device-'.$device->id.'-time-prev', $cached_time);
            Cache::put('set-measurements-device-'.$device->id.'-data-prev', $cached_data);
            $has_prev_data = true;
        }
        Cache::put('set-measurements-device-'.$device->id.'-time', $time);
        Cache::put('set-measurements-device-'.$device->id.'-data', $data_array);
        
        // Parse Alert rules if available
        $alert_count     = 0;
        $device_rule_ids = $device->hiveUserRuleIds();
        if (count($device_rule_ids) > 0)
        {
            $last_values_array = [$data_array];
            if ($has_prev_data)
                $last_values_array[] = $cached_data;

            $alert_count = AlertRule::parseUserDeviceDirectAlertRules($device_rule_ids, $device->id, $last_values_array);
            $this->cacheRequestRate('alert-direct', $alert_count);
        }

        if($stored) 
        {
            $this->cacheRequestRate('store-measurements-201');
            $alert_comment = $alert_count > 0 ? '-'.$alert_count.'-alerts' : '';
            return Response::json('saved'.$alert_comment, 201);
        } 
        else
        {
            //die(print_r($data_array));
            Storage::disk('local')->put('sensors/sensor_write_error.log', json_encode($data_array));
            $this->cacheRequestRate('store-measurements-500');
            $this->cacheRequestArray('store-measurements-500', json_encode($data_array));
            return Response::json('sensor-write-error', 500);
        }
    }

    /**
    api/sensors/measurement_types GET
    Request all currently available sensor measurement types that can be POSTed to
    @queryParam locale string Two digit locale to get translated sensor measurement types. Example: en
    */
    public function sensor_measurement_types(Request $request)
    {
        $locale = null;
        if ($request->filled('locale'))
            $locale = $request->input('locale');

        return Response::json(Measurement::getValidMeasurements(false, false, $locale));
    }


    public function sensor_measurement_types_available(Request $request)
    {
        $device_id           = $request->input('device_id');
        $device              = $this->get_user_device($request);

        if ($device)
        {
            $start       = $request->input('start');
            $end         = $request->input('end');
            
            $tz          = $request->input('timezone', 'UTC');
            $startMoment = new Moment($start, 'UTC');
            $startString = $startMoment->setTimezone($tz)->format($this->timeFormat); 
            $endMoment   = new Moment($end, 'UTC');
            $endString   = $endMoment->setTimezone($tz)->format($this->timeFormat);
            
            $sensors     = $request->input('sensors', $this->output_sensors);
            $where       = $device->influxWhereKeys().' AND time >= \''.$startString.'\' AND time <= \''.$endString.'\'';

            $sensor_measurements = Device::getAvailableSensorNamesNoCache($sensors, $where, 'sensors', false);
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
        $this->cacheRequestRate('get-measurements-last');

        $device = $this->get_user_device($request);
        if ($device)
        {
            $output = $device->last_sensor_values_array(implode('","',$this->output_sensors));

            if ($output === false)
                return Response::json('sensor-get-error', 500);
            else if ($output !== null)
                return Response::json($output);
        }
        return Response::json('error', 404);
    }


    private function convertOldFirmwareValues($data_array)
    {
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

        return $data_array;
    }
    
    private function storeDeviceMeta($device=null, $field=null, $value=null)
    {
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
                    case 'time_device':
                        $device->datetime = date("Y-m-d H:i:s", $value);
                        $time = time();
                        $device->datetime_offset_sec = round($value - $time, 2);
                        break;
                    default:
                        $device->{$field} = $value;
                        break;

                }
            }
        }
        return $device;
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
        if (isset($request_data['payload_fields'])) // TTN v2 with decoder installed
            $data_array = $request_data['payload_fields'];
        else if (isset($request_data['payload_raw'])) // TTN v2
            $data_array = $this->decode_ttn_payload($request_data);
        else if (isset($request_data['uplink_message']) && isset($request_data['end_device_ids'])) // TTN v3 (Things cloud)
            $data_array = $this->decode_ttn_payload($request_data);
        else if (isset($request_data['uplink_message']) && isset($request_data['end_device_ids'])) // TTN v3 (Things cloud)
            $data_array = $this->decode_ttn_payload($request_data);

        // process downlink
        if (isset($data_array['key']) && isset($data['downlink_url']))
            $this->sendDeviceDownlink($data_array['key'], $data['downlink_url']);

        if (isset($data_array['key']) && isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($data['port']) && $data['port'] == 6) // downlink response
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
    @bodyParam payload_raw string TTN BEEP Measurement data in Base 64 encoded string
    @bodyParam payload_fields json TTN Measurement data array
    @bodyParam DevEUI_uplink json KPN Measurement data array
    */
    public function lora_sensors(Request $request)
    {
        $data_array   = [];
        $request_data = $request->input();
        $payload_type = '';

        // distinguish type of data
        if ($request->filled('LrnDevEui') && $request->filled('DevEUI_uplink.payload_hex')) // KPN/Simpoint HTTPS POST
        {
            $data_array = $this->parse_kpn_payload($request_data);
            $payload_type = 'kpn';
        }
        else if (($request->filled('payload_fields') || $request->filled('payload_raw')) && $request->filled('hardware_serial')) // TTN V2 HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $payload_type = 'ttn-v2';
        }
        else if (($request->filled('data') || $request->filled('identifiers'))) // TTN V3 Packet broker HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data['data']);
            $payload_type = 'ttn-v3-pb';
        }
        else if (($request->filled('end_device_ids') || $request->filled('uplink_message'))) // TTN V3 HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $payload_type = 'ttn-v3';
        }
        $this->cacheRequestRate('store-lora-sensors-'.$payload_type);
        
        //die(print_r([$payload_type, $data_array, 'r'=>$request_data]));
        if (env('APP_ENV') == 'test')
        {
            $port        = isset($data_array['port']) ? '_port'.$data_array['port'] : '';
            $logFileName = isset($data_array['key'])  ? 'lora_sensor_'.$data_array['key'].$port.'.json' : 'lora_sensor_no_key.json';
            Storage::disk('local')->put('sensors/'.$logFileName, '[{"payload_type":"'.$payload_type.'"},{"request_input":'.json_encode($request_data).'},{"data_array":'.json_encode($data_array).'}]');
        }

        return $this->storeMeasurements($data_array);
    }

    /**
    api/sensors POST
    Store sensor measurement data (see BEEP sensor data API definition) from API, or TTN. See /sensors/measurement_types?locale=en which measurement types can be used to POST data to.
    @bodyParam key/data json required Measurement data as JSON: {"key":"your_beep_device_key", "t":18.4, t_i":34.5, "weight_kg":57.348, "h":58, "bv":3.54}
    @queryParam key/data string required Measurement formatted as URL query: key=your_beep_device_key&t=18.4&t_i=34.5&weight_kg=57.348&h=58&bv=3.54
    */
    public function storeMeasurementData(Request $request)
    {
        $request_data = $request->input();
        // Check for valid data 
        if (($request->filled('payload_fields') || $request->filled('payload_raw')) && $request->filled('hardware_serial')) // TTN HTTP POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-ttn-v2');
        }
        else if (($request->filled('data') && $request->filled('identifiers'))) // TTN V3 Packet broker HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data['data']);
            $this->cacheRequestRate('store-lora-sensors-ttn-v3-pb');
        }
        else if (($request->filled('end_device_ids') || $request->filled('uplink_message'))) // TTN V3 HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-ttn-v3');
        }
        else if ($request->filled('LrnDevEui') && $request->filled('DevEUI_uplink.payload_hex')) // KPN/Simpoint
        {
            $data_array = $this->parse_kpn_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-kpn');
        }
        else if ($request->filled('data')) // Check for sensor string (colon and pipe devided) fw v1-3
        {
            if (gettype($request_data['data']) == 'array')
                $data_array = $request_data['data'];
            else
                $data_array = $this->convertSensorStringToArray($request_data['data']);
            
            $this->cacheRequestRate('store-sensors');
        }
        else // Assume post data input
        {
            $data_array = $request_data;
            $this->cacheRequestRate('store-sensors');
        }
        
        //die(print_r($data_array));
        return $this->storeMeasurements($data_array);
    }


    /**
    api/sensors/flashlog
    POST data from BEEP base fw 1.5.0+ FLASH log (with timestamp), interpret data and store in InlfuxDB (overwriting existing data). BEEP base BLE cmd: when the response is 200 OK and erase_mx_flash > -1, provide the ERASE_MX_FLASH BLE command (0x21) to the BEEP base with the last byte being the HEX value of the erase_mx_flash value (0 = 0x00, 1 = 0x01, i.e.0x2100, or 0x2101, i.e. erase_type:"fatfs", or erase_type:"full")
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam data string MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
    @bodyParam file binary File with MX_FLASH_LOG Hexadecimal string lines (new line) separated, with many rows of log data, or text file binary with all data inside.
    @queryParam show integer 1 for displaying info in result JSON, 0 for not displaying (default).
    @queryParam save integer 1 for saving the data to a file (default), 0 for not save log file.
    @queryParam fill integer 1 for filling data gaps in the database, 0 for not filling gaps (default).
    @queryParam log_size_bytes integer 0x22 decimal result of log size requested from BEEP base.
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
            'save'              => 'nullable|boolean',
            'fill'              => 'nullable|boolean',
            'log_size_bytes'    => 'nullable|integer'
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
            $device    = null;

            if (isset($sid))
            {
                if (Auth::user()->hasRole('superadmin'))
                    $device = Device::find($sid);
                else
                    $device = $user->devices()->where('id', $sid)->first();
            }
            else if (isset($key) && !isset($sid) && isset($hwi))
                $device = $user->devices()->where('hardware_id', $hwi)->where('key', $key)->first();
            else if (isset($hwi))
                $device = $user->devices()->where('hardware_id', $hwi)->first();
            else if (isset($key) && !isset($sid) && !isset($hwi))
                $device = $user->devices()->where('key', $key)->first();
            
            if ($device == null)
                return Response::json(['errors'=>'device_not_found'], 400);

            $log_bytes = $request->filled('log_size_bytes') ? intval($inp['log_size_bytes']) : null;
            $fill      = $request->filled('fill') ? $inp['fill'] : false;
            $show      = $request->filled('show') ? $inp['show'] : false;
            $save      = $request->filled('save') ? $inp['save'] : true;

            if ($device && ($request->filled('data') || $request->hasFile('file')))
            {
                $sid   = $device->id; 
                $time  = date("YmdHis");
                $disk  = env('FLASHLOG_STORAGE', 'public');
                $f_dir = 'flashlog';
                $data  = '';
                $lines = 0; 
                $bytes = 0; 
                $logtm = 0;
                $erase = -1;
                
                if ($request->hasFile('file') && $request->file('file')->isValid())
                {
                    $files= true;
                    $file = $request->file('file');
                    $name = "sensor_".$sid."_flash_$time.log";
                    $f_log= Storage::disk($disk)->putFileAs($f_dir, $file, $name); 
                    $saved= $f_log ? true : false; 
                    $data = Storage::disk($disk)->get($f_dir.'/'.$name);
                    $f_log= Storage::disk($disk)->url($f_dir.'/'.$name); 
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

                if ($data)
                {
                    $f = [
                        'user_id'       => $user->id,
                        'device_id'     => $device->id,
                        'log_file'      => $f_log,
                        'log_size_bytes'=> $log_bytes,
                        'log_saved'     => $saved
                    ];
                    $flashlog = new FlashLog($f); 
                    $result   = $flashlog->log($data, $log_bytes, $save, $fill, $show); // creates result for erasing flashlog in BEEP base apps 
                    $messages = $result['log_messages'];
                    $lines    = $result['lines_received'];
                    $bytes    = $result['bytes_received'];
                    $logtm    = $result['log_has_timestamps'];
                    $saved    = $result['log_saved'];
                    $parsed   = $result['log_parsed'];
                    $erase    = $result['erase'];
                }

                Webhook::sendNotification("Flashlog from ".$user->name.", device: ".$device->name.", parsed:".$parsed.", size: ".round($bytes/1024/1024, 2)."MB (".($log_bytes != null && $log_bytes > 0 ? round(100*$bytes/$log_bytes, 1) : '?')."%), messages:".$messages.", saved:".$saved.", erased:".$erase.", to disk:".$disk.'/'.$f_dir);
            }

            if ($show)
            {
                $result['fields'] = array_keys($inp);
                //$result['output'] = $out;
            }
        }

        return Response::json($result, $parsed ? 200 : 500);
    }

    public function decode_beep_lora_payload(Request $request, $port, $payload)
    {
        return Response::json($this->decode_beep_payload($payload, $port));
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
        $this->cacheRequestRate('get-measurements');

        //Get the sensor
        $device  = $this->get_user_device($request);
        $location= $device->location();
        $names   = array_keys($this->valid_sensors);
        $names_w = array_keys($this->valid_weather);

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
        $index     = intval($request->input('index',0));
        $timeGroup = $request->input('timeGroup','day');
        $timeZone  = $request->input('timezone','UTC');
        
        $durationInterval = $interval.'s';
        $requestInterval  = $interval;
        $resolution       = null;
        $staTimestamp = new Moment();
        $staTimestamp->setTimezone($timeZone);
        $endTimestamp = new Moment();
        $endTimestamp->setTimezone($timeZone);

        $cache_sensor_names = $index < 7 ? true : false;
        // if (timeGroup != null)
        // {
            switch($interval)
            {
                case 'year':
                    $resolution = '1d';
                    $staTimestamp->subtractYears($index);
                    $endTimestamp->subtractYears($index);
                    $cache_sensor_names = false;
                    break;
                case 'month':
                    $resolution = $deviceMaxResolutionMinutes > 180 ? $deviceMaxResolutionMinutes.'m' : '3h';
                    $staTimestamp->subtractMonths($index);
                    $endTimestamp->subtractMonths($index);
                    $cache_sensor_names = false;
                    break;
                case 'week':
                    $requestInterval = 'week';
                    $resolution = $deviceMaxResolutionMinutes > 60 ? $deviceMaxResolutionMinutes.'m' : '1h';
                    $staTimestamp->subtractWeeks($index);
                    $endTimestamp->subtractWeeks($index);
                    $cache_sensor_names = false;
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
        $whereKeyAndTime    = $device->influxWhereKeys().' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\'';


        if($resolution != null)
        {
            if ($device)
            {
                $fill              = env('INFLUX_FILL', 'null');
                $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
                $queryList         = Device::getAvailableSensorNamesFromData($device->id, $names, $whereKeyAndTime, 'sensors', true, $cache_sensor_names);

                foreach ($queryList as $i => $name) 
                    $queryList[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';
                
                $groupBySelect = implode(', ', $queryList);
            }
            // Add weather
            if ($location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
            {
                $whereLoc = '"lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\'';
                $queryListWeather   = Device::getAvailableSensorNamesFromData('loc'.$location->id, $names_w, $whereLoc, 'weather', true, $cache_sensor_names);
                
                foreach ($queryListWeather as $i => $name) 
                    $queryListWeather[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';

                $groupBySelectWeather = implode(', ', $queryListWeather);
            }
        }
        
        $sensors_out = [];
        $weather_out = [];
        
        if ($groupBySelect != null) 
        {
            $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE '.$whereKeyAndTime.' '.$groupByResolution.' '.$limit;
            $sensors_out = Device::getInfluxQuery($sensorQuery, 'data');
        }

        // Add weather data
        if ($groupBySelectWeather != null && $location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
        {
            $weatherQuery = 'SELECT '.$groupBySelectWeather.' FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$staTimestampString.'\' AND time <= \''.$endTimestampString.'\' '.$groupByResolution.' '.$limit;

            $weather_out = Device::getInfluxQuery($weatherQuery, 'weather');

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

        if (count($sensors_out) == 0 && count($weather_out) == 0)
            return Response::json('sensor-none-error', 500);

        return Response::json( ['id'=>$device->id, 'interval'=>$interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'sensorDefinitions'=>$sensorDefinitions, 'cacheSensorNames'=>$cache_sensor_names] );
    }
}