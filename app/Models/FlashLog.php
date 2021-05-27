<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MeasurementLoRaDecoderTrait;

use App\Hive;
use App\Device;
use App\User;
use Moment\Moment;
use Storage;

class FlashLog extends Model
{
    use MeasurementLoRaDecoderTrait;
    
    protected $precision  = 's';
    protected $timeFormat = 'Y-m-d H:i:s';

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
    protected $fillable = ['user_id', 'device_id', 'hive_id', 'log_messages', 'log_saved', 'log_parsed', 'log_has_timestamps', 'bytes_received', 'log_file', 'log_file_stripped', 'log_file_parsed', 'log_size_bytes', 'log_erased', 'time_percentage'];

    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function log($data='', $log_bytes=null, $save=true, $fill=false, $show=false)
    {
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
        $sid    = $device->id; 
        $time   = date("YmdHis");

        if ($data)
        {
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
            
            // Split data by 0A02 and 0A03 (0A03 30 1B) 0A0330
            $data  = preg_replace('/0A022([A-Fa-f0-9]{1})0100/', "0A\n022\${1}0100", $alldata);

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
            
            $data    = preg_replace($payload, $replace, $data);

            // fix missing battery hex code
            $data  = preg_replace('/0A03([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})0D/', "0A\n03\${1}1B0D\${2}0D", $data);
            // split error lines
            $data  = preg_replace('/03([A-Fa-f0-9]{90,120})0A([A-Fa-f0-9]{0,4})03([A-Fa-f0-9]{90,120})0A/', "03\${1}0A\${2}\n03\${3}0A", $data);
            $data  = preg_replace('/03([A-Fa-f0-9]{90,120})0A1B([A-Fa-f0-9]{90,120})0A/', "03\${1}0A\n031E1B\${2}0A", $data); // missing 031E
            $data  = preg_replace('/02([A-Fa-f0-9]{76})0A03([A-Fa-f0-9]{90,120})0A/', "02\${1}0A\n03\${2}0A", $data);


            if ($save)
            {
                $logFileName =  $f_dir."/sensor_".$sid."_flash_stripped_$time.log";
                $saved = Storage::disk($disk)->put($logFileName, $data);
                $f_str = Storage::disk($disk)->url($logFileName); 
            }

            $counter = 0;
            $in      = explode("\n", $data);
            $log_min = 0;
            $minute  = 0;
            foreach ($in as $line)
            {
                $counter++;
                $data_array = $this->decode_flashlog_payload($line, $show);
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

                if (in_array('time_device', array_keys($data_array)))
                    $logtm++;

                $out[] = $data_array;
            }

            if ($messages > 0)
            {
                $parsed = true;
                if ($save)
                {
                    $logFileName = $f_dir."/sensor_".$sid."_flash_parsed_$time.json";
                    $saved = Storage::disk($disk)->put($logFileName, json_encode($out));
                    $f_par = Storage::disk($disk)->url($logFileName);
                }
            }
        }

        $erase = $log_bytes != null && $log_bytes == $bytes ? true : false;
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

        // fill time in unknown time data (before fw 1.5.9)
        if ($fill && count($out) > 0)
        {
            $flashlog_filled = $this->fillDataGaps($device->id, $out, $save); // ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];
            
            if ($save && isset($flashlog_filled['flashlog']) && count($flashlog_filled['flashlog']) > 0 && $flashlog_filled['time_insert_count'] > 0)
            {
                $logFileName = $f_dir."/sensor_".$sid."_flash_filled_$time.json";
                $saved = Storage::disk($disk)->put($logFileName, json_encode($flashlog_filled['flashlog']));
                $f_par = Storage::disk($disk)->url($logFileName);
            }
            $result['records_flashlog']  = $flashlog_filled['records_flashlog'];
            $result['time_percentage']   = $flashlog_filled['time_percentage'];
            $result['time_insert_count'] = $flashlog_filled['time_insert_count'];
            $result['records_timed']     = $flashlog_filled['records_timed'];
            $result['time_insert_count'] = $flashlog_filled['time_insert_count'];
            $time_percentage             = $result['time_percentage'];
        }
        else
        {
            $time_percentage = $messages > 0 ? round(100 * $logtm / $messages, 2) : 0;
        }

        // create Flashlog entity
        if ($save)
        {
            if (isset($this->log_erased) == false) // first upload 
            {
                $this->hive_id = $device->hive_id;
                $this->log_erased = $erase;
                $this->log_saved = $saved;
                
            }
            $this->bytes_received = $bytes;
            $this->log_has_timestamps = $logtm > 0 ? true : false;
            $this->log_parsed = $parsed;
            $this->log_size_bytes = $log_bytes;
            $this->log_messages = $messages;
            $this->log_file_stripped = $f_str;
            $this->log_file_parsed = $f_par;
            $this->time_percentage = $time_percentage;
            $this->save();
        }

        return $result;
    }


