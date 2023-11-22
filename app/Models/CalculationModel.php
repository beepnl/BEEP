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
        $interval_array = $this->interval_array();
        $model_result   = null;
        
        if ($user)
        {
            switch($this->calculation)
            {
                case 'model_cumulative_daily_weight_anomaly':
                    $devices = $user->allDevices()->get();
                    if ($devices->count() > 0)
                    {
                        // get data arrays per apiary ()
                        $apiaries = $devices->groupBy('location_name');

                        foreach ($apiaries as $apiary_name => $hive_devices) 
                        {
                            $apiary_data = [];
                            foreach ($hive_devices as $device)
                            {
                                $apiary_data = $this->addDeviceCleanWeight($apiary_data, $device, $interval_array);
                                if (count($apiary_data) > 0)
                                    return $apiary_data;
                            }
                        }
                        $model_result = $this->model_cumulative_daily_weight_anomaly($apiary_data);
                    }

                    break;
                case 'model_colony_failure_weight_history':
                    $data_arrays = $this->model_colony_failure_weight_history($data_arrays);
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
        $model_result_json = $this->callApi($apiary_weight_data_arrays);
        return $model_result_json;
    }

    private function model_colony_failure_weight_history($data_arrays)
    {
        return $data_arrays;
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
        $cleanWeight_query = $device -> getCleanedWeightQuery($this->data_interval, $interval_array['start'], $interval_array['end'], $this->data_interval_amount); // getCleanedWeightQuery($resolution, $start_date, $end_date, $limit=5000, $threshold=0.75, $frame=2, $timeZone='UTC')
        
        $cleanWeight_out = Cache::remember($cleanWeight_query, env('CACHE_TIMEOUT_LONG'), function () use ($cleanWeight_query)
        {
            return Device::getInfluxQuery($cleanWeight_query, 'alert');
        });

        //dd(['ccw' => count($cleanWeight_out), 'so' => count($cleanWeight_out)]);
        
        if (count($cleanWeight_out) == (count($apiary_data)+1))
        {
            array_shift($cleanWeight_out);
        }
                    
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
            try
            {
                $guzzle   = new Client();
                $response = $guzzle->request($type, $url, ['json' => $data, 'verify' => true, 'http_errors' => false]);
                if ($response)
                {
                    if ($response->getStatusCode() == 200)
                        return json_decode($response->getBody());
                    else
                        Log::error(['service'=>'CalculationModel::callApi', 'url'=>$url, 'type'=>$type, 'error'=>['body'=>json_decode($response->getBody()), 'code'=>$response->getStatusCode()]]);
                }
            }
            catch(\Exception $e)
            {
                Log::error(['service'=>'CalculationModel::callApi', 'url'=>$url, 'type'=>$type, 'error'=>$e->getMessage()]);
            }
        }
        return $out;
    }
}



