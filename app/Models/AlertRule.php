<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Measurement;
use App\User;

class AlertRule extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_rules';

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
    protected $fillable = ['name', 'description', 'measurement_id', 'calculation', 'calculation_minutes', 'comparator', 'comparison', 'threshold_value', 'exclude_months', 'exclude_hours', 'exclude_hive_ids', 'alert_via_email', 'webhook_url', 'active', 'user_id', 'default_rule', 'timezone'];

    protected $hidden   = ['last_calculated_at'];

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "der"=>"Derivative", "cnt"=>"Count"];
    public static $comparators    = ["="=>"=", "<"=>"<", ">"=>">", "<="=>"<=", ">="=>">="];
    public static $comparisons    = ["val"=>"Value", "dif"=>"Difference", "abs"=>"Absolute value", "abs_dif"=>"Absolute differerence"];
    public static $exclude_months = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"];
    public static $exclude_hours  = [0=>"0:00 -> 0:59",1=>"1:00 -> 1:59",2=>"2:00 -> 2:59",3=>"3:00 -> 3:59",4=>"4:00 -> 4:59",5=>"5:00 -> 5:59",6=>"6:00 -> 6:59",7=>"7:00 -> 7:59",8=>"8:00 -> 8:59",9=>"9:00 -> 9:59",10=>"10:00 -> 10:59",11=>"11:00 -> 11:59",12=>"12:00 -> 12:59",13=>"13:00 -> 13:59",14=>"14:00 -> 14:59",15=>"15:00 -> 15:59",16=>"16:00 -> 16:59",17=>"17:00 -> 17:59",18=>"18:00 -> 18:59",19=>"19:00 -> 19:59",20=>"20:00 -> 20:59",21=>"21:00 -> 21:59",22=>"22:00 -> 22:59",23=>"23:00 -> 23:59"];


    public function getExcludeMonthsAttribute()
    {
        return !empty($this->attributes['exclude_months']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_months'])) : null;
    }

    public function getExcludeHoursAttribute()
    {
        return !empty($this->attributes['exclude_hours']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_hours'])) : null;
    }

    public function getExcludeHiveIdsAttribute()
    {
        return !empty($this->attributes['exclude_hive_ids']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_hive_ids'])) : null;
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function selectList()
    {
        return AlertRule::orderBy('name')->pluck('name','id');
    }

    public static function parseRules()
    {
        $alertRules = AlertRule::where('active', 1)->where('default_rule', 0)->orderByAsc('last_calculated_at');

        foreach ($alertRules as $ar) 
        {
            /*
            1. define gmt_time based of timezone
            2. if filled exclude_months, define current local time (timezone) month and exclude rule if in current local time month
            3. if filled exclude_hours, define current local time (timezone) hour and exclude rule if in current local time hour
            4. check minute diff of rule compared to calculation_minutes
            5. get comparison data from influx for all (except exclude_hive_ids) sensor keys
            6. compare result via comparator with threshold_value
            7. if result is true, create alert and check if e-mail needs to be sent
            8. set last_calculated_at to current time
            */
            
            if ($ar->last_calculated_at)
            {

            }
        }
    }
}