    // Flashlog parsing functions
    private function getFlashLogOnOffs($flashlog, $start_index=0, $start_time='2018-01-01 00:00:00')
    {
        $onoffs      = [];
        $fl_index    = $start_index;
        $fl_index_end= count($flashlog) - 1;

        for ($i=$fl_index; $i < $fl_index_end; $i++) 
        {
            $f = $flashlog[$i];
            if ($f['port'] == 2 && isset($f['beep_base'])) // check for port 2 messages (switch on/off) in between 'before' and 'after' matches
            {
                $onoffs[$i] = $f;
                $onoffs[$i]['block_count'] = 1;
                if (isset($onoffs[$i-1]))
                {
                    $onoffs[$i]['block_count'] = $onoffs[$i-1]['block_count'] + 1; // count amount of messages in a row
                    unset($onoffs[$i-1]); // make sure only the last on/off message of every block is returned (if it has the same interval)
                }
            }
        }
        return array_values($onoffs);
    }

    private function matchFlashLogTime($device_id, $flashlog, $matches_min=1, $match_props=9, $start_index=0, $start_time='2018-01-01 00:00:00')
    {
        $matches     = [];
        $device      = Device::find($device_id);
        $query       = 'SELECT * FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time > \''.$start_time.'\' ORDER BY time ASC LIMIT 20';
        $db_data     = Device::getInfluxQuery($query);
        $fl_index    = $start_index;
        $fl_index_end= count($flashlog) - 1;

        $database_log = [];
        foreach ($db_data as $d)
        {
            unset($d['rssi']);
            unset($d['snr']);
            unset($d['key']);
            $clean_d = array_filter($d);
            if (count($clean_d) > $match_props && array_sum(array_values($clean_d)) != 0)
                $database_log[] = $clean_d;

            // if (count($database_log) >= $matches_min)
            //     break;
        }

        if ($flashlog == null || count($flashlog) < $matches_min)
            return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'db_start_time'=>$start_time, 'db_measurements'=>count($database_log), 'message'=>'too few flashlog items to match: '.count($flashlog)];

