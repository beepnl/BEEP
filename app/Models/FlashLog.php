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
    protected $fillable = ['user_id', 'device_id', 'hive_id', 'log_messages', 'log_saved', 'log_parsed', 'log_has_timestamps', 'bytes_received', 'log_file', 'log_file_stripped', 'log_file_parsed', 'log_size_bytes', 'log_erased', 'time_percentage', 'persisted_days', 'persisted_measurements', 'persisted_block_ids', 'log_date_start', 'log_date_end', 'logs_per_day', 'csv_url', 'meta_data'];
    protected $hidden   = ['device', 'hive', 'user', 'persisted_block_ids'];

    protected $appends  = ['device_name', 'hive_name', 'user_name'];
    protected $casts    = ['meta_data' => 'array'];


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
        $log_days = $this->getLogDays();
        if (isset($log_days) && $log_days > 0 && isset($this->log_messages))
        {
            return round($this->log_messages * (min(100, $this->time_percentage)/100) / $log_days);
        }
        return null;
    }

    public function getTimeLogPercentage($logs_per_day = null)
    {
        if ($logs_per_day === null)
            $logs_per_day = $this->getLogPerDay();

        if (isset($logs_per_day)) // means that $this->log_date_end is set
        {
            $logs_per_day_full = isset($this->device) ? $this->device->getMeasurementsPerDay() : 96;
            $logs_per_day_perc = max(0, min(100, round(100 * $logs_per_day / $logs_per_day_full, 1)));
            return $logs_per_day_perc;
        }
        return 0;
    }

    public function validLog()
    {
        /* validate log if: 
           1. created_at is within 1 hour from last timestamp
           2. log % > 90%: interval 15 min should have 96 msg/day (>86msg = >90%)
        */
        $logs_per_day = $this->getLogPerDay();
        
        if (isset($logs_per_day)) // means that $this->log_date_end is set
        {
            $created_u  = strtotime($this->created_at);
            $last_log_u = strtotime($this->log_date_end);
            if ($last_log_u >= $created_u - env('FLASHLOG_VALID_UPLOAD_DIFF_SEC', 7200))
            {
                $logs_per_day_perc = $this->getTimeLogPercentage();
                if ($logs_per_day_perc >= env('FLASHLOG_VALID_TIME_LOG_PERC', 90))
                    return true;
            }
        }
        return false;
    }

    public function getLogCacheName($fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null)
    {
        return 'flashlog-'.$this->id.'-fill-'.$fill.'-show-'.$show.'-matches-'.$matches_min_override.'-dbrecs-'.$db_records_override; // removed -props-'.$match_props_override.'
    }

    public function log($data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false, $from_cache=true, $match_days_offset=0, $add_sensordefinitions=true, $use_rtc=true)
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
                $data_array['i'] = $counter;

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

            if ($messages > 0)
            {
                $parsed = true;
                if ($save)
                {
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
            $flashlog_filled = $this->fillTimeFromInflux($device, $out, $save, $show, $matches_min_override, $match_props_override, $db_records_override, $match_days_offset, $add_sensordefinitions, $use_rtc); // ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];
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
                    $result["Meta data"] = CalculationModel::arrayToString($this->meta_data, ', ', '', ['valid_data_points']);
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

        for ($i=$fl_index; $i < $fl_index_end; $i++) 
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
                            $onoffs[$i] = $f;
                            $onoffs[$i]['block_count'] = $block_count;

                            // check if previous message is a block start 
                            if (!$device->rtc && isset($onoffs[$i-1]))
                            {
                                $onoffs[$i]['block_count'] = $onoffs[$i-1]['block_count'] + 1; // count amount of messages in a row
                                unset($onoffs[$i-1]); // make sure only the last on/off message of every block is returned (if it has the same interval)
                            }
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
                                $first_p3_mes = $f;

                            $last_p3_mes = $f;
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
            $first_p3_mes['i'] = $first_p3_mes['i'] - 1;
            
            $onoffs[0] = $first_p3_mes;
        }

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
        
        //die(print_r([$start_time, $end_time, $duration_hrs, count($data_count), $db_data_cnt]));

        // get data from the day with max amount of measurements
        $query       = 'SELECT * FROM "sensors" WHERE '.$device->influxWhereKeys().' AND from_flashlog != \'1\' AND time >= \''.$start_time.'\' ORDER BY time ASC LIMIT '.min(1000, max($matches_min, $db_records));
        $db_data     = Device::getInfluxQuery($query, 'flashlog');

        //die(print_r([$start_time, $db_data]));
        
        $database_log  = [];
        $db_first_unix = 0;
        foreach ($db_data as $d)
        {
            $clean_d = array_filter($d);
            unset($clean_d['hardware_id']);
            unset($clean_d['device_name']);
            unset($clean_d['key']);
            unset($clean_d['user_id']);
            unset($clean_d['rssi']);
            unset($clean_d['snr']);

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

    private function setFlashBlockTimes($match, $blockInd, $startInd, $endInd, $flashlog, $device, $show=false, $sec_diff_per_index=null, $add_sensordefinitions=true, $use_device_time=false)
    {
        if (isset($match) && isset($match['flashlog_index']) && isset($match['minute_interval']) && isset($match['time'])) // set times for current block
        {
            $matchInd= $match['flashlog_index'];
            $messages= $endInd - $startInd;
            $setCount= 0;
            
            if ($messages > 0)
            {
                $blockStaOff = $startInd - $matchInd;
                $blockEndOff = $endInd - $matchInd;
                $matchSecInt = isset($sec_diff_per_index) ? $sec_diff_per_index : $match['minute_interval']*60;
                
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
                    $fl      = $flashlog[$i];
                    $fl_time = null;
                    if ($use_device_time)
                    {
                        if (isset($fl['time_device']) && !isset($fl['time_error']))
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
                //     $log = ['setFlashBlockTimes', 'block_i'=>$blockInd, 'time0'=>$flashlog[$startInd]['time'], 'time1'=>$flashlog[$endInd]['time'], 'bl_start_i'=>$startInd, 'bl_end_i'=>$endInd, 'match_time'=>$matchTime, 'mi'=>$matchInd, 'min_int'=>$matchMinInt, 'msg'=>$messages, 'bso'=>$blockStaOff, 'bsd'=>$blockStaDate, 'beo'=>$blockEndOff, 'bed'=>$blockEndDate,'setCount'=>$setCount];
                // }
                //Log::debug(['setFlashBlockTimes', 'device_id'=>$device->id, 'bl_start_i'=>$startInd, 'bl_end_i'=>$endInd, 'match_time'=>$matchTime, 'mi'=>$matchInd, 'msg'=>$messages, 'block_i'=>$blockInd, 'sensor_def'=>$sensor_def->toArray(), 'bsd'=>$blockStaDate, 'bed'=>$blockEndDate, 'setCount'=>$setCount]);
                
                $dbCount = $device->getMeasurementCount($blockStaDate, $blockEndDate);
                // TODO: Add check for every timestamp in DB with matching Flashlog (for bv, w_v, (t_0, t_1, or t_i))
                return ['flashlog'=>$flashlog, 'index_start'=>$startInd, 'index_end'=>$endInd, 'time_start'=>$blockStaDate, 'time_end'=>$blockEndDate, 'setCount'=>$setCount, 'log'=>$log, 'dbCount'=>$dbCount];
            }
        }
        return ['flashlog'=>$flashlog];
    }

    private function matchFlashLogBlock($i, $fl_index, $end_index, $on, $flashlog, $setCount, $device, $log, $db_time, $matches_min, $match_props, $db_records, $show=false, $add_sensordefinitions=true, $use_rtc=true)
    {
        $has_matches     = false;
        $block_index     = $on['i'];
        $start_index     = $block_index+1;
        $interval        = isset($on['measurement_interval_min']) ? intval($on['measurement_interval_min']) : $device->measurement_interval_min; // transmission ratio is not of importance here, because log contains all measurements
        $interval_sec    = $interval * 60; // transmission ratio is not of importance here, because log contains all measurements

        $db_moment       = new Moment($db_time);
        
        $indexes         = max(0, $end_index - $start_index);
        $duration_min    = $interval * $indexes;
        $duration_hrs    = round($duration_min / 60, 1);
        $min_timestamp   = self::$minUnixTime;
        $max_timestamp   = time();

        // check if database query should be based on the device time, or the cached time from the 
        $use_device_time = false;

        // get time_device start/end from block data
        $time_device_start = null;
        $time_device_end   = null;
        $time_start_index  = $start_index;
        $time_end_index    = $end_index;

        // Try to fill by device time
        if ($use_rtc)
        {
            for ($i=$start_index; $i <= $end_index; $i++) 
            {
                if (isset($flashlog[$i]['time_device']) && !isset($flashlog[$i]['time_error']))
                {
                    $time_device = intval($flashlog[$i]['time_device']);
                    if ($time_device_start === null && $time_device > self::$minUnixTime && $time_device < $max_timestamp)
                    {
                        $time_device_start = $time_device;
                        $time_start_index  = $i;
                    }
                    // set end index
                    if ($time_device_start !== null && $time_device > self::$minUnixTime && $time_device < $max_timestamp)
                    {
                        $time_device_end = $time_device;
                        $time_end_index  = $i;
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
        
        //Log::debug(['matchFlashLogBlock', 'use_device_time'=>$use_device_time, 'use_rtc'=>$use_rtc, 'db_time'=>$db_time, 'time_device_start'=>$time_device_start, 'time_device_end'=>$time_device_end]);

        // // If the device has an RTC, assume that all times match (if valid times)
        if ($use_rtc && $device->rtc && $use_device_time)
        {
            $end_moment  = new Moment($time_device_end);
            $time_end    = $end_moment->format($this->timeFormat);
            $match_first = ['flashlog_index'=>$start_index, 'minute_interval'=>$interval, 'time'=>$db_time];
            $block       = $this->setFlashBlockTimes($match_first, $block_index, $start_index, $end_index, $flashlog, $device, $show, $interval_sec, $add_sensordefinitions, $use_device_time);
            $flashlog    = $block['flashlog'];

            if (isset($block['index_start']))
            {
                $log_block = ['block'=>$i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$start_index, 'db_time'=>$db_time, 'interval_min'=>$interval, 'interval_sec'=>$interval_sec, 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$time_end, 'setCount'=>$block['setCount'], 'matches'=>['matches'=>array_fill(0, $matches_min, ['time'=>'RTC'])]];

                if (isset($on['measurement_transmission_ratio']))
                    $log_block['transmission_ratio'] = $on['measurement_transmission_ratio'];

                if (isset($on['firmware_version']))
                    $log_block['fw_version'] = $on['firmware_version'];

                $log[] = $log_block;

                $setCount += $block['setCount'];
                $fl_index = $block['index_end'];
                $db_time  = $time_end;
            }

            return ['has_matches'=>true, 'flashlog'=>$flashlog, 'db_time'=>$db_time, 'log'=>$log, 'fl_index'=>$fl_index, 'setCount'=>$setCount];
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
                if (abs($sec_diff_per_match - $interval_sec) < $max_sec_deviation) // deviation is within $max_sec_deviation (10%), so take $sec_diff_per_match
                    $sec_diff_per_index = $sec_diff_per_match;
                else
                    $sec_diff_per_index = $interval_sec;

                $matches_display    = array_slice($matches_arr, 0, $matches_min);
                $matches['matches'] = $matches_display;
                //die(print_r([$match_first_time, $match_last_time, $sec_diff_per_index, $match_first, $match_last]));

                $block    = $this->setFlashBlockTimes($match_first, $block_index, $start_index, $end_index, $flashlog, $device, $show, $sec_diff_per_index, $add_sensordefinitions);
                $flashlog = $block['flashlog'];

                if (isset($block['index_end']))
                {
                    // A match is found
                    $has_matches = true;
                    if ($show)
                        $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$interval, 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$block['time_end'], 'setCount'=>$block['setCount'], 'matches'=>$matches, 'dbCount'=>$block['dbCount'], 'sec_diff_per_index'=>$sec_diff_per_index, 'match_first_db_sec'=>$match_first_time, 'match_last_db_sec'=>$match_last_time, 'match_total_count'=>$match_total_count, 'sec_diff_per_match'=>$sec_diff_per_match, 'interval_sec'=>$interval_sec];

                    $setCount += $block['setCount'];
                    $db_time  = $block['time_end'];
                    $fl_index = $block['index_end'];
                }
                else
                {
                    if ($show)
                        $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$interval, 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'sec_diff_per_match'=>$sec_diff_per_match, 'interval_sec'=>$interval_sec];

                    $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
                }
            }
            else
            {
                //die(print_r($matches));
                if ($show)
                    $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$interval, 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>'fl_i and time of match not set', 'match'=>$matches];

                $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
            }
        }
        else
        {
            if ($show)
                $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$interval, 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>$matches];

            $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
        }
        return ['has_matches'=>$has_matches, 'flashlog'=>$flashlog, 'db_time'=>$db_time, 'log'=>$log, 'fl_index'=>$fl_index, 'setCount'=>$setCount];
    }


    /* Flashlog data correction algorithm
    1. Match time ascending database data to Flash log data of BEEP bases
    2. Match a number of ($matches_min) measurements in a row with multiple ($match_props) exact matches to find the correct time of the log data
    3. Align the Flash log time for all 'blocks' of port 3 (measurements) between port 2 (on/off) records  
    4. Add the correct time to the Flashlog file
    */
    private function fillTimeFromInflux($device, $flashlog=null, $save=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $match_days_offset=0, $add_sensordefinitions=true, $use_rtc=true)
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
        //die(print_r($on_offs));
        $device_id= $device->id;
        $matches  = 0;

        foreach ($on_offs as $i => $on)
        {
            $end_index    = $i < count($on_offs)-1 ? $on_offs[$i+1]['i']-1 : count($flashlog)-1;
            // if ($start_index >= $fl_index)
            // {
            $matchBlockResult = $this->matchFlashLogBlock($i, $fl_index, $end_index, $on, $flashlog, $setCount, $device, $log, $db_time, $matches_min, $match_props, $db_records, $show, $add_sensordefinitions, $use_rtc);
            $flashlog         = $matchBlockResult['flashlog'];
            $db_time          = $matchBlockResult['db_time'];
            $log              = $matchBlockResult['log'];
            $setCount         = $matchBlockResult['setCount'];
            $fl_index         = $matchBlockResult['fl_index'];
            $matches         += $matchBlockResult['has_matches'] ? 1 : 0;
            // }
            // else
            // {
            // if ($show)
            //     $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>'start_index < fl_index'];
            // }
            //Log::debug(['fillTimeFromInflux', 'device_id'=>$device->id, 'use_rtc'=>$use_rtc, 'matches'=>$matches, 'db_time'=>$db_time, 'log'=>$log]);
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
                $data_array['time_error']
            );

        return $data_array;
    }

    public static function insertAt(array $array, int $position, $key, $value): array {
        return array_slice($array, 0, $position, true)
             + [$key => $value]
             + array_slice($array, $position, null, true);
    }

    public static function exportData($data, $name, $csv=true, $separator=',', $link_override=false, $validate_time=false, $min_unix_ts=null, $max_unix_ts=null)
    {
        $link     = $link_override ? $link_override : env('FLASHLOG_EXPORT_LINK', true);
        $time_min = isset($min_unix_ts) ? $min_unix_ts : self::$minUnixTime;
        $time_max = isset($max_unix_ts) ? $max_unix_ts : time();

        //dd($name, gettype($data));
        
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
                $header_item  = null;
                $header_count = 0;
                $csv_body     = [];

                // Run backwards through the data, because time can be adjusted backwards, then the new (and correct) data if in the lower end of the file 
                for ($i=$data_count-1; $i >= 0; $i--) 
                { 
                    $data_item = $data[$i];
                    if (isset($data_item['port'])) 
                    {
                        if ($data_item['port'] == 3)
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
                                
                                $data_time_utc = str_replace(' ', 'T', $data_time).'Z'; // Format as Influx time + UTC timezone

                                // Add data point to date_arr and set frist/last data date
                                if (!isset($data_item['time_device']) || ($data_item['time_device'] >= $time_min && $data_item['time_device'] < $time_max)) // time is set (also allow previously parsed Flashlogs without RTC), or time_device should be correctly set
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
                                    $data_item       = array_merge(['time'=>$data_time_utc], $data_item); // Time in first column
                                    $data_item_clean = self::cleanFlashlogItem($data_item, true);

                                    // Add missing temperature column (for for combined flashlogs having no t_i at first but afterwards added)
                                    if (!isset($data_item_clean['t_i']))
                                        $data_item_clean = self::insertAt($data_item_clean, 3, 't_i', null); // after w_v

                                    array_unshift($csv_body, implode($separator, $data_item_clean)); // because of walking backwards through data, prepend to array to have the final array time ascending again
                                    
                                    // get biggest headers
                                    $param_count = count($data_item_clean);
                                    if ($param_count > $header_count)
                                    {
                                        $header_count = $param_count;
                                        $header_item  = $data_item_clean;
                                    }
                                    
                                    // Register data_ts in date_times to not overwrite
                                    $date_times[] = $date_time_round_min;
                                }
                            }
                        }
                    }
                }

                if (isset($header_item) && gettype($header_item) == 'array')
                {
                    // format CSV
                    $csv_sens = array_keys($header_item);
                    $csv_head = [];
                    foreach ($csv_sens as $header) 
                    {
                        if ($header == 'time')
                        {
                            $col_head = 'time';
                        }
                        else
                        {
                            $meas       = Measurement::where('abbreviation', $header)->first();
                            $col_head   = !$meas ? $header : $meas->pq_name_unit();
                            $col_head  .= $meas ? " ($header)" : "";
                        }

                        $csv_head[] = $col_head;
                    }
                    $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

                    // format CSV file body
                    $fileBody = $csv_head.implode("\r\n", $csv_body);
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

    public function addMetaData($data, $validate_time=false, $only_return_meta_data=false)
    {
        $time_min = self::$minUnixTime;
        $time_max = time();

        //dd($name, gettype($data));
        
        if ($data && gettype($data) == 'array' && count($data) > 0)
        {
            $first_date = null; 
            $last_date  = null; 
            $data_days  = null;
            $data_count = count($data);
            $port2_msg  = 0;
            $port3_msg  = 0;
            $weight_arr = []; // array of weight measurements
            $date_arr   = []; // array with date (YYYY-MM-DD) as key and amount of valid data points (timestamps) as value
            $date_times = []; // check which date times (rounded on minute) are already set
        
            for ($i=$data_count-1; $i >= 0; $i--) 
            { 
                $data_item = $data[$i];

                if (isset($data_item['port'])) 
                {
                    if ($data_item['port'] == 2)
                    {
                        $port2_msg+= 1;
                    }
                    else if ($data_item['port'] == 3)
                    {
                        //change order of time
                        $port3_msg+= 1;
                        $data_time = null;
                        $data_ts   = null;
                        
                        if (isset($data_item['time']) && !isset($data_item['time_error']))
                        {
                            $data_time = $data_item['time'];
                            $data_ts   = strtotime($data_time);
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
                                    if (!isset($data_item['time_device']) || ($data_item['time_device'] >= $time_min && $data_item['time_device'] < $time_max)) // time is set (also allow previously parsed Flashlogs without RTC), or time_device should be correctly set
                                    {
                                        if ($last_date === null)
                                            $last_date = $data_time; // update until last item with date
                                        
                                        $first_date = $data_time;
                                    }

                                    $data_date = substr($data_time, 0, 10);
                                    if (!isset($date_arr[$data_date]))
                                        $date_arr[$data_date] = 0;
                                    
                                    $date_arr[$data_date] += intval($weight_set); // count 1 data point if weight AND time are set
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

                foreach ($date_arr as $measurement_cnt)
                {
                    $day_fraction = $measurement_cnt > $max_data_per_day ? 1 : $measurement_cnt / $max_data_per_day; // 0-1
                    $data_days   += $day_fraction;
                }
            }
        }

        $meta_data  = ['port2_msg'=>$port2_msg, 'port3_msg'=>$port3_msg, 'data_days'=>$data_days];

        if (count($weight_arr) > 0)
            $meta_data['weight_kg'] = CalculationModel::calculateBoxplot($weight_arr);

        if (count($date_arr) > 0 && array_sum($date_arr) > 0)
            $meta_data['valid_data_points'] = $date_arr;

        if ($only_return_meta_data)
        {
            return array_merge(['log_date_start'=>$first_date, 'log_date_end'=>$last_date], $meta_data);
        }

        // Default, save meta to Flashlog
        $this->log_date_start = $first_date;
        $this->log_date_end   = $last_date;
        $this->meta_data      = $meta_data;
        $this->logs_per_day   = $this->getLogPerDay();
        return $this->save();
    }

    // from log_file_parsed property
    public function addMetaToFlashlog($flashlog_array = null)
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
            return $this->addMetaData($data_array, true);
        }
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
