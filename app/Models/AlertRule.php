<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Measurement;
use App\User;
use App\Device;
use App\Models\Alert;
use Moment\Moment;
use Mail;
use Cache;
use Translation;
use App\Mail\AlertMail;
use App\Models\AlertRuleFormula;
use LaravelLocalization;

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
    protected $fillable           = ['name', 'description', 'measurement_id', 'calculation', 'calculation_minutes', 'comparator', 'comparison', 'threshold_value', 'exclude_months', 'exclude_hours', 'exclude_hive_ids', 'alert_via_email', 'webhook_url', 'active', 'user_id', 'default_rule', 'timezone', 'alert_on_occurences', 'last_calculated_at', 'last_evaluated_at'];
    protected $hidden             = ['last_calculated_at','alert_rule_formulas'];
    protected $appends            = ['formulas','no_value'];

    public static $calculations   = ["min"=>"Minimum", "max"=>"Maximum", "ave"=>"Average", "cnt"=>"Count"]; // exclude "der"=>"Derivative" for the moment (because of user interpretation complexity)
    public static $influx_calc    = ["min"=>"MIN", "max"=>"MAX", "ave"=>"MEAN", "der"=>"DERIVATIVE", "cnt"=>"COUNT"];
    
    public static $comparators    = ["="=>"equal_to", "<"=>"less_than", ">"=>"greater_than", "<="=>"less_than_or_equal", ">="=>"greater_than_or_equal"];
    public static $comparisons    = ["val"=>"Value", "inc"=>"Increase", "dec"=>"Decrease", "abs_dif"=>"Absolute_value_of_dif"]; // excluse "abs"=>"Absolute_value","dif"=>"Difference" because it has no usecase
    public static $exclude_months = [1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dec"];
    public static $exclude_hours  = [0=>"0:00 - 0:59",1=>"1:00 - 1:59",2=>"2:00 - 2:59",3=>"3:00 - 3:59",4=>"4:00 - 4:59",5=>"5:00 - 5:59",6=>"6:00 - 6:59",7=>"7:00 - 7:59",8=>"8:00 - 8:59",9=>"9:00 - 9:59",10=>"10:00 - 10:59",11=>"11:00 - 11:59",12=>"12:00 - 12:59",13=>"13:00 - 13:59",14=>"14:00 - 14:59",15=>"15:00 - 15:59",16=>"16:00 - 16:59",17=>"17:00 - 17:59",18=>"18:00 - 18:59",19=>"19:00 - 19:59",20=>"20:00 - 20:59",21=>"21:00 - 21:59",22=>"22:00 - 22:59",23=>"23:00 - 23:59"];

    public static $calc_minutes   = [0, 60, 180, 360, 720, 1440, 2880, 10080];

    public static function boot()
    {
        parent::boot();

        static::created(function($r)
        {
            $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>'alert_rule_created', 'alert_value'=>$r->readableFunction(), 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
            $a->save();
        });

        // static::updated(function($r)
        // {
        //     $alert_func = $r->measurement->pq.' '.$r->comparator.' '.$r->threshold_value.' '.$r->measurement->unit;
        //     $activated  = $r->active ? 'activated' : 'deactivated';
        //     $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>$r->readableFunction(), 'alert_value'=>'AlertRule updated and '.$activated, 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
        //     $a->save();
        // });

        // static::deleting(function($r)
        // {
        //     $a = new Alert(['alert_rule_id'=>$r->id, 'alert_function'=>$r->readableFunction(), 'alert_value'=>'alert_rule_deleted', 'measurement_id'=>$r->measurement_id, 'user_id'=>$r->user_id]);
        //     $a->save();
        // });
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

    public function getNoValueAttribute()
    {
        if ($this->threshold_value == 0 && !empty($this->attributes['calculation']) && $this->attributes['calculation'] == 'cnt')
        {
            return true;
        }
        else if ($this->formulas->count() > 0)
        {
            $f = $this->formulas->first();
            if ($f->threshold_value == 0 && $f->calculation == 'cnt')
                return true;
        }
        return false;
    }

    public function getFormulasAttribute()
    {
        return $this->alert_rule_formulas;
    }

    public function getUnit()
    {
        return $this->calculation == 'cnt' || $this->calculation == 'der' || $this->measurement->unit == '-' ? '' : ''.$this->measurement->unit;
    }

    public function getPq($abbr=false)
    {
        $pq = null;
        
        if (isset($this->measurement_id) && isset($this->measurement->physical_quantity))
            $pq = $abbr ? $this->measurement->physical_quantity->abbreviation : $this->measurement->pq;

        if ($pq)
        {
            $loc = LaravelLocalization::getCurrentLocale();
            return Cache::remember('ar-'.$this->id.'-pq-name-'.$loc.'-'.$pq, env('CACHE_TIMEOUT_LONG'), function () use ($loc, $pq) 
                {
                    if ($this->calculation == 'der' || !isset($pq))
                        return '';
                    elseif (isset($pq))
                        return $pq;

                    return '';
                });
            }
        else
        {
            return '';
        }
    }

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function alert_rule_formulas()
    {
        return $this->hasMany(AlertRuleFormula::class);
    }
    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
    
    public static function selectList()
    {
        return self::orderBy('name')->pluck('name','id');
    }

    public function remove_hive_id_from_exclude_hive_ids($hive_id)
    {

        $hive_ids_array = $this->exclude_hive_ids;
        $array_index    = array_search($hive_id, $hive_ids_array);
        
        if ($array_index !== false)
        {
            array_splice($hive_ids_array, $array_index, 1);
            $this->exclude_hive_ids = implode(",", $hive_ids_array);
            $this->save();
        }
    }



    public static function cacheRequestRate($name, $amount=1)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count', $amount);
        else
            Cache::put($name.'-count', $amount);

    }

    public function readableFunction($short=false, $value=null)
    {
        $rule       = $this;
        
        $locales    = config('laravellocalization.supportedLocales');
        $locale     = isset($locales[$rule->user->locale]) ? $rule->user->locale : LaravelLocalization::getCurrentLocale();
        $pq_name    = isset($m_abbr) ? Translation::get($locale, $m_abbr, 'measurement') : $rule->getPq();
        $unit       = $rule->getUnit();
        $direct     = $rule->calculation_minutes == 0 ? true : false;
        $calc       = $direct ? '' : ''.__('beep.'.$rule->calculation).' ';                             // min / max / average / count
        $comparison = $rule->comparison == 'val' ? '' : ' '.__('beep.'.$rule->comparison);  // value (not shown) / increase / difference
        $cnt_zero   = $rule->calculation == 'cnt' && ($value === 0 || $rule->threshold_value == 0) ? true : false;
        $text_zero  = ucfirst(__('beep.cnt_zero')).' ';                                     // Absence of
        $calc_trans = $direct ? '' : ($cnt_zero ? $text_zero : $calc.$comparison);          // Absence of / average value

        // alert function with value
        if ($value != null) 
        {
            if ($cnt_zero)
                return ucfirst($calc_trans).$pq_name.__('beep.values');
            else
                return ucfirst($calc_trans).' '.$pq_name.' = '.$value.$unit."\n(".$rule->comparator.' '.$rule->threshold_value.$unit.')'; // value Temperature average value = 10.3°C
        }

        // alert function for logging
        if ($short)
            return $rule->getPq(true).' '.$calc.$rule->comparison.' '.$rule->comparator.' '.$rule->threshold_value.$unit;

        // alert function without value (for alert rule display)
        if ($cnt_zero)
            return ucfirst($calc_trans).$pq_name.__('beep.values'); // Absence of Temperature values
        else
            return $pq_name.' '.$calc_trans.__('beep.'.$rule->comparison).' '.$rule->comparator.' '.$rule->threshold_value.$unit; // Temperature average value = 10.3°C
    }

    public function evaluateDeviceRuleAlerts($device, $user, $alert_rule_calc_date, $data_array=null, $log_on=false)
    {
        $r = $this;
        $d = $device;
        $u = $user;

        $parse_min   = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));
        $debug_start = ' |- D='.$d->id.' ';

        if (!isset($d->hive_id) || in_array($d->hive_id, $r->exclude_hive_ids)) // only parse existing hives that are not excluded
        {
            if($log_on)
                Log::debug($debug_start.' No hive_id to evaluate, excluded hive_ids='.implode(',',$r->exclude_hive_ids));
            return 0;
        }

        if (!isset(self::$influx_calc[$r->calculation]))
        {
            if($log_on)
                Log::debug($debug_start.' Undefined calculation: '.$r->calculation);
            return 0;
        }
        $diff_comp   = $r->comparison == 'dif' || $r->comparison == 'abs_dif' || $r->comparison == 'inc' || $r->comparison == 'dec' ? true : false;
        $round_decim = $diff_comp ? 2 : 1;    // round to 2 decimals for difference comparison, because difference can be 0 too sooon for 1 decimal (temperature sensor of e.g. brood), otherwise to 1 decimal
        $m_abbr      = $r->measurement->abbreviation;
        $influx_func = self::$influx_calc[$r->calculation];
        $limit       = $diff_comp ? $r->alert_on_occurences + 1 : $r->alert_on_occurences; // one extra for diff calculation
        $direct_data = isset($data_array) && count($data_array) > 0 && isset($data_array[0][$m_abbr]) ? true : false;

        if ($direct_data)
            $last_val_inf= ['values'=>$data_array, 'query'=>'', 'from'=>'measurement', 'min_ago'=>0];
        else
            $last_val_inf= $d->getAlertSensorValues($m_abbr, $influx_func, $r->calculation_minutes, $limit); // provides: ['values'=>$values,'query'=>$query, 'from'=>'cache', 'min_ago'=>$val_min_ago]


        $last_values      = $last_val_inf['values'];
        $alert_count      = 0;
        $evaluation_count = 0;
        $alert_function   = '';
        $alert_values     = [];
        $last_values_data = [];
        $last_value_count = $diff_comp ? count($last_values) - 1 : min($r->alert_on_occurences, count($last_values));
        $alert_on_no_vals = $r->calculation == 'cnt' && $r->threshold_value == 0 ? true : false;
        
        if ($last_val_inf['min_ago'] > $r->calculation_minutes && $alert_on_no_vals == false)
        {
            if($log_on)
                Log::debug($debug_start.' Not evaluating, data from '.$last_val_inf['min_ago'].'m ago is longer ago than '.$r->calculation_minutes.'m (calc min)');
            return 0;
        }

        if ($last_value_count > 0 || $alert_on_no_vals)
        {
            //Log::debug(json_encode(['r'=>$r->name, 'd'=>$d->name, 'lv'=>$last_values, 'mve'=>$last_value_count]));

            // evaluate measurement values
            if ($last_value_count > 0)
            {
                for ($i=0; $i < $last_value_count; $i++) // start with most recent value (ordered desc on time)
                {  
                    if (!isset($last_values[$i][$m_abbr]))
                    {
                        continue;
                    }
                    else
                    {
                        $value = $last_values[$i][$m_abbr];
                        // Save last calc values for reference
                        $key   = $m_abbr.'_'.$i;
                        if (isset($last_values[$i]['time']))
                            $key = $last_values[$i]['time'];
                    
                        $last_values_data["$key"] = $value;
                        // Continue if unset
                        if (!isset($value) || $value == '' || $value == 'null')
                            continue;
                    }
                    
                    $value_prev = null;
                    if ($i+1 < count($last_values) && isset($last_values[$i+1][$m_abbr]))
                    {
                        $value_prev = $last_values[$i+1][$m_abbr];
                        // Save last calc values for reference
                        if ($diff_comp && $i == $last_value_count-1)
                        {
                            //die(print_r([$i,$m_abbr]));
                            $key = $m_abbr.'_'.($i+1);
                            if (isset($last_values[$i+1]['time']))
                                $key = $last_values[$i+1]['time'];

                            $last_values_data["$key"] = $value_prev;
                        }
                        // Continue if unset
                        if (!isset($value_prev) || $value_prev == '' || $value_prev == 'null')
                            continue;
                    }
 

                    // Calculate value for rule
                    $calc = null;
                    if ($diff_comp)
                    {
                        switch($r->comparison)
                        {
                            case 'inc':
                            case 'dif':
                                $calc = $value - $value_prev;
                                break;
                            case 'dec':
                                $calc = $value_prev - $value;
                                break;
                            case 'abs_dif':
                                $calc = abs($value - $value_prev);
                                break;
                        }
                    }
                    else
                    {
                        $calc = $value;
                    }

                    if (is_nan($calc))
                        continue;

                    
                    $calc  = round($calc, $round_decim); // round, just like the threshold_value
                    $thres = round($r->threshold_value, $round_decim);
                    
                    $evaluation = false;
                    switch($r->comparator)
                    {
                        case '=':
                            $evaluation = $calc == $thres ? true : false;
                            break;
                        case '<':
                            $evaluation = $calc < $thres ? true : false;
                            break; 
                        case '>':
                            $evaluation = $calc > $thres ? true : false;
                            break; 
                        case '<=':
                            $evaluation = $calc <= $thres ? true : false;
                            break; 
                        case '>=':
                            $evaluation = $calc >= $thres ? true : false;
                            break; 
                    }
                    if ($evaluation)
                    {
                        $evaluation_count++;
                        $alert_values[] = $calc;
                    }
                }
            }
            else if ($alert_on_no_vals && $last_value_count == 0)
            {
                $evaluation_count = $r->alert_on_occurences;

                for ($i=0; $i < $evaluation_count; $i++) 
                { 
                    $alert_values[]     = 0; // alert on 0 value count
                    $last_values_data[] = 0;
                }
            }

            // check if alert should be made
            if ($evaluation_count >= $r->alert_on_occurences)
            {
                // check if same alert was created at last alert of this alert_rule
                $alert_counter = 1;  // # of occurrences in a row
                $a             = null;
                $check_alert   = null;
                $device_min    = $d->getRefreshMin()*2; // do not create new alert on every 1 missing value
                $alerts_min_max= max($r->calculation_minutes, $parse_min, $device_min)+1; // the higher, the earlier the $alerts_after, the more alerts are matching
                $alerts_after    = date('Y-m-d H:i:s', time()-60*$alerts_min_max); // a bit less than alerts_min_max ago
                //die(print_r(['am'=>$alerts_min_max, 'cm'=>$r->calculation_minutes, 'pm'=>$parse_min, 'dm'=>$device_min]));
                if ($alerts_after)
                    $check_alert = $d->alerts()->where('user_id', $u->id)->where('alert_rule_id', $r->id)->where('device_id', $d->id)->where('updated_at', '>=', $alerts_after)->orderBy('updated_at', 'desc')->first();
                
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
                        $check_alert->alert_value    = implode(', ', $alert_values);
                        $check_alert->alert_function = $r->readableFunction(false, $check_alert->alert_value);
                        $a             = $check_alert;
                        
                        if($log_on)
                        {
                            $alert_comp    = $r->comparator == '=' ? '==' : '!=';
                            $diff_expla    = ', Thr='.$r->threshold_value.' diff_new='.$value_diff_new.' > diff_old='.$value_diff_old_max;
                            Log::debug($debug_start.' Update Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_comp.' v_last='.$check_alert->alert_value.$diff_expla.', from: '.$last_val_inf['from']);
                        }
                    }
                    else // only update count of existing alert
                    {
                        $check_alert->alert_function = $r->readableFunction(false, $check_alert->alert_value);

                        if($log_on)
                        {
                            if ($value_diff_new == $value_diff_old_max)
                            {
                                $alert_compv= '==';
                                $alert_comp = '==';
                                $diff_expla = '';
                            }
                            else
                            {
                                $alert_compv= '!=';
                                $alert_comp = '<';
                                $diff_expla = ', Thr='.$r->threshold_value.' diff_new='.$value_diff_new.' '.$alert_comp.' diff_old='.$value_diff_old_max;
                            }

                            Log::debug($debug_start.' Maintain Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_compv.' v_last='.$check_alert->alert_value.$diff_expla.', from: '.$last_val_inf['from']);
                        }
                    }
                    $check_alert->save();
                }
                else // no previous alerts, so create
                {
                    $alert_value = implode(', ', $alert_values);
                    $alert_func  = $r->readableFunction(false, $alert_value);

                    if($log_on)
                        Log::debug($debug_start.' Create new Alert, v='.$alert_value.', eval_count='.$evaluation_count.' alert_count='.$alert_counter.' f='.$r->readableFunction(true).', from: '.$last_val_inf['from']);

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
                    $last_values_string = null;
                    if ($diff_comp)
                    {
                        $last_values_data = array_reverse($last_values_data); // for 
                        $last_values_array= [];
                        foreach ($last_values_data as $key => $value) 
                        {
                            $last_values_array[] = round($value, $round_decim);
                        }
                        $last_values_string = implode(' -> ',$last_values_array).$r->getUnit();
                    }

                    // Set locale for date
                    $created_date       = gettype($a->created_at) == 'string' ? $a->created_at : $a->created_at->toDateTimeString();
                    $created_date_local = new Moment($created_date, 'UTC');
                    $locale_array       = config('laravellocalization.supportedLocales');
                    $locale_identifier  = isset($locale_array[$u->locale]) ? $locale_array[$u->locale]['regional'] : null;

                    if ($locale_identifier)
                        \Moment\Moment::setLocale($locale_identifier);

                    $display_date_local = $created_date_local->setTimezone($r->timezone)->format('LLLL', new \Moment\CustomFormats\MomentJs());
                    
                    if ($locale_identifier) // reset global locale
                        \Moment\Moment::setLocale('en_GB');
                    
                    if($log_on)
                        Log::debug($debug_start.' Updated or created Alert, sending email to '.$u->email);

                    Mail::to($u->email)->send(new AlertMail($a, $u->name, $last_values_string, $display_date_local));
                }
            }
            else if($log_on)
            {
                Log::debug($debug_start.' evaluation_count='.$evaluation_count.'x (< '.$r->alert_on_occurences.'x), from: '.$last_val_inf['from'].', last_values='.json_encode($last_values_data));
            }
        }
        else if($log_on)
        {
            Log::debug($debug_start.' last_value_count=0, from: '.$last_val_inf['from'].', query: '.$last_val_inf['query']);
        }

        return $alert_count;
    }

    public function parseRule($device_id=null, $data_array=null, $log_on=false)
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
        $direct_data = isset($data_array) && count($data_array) > 0 && isset($data_array[0][$m_abbr]) ? true : false;
        
        // exclude parsing of rules
        $min_ago           = 0;
        $last_evaluated_at = $r->last_evaluated_at;
        $check_min         = $direct_data ? 1 : max($r->calculation_minutes, $parse_min);

        if (isset($last_evaluated_at))
        {
            $now       = new Moment(); // UTC
            $min_ago   = -1 * round($now->from($last_evaluated_at)->getMinutes()); // round to whole value
        }

        if($log_on)
            Log::debug($debug_start.'direct_data='.($direct_data?'1':'0').' ('.$r->readableFunction(true).' @ '.$r->alert_on_occurences.'x'.$r->calculation_minutes.'m) last eval '.$min_ago.'m ago (check_min='.$check_min.') @ '.$last_evaluated_at);
        
        if ($min_ago > 0)
        {
            if ($min_ago < $check_min) // do not parse too often
            {
                //Log::debug($debug_start.' Not evaluated: last evaluated '.$min_ago.' min ago (< calc_min='.$r->calculation_minutes.')');
                return ['eval'=>0,'calc'=>0,'msg'=>'too_soon: min_ago='.$min_ago.' check_min='.$check_min];
            }
        }


        $now_local = new Moment('now', $r->timezone);  // Timezone of user that set alert rule (default: Europe/Amsterdam)
        $now_month = $now_local->getMonth();
    
        if (isset($r->exclude_months) && in_array($now_month, $r->exclude_months))
        {
            //Log::debug($debug_start.' Not evaluated: current month ('.$now_month.') in exclude_months='.implode(',',$r->exclude_months));
            return ['eval'=>0,'calc'=>0,'msg'=>'excl_month='.$now_month];
        }

        $now_hour  = $now_local->getHour();

        if (isset($r->exclude_hours) && in_array($now_hour, $r->exclude_hours))
        {
            //Log::debug($debug_start.' Not evaluated: current hour ('.$now_hour.') in exclude_hours='.implode(',',$r->exclude_hours));
            return ['eval'=>0,'calc'=>0,'msg'=>'excl_hour='.$now_hour];
        }

        // check if user (still) exists
        $user_id     = $r->user_id;
        $user        = User::find($user_id);
        if (!isset($user))
        {
            if ($r->default_rule == false)
            {
                $alerts = Alert::where('alert_rule_id', $r->id);
                Log::warning($debug_start.' Not evaluated: user not found, so deleting '.$alerts->count().' alerts and rule: '.$r->name);
                $alerts->delete();
                $r->delete();
            }
            return ['eval'=>0,'calc'=>0,'msg'=>'no_user'];
        } 
        else
        {
            if (isset($user->locale))
                App::setLocale($user->locale); // make sure all (blade)translations are in users language
        }

        // Evaluate rule
        $alert_rule_calc_date = date('Y-m-d H:i:s'); // PGe 2021-09-17: was local, now UTC (since config/app.php has UTC as default timezone)
        $r->last_evaluated_at = $alert_rule_calc_date; // update also if no devices, to not evaluate all the time
        $r->save();

        // define calculation per user device
        $min_msg_date = '2019-01-01 00:00:00';
        if ($r->calculation != 'cnt')
            $min_msg_date = date('Y-m-d H:i:s', time()-(60*max($parse_min, $r->calculation_minutes))); // at least 

        $all_user_devices = $user->allDevices()->where('hive_id', '!=', null)->whereNotIn('hive_id', $r->exclude_hive_ids)->get();

        if ($device_id == null) // default all devices
        {
            $user_devices = $all_user_devices->where('last_message_received', '>=', $min_msg_date);
        }
        else
        {
            //die(print_r(['d'=>$data_array,'r'=>$r->toArray()]));
            if ($direct_data) // new last_message_received not yet saved
                $user_devices = $all_user_devices->where('id', $device_id);
            else
                $user_devices = $all_user_devices->where('last_message_received', '>=', $min_msg_date)->where('id', $device_id);
        }

        $calculated = 0;
        if (count($user_devices) > 0)
        {
            foreach ($user_devices as $device) 
                $calculated += $r->evaluateDeviceRuleAlerts($device, $user, $alert_rule_calc_date, $data_array, $log_on);
        }
        else
        {
            return ['eval'=>1,'calc'=>0,'msg'=>'no device ('.$device_id.' data='.$direct_data.') with hive and msg >= '.$min_msg_date];
        }

        // save last evaluated date
        if ($calculated > 0)
        {
            $r->last_calculated_at = $alert_rule_calc_date;
            $r->save();
        }

        return ['eval'=>1,'calc'=>$calculated,'msg'=>'ok'];
               
    }

    public static function parseUserDeviceDirectAlertRules($rule_ids, $device_id=null, $data_array=null)
    {
        $alertCount = 0;
        $evalCount  = 0;
        $min_ago_e  = date('Y-m-d H:i:s', time()-59); // a bit less than 1 min ago
        $parse_min  = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));
        $log_on     = env('LOG_ALERT_RULE_PARSING', false);

        $alertRules = self::whereIn('id', $rule_ids)
                        ->where('active', 1)
                        ->where('default_rule', 0)
                        ->where('alert_on_occurences', '=', 1)
                        ->where('calculation_minutes', '<', $parse_min) // only parse alerts that are set to parsing 'at time of device data' (i.e. calculation_minutes == 0)
                        ->where(function($query) use ($min_ago_e) {  
                            $query->where('last_evaluated_at','<=', $min_ago_e)
                            ->orWhereNull('last_evaluated_at'); 
                        })
                        ->orderBy('last_evaluated_at')
                        ->get();

        if ($log_on)
            Log::info('Evaluating (D='.$device_id.') '.count($alertRules).' direct alert rules last evaluated before '.$min_ago_e.' (59s ago)');
        //die(print_r(['$user_id'=>$user_id,'$parse_min'=>$parse_min,'ar'=>$alertRules->toArray()]));
        $m_abbr_no_data = [];
        foreach ($alertRules as $r) 
        {
            $debug_start = '|- R='.$r->id.' U='.$r->user_id.' ';
            $m_abbr      = $r->measurement->abbreviation;
            $direct_data = isset($data_array) && count($data_array) > 0 && isset($data_array[0][$m_abbr]) ? true : false;

            if ($direct_data)
            {
                $parsed = $r->parseRule($device_id, $data_array, $log_on); // returns ['eval'=>0,'calc'=>0,'msg'=>'no_user'];
                $alertCount += $parsed['calc'];
                //$evalCount  += $parsed['eval'];
                // if ($parsed['eval'] == 0)
                //     Log::debug('  |- '.$parsed['msg']);
            }
            else if ($log_on)
            {
                Log::debug($debug_start.'direct_data=0 ('.$r->readableFunction(true).') No data available for: '.$m_abbr);
            }
        }
        // if ($alertCount > 0)
        //     Log::debug('|=> Evaluated direct rules='.$evalCount.', created/updated alerts='.$alertCount);

        return $alertCount;
    }

    // Parse all active alert rules by cron job. Only evaluate rules with calculation_minutes > PARSE_ALERT_RULES_EVERY_X_MIN
    public static function parseRules()
    {
        $alertCount = 0;
        $now        = new Moment(); // UTC
        $now_min    = $now->getMinute();
        $parse_min  = min(60, env('PARSE_ALERT_RULES_EVERY_X_MIN', 15));
        $log_on     = env('LOG_ALERT_RULE_PARSING', false);

        if ($now_min % $parse_min == 0)
        {
            $evalCount  = 0;
            $min_ago_e  = date('Y-m-d H:i:s', time()-(59*$parse_min)); // a bit less than 15 min ago

            $alertRules = self::where('active', 1)
                            ->where('default_rule', 0)
                            ->where('user_id', '!=', null)
                            ->where('calculation_minutes', '>=', $parse_min) // do not parse alerts that are set to parsing 'at time of device data' (i.e. calculation_minutes == 0)
                            ->where(function($query) use ($min_ago_e) {  
                                $query->where('last_evaluated_at','<=', $min_ago_e)
                                ->orWhereNull('last_evaluated_at'); 
                            })
                            ->orderBy('user_id')
                            ->orderBy('last_evaluated_at')
                            ->get();

            if ($log_on)
                Log::info('Evaluating '.count($alertRules).' active alert rules last evaluated before '.$min_ago_e.' ('.$parse_min.'m ago)');

            foreach ($alertRules as $r) 
            {
                $parsed = $r->parseRule(null, null, $log_on); // returns ['eval'=>0,'calc'=>0,'msg'=>'no_user'];
                $alertCount += $parsed['calc'];
                // $evalCount  += $parsed['eval'];
            }
            // if ($alertCount > 0)
            //     Log::debug('|=> Evaluated rules='.$evalCount.', created/updated alerts='.$alertCount);
        }
        self::cacheRequestRate('alert-timed', $alertCount);
        return $alertCount;
    }
}
