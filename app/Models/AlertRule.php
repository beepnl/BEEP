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

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "cnt"=>"Count"]; // exclude "der"=>"Derivative" for the moment (because of user interpretation complexity)
    public static $influx_calc    = ["min"=>"MIN", "max"=>"MAX", "ave"=>"MEAN", "der"=>"DERIVATIVE", "cnt"=>"COUNT"];
    public static $comparators    = ["="=>"equal_to", "<"=>"less_than", ">"=>"greater_than", "<="=>"less_than_or_equal", ">="=>"greater_than_or_equal"];
    public static $comparisons    = ["val"=>"Value", "dif"=>"Difference", "abs_dif"=>"Absolute_value_of_dif"]; // excluse "abs"=>"Absolute_value", because it has no usecase
    public static $exclude_months = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"];
    public static $exclude_hours  = [0=>"0:00 - 0:59",1=>"1:00 - 1:59",2=>"2:00 - 2:59",3=>"3:00 - 3:59",4=>"4:00 - 4:59",5=>"5:00 - 5:59",6=>"6:00 - 6:59",7=>"7:00 - 7:59",8=>"8:00 - 8:59",9=>"9:00 - 9:59",10=>"10:00 - 10:59",11=>"11:00 - 11:59",12=>"12:00 - 12:59",13=>"13:00 - 13:59",14=>"14:00 - 14:59",15=>"15:00 - 15:59",16=>"16:00 - 16:59",17=>"17:00 - 17:59",18=>"18:00 - 18:59",19=>"19:00 - 19:59",20=>"20:00 - 20:59",21=>"21:00 - 21:59",22=>"22:00 - 22:59",23=>"23:00 - 23:59"];
    public static $calc_minutes   = [0, 30, 60, 180, 360, 720, 1440, 2880, 10080];

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
        $u = $r->calculation == 'cnt' || $r->calculation == 'der' ? '' : ' '.$r->measurement->unit;
        $f = $r->measurement->pq.' '.__('beep.'.$r->calculation).' '.__('beep.'.$r->comparison).' '.$r->comparator.' '.$r->threshold_value.$u;
        return $f;
    }

    public function evaluateDeviceRuleAlerts($device, $user, $alert_rule_calc_date, $data_array=null)
    {
        $r = $this;
        $d = $device;
        $u = $user;

        $debug_start = ' |-- D='.$d->id.' ';

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
        $influx_func = AlertRule::$influx_calc[$r->calculation];
        $limit       = $diff_comp ? $r->alert_on_occurences + 1 : $r->alert_on_occurences; // one extra for diff calculation

        if (isset($data_array) && isset($data_array[$m_abbr]))
            $last_val_inf= ['values'=>[["$m_abbr"=>$data_array[$m_abbr]]], 'query'=>'', 'from'=>'measurement', 'min_ago'=>0];
        else
            $last_val_inf= $d->getAlertSensorValues($m_abbr, $influx_func, $r->calculation_minutes, $limit); // provides: ['values'=>$values,'query'=>$query, 'from'=>'cache', 'min_ago'=>$val_min_ago]

        $last_values      = $last_val_inf['values'];
        $alert_count      = 0;
        $evaluation_count = 0;
        $alert_function   = '';
        $alert_values     = [];
        $last_value_count = $diff_comp ? count($last_values) - 1 : count($last_values);
        $alert_on_no_vals = $r->calculation == 'cnt' && $r->threshold_value == 0 ? true : false;

        if ($last_value_count > 0 || $alert_on_no_vals)
        {
            //Log::debug(json_encode(['r'=>$r->name, 'd'=>$d->name, 'lv'=>$last_values, 'mve'=>$last_value_count]));

            // evaluate measurement values
            if ($last_value_count > 0)
            {
                for ($i=0; $i < $last_value_count; $i++) // start with most recent value (ordered desc on time)
                {  

                    if (!isset($last_values[$i][$m_abbr]))
                        continue;

                    $value = $last_values[$i][$m_abbr];

                    if (!isset($value) || $value == '' || $value == 'null')
                        continue;

                    if ($last_val_inf['min_ago'] > $r->calculation_minutes)
                        continue;

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
            }
            else if ($alert_on_no_vals && $last_value_count == 0)
            {
                $evaluation_count = $r->alert_on_occurences;

                for ($i=0; $i < $evaluation_count; $i++) 
                { 
                    $alert_values[] = 0; // alert on 0 value count
                }
            }

            // check if alert should be made
            if ($evaluation_count >= $r->alert_on_occurences)
            {
                // check if same alert was created at last alert of this alert_rule
                $alert_counter = 1;  // # of occurrences in a row
                $a             = null;
                $check_alert   = null;
                $check_date    = $r->last_calculated_at;
                if ($check_date)
                    $check_alert = $d->alerts()->where('user_id', $u->id)->where('alert_rule_id', $r->id)->where('device_id', $d->id)->where('updated_at', '>=', $check_date)->first();
                
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
                        
                    if ($value_diff_new > $value_diff_old_max) // update the existing alert with new (higher diff) value and trigger e-mail
                    {
                        $check_alert->alert_value = implode(', ', $alert_values);
                        $a             = $check_alert;
                        $alert_comp    = $r->comparator == '=' ? '==' : '!=';
                        $diff_comp     = ', Thr='.$r->threshold_value.' diff_new='.$value_diff_new.' > diff_old='.$value_diff_old_max;
                        Log::debug($debug_start.' Update Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_comp.' v_last='.$check_alert->alert_value.$diff_comp.', from: '.$last_val_inf['from']);
                    }
                    else
                    {
                        if ($value_diff_new == $value_diff_old_max)
                        {
                            $alert_compv= '==';
                            $alert_comp = '==';
                            $diff_comp  = '';
                        }
                        else
                        {
                            $alert_compv= '!=';
                            $alert_comp = '<';
                            $diff_comp  = ', Thr='.$r->threshold_value.' diff_new='.$value_diff_new.' '.$alert_comp.' diff_old='.$value_diff_old_max;
                        }

                        Log::debug($debug_start.' Maintain Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_compv.' v_last='.$check_alert->alert_value.$diff_comp.', from: '.$last_val_inf['from']);
                    }
                    $check_alert->save();
                }
                else // no previous alerts, so create
                {
                    $alert_value = implode(', ', $alert_values);
                    $alert_func  = $r->readableFunction();

                    Log::debug($debug_start.' Create new Alert, v='.$alert_value.', eval_count='.$evaluation_count.' alert_count='.$alert_counter.' f='.$alert_func.', from: '.$last_val_inf['from']);

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
                }
                
                $alert_count++;


                if ($a && $r->alert_via_email)
                {
                    Log::debug($debug_start.' Updated or created Alert, sending email to '.$u->email);
                    Mail::to($u->email)->send(new AlertMail($a, $u->name));
                }
            }
            else
            {
                Log::debug($debug_start.' evaluation_count='.$evaluation_count.' (< '.$r->alert_on_occurences.'), from: '.$last_val_inf['from'].', last_values='.json_encode($last_values));
            }
        }
        else
        {
            Log::debug($debug_start.' last_value_count=0, from: '.$last_val_inf['from'].', query: '.$last_val_inf['query']);
        }

        return $alert_count;
    }

    public function parseRule($device_id=null, $data_array=null)
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
        $r = $this;

        $parse_min   = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));
        $m_abbr      = $r->measurement->abbreviation;
        $debug_start = '|- R='.$r->id.' U='.$r->user_id.' ';

        // exclude parsing of rules
        $min_ago           = 0;
        $last_evaluated_at = $r->last_evaluated_at;

        if (isset($last_evaluated_at))
        {
            $now = new Moment(); // UTC
            $min_ago = -1 * round($now->from($last_evaluated_at)->getMinutes()); // round to whole value
            if ($min_ago < $r->calculation_minutes) // do not parse too often
            {
                //Log::debug($debug_start.' Not evaluated: last evaluated '.$min_ago.' min ago (< calc_min='.$r->calculation_minutes.')');
                return ['id'=>$r->id,'rules'=>0,'calc'=>0,'msg'=>'too_soon'];
            }
        }

        $now_local = new Moment('now', $r->timezone);  // Timezone of user that set alert rule (default: Europe/Amsterdam)
        $now_month = $now_local->getMonth();
    
        if (isset($r->exclude_months) && in_array($now_month, $r->exclude_months))
        {
            //Log::debug($debug_start.' Not evaluated: current month ('.$now_month.') in exclude_months='.implode(',',$r->exclude_months));
            return ['id'=>$r->id,'rules'=>0,'calc'=>0,'msg'=>'excl_month'];
        }

        $now_hour  = $now_local->getHour();

        if (isset($r->exclude_hours) && in_array($now_hour, $r->exclude_hours))
        {
            //Log::debug($debug_start.' Not evaluated: current hour ('.$now_hour.') in exclude_hours='.implode(',',$r->exclude_hours));
            return ['id'=>$r->id,'rules'=>0,'calc'=>0,'msg'=>'excl_hour'];
        }

        // check if user (still) exists
        $user_id     = $r->user_id;
        $user        = User::find($user_id);
        if (!isset($user))
        {
            if ($r->default_rule == false)
            {
                $alerts = Alert::where('alert_rule_id', $r->id);
                Log::debug($debug_start.' Not evaluated: user not found, so deleting '.$alerts->count().' alerts and rule: '.$r->name);
                $alerts->delete();
                $r->delete();
            }
            return ['id'=>$r->id,'rules'=>0,'calc'=>0,'msg'=>'no_user'];
        }

        // Evaluate rule
        $alert_rule_calc_date = date('Y-m-d H:i:s'); // PGe 2021-09-17: was local, now UTC (since config/app.php has UTC as default timezone)
        $r->last_evaluated_at = $alert_rule_calc_date; // update also if no devices, to not evaluate all the time
        $r->save();

        // define calculation per user device
        $min_msg_date = '2019-01-01 00:00:00';
        if ($r->calculation != 'cnt')
            $min_msg_date = date('Y-m-d H:i:s', time()-(60*max($parse_min, $r->calculation_minutes))); // at least 

        $all_user_devices = $user->allDevices()->where('hive_id', '!=', null)->whereNotIn('hive_id', $r->exclude_hive_ids);

        if ($device_id == null) // default all devices
            $user_devices = $all_user_devices->where('last_message_received', '>=', $min_msg_date)->get();
        else
        {
            //die(print_r(['d'=>$data_array,'r'=>$r->toArray()]));
            if (isset($data_array) && isset($data_array[$m_abbr])) // new last_message_received not yet saved
                $user_devices = $all_user_devices->where('id', $device_id)->get();
            else
                $user_devices = $all_user_devices->where('last_message_received', '>=', $min_msg_date)->where('id', $device_id)->get();
        }

        $calculated = 0;
        if (count($user_devices) > 0)
        {
            Log::debug($debug_start.' ('.$r->readableFunction().' @ '.$r->alert_on_occurences.'x '.$r->calculation_minutes.'min) last evaluated @ '.$last_evaluated_at.' ('.$min_ago.' min ago), devices='.count($user_devices).' (with hives, and msg received > '.$min_msg_date.')');

            foreach ($user_devices as $device) 
                $calculated += $r->evaluateDeviceRuleAlerts($device, $user, $alert_rule_calc_date, $data_array);
        }
        else
        {
            return ['id'=>$r->id,'rules'=>1,'calc'=>0,'msg'=>'no device ('.$device_id.') with hive and msg >= '.$min_msg_date];
        }

        // save last evaluated date
        if ($calculated > 0)
        {
            $r->last_calculated_at = $alert_rule_calc_date;
            $r->save();
        }

        return ['id'=>$r->id,'rules'=>1,'calc'=>$calculated,'msg'=>'ok'];
               
    }

    public static function parseUserDeviceDirectAlertRules($rule_ids, $device_id=null, $data_array=null)
    {
        $alertCount = 0;
        $ruleCount  = 0;
        $min_ago_5  = date('Y-m-d H:i:s', time()-59); // a bit less than 1 min ago
        $parse_min  = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));

        $alertRules = AlertRule::whereIn('id', $rule_ids)
                        ->where('active', 1)
                        ->where('default_rule', 0)
                        ->where('alert_on_occurences', '=', 1)
                        ->where('calculation_minutes', '<', $parse_min) // only parse alerts that are set to parsing 'at time of device data' (i.e. calculation_minutes == 0)
                        ->where(function($query) use ($min_ago_5) {  
                            $query->where('last_evaluated_at','<=', $min_ago_5)
                            ->orWhereNull('last_evaluated_at'); 
                        })
                        ->orderBy('last_evaluated_at')
                        ->get();

        Log::debug('Parsing D='.$device_id.' direct='.count($alertRules).' active alert rules last evaluated before '.$min_ago_5);
        //die(print_r(['$user_id'=>$user_id,'$parse_min'=>$parse_min,'ar'=>$alertRules->toArray()]));
        foreach ($alertRules as $r) 
        {
            $parsed = $r->parseRule($device_id, $data_array); // returns ['rules'=>0,'calc'=>0,'msg'=>'no_user'];
            $ruleCount  += $parsed['rules'];
            $alertCount += $parsed['calc'];
            Log::debug(' |- '.json_encode($parsed));
        }
        if ($alertCount > 0)
            Log::debug('|=> Parsed direct active rules='.$ruleCount.', created/updated alerts='.$alertCount);

        return $alertCount;
    }

    // Parse all active alert rules by cron job. Only evaluate rules with calculation_minutes > PARSE_ALERT_RULES_EVERY_X_MIN
    public static function parseRules()
    {
        $alertCount = 0;
        $now        = new Moment(); // UTC
        $now_min    = $now->getMinute();
        $parse_min  = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));

        if ($now_min % $parse_min == 0)
        {
            $ruleCount  = 0;
            $min_ago_15 = date('Y-m-d H:i:s', time()-890); // a bit less than 15 min ago

            $alertRules = AlertRule::where('active', 1)
                            ->where('default_rule', 0)
                            ->where('user_id', '!=', null)
                            ->where('alert_on_occurences', '>', 1)
                            ->where('calculation_minutes', '>=', $parse_min) // do not parse alerts that are set to parsing 'at time of device data' (i.e. calculation_minutes == 0)
                            ->where(function($query) use ($min_ago_15) {  
                                $query->where('last_evaluated_at','<=', $min_ago_15)
                                ->orWhereNull('last_evaluated_at'); 
                            })
                            ->orderBy('user_id')
                            ->orderBy('last_evaluated_at')
                            ->get();

            Log::debug('Parsing '.count($alertRules).' active alert rules last evaluated before '.$min_ago_15);

            foreach ($alertRules as $r) 
            {
                $parsed = $r->parseRule(); // returns ['rules'=>0,'calc'=>0,'msg'=>'no_user'];
                $ruleCount  += $parsed['rules'];
                $alertCount += $parsed['calc'];
            }
            if ($alertCount > 0)
                Log::debug('|=> Parsed active rules='.$ruleCount.', created/updated alerts='.$alertCount);
        }

        return $alertCount;
    }
}
