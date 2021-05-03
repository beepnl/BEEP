<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use Validator;
use Illuminate\Validation\Rule;
use Response;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Moment\Moment;
use App\Translation;
use App\Inspection;
use App\InspectionItem;

/**
 * @group Api\DeviceController
 * Store and retreive Devices that produce measurements
 */
class DeviceController extends Controller
{
    private function doTTNRequest($deviceId, $type='GET', $data=null)
    {
        $guzzle   = new Client();
        $url      = env('TTN_API_URL').'/applications/'.env('TTN_APP_NAME').'/devices/'.$deviceId;
        $response = null;

        try
        {
            $response = $guzzle->request($type, $url, ['headers'=>['Authorization'=>'Key '.env('TTN_APP_KEY')], 'json' => $data]);
        }
        catch(RequestException $e)
        {
            if (!$e->hasResponse())
                return Response::json('no_ttn_response', 500);
            
            $response = $e->getResponse();
        }

        return $response;
    }


    /**
    api/devices GET
    List all user Devices
    @authenticated
    @bodyParam hardware_id string Provide to filter on hardware_id
    @response [
        {
            "id": 1,
            "hive_id": 2,
            "name": "BEEPBASE-0000",
            "key": "000000000000000",
            "created_at": "2020-01-22 09:43:03",
            "last_message_received": null,
            "hardware_id": null,
            "firmware_version": null,
            "hardware_version": null,
            "boot_count": null,
            "measurement_interval_min": null,
            "measurement_transmission_ratio": null,
            "ble_pin": null,
            "battery_voltage": null,
            "next_downlink_message": null,
            "last_downlink_result": null,
            "type": "beep",
            "hive_name": "Hive 2",
            "location_name": "Test stand 1",
            "owner": true,
            "sensor_definitions": [
                {
                    "id": 7,
                    "name": null,
                    "inside": null,
                    "offset": 8131,
                    "multiplier": null,
                    "input_measurement_id": 7,
                    "output_measurement_id": 20,
                    "device_id": 1,
                    "input_abbr": "w_v",
                    "output_abbr": "weight_kg"
                }
            ]
        }
    ]
    */
    public function index(Request $request)
    {
        
        if ($request->filled('hardware_id'))
        {
            $hw_id   = $request->input('hardware_id');
            $devices = $request->user()->allDevices()->where('hardware_id', $hw_id)->with('sensorDefinitions');

            // TODO: Exception for old hardware id's (including 0e as first byte) that have been stored, can be removed after implementation of issue #36 (correct hw_id in native apps, LoRa message parsers and database update of old id's)
            if ($devices->count() == 0 && strlen($hw_id) == 18)
                $devices = $request->user()->allDevices()->where('hardware_id', '0e'.$hw_id)->with('sensorDefinitions');
        }  
        else
        {
            $devices = $request->user()->allDevices()->with('sensorDefinitions');
        }

        // Check for device hijacking
        if ($devices->count() == 0)
        {
            if ($request->filled('hardware_id') && Device::where('hardware_id', $request->input('hardware_id'))->count() > 0)
                return Response::json('device_not_yours', 403);

            return Response::json('no_devices_found', 404);
        }

        return Response::json($devices->get());
    }

    /**
    api/devices/ttn/{dev_id} GET
    Get a TTN Device by Device ID (BEEP hardware_id)
    @authenticated
    */
    public function getTTNDevice(Request $request, $dev_id)
    {
        
        $response = $this->doTTNRequest($dev_id);
        return Response::json($response->getData(), $response->code());
    }
    /**
    api/devices/ttn/{dev_id} POST
    Create an OTAA LoRaWAN Device in the BEEP TTN Console by dev_id (dev_id (= BEEP hardware_id) a unique identifier for the device. It can contain lowercase letters, numbers, - and _) and this payload:
    {
      "lorawan_device": {
        "dev_eui": "<8 byte identifier for the device>", 
        "app_key": "<16 byte static key that is known by the device and the application. It is used for negotiating session keys (OTAA)>"
      }
    }
    @authenticated
    */
    public function postTTNDevice(Request $request, $dev_id)
    {
        $validator = Validator::make($request->input(), [
            'lorawan_device.dev_eui' => 'required|alpha-num|size:16',
            'lorawan_device.app_key' => 'required|alpha-num|size:32'
        ]);

        if ($validator->fails())
            return ['errors'=>$validator->errors()];

        $dev_eui = $request->input('lorawan_device.dev_eui');
        $app_key = $request->input('lorawan_device.app_key');

        $response = $this->createTTNDevice($dev_id, $dev_eui, $app_key);
        return Response::json($response->getData(), $response->code());
    }