        if (count($database_log) < $matches_min)
            return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'db_start_time'=>$start_time, 'db_measurements'=>count($database_log), 'message'=>'too few database items to match from query: '.$query];

        // look for the measurement value(s) in $database_log in the remainder of $flashlog
        $tries = 0;
        foreach ($database_log as $d)
        {
            for ($i=$fl_index; $i < $fl_index_end; $i++) 
            {
                $f = $flashlog[$i];

                if ($f['port'] == 3 && count($matches) < $matches_min) // keep looking if found matches are < min matches
                {
                    $match = array_intersect_assoc($d, $f);
                    if ($match != null && count($match) >= $match_props)
                    {
                        $fl_index                 = $i;
                        $match['time']            = $d['time'];
                        $match['minute']          = $f['minute'];
                        $match['minute_interval'] = $f['minute_interval'];  
                        $match['flashlog_index']  = $i;
                        $matches[$i]              = $match;
                    }
                    $tries++;
                }

                if (count($matches) >= $matches_min)
                    return $matches;

            }
        }
        return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_measurements'=>$database_log, 'db_data_count'=>count($db_data), 'message'=>'no matches found'];
    }

    private function getFlashBlockStartEndIndex($on_offs, $flashLogIndex)
    {
        $startIndex = -1;
        $endIndex   = -1;

        if (count($on_offs) > 0)
        {
            //die(print_r($on_offs));
            // first match
            foreach ($on_offs as $on)
            {
                $i = $on['i'];

                if ($startIndex == -1 || $i < $flashLogIndex)
                    $startIndex = $i;

                if ($i > $flashLogIndex)
                {
                    $endIndex = $i;
                    return [$startIndex, $endIndex];
                }

            }
        }
        return [$startIndex, $endIndex]; 
    }

    private function setFlashBlockTimes($match, $on_offs, $flashlog)
    {
        if (isset($match) && isset($match['flashlog_index']) && isset($match['minute_interval']) && isset($match['time'])) // set times for current block
        {
            $matchInd= $match['flashlog_index'];
            $indexes = $this->getFlashBlockStartEndIndex($on_offs, $matchInd);
            //die(print_r($indexes));
            $messages= $indexes[1] - $indexes[0];
            $setCount= 0;
            
            if ($indexes[0] > -1 && $messages > 0)
            {
                $blockStaOff = $indexes[0] - $matchInd;
                $blockEndOff = $indexes[1] - $matchInd;
                $matchMinInt = $match['minute_interval'];
                $matchTime   = $match['time'];
                $matchMoment = new Moment($matchTime);
                $matchTime   = $matchMoment->format($this->timeFormat);
                $blockStart  = $matchMoment->addMinutes($blockStaOff * $matchMinInt);
                $blockStaDate= $blockStart->format($this->timeFormat);
                $blockEnd    = $matchMoment->addMinutes($blockEndOff * $matchMinInt);
                $blockEndDate= $blockStart->format($this->timeFormat);

                
                // add time to flashlog block
                $addCounter = 0;
                for ($i=$indexes[0]; $i < $indexes[1]; $i++) 
                { 
                    if (isset($flashlog[$i]['time']) === false)
                    {
                        $startMoment          = new Moment($blockStaDate);
                        $flashlog[$i]['time'] = $startMoment->addMinutes($addCounter * $matchMinInt)->format($this->timeFormat);

                        // check for device_time, set device time if less than 30 seconds off
                        if (isset($flashlog[$i]['device_time']))
                        {
                            $second_deviation = abs($startMoment->from($flashlog[$i]['device_time'])->getSeconds());
                            if ($second_deviation < 30)
                            {
                                $device_time          = new Moment($flashlog[$i]['device_time']);
                                $flashlog[$i]['time'] = $device_time->format($this->timeFormat);
                            }
                        }
                        $setCount++;

                    }
                    $addCounter++;
                }

                //print_r(['time0'=>$flashlog[$indexes[0]]['time'], 'time1'=>$flashlog[$indexes[1]-1]['time'], 'bl_start_i'=>$indexes[0], 'bl_end_i'=>$indexes[1], 'match_time'=>$matchTime, 'mi'=>$matchInd, 'min_int'=>$matchMinInt, 'msg'=>$messages, 'bso'=>$blockStaOff, 'bsd'=>$blockStaDate, 'beo'=>$blockEndOff, 'bed'=>$blockEndDate,'setCount'=>$setCount]);
                
                return ['flashlog'=>$flashlog, 'index_start'=>$indexes[0], 'index_end'=>$indexes[1], 'time_start'=>$blockStaDate, 'time_end'=>$blockEndDate, 'setCount'=>$setCount];
            }
        }
        return ['flashlog'=>$flashlog];
    }

    /* Flashlog data correction algorithm
    1. Match time ascending database data to Flash log data of BEEP bases
    2. Match a number of (3) measurements in a row with multiple (9) exact matches to find the correct time of the log data
    3. Align the Flash log time for all 'blocks' of port 3 (measurements) between port 2 (on/off) records  
    4. Save as a filled file
    */
    private function fillDataGaps($device_id, $flashlog=null, $save=false)
    {
        $matches_min = 3; // minimum amount of inline measurements that should be matched 
        $match_props = 9; // minimum amount of measurement properties that should match 

        if ($flashlog == null || count($flashlog) < $matches_min)
            return $matches;

        $fl_index = 0;
        $db_time  = '2019-01-01 00:00:00'; // start before any BEEP bases were live
        $setCount = 0;
        $log      = [];
        $on_offs  = $this->getFlashLogOnOffs($flashlog, $fl_index);
        //die(print_r($on_offs));

        foreach ($on_offs as $i => $on)
        {
            $on_off_index = $on['i'];
            if ($on_off_index >= $fl_index)
            {
                $start_index = $on_off_index+1;
                $matches = $this->matchFlashLogTime($device_id, $flashlog, $matches_min, $match_props, $start_index, $db_time);
                
                if (count($matches) > 0)
                {
                    $match = reset($matches); // take first match
                    //$match_last = end($matches); // take last match
                    //die(print_r([$match,$match_last]));

                    if (isset($match['flashlog_index']) && isset($match['time']))
                    {
                        $fl_index = $match['flashlog_index'];
                        $block    = $this->setFlashBlockTimes($match, $on_offs, $flashlog);
                        $flashlog = $block['flashlog'];

                        if (isset($block['index_end']))
                        {
                            $log[] = ['on_off_i'=> $i, 'on_off_index'=>$on_off_index, 'flashLogIndex'=>$fl_index, 'firmware_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$block['time_end'], 'setCount'=>$block['setCount'], 'matches'=>$matches];
                            $setCount += $block['setCount'];
                            $db_time  = $block['time_end'];
                            $fl_index = $block['index_end'];
                        }
                        else
                        {
                            $log[] = ['on_off_i'=> $i, 'on_off_index'=>$on_off_index, 'flashLogIndex'=>$fl_index, 'time_start'=>$db_time, 'firmware_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio']];
                        }
                    }
                    else
                    {
                        $log[] = ['on_off_i'=> $i, 'on_off_index'=>$on_off_index, 'flashLogIndex'=>$fl_index, 'time_start'=>$db_time, 'firmware_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>$matches];
                    }
                }
                else
                {
                    $log[] = ['on_off_i'=> $i, 'on_off_index'=>$on_off_index, 'flashLogIndex'=>$fl_index, 'time_start'=>$db_time, 'firmware_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio']];
                }
            }
        }
        //die(print_r($log));

        $records_flashlog = 0;
        $records_timed    = 0;
        foreach ($flashlog as $f) 
        {
            if ($f['port'] == 3)
            {
                $records_flashlog++;
                
                if (isset($f['time']))
                    $records_timed++;
            }

        }
        $time_percentage = $records_flashlog > 0 ? 100 * ($records_timed/$records_flashlog) : 0;
        $out = ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];

        //die(print_r($out));
        //die();
        return $out;
    }

}
