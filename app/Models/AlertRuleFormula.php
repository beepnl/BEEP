<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Measurement;
use Translation;
use App\Models\AlertRule;
use LaravelLocalization;
use Cache;

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
    //protected $appends            = ['calculation_minutes']; // CAUSES LOOP BY append of alert_rule->formulas

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

    public function user()
    {
        return $this->alert_rule->user();
    }

    public function user_devices($include_shared=true)
    {
        if ($include_shared)
            return $this->user->allDevices()->get();

        return $this->user->devices;

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

    public function getPq($abbr=false)
    {
        $loc = LaravelLocalization::getCurrentLocale();
        $pq  = '';

        if (isset($this->measurement_id) && isset($this->measurement->physical_quantity))
        { 
            $pq = $this->measurement->physical_quantity;
            return Cache::remember('ar-'.$this->id.'-pq-name-'.$loc, env('CACHE_TIMEOUT_LONG'), function () use ($loc, $pq, $abbr) 
            {
                if ($this->calculation == 'der' || !isset($pq))
                {
                    return '';
                }
                elseif (isset($pq))
                {
                    if ($abbr)
                        return $pq->abbreviation;
                    else
                        return $pq->transName();
                }

                return '';
            });
        }
        return '';
    }

    public function readableFunction($short=false, $value=null)
    {
        $arf        = $this;
        $locales    = config('laravellocalization.supportedLocales');
        $locale     = isset($locales[$arf->user->locale]) ? $arf->user->locale : LaravelLocalization::getCurrentLocale();
        
        $pq_name    = isset($m_abbr) ? Translation::get($locale, $m_abbr, 'measurement') : $arf->getPq();
        $unit       = $arf->getUnit();
        $direct     = $arf->calculation_minutes == 0 ? true : false;
        $calc       = $direct ? '' : ''.__('beep.'.$arf->calculation).' ';                             // min / max / average / count
        $comparison = $arf->comparison == 'val' ? '' : ' '.__('beep.'.$arf->comparison);  // value (not shown) / increase / difference
        $cnt_zero   = $arf->calculation == 'cnt' && ($value === 0 || $arf->threshold_value == 0) ? true : false;
        $text_zero  = ucfirst(__('beep.cnt_zero')).' ';                                     // Absence of
        $calc_trans = $direct ? '' : ($cnt_zero ? $text_zero : $calc.$comparison);          // Absence of / average value

        // alert function with value
        if ($value != null) 
        {
            if ($cnt_zero)
                return ucfirst($calc_trans).$pq_name.__('beep.values');
            else
                return ucfirst($calc_trans).' '.$pq_name.' = '.$value.$unit."\n(".$arf->comparator.' '.$arf->threshold_value.$unit.')'; // value Temperature average value = 10.3°C
        }

        // alert function for logging
        if ($short)
            return $arf->getPq(true).' '.$calc.$arf->comparison.' '.$arf->comparator.' '.$arf->threshold_value.$unit;

        // alert function without value (for alert rule display)
        if ($cnt_zero)
            return ucfirst($calc_trans).$pq_name.__('beep.values'); // Absence of Temperature values

        return $pq_name.' '.$calc_trans.__('beep.'.$arf->comparison).' '.$arf->comparator.' '.$arf->threshold_value.$unit; // Temperature average value = 10.3°C
    }

    // for selected measurement, get data and evaluate formula on data
    public function evaluateFormula($data_array=null, $log_on=false)
    {
        $f = $this;

        // Check measurement source, if source != db_influx, only allow comparison == val

        $diff_comp   = $f->comparison == 'dif' || $f->comparison == 'abs_dif' || $f->comparison == 'inc' || $f->comparison == 'dec' ? true : false;
        $m_abbr      = $f->measurement->abbreviation;
        $influx_func = AlertRule::$influx_calc[$f->calculation];
        $limit       = $diff_comp ? $f->alert_on_occurences + 1 : $f->alert_on_occurences; // one extra for diff calculation
        $direct_data = isset($data_array) && count($data_array) > 0 && isset($data_array[0][$m_abbr]) ? true : false;

        if ($direct_data)
            $last_val_inf= ['values'=>$data_array, 'query'=>'', 'from'=>'measurement', 'min_ago'=>0];
        else
            $last_val_inf= $d->getAlertSensorValues($m_abbr, $influx_func, $f->calculation_minutes, $limit); // provides: ['values'=>$values,'query'=>$query, 'from'=>'cache', 'min_ago'=>$val_min_ago]

        if ($last_val_inf['min_ago'] > $f->calculation_minutes)
        {
            if($log_on)
                Log::debug($debug_start.' Not evaluating, data from '.$last_val_inf['min_ago'].'m ago is longer ago than '.$f->calculation_minutes.'m (calc min)');
            return 0;
        }

        $last_values      = $last_val_inf['values'];
        $alert_count      = 0;
        $evaluation_count = 0;
        $alert_function   = '';
        $alert_values     = [];
        $last_values_data = [];
        $last_value_count = $diff_comp ? count($last_values) - 1 : min($f->alert_on_occurences, count($last_values));
        $alert_on_no_vals = $f->calculation == 'cnt' && $f->threshold_value == 0 ? true : false;

        if ($last_value_count > 0 || $alert_on_no_vals)
        {
            //Log::debug(json_encode(['r'=>$f->name, 'd'=>$d->name, 'lv'=>$last_values, 'mve'=>$last_value_count]));

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
                        switch($f->comparison)
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

                    $calc  = round($calc, 1); // round to 1 decimal, just like the threshold_value
                    $thres = round($f->threshold_value, 1);
                    
                    $evaluation = false;
                    switch($f->comparator)
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
                $evaluation_count = $f->alert_on_occurences;

                for ($i=0; $i < $evaluation_count; $i++) 
                { 
                    $alert_values[]     = 0; // alert on 0 value count
                    $last_values_data[] = 0;
                }
            }

            // check if alert should be made
            if ($evaluation_count >= $f->alert_on_occurences)
            {
                // check if same alert was created at last alert of this alert_rule
                $alert_counter = 1;  // # of occurrences in a row
                $a             = null;
                $check_alert   = null;
                $device_min    = $d->getRefreshMin()*2; // do not create new alert on every 1 missing value
                $alerts_min_max= max($f->calculation_minutes, $parse_min, $device_min)+1; // the higher, the earlier the $alerts_after, the more alerts are matching
                $alerts_after    = date('Y-m-d H:i:s', time()-60*$alerts_min_max); // a bit less than alerts_min_max ago
                //die(print_r(['am'=>$alerts_min_max, 'cm'=>$f->calculation_minutes, 'pm'=>$parse_min, 'dm'=>$device_min]));
                if ($alerts_after)
                    $check_alert = $d->alerts()->where('user_id', $u->id)->where('alert_rule_id', $f->id)->where('device_id', $d->id)->where('updated_at', '>=', $alerts_after)->orderBy('updated_at', 'desc')->first();
                
                if ($check_alert) // check if user already has this alert, if so, update it if diff value is bigger
                {
                    $newest_alert_value = $alert_values[0];
                    $value_diff_new     = abs($newest_alert_value - $f->threshold_value);
                    $value_diff_old_max = 0;
                    
                    $old_alert_values = explode(', ', $check_alert->alert_value);
                    foreach ($old_alert_values as $v)
                        $value_diff_old_max = max($value_diff_old_max, abs($v - $f->threshold_value));

                    $alert_counter = $check_alert->count + 1;

                    // update attributes that could be changed over time
                    $check_alert->updated_at     = $alert_rule_calc_date;
                    $check_alert->alert_function = $f->readableFunction();
                    $check_alert->measurement_id = $f->measurement_id;
                    $check_alert->location_id    = $d->hive ? $d->hive->location_id : null;
                    $check_alert->location_name  = $d->location_name;
                    $check_alert->device_name    = $d->name;
                    $check_alert->hive_id        = $d->hive_id;
                    $check_alert->hive_name      = $d->hive_name;
                    $check_alert->count          = $alert_counter;
                        
                    if ($value_diff_new > $value_diff_old_max) // update the existing alert with new (higher diff) value and trigger e-mail
                    {
                        $check_alert->alert_value    = implode(', ', $alert_values);
                        $check_alert->alert_function = $f->readableFunction(false, $check_alert->alert_value);
                        $a             = $check_alert;
                        
                        if($log_on)
                        {
                            $alert_comp    = $f->comparator == '=' ? '==' : '!=';
                            $diff_expla    = ', Thr='.$f->threshold_value.' diff_new='.$value_diff_new.' > diff_old='.$value_diff_old_max;
                            Log::debug($debug_start.' Update Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_comp.' v_last='.$check_alert->alert_value.$diff_expla.', from: '.$last_val_inf['from']);
                        }
                    }
                    else // only update count of existing alert
                    {
                        $check_alert->alert_function = $f->readableFunction(false, $check_alert->alert_value);

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
                                $diff_expla = ', Thr='.$f->threshold_value.' diff_new='.$value_diff_new.' '.$alert_comp.' diff_old='.$value_diff_old_max;
                            }

                            Log::debug($debug_start.' Maintain Alert id='.$check_alert->id.' count='.$alert_counter.', v_new='.$newest_alert_value.' '.$alert_compv.' v_last='.$check_alert->alert_value.$diff_expla.', from: '.$last_val_inf['from']);
                        }
                    }
                    $check_alert->save();
                }
                else // no previous alerts, so create
                {
                    $alert_value = implode(', ', $alert_values);
                    $alert_func  = $f->readableFunction(false, $alert_value);

                    if($log_on)
                        Log::debug($debug_start.' Create new Alert, v='.$alert_value.', eval_count='.$evaluation_count.' alert_count='.$alert_counter.' f='.$f->readableFunction(true).', from: '.$last_val_inf['from']);

                    $a = new Alert();
                    $a->created_at     = $alert_rule_calc_date;
                    $a->updated_at     = $alert_rule_calc_date;
                    $a->alert_rule_id  = $f->id;
                    $a->alert_function = $alert_func;
                    $a->alert_value    = $alert_value;
                    $a->measurement_id = $f->measurement_id;
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


                if ($a && $f->alert_via_email)
                {
                    $last_values_string = null;
                    if ($diff_comp)
                    {
                        $last_values_data = array_reverse($last_values_data); // for 
                        $last_values_array= [];
                        foreach ($last_values_data as $key => $value) 
                        {
                            $last_values_array[] = round($value, 1);
                        }
                        $last_values_string = implode(' -> ',$last_values_array).$f->getUnit();
                    }

                    // Set locale for date
                    $created_date       = gettype($a->created_at) == 'string' ? $a->created_at : $a->created_at->toDateTimeString();
                    $created_date_local = new Moment($created_date, 'UTC');
                    $locale_array       = config('laravellocalization.supportedLocales');
                    $locale_identifier  = isset($locale_array[$u->locale]) ? $locale_array[$u->locale]['regional'] : null;

                    if ($locale_identifier)
                        \Moment\Moment::setLocale($locale_identifier);

                    $display_date_local = $created_date_local->setTimezone($f->timezone)->format('LLLL', new \Moment\CustomFormats\MomentJs());
                    
                    if ($locale_identifier) // reset global locale
                        \Moment\Moment::setLocale('en_GB');
                    
                    if($log_on)
                        Log::debug($debug_start.' Updated or created Alert, sending email to '.$u->email);

                    Mail::to($u->email)->send(new AlertMail($a, $u->name, $last_values_string, $display_date_local));
                }
            }
            else if($log_on)
            {
                Log::debug($debug_start.' evaluation_count='.$evaluation_count.'x (< '.$f->alert_on_occurences.'x), from: '.$last_val_inf['from'].', last_values='.json_encode($last_values_data));
            }
        }
        else if($log_on)
        {
            Log::debug($debug_start.' last_value_count=0, from: '.$last_val_inf['from'].', query: '.$last_val_inf['query']);
        }
        
        return $alert_count;
    }
}
