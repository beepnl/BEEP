<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Device;
use App\Measurement;
use App\Models\AlertRuleFormula;
use Moment\Moment;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Cache;

class CalculationModel extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'calculation_models';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'measurement_id', 'data_measurement_id', 'data_interval', 'data_relative_interval', 'data_interval_index', 'data_api_url', 'data_api_http_request', 'data_last_call', 'calculation', 'repository_url', 'data_interval_amount', 'calculation_interval_minutes'];
    protected $casts    = ['data_relative_interval'=>'boolean'];

    /**
     * Model properties:
     * name
     * measurement_id           int     Output measurement id
     * data_measurement_id      int     Input measurement id
     * data_interval            str     Influx interval of the input measurement (1h, 7d, etc)
     * data_relative_interval   bool    relative of the input measurement
     * data_interval_index      int     Relative interval index (-1=next, 0=last, 1=previous)
     * data_api_url             str     API url (for external data)
     * data_api_http_request    enum    GET/POST
     * data_last_call           timest  Timestamp of last request
     * calculation              enum    internal/api/lambda
     * repository_url           str     Description of the current model
     * data_processing_function str     Name of the function to post process the data before 
     * data_interval_amount     int     Amount of data intervals to fetch
     * calculation_interval_minutes int Prefered calculation interval
     */

    // Internal or external model calculation type
    public static $request_types= ["GET"=>"GET request", "POST"=>"POST request"];
    public static $calculations = ["model_cumulative_daily_weight_anomaly"=>"Cumulative daily hive weight anomaly", 
                                   "model_colony_failure_weight_history"=>"Colony failure weight history (AWS Lambda)"];


    public function input_measurement()
    {
        return $this->belongsTo(Measurement::class, 'data_measurement_id');
    }
    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }

    public function run_model($user)
    {
        $model_result = ['error'=>'empty result'];
        
        if ($user)
        {
            $interval_array = $this->interval_array();
            $model_result   = ['user'=>$user->name, 'interval_array'=>$interval_array];
        
            switch($this->calculation)
            {
                case 'model_cumulative_daily_weight_anomaly':
                    $devices = $user->allDevices()->get();
                    if ($devices->count() > 0)
                    {
                        // get data arrays per apiary ()
                        $apiaries = $devices->groupBy('location_name');
                        $model_result['devices'] = $devices->count();

                        foreach ($apiaries as $apiary_name => $hive_devices) 
                        {
                            $model_result[$apiary_name] = [];
                            $model_result[$apiary_name]['hives'] = $hive_devices->count();

                            $apiary_data = [];
                            $has_data = false;
                            foreach ($hive_devices as $device)
                            {
                                $model_result[$apiary_name][$device->name] = [];
                                
                                $apiary_data = $this->addDeviceCleanWeight($apiary_data, $device, $interval_array);
                                if (isset($apiary_data['query']))
                                {   
                                    $model_result[$apiary_name][$device->name]['query'] = $apiary_data['query'];
                                }
                                else if (count($apiary_data) > 0)
                                {
                                    $has_data = true;
                                }
                            }
                            if ($has_data){
                                $model_result[$apiary_name]['apiary_data'] = $apiary_data;
                                $model_result[$apiary_name]['api_result']  = $this->model_cumulative_daily_weight_anomaly($apiary_data);
                                return $model_result;  // TODO: remove after it works
                            }
                        }
                    }

                    break;
                case 'model_colony_failure_weight_history':
                    // Send 240 cumulative weight values in a flat array
                    $devices = $user->allDevices()->get();
                    if ($devices->count() > 0)
                    {
                        // get data arrays per apiary ()
                        $apiaries = $devices->groupBy('location_name');
                        $model_result['devices'] = $devices->count();

                        foreach ($apiaries as $apiary_name => $hive_devices) 
                        {
                            $model_result[$apiary_name] = [];
                            $model_result[$apiary_name]['hives'] = $hive_devices->count();

                            $has_data = false;
                            foreach ($hive_devices as $device)
                            {
                                $model_result[$apiary_name][$device->name] = [];
                                
                                $device_data = [];
                                $device_data = $this->addDeviceCleanWeight($device_data, $device, $interval_array);
                                if (isset($device_data['query']))
                                {   
                                    $model_result[$apiary_name][$device->name]['query'] = $device_data['query'];
                                }
                                else if (count($device_data) > 0)
                                {
                                    $has_data = true;
                                    $model_result[$apiary_name][$device->name]['api_result']  = $this->model_colony_failure_weight_history($device_data);
                                    return $model_result; // TODO: remove after it works
                                }
                            }
                        }
                    }
                    break;
                default:
                    // No calculation
            }
        }
        return $model_result;
    }

    private function get_data($devices)
    {
        return $data_arrays;
    }

    // Calculation models (+ post data processing), return single $data_arrays
    private function model_cumulative_daily_weight_anomaly($apiary_weight_data_arrays)
    {
        // Send data to external model Lambda in JSON POST format
        // http://calculation.beep.nl:8080/cumulative_weight_anomaly
        $model_result_json = $this->callApi($apiary_weight_data_arrays);
        
        return $model_result_json;
    }

    private function model_colony_failure_weight_history($data_arrays)
    {
        // Send 240 weight diff values
        // http://calculation.beep.nl:8080/colony_survival_prediction
        $weight_array = [];

        // transform from 240 timestamps + values to single array of 240 values
        foreach ($data_arrays as $data_array)
        {
            if (isset($data_array['net_weight_kg']))
                $weight_array[] = $data_array['net_weight_kg'];

        }
        $model_result_json = $this->callApi(['weights'=>$weight_array]);

        return $model_result_json * 100;
    }


    // Calculate relevant start/end time interval
    private function interval_array()
    {
        $relative_interval  = $this->data_relative_interval;
        $interval_char      = substr($this->data_interval, -1); // m, h, d, w, etc
        $index              = $this->data_interval_index;
        $interval_count     = $this->data_interval_amount;
        $timeZone           = 'UTC';
        $interval           = 'day';
        $total_minutes      = 1440;
        $timeFormat         = 'Y-m-d H:i:s';

        switch($interval_char)
        {
            case "m":
                $total_minutes = $interval_count;
                $interval      = 'minute';
                break; 
            case "h":
                $total_minutes = $interval_count*60;
                $interval      = 'hour';
                break;
            case "d":
                $total_minutes = $interval_count*24*60;
                $interval      = 'day';
                break;
            case "w":
                $total_minutes = $interval_count*24*60*7;
                $interval      = 'week';
                break;
        }

        // Calculate start/end based on 
        $staTimestamp = new Moment(null, $timeZone);
        $endTimestamp = new Moment(null, $timeZone);
        
        // set start/end of interval
        $staIndex = $index;
        $endIndex = $index;

        if ($relative_interval)
            $staIndex += 1;

        $staTimestamp->subtractMinutes($staIndex*$total_minutes);
        $endTimestamp->subtractMinutes($endIndex*$total_minutes);

        // Relative
        if ($relative_interval)
        {
            // $start = $staTimestamp->setTimezone('UTC')->format($timeFormat);
            // $end   = $endTimestamp->setTimezone('UTC')->format($timeFormat);
            $start = $staTimestamp->startOf($interval)->setTimezone('UTC')->format($timeFormat);
            $end   = $endTimestamp->endOf($interval)->setTimezone('UTC')->format($timeFormat);
        }
        else // absolute time intervals
        {
            $start = $staTimestamp->startOf($interval)->setTimezone('UTC')->format($timeFormat);
            $end   = $endTimestamp->endOf($interval)->setTimezone('UTC')->format($timeFormat);
        }

        return ['start'=>$start, 'end'=>$end, 'interval'=>$interval, 'relative_interval'=>$relative_interval, 'index'=>$index, 'total_minutes'=>$total_minutes, 'timeZone'=>$timeZone];
    }

    // Add device clean weight to data array
    private function addDeviceCleanWeight($apiary_data, $device, $interval_array)
    {
        //$cleanWeight_query = $device -> getCleanedWeightQuery($this->data_interval, $interval_array['start'], $interval_array['end']); // getCleanedWeightQuery($resolution, $start_date, $end_date, $limit=5000, $threshold=0.75, $frame=2, $timeZone='UTC')
        
        $fill              = env('INFLUX_FILL') !== null ? env('INFLUX_FILL') : 'null';
        $groupByResolution = 'GROUP BY time('.$this->data_interval.') fill('.$fill.')';
        $whereKeyAndTime   = $device->influxWhereKeys().' AND time >= \''.$interval_array['start'].'\' AND time <= \''.$interval_array['end'].'\'';
        $name              = "weight_$device->id";
        $cleanWeight_query = 'SELECT MEAN("weight_kg") as "'.$name.'" FROM "sensors" WHERE '.$whereKeyAndTime.' '.$groupByResolution.' LIMIT '.$this->data_interval_amount;

        $cleanWeight_out = Cache::remember($cleanWeight_query, env('CACHE_TIMEOUT_LONG'), function () use ($cleanWeight_query)
        {
            return Device::getInfluxQuery($cleanWeight_query, 'alert');
        });

        if (count($cleanWeight_out) > 0)
        {
            //dd(['ccw' => count($cleanWeight_out), 'so' => count($cleanWeight_out)]);
        
            if (count($cleanWeight_out) == (count($apiary_data)+1))
            {
                array_shift($cleanWeight_out);
            }
                        
            if (count($apiary_data) == 0)
            {
                $apiary_data = $cleanWeight_out;
            }
            else
            {
                if (count($cleanWeight_out) == count($apiary_data))
                {
                    foreach ($apiary_data as $key => $value) 
                    {
                        foreach ($cleanWeight_out[$key] as $name => $value) 
                        {
                            if ($name != 'time')
                                $apiary_data[$key][$name] =  $value;
                        }
                    }
                }
            }
            
        }
        // else
        // {
        //     $apiary_data['query'] = $cleanWeight_query;
        // }
        //dd(['cleanWeight_out'=> count($cleanWeight_out), 'apiary_data' => count($apiary_data), 'sensor_query' => $sensorQuery, 'cleanWeight_query' => $cleanWeight_query, 'cleanWeight_out'=>$cleanWeight_out, 'apiary_data'=>$apiary_data]);
        
        return $apiary_data;
    }

    // Call the model api
    private function callApi($data)
    {
        $out = null;

        if ($data)
        {
            $url  = $this->data_api_url;
            $type = $this->data_api_http_request == 'POST' ? 'POST' : 'GET';

            $out  = ['url'=>$url, 'type'=>$type];
            try
            {
                $guzzle   = new Client();
                $response = $guzzle->request($type, $url, ['json' => json_encode($data), 'verify' => true, 'http_errors' => false]);
                if ($response)
                {
                    $out['status'] = $response->getStatusCode();
                    if ($response->getStatusCode() == 200)
                    {
                        return json_decode($response->getBody());
                    }
                    else
                    {
                        Log::error(['service'=>'CalculationModel::callApi', 'url'=>$url, 'type'=>$type, 'error'=>['body'=>json_decode($response->getBody()), 'code'=>$response->getStatusCode()]]);
                        $out['body'] = json_decode($response->getBody());
                    }
                }
            }
            catch(\Exception $e)
            {
                Log::error(['service'=>'CalculationModel::callApi', 'url'=>$url, 'type'=>$type, 'error'=>$e->getMessage()]);
                $out['error'] = $e->getMessage();
            }
        }
        return $out;
    }

    // Boxplot from data array
    public static function calculateBoxplot(array $data): array 
    {
        sort($data);
        $count = count($data);

        $median = function($arr) {
            $n = count($arr);
            $mid = floor($n / 2);
            if ($n % 2) {
                return $arr[$mid];
            } else {
                return ($arr[$mid - 1] + $arr[$mid]) / 2;
            }
        };

        $min = min($data);
        $max = max($data);
        $q2 = $median($data);

        $lowerHalf = array_slice($data, 0, floor($count / 2));
        $upperHalf = array_slice($data, ceil($count / 2));

        $q1 = $median($lowerHalf);
        $q3 = $median($upperHalf);


        // Calculate bit resolution
        $bitResolution = null;
        $minDiff = null;

        for ($i = 1; $i < $count; $i++) {
            $diff = abs($data[$i] - $data[$i - 1]);
            if ($diff > 0 && ($minDiff === null || $diff < $minDiff)) {
                $minDiff = $diff;
            }
        }

        if (isset($minDiff) && $minDiff > 0)
        {
           
            // Calculate range
            $range = $max - $min;

            if ($range == 0) {
                $bitResolution = 1; // All values same; technically 1 distinguishable value = 1 bit
            }

            // Calculate the number of distinguishable values
            $distinctValues = $range / $minDiff;

            // Bit resolution = ceil(log2(distinctValues))
            $bitResolution = (int) ceil(log($distinctValues, 2));
        }

        return [
            'min' => $min,
            'p25' => $q1,
            'median' => $q2,
            'p75' => $q3,
            'max' => $max,
            'count' => $count,
            'bitResolution' => $bitResolution
        ];
    }

    public static function arrayToString($array, string $separator = ', ', string $prefix = '', $not_keys=[]): string {

        if (is_array($array))
        {
            $result = [];
            foreach ($array as $key => $value)
            {
                if (is_array($not_keys) && in_array($key, $not_keys))
                    continue;

                $compositeKey = $prefix === '' ? $key : "$prefix.$key";

                if (is_array($value))
                {
                    $result[] = self::arrayToString($value, $separator, $compositeKey, $not_keys);
                } else {
                
                    $rounded_value = $value;
                    if (is_numeric($rounded_value))
                        $rounded_value = round($rounded_value, 2);

                    $result[] = "$compositeKey: $rounded_value";
                }
            }

            return implode($separator, $result);
        }

        return ''; 
    }
}



