<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\User;
use App\Sensor;
use App\Setting;
use App\Category;
use App\Measurement;
// use App\Transformer\SensorTransformer;
use Validator;
use InfluxDB;
use Response;
use Moment\Moment;
use League\Fractal;
use App\Http\Requests\PostSensorRequest;

class SensorController extends Controller
{
    protected $respose;
    protected $valid_sensors = [];
    protected $output_sensors = [];
    protected $precision   = 's';
    protected $timeFormat  = 'Y-m-d H:i:s';
    protected $maxDataPoints = 5000;
 
    public function __construct()
    {
        $this->valid_sensors  = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $this->output_sensors = Measurement::where('show_in_charts', '=', 1)->pluck('abbreviation')->toArray();
        //die(print_r($this->valid_sensors));
    }
   
    // Sensor crud functions
    public function index(Request $request)
    {
        $sensors = $request->user()->allSensors();
        
        if ($sensors->count() == 0)
            return Response::json('No sensors found', 404);

        return Response::json($sensors->get());
    }

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
            'hive_id'           => 'required|exists:hives,id',
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


            $sensor['hive_id']            = $valid_data['hive_id'];
            $sensor['name']               = $valid_data['name']; 
            $sensor['key']                = $valid_data['key']; 
            $sensor['category_id']        = Category::findCategoryIdByParentAndName('sensor', $valid_data['type']); 
            