    private function createTTNDevice($dev_id, $dev_eui, $app_key)
    {
        $dev_id = strtolower($dev_id);

        $data = [
            "dev_id" => $dev_id,
            "lorawan_device"=>[
                "activation_constraints"=>"otaa",
                "app_id"=>env('TTN_APP_NAME'),
                "dev_eui"=>strtolower($dev_eui),
                "dev_id"=>$dev_id,
                "app_key"=>strtolower($app_key),
                "app_eui"=>env('TTN_APP_EUI')
            ]
        ];
        return $this->doTTNRequest($dev_id, 'POST', $data);
    }

    /**
    api/devices/{id} GET
    List one Device by id
    @authenticated
    */
    public function show(Request $request, $id)
    {
        $device = $request->user()->allDevices()->with('sensorDefinitions')->findOrFail($id);
        
        if ($device)
            return Response::json($device);

        return Response::json('no_devices_found', 404);
    }

    /**
    api/devices POST
    Create or Update a Device
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    @bodyParam create_ttn_device boolean If true, create a new LoRaWAN device in the BEEP TTN console. If succesfull, create the device.
    @bodyParam app_key string BEEP base LoRaWAN application key that you would like to store in TTN
    */

    public function store(Request $request)
    {
        $device_array = $request->input();

        if ($request->filled('create_ttn_device') && $request->input('create_ttn_device') == true && $request->filled('hardware_id'))
        {
            if ($request->user()->hasRole(['superadmin', 'admin']) == false)
            {
                $device_count = Device::where('user_id', $request->user()->id)->count();
                if ($device_count > 50)
                    return Response::json("max_ttn_devices_reached_please_request_more", 403);
            }

            $dev_eui = $request->filled('key') ? $request->input('key') : bin2hex(random_bytes(8)); // doubles output length
            $app_key = $request->filled('app_key') ? $request->input('app_key') : bin2hex(random_bytes(16)); // doubles output length
            $dev_id  = strtolower($request->input('hardware_id'));

            $response = $this->createTTNDevice($dev_id, $dev_eui, $app_key);
            if ($response->code() == 200 || $response->code() == 201)
            {
                $device_array['hardware_id']= $dev_id;
                $device_array['key']        = $dev_eui;
                $device_array['app_key']    = $app_key;
            }
            else
            {
                return Response::json($response->getData(), $response->code());
            }
        }

        $timeZone  = $request->input('timezone','Europe/Amsterdam');
        $result    = $this->updateOrCreateDevice($device_array, $timeZone);

        if (gettype($result) == 'object' && $request->filled('create_ttn_device') && isset($device_array['app_key']))
        {
            $device          = Device::find($result->id);
            $device->app_key = $device_array['app_key'];
            $device->app_eui = strtolower(env('TTN_APP_EUI'));
        }
        else
        {
            $device = $result;
        }

        if (gettype($device) == 'array' && isset($device['http_response_code']))
        {
            $http_response_code = $device['http_response_code'];
            unset($device['http_response_code']);
            return Response::json($device, $http_response_code);
        }

        return Response::json($device, $device == null || gettype($device) == 'array' ? 400 : 200);
    }

    /**
    api/devices/multiple POST
    Store/update multiple Devices in an array of Device objects
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    */
    public function storeMultiple(Request $request)
    {
        //die(print_r($request->input()));
        $timeZone   = $request->input('timezone','Europe/Amsterdam');

        foreach ($request->input() as $device) 
        {
            $result = $this->updateOrCreateDevice($device, $timeZone);

            if ($result == null || gettype($result) == 'array')
            {
                if (gettype($result) == 'array' && isset($result['http_response_code']))
                {
                    $http_response_code = $result['http_response_code'];
                    unset($result['http_response_code']);
                    return Response::json($result, $http_response_code);
                }
                return Response::json($result, 400);
            }
        }
       
        return $this->index($request);
    }

