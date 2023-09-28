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
    protected $fillable = ['name', 'measurement_id', 'data_measurement_id', 'data_interval', 'data_relative_interval', 'data_interval_index', 'data_api_url', 'data_api_http_request', 'data_last_call', 'calculation', 'repository_url'];

    //internal or external model calculation type
    public static $calculations = ["model"=>"Model", "api"=>"API"];

    public function alert_rule()
    {
        return $this->belongsTo(AlertRule::class);
    }
    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function data_measurement()
    {
        return $this->belongsTo(Measurement::class, 'data_measurement_id');
    }
}
