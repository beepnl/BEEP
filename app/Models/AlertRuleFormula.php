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
    protected $casts              = ['future'=>'boolean'];
    protected $appends            = ['calculation_minutes'];

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

    public function getCalculationMinutesAttribute()
    {
        $ar = $this->alert_rule;
        if ($ar)
            return $ar->calculation_minutes;

        return null;
    }

    public function getUnit()
    {
        return $this->calculation == 'cnt' || $this->calculation == 'der' || $this->measurement->unit == '-' ? '' : ''.$this->measurement->unit;
    }

    public function readableFunction($short=false, $value=null)
    {
        $arf        = $this;
        $unit       = $arf->getUnit();
        $calc       = $arf->calculation_minutes == 0 ? '' : ''.$arf->calculation.' ';
        $calc_trans = $arf->calculation_minutes == 0 ? '' : __('beep.'.$arf->calculation).' ';


        if ($value != null) // alert function
            return ucfirst($calc_trans).__('beep.'.$arf->comparison).' '.$arf->measurement->pq.' = '.$value.$unit."\n(".$arf->comparator.' '.$arf->threshold_value.$unit.')';

        if ($short)
            return $arf->measurement->abbreviation.' '.$calc.$arf->comparison.' '.$arf->comparator.' '.$arf->threshold_value.$unit;

        return $arf->measurement->pq.' '.$calc_trans.__('beep.'.$arf->comparison).' '.$arf->comparator.' '.$arf->threshold_value.$unit;
    }
}
