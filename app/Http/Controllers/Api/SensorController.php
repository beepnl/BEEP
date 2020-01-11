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
 * Store and retreive sensor data (both LoRa and direct API POSTs)
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
    @bodyParam id integer 
    @bodyParam name string required
    @bodyParam hive_id integer required
    @bodyParam type string required Category name of the hive type from the Categories table
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
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
    Edit a sensor
    @authenticated
    @bodyParam id integer 
    @bodyParam name string required
    @bodyParam hive_id integer required
    @bodyParam type string required Category name of the hive type from the Categories table
    @bodyParam key string required DEV EUI of the sensor to enable storing sensor data incoming on the api/sensors or api/lora_sensors endpoint
    @bodyParam delete boolean If true delete the sensor and all it's data in the Influx database
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
            'id'                => 'nullable|integer|unique:sensors,id'.$sid,
            'name'              => 'required|string',
            'hive_id'           => 'nullable|exists:hives,id',
            'type'              => 'required|string|exists:categories,name',
            'key'               => 'required|string|min:4|unique:sensors,key'.$sid,
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


            $sensor['hive_id']            = isset($valid_data['hive_id']) ? $valid_data['hive_id'] : null;
            $sensor['name']               = $valid_data['name']; 
            $sensor['key']                = $valid_data['key']; 
            $sensor['category_id']        = Category::findCategoryIdByParentAndName('sensor', $valid_data['type']); 
            
            return Auth::user()->sensors()->updateOrCreate(['id'=>$sensor_id], $sensor);
        }

        return null;
    }   
}