    /**
    api/devices PUT/PATCH
    Update an existing Device
    @authenticated
    @bodyParam id integer Device id to update. (Required without key and hardware_id)
    @bodyParam key string DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint. (Required without id and hardware_id)
    @bodyParam hardware_id string Hardware id of the device as device name in TTN. (Required without id and key)
    @bodyParam name string Name of the sensor
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    @bodyParam battery_voltage float Last measured battery voltage
    @bodyParam next_downlink_message string Hex string to send via downlink at next connection (LoRaWAN port 6)
    @bodyParam last_downlink_result string Result received from BEEP base after downlink message (LoRaWAN port 5)
    */
    public function update(Request $request, $id)
    {
        $result     = null;
        $timeZone   = $request->input('timezone','Europe/Amsterdam');

        if ($id)
        {
            $device       = $request->input();
            $device['id'] = $id;
            $result       = $this->updateOrCreateDevice($device, $timeZone);
        }

        if (gettype($result) == 'array' && isset($result['http_response_code']))
        {
            $http_response_code = $result['http_response_code'];
            unset($result['http_response_code']);
            return Response::json($result, $http_response_code);
        }

        return Response::json($result, $result == null || gettype($result) == 'array' ? 400 : 200);
    }

    public function updateOrCreateDevice($device, $timeZone)
    {
        $sid = isset($device['id']) ? $device['id'] : null;
        $key = isset($device['key']) ? strtolower($device['key']) : null;
        $hwi = isset($device['hardware_id']) ? strtolower($device['hardware_id']) : null;

        // Get $sid from hardware_id key combination
        if (isset($key) && !isset($id) && isset($hwi))
        {
            $dev = Device::where('hardware_id', $hwi)->where('key', $key)->first();
            if ($dev)
                $sid = $dev->id;
        }

        // user webapp generated key fix for required hw id
        if (isset($key) && !isset($id) && !isset($hwi))
        {
            $hwi = $key;
            $device['hardware_id'] = $device['key'];
        }

        $validator = Validator::make($device, [
            'key'               => ['required_without_all:id,hardware_id','string','min:4',Rule::unique('sensors', 'key')->ignore($sid)],
            'name'              => 'nullable|string',
            'id'                => ['required_without_all:key,hardware_id','integer', Rule::unique('sensors')->ignore($sid)],
            'hardware_id'       => ['required_without_all:key,id','string'],
            'hive_id'           => 'nullable|integer|exists:hives,id',
            'type'              => 'nullable|string|exists:categories,name',
            'delete'            => 'nullable|boolean'
        ]);

        if ($validator->fails())
        {
            return ['errors'=>$validator->errors().' (KEY: '.$key.', HW ID: '.$hwi.')', 'http_response_code'=>400];
        }
        else
        {
            $valid_data = $validator->validated();
            $device_new = [];
            $device_obj = null;
            $device_id  = null;

            if (isset($sid))
                $device_obj = Auth::user()->devices->find($sid);
            else if (isset($device['hardware_id']))
                $device_obj = Auth::user()->devices->where('hardware_id', $device['hardware_id'])->first();
            else if (isset($hwi))
                $device_obj = Auth::user()->devices->where('hardware_id', $hwi)->first();
            else if (isset($device['key']))
                $device_obj = Auth::user()->devices->where('key', $device['key'])->first();
            else if (isset($key))
                $device_obj = Auth::user()->devices->where('key', $key)->first();

            if ($device_obj != null)
            {
                // delete
                if (isset($valid_data['delete']) && boolval($valid_data['delete']) === true)
                {
                    // try
                    // {
                    //     $client = new \Influx;
                    //     $query  = 'DELETE from "sensors" WHERE "key" = \''.$device_obj->key.'\'';
                    //     $result = $client::query($query);
                    // }
                    // catch(\Exception $e)
                    // {
                    //     return ['errors'=>'Data values of device with key '.$device_obj->key.' cannot be deleted, try again later...', 'http_response_code'=>500];
                    // }
                    $device_obj->delete();
                    return 'device_deleted';
                }

                // edit
                $device_new = $device_obj->toArray();
                $device_id  = $device_obj->id;
            }
            else
            {
                // Check if hw id is available
                if (isset($device['hardware_id']) && Device::where('hardware_id', $device['hardware_id'])->count() > 0)
                    return ['errors'=>'device_not_yours', 'http_response_code'=>403];

                if (isset($device['key']) && Device::where('key', $device['key'])->count() > 0)
                    return ['errors'=>'device_not_yours', 'http_response_code'=>403];
            }

            $typename                  = isset($device['type']) ? $device['type'] : 'beep'; 
            $device_new['category_id'] = Category::findCategoryIdByParentAndName('sensor', $typename);

            // $device_new['id'] = $device_id; 

            // Update devicename if BEEPBASE-[####] and not a new name is being set
            if ($typename == 'beep' && isset($device['key']) && isset($device['app_key']) && (!isset($device['name']) || ($device_obj['name'] == $device['name'])) && substr($device_obj['name'], 0, 9) == 'BEEPBASE-')
            {
                $device_new['name'] = 'BEEPBASE-'.strtoupper(substr($device['key'], -4, 4));
            }
            else if (isset($device['name']))
            {
                $device_new['name'] = $device['name']; 
            }

            if (isset($device['key']))
                $device_new['key'] = $device['key'];
            
            if (isset($device['hive_id']))
            {
                $device_change = false;

                if ($device['hive_id'] != $device_new['hive_id'])
                    $device_change = true;

                // Create auto inspection
                if ($device_change)
                {
                    // First set inspection because location will be fixed after setting in hive

                    // Inspection items to add 
                    $device_added   = Category::findCategoryByRootParentAndName('hive', 'device', 'id_added', ['system']);
                    $device_removed = Category::findCategoryByRootParentAndName('hive', 'device', 'id_removed', ['system']);

                    if (isset($device_removed) && ($device['hive_id'] == null || ($device['hive_id'] != null && $device_new['hive_id'] != null))) // removed, or hive_id changed
                    {
                        $notes                      = $device_removed->transName().': '.$device_new['name'];
                        $items                      = [];
                        $items[$device_removed->id] = $sid;
                        Inspection::createInspection($items, $device_new['hive_id'], null, $notes, $timeZone); // set inspection to old hive id (from unchanged device object)
                    }

                    if (isset($device_added) && $device['hive_id'] != null) // device added, or changed to new id
                    {
                        $notes                    = $device_added->transName().': '.$device_new['name'];
                        $items                    = [];
                        $items[$device_added->id] = $sid;
                        Inspection::createInspection($items, $device['hive_id'], null, $notes, $timeZone); 
                    }
                }

                // change hive
                $device_new['hive_id'] = $device['hive_id'];
            }
            
            if (isset($device['last_message_received']))
                $device_new['last_message_received'] = $device['last_message_received'];
            
            if (isset($device['hardware_id']))
                $device_new['hardware_id'] = $hwi;
            
            if (isset($device['firmware_version']))
                $device_new['firmware_version'] = $device['firmware_version'];
            
            if (isset($device['hardware_version']))
                $device_new['hardware_version'] = $device['hardware_version'];
            
            if (isset($device['boot_count']))
                $device_new['boot_count'] = $device['boot_count'];
            
            if (isset($device['measurement_interval_min']))
                $device_new['measurement_interval_min'] = $device['measurement_interval_min'];
            
            if (isset($device['measurement_transmission_ratio']))
                $device_new['measurement_transmission_ratio'] = $device['measurement_transmission_ratio'];
            
            if (isset($device['ble_pin']))
                $device_new['ble_pin'] = $device['ble_pin'];
            
            if (isset($device['battery_voltage']))
                $device_new['battery_voltage'] = $device['battery_voltage'];
            
            if (isset($device['next_downlink_message']))
                $device_new['next_downlink_message'] = $device['next_downlink_message'];
            
            if (isset($device['last_downlink_result']))
                $device_new['last_downlink_result'] = $device['last_downlink_result'];
            
            return Auth::user()->devices()->updateOrCreate(['id'=>$device_id], $device_new);
        }

        return null;
    }   
}