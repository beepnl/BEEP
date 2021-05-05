<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Measurement;
use App\User;
use App\Models\Alert;
use Moment\Moment;

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
    protected $fillable = ['name', 'description', 'measurement_id', 'calculation', 'calculation_minutes', 'comparator', 'comparison', 'threshold_value', 'exclude_months', 'exclude_hours', 'exclude_hive_ids', 'alert_via_email', 'webhook_url', 'active', 'user_id', 'default_rule', 'timezone', 'alert_on_occurences', 'last_calculated_at'];
    protected $hidden   = ['last_calculated_at'];

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "der"=>"Derivative", "cnt"=>"Count"];
    public static $influx_calc    = ["min"=>"MIN", "max"=>"MAX", "ave"=>"MEAN", "der"=>"DERIVATIVE", "cnt"=>"COUNT"];
    public static $comparators    = ["="=>"=", "<"=>"<", ">"=>">", "<="=>"<=", ">="=>">="];
    public static $comparisons    = ["val"=>"Value", "dif"=>"Difference", "abs"=>"Absolute value", "abs_dif"=>"Absolute differerence"];
    public static $exclude_months = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"];
    public static $exclude_hours  = [0=>"0:00 -> 0:59",1=>"1:00 -> 1:59",2=>"2:00 -> 2:59",3=>"3:00 -> 3:59",4=>"4:00 -> 4:59",5=>"5:00 -> 5:59",6=>"6:00 -> 6:59",7=>"7:00 -> 7:59",8=>"8:00 -> 8:59",9=>"9:00 -> 9:59",10=>"10:00 -> 10:59",11=>"11:00 -> 11:59",12=>"12:00 -> 12:59",13=>"13:00 -> 13:59",14=>"14:00 -> 14:59",15=>"15:00 -> 15:59",16=>"16:00 -> 16:59",17=>"17:00 -> 17:59",18=>"18:00 -> 18:59",19=>"19:00 -> 19:59",20=>"20:00 -> 20:59",21=>"21:00 -> 21:59",22=>"22:00 -> 22:59",23=>"23:00 -> 23:59"];


    public function getExcludeMonthsAttribute()
    {
        return !empty($this->attributes['exclude_months']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_months'])) : [];
    }

    public function getExcludeHoursAttribute()
    {
        return !empty($this->attributes['exclude_hours']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_hours'])) : [];
    }

    public function getExcludeHiveIdsAttribute()
    {
        return !empty($this->attributes['exclude_hive_ids']) ? array_map(function($value){ return intval($value); }, explode(",", $this->attributes['exclude_hive_ids'])) : [];
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

    public function evaluateDeviceAlerts($device)
    {
        $r = $this;
        $d = $device;

        if (!isset($d))
            return 0;

        if (!isset($d->hive_id) || in_array($d->hive_id, $r->exclude_hive_ids)) // only parse existing hives that are not excluded
            return 0;

        $diff_comp   = $r->comparison == 'dif' || $r->comparison == 'abs_dif' ? true : false;
        $m_abbr      = $r->measurement->abbreviation;
        $influx_comp = AlertRule::$influx_calc[$r->calculation];
        $limit       = $diff_comp ? $r->alert_on_occurences + 1 : $r->alert_on_occurences; // one extra for diff calculation
        $last_values = $d->getSensorValues($m_abbr, $influx_comp, $r->calculation_minutes, $limit);

        $alert_count      = 0;
        $evaluation_count = 0;
        $alert_function   = '';
        $alert_values     = [];
            
        $max_value_eval   = $diff_comp ? count($last_values) - 1 : count($last_values);

        if ($max_value_eval > 0)
        {
            Log::debug(['r'=>$r->name, 'd'=>$d->name, 'lv'=>$last_values, 'mve'=>$max_value_eval]);

            // evaluate measurement values
            for ($i=0; $i < $max_value_eval; $i++) 
            {  

                if (!isset($last_values[$i][$m_abbr]))
                    continue;

                $value = $last_values[$i][$m_abbr];

                if (!isset($value) || $value == '')
                    continue;

                //$time  = $last_values[$i]['time'];
                if ($diff_comp)
                    $value = $last_values[$i] - $last_values[$i+1];

                if ($r->comparison == 'abs_dif')
                    $value = abs($value);

                $evaluation = false;
                switch($r->comparator)
                {
                    case "=":
                        $evaluation = $value == $r->threshold_value ? true : false;
                        break;
                    case "<":
                        $evaluation = $value < $r->threshold_value ? true : false;
                        break; 
                    case ">":
                        $evaluation = $value > $r->threshold_value ? true : false;
                        break; 
                    case "<=":
                        $evaluation = $value <= $r->threshold_value ? true : false;
                        break; 
                    case ">=":
                        $evaluation = $value >= $r->threshold_value ? true : false;
                        break; 
                }
                if ($evaluation)
                {
                    $evaluation_count++;
                    $alert_values[] = $value;
                }
            }
            $alert_rule_calc_date = date('Y-m-d H:i:s');

            // check if alert should be made
            if ($evaluation_count >= $r->alert_on_occurences)
            {
                // check if same alert was created at last evaluation of this alert_rule
                $check_date = $r->last_calculated_at;
                $check_alert= $d->alerts()->where('alert_rule_id', $r->id)->whereDate('created_at', $check_date)->count();
                $alert_value= implode(', ', $alert_values);
                $alert_func = $r->measurement->pq.' '.$r->comparator.' '.$threshold_value.' '.$r->measurement->unit;
                
                Log::debug(['r'=>$r->name, 'd'=>$alert_rule_calc_date, 'cd'=>$check_date, 'ca'=>$check_alert, 'av'=>$alert_value, 'af'=>$alert_func, 'ec'=>$evaluation_count]);

                if ($check_alert == 0) // no previous alerts, so create
                {
                    $a = new Alert();
                    $a->created_at     = $alert_rule_calc_date;
                    $a->updated_at     = $alert_rule_calc_date;
                    $a->alert_rule_id  = $r->id;
                    $a->alert_function = $alert_func;
                    $a->alert_value    = $alert_value;
                    $a->measurement_id = $r->measurement_id;
                    $a->location_id    = $d->hive ? $d->hive->location_id : null;
                    $a->location_name  = $d->location_name;
                    $a->device_id      = $d->id;
                    $a->device_name    = $d->name;
                    $a->hive_id        = $d->hive_id;
                    $a->hive_name      = $d->hive_name;
                    $a->user_id        = $d->user_id;
                    $a->save();

                    $alert_count++;

                    // Todo: send e-mail
                }
            }
            // save last evaluated date
            $r->last_calculated_at = $alert_rule_calc_date;
            $r->save();
        }

        return $alert_count;
    }

    public static function parseRules()
    {
        $alertCount = 0;
        $now        = new Moment(); // UTC
        $now_month  = $now->getMonth();
        $now_hour   = $now->getHour();
        $alertRules = AlertRule::where('active', 1)->where('default_rule', 0)->where('user_id', '!=', null)->orderBy('last_calculated_at')->get();

        foreach ($alertRules as $r) 
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
            
            // exclude parsing of rules
            $min_ago = 0;
            if (isset($r->last_calculated_at))
            {
                $min_ago = -1 * $now->from($r->last_calculated_at)->getMinutes();
                if ($min_ago < $r->calculation_minutes)
                    continue;
            }

            if (isset($r->exclude_months) && in_array($now_month, $r->exclude_months))
                continue;

            if (isset($r->exclude_hours) && in_array($now_hour, $r->exclude_hours))
                continue;

            Log::debug('evaluating AlertRule '.$r->name.', last calculated '.$min_ago.' min ago');

            // define calculation
            $user_id     = $r->user_id;
            $user        = User::find($user_id);
            if (!isset($user))
                continue;

            $user_devices= $user->allDevices()->get();

            foreach ($user_devices as $d) 
                $alertCount += $r->evaluateDeviceAlerts($d);
            
        }
        return $alertCount;
    }
}
