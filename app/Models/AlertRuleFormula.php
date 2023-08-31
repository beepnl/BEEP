<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Measurement;
use App\Models\AlertRule;

class AlertRuleFormula extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_rule_formulas';

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
    protected $fillable           = ['alert_rule_id', 'measurement_id', 'calculation', 'comparator', 'comparison', 'logical', 'period_minutes', 'threshold_value', 'future'];
    protected $hidden             = [];

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "cnt"=>"Count"]; // exclude "der"=>"Derivative" for the moment (because of user interpretation complexity)
    public static $comparators    = ["="=>"equal_to", "<"=>"less_than", ">"=>"greater_than", "<="=>"less_than_or_equal", ">="=>"greater_than_or_equal"];
    public static $comparisons    = ["val"=>"Value", "inc"=>"Increase", "dec"=>"Decrease", "abs_dif"=>"Absolute_value_of_dif"]; // exclude "abs"=>"Absolute_value","dif"=>"Difference" because it has no usecase
    public static $logicals       = ["or"=>"Or", "and"=>"And"];
    

    public function alert_rule()
    {
        return $this->belongsTo(AlertRule::class);
    }
    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
}
