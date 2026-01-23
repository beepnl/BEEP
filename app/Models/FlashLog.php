<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Traits\MeasurementLoRaDecoderTrait;

use App\Hive;
use App\Device;
use App\User;
use App\Measurement;
use App\Models\CalculationModel;
use Moment\Moment;
use Storage;
use Cache;

class FlashLog extends Model
{
    use MeasurementLoRaDecoderTrait;
    
    protected $precision  = 's';
    protected $timeFormat = 'Y-m-d H:i:s';
    protected $weight_mid = 20;
    protected static $minUnixTime= 1546297200; // min time for Flashlog internal time is: 2019-01-01 00:00:00

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flash_logs';

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
    protected $fillable = ['user_id', 'device_id', 'hive_id', 'log_messages', 'log_saved', 'log_parsed', 'log_has_timestamps', 'bytes_received', 'log_file', 'log_file_stripped', 'log_file_parsed', 'log_size_bytes', 'log_erased', 'time_percentage', 'persisted_days', 'persisted_measurements', 'persisted_block_ids', 'log_date_start', 'log_date_end', 'logs_per_day', 'csv_url', 'meta_data', 'time_corrections', 'valid_override'];
    protected $hidden   = ['device', 'hive', 'user', 'persisted_block_ids'];

    protected $appends  = ['device_name', 'hive_name', 'user_name'];
    protected $casts    = ['meta_data' => 'array', 'time_corrections'=>'array'];


    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getDeviceKeyAttribute()
    {
        $device = $this->device;
        if (isset($device))
            return $device->key;

        return null;
    }
    public function getDeviceNameAttribute()
    {
        $device = $this->device;
        if (isset($device))
            return $device->name.' ('.$device->id.')';

        return null;
    }
    public function getHiveNameAttribute()
    {
        $hive = $this->hive;
        if (isset($hive))
            return $hive->name;

        return null;
    }
    public function getUserNameAttribute()
    {
        $user = $this->user;
        if (isset($user))
            return $user->name;

        return null;
    }
    
    // Get content of log_file, log_file_stripped, or log_file_parsed
    public function getFileSizeBytes($type='log_file')
    {
        if(isset($this->{$type}))
        {
            $file = 'flashlog/'.last(explode('/',$this->{$type}));
            //die(print_r($file));
            $disk = env('FLASHLOG_STORAGE', 'public');
            if (Storage::disk($disk)->exists($file))
                return Storage::disk($disk)->size($file);
        }
        return null;
    }

    public function getFileContent($type='log_file')
    {
        if(isset($this->{$type}))
        {
            $file = 'flashlog/'.last(explode('/',$this->{$type}));
            //die(print_r($file));
            $disk = env('FLASHLOG_STORAGE', 'public');
            if (Storage::disk($disk)->exists($file))
                return Storage::disk($disk)->get($file);
        }
        return null;
    }

    public function getPersistedBlockIdsArrayAttribute($value)
    {
        if (isset($value) == false)
            $value = $this->persisted_block_ids;

        $array = [];

        if (isset($value))
        {
            $array = explode(',', $value);

            foreach ($array as $key => $value) 
            {
                $array[$key] = intval($value);
            }
        }

        return $array;
    }

    public function setPersistedBlockIdsArrayAttribute($array)
    {
        $this->persisted_block_ids = implode(',', $array);
        $this->save();

        return $array;
    }

    public function shouldBeParsed()
    {
        if ($this->bytes_received > 100 && !isset($this->meta_data['auto_parsed']) &&
            (
                empty($this->log_date_start) || 
                empty($this->meta_data) || 
                empty($this->csv_url) || 
                !isset($this->meta_data['rtc_bug']) || 
                !isset($this->meta_data['valid_data_points']) ||
                count($this->getHighDataDates()) > 0 ||
                $this->hasNoWeightData() ||
                $this->parsed == false
            )
        )
        {
            return true;
        }

        return false;    
    }

    public static function parseUnparsedFlashlogs()
    {
        $parse_fl_sec   = env('FLASHLOG_PARSE_HISTORY_SEC', (24*7*3600)); // last week
        $last_week_date = date('Y-m-d', time()-$parse_fl_sec);
        $fls            = self::where('created_at', '>' , $last_week_date)->whereNotNull('log_file')->where('log_messages', '>', 0)->orderByDesc('created_at')->get();
        $cnt            = $fls->count();
        Log::info("Auto parse check for $cnt Flashlogs since $last_week_date");
        
        foreach($fls as $fl)
        {
            if ($fl->shouldBeParsed())
            {
                Log::info("Flashlog $fl->id :");
                $data = $fl->getFileContent('log_file');
                if (isset($data))
                {
                    // log($data='', $log_bytes=null, $save=true, $fill=?, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=false, $match_days_offset=0, $add_sensordefinitions=true, $use_rtc=true, $correct_data=false)
                    $res  = $fl->log($data, null, true, true, false, null, null, null, false, false, 0, true, true, true);

                    foreach ($res as $key => $value) {
                        Log::info("$key=$value"); 
                    }
                    $meta_data = $fl->meta_data;
                    $meta_data['auto_parsed']=date('Y-m-d H:i:s');
                    $fl->meta_data = $meta_data;
                    $fl->save();

                    Log::info("________________________");
                }
                else
                {
                    Log::info("No data");
                }
            }
        }
    }

    public function getLogDays($data_only=true) // only logs with set time
    {
        if ($data_only && isset($this->meta_data['data_days']))
        {
            return round($this->meta_data['data_days'], 2);
        }
        else if ($data_only && isset($this->meta_data['valid_data_points']))
        {
            return count($this->meta_data['valid_data_points']);
        }
        else if (isset($this->log_date_start) && isset($this->log_date_end))
        {
            return round( (strtotime($this->log_date_end) - strtotime($this->log_date_start))/(24*3600), 2);
        }
        return null;
    }

    public function getLogPerDay()
    {
        // first check if meta data contains the exact value
        if (isset($this->meta_data['valid_data_points']))
        {
            $data_points_sum = 0;
            $data_points_cnt = 0;
            $data_points_arr = array_values($this->meta_data['valid_data_points']); // keys are dates, make integer
            $data_points_len = count($data_points_arr);

            for ($i=0; $i < $data_points_len; $i++)
            { 
                $v   = $data_points_arr[$i];
                if (is_numeric($v))
                {
                    $v_p = $i > 0 ? $data_points_arr[$i-1] : null;
                    $v_n = $i < $data_points_len - 1 ? $data_points_arr[$i+1] : null;
                    {
                        if ($v > 0 && $v_p != 0 && $v_n != 0) // only count full day values, no days at start and end, that are half
                        {
                            $data_points_sum += $v;
                            $data_points_cnt ++;
                        }
                    }
                }
            }

            $logs_per_day = $data_points_cnt == 0 ? 0 : round($data_points_sum/$data_points_cnt);
            return $logs_per_day;
        }
        // else base on log_messages and log_days
        $log_days = $this->getLogDays();
        if (isset($log_days) && $log_days > 0 && isset($this->log_messages))
        {
            return round($this->log_messages * (min(100, $this->time_percentage)/100) / $log_days);
        }
        return null;
    }

    public function getTimeLogPercentage($logs_per_day = null)
    {
        if ($logs_per_day === null || !is_numeric($logs_per_day))
            $logs_per_day = $this->getLogPerDay();

        if (isset($logs_per_day)) // means that $this->log_date_end is set
        {
            $logs_per_day_full = isset($this->device) ? $this->device->getMeasurementsPerDay() : 96;
            $logs_per_day_perc = max(0, round(100 * $logs_per_day / $logs_per_day_full, 1));
            return $logs_per_day_perc;
        }
        return 0;
    }

    public function getWeightLogPercentage()
    {
        $weight_kg_perc = 0; 
        if (isset($this->meta_data['data_days_weight']) && isset($this->meta_data['data_days']) && $this->meta_data['data_days'] > 0)
        {
            $weight_kg_perc = round(100 * $this->meta_data['data_days_weight'] / $this->meta_data['data_days']);
        }
        return $weight_kg_perc;
    }

    public function validLog()
    {
        /* validate log if: 
           1. Manual validation, or
           2. created_at (upload date) is within 1 hour from last timestamp
           3. log % > 90%: interval 15 min should have 96 msg/day (>86msg = >90%)
        */
        if (isset($this->valid_override) && $this->valid_override === 1)
            return true; // 1.

        $logs_per_day = $this->getLogPerDay();
        
        if (isset($logs_per_day)) // means that $this->log_date_end is set
        {
            $created_u  = strtotime($this->created_at);
            $last_log_u = strtotime($this->log_date_end);
            if (abs($last_log_u - $created_u) < env('FLASHLOG_VALID_UPLOAD_DIFF_SEC', 7200)) // 2. make sure last available containing data is not more than max time away from upload date
            {
                $logs_per_day_perc = $this->getTimeLogPercentage();
                if ($logs_per_day_perc >= env('FLASHLOG_VALID_TIME_LOG_PERC', 90) && $logs_per_day_perc <= 101) // 3.
                    return true; 
            }
        }
        return false;
    }

    public function hasRtcBug()
    {
        /* has RTC bug if: 
           meta data shows more than the set interval data items (96) on a RTC month index error date
        */
        if (isset($this->meta_data['rtc_bug']))
            return $this->meta_data['rtc_bug'];

        else if (isset($this->meta_data['valid_data_points']))
            return $this->fixBugRtcMonthIndex(null, true); // indicate bug

        return false;
    }

    public function hasNoWeightData()
    {
        // has no weight data if data_days_weight < 1 
        if (isset($this->meta_data['data_days_weight']))
            return ($this->meta_data['data_days_weight'] < 1);

        return false;
    }

    public function getHighDataDates()
    {
        // has high data days if a day has more than 97 log indices: 
        if (isset($this->meta_data['valid_data_points']))
        {
            $valid_data_points = $this->meta_data['valid_data_points'];
            if (is_array($valid_data_points) && count($valid_data_points) > 0 && max($valid_data_points) > 97)
            {
                $dates = [];
                foreach ($valid_data_points as $date => $value)
                {
                    if ($value > 97)
                        $dates[$date] = $value;
                }
                return $dates;
            }
        }
        return [];
    }

    public function hasTimeErr()
    {
        /* has RTC time error if: 
           last port 2 message time is > now
        */
        if (isset($this->meta_data['time_err_perc']))
            return ($this->meta_data['time_err_perc'] > 0); 

        return false;
    }

    public function hasBatErr()
    {
        /* has Battery voltage issue: 
           if voltage dropped below 2.7V
        */
        if (isset($this->meta_data['bat_low_blocks']))
            return ($this->meta_data['bat_low_blocks'] > 0); 

        return false;
    }

    public function getFixesArray()
    {
        $fixes = [];

        if (isset($this->meta_data['fixBugRtcMonthIndex']) && $this->meta_data['fixBugRtcMonthIndex'] > 0)
            $fixes['fa-clock-o'] = "RTC bug fixes: ".$this->meta_data['fixBugRtcMonthIndex'];

        if ($this->valid_override === 1)
            $fixes['fa-exclamation-triangle'] = "Manually validated";

        return $fixes;
    }

