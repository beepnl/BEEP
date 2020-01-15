<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Device;
use App\Category;
use Validator;
use Response;

/**
 * @group Api\DeviceController
 * Store and retreive Devices that produce measurements
 */
class DeviceController extends Controller
{
   
    /**
    api/devices GET
    List all user Devices
    @authenticated
    @response [
        {
            "id": 51,
            "hive_id": 278,
            "name": "Device 7",
            "key": "6lwyeTGtrEx0Z2Xr",
            "created_at": "2019-06-14 12:59:20",
            "type": "beep",
            "hive_name": "Hive 2",
            "location_name": "BEEP",
            "owner": true,
            "hive": {
                "id": 278,
                "location_id": 72,
                "hive_type_id": 44,
                "color": "#3352af",
                "name": "Hive 2",
                "created_at": "2017-08-10 18:16:05",
                "type": "segeberger",
                "location": "BEEP",
                "attention": null,
                "impression": null,
                "reminder": null,
                "reminder_date": null,
                "inspection_count": 7,
                "sensors": [
                    51
                ],
                "owner": true
            }
        }
    ]
    */
    public function index(Request $request)
    {
        $devices = $request->user()->allDevices();
        
        if ($devices->count() == 0)
            return Response::json('No sensors found', 404);

        return Response::json($devices->get());
    }

    /**
    api/devices POST
    Create or Update a Device
    @authenticated
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam hardware_id string Unchangeable Device id
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    */
    public function store(Request $request)
    {
        $result = $this->updateOrCreateDevice($request->input());

        return Response::json($result, $result == null || gettype($result) == 'array' ? 500 : 201);
    }

    /**
    api/devices/multiple POST
    Store/update multiple Devices in an array of Device objects
    @authenticated
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam name string Device name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam hardware_id string Unchangeable Device id
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    */
    public function storeMultiple(Request $request)
    {
        //die(print_r($request->input()));
        foreach ($request->input() as $device) 
        {
            $result = $this->updateOrCreateDevice($device);
            if ($result == null || gettype($result) == 'array')
                return Response::json($result, 500);
        }
       
        return $this->index($request);
    }

    /**
    api/devices PUT/PATCH
    Update an existing Device
    @authenticated
    @bodyParam id integer required Device to update
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam name string Name of the sensor
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam hardware_id string Unchangeable Device id
    @bodyParam firmware_version string Firmware version of the Device
    @bodyParam hardware_version string Hardware version of the Device
    @bodyParam boot_count integer Amount of boots of the Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Device: 6 numbers between 0-9
    */
    public function update(Request $request, $id)
    {
        $result = null;

        if ($id)
        {
            $result = $this->updateOrCreateDevice($request->input());
        }

        return Response::json($result, $result == null || gettype($result) == 'array' ? 404 : 200);
    }

    public function updateOrCreateDevice($device)
    {
        $sid = isset($device['id']) ? ','.$device['id'] : '';
        $validator = Validator::make($device, [
            'key'               => 'required|string|min:4|unique:sensors,key'.$sid,
            'name'              => 'nullable|string',
            'id'                => 'nullable|integer|unique:sensors,id'.$sid,
            'hive_id'           => 'nullable|integer|exists:hives,id',
            'type'              => 'nullable|string|exists:categories,name',
            'delete'            => 'nullable|boolean'
        ]);

        if ($validator->fails())
        {
            return ['errors'=>$validator->errors()];
        }
        else
        {
            $valid_data = $validator->validated();
            $device_obj = isset($valid_data['id']) ? Auth::user()->devices->find($valid_data['id']) : null;
            $device_id  = null;
            if ($device_obj == null)
            {
                //create
                $device = [];
            }
            else
            {
                // delete
                if (isset($valid_data['delete']) && boolval($valid_data['delete']) === true)
                {
                    try
                    {
                        $client = new \Influx;
                        $query  = 'DELETE from "sensors" WHERE "key" = \''.$device_obj->key.'\'';
                        $result = $client::query($query);
                    }
                    catch(\Exception $e)
                    {
                        return ['errors'=>'Data values of device with key '.$device_obj->key.' cannot be deleted, try again later...'];
                    }
                    $device_obj->delete();
                    return 'sensor_deleted';
                }
                // edit
                $device    = $device_obj->toArray();
                $device_id = $device_obj->id; 
            }

            $typename = isset($valid_data['type']) ? $valid_data['type'] : 'beep'; 

            $device['key']                            = $valid_data['key']; 
            $device['name']                           = isset($valid_data['name']) ? $valid_data['name'] : null; 
            $device['category_id']                    = Category::findCategoryIdByParentAndName('sensor', $typename); 
            $device['hive_id']                        = isset($valid_data['hive_id']) ? $valid_data['hive_id'] : null;
            $device['last_message_received']          = isset($valid_data['last_message_received']) ? $valid_data['last_message_received'] : null;
            $device['hardware_id']                    = isset($valid_data['hardware_id']) ? $valid_data['hardware_id'] : null;
            $device['firmware_version']               = isset($valid_data['firmware_version']) ? $valid_data['firmware_version'] : null;
            $device['hardware_version']               = isset($valid_data['hardware_version']) ? $valid_data['hardware_version'] : null;
            $device['boot_count']                     = isset($valid_data['boot_count']) ? $valid_data['boot_count'] : null;
            $device['measurement_interval_min']       = isset($valid_data['measurement_interval_min']) ? $valid_data['measurement_interval_min'] : null;
            $device['measurement_transmission_ratio'] = isset($valid_data['measurement_transmission_ratio']) ? $valid_data['measurement_transmission_ratio'] : null;
            $device['ble_pin']                        = isset($valid_data['ble_pin']) ? $valid_data['ble_pin'] : null;
            
            return Auth::user()->devices()->updateOrCreate(['id'=>$device_id], $device);
        }

        return null;
    }   
}