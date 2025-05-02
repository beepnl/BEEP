<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use App\Measurement;
use App\SensorDefinition;
use App\Models\FlashLog;
use App\Models\Webhook;
use App\Models\AlertRule;
use App\Models\Calculation;
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


        $devices = $request->user()->allDevices($mine); // inlude user Group hive sensors ($mine == false)
        $check_device = [];

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
                $keys = $request->input('key');
                if(!is_array($keys)){
                    $check_device = $devices->where('key', $keys)->first();
                }else{
                    $check_device = $devices->whereIn('key', $keys)->get();
                }
            }
            else if ($request->filled('hive_id') && $request->input('hive_id') != 'null')
            {
                $hive_ids = $request->input('hive_id');
                if(!is_array($hive_ids)){
                    $check_device = $devices->where('hive_id', $hive_ids)->first();
                }else{
                    $check_device = $devices->whereIn('hive_id', $hive_ids)->get();
                }
            }
            else
            {
                $check_device = $devices->first();
            }

            if(isset($check_device))
                return $check_device;
        }
        return null;
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
        // die(print_r($points));
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
                // enable for DEBUG
                // die(print_r($e->getMessage()));
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
        if (!isset($data_array['key']) || $data_array['key'] == '' || $data_array['key'] == null)
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
                $device = $this->addDeviceMeta($device, 'hardware_id', $data_array['hardware_id']);
                if (isset($data_array['measurement_transmission_ratio']))
                    $device = $this->addDeviceMeta($device, 'measurement_transmission_ratio', $data_array['measurement_transmission_ratio']);
                if (isset($data_array['measurement_interval_min']))
                    $device = $this->addDeviceMeta($device, 'measurement_interval_min', $data_array['measurement_interval_min']);
                if (isset($data_array['hardware_version']))
                    $device = $this->addDeviceMeta($device, 'hardware_version', $data_array['hardware_version']);
                if (isset($data_array['firmware_version']))
                    $device = $this->addDeviceMeta($device, 'firmware_version', $data_array['firmware_version']);
                if (isset($data_array['bootcount']))
                    $device = $this->addDeviceMeta($device, 'bootcount', $data_array['bootcount']);
                if (isset($data_array['time_device']))
                    $device = $this->addDeviceMeta($device, 'time_device', $data_array['time_device']);
                if (isset($data_array['time_clock']) && $data_array['time_clock'] == 'rtc')
                {
                    $device = $this->addDeviceMeta($device, 'last_downlink_result', 'RTC detected');
                    $device = $this->addDeviceMeta($device, 'rtc', true);
                }
            }
            // store metadata from sensor
            $device->last_message_received = date('Y-m-d H:i:s');
            $device->save();
        }
        else
        {
            if (isset($data_array['beep_base']) && boolval($data_array['beep_base']) && isset($data_array['hardware_id'])) // store hardware id
                $device = $this->addDeviceMeta($device, 'hardware_id', $data_array['hardware_id']); // create device if ALLOW_DEVICE_CREATION == 'true'

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

        if(!isset($device))
            return Response::json('no-device-defined');

        // Save data
        unset($data_array['key']);
        unset($data_array['hardware_id']);
        unset($data_array['beep_base']);

        if(count($data_array) == 0)
            return Response::json('no-data-to-write');


        $time = time();
        if (isset($data_array['time']))
            $time = intval($data_array['time']);


        // Add senaor data based on available device sensorDefinitions
        $date            = date($this->timeFormat, $time);
        $sensor_defs     = $device->activeSensorDefinitions();
        $sensor_defs_all = $device->sensorDefinitions;
        foreach ($sensor_defs as $sd)
        {
            if (isset($sd->output_abbr) && isset($data_array[$sd->input_abbr]))
            {
                $data_array = $device->addSensorDefinitionMeasurements($data_array, $data_array[$sd->input_abbr], $sd->input_measurement_id, $date, $sensor_defs_all);
                
            }
        }
        if (isset($data_array['debug']) && $data_array['debug'] == 1)
            return Response::json($data_array);

        // store battery voltage after applying sensor defs
        if (isset($data_array['bv']))
        {
            $battery_voltage = floatval($data_array['bv']);
            if ($battery_voltage > 100)
            {
                $battery_voltage = $battery_voltage / 1000;
                $data_array['bv'] = $battery_voltage;
            }
            $device = $this->addDeviceMeta($device, 'battery_voltage', $battery_voltage);
            $device->save();
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
    @bodyParam key string DEV EUI to look up the Device.
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

    /* KPN Simpoint JSON uplink payload:
    {
        "DevEUI_uplink":
        {
            "Time": "2019-08-02T10:56:29.744+02:00",
            "DevEUI": "xxxxxxxxxxxxxxxx",
            "FPort": "1",
            "FCntUp": "12",
            "ADRbit": "1",
            "MType": "2",
            "FCntDn": "4",
            "payload_hex": "54657374",
            "LrrRSSI": "- 112.000000",
            "LrrSNR": "1.000000"
        }
    }
    */
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

    // KPN Things SenML format is changed from 13-12-2022 onwards, see:
    // https://docs.kpnthings.com/dm/concepts/senml/upcoming-changes-in-kpn-senml
    // so check $request_data for 'n' in first field as well
    private function is_kpn_things_payload($data)
    {
        $dev_eui = false;
        $payload = false;
        $port    = false;
        $loc     = false;
        /*
        KPN Thing payload (2023-04-13):
        [
            {"bn":"urn:dev:DEVEUI:0059AC0000000000:","bt":1.76675482e9,"n":"payload","vs":"1b0c6e0c6a640a01ffcd7304000c0a0946000300020002000200010001000100010001000007000000000000"},
            {"n":"port","v":3.0},
            {"n":"timeOrigin","vs":"NETWORK"}
        ]
        */
        foreach ($data as $item) // KPN things JSON payload is array of 4? items
        {
            if (count((array)$item) > 1) // each object has 2 items
            {
                if (isset($item['bn'])) // get key (DevEUI) from "urn:dev:DEVEUI:DevEUIstring:"
                    $dev_eui = array_search('DEVEUI', explode(':', $item['bn'])) !== false ? true : false;

                if (isset($item['n']))
                {
                    if ($item['n'] == 'payload' && isset($item['vs']))
                        $payload = true;

                    if ($item['n'] == 'port' && isset($item['v']))
                        $port = true;

                    if ($item['n'] == 'locOrigin' && isset($item['vs']) && $item['vs'] == 'KPNLORA') // location 
                        $loc = true;
                }
            }
        }
        return $dev_eui && ($loc || ($port && $payload)); // only true if all three items are available
    }

    /*  KPN Things JSON uplink payload (payload optionally added to bn object after 2023-04-13):
        [{
            "bn": "urn:dev:DEVEUI:0059AC000000000:",
            "bt": 1.668814455E9,
            "n":"payload",
            "vs":"1b0c6e0c6a640a01ffcd7304000c0a0946000300020002000200010001000100010001000007000000000000"
        },
        {
            "n": "payload",
            "vs": "1b0c6e0c6a640a01ffcd7304000c0a0946000300020002000200010001000100010001000007000000000000"
        },
        {
            "n": "port",
            "v": 3.0
        },
        {
            "n": "TIME_ORIGIN",
            "vs": "THINGSENGINE"
        }]
    */
    private function parse_kpnthings_payload($request_data)
    {
        $data_array = [];

        foreach ($request_data as $item) // KPN things JSON payload is array of 4? items
        {
            if (count((array)$item) > 1) // each object has 2 items
            {
                if (isset($item['bn'])) // get key (DevEUI) from "urn:dev:DEVEUI:DevEUIstring:"
                {
                    $dev_eui_arr = explode(':', $item['bn']);
                    $dev_eui_ind = array_search('DEVEUI', $dev_eui_arr);
                    if (count($dev_eui_arr) > $dev_eui_ind+1)
                        $data_array['key'] = $dev_eui_arr[$dev_eui_ind+1];
                }

                if (isset($item['n']))
                {
                    if ($item['n'] == 'payload' && isset($item['vs']))
                        $data_array['payload'] = $item['vs'];

                    if ($item['n'] == 'port' && isset($item['v']))
                        $data_array['port'] = intval($item['v']);

                    if ($item['n'] == 'latitude' && isset($item['v']))
                        $data_array['lat'] = floatval($item['v']);

                    if ($item['n'] == 'longitude' && isset($item['v']))
                        $data_array['lon'] = floatval($item['v']);

                    if ($item['n'] == 'locTime' && isset($item['v']))
                        $data_array['time'] = intval($item['v']);
                }
            }
        }

        if (isset($data_array['key']) && isset($data_array['payload']) && isset($data_array['port']))
            $data_array = array_merge($data_array, $this->decode_kpnthings_payload($data_array));

        return $data_array;
    }

    /*  Helium format:

    // datacredits: 24 bytes  = 0.00001 USD
    0.00001 USD * 96 * 365  = 0.35 USD/year for 1x/15 min BEEP payloads
    0.35 * 3 (72 bytes p/packet)

    // port 3
    {
        "app_eui": "xxxxxxxxxxxxxxxx",
        "dc": {
            "balance": 235,
            "nonce": 1
        },
        "dev_eui": "xxxxxxxxxxxxxxxx",
        "devaddr": "DE000048",
        "downlink_url": "https://console.helium.com/api/v1/down/0d63a575-bad0-4cb0-a622-f66c729f3c9a/ONCB6I9xWxzanViZu4hjvn3X0xYBDe75/1e0f4dbb-2f80-4cd9-9ab9-3a828f37073d",
        "fcnt": 3,
        "hotspots": [
            {
                "channel": 5,
                "frequency": 868.0999755859375,
                "hold_time": 0,
                "id": "112L77THmjaWxAWTVXGpfdTdSxCfkSBMWHECnBKecHqwAruvexiN",
                "lat": 52.36352502561771,
                "long": 4.9292835895307565,
                "name": "happy-zinc-troll",
                "reported_at": 1669376394746,
                "rssi": -118.0,
                "snr": -5.800000190734863,
                "spreading": "SF12BW125",
                "status": "success"
            }
        ],
        "id": "1e0f4dbb-2f80-4cd9-9ab9-3a828f37073d",
        "metadata": {
            "adr_allowed": true,
            "cf_list_enabled": false,
            "labels": [
                {
                    "id": "e5de727f-c38b-43ed-bbf2-2547a16a0260",
                    "name": "base1",
                    "organization_id": "fbe0998c-eb26-4672-9446-0e1813e9d18e"
                }
            ],
            "multi_buy": 1,
            "organization_id": "fbe0998c-eb26-4672-9446-0e1813e9d18e",
            "preferred_hotspots": [],
            "rx_delay": 5,
            "rx_delay_actual": 5,
            "rx_delay_state": "rx_delay_established"
        },
        "name": "beepbase-6ac",
        "payload": "GwuxC6pkCgH/4y0EAAwMBv4ASAA/ACgAMQA+ADQALAAnACQAJwAZABQHAAAAAAAA",
        "payload_size": 48,
        "port": 3,
        "raw_packet": "QN4AAEiAAwAD4E8JhePsCggb04HoeMyiYbY5buh6v+tHJeJGkcqdv0AVECYxammXs6SjOxVG4ifCd9kkbQ==",
        "replay": false,
        "reported_at": 1669376394746,
        "type": "uplink",
        "uuid": "c60af58f-ec5a-4a39-826d-88c2e10aeb1c"
    }*/
    private function parse_helium_payload($request_data)
    {
        $data_array = [];

        if (isset($request_data['dev_eui']))
            if (Device::where('key', $request_data['dev_eui'])->count() > 0)
                $data_array['key'] = $request_data['dev_eui'];

        if (isset($request_data['hotspots'][0]['rssi']))
            $data_array['rssi'] = $request_data['hotspots'][0]['rssi'];
        if (isset($request_data['hotspots'][0]['snr']))
            $data_array['snr']  = $request_data['hotspots'][0]['snr'];
        if (isset($request_data['hotspots'][0]['lat']))
            $data_array['lat']  = $request_data['hotspots'][0]['lat'];
        if (isset($request_data['hotspots'][0]['long']))
            $data_array['lon']  = $request_data['hotspots'][0]['long'];

        if (isset($request_data['port']))
            $data_array['port'] = $request_data['port'];

        if (isset($request_data['payload']))
            $data_array['payload'] = $request_data['payload'];

        if (isset($data_array['key']) && isset($data_array['payload']) && isset($data_array['port']))
            $data_array = array_merge($data_array, $this->decode_helium_payload($data_array));

        return $data_array;
    }

    private function parse_swisscom_payload($request_data)
    {
        $data_array = [];
        
        if (isset($request_data['data']['DevEUI']))
        {
            $key       = $request_data['data']['DevEUI'];
            $key_lower = strtolower($key);

            if (Device::where('key', $key)->count() > 0)
                $data_array['key'] = $key;
            else if (Device::where('key', $key_lower)->count() > 0)
                $data_array['key'] = $key_lower;
        }

        if (isset($request_data['uplinkMetrics']['rssi']))
                $data_array['rssi'] = $request_data['uplinkMetrics']['rssi'];
            
        if (isset($request_data['uplinkMetrics']['snr']))
                $data_array['snr']  = $request_data['uplinkMetrics']['snr'];

        if (isset($request_data['data']['FPort']))
            $data_array['port'] = $request_data['data']['FPort'];

        if (isset($request_data['data']['payload_hex']))
            $data_array['payload'] = $request_data['data']['payload_hex'];

        if (isset($data_array['key']) && isset($data_array['payload']) && isset($data_array['port']))
            $data_array = array_merge($data_array, $this->decode_swisscom_payload($data_array));

        return $data_array;
    }

    private function addDeviceMeta($device=null, $field=null, $value=null)
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
                            return $device;
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
        else if (isset($request_data['join_accept']) && isset($request_data['end_device_ids'])) // TTN v3 (Things cloud)
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
        else if ($request->filled('rawData') && $request->filled('uplinkMetrics') && $request->filled('deviceId')) // Swisscom HTTPS POST
        {
            $data_array = $this->parse_swisscom_payload($request_data);
            $payload_type = 'swisscom';
        }
        else if (($request->filled('end_device_ids') || $request->filled('uplink_message'))) // TTN V3 HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data);
            $payload_type = 'ttn-v3';
        }
        else if ($request->filled('reported_at') && $request->filled('payload')) // Helium HTTPS POST
        {
            $data_array = $this->parse_helium_payload($request_data);
            $payload_type = 'helium';
        }
        else if (is_array($request_data) && $this->is_kpn_things_payload($request_data))
        {
            $data_array = $this->parse_kpnthings_payload($request_data);
            $payload_type = 'kpn-things';
        }
        else if (($request->filled('data') || $request->filled('identifiers'))) // TTN V3 Packet broker HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data['data']);
            $payload_type = 'ttn-v3-pb';
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
        else if (is_array($request_data) && count($request_data) > 1 && isset($request_data['rawData']['port']) && isset($request_data['deviceId']))
        {
            
            $data_array = $this->parse_swisscom_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-swisscom');
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
        else if (is_array($request_data) && count($request_data) > 1 && $this->is_kpn_things_payload($request_data)) // KPN things check is now JSON order based, which is bad practice
        {
            $data_array = $this->parse_kpnthings_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-kpn-things');
        }
        else if (is_array($request_data) && count($request_data) > 1 && isset($request_data['reported_at']) && isset($request_data['payload'])) // KPN things check is now JSON order based, which is bad practice
        {
            $data_array = $this->parse_helium_payload($request_data);
            $this->cacheRequestRate('store-lora-sensors-helium');
        }
        else if (($request->filled('data') && $request->filled('identifiers'))) // TTN V3 Packet broker HTTPS POST
        {
            $data_array = $this->parse_ttn_payload($request_data['data']);
            $this->cacheRequestRate('store-lora-sensors-ttn-v3-pb');
        }
        else if ($request->filled('data')) // Check for sensor string (colon and pipe devided) fw v1-3
        {
            if (is_array($request_data['data']))
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
    @bodyParam id integer Device id to target. (Required without key and hardware_id)
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
            $dev = $user->allDevices()->whereRaw('lower(`key`) = \''.$key.'\'')->first(); // check for case insensitive device key before validation
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
                    $device = $user->allDevices()->where('id', $sid)->first();
            }
            else if (isset($key) && !isset($sid) && isset($hwi))
                $device = $user->allDevices()->where('hardware_id', $hwi)->where('key', $key)->first();
            else if (isset($hwi))
                $device = $user->allDevices()->where('hardware_id', $hwi)->first();
            else if (isset($key) && !isset($sid) && !isset($hwi))
                $device = $user->allDevices()->where('key', $key)->first();

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
                $mime  = ['mimetype' => 'text/plain'];

                if ($request->hasFile('file') && $request->file('file')->isValid())
                {
                    $files= true;
                    $file = $request->file('file');
                    $name = "sensor_".$sid."_flash_$time.log";
                    $f_log= Storage::disk($disk)->putFileAs($f_dir, $file, $name, $mime);
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
                        $saved = Storage::disk($disk)->put($logFileName, $data, $mime);
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


    private function interval(Request $request, $relative_interval=false, $download=false, $min_interval_min=1)
    {
        $interval  = $request->input('interval','day');
        $index     = intval($request->input('index',0));
        $timeGroup = $request->input('timeGroup','day');
        $timeZone  = $request->input('timezone','UTC');

        $resolution   = null;
        $staTimestamp = new Moment(null, $timeZone);
        $endTimestamp = new Moment(null, $timeZone);

        if ($request->filled('start') && $request->filled('end'))
        {
            $interval          = null;
            $timeGroup         = null;
            $relative_interval = true;
            $staDate           = $request->input('start');
            $endDate           = $request->input('end');
        }
        else
        {
            $endDate = date('Y-m-d H:i:s');
            $device  = $this->get_user_device($request);
            if ($device)
                $staDate = $device->created_at;
            else
                $staDate = $endDate;
        }

        // set start/end of interval
        $staIndex = $index;
        $endIndex = $index;

        if ($relative_interval)
            $staIndex += 1;

        $cache_sensor_names = $index < 7 ? true : false;

        switch($interval)
        {
            case 'year':
                $resolution = '1d'; // 365 values
                $staTimestamp->subtractYears($staIndex);
                $endTimestamp->subtractYears($endIndex);
                $cache_sensor_names = false;
                break;
            case 'month':
                $resolution = $min_interval_min > 180 ? $min_interval_min.'m' : '3h'; // 240 values
                $staTimestamp->subtractMonths($staIndex);
                $endTimestamp->subtractMonths($endIndex);
                $cache_sensor_names = false;
                break;
            case 'week':
                $resolution = $min_interval_min > 60 ? $min_interval_min.'m' : '1h'; // 168 values
                $staTimestamp->subtractWeeks($staIndex);
                $endTimestamp->subtractWeeks($endIndex);
                $cache_sensor_names = false;
                break;
            case 'day':
                $resolution = $min_interval_min > 10 ? $min_interval_min.'m' : '10m'; // 144 values
                $staTimestamp->subtractDays($staIndex);
                $endTimestamp->subtractDays($endIndex);
                break;
            case 'hour':
                $resolution = $min_interval_min > 1 ? $min_interval_min.'m' : '1m'; // 60 values
                $staTimestamp->subtractHours($staIndex);
                $endTimestamp->subtractHours($endIndex);
                break;

            default: // provide data from total period
                $relative_interval = true;
                $staTimestamp = new Moment($staDate, $timeZone);
                $endTimestamp = new Moment($endDate, $timeZone);
                $durationDays = abs($staTimestamp->from($endTimestamp)->getDays());
                switch(true)
                {
                    case $durationDays > 90:
                        $resolution = $download ? ($min_interval_min > 60 ? $min_interval_min.'m' : '1h') : '1d'; // 90+ values
                        break;
                    case $durationDays > 30:
                        $resolution = $download ? ($min_interval_min > 30 ? $min_interval_min.'m' : '30m') : '6h'; // 90-270 values
                        break;
                    case $durationDays > 7:
                        $resolution = $download ? ($min_interval_min > 10 ? $min_interval_min.'m' : '10m') : '3h'; // 84-360 values
                        break;
                    case $durationDays > 2:
                        $resolution = $download ? null : ($min_interval_min > 30 ? $min_interval_min.'m' : '30m'); // 96-336 values
                        break;
                    case $durationDays > 6/24: // 6 hours
                        $resolution = $download ? null : ($min_interval_min > 10 ? $min_interval_min.'m' : '10m'); // 60-240 values
                        break;
                    default:
                        $resolution = $download ? null : ($min_interval_min > 1 ? $min_interval_min.'m' : '1m'); // 0-360 values
                        break;
                }
        }

        // Relative
        if ($relative_interval)
        {
            $start = $staTimestamp->setTimezone('UTC')->format($this->timeFormat);
            $end   = $endTimestamp->setTimezone('UTC')->format($this->timeFormat);
        }
        else // absolute time intervals
        {
            $start = $staTimestamp->startOf($interval)->setTimezone('UTC')->format($this->timeFormat);
            $end   = $endTimestamp->endOf($interval)->setTimezone('UTC')->format($this->timeFormat);
        }

        return ['start'=>$start, 'end'=>$end, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'resolution'=>$resolution, 'timeGroup'=>$timeGroup, 'timeZone'=>$timeZone, 'cacheSensorNames'=>$cache_sensor_names];
    }


    // just a copy of interal but working for multiple devices
    private function compareinterval(Request $request, $relative_interval=false, $download=false)
    {
        $interval  = $request->input('interval','day');
        $index     = intval($request->input('index',0));
        $timeGroup = $request->input('timeGroup','day');
        $timeZone  = $request->input('timezone','UTC');

        $staTimestamp = new Moment(null, $timeZone);
        $endTimestamp = new Moment(null, $timeZone);

        $resolution = null;
        $resolutions = [];
        $devices  = $this->get_user_device($request);
        foreach($devices as $device){
            array_push($resolutions, isset($device->measurement_interval_min) ? $device->measurement_interval_min : 1);
        }
        $maxResolution = max($resolutions);


        if ($request->filled('start') && $request->filled('end'))
        {
            $interval          = null;
            $timeGroup         = null;
            $relative_interval = true;
            $staDate           = $request->input('start');
            $endDate           = $request->input('end');
        }
        else
        {
            $endDate = date('Y-m-d H:i:s');
            $createds = [];
            foreach($devices as $device){
                array_push($createds, $device->created_at);
            }

            if ($devices) {
                #$createds = array_column($devices, 'created_at');
                $staDate = min($createds);
            }
            else
                $staDate = $endDate;
        }

        // set start/end of interval
        $staIndex = $index;
        $endIndex = $index;

        if ($relative_interval)
            $staIndex += 1;

        $cache_sensor_names = $index < 7 ? true : false;

        switch($interval)
        {
            case 'year':
                $resolution = '1d'; // 365 values
                $staTimestamp->subtractYears($staIndex);
                $endTimestamp->subtractYears($endIndex);
                $cache_sensor_names = false;
                break;
            case 'month':
                $resolution = $maxResolution > 180 ? $maxResolution.'m' : '3h'; // 240 values
                $staTimestamp->subtractMonths($staIndex);
                $endTimestamp->subtractMonths($endIndex);
                $cache_sensor_names = false;
                break;
            case 'week':
                $resolution = $maxResolution > 60 ? $maxResolution.'m' : '1h'; // 168 values
                $staTimestamp->subtractWeeks($staIndex);
                $endTimestamp->subtractWeeks($endIndex);
                $cache_sensor_names = false;
                break;
            case 'day':
                $resolution = $maxResolution > 10 ? $maxResolution.'m' : '10m'; // 144 values
                $staTimestamp->subtractDays($staIndex);
                $endTimestamp->subtractDays($endIndex);
                break;
            case 'hour':
                $resolution = $maxResolution > 1 ? $maxResolution.'m' : '1m'; // 60 values
                $staTimestamp->subtractHours($staIndex);
                $endTimestamp->subtractHours($endIndex);
                break;

            default: // provide data from total period
                $relative_interval = true;
                $staTimestamp = new Moment($staDate, $timeZone);
                $endTimestamp = new Moment($endDate, $timeZone);
                $durationDays = abs($staTimestamp->from($endTimestamp)->getDays());
                switch(true)
                {
                    case $durationDays > 90:
                        $resolution = $download ? ($maxResolution > 60 ? $maxResolution.'m' : '1h') : '1d'; // 90+ values
                        break;
                    case $durationDays > 30:
                        $resolution = $download ? ($maxResolution > 30 ? $maxResolution.'m' : '30m') : '6h'; // 90-270 values
                        break;
                    case $durationDays > 7:
                        $resolution = $download ? ($maxResolution > 10 ? $maxResolution.'m' : '10m') : '3h'; // 84-360 values
                        break;
                    case $durationDays > 2:
                        $resolution = $download ? null : ($maxResolution > 30 ? $maxResolution.'m' : '30m'); // 96-336 values
                        break;
                    case $durationDays > 6/24: // 6 hours
                        $resolution = $download ? null : ($maxResolution > 10 ? $maxResolution.'m' : '10m'); // 60-240 values
                        break;
                    default:
                        $resolution = $download ? null : ($maxResolution > 1 ? $maxResolution.'m' : '1m'); // 0-360 values
                        break;
                }
        }

        // Relative
        if ($relative_interval)
        {
            $start = $staTimestamp->setTimezone('UTC')->format($this->timeFormat);
            $end   = $endTimestamp->setTimezone('UTC')->format($this->timeFormat);
        }
        else // absolute time intervals
        {
            $start = $staTimestamp->startOf($interval)->setTimezone('UTC')->format($this->timeFormat);
            $end   = $endTimestamp->endOf($interval)->setTimezone('UTC')->format($this->timeFormat);
        }

        return ['start'=>$start, 'end'=>$end, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'resolution'=>$resolution, 'timeGroup'=>$timeGroup, 'timeZone'=>$timeZone, 'cacheSensorNames'=>$cache_sensor_names];
    }


    /**
    api/sensors/measurements GET
    Request all sensor measurements from a certain interval (hour, day, week, month, year) and index (0=until now, 1=previous interval, etc.)
    @authenticated
    @bodyParam key string DEV EUI to look up the sensor (Device).
    @bodyParam id integer ID to look up the sensor (Device)
    @bodyParam hive_id integer Hive ID to look up the sensor (Device)
    @bodyParam names string comma separated list of Measurement abbreviations to filter request data (weight_kg, t, h, etc.)
    @bodyParam interval string Data interval for interpolation of measurement values: hour (2min), day (10min), week (1 hour), month (3 hours), year (1 day). Default: day.
    @bodyParam relative_interval integer Load data from the selected interval relative to current time (1), or load data in absolute intervals (from start-end of hour/day/week/etc) (0). Default: 0.
    @bodyParam index integer Interval index (>=0; 0=until now, 1=previous interval, etc.). Default: 0.
    @bodyParam start date Date for start of measurements. Required without interval & index. Example: 2020-05-27 16:16
    @bodyParam end date Date for end of measurements. Required without interval & index. Example: 2020-05-30 00:00
    @bodyParam weather integer Load corresponding weather data from the weather database (1) or not (0). Example: 1
    @bodyParam timezone string Provide the front-end timezone to correct the time from UTC to front-end time. Example: Europe/Amsterdam
    */
    public function data(Request $request)
    {
        $this->cacheRequestRate('get-measurements');

        $validator = Validator::make($request->all(), [
            'id'          => 'nullable|integer|exists:sensors,id',
            'key'         => 'nullable|string|exists:sensors,key',
            'hive_id'     => 'nullable|integer|exists:hives,id',
            'start'       => 'required_without:index|date',
            'end'         => 'required_without:index|date',
            'index'       => 'required_without:start|required_with:interval|integer',
            'interval'    => 'nullable|string',
            'timeGroup'   => 'nullable|string',
            'names'       => 'nullable|string',
            'weather'     => 'nullable|integer',
            'clean_weight'=> 'nullable|integer',
            'timezone'    => 'nullable|timezone',
            'relative_interval' => 'nullable|integer',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);



        //Get the sensor
        $device  = $this->get_user_device($request);

        if (!isset($device))
            return Response::json('sensor-none-error', 500);

        $names         = $request->input('names', $this->output_sensors);
        $names_w       = $this->output_weather;

        if (count($names) == 0)
            return Response::json('sensor-no-measurements-error', 500);

        // add sensorDefinition names
        $sensorDefinitions = [];
        foreach ($names as $name)
        {
            $measurement_id   = Measurement::getIdByAbbreviation($name);
            $sensordefinition = $device->sensorDefinitions->where('output_measurement_id', $measurement_id)->sortByDesc('updated_at')->first();
            if ($sensordefinition)
                $sensorDefinitions["$name"] = $sensordefinition->toArray();
        }

        //Get the data interval
        $min_interval_min     = isset($device->measurement_interval_min) ? $device->measurement_interval_min : 1;
        $relative_interval    = boolval($request->input('relative_interval', 0));
        $loadWeather          = boolval($request->input('weather', 1));
        $loadCleanWeight      = boolval($request->input('clean_weight', 0));
        $intervalArr          = $this->interval($request, $relative_interval, false, $min_interval_min);

        $groupBySelect        = null;
        $groupBySelectWeather = null;
        $groupByResolution    = '';
        $limit                = $this->maxDataPoints;
        $relative_interval    = $intervalArr['relative_interval'];
        $resolution           = $intervalArr['resolution'];
        $cache_sensor_names   = $intervalArr['cacheSensorNames'];
        $start_date           = $intervalArr['start'];
        $end_date             = $intervalArr['end'];
        $interval             = $intervalArr['interval'];
        $index                = $intervalArr['index'];
        $timeGroup            = $intervalArr['timeGroup'];
        $timeZone             = $intervalArr['timeZone'];
        $whereKeyAndTime      = $device->influxWhereKeys().' AND time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';

        $calibration_m_abbr   = $device->calibrationsMeasurementAbbreviations();
        $add_calibrations     = count($calibration_m_abbr) > 0 ? true : false;

        if($resolution != null)
        {
            if ($device)
            {
                $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
                $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
                $queryList         = Device::getAvailableSensorNamesFromData($device->id, $names, $whereKeyAndTime, 'sensors', true, $cache_sensor_names);

                // Add calibration input measurements for recalculation
                if ($add_calibrations)
                {
                    $m_abbr_calibrations = array_keys($calibration_m_abbr);
                    foreach ($m_abbr_calibrations as $m)
                        $queryList[] = $m; // 'w_v', etc.
                }

                foreach ($queryList as $i => $name)
                    $queryList[$i] = 'MEAN("'.$name.'") AS "'.$name.'"';

                $groupBySelect = implode(', ', $queryList);

                //$groupBySelect .= ', SUM("weight_delta_noOutlier") AS "mean_weight_intake"';
            }

            // Add weather
            if ($loadWeather){

                $queryListWeather = [];

                foreach ($names_w as $name)
                    $queryListWeather[] = 'MEAN("'.$name.'") AS "'.$name.'"';

                $groupBySelectWeather = implode(', ', $queryListWeather);
            }
        }

        $sensors_out = [];
        $sensorQuery = '';

        if ($groupBySelect != null && $groupBySelect != '')
        {
            $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE '.$whereKeyAndTime.' '.$groupByResolution.' LIMIT '.$limit;
            $sensors_out = Device::getInfluxQuery($sensorQuery, 'data');
            
            // Apply SensorDefinitions that have 'recalculate' set to true
            if ($add_calibrations)
            {
                foreach ($sensors_out as $i => $data_array)
                    $sensors_out[$i] = SensorDefinition::addDeviceMeasurementCalibrations($device, $data_array, $calibration_m_abbr);
            }
        }

        // Add weather data
        if ($loadWeather && $groupBySelectWeather != null)
        {
            $location = $device->location();
        
            if (isset($location->coordinate_lat) && isset($location->coordinate_lon))
            {
                $weatherQuery = 'SELECT '.$groupBySelectWeather.' FROM "weather" WHERE "lat" = \''.$location->coordinate_lat.'\' AND "lon" = \''.$location->coordinate_lon.'\' AND time >= \''.$start_date.'\' AND time <= \''.$end_date.'\' '.$groupByResolution.' LIMIT '.$limit;
                $weather_out  = Device::getInfluxQuery($weatherQuery, 'weather');

                if (count($sensors_out) == 0)
                {
                    $sensors_out = $weather_out;
                }
                else if (count($weather_out) > 0)
                {
                    // Add weather data to existing sensor data
                    $data_time_key_arr = [];

                    foreach ($weather_out as $values)
                    {
                        $time = $values['time'];
                        $data_time_key_arr[$time] = $values;
                    }
                    // add clean_weight values to sensor time keys where the clean_weight values also exist
                    if (count($data_time_key_arr) > 0)
                    {
                        foreach ($sensors_out as $i => $values)
                        {
                            $time = $values['time'];
                            if (isset($data_time_key_arr[$time])) // add clean_weight data to already available datetime
                            {
                                $sensors_out[$i] = array_merge($sensors_out[$i], $data_time_key_arr[$time]);
                                unset($data_time_key_arr[$time]); // to retain missing values and add then later
                            }
                        }
                    }
                    // add missing time values to sensors
                    if (count($data_time_key_arr) > 0)
                        $sensors_out = array_merge($sensors_out, array_values($data_time_key_arr));
                }
            }
        }


        // Add cleaned weight
        if($loadCleanWeight){
            $alter_request = $request;
            $alter_request["id"] = [$alter_request["id"]];
            $clean_weight_query = $this -> cleanedWeightQuery($alter_request) ;
            $clean_weight_out = Device::getInfluxQuery($clean_weight_query, 'data');
            array_shift($clean_weight_out);
            if (count($sensors_out) == 0)
            {
                $sensors_out = $clean_weight_out;
            }
            else
            {
                // Add weight data to existing sensor data
                $data_time_key_arr = [];

                foreach ($clean_weight_out as $values)
                {
                    $time = $values['time'];
                    $data_time_key_arr[$time] = $values;
                }
                // add clean_weight values to sensor time keys where the clean_weight values also exist
                if (count($data_time_key_arr) > 0)
                {
                    foreach ($sensors_out as $i => $values)
                    {
                        $time = $values['time'];
                        if (isset($data_time_key_arr[$time])) // add clean_weight data to already available datetime
                        {
                            $sensors_out[$i] = array_merge($sensors_out[$i], $data_time_key_arr[$time]);
                            unset($data_time_key_arr[$time]); // to retain missing values and add then later
                        }
                    }
                }
                // add missing time values to sensors
                if (count($data_time_key_arr) > 0)
                    $sensors_out = array_merge($sensors_out, array_values($data_time_key_arr));

            }
            //return Response::json(['sensor_query' => $sensorQuery, 'cleanWeight_query' => $clean_weight_query, 'cleanWeight_out'=> $clean_weight_out, 'measurements' => $sensors_out]);
        }

        if (count($sensors_out) == 0)
            return Response::json('sensor-no-data-error', 500);

        return Response::json( ['id'=>$device->id, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'sensorDefinitions'=>$sensorDefinitions, 'cacheSensorNames'=>$cache_sensor_names] );
    }


    /**
        api/sensors/comparemeasurements GET
        Request mean measurements for multiple hives from a certain interval (hour, day, week, month, year) and index (0=until now, 1=previous interval, etc.)
        @authenticated
        @bodyParam key string DEV EUI to look up the sensor (Device).
        @bodyParam id integer ID to look up the sensor (Device)
        @bodyParam hive_id integer Hive ID to look up the sensor (Device)
        @bodyParam names string comma separated list of Measurement abbreviations to filter request data (weight_kg, t, h, etc.)
        @bodyParam interval string Data interval for interpolation of measurement values: hour (2min), day (10min), week (1 hour), month (3 hours), year (1 day). Default: day.
        @bodyParam relative_interval integer Load data from the selected interval relative to current time (1), or load data in absolute intervals (from start-end of hour/day/week/etc) (0). Default: 0.
        @bodyParam index integer Interval index (>=0; 0=until now, 1=previous interval, etc.). Default: 0.
        @bodyParam start date Date for start of measurements. Required without interval & index. Example: 2020-05-27 16:16
        @bodyParam end date Date for end of measurements. Required without interval & index. Example: 2020-05-30 00:00
        @bodyParam weather integer Load corresponding weather data from the weather database (1) or not (0). Example: 1
        @bodyParam timezone string Provide the front-end timezone to correct the time from UTC to front-end time. Example: Europe/Amsterdam
        */
        public function comparedata(Request $request)
        {
            $this->cacheRequestRate('get-measurements');

            $validator = Validator::make($request->all(), [
                'id'          => 'nullable|array|exists:sensors,id',
                'key'         => 'nullable|array|exists:sensors,key',
                'hive_id'     => 'nullable|array|exists:hives,id',
                'start'       => 'required_without:index|date',
                'end'         => 'required_without:index|date',
                'index'       => 'required_without:start|required_with:interval|integer',
                'interval'    => 'nullable|string',
                'timeGroup'   => 'nullable|string',
                'names'       => 'nullable|string',
                'weather'     => 'nullable|integer',
                'timezone'    => 'nullable|timezone',
                'relative_interval' => 'nullable|integer',
            ]);

            #return Response::json( ['status'=>"before validation"] );
    
            if ($validator->fails())
                return response()->json(['errors'=>$validator->errors()]);

            #return Response::json( ['status'=>"validated"] );
    
            //Get the sensors
            $devices  = $this->get_user_device($request);
            #return Response::json( ['status'=>"got devices"] );
            #return Response::json( ['devicesLength'=>sizeof($devices)] );
            #return Response::json( ['devices'=>$devices] );
    

            if (!isset($devices))
                return Response::json('sensor-none-error', 500);

            $names         = $request->input('names', $this->output_sensors);

            if (count($names) == 0)
                return Response::json('sensor-no-measurements-error', 500);

            
            // add sensorDefinition names
            $sensorDefinitions = [];
            foreach ($names as $name)
            {
                $measurement_id   = Measurement::getIdByAbbreviation($name);
                foreach($devices as $device){
                    $sensordefinition = $device->sensorDefinitions->where('output_measurement_id', $measurement_id)->sortByDesc('updated_at')->first();
                    if ($sensordefinition)
                        $sensorDefinitions["$name"] = ['name'=>$sensordefinition->name, 'inside'=>$sensordefinition->inside];
         
                }
            }

            //Get the data interval
            $relative_interval    = boolval($request->input('relative_interval', 0));  
            $intervalArr          = $this->compareinterval($request, $relative_interval, false);

            $groupBySelect        = null;
            $groupBySelectWeather = null;
            $groupByResolution    = '';
            $limit                = 'LIMIT '.$this->maxDataPoints;
            $relative_interval    = $intervalArr['relative_interval'];
            $resolution           = $intervalArr['resolution'];
            $cache_sensor_names   = $intervalArr['cacheSensorNames'];
            $start_date           = $intervalArr['start'];
            $end_date             = $intervalArr['end'];
            $interval             = $intervalArr['interval'];
            $index                = $intervalArr['index'];
            $timeGroup            = $intervalArr['timeGroup'];
            $timeZone             = $intervalArr['timeZone'];
            $wherekeys            = '';
            $whereTime            = 'time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';
            
            foreach($devices as $device){
                if($wherekeys !== '') $wherekeys .= ' OR ';

                $wherekeys.=$device->influxWhereKeys();
            }

            $whereKeyAndTime      = $wherekeys.' AND time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';

            if($resolution != null)
            {
                if ($devices)
                {
                    $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
                    $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
                    #$queryList         = Device::getAvailableSensorNamesFromData($device->id, $names, $whereKeyAndTime, 'sensors', true, $cache_sensor_names);
                    $queryList = $names;


                    foreach ($queryList as $i => $name) 
                        $queryList[$i] = 'MEAN("'.$name.'") AS "mean_'.$name.'", stddev("'.$name.'") AS "sd_'.$name.'"';
                        
                    
                    $groupBySelect = implode(', ', $queryList);

                    #$groupBySelect .= ', SUM("weight_delta_noOutlier") AS "weight_intake"'; 
                }

                
            $sensors_out = [];

            
            if ($groupBySelect != null && $groupBySelect != '') 
            {
                $sensorQuery = 'SELECT '.$groupBySelect.' FROM "sensors" WHERE '.$whereKeyAndTime.' '.$groupByResolution.' '.$limit;
                $sensors_out = Device::getInfluxQuery($sensorQuery, 'data');
            }

            // Add cleaned weight

            

            $cleanWeight_query = $this -> cleanedWeightQuery($request) ;
            #if(count($queries)>1){      
                $cleanWeight_query = 'SELECT mean(net_weight_kg) as mean_net_weight_kg, stddev(net_weight_kg) as sd_net_weight_kg  FROM ('.$cleanWeight_query.') where '.$whereTime.' '.$groupByResolution.' '.$limit; 
            #}
            
            $cleanWeight_out = Device::getInfluxQuery($cleanWeight_query, 'data');
            #return Response::json( ['ccw' => count($cleanWeight_out), 'so' => count($sensors_out)] );
            if (count($cleanWeight_out) == (count($sensors_out)+1)){
                array_shift($cleanWeight_out);
            }
                        
            if (count($cleanWeight_out) == count($sensors_out))
            {
                foreach ($sensors_out as $key => $value) 
                {
                    foreach ($cleanWeight_out[$key] as $name => $value) 
                    {
                        if ($name != 'time')
                            $sensors_out[$key][$name] =  $value;
                    }
                }
            }
                
            #return Response::json(['sensor_query' => $sensorQuery, 'cleanWeight_query' => $cleanWeight_query, 'cleanWeight_out'=> count($cleanWeight_out), 'sensors_out' => count($sensors_out), 'measurements'=>$cleanWeight_out, 'measurements2'=>$sensors_out]);
            
            if (count($sensors_out) == 0)
                return Response::json('sensor-no-data-error', 500);

            #return Response::json( ['cleanquery' => $cleanWeight_query, 'id'=>$device->id, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'sensorDefinitions'=>$sensorDefinitions, 'cacheSensorNames'=>$cache_sensor_names] );
      
            return Response::json( ['id'=>$device->id, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'timeGroup'=>$timeGroup, 'resolution'=>$resolution, 'measurements'=>$sensors_out, 'sensorDefinitions'=>$sensorDefinitions, 'cacheSensorNames'=>$cache_sensor_names] );
        }
    }


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


    private function getDeviations(Device $device, $resolution, $start_date, $end_date, $limit, $threshold, $frame, $timeZone){

            $wherekeys = $device->influxWhereKeys();

            $whereTreshold = 'weight_delta > '.$threshold.' OR weight_delta <'.-1*$threshold;
            $inspections   = [];

            if (isset($device -> hive))
            {
                $inspections = $device -> hive -> getAllInspectionDates();
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
                #return Response::json( ['status'=>$inspections] );
            }
            
            // array for time frames shortly before and after inspections
            $inspectionTuples = [];
            // array for other time frames
            $periodTuples = [];

            $inspectionFrame = $frame;

            // create first tuple / or the only tuple needed
            $length = count($inspections);
            
            if($length > 0){
                $periodTuples[0] = ['\''.$start_date.'\'', '\''.$inspections[0].'\''.' - '.$inspectionFrame.'h', $resolution];
            }else{
                $periodTuples[0] = ['\''.$start_date.'\'', '\''.$end_date.'\'', $resolution];
            }

            // create all tuples
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
                // $difference = round(abs(strtotime($nex) - strtotime($inter)) / 60);
                // $transRes = $this->translateResolutionToMinutes($resolution);
                // $useRes = $resolution;
                // if(!is_null($transRes) & (($difference - 2*60*$inspectionFrame)< $transRes)){
                //     $useRes = $this -> mapToSmallerResolution($resolution);
                // }
                // // add period tuple
                // if($i <= $length-1){                 
                //     $periodTuples[$i+1] = ['\''.$inter.'\' + '.$inspectionFrame.'h + '.$useRes, '\''.$nex.'\' - '.$inspectionFrame.'h', $useRes];
                // }else{
                //     $periodTuples[$i+1] = ['\''.$inter.'\' + '.$inspectionFrame.'h + '.$useRes, '\''.$end_date.'\'', $useRes];
                // }
            }

          
            $whereKeyAndTime      = $wherekeys.' AND time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';
            
                
            
            
            if($resolution != null)
            {
                if ($device)
                {
                    $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
                    $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
                    $groupInspection = 'GROUP BY time(15m)';
                    
                    #$groupBySelectOuter = 'cumulative_sum(sum(weight_delta)) as weight_kg_noOutlier'; 
                    $groupBySelectInnerInspection = 'derivative(mean(weight_kg), 15m) as weight_delta';
                    #$groupBySelectInnerPeriod = 'derivative(mean(weight_kg), '.$resolution.') as weight_delta';
                }

                
            $sensors_out = [];

            
          
                $inspectionQueries = [];
                foreach($inspectionTuples as $i => $tuple){
                    $inspectionQueries[$i] = '(SELECT * FROM ( SELECT '.$groupBySelectInnerInspection.' FROM "sensors" WHERE '.$wherekeys.
                    ' AND time >= '.$tuple[0].' AND time <= '.$tuple[1].' '.$groupInspection.' fill(linear)) WHERE '.$whereTreshold.')';
                }
                // $periodQueries = [];
                // foreach($periodTuples as $i => $tuple){
                //      $periodQueries[$i] = '(SELECT derivative(mean(weight_kg), '.$tuple[2].') as weight_delta FROM "sensors" WHERE '.$wherekeys.
                //     ' AND time >= '.$tuple[0].' AND time <= '.$tuple[1].' group by time('.$tuple[2].') '.$limit.')';
                // }
                 //  $allQueries = array_merge($periodQueries, $inspectionQueries);
                $innerQuery = implode(', ', $inspectionQueries);
            
        }
            return $innerQuery;

    }

    // Get cleaned weight query
    public function cleanedWeightQuery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'          => 'nullable|array|exists:sensors,id',
            'key'         => 'nullable|array|exists:sensors,key',
            'hive_id'     => 'nullable|array|exists:hives,id',
            'start'       => 'required_without:index|date',
            'end'         => 'required_without:index|date',
            'index'       => 'required_without:start|required_with:interval|integer',
            'interval'    => 'nullable|string',
            'timeGroup'   => 'nullable|string',
            'names'       => 'nullable|string',
            'weather'     => 'nullable|integer',
            'timezone'    => 'nullable|timezone',
            'relative_interval' => 'nullable|integer',
            'threshold' => 'nullable|integer',
            'frame'     => 'nullable|integer',
        ]);

        #return Response::json( ['status'=>"before validation"] );

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);

        if ($request->filled('threshold') && $request->input('threshold') != 'null'){
            $threshold = floatval($request->input('threshold'));
        }
        else{
            $threshold = 0.75;
        }

        if ($request->filled('frame') && $request->input('frame') != 'null'){
            $frame = intval($request->input('frame'));
        }
        else{
            $frame = 2;
        }

        //Get the sensors
        $devices  = $this->get_user_device($request);


        if (!isset($devices))
            return Response::json('sensor-none-error', 500);


        $names         = $request->input('names', $this->output_sensors);

        if (count($names) == 0)
            return Response::json('sensor-no-measurements-error', 500);


        // add sensorDefinition names
        $sensorDefinitions = [];
        foreach ($names as $name)
        {
            $measurement_id = Measurement::getIdByAbbreviation($name);
            foreach($devices as $device){
                $sensordefinition = $device->sensorDefinitions->where('output_measurement_id', $measurement_id)->sortByDesc('updated_at')->first();
                if ($sensordefinition)
                    $sensorDefinitions["$name"] = ['name'=>$sensordefinition->name, 'inside'=>$sensordefinition->inside];

            }
        }

        //Get the data interval
        $relative_interval    = boolval($request->input('relative_interval', 0));
        $intervalArr          = $this->compareinterval($request, $relative_interval, false);

        $groupBySelect        = null;
        $groupBySelectWeather = null;
        $groupByResolution    = '';
        $limit                = $this->maxDataPoints;
        $relative_interval    = $intervalArr['relative_interval'];
        $resolution           = $intervalArr['resolution'];
        $smallerResolution    = $this -> mapToSmallerResolution($resolution);
        $cache_sensor_names   = $intervalArr['cacheSensorNames'];
        $start_date           = $intervalArr['start'];
        $end_date             = $intervalArr['end'];
        $interval             = $intervalArr['interval'];
        $index                = $intervalArr['index'];
        $timeGroup            = $intervalArr['timeGroup'];
        $timeZone             = $intervalArr['timeZone'];
        $wherekeys            = '';
        $whereTime            = 'time >= \''.$start_date.'\' AND time <= \''.$end_date.'\'';
        $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
        $groupByResolution = 'GROUP BY time('.$resolution.') fill('.$fill.')';
        $groupByKeyResolutionPrev = 'GROUP BY "key",time('.$resolution.') fill(previous)';
        $groupByKeyResolutionPrevSmall = 'GROUP BY "key",time('.$smallerResolution.') fill(previous)';
        $groupByKeyResolutionNullSmall = 'GROUP BY "key",time('.$smallerResolution.') fill(null)';
        $groupByKeyResolution0Small = 'GROUP BY "key",time('.$smallerResolution.') fill(0)';
        $groupBySelectOuter = 'cumulative_sum(sum(weight_delta)) as jump'; 
        

        $innerQueries = [];
         foreach($devices as $i => $device)
         {
            $innerQuery = $this->getDeviations($device, $resolution, $start_date, $end_date, $limit, $threshold, $frame, $timeZone);
//          foreach($devices as $i => $device)
//          {
//             $innerCleanQuery  = $device->getInnerCleanQuery($resolution, $start_date, $end_date, $limit, $threshold, $frame, $timeZone);
//             if ($innerCleanQuery !== null)
//                 $innerQueries[$i] = $innerCleanQuery;
//          }
//          $innerQuery = implode(', ', $innerQueries);


            if($resolution != null)
            {
                $wherekeys=$device->influxWhereKeys();
                # get raw weight
                $weightQuery = 'SELECT mean(weight_kg) AS "weight_kg" FROM "sensors" WHERE '.$wherekeys.' AND '.$whereTime.' '.$groupByKeyResolutionNullSmall;
                # get first value to transform ylim to 0 
                $firstQuery = 'SELECT mean(first_weight) as first_weight FROM ( SELECT first(weight_kg) as first_weight FROM "sensors" WHERE '.$wherekeys. ' AND '.$whereTime.' GROUP BY time(1000d)) WHERE '.$whereTime.' '.$groupByKeyResolutionPrev;
        
                if(strlen($innerQuery)>1){
                    $sensorQuery = $innerQuery;
                    # collect all information
                    $sensorQuery = 'SELECT mean(weight_kg) - mean(jump) as net_weight_kg FROM ('.$weightQuery.'),(SELECT mean(jump) AS jump FROM (SELECT mean(jump) AS jump FROM ( SELECT '.$groupBySelectOuter.' FROM '.$innerQuery.' WHERE '.$whereTime.' '.$groupByKeyResolutionNullSmall.')  WHERE '.$whereTime.' '.$groupByKeyResolutionPrevSmall.')  WHERE '.$whereTime.' '.$groupByKeyResolution0Small.' ) WHERE '.$whereTime.' '.$groupByKeyResolutionNullSmall.' ';    
                    # this is with the correct resolution
                    $sensorQuery = 'SELECT mean(net_weight_kg) as net_weight_kg FROM ('.$sensorQuery.') WHERE '.$whereTime.' '.$groupByResolution.' LIMIT '.$limit;
                }else{
                    # no cleaning necessary
                    $sensorQuery = 'SELECT mean(weight_kg) as net_weight_kg FROM "sensors" WHERE '.$wherekeys.' and '.$whereTime.' '.$groupByResolution.' LIMIT '.$limit; // this is necessary to fill with null values when data is missing
                }
                # substract first value
                $sensorQuery = 'SELECT mean(net_weight_kg) - mean(first_weight) as net_weight_kg FROM ('.$firstQuery.'), ('.$sensorQuery.') WHERE  '.$whereTime.' '.$groupByResolution.' LIMIT '.$limit;
        
                $queries[$i] = $sensorQuery;
            }
            $query = implode('), (', array_filter($queries));
        }


        


        return $sensorQuery;
    }

}