    public function getErrorsArray()
    {
        $errors= [];
        
        if ($this->hasRtcBug())
            $errors['fa-clock-o'] = 'RTC bug';

        if ($this->hasTimeErr())
        {
            $time_err = 'Time err';

            if (isset($this->meta_data['time_err_perc']))
                $time_err .= ': '.$this->meta_data['time_err_perc'].'%';

            $errors['fa-calendar'] = $time_err;
        }

        if ($this->hasBatErr())
        {
            $bat_low_err = '';

            if (isset($this->meta_data['bat_low_blocks']))
                $bat_low_err .= $this->meta_data['bat_low_blocks'].'x ';

            $bat_low_err .= 'Bat low';
            
            if (isset($this->meta_data['bat_low_perc']))
                $bat_low_err .= ': '.$this->meta_data['bat_low_perc'].'%';

            $errors['fa-battery-quarter'] = $bat_low_err;
        }

        $highDataDates     = $this->getHighDataDates();
        $highDataDateCount = count($highDataDates);
        if ($highDataDateCount > 0)
        {
            
            $errors['fa-plus-square'] = 'High data on '.$highDataDateCount.' days';
            
            if ($highDataDateCount <= 5)
            {
                $highDataDatesArr = [];
                foreach ($highDataDates as $date => $value) {
                    $highDataDatesArr[] = "$date: $value values";
                }
                $errors['fa-plus-square'] .= ': '.implode(', ', $highDataDatesArr);
            }
        }

        $weight_kg_perc = $this->getWeightLogPercentage();
        if ($this->hasNoWeightData())
        {
            $errors['fa-balance-scale'] = 'No weight data';
        }
        else if ($weight_kg_perc < 90)
        {
            $errors['fa-balance-scale'] = "Weight data: $weight_kg_perc %";
        }

        return $errors;
    }

    public function getFixAndErrorHtmlIcons()
    {
        $html  = '';

        $errs  = $this->getErrorsArray();
        if (count($errs) > 0)
        {
            foreach ($errs as $e_icon => $e_text)
            {
                $color = 'red';
                if ($e_icon == 'fa-balance-scale' && intval(substr($e_text, -4, 2)) > 0)
                    $color = 'orange';

                $html .= '<i class="fa fa-sm '.$e_icon.'" style="display: inline-block; height: 16px; width: 14px; margin:2px; color: '.$color.';" title="'.$e_text.'"></i>';
            }
        }
        $fixes = $this->getFixesArray();
        if (count($fixes) > 0)
        {
            foreach ($fixes as $f_icon => $f_text)
            {
                $html  .= '<i class="fa fa-sm '.$f_icon.'" style="display: inline-block; height: 16px; width: 14px; margin:2px; color: green;" title="'.$f_text.'"></i>';
            }
        }
        return $html;
    }

    public function getLogCacheName($fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null)
    {
        return 'flashlog-'.$this->id.'-fill-'.$fill.'-show-'.$show.'-matches-'.$matches_min_override.'-dbrecs-'.$db_records_override; // removed -props-'.$match_props_override.'
    }

    private function getTimeCorrections()
    {
        $time_corrections_array = [];
        if (isset($this->time_corrections) && is_array($this->time_corrections))
        {
            foreach ($this->time_corrections as $date_sec_arr)
            {
                $datetime   = array_key_first($date_sec_arr);
                $timestamp  = strtotime($datetime);
                $sec_offset = $date_sec_arr[$datetime];
                $time_corrections_array[$timestamp] = $sec_offset;
            }
            krsort($time_corrections_array, SORT_NUMERIC);
            return $time_corrections_array;
        }
        return [];
    }

    // Main function that creates the array from the string FlashLog files
    public function log($data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0, $add_sensordefinitions=true, $use_rtc=true, $correct_data=true)
    {
        if (!isset($this->device_id) || !isset($this->device))
            return ['error'=>'No device set, cannot parse Flashlog because need device key to get data from database'];

        $cache_name = $this->getLogCacheName($fill, $show, $matches_min_override, $match_props_override, $db_records_override);
        
        // get result from cache only if it contains any matches
        if ($from_cache === true && Cache::has($cache_name) && $save === false && $fill === true)
        {
            $result = Cache::get($cache_name);
            if (isset($result['log']))
            {
                foreach ($result['log'] as $block_i => $block) 
                {
                    if (isset($block['matches']))
                        return $result; // return cache result if it has a block with matches
                }
            }
        }
        
        $result   = null;
        $parsed   = false;
        $saved    = false;
        $messages = 0;

        $out   = [];
        $disk  = env('FLASHLOG_STORAGE', 'public');
        $f_dir = 'flashlog';
        $lines = 0; 
        $bytes = 0; 
        $logtm = 0;
        $erase = -1;
        
        $f_log = null;
        $f_str = null;
        $f_par = null;

        $device = $this->device;
        $sid    = $this->device_id; 
        $time   = date("YmdHis");
        $mime_t = ['mimetype' => 'text/plain'];
        $mime_j = ['mimetype' => 'application/json'];

        if (empty($data)) // get data from parsed flashlog file, or unparsed log_file
        {
            if (isset($this->log_file_parsed) && $from_cache)
            {
                $out = json_decode($this->getFileContent('log_file_parsed'), true); // decode ar associative array
                if (isset($out) && gettype($out) == 'array')
                {
                    $messages = count($out);
                    $lines    = $messages;
                }
            }

            if (empty($out) && isset($this->log_file))
                $data = $this->getFileContent('log_file');
        }
        
        // parse the data from the unparsed log file
        if (empty($out) && isset($data) && isset($sid))
        {
            $data    = strtoupper($data);
            $data    = preg_replace('/[\r\n|\r|\n]+|\)\(|FEFEFEFE/i', "\n", $data);
            // interpret every line as a standard LoRa message
            $in      = explode("\n", $data);
            $lines   = count($in);
            $bytes   = 0;
            $alldata = "";
            foreach ($in as $line)
            {
                $lineData = substr(preg_replace('/[^A-Fa-f0-9]/', '', $line),4);
                $alldata .= $lineData;
                $bytes   += mb_strlen($lineData, 'utf-8')/2;
            }
            unset($in);
            
            // fw 1.5.9 1st port 2 log entry
            // 0100010005000902935D7C83FFFF94540E0123A76E05A5161AEE1F0000002A03091D01000F256079A4250A 03351B0CF10CEA640A0116539504020D8C081B0C0A094600140010002C0049001A0014005A001A0033002A07000000000000256079A6270A
            // 01000100050009029356BAA6FFFF94540E0123FA62FD38425EEE1F0000001A03091D01000F256079A25D0A 03351B0CBF0CB5640A011386550402059D0C600C0A0946005B002E0067003C0016001000160015000F000507000000000000256079A3E00A


            // Split data by 0A02 and 0A03 (0A03 30 1B) 0A0330
            $data  = preg_replace('/0A022([A-Fa-f0-9]{1})0100/', "0A\n022\${1}0100", $alldata);
            $data  = preg_replace('/0A03351B/', "0A\n03351B", $data);

            // Calculate from payload parts
            //            port pl_len     |bat 5 bytes value |weight (1/2)  3/6 bytes value  |ds18b20 (0-9) 0-20 bytes value |audio (0-12 bins)   sta/sto bin     0-12 x 2 bytes   |bme280 3x 2b values|time             |delimiter
            // $payload = '/0([0|3]{1})([A-Fa-f0-9]{2})1B([A-Fa-f0-9]{10})0A0([1-2]{1})([A-Fa-f0-9]{6,12})040([0-9]{1})([A-Fa-f0-9]{0,40})0C0([A-Ca-c0-9]{1})([A-Fa-f0-9]{4})([A-Fa-f0-9]{0,48})07([A-Fa-f0-9]{12})([A-Fa-f0-9]{0,12})0A/';
            // $replace = "\n0\${1}\${2}1B\${3}0A0\${4}\${5}040\${6}\${7}0C0\${8}\${9}\${10}07\${11}\${12}0A";

            // Calculate from payload parts
            // $payload = '/([A-Fa-f0-9]{4})1B([A-Fa-f0-9]{10})0A0([1-2]{1})([A-Fa-f0-9]{6,12})040([0-9]{1})([A-Fa-f0-9]{0,40})0C0([A-Ca-c0-9]{1})([A-Fa-f0-9]{4})([A-Fa-f0-9]{0,48})07([A-Fa-f0-9]{12})([A-Fa-f0-9]{0,12})0A/';
            // $replace = "\n\${1}1B\${2}0A0\${3}\${4}040\${5}\${6}0C0\${7}\${8}\${9}07\${10}\${11}0A";

            // Calculate from payload min/max length
            $payload = '/([A-Fa-f0-9]{4})1B([A-Fa-f0-9]{10,14})0A([A-Fa-f0-9]{77,90})0A/';
            
            $replace = "\n\${1}1B\${2}0A0\${3}0A";
            
            $data  = preg_replace($payload, $replace, $data);

            // fix missing battery hex code
            $data  = preg_replace('/0A03([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})0D/', "0A\n03\${1}1B0D\${2}0D", $data);
            // split error lines
            $data  = preg_replace('/03([A-Fa-f0-9]{90,120})0A([A-Fa-f0-9]{0,4})03([A-Fa-f0-9]{90,120})0A/', "03\${1}0A\${2}\n03\${3}0A", $data);
            $data  = preg_replace('/03([A-Fa-f0-9]{90,120})0A1B([A-Fa-f0-9]{90,120})0A/', "03\${1}0A\n031E1B\${2}0A", $data); // missing 031E
            $data  = preg_replace('/03([A-Fa-f0-9]{2})1B0D1B0D([A-Fa-f0-9]{90,120})0A/', "03\${1}1B0D\${2}0A", $data); // Double 1B0D (fw 1.4.2)
            $data  = preg_replace('/02([A-Fa-f0-9]{76})0A03([A-Fa-f0-9]{90,120})0A/', "02\${1}0A\n03\${2}0A", $data); // port 2 data
            $data  = preg_replace('/([A-Fa-f0-9]{12,14})0A01([A-Fa-f0-9]{6})([A-Fa-f0-9]{1})040([A-Fa-f0-9]{70,90})0A/', "\${1}0A01\${2}040\${4}0A", $data); // 2025-06-10 PGe: fw 1.5.15: remove extra character after weight 24 bit string

            // remove empty rows
            $data  = preg_replace('/^\h*\v+/m', '', $data);

            if ($save)
            {
                $logFileName =  $f_dir."/sensor_".$sid."_flash_stripped_$time.log";
                $saved = Storage::disk($disk)->put($logFileName, $data, $mime_t);
                $f_str = Storage::disk($disk)->url($logFileName); 
            }

            $counter = 0;
            $log_min = 0;
            $minute  = 0;
            $max_time= time();
            $in      = explode("\n", $data);
            unset($data);

            foreach ($in as $line)
            {
                $counter++;
                $data_array = $this->decode_flashlog_payload($line);
                $data_array['i'] = $counter; // i is 1 based (not 0 based)

                if ($data_array['port'] == 3) // port 3 message is log message
                    $messages++;
                
                if (isset($data_array['measurement_interval_min']))
                {
                    $log_min = $data_array['measurement_interval_min'];
                }
                else
                {
                    $minute += $log_min;
                    $data_array['minute'] = $minute;
                    $data_array['minute_interval'] = $log_min;
                }

                // Add time if not present
                if (!isset($data_array['time']) && isset($data_array['time_device']))
                {
                    $logtm++;

                    if (!isset($data_array['time_error'])) // don't set time from time_device if too low/high
                    {
                        $ts = intval($data_array['time_device']);

                        if ($ts > self::$minUnixTime && $ts < $max_time) // > 2019-01-01 00:00:00 < now
                        {
                            $time_device = new Moment($ts);
                            $data_array['time'] = $time_device->format($this->timeFormat);
                        }
                    }
                }

                $out[] = $data_array;
            }

            // Save parsed flashlog
            if ($messages > 0)
            {
                $parsed = true;
                
                if ($save)
                {
                    // Save
                    $logFileName = $f_dir."/sensor_".$sid."_flash_parsed_$time.json";
                    $saved = Storage::disk($disk)->put($logFileName, json_encode($out), $mime_j);
                    $f_par = Storage::disk($disk)->url($logFileName);
                }
            }
        }
        unset($in);

        if (env('FLASHLOG_NEVER_DELETE', false) === true)
            $erase = false;
        else
            $erase = $log_bytes != null && $this->diff_percentage($log_bytes, $bytes, 2) < 0.1 ? true : false;
        
        $result = [
            'lines_received'=>$lines,
            'bytes_received'=>$bytes,
            'log_size_bytes'=>$log_bytes,
            'log_has_timestamps'=>$logtm,
            'log_saved'=>$saved,
            'log_parsed'=>$parsed,
            'log_messages'=>$messages,
            'erase_mx_flash'=>$erase ? 0 : -1,
            'erase'=>$erase,
            'erase_type'=>$saved ? 'fatfs' : null // fatfs, or full
        ];

        // fill time in unknown time data 
        $time_percentage = $messages > 0 ? round(100 * $logtm / $messages, 2) : 0;

        if ($fill && isset($out) && gettype($out) == 'array' && count($out) > 0)
        {
            $flashlog_filled = $this->fillTimeFromInflux($device, $out, $save, $show, $matches_min_override, $match_props_override, $db_records_override, $match_days_offset, $add_sensordefinitions, $use_rtc, $correct_data); // ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];
            unset($out);

            if ($flashlog_filled)
            {
                if (isset($flashlog_filled['log']))
                    $result['log'] = $flashlog_filled['log'];

                $result['matching_blocks']   = $flashlog_filled['matching_blocks'];
                $result['device']            = $device->name.' ('.$sid.')';
                $result['records_flashlog']  = $flashlog_filled['records_flashlog'];
                $result['time_insert_count'] = $flashlog_filled['time_insert_count'];
                $result['records_timed']     = $flashlog_filled['records_timed'];
                $weight_percentage           = round($flashlog_filled['weight_percentage'], 2);
                $result['weight_percentage'] = $weight_percentage.'%';
                $result['time_insert_count'] = $flashlog_filled['time_insert_count'];
                $time_percentage             = round($flashlog_filled['time_percentage'], 2);
                $result['time_percentage']   = $time_percentage.'%';

                if (isset($this->time_percentage) == false || (min(100, $this->time_percentage*0.9) <= $time_percentage || $save_override) || $this->time_percentage > 100)
                {
                    if ( ($save || $result['matching_blocks'] > 0) && isset($flashlog_filled['flashlog']) && count($flashlog_filled['flashlog']) > 0 && $flashlog_filled['time_insert_count'] > 0)
                    {
                        $save        = true;
                        $logFileName = $f_dir."/sensor_".$sid."_flash_filled_$time.json";
                        $saved       = Storage::disk($disk)->put($logFileName, json_encode($flashlog_filled['flashlog']), $mime_j);
                        $f_par       = Storage::disk($disk)->url($logFileName);
                    }
                }
                else
                {
                    $result['time_percentage'] .= ', previous time percentage ('.$this->time_percentage.'%) > new ('.$time_percentage.'%)';
                    if ($save)
                        $result['time_percentage'] .= ', so filled file not saved';

                    $time_percentage = $this->time_percentage;
                    $f_par           = $this->log_file_parsed;
                }

            }
        }

        // create Flashlog entity
        if ($save)
        {
            if (isset($this->log_size_bytes) == false && isset($log_bytes)) // first upload 
                $this->log_size_bytes = $log_bytes;

            if (isset($this->hive_id) == false) // first upload 
                $this->hive_id = $device->hive_id;

            if (isset($this->log_erased) == false) // first upload 
                $this->log_erased = $erase;

            if (isset($this->log_saved) == false) // first upload 
                $this->log_saved = $saved;
            
            $this->bytes_received = $bytes == 0 && isset($this->bytes_received) && $this->bytes_received > 0 ? $this->bytes_received : $bytes; // only update on >0
            $this->log_has_timestamps = $logtm > 0 ? true : false;
            $this->log_parsed = $parsed;
            $this->log_messages = $messages;
            $this->log_file_stripped = $f_str;
            $this->log_file_parsed = $f_par;
            $this->time_percentage = $time_percentage;
            $this->save();

            // if data is available, also add CSV file from log_file_parsed
            if (isset($flashlog_filled['flashlog']))
            {
                $csv_saved = $this->addCsvToFlashlog($flashlog_filled['flashlog']);
                //dd($csv_saved, $this->meta_data);
                if ($csv_saved)
                    $result["Meta data"] = CalculationModel::arrayToString($this->meta_data, ', ', '', ['valid_data_points','port2_times_device','firmwares','lowest_bv']);
            }
        }

        Cache::put($cache_name, $result, 86400); // keep for a day

        return $result;
    }


