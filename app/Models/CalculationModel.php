<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Measurement;
use App\Models\AlertRuleFormula;

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

    private function run_model($devices)
    {
        $data_arrays = get_data($devices);

        switch($this->calculation)
        {
            case 'model_cumulative_daily_weight_anomaly':
                $data_arrays = model_cumulative_daily_weight_anomaly($data_arrays);
                break;
            case 'model_colony_failure_weight_history':
                $data_arrays = model_colony_failure_weight_history($data_arrays);
                break;
            default:
                // No calculation
        }
    }

    private function get_data($devices)
    {
        return $data_arrays;
    }

    // Calculation models (+ post data processing)
    private function model_cumulative_daily_weight_anomaly($data_arrays)
    {
        return $data_arrays;
    }

    private function model_colony_failure_weight_history($data_arrays)
    {
        return $data_arrays;
    }
}
