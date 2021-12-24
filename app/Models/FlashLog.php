<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\MeasurementLoRaDecoderTrait;

use App\Hive;
use App\Device;
use App\User;
use App\Measurement;
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

    public function log($data='', $log_bytes=null, $save=true, $fill=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null, $save_override=false)
    {
        if (!isset($this->device_id) || !isset($this->device))
            return ['error'=>'No device set, cannot parse Flashlog because need device key to get data from database'];

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

        if ($data && isset($sid))
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
            $max_time= time();

            // load device sensor definitions
            $sensor_defs     = $device->activeSensorDefinitions();
            $sensor_defs_all = $device->sensorDefinitions;

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

                // Add time if not present
                if (!isset($data_array['time']) && isset($data_array['time_device']))
                {
                    $logtm++;
                    $ts = intval($data_array['time_device']);

                    if ($ts > 1546297200 && $ts < $max_time) // > 2019-01-01 00:00:00 < now
                    {
                        $time_device = new Moment($ts);
                        $data_array['time'] = $time_device->format($this->timeFormat);
                    }
                }

                // Add sensor definition measurement if not yet present (or if input_measurement_id == output_measurement_id) 
                foreach ($sensor_defs as $sd) 
                {
                    if (isset($sd->output_abbr) && isset($data_array[$sd->input_abbr]) && (!isset($data_array[$sd->output_abbr]) || $sd->input_measurement_id == $sd->output_measurement_id))
                    {
                        $date       = isset($data_array['time']) ? $data_array['time'] : null;
                        $data_array = $device->addSensorDefinitionMeasurements($data_array, $data_array[$sd->input_abbr], $sd->input_measurement_id, $date, $sensor_defs_all);
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
        $time_percentage = $messages > 0 ? round(100 * $logtm / $messages, 2) : 0;

        if ($fill && count($out) > 0)
        {
            $flashlog_filled = $this->fillDataGaps($device, $out, $save, $show, $matches_min_override, $match_props_override, $db_records_override); // ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];
            
            if ($flashlog_filled)
            {
                if (isset($flashlog_filled['log']))
                    $result['log'] = $flashlog_filled['log'];

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
                    if ($save && isset($flashlog_filled['flashlog']) && count($flashlog_filled['flashlog']) > 0 && $flashlog_filled['time_insert_count'] > 0)
                    {
                        $logFileName = $f_dir."/sensor_".$sid."_flash_filled_$time.json";
                        $saved = Storage::disk($disk)->put($logFileName, json_encode($flashlog_filled['flashlog']));
                        $f_par = Storage::disk($disk)->url($logFileName);
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
            if (isset($this->log_size_bytes) == false) // first upload 
                $this->log_size_bytes = $log_bytes;

            if (isset($this->hive_id) == false) // first upload 
                $this->hive_id = $device->hive_id;

            if (isset($this->log_erased) == false) // first upload 
                $this->log_erased = $erase;

            if (isset($this->log_saved) == false) // first upload 
                $this->log_saved = $saved;
            
            $this->bytes_received = $bytes;
            $this->log_has_timestamps = $logtm > 0 ? true : false;
            $this->log_parsed = $parsed;
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

    private function matchFlashLogTime($device_id, $flashlog, $matches_min=1, $match_props=9, $start_index=0, $end_index=0, $start_time='2018-01-01 00:00:00', $db_records=20)
    {
        $matches     = [];
        $device      = Device::find($device_id);
        $query       = 'SELECT * FROM "sensors" WHERE '.$device->influxWhereKeys().' AND time >= \''.$start_time.'\' ORDER BY time ASC LIMIT '.min(500, max($matches_min, $db_records));
        $db_data     = Device::getInfluxQuery($query, 'flashlog');
        $fl_index    = $start_index;
        $fl_index_end= $end_index;
        $fl_items    = max(0, $end_index - $start_index);

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

        if ($flashlog == null || $fl_items < $matches_min)
            return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'db_start_time'=>$start_time, 'db_measurements'=>count($database_log), 'message'=>'too few flashlog items to match: '.$fl_items];

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
        return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_data_count'=>count($db_data), 'message'=>'no matches found'];
        //return ['fl_index'=>$fl_index, 'fl_index_end'=>$fl_index_end, 'fl_match_tries'=>$tries, 'db_start_time'=>$start_time, 'db_measurements'=>$database_log, 'db_data_count'=>count($db_data), 'message'=>'no matches found'];
    }

    private function setFlashBlockTimes($match, $blockInd, $startInd, $endInd, $flashlog, $device, $show=false)
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
                $matchMinInt = $match['minute_interval'];
                $matchTime   = $match['time'];
                $matchMoment = new Moment($matchTime);
                $matchTime   = $matchMoment->format($this->timeFormat);
                $blockStart  = $matchMoment->addMinutes($blockStaOff * $matchMinInt);
                $blockStaDate= $blockStart->format($this->timeFormat);
                $blockEnd    = $matchMoment->addMinutes($blockEndOff * $matchMinInt);
                $blockEndDate= $blockStart->format($this->timeFormat);

                // load device sensor definitions
                $sensor_defs     = $device->activeSensorDefinitions();
                $sensor_defs_all = $device->sensorDefinitions;

                // add time to flashlog block
                $setTimeStart = '';
                $setTimeEnd   = '';
                $addCounter   = 0;
                for ($i=$startInd; $i <= $endInd; $i++) 
                { 
                    $fl = $flashlog[$i];

                    // Add time if not present
                    if (!isset($fl['time']))
                    {
                        $startMoment= new Moment($blockStaDate);
                        $indexMoment= $startMoment->addMinutes($addCounter * $matchMinInt);
                        $fl['time'] = $indexMoment->format($this->timeFormat);

                        // check for time_device, replace time with device time if less than 60 seconds off
                        if (isset($fl['time_device']) && $fl['time_device'] > 1546300800) // unix timestamp > Tue Jan 01 2019 00:00:00 GMT+0000
                        {
                            $second_deviation = abs($indexMoment->getSeconds() - $fl['time_device']);
                            if ($second_deviation < 60)
                            {
                                $time_device = new Moment($fl['time_device']);
                                $fl['time']  = $time_device->format($this->timeFormat);
                            }
                        }
                    }

                    // Add sensor definition measurement if not yet present (or if input_measurement_id == output_measurement_id) 
                    if (isset($fl['time']))
                    {
                        foreach ($sensor_defs as $sd) 
                        {
                            if (isset($sd->output_abbr) && isset($fl[$sd->input_abbr]) && (!isset($fl[$sd->output_abbr]) || $sd->input_measurement_id == $sd->output_measurement_id))
                                $fl = $device->addSensorDefinitionMeasurements($fl, $fl[$sd->input_abbr], $sd->input_measurement_id, $fl['time'], $sensor_defs_all);
                        }
                    }
                    
                    if ($fl['port'] == 3)
                        $setCount++;

                    $flashlog[$i] = $fl;

                    $addCounter++;
                }

                $log = null;
                if ($show)
                {
                    // add request for database values per day
                    $log = ['setFlashBlockTimes', 'block_i'=>$blockInd, 'time0'=>$flashlog[$startInd]['time'], 'time1'=>$flashlog[$endInd]['time'], 'bl_start_i'=>$startInd, 'bl_end_i'=>$endInd, 'match_time'=>$matchTime, 'mi'=>$matchInd, 'min_int'=>$matchMinInt, 'msg'=>$messages, 'bso'=>$blockStaOff, 'bsd'=>$blockStaDate, 'beo'=>$blockEndOff, 'bed'=>$blockEndDate,'setCount'=>$setCount];
                }
                
                $dbCount = $device->getMeasurementCount($blockStaDate, $blockEndDate);
                // TODO: Add check for every timestamp in DB with matching Flashlog (for bv, w_v, (t_0, t_1, or t_i))
                return ['flashlog'=>$flashlog, 'index_start'=>$startInd, 'index_end'=>$endInd, 'time_start'=>$blockStaDate, 'time_end'=>$blockEndDate, 'setCount'=>$setCount, 'log'=>$log, 'dbCount'=>$dbCount];
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
    private function fillDataGaps($device, $flashlog=null, $save=false, $show=false, $matches_min_override=null, $match_props_override=null, $db_records_override=null)
    {
        $out         = [];
        $matches_min = env('FLASHLOG_MIN_MATCHES', 2); // minimum amount of inline measurements that should be matched 
        $match_props = env('FLASHLOG_MATCH_PROPS', 7); // minimum amount of measurement properties that should match 
        $db_records  = env('FLASHLOG_DB_RECORDS', 15);// amount of DB records to fetch to match each block

        if (isset($matches_min_override))
            $matches_min = $matches_min_override;

        if (isset($match_props_override))
            $match_props = $match_props_override;

        if (isset($db_records_override))
            $db_records = $db_records_override;

        if ($flashlog == null || count($flashlog) < $matches_min)
            return null;

        $fl_index = 0;
        $db_time  = '2019-01-01 00:00:00'; // start before any BEEP bases were live
        $setCount = 0;
        $log      = [];
        $on_offs  = $this->getFlashLogOnOffs($flashlog, $fl_index);
        //die(print_r($on_offs));
        $device_id= $device->id;

        foreach ($on_offs as $i => $on)
        {
            $block_index  = $on['i'];
            $start_index  = $block_index+1;
            $end_index    = $i < count($on_offs)-1 ? $on_offs[$i+1]['i']-1 : count($flashlog)-1;
            $interval     = $on['measurement_interval_min'];
            $indexes      = max(0, $end_index - $start_index);
            $duration_min = $interval * $indexes;
            $duration_hrs = round($duration_min / 60, 1);

            if ($start_index >= $fl_index)
            {
                $db_moment = new Moment($db_time);

                if ($indexes > 2 * $db_records) // only set time to 1/2 of interval if > 2 * amount of indexes 
                {
                    $db_q_time = $db_moment->addMinutes(round($duration_min/2))->format($this->timeFormat);
                    $db_max    = max($matches_min, min($db_records, $indexes/2));
                }
                else
                {
                    $db_q_time = $db_time;
                    $db_max    = max($matches_min, min($db_records, $indexes));
                }

                $matches = $this->matchFlashLogTime($device_id, $flashlog, $matches_min, $match_props, $start_index, $end_index, $db_q_time, $db_max);
                
                if (count($matches) > 0)
                {
                    $match = reset($matches); // take first match
                    //$match_last = end($matches); // take last match

                    if (isset($match['flashlog_index']) && isset($match['time']))
                    {
                        $fl_index = $match['flashlog_index'];
                        $block    = $this->setFlashBlockTimes($match, $block_index, $start_index, $end_index, $flashlog, $device);
                        $flashlog = $block['flashlog'];

                        if (isset($block['index_end']))
                        {
                            // A match is found
                            if ($show)
                                $log[] = ['fillDataGaps', 'block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'index_start'=>$block['index_start'], 'index_end'=>$block['index_end'], 'time_start'=>$block['time_start'], 'time_end'=>$block['time_end'], 'setCount'=>$block['setCount'], 'matches'=>$matches, 'dbCount'=>$block['dbCount']];

                            $setCount += $block['setCount'];
                            $db_time  = $block['time_end'];
                            $fl_index = $block['index_end'];
                        }
                        else
                        {
                            if ($show)
                                $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio']];

                            $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
                        }
                    }
                    else
                    {
                        //die(print_r($matches));
                        if ($show)
                            $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>'fl_i and time of match not set', 'match'=>$matches];

                        $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
                    }
                }
                else
                {
                    if ($show)
                        $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>$matches];

                    $db_time = $db_moment->addMinutes($duration_min)->format($this->timeFormat);
                }
            }
            else
            {
                if ($show)
                    $log[] = ['block'=> $i, 'block_i'=>$block_index, 'start_i'=>$start_index, 'end_i'=>$end_index, 'duration_hours'=>$duration_hrs, 'fl_i'=>$fl_index, 'db_time'=>$db_time, 'fw_version'=>$on['firmware_version'], 'interval_min'=>$on['measurement_interval_min'], 'transmission_ratio'=>$on['measurement_transmission_ratio'], 'no_matches'=>'start_index < fl_index'];
            }
        }

        $records_flashlog = 0;
        $records_timed    = 0;
        $records_weight   = 0;
        foreach ($flashlog as $f) 
        {
            if ($f['port'] == 3)
            {
                $records_flashlog++;
                
                if (isset($f['time']))
                    $records_timed++;

                if (isset($f['weight_kg']))
                    $records_weight++;
            }

        }
        $time_percentage = $records_flashlog > 0 ? min(100, 100*($records_timed/$records_flashlog)) : 0;
        $weight_percentage = $records_flashlog > 0 ? min(100, 100*($records_weight/$records_flashlog)) : 0;
        $out = ['time_percentage'=>$time_percentage, 'records_timed'=>$records_timed, 'weight_percentage'=>$weight_percentage, 'records_flashlog'=>$records_flashlog, 'time_insert_count'=>$setCount, 'flashlog'=>$flashlog];

        if ($show)
        {
            $out['log'] = $log;
            $out['matches_min'] = $matches_min;
            $out['match_props'] = $match_props;
            $out['db_records']  = $db_records;
        }

        return $out;
    }

}