    // Flashlog parsing functions
    // Try to make blocks of data for matching
    private function getFlashLogOnOffs($device, $flashlog, $start_index=0, $start_time='2018-01-01 00:00:00')
    {
        $onoffs      = [];
        $fl_index    = $start_index;
        $fl_length   = count($flashlog);
        $fl_index_end= $fl_length - 1;

        $first_p3_mes= null;
        $last_p3_mes = null;
        $p3_mes_count= 0;
        $p2_mes_count= 0;
        $block_count = 0;

        for ($i=$fl_index; $i <= $fl_index_end; $i++) 
        {
            $f = $flashlog[$i];
            if (isset($f['port'])) // check for port 2 messages (switch on/off) in between 'before' and 'after' matches
            {
                $f_port = intval($f['port']);

                if ($f_port > 0 && isset($f['beep_base']))
                {
                    if ($f_port == 2 && isset($f['firmware_version']))
                    {
                        $p2_mes_count++;
                        if ($i < $fl_index_end && $flashlog[$i+1]['port'] == 3) // count a new block if the message after this port 2 is a port 3 (measurement)
                        {
                            $block_count++;
                            $onoffs[$block_count] = $f;
                            $onoffs[$block_count]['block_count'] = $block_count;
                        }
                    }
                    else if ($f_port == 3)
                    {
                        $p3_mes_count++;

                        // If no blocks have been found yet
                        if ($block_count == 0)
                        {
                            // if device has RTC, then use first port 3 message as first block ID
                            if (!isset($first_p3_mes))
                            {
                                $first_p3_mes = $f;
                                $block_count++;
                                $onoffs[$block_count] = $f;
                                $onoffs[$block_count]['block_count'] = $block_count;
                            }

                            $last_p3_mes = $f;
                        }
                        else // set last index of the current port 2 block 
                        {
                            $onoffs[$block_count]['end_index'] = $i; // index of the flashlog item is 1 lower than the i inside the flashlog
                        }
                    }
                }
            }
        }
        // For FlashLogs with RTC, and no blocks: check if there are port3 messages
        if (isset($first_p3_mes) && $block_count == 0 && $p3_mes_count > 10)
        {
            // if a port 2 message is missing take the firt port 3 message 
            $first_p3_mes['port'] = 2;
            $first_p3_mes_time    = isset($first_p3_mes['time']) ? $first_p3_mes['time'] : 0;
            $last_p3_mes_time     = isset($last_p3_mes['time']) ? $last_p3_mes['time'] : 0;
            $first_p3_mes_i       = isset($first_p3_mes['i']) ? $first_p3_mes['i'] : 0;
            $last_p3_mes_i        = isset($last_p3_mes['i']) ? $last_p3_mes['i'] : 0;
            
            $p3_total_indices     = $last_p3_mes_i - $first_p3_mes_i;
            $time_p3_total_sec    = strtotime($last_p3_mes_time) - strtotime($first_p3_mes_time);
            $first_p3_mes['measurement_interval_min'] = $time_p3_total_sec > 0 && $p3_total_indices > 0 ? ($time_p3_total_sec / 60) / $p3_total_indices : $device->measurement_interval_min;
            $first_p3_mes['i']    = max(0, $first_p3_mes['i'] - 1); // i of the faked port 2 message coming in front of the port 3 messages
            $first_p3_mes['end_index'] = $first_p3_mes['i'] + 1 + $p3_total_indices; // from first to last p2 message
            
            $onoffs[0] = $first_p3_mes;
        }

        //dd($onoffs);
        //Log::debug(['getFlashLogOnOffs', 'device_id'=>$device->id, 'fl_length'=>$fl_length, 'p2'=>$p2_mes_count, 'p3'=>$p3_mes_count, 'fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'onoffs'=>$onoffs]);

        return array_values($onoffs);
    }

    private function matchFlashLogTime($device, $flashlog, $matches_min=1, $match_props=9, $start_index=0, $end_index=0, $duration_hrs=0, $interval_min=15, $start_time='2018-01-01 00:00:00', $db_records=80, $show=false)
    {
        $fl_index    = $start_index;
        $fl_index_end= $end_index;
        $fl_items    = max(0, $end_index - $start_index);
        $day_props_m = isset($interval_min) ? 24 * 60 / $interval_min : 96; // max total measurements per match prop on a full data day
        $day_total_m = $match_props * $day_props_m; // max total measurements for all match_props on a full data day
        
        if ($flashlog == null || $fl_items < $matches_min)
            return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'db_start_time'=>$start_time, 'db_data'=>[], 'db_data_count'=>0, 'message'=>'too few flashlog items to match: '.$fl_items];
        
        // Check the amount of data per day over the full length of the period, check for the date with the maximum amount of measurements
        $start_mom   = new Moment($start_time);
        $end_time    = $start_mom->addHours(round($duration_hrs))->format($this->timeFormat);

        $count_query = 'SELECT COUNT(*) FROM "sensors" WHERE '.$device->influxWhereKeys().' AND from_flashlog != \'1\' AND time >= \''.$start_time.'\' AND time <= \''.$end_time.'\' GROUP BY time(24h) ORDER BY time ASC LIMIT 500';
        $data_count  = Device::getInfluxQuery($count_query, 'flashlog');
        $day_sum_max = 0;
        $days_valid  = 0;

        $db_data_cnt = 0;
        foreach ($data_count as $day_i => $count_array) 
        {
            $data_date    = $count_array['time'];
            unset($count_array['time']); // don't include time in sum
            $day_sum      = array_sum($count_array);
            $day_valid    = ($day_sum > 0.7*$day_total_m); // magic factor 0.7
            $db_data_cnt += $day_sum; // fill db_data_cnt for providing insight in where to fill flashlog data (sum == 0)
            
            if ($day_valid && $days_valid < 2) // take the second, or first valid dates
            {
                $start_time = $data_date;
                $days_valid++;
            }
        }
        
        //dd($start_time, $end_time, $duration_hrs, count($data_count), $db_data_cnt);

        // get data from the day with max amount of measurements
        $query       = 'SELECT * FROM "sensors" WHERE '.$device->influxWhereKeys().' AND from_flashlog != \'1\' AND time >= \''.$start_time.'\' ORDER BY time ASC LIMIT '.min(1000, max($matches_min, $db_records));
        $db_data     = Device::getInfluxQuery($query, 'flashlog');

        //die(print_r([$start_time, $db_data]));
        
        $database_log  = [];
        $db_first_unix = 0;
        foreach ($db_data as $d)
        {
            $clean_d = self::cleanDbDataItem($d);

            if (count($clean_d) > $match_props && array_sum(array_values($clean_d)) != 0)
            {
                if (count($database_log) == 0) // first entry
                {
                    $db_first_unix      = strtotime($clean_d['time']);
                    $clean_d['unix']    = $db_first_unix;
                    $clean_d['seconds'] = 0;
                }

                $clean_d['unix']    = strtotime($clean_d['time']);
                $clean_d['seconds'] = $clean_d['unix'] - $db_first_unix;

                $database_log[] = $clean_d;

                //die(print_r([$start_time, $clean_d, count($clean_d), $db_data]));
            }

        }

        $db_log_item_cnt = count($database_log);

