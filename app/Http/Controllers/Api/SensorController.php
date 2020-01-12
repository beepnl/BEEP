<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Sensor;
use App\Category;
use Validator;
use Response;

/**
 * @group Api\SensorController
 * Store and retreive Devices that produce measurements
 */
class SensorController extends Controller
{
   
    /**
    api/sensors
    List all user sensors
    @authenticated
    @response [
        {
            "id": 51,
            "hive_id": 278,
            "name": "Sensor 7",
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
        $sensors = $request->user()->allSensors();
        
        if ($sensors->count() == 0)
            return Response::json('No sensors found', 404);

        return Response::json($sensors->get());
    }

    /**
    api/sensors/store POST
    Store a new sensor
    @authenticated
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam name string Sensor name
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam hardware_id string Unchangeable Sensor Device id
    @bodyParam firmware_version string Firmware version of the Sensor Device
    @bodyParam hardware_version string Hardware version of the Sensor Device
    @bodyParam boot_count integer Amount of boots of the Sensor Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Sensor Device: 6 numbers between 0-9
    */
    public function store(Request $request)
    {
        //die(print_r($request->input()));
        foreach ($request->input() as $sensor) 
        {
            $result = $this->updateOrCreateSensor($sensor);
            if ($result == null || gettype($result) == 'array')
                return Response::json($result, 500);
        }
        return $this->index($request);
    }

    /**
    api/sensor POST
    Update a sensor
    @authenticated
    @bodyParam id integer required Sensor to update
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam name string Name of the sensor
    @bodyParam hive_id integer Hive that the sensor is measuring. Default: null
    @bodyParam type string Category name of the hive type from the Categories table. Default: beep
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
    @bodyParam last_message_received timestamp Will be converted with date('Y-m-d H:i:s', $last_message_received); before storing
    @bodyParam hardware_id string Unchangeable Sensor Device id
    @bodyParam firmware_version string Firmware version of the Sensor Device
    @bodyParam hardware_version string Hardware version of the Sensor Device
    @bodyParam boot_count integer Amount of boots of the Sensor Device
    @bodyParam measurement_interval_min float Measurement interval in minutes
    @bodyParam measurement_transmission_ratio float Measurements ratio of non-transmitted vs transmitted messages. If 0 or 1, every measurement gets transmitted.
    @bodyParam ble_pin string Bleutooth PIN of Sensor Device: 6 numbers between 0-9
    */
    public function update(Request $request)
    {
        $result = $this->updateOrCreateSensor($request->input());

        return Response::json($result, $result == null || gettype($result) == 'array' ? 500 : 200);
    }

    public function updateOrCreateSensor($sensor)
    {
        $sid = isset($sensor['id']) ? ','.$sensor['id'] : '';
        $validator = Validator::make($sensor, [
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
            $sensor_obj = isset($valid_data['id']) ? Auth::user()->sensors->find($valid_data['id']) : null;
            $sensor_id  = null;
            if ($sensor_obj == null)
            {
                //create
                $sensor = [];
            }
            else
            {
                // delete
                if (isset($valid_data['delete']) && boolval($valid_data['delete']) === true)
                {
                    try
                    {
                        $client = new \Influx;
                        $query  = 'DELETE from "sensors" WHERE "key" = \''.$sensor_obj->key.'\'';
                        $result = $client::query($query);
                    }
                    catch(\Exception $e)
                    {
                        return ['errors'=>'Data values of sensor with key '.$sensor_obj->key.' cannot be deleted, try again later...'];
                    }
                    $sensor_obj->delete();
                    return 'sensor_deleted';
                }
                // edit
                $sensor    = $sensor_obj->toArray();
                $sensor_id = $sensor_obj->id; 
            }

            $typename = isset($valid_data['type']) ? $valid_data['type'] : 'beep'; 

            $sensor['key']                            = $valid_data['key']; 
            $sensor['name']                           = isset($valid_data['name']) ? $valid_data['name'] : null; 
            $sensor['category_id']                    = Category::findCategoryIdByParentAndName('sensor', $typename); 
            $sensor['hive_id']                        = isset($valid_data['hive_id']) ? $valid_data['hive_id'] : null;
            $sensor['last_message_received']          = isset($valid_data['last_message_received']) ? $valid_data['last_message_received'] : null;
            $sensor['hardware_id']                    = isset($valid_data['hardware_id']) ? $valid_data['hardware_id'] : null;
            $sensor['firmware_version']               = isset($valid_data['firmware_version']) ? $valid_data['firmware_version'] : null;
            $sensor['hardware_version']               = isset($valid_data['hardware_version']) ? $valid_data['hardware_version'] : null;
            $sensor['boot_count']                     = isset($valid_data['boot_count']) ? $valid_data['boot_count'] : null;
            $sensor['transmission_interval_min']      = isset($valid_data['transmission_interval_min']) ? $valid_data['transmission_interval_min'] : null;
            $sensor['measurement_transmission_ratio'] = isset($valid_data['measurement_transmission_ratio']) ? $valid_data['measurement_transmission_ratio'] : null;
            $sensor['ble_pin']                        = isset($valid_data['ble_pin']) ? $valid_data['ble_pin'] : null;
            
            return Auth::user()->sensors()->updateOrCreate(['id'=>$sensor_id], $sensor);
        }

        return null;
    }   
}