            return Auth::user()->sensors()->updateOrCreate(['id'=>$sensor_id], $sensor);
        }

        return null;
    }
    

    // Sensor measurement functions

    protected function get_user_sensor(Request $request, $mine = false)
    {
        $this->validate($request, [
            'id'        => 'nullable|integer|exists:sensors,id',
            'key'       => 'nullable|integer|exists:sensors,key',
            'hive_id'   => 'nullable|integer|exists:hives,id',
        ]);
        
        $sensors = $request->user()->allSensors($mine); // inlude user Group hive sensors ($mine == editable)
        if ($sensors->count() > 0)
        {
            if ($request->filled('id') && $request->input('id') != 'null')
            {
                $id = $request->input('id');
                $check_sensor = $sensors->findOrFail($id);
            }
            else if ($request->filled('key') && $request->input('key') != 'null')
            {
                $key = $request->input('key');
                $check_sensor = $sensors->where('key', $key)->first();
            }
            else if ($request->filled('hive_id') && $request->input('hive_id') != 'null')
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

    protected function calculateWeightKg($sensor, $data_array)
    {
        $totalWeight    = 0;
        $use_separate   = false;
        $separate_names = ['w_fl', 'w_fr', 'w_bl', 'w_br'];

        foreach ($separate_names as $name) 
        {
            if (isset($data_array[$name]) && $data_array[$name] > 0)
                $use_separate = true;
        }
        
        foreach($data_array as $name => $value)
        {
            if (($use_separate && in_array($name, $separate_names)) || (!$use_separate && $name == 'w_v')) // 4 names w_fl, w_fr, w_bl, w_br || 1 combined name w_v
            {
                $name_offset = $this->last_sensor_measurement_time_value($sensor, $name.'_offset');
                $name_factor = $this->last_sensor_measurement_time_value($sensor, $name.'_kg_per_val');
                
                if ($name_factor === 0)
                {
                    // exclude from calculation
                }
                else if ($name_factor !== null) // factor available
                {
                    $offset = isset($name_offset) ? floatval($name_offset) : 0;
                    $factor = floatval($name_factor);
                    $weight = $factor * (floatval($value) - $offset);
                    $totalWeight += $weight;
                    
                    //die("offset=$offset factor=$factor weight=$weight totalWeight=$totalWeight");
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


    // requires at least ['name'=>value] to be set
    private function storeInfluxData($data_array, $sensor_key, $unix_timestamp)
    {
        // store posted data
        $client    = new \Influx;
        $sensors   = [];
        $points    = [];
        $unix_time = isset($unix_timestamp) ? $unix_timestamp : time();

        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, array_keys($this->valid_sensors)) )
            {
                $sensor = new \stdClass();
                $sensor->name   = $key;
                $sensor->value  = floatval($value);  
                array_push($points, 
                    new InfluxDB\Point(
                        'sensors',                  // name of the measurement
                        null,                       // the measurement value
                        ['key' => $sensor_key],     // optional tags
                        ["$key" => $sensor->value], // key value pairs
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


    private function storeMeasurements($data_array)
    {
        if (!in_array('key', array_keys($data_array)) || $data_array['key'] == '' || $data_array['key'] == null)
        {
            Storage::disk('local')->put('sensors/sensor_no_key.log', json_encode($data_array));
            return Response::json('No key provided', 400);
        }

        // Check if key is valid
        $sensor_key = $data_array['key']; // save sensor data under sensor key
        $sensor     = Sensor::where('key', $sensor_key)->first();
        if(!$sensor)
        {
            Storage::disk('local')->put('sensors/sensor_invalid_key.log', json_encode($data_array));
            return Response::json('No valid key provided', 401);
        }

        unset($data_array['key']);

        if (isset($data_array['w_v']) || isset($data_array['w_fl']) || isset($data_array['w_fr']) || isset($data_array['w_bl']) || isset($data_array['w_br'])) 
        {
            // check if calibration is required
            $calibrate = $this->last_sensor_measurement_time_value($sensor, 'calibrating_weight');
            if (floatval($calibrate) > 0)
                $cal = $this->calibrate_weight_sensors($sensor, $calibrate, false, $data_array);

            // take into account offset and multi
            $weight_kg = $this->calculateWeightKg($sensor, $data_array);
            $data_array['weight_kg'] = $weight_kg;

            // check if we need to compensate weight for temp
            $data_array = $this->add_weight_kg_corrected_with_temperature($sensor, $data_array);
        }
        
        //die(print_r($sensor_key));
        $time = time();
        if (isset($data_array['time']))
            $time = intVal($data_array['time']);

        $stored = $this->storeInfluxData($data_array, $sensor_key, $time);
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


    private function add_weight_kg_corrected_with_temperature($sensor, $data_array)
    {
        if (isset($data_array['t']))
        {
            $sensor_names    = ['w_fl', 'w_fr', 'w_bl', 'w_br'];
            $temp_weight_arr = [];
            foreach ($sensor_names as $key) 
            {
                if(isset($data_array[$key]))
                {
                    $temp_weight_arr[$key] = $data_array[$key];
                }
            }
            
            if (count($temp_weight_arr) == 0) // no value available, so do not add
                return $data_array; 

            $diff_arr = $this->last_sensor_increment_values($sensor, $data_array);

            if (!isset($diff_arr['t']) || $data_array['weight_kg'] == 0)
                return $data_array;

            $diff_temp = $diff_arr['t'];
            
            // temp -> raw weight value correlation is 2 -> 1 = 0.5 W/T
            // So apply '- diff_arr['t'] * 0.5' to raw values and then calculate the total weight again (incl. offset and factor)
            $weight_per_temp = 0.35;
            
            // foreach ($temp_weight_arr as $sens => $val) 
            // {
            //     if (isset($diff_arr[$sens]))
            //     {
            //         // check if the value is dependent in the same direction, otherwise do not correct
            //         $sign                   = (($diff_temp >= 0 && $diff_arr[$sens] >= 0) || ($diff_temp < 0 && $diff_arr[$sens] < 0)) ? -1 : 0; 
            //         $temp_weight_arr[$sens] = $val + ($sign * $weight_per_temp * $diff_temp);
            //     }
            // }
            // // calculate corrected total weight
            // $temp_corr_weight = $this->calculateWeightKg($sensor, $temp_weight_arr);;
            //die(print_r(['tw'=>$temp_weight_arr, 'diff'=>$diff_arr, 'corr'=>$temp_corr_weight,'uncorr'=>$data_array['weight_kg']]));

            $sign             = (($diff_temp >= 0 && $diff_arr['weight_kg'] >= 0) || ($diff_temp < 0 && $diff_arr['weight_kg'] < 0)) ? -1 : 0; 
            $temp_corr_weight = $data_array['weight_kg'] + ($sign * $weight_per_temp * $diff_temp);

            if ($temp_corr_weight > 0)
                $data_array['weight_kg_corrected'] = $temp_corr_weight;
        }
        return $data_array;
    }


    private function calibrate_weight_sensors($sensor, $weight_kg, $next_measurement=true, $data_array=null)
    {
        if ($next_measurement)
        {
            $store  = ['calibrating_weight'=>$weight_kg];
            //die(print_r($store));
            $stored = $this->storeInfluxData($store, $sensor->key, time());
            if ($stored)
                return true;
        }
        else
        {
            if ($weight_kg == 0)
                return 'no-calibration-weight-error';

            $search_for    = ['w_fl'=>0, 'w_fr'=>0, 'w_bl'=>0, 'w_br'=>0];
            $calibrate     = [];
            $value_counter = 0;

            // set and count values to use
            foreach ($search_for as $key => $value) 
            {
                $last_value = 0;
                if (isset($data_array[$key]))
                    $last_value = $data_array[$key];
                else
                    $last_value = $this->last_sensor_measurement_time_value($sensor, $key);
                
                $key_offset = floatval($this->last_sensor_measurement_time_value($sensor, $key.'_offset'));
                $last_value = $last_value - $key_offset;

                if (isset($last_value) && $last_value > 1)
                {
                    $calibrate[$key.'_kg_per_val'] = $last_value;
                    $search_for[$key]              = $last_value;
                    $value_counter++;
                }
                else
                {
                    $calibrate[$key.'_kg_per_val'] = 0;
                }
            }

            if (count($calibrate) == 0)
                return 'no-weight-values-error';

            $weight_part = $weight_kg / max(1, $value_counter);

            foreach ($calibrate as $key => $value) 
                $calibrate[$key] = $value != 0 ? $weight_part / $value : 0;

            // check if w_v should be added
            $key = 'w_v';
            $combined_value = 0;
            if (isset($data_array[$key]))
                $combined_value = $data_array[$key];
            else
                $combined_value = $this->last_sensor_measurement_time_value($sensor, $key);

            if ($combined_value > 0)
            {
                $calibrate[$key.'_kg_per_val'] = $weight_kg / $combined_value;
                $search_for[$key]              = $combined_value;
            }

            // switch 'off' calibrating mode
            $calibrate['calibrating_weight'] = 0;

            //die(print_r(['weight_kg'=>$weight_kg, 'values'=>$search_for, 'cal'=>$calibrate,'key'=>$sensor->key]));

            $stored = $this->storeInfluxData($calibrate, $sensor->key, time());
            if ($stored)
                return true;
        }

        return 'calibrate-error';
    }

    private function offset_weight_sensors($sensor)
    {
        $search_for    = ['w_fl', 'w_fr', 'w_bl', 'w_br', 'w_v'];
        $offset        = [];
        
        foreach ($search_for as $key) 
        {
            $value = $this->last_sensor_measurement_time_value($sensor, $key);
            if ($value != null)
                $offset[$key.'_offset'] = $value;
        }

        if (count($offset) == 0)
            return 'no-weight-values-error';

        //die(print_r(['cal'=>$offset,'key'=>$sensor->key]));

        $stored = $this->storeInfluxData($offset, $sensor->key, time());
        if ($stored)
            return true;

        return 'offset-error';
    }


    // Public functions

    // At the next measurement coming in, calibrate each weight sensor with it's part of a given weight.
    // Because the measurements can come in only each hour/ 3hrs, set a value to trigger the calcuylation on next measurement
    //
    // 1. If $next_measurement == true: save 'calibrating' = true in Influx with the sensor key
    // 2. If $next_measurement == false: save 'calibrating' = false in Influx with the sensor key and...
    // 3.   Get the last measured weight values for this sensor key, 
    //      Divide the given weight (in kg) with the amount of sensor values > 1.0 (assuming the weight is evenly distributed)
    //      Calculate the multiplier per sensor by dividing the multiplier = weight_part / (value - offset)
    //      Save the multiplier as $sensor_name.'_kg_per_val' in Influx
    public function calibrateweight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'next_measurement' => 'nullable|boolean',
            'weight_kg'        => 'numeric|required_if:next_measurement,true',
        ]);

        if ($validator->fails())
            return Response::json('validation-error', 500);

        $sensor           = $this->get_user_sensor($request, true); // requires id, key, hive_id, or nothing (if only one sensor) to be set
        $next_measurement = $request->filled('next_measurement') ? $request->input('next_measurement') : true;
        $weight_kg        = floatval($request->filled('weight_kg') ? $request->input('weight_kg') : $this->last_sensor_measurement_time_value($sensor, 'calibrating_weight'));
        $calibrated       = $this->calibrate_weight_sensors($sensor, $weight_kg, $next_measurement);

        if($calibrated === true)
        {
            if ($next_measurement)
                return Response::json("calibrating_weight", 200);
            else
                return Response::json("calibrated_weight", 201);
        }
        
        return Response::json($calibrated, 500);
    }

    public function offsetweight(Request $request)
    {
        $sensor = $this->get_user_sensor($request, true); // requires id, key, hive_id, or nothing (if only one sensor) to be set
        $offset = $this->offset_weight_sensors($sensor);

        if($offset === true)
            return Response::json("offset_weight", 201);
        
        return Response::json($offset, 500);
    }

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

    public function lastweight(Request $request)
    {
        $weight = ['w_fl', 'w_fr', 'w_bl', 'w_br', 'w_v', 'weight_kg','weight_kg_corrected','calibrating_weight'];
        $sensor = $this->get_user_sensor($request);
        $output = $this->last_sensor_values_array($sensor, implode('","',$weight));

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
            if (Sensor::all()->where('key', $request_data['LrnDevEui'])->count() > 0)
                $data_array['key'] = $request_data['LrnDevEui'];

        if (isset($request_data['DevEUI_uplink']['DevEUI'])) // KPN Simpoint msg
            if (Sensor::where('key', $request_data['DevEUI_uplink']['DevEUI'])->count() > 0)
                $data_array['key'] = $request_data['DevEUI_uplink']['DevEUI'];

        if (isset($request_data['DevEUI_location']['DevEUI'])) // KPN Simpoint msg
            if (Sensor::where('key', $request_data['DevEUI_location']['DevEUI'])->count() > 0)
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
    
    private function parse_ttn_payload($request_data)
    {
        $data_array = $request_data['payload_fields'];

        if (isset($request_data['hardware_serial']) && !isset($data_array['key']))
            $data_array['key'] = $request_data['hardware_serial']; // LoRa WAN = Device EUI
        if (isset($request_data['metadata.gateways.0.rssi']))
            $data_array['rssi'] = $request_data['metadata.gateways.0.rssi'];
        if (isset($request_data['metadata.gateways.0.snr']))
            $data_array['snr']  = $request_data['metadata.gateways.0.snr'];

        return $data_array;
    }

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
        
        $durationInterval = $interval.'s';
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