        if ($db_log_item_cnt < $matches_min)
            return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'db_start_time'=>$start_time, 'db_data_measurements'=>$db_data_cnt, 'db_data_count'=>count($database_log), 'message'=>'too few database items to match: '.count($database_log)];

        // look for the measurement value(s) in $database_log in the remainder of $flashlog
        $matches            = [];
        $tries              = 0;
        $match_count        = 0;
        $match_first_min_fl = 0;
        $match_first_sec_db = 0;
        $db_log_item_index  = 0;

        foreach ($database_log as $d)
        {
            for ($i=$fl_index; $i < $fl_index_end; $i++) 
            {
                $f = $flashlog[$i];

                if ($f['port'] == 3) // keep looking if found matches are < min matches
                {
                    $match = array_intersect_assoc($d, $f);
                    if ($match != null && count($match) >= $match_props)
                    {
                        if ($match_count == 0)
                        {
                            $match_first_min_fl = $f['minute'];
                            $match_first_sec_db = $d['seconds'];
                        }
                        else if ($match_count == 1) // check the time interval of the match in 2nd match
                        {
                            $match_increase_min = $f['minute'] - $match_first_min_fl;
                            $datab_increase_min = round( ($d['seconds'] - $match_first_sec_db) / 60);

                            //print_r([$match_increase_min, $datab_increase_min, $matches]);

                            if ($match_increase_min != $datab_increase_min) // if this does not match, remove first match and replace by this one
                            {
                                $match_first_sec_db = $d['seconds'];
                                $match_first_min_fl = $f['minute'];
                                $matches            = [];
                                $match_count        = 0;
                            }
                        }

                        $fl_index                 = $i;
                        $match['time']            = $d['time'];
                        $match['db_sec']          = $d['seconds'];
                        $match['minute']          = $f['minute'];
                        $match['minute_interval'] = $f['minute_interval'];  
                        $match['flashlog_index']  = $i;
                        $matches[$i]              = $match;
                        $match_count++;
                        continue 2; // next foreach loop to continue with the next database item

                        // TODO: do not break the loop to check how many matches there are
                    }
                    $tries++;
                }
            }
        }
        //die();
        if ($match_count > $matches_min)
                return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_data_measurements'=>$db_data_cnt, 'db_data_count'=>count($database_log), 'matches'=>$matches];

        return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_data_measurements'=>$db_data_cnt, 'db_data_count'=>count($db_data), 'message'=>'no matches found'];
    }

    private function diff_percentage($val1, $val2, $round_decimals=1)
    {
        if ($val1 === $val2)
            return 0;

        $rval1= round($val1,$round_decimals);
        $rval2= round($val2,$round_decimals);
        $diff = abs($rval1 - $rval2);
        $ave  = ($rval1 + $rval2) / 2;
        return $ave != 0 ? min(100, max(0, round(100 * $diff / $ave, 1))) : 0;
    }

    // Set time and add weight_kg by calibration 
    private function setFlashBlockTimes($match, $block_i, $startInd, $endInd, $flashlog, $device, $show=false, $sec_diff_per_index=null, $add_sensordefinitions=true, $use_device_time=false)
    {
        if (isset($match) && isset($match['flashlog_index']) && (isset($match['minute_interval']) || $use_device_time) && isset($match['time'])) // set times for current block
        {
            $matchInd= $match['flashlog_index'];
            $messages= $endInd - $startInd;
            $setCount= 0;
            
            if ($messages > 0)
            {
                $blockStaOff = $startInd - $matchInd;
                $blockEndOff = $endInd - $matchInd;
                $second_intv = isset($match['minute_interval']) ? $match['minute_interval']*60 : $device->measurement_interval_min*60;
                $matchSecInt = isset($sec_diff_per_index) ? $sec_diff_per_index : $second_intv;
                
                $matchTime   = $match['time'];
                $matchMoment = new Moment($matchTime);
                $startMoment = new Moment($matchTime);
                $endMoment   = new Moment($matchTime);
                
                $blockStart  = $startMoment->addSeconds(round($blockStaOff * $matchSecInt));
                $blockStaDate= $blockStart->format($this->timeFormat);
                $blockEnd    = $endMoment->addSeconds(round($blockEndOff * $matchSecInt));
                $blockEndDate= $blockEnd->format($this->timeFormat);

                // Load active weight device sensor definitions
                $weight_m_ids  = Measurement::getWeightMeasurementIds();
                $sensor_defs_w = $device->activeTypeDateSensorDefinitions($weight_m_ids['input_id'], $weight_m_ids['output_id'], $blockStaDate, $blockEndDate);
                $sensor_defs_c = $sensor_defs_w->count();
                $sensor_def    = null;

                if ($sensor_defs_c == 1)
                    $sensor_def = $sensor_defs_w->first();

                // add time to flashlog block
                $addCounter         = 0;
                $blockDeviceStaDate = null;

                for ($i=$startInd; $i <= $endInd; $i++) 
                { 
                    if (!isset($flashlog[$i]))
                        continue;

                    $fl      = $flashlog[$i];
                    $fl_time = null;
                    
                    if ($use_device_time)
                    {
                        if (isset($fl['time_device']) && !isset($fl['time_error']) && $fl['time_device'] > self::$minUnixTime)
                        {
                            $indexMoment= new Moment(intval($fl['time_device']));
                            $fl_time    = $indexMoment->format($this->timeFormat);
                            $fl['time'] = $fl_time;
                            if ($blockDeviceStaDate === null)
                            {
                                $blockDeviceStaDate = $fl_time;
                                $blockStaDate       = $fl_time;
                                $blockEndDate       = $fl_time;
                            }
                            else
                            {
                                $blockEndDate       = $fl_time;
                            }
                        }
                    }
                    else
                    {
                        $startMoment= new Moment($blockStaDate);
                        $indexMoment= $startMoment->addSeconds(round($addCounter * $matchSecInt));
                        $fl_time    = $indexMoment->format($this->timeFormat);
                        $fl['time'] = $fl_time;
                    }

                    // Add sensor definition measurement if not yet present (or if input_measurement_id == output_measurement_id) 
                    if ($fl['port'] == 3)
                    {
                        // if (isset($fl['time_corr']) && $fl['time_corr'] == 'prev')
                        //     dd($fl);

                        if ($add_sensordefinitions && $sensor_defs_c > 0 && isset($fl_time) && isset($fl['w_v']) && !isset($fl['weight_kg']) )
                        {
                            if ($sensor_defs_c > 1) // select appropriate $sensor_def for multiple sensor_defs
                            {
                                foreach ($sensor_defs_w as $sd) // ordered descending
                                {
                                    if ($sd->updated_at <= $fl_time) // take the first sd before the current time
                                    {
                                        $sensor_def = $sd;
                                        break;
                                    }
                                }
                            }
                            // Add calibrated weight value
                            if ($sensor_def)
                            {
                                $calibrated_measurement_val = $sensor_def->calibrated_measurement_value($fl['w_v']);
                                if ($calibrated_measurement_val !== null) // do not add sensor measurement is outside measurement min/max value
                                    $fl['weight_kg'] = $calibrated_measurement_val;
                            }
                        }
                        $setCount++;
                    }

                    $flashlog[$i] = $fl;

                    $addCounter++;
                }

                $log = null;
                // if ($show)
                // {
                //     // add request for database values per day
                //     $log = ['setFlashBlockTimes', 'block_i'=>$block_i, 'time0'=>$flashlog[$startInd]['time'], 'time1'=>$flashlog[$endInd]['time'], 'bl_start_i'=>$startInd, 'bl_end_i'=>$endInd, 'match_time'=>$matchTime, 'mi'=>$matchInd, 'min_int'=>$matchMinInt, 'msg'=>$messages, 'bso'=>$blockStaOff, 'bsd'=>$blockStaDate, 'beo'=>$blockEndOff, 'bed'=>$blockEndDate,'setCount'=>$setCount];
                // }
                //Log::debug(['setFlashBlockTimes', 'device_id'=>$device->id, 'bl_start_i'=>$startInd, 'bl_end_i'=>$endInd, 'match_time'=>$matchTime, 'mi'=>$matchInd, 'msg'=>$messages, 'block_i'=>$block_i, 'sensor_def'=>$sensor_def->toArray(), 'bsd'=>$blockStaDate, 'bed'=>$blockEndDate, 'setCount'=>$setCount]);
                
                $dbCount = $device->getMeasurementCount($blockStaDate, $blockEndDate);
                // TODO: Add check for every timestamp in DB with matching Flashlog (for bv, w_v, (t_0, t_1, or t_i))
                return ['flashlog'=>$flashlog, 'index_start'=>$startInd, 'index_end'=>$endInd, 'time_start'=>$blockStaDate, 'time_end'=>$blockEndDate, 'setCount'=>$setCount, 'log'=>$log, 'dbCount'=>$dbCount];
            }
        }
        return ['flashlog'=>$flashlog];
    }

    // Fix RTC month index rollover bug in fw 1.5.13 where the key dates are to be replaced by the value dates to reverse the RTC bug 
    // Only replace if the current date if it occurs twice and the target date does not yet exist
    private function fixBugRtcMonthIndex($data_array, $return_bug_detected=false, $start_index=null, $end_index=null)
    {
        $primary_dates_to_replace = [
        // flashlog_date => actual date (1 2, or 3 days earlier), this includes dates where It might be possible that the RTC wasn't updated during firmware updates, in which case the date will be 1 month out of date
            "2023-05-01" => "2023-05-31",
            "2023-06-01" => "2023-05-31", // after 2023-05-30, 2023-06-01 will be written as FL date, so 2023-05-31 will not exist, and 2023-06-01 will have double data
            "2023-07-01" => "2023-07-31",
            "2023-08-01" => "2023-07-31",
            "2023-10-01" => "2023-10-31",
            "2023-11-01" => "2023-10-31",
            "2023-12-01" => "2023-12-31",
            "2024-01-01" => "2023-12-31",
            "2024-03-01" => "2024-03-30",
            "2024-03-02" => "2024-03-31",
            "2024-04-01" => "2024-03-30",
            "2024-04-02" => "2024-03-31",
            "2024-05-01" => "2024-05-31",
            "2024-06-01" => "2024-05-31",
            "2024-07-01" => "2024-07-31",
            "2024-08-01" => "2024-07-31",
            "2024-10-01" => "2024-10-31",
            "2024-11-01" => "2024-10-31",
            "2024-12-01" => "2024-12-31",
            "2025-01-01" => "2024-12-31",
            "2025-03-01" => "2025-03-29",
            "2025-03-02" => "2025-03-30",
            "2025-03-03" => "2025-03-31",
            "2025-04-01" => "2025-03-29",
            "2025-04-02" => "2025-03-30",
            "2025-04-03" => "2025-03-31",
            "2025-05-01" => "2025-05-31",
            "2025-06-01" => "2025-05-31",
            "2025-07-01" => "2025-07-31",
            "2025-08-01" => "2025-07-31",
        ];

        // Build final dates_to_replace array based on valid_data_points
        $dates_to_replace   = [];
        $min_data_points    = 110;
        $saved              = false;
        $meta_data          = $this->meta_data;
                    
        if (isset($meta_data['valid_data_points']) && is_array($meta_data['valid_data_points']))
        {
            $vdp = $meta_data['valid_data_points'];
            
            // Check primary dates
            foreach ($primary_dates_to_replace as $date => $corrected_date)
            {
                // Only apply replacement if:
                // 1. The incorrect date has sufficient data points (> min_data_points)
                // 2. The corrected date doesn't already have data (to prevent overwriting valid data)
                
                if (isset($vdp[$date]) && is_numeric($vdp[$date]) && $vdp[$date] > $min_data_points)
                {
                    if (!isset($vdp[$corrected_date]) || $vdp[$corrected_date] < $vdp[$date] - 96)
                    {
                        $dates_to_replace[$date] = $corrected_date;
                        
                        if (!$return_bug_detected)
                            Log::debug("fixBugRtcMonthIndex fl=$this->id, found date to replace: $date -> $corrected_date has ".$vdp[$date]." data points"); 
                    }
                }
            }
        }

        if (count($dates_to_replace) > 0)
        {
            if ($return_bug_detected)
                return true;

            if (!isset($start_index))
                $start_index = 0;

            if (!isset($end_index))
                $end_index = count($data_array)-1;

            $replace_count = 0;
            $time_memory   = 0;
            $date_memory   = null; // memory for next date 
            $date_replaces = 0;
            $date_changed  = false;
            $dates_replaced= [];
            
            for ($index = $start_index; $index <= $end_index; $index++) // look forwards to replace the first occurence in the flashlog, because it replaces with a date for last port 3 time_device without error
            {
                if (isset($data_array[$index]['time_device']))
                {
                    $time_device   = intval($data_array[$index]['time_device']);
                    $dtime_device  = date('Y-m-d H:i:s', $time_device);
                    $date_device   = substr($dtime_device, 0, 10); // YYYY-MM-DD        
                    
                    if (isset($dates_to_replace[$date_device]))
                    {
                        if (!in_array($date_device, $dates_replaced)) // first entry of new date, allow this whole block until date change, or time decrease
                        {
                            Log::debug("fixBugRtcMonthIndex $index fl=$this->id, found new date $date_device to replace");
                            $dates_replaced[] = $date_device;
                            $date_replaces = 0;
                            $date_changed  = false; 
                            $time_decrease = false;
                            $date_memory   = $date_device;
                        }
                        else
                        {
                            $time_decrease = $time_device < $time_memory ? true : false; // indicate that time increases, because at moment of decrease, replace should stop
                            $time_memory   = $time_device;                          
                            $date_changed  = $date_memory !== null && $date_memory != $date_device ? true : false; // true is new date
                            $date_memory   = $date_device;
                        }

                        // replace date for incorrect RTC dates (only first encountered block)
                        if ($time_decrease || $date_changed || $date_replaces == 96)
                        {
                            Log::debug("fixBugRtcMonthIndex $index fl=$this->id, date_device=$date_device, time_decrease=$time_decrease, date_changed=$date_changed, date_replaces=$date_replaces so do not replace anymore");
                            unset($dates_to_replace[$date_memory]); // skip the next block of this date, because this is the actual correct read out date that should be remained
                        }
                        else
                        {
                            $correct_date                      = $dates_to_replace[$date_device];
                            $dtime_device                      = $correct_date . substr($dtime_device, 10); // corrected date + original time
                            $data_array[$index]['time_device'] = strtotime($dtime_device); // correct time by $device_time
                            $data_array[$index]['time']        = $dtime_device ; // correct time by $device_time
                            $data_array[$index]['time_corr']   = isset($data_array[$index]['time_corr']) ? $data_array[$index]['time_corr'].' + rtc-replace' : 'rtc-replace';
                            
                            $replace_count++;
                            $date_replaces++;

                            Log::debug("fixBugRtcMonthIndex $index fl=$this->id, replaced $date_device with $correct_date => $dtime_device");
                        }
                    }
                }
            }
            
            Log::debug("fixBugRtcMonthIndex fl=$this->id, $replace_count items replaced for fl_id=$this->id start_index=$start_index, end_index=$end_index"); 

            if ($replace_count > 0)
            {
                $this->addMetaData($data_array, true, false, ['fixBugRtcMonthIndex'=>$replace_count], false); // Do NOT fixBugRtcMonthIndex again
                
                // Store corrected data_array to log_file_parsed
                $disk        = env('FLASHLOG_STORAGE', 'public');
                $time        = date("YmdHis");
                $logFileName = "flashlog/sensor_".$this->device_id."_flash_parsed_$time.json";
                $saved       = Storage::disk($disk)->put($logFileName, json_encode($data_array), ['mimetype' => 'application/json']);
                $file_url    = Storage::disk($disk)->url($logFileName);

                $this->log_file_parsed = $file_url;
                $this->save();
            }

        } 
        else
        {
            if ($return_bug_detected)
                return false;

            Log::debug("fixBugRtcMonthIndex fl=$this->id, no dates to replace for start_index=$start_index, end_index=$end_index"); 
        }

        return $saved;
    }

    private function matchFlashLogBlock($block_index, $fl_index, $end_index, $on, $flashlog, $setCount, $device, $log, $db_time, $matches_min, $match_props, $db_records, $show=false, $add_sensordefinitions=true, $use_rtc=true, $last_onoff=false, $correct_data=false, $previous_offset=0)
    {
        $has_matches     = false;
        $block_i         = $on['i'];
        $start_index     = $block_i + 1;
        $interval        = isset($on['measurement_interval_min']) ? intval($on['measurement_interval_min']) : $device->measurement_interval_min; // transmission ratio is not of importance here, because log contains all measurements
        $interval_sec    = $interval * 60; // transmission ratio is not of importance here, because log contains all measurements

        $db_moment       = new Moment($db_time);
        
        $indexes         = max(0, $end_index - $start_index);
        $duration_min    = $interval * $indexes;
        $duration_hrs    = round($duration_min / 60, 1);
        $min_timestamp   = self::$minUnixTime;
        $max_timestamp   = isset($this->created_at) ? strtotime($this->created_at) : time(); // PGe 20250909: used to be time(), but created_at is FL upload date, so should not go beyond that;
        // check if database query should be based on the device time, or the cached time from the 
        $use_device_time = false;

        // get time_device start/end from block data
        $time_device_start = null;
        $time_device_end   = null;
        $time_start_index  = $start_index;
        $time_end_index    = $end_index;
        $device_time_offset= $previous_offset !== 0 ? $previous_offset : null;
        $time_device_last  = 0;
        $upload_time_sec   = 120; // offset seconds from last timestamp to upload 

        $time_corrections  = $this->getTimeCorrections(); // array with unix timestamps as keys and time correction seconds as values to correct flashlog data
        $time_correct_set  = count($time_corrections) > 0 ? true : false;
        //dd($time_corrections);

        // correct device time in last onoff block if it is not close to upload date, or goes beyond the $max_timestamp
        if (($correct_data || $time_correct_set) && $use_rtc) 
        {
            
            for ($index=$end_index; $index >= $start_index; $index--) // look backwards for last port 3 time_device without error
            {
                if (isset($flashlog[$index]['port']) && $flashlog[$index]['port'] == 3 && isset($flashlog[$index]['time_device']))
                {
                    $time_device = intval($flashlog[$index]['time_device']) + $previous_offset;

                    if ($correct_data && $previous_offset)
                    {
                        $flashlog[$index]['time_device'] = $time_device; // correct time by $device_time_offset
                        $flashlog[$index]['time']        = date('Y-m-d H:i:s', $time_device); // correct time by $device_time
                        $flashlog[$index]['time_offset'] = $previous_offset;
                        $flashlog[$index]['time_corr']   = isset($flashlog[$index]['time_corr']) ? $flashlog[$index]['time_corr'].' + prev' : 'prev';
                        $use_device_time                 = true;
                    }
                    
                    // In last block, correct for difference with last time value and upload date ($max_timestamp)
                    if ($correct_data && $last_onoff)
                    {
                        // if (!isset($flashlog[$index]['time_error']))
                        // {
                        //     if ($time_device > $time_device_last)
                        //         $time_device_last = $time_device; // log for checking if whole block time is too_low

                        //     if (!isset($time_device_end))
                        //     {
                        //         $time_device_end = $time_device;
                        //         $time_end_index  = $index;
                                
                        //         if (!isset($device_time_offset) && $time_device_end > $max_timestamp)
                        //         {
                        //             $device_time_offset = $max_timestamp - $time_device_end - $upload_time_sec; // offset negative number, minus 120 sec for upload time
                        //             $time_device_end    = $time_device_end + $device_time_offset;

                        //             //dd($block_index, $time_end_index, $time_device, $time_device_end, $device_time_offset, $max_timestamp, $upload_time_sec, $on, $start_index, $end_index, $flashlog[$start_index-1], $flashlog[$end_index-1]);
                        //         }
                        //     }
                        // }

                        // Correct too_high complete block offset time by interval
                        if (isset($device_time_offset)) 
                        {
                            //$time_device_new = $time_device + $device_time_offset;
                            $time_device_new = $max_timestamp - (($time_end_index - $index) * $interval_sec) - $upload_time_sec;
                            $flashlog[$index]['time_device'] = $time_device_new; // correct time by $device_time_offset
                            $flashlog[$index]['time']        = date('Y-m-d H:i:s', $time_device_new); // correct time by $device_time
                            $flashlog[$index]['time_offset'] = $time_device_new - $time_device; 
                            $flashlog[$index]['time_corr']   = isset($flashlog[$index]['time_corr']) ? $flashlog[$index]['time_corr'].' + down' : 'down';
                            $use_device_time                 = true;
                            // $flashlog[$index]['time_offset'] = $device_time_offset; 
                            unset($flashlog[$index]['time_error']);
                            //dd($block_index, $start_index, $end_index, $time_device-$device_time_offset, $device_time_offset, $device_time_offset, $index, $flashlog[$index]);
                        }
                    }

                    
                }
            }

            // Correct too_low complete block offset time by interval
            // if ($correct_data && $last_onoff && $time_device_last < $max_timestamp - 3600)
            // {
            //     $device_time_offset = $max_timestamp - $time_device_last - $upload_time_sec;
            //     //dd($on, $start_index, $end_index, $time_device_last, date('Y-m-d H:i:s', $time_device_last), $time_device_end, date('Y-m-d H:i:s', $time_device_end));

            //     for ($index=$end_index; $index >= $start_index; $index--)  // correct time backwards from last index
            //     {
            //         if (isset($flashlog[$index]['port']) && $flashlog[$index]['port'] == 3 && isset($flashlog[$index]['time_device']))
            //         {
            //             $time_device_new = intval($flashlog[$index]['time_device']) + $device_time_offset;
            //             $flashlog[$index]['time_device'] = $time_device_new; // correct time by $device_time_offset
            //             $flashlog[$index]['time']        = date('Y-m-d H:i:s', $time_device_new); // correct time by $device_time
            //             $flashlog[$index]['time_offset'] = $device_time_offset; 
            //             $flashlog[$index]['time_corr']   = isset($flashlog[$index]['time_corr']) ? $flashlog[$index]['time_corr'].' + up' : 'up';
            //             $use_device_time                 = true;
            //             unset($flashlog[$index]['time_error']);
            //         }
            //     }
            //     //dd($time_end_index, date('Y-m-d H:i:s', $time_device_end), $on, $start_index, $end_index, $time_device_last, date('Y-m-d H:i:s', $time_device_last), $time_device_end, date('Y-m-d H:i:s', $time_device_end));
            // }

            // Correct device time in same block, if the time goes back by 24 hours (caused by the RTC jump?)
            $block_time_offset = 0;
            $block_manual_offset = 0;
            $apply_time_shift_offset = env('FLASHLOG_FIX_TIME_JUMP', true);
            for ($index=$start_index; $index <= $end_index; $index++) 
            {
                if (isset($flashlog[$index]['time_device']) && !isset($flashlog[$index]['time_error']) && $flashlog[$index]['port'] == 3)
                {
                    $time_device      = intval($flashlog[$index]['time_device']) + $previous_offset;
                    $time_device_next = $index < $end_index && isset($flashlog[$index+1]['time_device']) ? intval($flashlog[$index+1]['time_device']) + $previous_offset : $time_device;
                    
                    // Apply manual time corrections
                    if ($time_correct_set)
                    {
                        foreach ($time_corrections as $timestamp => $correct_sec)
                        {
                            if ($timestamp <= $time_device)
                            {
                                $block_manual_offset = $correct_sec;
                                break;
                            }
                        }
                    }

                    if ($block_time_offset !== 0 || $block_manual_offset !== 0)
                    {
                        $corr_msg                        = $block_time_offset !== 0 && $block_manual_offset !== 0 ? 'corr + step' : ($block_manual_offset !== 0 ? 'corr' : 'step');
                        $time_device                    += $block_time_offset + $block_manual_offset;
                        $flashlog[$index]['time_device'] = $time_device;
                        $flashlog[$index]['time_offset'] = $block_time_offset;
                        $flashlog[$index]['time_corr']   = isset($flashlog[$index]['time_corr']) ? $flashlog[$index]['time_corr']." + $corr_msg" : $corr_msg;
                        $use_device_time                 = true;
                        
                        if ($time_device < $max_timestamp)
                            $flashlog[$index]['time'] = date('Y-m-d H:i:s', $time_device); // correct time by $device_time
                        else
                            $flashlog[$index]['time_error'] = 'beyond max';
                    }

                    // Detect jumps in time: if step in time it the wrong direction (down in stead of up), correct forwards for this jump
                    if ($apply_time_shift_offset && $correct_data)
                    {
                        $next_time_offset = $time_device - $time_device_next;

                        if (abs($next_time_offset) > 85000 && abs($next_time_offset) < 87400 && $time_device < $max_timestamp && $time_device > self::$minUnixTime) // only detect jumps of 24h back/forward in time (fw 1.5.13 RTC readout bug)
                        {
                            $block_time_offset = $next_time_offset + $interval_sec - $previous_offset;
                            Log::debug("matchFlashLogBlock block_i=$block_index, fl_i=$fl_index, i=$index, previous_offset=$previous_offset, next_time_offset=$next_time_offset, block_time_offset=$block_time_offset");
                            $device_time_offset= $block_time_offset; // make sure step time offset is pushed to the next block
                        }
                    }
                }
                else
                {
                    Log::debug("matchFlashLogBlock NOT CORR block_i=$block_index, fl_i=$fl_index, i=$index, previous_offset=$previous_offset, block_time_offset=$block_time_offset");
                }
            }
        }


        $firmware_version  = isset($on['firmware_version']) ? $on['firmware_version'] : null;
        $transmission_ratio= isset($on['measurement_transmission_ratio']) ? $on['measurement_transmission_ratio'] : null;

        // Cap start and end index by (corrected) device time
        if (!isset($time_device_start) || !isset($time_device_end))
        {
            for ($index=$start_index; $index <= $end_index; $index++) 
            {
                if (isset($flashlog[$index]['time_device']) && !isset($flashlog[$index]['time_error']) && $flashlog[$index]['port'] == 3)
                {
                    $time_device = intval($flashlog[$index]['time_device']);

                    // cap start index
                    if ($time_device_start === null && $time_device > self::$minUnixTime && $time_device < $max_timestamp)
                    {
                        $time_device_start = $time_device;
                        $time_start_index  = $index;
                    }
                    // cap end index
                    if ($time_device_start !== null && $time_device > self::$minUnixTime && $time_device < $max_timestamp)
                    {
                        $time_device_end = $time_device;
                        $time_end_index  = $index;
                    }
                }
            }
        }

        if (isset($time_device_start))   
        {
            $diff_start_sec= $interval_sec * ($time_start_index - $start_index);
            $device_moment = new Moment($time_device_start - $diff_start_sec);
            $device_time   = $device_moment->format($this->timeFormat);
            if ($time_device_start >= strtotime($db_time) - 60) // db time should be a little later than device time, becuase of lora message delay
            {
                // adjust time to use as start of block from db time to flashlog time
                $db_time         = $device_time;
                $db_moment       = $device_moment;
                $use_device_time = true;
            }
        }
        
        // if ($last_onoff)
        //     dd(['matchFlashLogBlock', 'use_device_time'=>$use_device_time, 'use_rtc'=>$use_rtc, 'db_time'=>$db_time, 'time_device_start'=>date('Y-m-d H:i:s', $time_device_start), 'time_device_end'=>date('Y-m-d H:i:s', $time_device_end)]);

        // // If the device has an RTC, assume that all times match (if valid times)
        if ($use_rtc && $device->rtc && $use_device_time)
        {
            $end_moment  = new Moment($time_device_end);
            $time_end    = $end_moment->format($this->timeFormat);
            $match_first = ['flashlog_index'=>$start_index, 'minute_interval'=>$interval, 'time'=>$db_time];
            // Set time and add weight_kg by calibration 
            $block       = $this->setFlashBlockTimes($match_first, $block_i, $start_index, $end_index, $flashlog, $device, $show, $interval_sec, $add_sensordefinitions, $use_device_time);
            $flashlog    = $block['flashlog'];

            if (isset($block['index_start']))
            {
                $match_feedback_arr = ['time'=>'RTC'];

                if ($last_onoff)
                {
                    if (isset($flashlog[$end_index]['time_clock']))
                        $match_feedback_arr['time_clock'] = $flashlog[$end_index]['time_clock'];

                    if (isset($flashlog[$end_index]['time_corr']))
                        $match_feedback_arr['time_corr'] = $flashlog[$end_index]['time_corr'];

                    if ($device_time_offset !== null)
                        $match_feedback_arr['offset_sec'] = $device_time_offset;
                }

                $log_block = ['block'=>$block_index, 'block_i'=>$block_i, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$start_index, 'db_time'=>$db_time, 'interval_min'=>$interval, 'interval_sec'=>$interval_sec, 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$time_end, 'setCount'=>$block['setCount'], 'matches'=>['matches'=>array_fill(0, $matches_min, $match_feedback_arr)]];

                if (isset($transmission_ratio))
                    $log_block['transmission_ratio'] = $transmission_ratio;

                if (isset($firmware_version))
                    $log_block['fw_version'] = $firmware_version;

                $log[] = $log_block;

                $setCount += $block['setCount'];
                $fl_index = $block['index_end'];
                $db_time  = $time_end;
            }

            return ['has_matches'=>true, 'flashlog'=>$flashlog, 'db_time'=>$db_time, 'log'=>$log, 'fl_index'=>$fl_index, 'setCount'=>$setCount, 'device_time_offset'=>$device_time_offset];
        }        

        // Disable half time checking, because will be solved in matchFlashLogTime
        // set time to 1/2 of interval if > 2 * amount of indexes 
        // if ($indexes > 2 * $db_records && $use_device_time === false) 
        // {
        //     $db_q_time = $db_moment->addMinutes(round($duration_min/2))->format($this->timeFormat);
        //     $db_max    = max($matches_min, min($db_records, $indexes/2));
        // }
        // else
        // {
            $db_q_time = $db_time;
            $db_max    = max($matches_min, min($db_records, $indexes));
        // }

        // matchFlashLogTime returns: ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_data'=>$db_data, 'db_data_count'=>count($db_data), 'matches'=>$matches];

        $matches = $this->matchFlashLogTime($device, $flashlog, $matches_min, $match_props, $start_index, $end_index, $duration_hrs, $interval, $db_q_time, $db_max, $show);
        
        if (isset($matches['matches']))
        {
            $matches_arr = $matches['matches'];
            $match_first = reset($matches_arr); // take first match for matching the time

            // Correct time based on time deviation in matches
            if (isset($match_first['flashlog_index']) && isset($match_first['time']))
            {
                $fl_index   = $match_first['flashlog_index'];
                $match_last = end($matches_arr); // take last match
                //die(print_r($match));

                $match_first_time   = $match_first['db_sec'];// 1620339094
                $match_last_time    = $match_last['db_sec']; // 1620356195
                $match_total_count  = count($matches_arr);   // 20 
                $sec_diff_per_match = ($match_last_time - $match_first_time) / ($match_total_count-1); // 900.0526315789 sec in between
                $max_sec_deviation  = round($interval_sec / 10); // max 90 sec for 900 sec interval

                // Set the difference per time index based on the deviation in the matches found (if smaller than $max_sec_deviation), or set it on the set interval
                if ($device->rtc) // assume that the timing is good
                    $sec_diff_per_index = $interval_sec;
                else if (abs($sec_diff_per_match - $interval_sec) < $max_sec_deviation) // deviation is within $max_sec_deviation (10%), so take $sec_diff_per_match
                    $sec_diff_per_index = $sec_diff_per_match;
                else
                    $sec_diff_per_index = $interval_sec;

                $matches_display    = array_slice($matches_arr, 0, $matches_min);
                $matches['matches'] = $matches_display;
                
                //dd([$match_first_time, $match_last_time, $sec_diff_per_index, $match_first, $match_last]);

                $block    = $this->setFlashBlockTimes($match_first, $block_i, $start_index, $end_index, $flashlog, $device, $show, $sec_diff_per_index, $add_sensordefinitions);
                $flashlog = $block['flashlog'];

                if (isset($block['index_end']))
                {
                    // A match is found
                    $has_matches = true;
                    if ($show)
                        $log[] = ['block'=> $block_index, 'block_i'=>$block_i, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$firmware_version, 'interval_min'=>$interval, 'transmission_ratio'=>$transmission_ratio, 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$block['time_end'], 'setCount'=>$block['setCount'], 'matches'=>$matches, 'dbCount'=>$block['dbCount'], 'sec_diff_per_index'=>$sec_diff_per_index, 'match_first_db_sec'=>$match_first_time, 'match_last_db_sec'=>$match_last_time, 'match_total_count'=>$match_total_count, 'sec_diff_per_match'=>$sec_diff_per_match, 'interval_sec'=>$interval_sec];

                    $setCount += $block['setCount'];
                    $db_time  = $block['time_end'];
                    $fl_index = $block['index_end'];
                }
                else
                {
                    if ($show)
                        $log[] = ['block'=> $block_index, 'block_i'=>$block_i, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$firmware_version, 'interval_min'=>$interval, 'transmission_ratio'=>$transmission_ratio, 'sec_diff_per_match'=>$sec_diff_per_match, 'interval_sec'=>$interval_sec];

                    $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
                }
            }
            else
            {
                //die(print_r($matches));
                if ($show)
                    $log[] = ['block'=> $block_index, 'block_i'=>$block_i, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$firmware_version, 'interval_min'=>$interval, 'transmission_ratio'=>$transmission_ratio, 'no_matches'=>'fl_i and time of match not set', 'match'=>$matches];

                $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
            }
        }
        else
        {
            if ($show)
                $log[] = ['block'=> $block_index, 'block_i'=>$block_i, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$firmware_version, 'interval_min'=>$interval, 'transmission_ratio'=>$transmission_ratio, 'no_matches'=>$matches];

            $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
        }
        return ['has_matches'=>$has_matches, 'flashlog'=>$flashlog, 'db_time'=>$db_time, 'log'=>$log, 'fl_index'=>$fl_index, 'setCount'=>$setCount, 'device_time_offset'=>$device_time_offset];
    }


    /* Flashlog data correction algorithm
    1. Match time ascending database data to Flash log data of BEEP bases
    2. Match a number of ($matches_min) measurements in a row with multiple ($match_props) exact matches to find the correct time of the log data
    3. Align the Flash log time for all 'blocks' of port 3 (measurements) between port 2 (on/off) records  
    4. Add the correct time to the Flashlog file
    */
    private function fillTimeFromInflux($device, $flashlog=null, $save=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $match_days_offset=0, $add_sensordefinitions=true, $use_rtc=true, $correct_data=false)
    {
        $out         = [];
        $matches_min = env('FLASHLOG_MIN_MATCHES', 5); // minimum amount of inline measurements that should be matched 
        $match_props = env('FLASHLOG_MATCH_PROPS', 9); // minimum amount of measurement properties that should match 
        $db_records  = env('FLASHLOG_DB_RECORDS', 80);// amount of DB records to fetch to match each block

        if (isset($matches_min_override))
            $matches_min = $matches_min_override;

        if (isset($match_props_override))
            $match_props = $match_props_override;

        if (isset($db_records_override))
            $db_records = $db_records_override;

        if ($flashlog == null || count($flashlog) < $matches_min) // reject stoo small blocks of data
            return null;

        $fl_index = 0;
        $db_time  = '2019-01-01 00:00:00'; // start before any BEEP bases were live
        $setCount = 0;
        $log      = [];
        $on_offs  = $this->getFlashLogOnOffs($device, $flashlog, $fl_index);
        $device_id= $device->id;
        $matches  = 0;
        $onoff_cnt= count($on_offs); 
        $offset_s = 0; 

        for ($on_i=0 ; $on_i <= $onoff_cnt-1 ; $on_i++) // analyse blocks forwards, to be able to match with db
        {
            $block_index  = $on_i;
            $on           = $on_offs[$on_i];
            $last_onoff   = $block_index == $onoff_cnt-1 ? true : false;
            
            if (isset($on['end_index']))
            {
                $end_index    = $on['end_index'];

                // if ($start_index >= $fl_index)
                // {
                
                $matchBlockResult = $this->matchFlashLogBlock($block_index, $fl_index, $end_index, $on, $flashlog, $setCount, $device, $log, $db_time, $matches_min, $match_props, $db_records, $show, $add_sensordefinitions, $use_rtc, $last_onoff, $correct_data, $offset_s);
                $flashlog         = $matchBlockResult['flashlog'];
                $offset_s         = $matchBlockResult['device_time_offset'];
                $db_time          = $matchBlockResult['db_time'];
                $log              = $matchBlockResult['log'];
                $setCount         = $matchBlockResult['setCount'];
                $fl_index         = $matchBlockResult['fl_index'];
                $matches         += $matchBlockResult['has_matches'] ? 1 : 0;
                // }
                // else
                // {
                // if ($show)
                //     $log[] = ['block'=> $block_index, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$firmware_version, 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>'start_index < fl_index'];
                // }
                //Log::debug(['fillTimeFromInflux', 'device_id'=>$device->id, 'use_rtc'=>$use_rtc, 'matches'=>$matches, 'db_time'=>$db_time, 'log'=>$log]);
            }
        }

        $records_flashlog = 0;
        $records_timed    = 0;
        $records_weight   = 0;
        foreach ($flashlog as $f) 
        {
            if (isset($f['port']) && $f['port'] == 3)
            {
                $records_flashlog++;
                
                if (isset($f['time']))
                    $records_timed++;

                if (isset($f['weight_kg']) || isset($f['w_v']))
                    $records_weight++;
            }

        }
        $time_percentage = $records_flashlog > 0 ? min(100, 100*($records_timed/$records_flashlog)) : 0;
        $weight_percentage = $records_flashlog > 0 ? min(100, 100*($records_weight/$records_flashlog)) : 0;
        $out = ['matching_blocks'=>$matches, 'time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'weight_percentage'=>$weight_percentage, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];

        if ($show)
        {
            $out['log'] = $log;
            $out['matches_min'] = $matches_min;
            $out['match_props'] = $match_props;
            $out['db_records']  = $db_records;
        }

        return $out;
    }

    public static function cleanFlashlogItem($data_array, $unset_time=true)
    {
        unset(
            $data_array['payload_hex'],
            $data_array['pl'],
            $data_array['len'],
            $data_array['vcc'],
            $data_array['pl_bytes'],
            $data_array['beep_base'],
            $data_array['weight_sensor_amount'],
            $data_array['ds18b20_sensor_amount'],
            $data_array['port'],
            $data_array['bat_perc'],
            $data_array['fft_bin_amount'],
            $data_array['fft_start_bin'],
            $data_array['fft_stop_bin']
        );
        
        if ($unset_time)
            unset(
                $data_array['i'],
                $data_array['minute_interval'],
                $data_array['minute'],
                $data_array['time_clock'],
                $data_array['time_device'],
                $data_array['time_error'],
                $data_array['time_corr'],
                $data_array['time_offset'],
            );

        return $data_array;
    }

    public static function cleanDbDataItem($data_array)
    {
        $clean_d = array_filter($data_array);
        unset(
            $clean_d['hardware_id'],
            $clean_d['device_name'],
            $clean_d['apiary_id'],
            $clean_d['hive_id'],
            $clean_d['user_id'],
            $clean_d['rssi'],
            $clean_d['key'],
            $clean_d['snr'],
        );
        
        return $clean_d;
    }

    public static function insertAt(array $array, int $position, $key, $value): array {
        return array_slice($array, 0, $position, true)
             + [$key => $value]
             + array_slice($array, $position, null, true);
    }

    public static function exportData(&$data, $name, $csv=true, $separator=',', $link_override=false, $validate_time=false, $min_unix_ts=null, $max_unix_ts=null, $csv_columns=null)
    {
        $link     = $link_override ? $link_override : env('FLASHLOG_EXPORT_LINK', true);
        $time_min = isset($min_unix_ts) ? $min_unix_ts : self::$minUnixTime;
        $time_max = isset($max_unix_ts) ? $max_unix_ts : time();

        //dd($name, gettype($data));
        Log::debug("Export data $name csv=$csv");
        
        if ($data && gettype($data) == 'array' && count($data) > 0)
        {
            $fileBody   = null;
            $first_date = null;
            $last_date  = null;
            $data_count = count($data);
            $date_times = []; // check which date times (rounded on minute) are already set
            
                                 
            if ($csv)
            {
                // format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Exclude the 'sensor' and 'key' columns
                $header_arr   = null;
                $header_count = 0;
                $csv_body     = [];

                // create $header_arr
                if (empty($csv_columns))
                {
                    foreach ($data as $key => $data_item)
                    {
                        $param_count = count($data_item);
                        if ($param_count > $header_count)
                        {
                            $header_count = $param_count;
                            $header_arr   = self::cleanFlashlogItem(array_keys($data_item), true);
                            unset($header_arr['time']);
                            array_unshift($header_arr, 'time'); // put time first
                        }
                    }
                } 
                else
                {
                    $header_arr = $csv_columns;
                }
                Log::debug("Export CSV headers: ".implode(',', $header_arr));

                if (isset($header_arr) && gettype($header_arr) == 'array')
                {
                    // Run backwards through the data, because time can be adjusted backwards, then the new (and correct) data if in the lower end of the file 
                    for ($i=$data_count-1; $i >= 0; $i--) 
                    { 
                        $data_item       = $data[$i];
                        $data_item_count = count($data_item);
                        if ($data_item_count > 2 && (!isset($data_item['port']) || $data_item['port'] == 3)) // min 2 data items + time, and is port defined, should be port 3
                        {
                            //change order of time
                            $data_time = null;
                            $data_ts   = null;
                            $data_time_utc = '';
                            
                            if (isset($data_item['time']) && !isset($data_item['time_error']))
                            {
                                $data_time = $data_item['time'];
                                $data_ts   = strtotime($data_time);
                                unset($data_item['time']);
                                
                                // Make time with timezone GMT (Z) and T for time
                                $data_time_utc = str_replace(' ', 'T', $data_time);

                                if (strlen($data_time_utc) == 19 && substr($data_time_utc, -1) != 'Z')
                                    $data_time_utc .= 'Z'; // Format as Influx time + UTC timezone
                                
                                // Add data point to date_arr and set frist/last data date
                                if ($data_ts >= $time_min && $data_ts < $time_max) // time is set (also allow previously parsed Flashlogs without RTC), or time_device should be correctly set
                                {
                                    if ($last_date === null)
                                        $last_date = $data_time; // update until last item with date

                                    $first_date = $data_time;
                                }
                            }
                            
                            if ($validate_time == false || (isset($data_ts) && $data_ts >= $time_min && $data_ts < $time_max))
                            {
                                $date_time_round_min = $data_ts; // Round to minute, to store only 1 value per minute: YYYY-MM-DD HH:mm (leave :ss)

                                if (!in_array($date_time_round_min, $date_times))
                                {
                                    $data_item_clean = self::cleanFlashlogItem($data_item, true);
                                    $data_item_clean['time']   = $data_time_utc;
                                    $data_item_clean['source'] = isset($data_item['db']) ? 'db' : 'fl';

                                    // Write each row, aligning to predefined columns
                                    $row = [];
                                    foreach ($header_arr as $m_abbr)
                                    {
                                        $row[] = isset($data_item_clean[$m_abbr]) ? $data_item_clean[$m_abbr] : ''; // fill missing with blank
                                    }
                                    
                                    // Add item to $csv_body
                                    array_unshift($csv_body, implode($separator, $row)); // because of walking backwards through data, prepend to array to have the final array time ascending again
                                                                    
                                    // Register data_ts in date_times to not overwrite
                                    $date_times[] = $date_time_round_min;
                                }
                            }
                        }
                    }

                    $data_count = count($csv_body);
                    Log::debug("Export data count=$data_count");



                    // format CSV header
                    $csv_head_str = []; // Header names (incl. unit)
                    $csv_head_row = "";

                    foreach ($header_arr as $header) 
                    {
                        if ($header == 'time')
                        {
                            $col_head = 'Time';
                        }
                        else if ($header == 'w_v')
                        {
                            $col_head = 'Raw weight measurement (w_v)';
                        }
                        else if ($header == 'source')
                        {
                            $col_head = 'Data source';
                        }
                        else
                        {
                            $meas       = Measurement::where('abbreviation', $header)->first();
                            $pq_unit    = $meas ? $meas->pq_name_unit() : '-';
                            $col_head   = $pq_unit == '-' ? $header : "$pq_unit ($header)";
                        }

                        $csv_head_str[] = $col_head;
                    }
                    $csv_head_row = '"'.implode('"'.$separator.'"', $csv_head_str).'"'."\r\n";

                    // Sort array by time ascending
                    sort($csv_body); // since time column is first, use this to sort
                    Log::debug("Export data sorted ascending");

                    // format CSV file body
                    $fileBody = $csv_head_row.implode("\r\n", $csv_body);
                }
            }
            else
            {
                $fileBody = $data; // return json as body
            }
            //dd($name, $first_date, $last_date);
            unset($date_times);

            if ($link)
            {
                if (isset($fileBody) && $fileBody !== '')
                {
                    $disk     = env('EXPORT_STORAGE', 'public');
                    $file_ext = $csv ? '.csv' : '.json';
                    $file_mime= $csv ? 'text/csv' : 'application/json';
                    $file_date= (isset($first_date) ? substr($first_date, 0, 10) : '').'-'.(isset($last_date) ? substr($last_date, 0, 10) : '');
                    $filePath = 'exports/flashlog/beep-export-'.$name.'-'.$file_date.'-'.Str::random(10).$file_ext;
                    $filePath = str_replace(' ', '', $filePath);

                    Storage::disk($disk)->put($filePath, $fileBody, ['mimetype' => $file_mime]);
                    return ['link'=>Storage::disk($disk)->url($filePath)];
                }
            }
            else
            {
                return $fileBody;
            }
        }
        return ['error'=>'export_not_saved'];
    }

    public function addMetaData($data, $validate_time=false, $only_return_meta_data=false, $add_items=null, $fixBugRtcMonthIndex=true)
    {
        Log::debug("addMetaData fl=$this->id, validate_time=$validate_time, only_return_meta_data=$only_return_meta_data, fixBugRtcMonthIndex=$fixBugRtcMonthIndex, add_items=".json_encode($add_items)); 

        $saved = false;
        
        if ($data && gettype($data) == 'array' && count($data) > 0)
        {
            $time_min = self::$minUnixTime;
            $time_max = time();
            $first_date = null; 
            $last_date  = null; 
            $data_days  = null;
            $data_days_w= null;
            $data_count = count($data);
            $port2_msg  = 0;
            $port2_dates= [];
            $port2_dts_mn= null;
            $port2_dts_mx= null;
            $port3_dts_mn= null;
            $port3_dts_mx= null;
            $port3_ts_mn= null;
            $port3_ts_mx= null;
            $port3_msg  = 0;
            $firmwares  = [];
            $time_errs  = [];
            $time_e_cnt = 0;
            $time_clock = [];
            $weight_arr = []; // array of weight measurements
            $date_arr   = []; // array with date (YYYY-MM-DD) as key and amount of valid data points (timestamps with weight and time set) as value
            $date_arr_bv= []; // array with date (YYYY-MM-DD) as key and minimum battery voltage of valid data points (timestamps) as value
            $date_times = []; // check which date times (rounded on minute) are already set
            $batVoltage = null;  
            $batLowCount= 0;
            $batLowBlock= 0;
            $batLowFlag = false;

            for ($i=$data_count-1; $i >= 0; $i--) 
            { 
                $data_item   = $data[$i];
                $time_device = isset($data_item['time_device']) ? intval($data_item['time_device']) : null;

                if (isset($data_item['port'])) 
                {
                    $data_time = null;
                    $data_ts   = null;
                        
                    if (!isset($data_item['time_error']) && isset($data_item['time'])) // no error and time set
                    {
                        $data_time = $data_item['time'];
                        $data_ts   = strtotime($data_time);
                        $data_date = substr($data_time, 0, 10);
                        if (!isset($date_arr[$data_date]))
                            $date_arr[$data_date] = ['t'=>0, 'w'=>0, 'bv'=>null, 'port2'=>0, 'port2_times_device'=>[]];

                    }

                    // Log clock types
                    if (isset($data_item['time_clock']))
                    {
                        $time_clock_msg = $data_item['time_clock'];
                        if (!isset($time_clock[$time_clock_msg]))
                            $time_clock[$time_clock_msg] = 0;

                        $time_clock[$time_clock_msg]++;
                    }

                    // Log time errors
                    if (isset($data_item['time_error']))
                    {
                        $time_error_msg = $data_item['time_error'];
                        if (!isset($time_errs[$time_error_msg]))
                            $time_errs[$time_error_msg] = 0;

                        $time_errs[$time_error_msg]++;
                    }

                    if ($data_item['port'] == 2)
                    {
                        $port2_msg++;

                        // Log min time
                        if (isset($time_device)){
                            if ($port2_dts_mn === null)
                                $port2_dts_mn = $time_device;

                            if ($time_device < $port2_dts_mn)
                                $port2_dts_mn = $time_device;
                        }

                        // Log max time
                        if (isset($time_device)){
                            if ($port2_dts_mx === null)
                                $port2_dts_mx = $time_device;

                            if ($time_device > $port2_dts_mx)
                                $port2_dts_mx = $time_device;
                        }

                        if (isset($data_date))
                        {
                            $dtime_device = date('Y-m-d H:i:s', $time_device);
                            $date_arr[$data_date]['port2']++;
                            array_unshift($date_arr[$data_date]['port2_times_device'], "[".$data_item['i']."] $time_device: $dtime_device"); // push to front of array, so it in ascending order again
                        }

                        if (isset($data_item['firmware_version']))
                        {
                            $dtime_device = date('Y-m-d H:i:s', $time_device);
                            $firmwares[$i] = $data_item['firmware_version'] . " @ $dtime_device";
                        }

                    }
                    else if ($data_item['port'] == 3)
                    {
                        //change order of time
                        $port3_msg++;
                        
                        // Log min time
                        if (isset($data_ts)){
                            if ($port3_ts_mn === null)
                                $port3_ts_mn = $data_ts;

                            if ($data_ts < $port3_ts_mn)
                                $port3_ts_mn = $data_ts;
                        }

                        // Log max time
                        if (isset($data_ts)){
                            if ($port3_ts_mx === null)
                                $port3_ts_mx = $data_ts;

                            if ($data_ts > $port3_ts_mx)
                                $port3_ts_mx = $data_ts;
                        }
                        
                        // Log min device time
                        if (isset($time_device)){
                            if ($port3_dts_mn === null)
                                $port3_dts_mn = $time_device;

                            if ($time_device < $port3_dts_mn)
                                $port3_dts_mn = $time_device;
                        }

                        // Log max device  time
                        if (isset($time_device)){
                            if ($port3_dts_mx === null)
                                $port3_dts_mx = $time_device;

                            if ($time_device > $port3_dts_mx)
                                $port3_dts_mx = $time_device;
                        }

                        // Log port 3 time errors
                        if (isset($data_ts) && ($data_ts < $time_min || $data_ts > $time_max))
                            $time_e_cnt++;

                        // Log battery voltage
                        if (isset($data_item['bv']))
                        {
                            $batVoltage = $data_item['bv'];
                            if ($batVoltage < 2.7)
                            {
                                if (!$batLowFlag)
                                    $batLowBlock++; // change of flag

                                $batLowFlag = true;
                                $batLowCount++;
                            } 
                            else if ($batVoltage > 2.9) // 0.2V hysteresis to avoid counting too many blocks
                            {
                                $batLowFlag = false;
                            }
                        }

                        if ( $validate_time == false || (isset($data_ts) && $data_ts >= $time_min && $data_ts < $time_max))
                        {
                            $date_time_round_min = $data_ts; // Round to minute, to store only 1 value per minute: YYYY-MM-DD HH:mm (leave :ss)

                            if (!in_array($date_time_round_min, $date_times))
                            {
                                $weight_set = false;
                                
                                if (isset($data_item['weight_kg']))
                                {
                                    $weight_arr[] = $data_item['weight_kg'];
                                    $weight_set   = true; // for counting valid data points
                                }

                                // Add data point to date_arr and set last/first data date
                                if (isset($data_time))
                                {
                                    if (!isset($time_device) || ($time_device >= $time_min && $time_device < $time_max)) // time is set (also allow previously parsed Flashlogs without RTC), or time_device should be correctly set
                                    {
                                        if ($last_date === null)
                                            $last_date = $data_time; // update until last item with date
                                        
                                        $first_date = $data_time;
                                    }

                                    $date_arr[$data_date]['t']++;
                                    
                                    if ($weight_set)
                                        $date_arr[$data_date]['w']++; // count 1 data point if weight AND time are set

                                    if ($date_arr[$data_date]['bv'] === null || ($batVoltage !== null && $batVoltage < $date_arr[$data_date]['bv']))
                                        $date_arr[$data_date]['bv'] = $batVoltage; // register lowest battery voltage of the day
                                }       

                                // Register data_ts in date_times to not overwrite
                                $date_times[] = $date_time_round_min;
                            }
                        }
                    }
                }
            }
            if (isset($date_arr) && count($date_arr) > 0)
            {
                // sort because keys are in reverse order by running backwards through $data
                ksort($date_arr);

                // count percentage of max data per day
                $max_data_per_day = 96;
                $data_days        = 0;
                $data_days_w      = 0;

                foreach ($date_arr as $d => $date_totals)
                {
                    $cnt_time_only   = $date_totals['t']; 
                    $cnt_time_weight = $date_totals['w'];  // weight AND time set
                    $lowest_bv       = $date_totals['bv']; // lowest day battery voltage

                    if ($lowest_bv !== null)
                        $date_arr_bv[$d] = $lowest_bv;

                    $date_arr[$d]    = $cnt_time_weight;   // replace date array with valid values
                    $day_fraction    = $cnt_time_only > $max_data_per_day ? 1 : $cnt_time_only / $max_data_per_day; // 0-1
                    $day_fraction_w  = $cnt_time_weight > $max_data_per_day ? 1 : $cnt_time_weight / $max_data_per_day; // 0-1
                    $data_days      += $day_fraction;
                    $data_days_w    += $day_fraction_w;

                    if ($date_totals['port2'] > 0)
                        $port2_dates[$d] = $date_totals['port2_times_device'];
                }
            }

            $batLowPerc  = $batLowCount > 0 && $port3_msg > 0 ? round(100 * $batLowCount / $port3_msg, 1) : 0;
            $timeErrPerc = $time_e_cnt > 0  && $port3_msg > 0 ? round(100 * $time_e_cnt / $port3_msg, 1) : 0;
            $meta_data   = [
                'port2_msg'=>$port2_msg, 
                'port3_msg'=>$port3_msg, 
                'data_days'=>$data_days, 
                'data_days_weight'=>$data_days_w,
                'bat_low_perc'=>$batLowPerc,
                'bat_low_blocks'=>$batLowBlock,
                'time_err_perc'=>$timeErrPerc
            ];

            if (count($firmwares) > 0)
                $meta_data['firmwares'] = $firmwares;

            if (count($port2_dates) > 0)
                $meta_data['port2_times_device'] = $port2_dates;

            if (isset($port2_dts_mn))
                $meta_data["port2_device_time_first"] = date('Y-m-d H:i:s', $port2_dts_mn);

            if (isset($port2_dts_mx))
            {
                $meta_data["port2_device_time_last"] = date('Y-m-d H:i:s', $port2_dts_mx);
                if ($port2_dts_mx > $time_max)
                    $meta_data["time_off"] = true;
            }

            if (isset($port3_dts_mn))
                $meta_data["port3_device_time_first"] = date('Y-m-d H:i:s', $port3_dts_mn);

            if (isset($port3_dts_mx))
                $meta_data["port3_device_time_last"] = date('Y-m-d H:i:s', $port3_dts_mx);

            if (isset($port3_ts_mn))
                $meta_data["port3_time_first"] = date('Y-m-d H:i:s', $port3_ts_mn);

            if (isset($port3_ts_mx))
                $meta_data["port3_time_last"] = date('Y-m-d H:i:s', $port3_ts_mx);

            if (count($time_clock) > 0)
            {
                foreach($time_clock as $msg => $msg_cnt)
                    $meta_data["time_clock_$msg"] = $msg_cnt;
            }

            if (count($time_errs) > 0)
            {
                foreach($time_errs as $err => $err_cnt)
                    $meta_data["time_err_$err"] = $err_cnt;
            }

            if (count($weight_arr) > 0)
                $meta_data['weight_kg'] = CalculationModel::calculateBoxplot($weight_arr);

            if (count($date_arr) > 0 && array_sum($date_arr) > 0)
                $meta_data['valid_data_points'] = $date_arr;

            if (count($date_arr_bv) > 0)
                $meta_data['lowest_bv'] = $date_arr_bv;

            if (is_array($add_items))
            {
                foreach ($add_items as $key => $value)
                    $meta_data[$key] = $value;
            }

            if ($only_return_meta_data)
            {
                return array_merge(['log_date_start'=>$first_date, 'log_date_end'=>$last_date], $meta_data);
            }

            // Default, save meta to Flashlog
            $this->log_date_start       = $first_date;
            $this->log_date_end         = $last_date;
            $this->meta_data            = $meta_data; // first store meta data on flashlog to use in fixBugRtcMonthIndex
            $meta_data['rtc_bug']       = $this->fixBugRtcMonthIndex(null, true); // indicate bug
            $this->meta_data            = $meta_data;
            $this->logs_per_day         = $this->getLogPerDay();
            $saved = $this->save();


            // Fix RTC error before saving
            if ($fixBugRtcMonthIndex)
                $this->fixBugRtcMonthIndex($data);

        }
        return $saved;
    }

    // from log_file_parsed property
    public function addMetaToFlashlog($flashlog_array = null)
    {
        $data_array = null;
        if (isset($flashlog_array) && is_array($flashlog_array) && count($flashlog_array) > 0)
        {
            $data_array = $flashlog_array;
        }

        if (empty($data_array) && isset($this->log_parsed)) // use parsed log file to generate CSV
        {
            $flashlog_parsed_text = $this->getFileContent('log_file_parsed');
            if (!empty($flashlog_parsed_text))
            {
                $data_array = json_decode($flashlog_parsed_text, true);
            }
        }

        if (!empty($data_array))
        {
            // Add metadata 
            Log::debug("addMetaToFlashlog $this->id");
            return $this->addMetaData($data_array, true);
        }
        Log::debug("addMetaToFlashlog $this->id Err: no data array");
        return false;
    }


    // from log_file_parsed property
    public function addCsvToFlashlog($flashlog_array = null)
    {
        $data_array = null;
        if (isset($flashlog_array) && is_array($flashlog_array) && count($flashlog_array) > 0)
        {
            $data_array = $flashlog_array;
        }

        if (!isset($data_array) && isset($this->log_parsed)) // use parsed log file to generate CSV
        {
            $flashlog_parsed_text = $this->getFileContent('log_file_parsed');
            if (!empty($flashlog_parsed_text))
            {
                $data_array = json_decode($flashlog_parsed_text, true);
            }
        }

        if (!empty($data_array))
        {
            // Add metadata 
            $this->addMetaData($data_array, true);

            // Save CSV
            $csv_file_name        = "flashlog-$this->id-device-id-$this->device_id-sensor-data";
            $save_output          = FlashLog::exportData($data_array, $csv_file_name, true, ',', true, true); // Research data is also exported with , as separator

            if (isset($save_output['link']))
            {
                $this->csv_url = $save_output['link'];
                return $this->save();
            }
        }
        return false;
    }

}
