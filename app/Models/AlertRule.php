<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Measurement;
use App\User;
use App\Models\Alert;
use Moment\Moment;

use Mail;
use App\Mail\AlertMail;

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
    protected $fillable = ['name', 'description', 'measurement_id', 'calculation', 'calculation_minutes', 'comparator', 'comparison', 'threshold_value', 'exclude_months', 'exclude_hours', 'exclude_hive_ids', 'alert_via_email', 'webhook_url', 'active', 'user_id', 'default_rule', 'timezone', 'alert_on_occurences', 'last_calculated_at', 'last_evaluated_at'];
    protected $hidden   = ['last_calculated_at'];

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "der"=>"Derivative", "cnt"=>"Count"];
    public static $influx_calc    = ["min"=>"MIN", "max"=>"MAX", "ave"=>"MEAN", "der"=>"DERIVATIVE", "cnt"=>"COUNT"];
    public static $comparators    = ["="=>"=", "<"=>"<", ">"=>">", "<="=>"<=", ">="=>">="];
    public static $comparisons    = ["val"=>"Value", "dif"=>"Difference", "abs"=>"Absolute value", "abs_dif"=>"Absolute differerence"];
    public static $exclude_months = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"];
    public static $exclude_hours  = [0=>"0:00 -> 0:59",1=>"1:00 -> 1:59",2=>"2:00 -> 2:59",3=>"3:00 -> 3:59",4=>"4:00 -> 4:59",5=>"5:00 -> 5:59",6=>"6:00 -> 6:59",7=>"7:00 -> 7:59",8=>"8:00 -> 8:59",9=>"9:00 -> 9:59",10=>"10:00 -> 10:59",11=>"11:00 -> 11:59",12=>"12:00 -> 12:59",13=>"13:00 -> 13:59",14=>"14:00 -> 14:59",15=>"15:00 -> 15:59",16=>"16:00 -> 16:59",17=>"17:00 -> 17:59",18=>"18:00 -> 18:59",19=>"19:00 -> 19:59",20=>"20:00 -> 20:59",21=>"21:00 -> 21:59",22=>"22:00 -> 22:59",23=>"23:00 -> 23:59"];


    public static function boot()
    {
        parent::boot();

        AlertRule::created(function($r)
        {
            $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>$r->readableFunction(), 'alert_value'=>'alert_rule_created', 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
            $a->save();
        });

        // AlertRule::updated(function($r)
        // {
        //     $alert_func = $r->measurement->pq.' '.$r->comparator.' '.$r->threshold_value.' '.$r->measurement->unit;
        //     $activated  = $r->active ? 'activated' : 'deactivated';
        //     $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>$r->readableFunction(), 'alert_value'=>'AlertRule updated and '.$activated, 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
        //     $a->save();
        // });

        AlertRule::deleting(function($r)
        {
            $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>$r->readableFunction(), 'alert_value'=>'alert_rule_deleted', 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
            $a->save();
        });
    }


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

    public function readableFunction()
    {
        $r = $this;
        $f = $r->measurement->pq.' '.__('beep.'.$r->calculation).' '.__('beep.'.$r->comparison).' '.AlertRule::$comparators[$r->comparator].' '.$r->threshold_value.' '.$r->measurement->unit;
        return $f;
    }

    public function evaluateDeviceAlerts($device, $user)
    {
        $r = $this;
        $d = $device;
        $u = $user;

        $debug_start = ' |-- D='.$d->id.' U='.$user_id.' ';

        $alert_rule_calc_date = date('Y-m-d H:i:s'); // PGe 2021-09-17: was local, now UTC (since config/app.php has UTC as default timezone)
        $r->last_evaluated_at = $alert_rule_calc_date;
        $r->save();

        if (!isset($d->hive_id) || in_array($d->hive_id, $r->exclude_hive_ids)) // only parse existing hives that are not excluded
        {
            Log::debug($debug_start.' No hive_id to evaluate, excluded hive_ids='.implode(',',$r->exclude_hive_ids));
            return 0;
        }

        if (!isset(AlertRule::$influx_calc[$r->calculation]))
        {
            Log::debug($debug_start.' Undefined calculation: '.$r->calculation);
            return 0;
        }
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
            //Log::debug(['r'=>$r->name, 'd'=>$d->name, 'lv'=>$last_values, 'mve'=>$max_value_eval]);

            // evaluate measurement values
            for ($i=0; $i < $max_value_eval; $i++) // start with oldest value, so in the end $value contains newest value
            {  

                if (!isset($last_values[$i][$m_abbr]))
                    continue;

                $value = $last_values[$i][$m_abbr];

                if (!isset($value) || $value == '')
                    continue;

                //$time  = $last_values[$i]['time'];
                if ($diff_comp)
                    $value = floatval($last_values[$i]) - floatval($last_values[$i+1]);

                if ($r->comparison == 'abs_dif')
                    $value = abs($value);

                $value = round($value, 1); // round to 1 decimal, just like the threshold_value
                $thres = round($r->threshold_value, 1);

                $evaluation = false;
                switch($r->comparator)
                {
                    case "=":
                        $evaluation = $value == $thres ? true : false;
                        break;
                    case "<":
                        $evaluation = $value < $thres ? true : false;
                        break; 
                    case ">":
                        $evaluation = $value > $thres ? true : false;
                        break; 
                    case "<=":
                        $evaluation = $value <= $thres ? true : false;
                        break; 
                    case ">=":
                        $evaluation = $value >= $thres ? true : false;
                        break; 
                }
                if ($evaluation)
                {
                    $evaluation_count++;
                    $alert_values[] = $value;
                }
            }
            
            // check if alert should be made
            if ($evaluation_count >= $r->alert_on_occurences)
            {
                // check if same alert was created at last alert of this alert_rule
                $check_date   = $r->last_calculated_at;
                $check_alert  = $d->alerts()->where('user_id', $u->id)->where('alert_rule_id', $r->id)->where('device_id', $d->id)->where('updated_at', '>=', $check_date)->first();
                $alert_counter= 1;  // # of occurrences in a row
                $a            = null;
                
                if ($check_alert) // check if user already has this alert, if so, update it if diff value is bigger
                {
                    $newest_alert_value = $alert_values[0];
                    $value_diff_new     = abs($newest_alert_value - $r->threshold_value);
                    $value_diff_old_max = 0;
                    
                    $old_alert_values = explode(', ', $check_alert->alert_value);
                    foreach ($old_alert_values as $v)
                        $value_diff_old_max = max($value_diff_old_max, abs($v - $r->threshold_value));

                    $alert_counter = $check_alert->count + 1;

                    // update attributes that could be changed over time
                    $check_alert->updated_at     = $alert_rule_calc_date;
                    $check_alert->alert_function = $r->readableFunction();
                    $check_alert->measurement_id = $r->measurement_id;
                    $check_alert->location_id    = $d->hive ? $d->hive->location_id : null;
                    $check_alert->location_name  = $d->location_name;
                    $check_alert->device_name    = $d->name;
                    $check_alert->hive_id        = $d->hive_id;
                    $check_alert->hive_name      = $d->hive_name;
                    $check_alert->count          = $alert_counter;
                        
                    if ($value_diff_new > $value_diff_old_max || ($value_diff_new == $value_diff_old_max && $r->comparator == '=')) // update the existing alert with new (higher diff) value and trigger e-mail
                    {
                        $check_alert->alert_value = implode(', ', $alert_values);
                        $a             = $check_alert;
                        $alert_comp    = $r->comparator == '=' ? 'is also equal' : 'has smaller diff';
                        Log::debug($debug_start.' Update Alert id='.$check_alert->id.' count='.$alert_counter.', v='.$check_alert->alert_value.' '.$alert_comp.': '.$value_diff_old_max.' vs new ('.$newest_alert_value.'): '.$value_diff_new);
                    }
                    else
                    {
                        Log::debug($debug_start.' Maintain Alert id='.$check_alert->id.' count='.$alert_counter.', v='.$check_alert->alert_value.' has equal, or bigger diff: '.$value_diff_old_max.' vs new ('.$newest_alert_value.'): '.$value_diff_new);
                    }
                    $check_alert->save();
                }
                else // no previous alerts, so create
                {
                    $alert_value = implode(', ', $alert_values);
                    $alert_func  = $r->readableFunction();
                    Log::debug($debug_start.' Create new Alert, v='.$alert_value.', eval_count='.$evaluation_count.' alert_count='.$alert_counter.' f='.$alert_func);

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
                    $a->user_id        = $u->id;
                    $a->count          = $alert_counter;
                    $a->save();

                    $alert_count++;

                }


                if ($a && $r->alert_via_email)
                {
                    Log::debug($debug_start.' Updated or created Alert, sending email to '.$u->email);
                    Mail::to($u->email)->send(new AlertMail($a, $u->name));
                }
                // save last evaluated date
                $r->last_calculated_at = $alert_rule_calc_date;
                $r->save();
            }
        }
        else
        {
            Log::debug($debug_start.' Max eval values: 0. Last values: '.implode(',',$last_values));
        }

        return $alert_count;
    }

    public static function parseRules()
    {
        $alertCount = 0;
        $now        = new Moment(); // UTC
        $now_month  = $now->getMonth();
        $min_ago_15 = date('Y-m-d H:i:s', time()-900); // 15 min ago

        $alertRules = AlertRule::where('active', 1)->where('default_rule', 0)->where('user_id', '!=', null)->where('last_evaluated_at', '<=', $min_ago_15)->orderBy('last_evaluated_at')->get();

        Log::debug('Parsing '.count($alertRules).' active alert rules last evaluated before '.$min_ago_15);

        foreach ($alertRules as $r) 
        {
            /*
            0. define UTC timezone
            1. evaluate only if >= 15 min ago
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
            if (isset($r->last_evaluated_at))
            {
                $min_ago = -1 * round($now->from($r->last_evaluated_at)->getMinutes()); // round to whole value
                if ($min_ago < $r->calculation_minutes) // do not parse too often
                    continue;
            }

            if (isset($r->exclude_months) && in_array($now_month, $r->exclude_months))
                continue;

            $now_local = new Moment('now', $r->timezone);  // Timezone of user that set alert rule (default: Europe/Amsterdam)
            $now_hour  = $now_local->getHour();

            if (isset($r->exclude_hours) && in_array($now_hour, $r->exclude_hours))
                continue;

            // check if user (still) exists
            $user_id     = $r->user_id;
            $user        = User::find($user_id);
            if (!isset($user))
            {
                if ($r->default_rule == false)
                {
                    $alerts = Alert::where('alert_rule_id', $r->id);
                    Log::debug('R='.$r->id.' U='.$user_id.' not found, so deleting '.$alerts->count().' alerts and rule: '.$r->name);
                    $alerts->delete();
                    $r->delete();
                }
                continue;
            }

            // define calculation per user device
            $user_devices= $user->allDevices()->where('hive_id', '!=', null)->get();

            if (count($user_devices) > 0)
            {
                Log::debug('R='.$r->id.' U='.$user_id.' ('.$r->name.') last evaluated @ '.$r->last_evaluated_at.' ('.$min_ago.' min ago), devices_with_hive='.count($user_devices));

                foreach ($user_devices as $device) 
                    $alertCount += $r->evaluateDeviceAlerts($device, $user);
            }
            else
            {
                $r->last_evaluated_at = date('Y-m-d H:i:s');
                $r->save();
            }
            
        }
        return $alertCount;
    }
}
