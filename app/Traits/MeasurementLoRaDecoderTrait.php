<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;

use App\Measurement;
use Cache;

/**
 * @group Api\MeasurementLoRaDecoderTrait
 * Measurement device LoRa payload decoding
 */
trait MeasurementLoRaDecoderTrait
{

    private function decode_ttn_payload($data)
    {
        $data_array = [];
        
        if (isset($data['payload_raw']) && isset($data['port'])) // ttn v2 uplink
        {
            $payload    = bin2hex(base64_decode($data['payload_raw']));
            $port       = $data['port'];
            $data_array = $this->decode_beep_payload($payload, $port);

            // add meta data
            $data_array['port'] = $port;
            if (isset($data['hardware_serial']) && !isset($data_array['key']))
                $data_array['key'] = $data['hardware_serial']; // LoRa WAN == Device EUI
            if (isset($data['device_id']) && !isset($data_array['key']))
                $data_array['key'] = $data['device_id']; // hardware ID is "device_id" in TTN v3 downlink 
            if (isset($data['metadata']['gateways'][0]['rssi']))
                $data_array['rssi'] = $data['metadata']['gateways'][0]['rssi'];
            if (isset($data['metadata']['gateways'][0]['snr']))
                $data_array['snr'] = $data['metadata']['gateways'][0]['snr'];
            if (isset($data['metadata']['gateways'][0]['channel']))
                $data_array['lora_channel'] = $data['metadata']['gateways'][0]['channel'];
            if (isset($data['metadata']['data_rate']))
                $data_array['data_rate'] = $data['metadata']['data_rate'];
            if (isset($data['downlink_url']))
                $data_array['downlink_url'] = $data['downlink_url'];


        }
        else if(isset($data['f_port']) && ($data['f_port'] == 6)) // TTN v3 downlink
            {
                Log::info('TTN downlink received:', $data);

                $payload = bin2hex(base64_decode($data['frm_payload'])); 
                Log::info('$payload from downlink:', $payload);

                // $data_array = $this->decode_beep_payload($payload, $port);

                $data_array['port']   = $data['f_port'];
                $data_array['key']    = $data['end_device_ids']['dev_eui'];

                Log::info('full $data_array from downlink:', $data_array);
                Log::info('dev_eui:', $data['end_device_ids']['dev_eui']);

                return $data_array;
            }
        else if (isset($data['end_device_ids']['dev_eui']) && isset($data['end_device_ids']['device_id']) && isset($data['join_accept']['received_at'])) // TTN v3 Join accept
        {
            $data_array['hardware_id'] = $data['end_device_ids']['device_id'];
            $data_array['key'] = $data['end_device_ids']['dev_eui'];

            if (isset($data['end_device_ids']['application_ids']['application_id']) && $data['end_device_ids']['application_ids']['application_id'] == 'beep-base-production')
                $data_array['beep_base'] = true;

        }

        else if (isset($data['end_device_ids']['dev_eui']) && isset($data['uplink_message']['frm_payload']) || isset($data['downlink_message']['frm_payload']) && isset($data['uplink_message']['f_port']) || isset($data['downlink_message']['f_port']) ) // ttn v3 uplink or downlink
        {
            if(isset($data['uplink_message']))    
                    $port = $data['uplink_message']['f_port'];

            if(isset($data['downlink_message']))  
                    $port = $data['downlink_message']['f_port'];
            
            if(isset($data['uplink_message']['decoded_payload']) && (isset($data['uplink_message']['decoded_payload']['payload_fields']) || count($data['uplink_message']['decoded_payload']) > 2)) // uplink: at least has payload fields, or 3 decoded payload fields (not only ['bytes'])
            {
                if (isset($data['uplink_message']['decoded_payload']['payload_fields']))
                    $data_array = $data['uplink_message']['decoded_payload']['payload_fields']; // TTN v3 with defined payload_fields (using decoder)
                else
                    $data_array = $data['uplink_message']['decoded_payload'];
            }

            
            else
            {
                $payload    = bin2hex(base64_decode($data['uplink_message']['frm_payload'])); // TTN v3 uplink with BEEP base payload 
                $data_array = $this->decode_beep_payload($payload, $port);
            }

            $data_array['port']   = $port;
            $data_array['key']    = $data['end_device_ids']['dev_eui'];

            //die(print_r($data_array));

            // add meta data
            if (isset($data['end_device_ids']['device_id']) && !isset($data_array['key']))
                $data_array['key'] = $data['end_device_ids']['device_id']; // LoRa WAN == Device EUI

            if (isset($data['uplink_message']['f_cnt']))
                $data_array['f_cnt'] = $data['uplink_message']['f_cnt'];

            if (isset($data['uplink_message']['rx_metadata'][0]['rssi']))
                $data_array['rssi'] = $data['uplink_message']['rx_metadata'][0]['rssi'];

            if (isset($data['uplink_message']['rx_metadata'][0]['snr']))
                $data_array['snr'] = $data['uplink_message']['rx_metadata'][0]['snr'];

            if (isset($data['uplink_message']['rx_metadata'][0]['location']['longitude']))
                $data_array['lon'] = $data['uplink_message']['rx_metadata'][0]['location']['longitude'];

            if (isset($data['uplink_message']['rx_metadata'][0]['location']['latitude']))
                $data_array['lat'] = $data['uplink_message']['rx_metadata'][0]['location']['latitude'];

            if (isset($data['uplink_message']['settings']['data_rate']['lora']['bandwidth']))
                $data_array['lora_bandwidth'] = $data['uplink_message']['settings']['data_rate']['lora']['bandwidth'];

            if (isset($data['uplink_message']['settings']['data_rate']['lora']['spreading_factor']))
                $data_array['lora_spf'] = $data['uplink_message']['settings']['data_rate']['lora']['spreading_factor'];

            if (isset($data['uplink_message']['settings']['data_rate_index']))
                $data_array['lora_data_rate'] = $data['uplink_message']['settings']['data_rate_index'];

            if (isset($data['uplink_message']['settings']['frequency']))
                $data_array['lora_frequency'] = $data['uplink_message']['settings']['frequency'];

            if (isset($data['uplink_message']['locations']['user']['latitude']))
                $data_array['lat'] = $data['uplink_message']['locations']['user']['latitude'];

            if (isset($data['uplink_message']['locations']['user']['longitude']))
                $data_array['lon'] = $data['uplink_message']['locations']['user']['longitude'];

            if (isset($data['uplink_message']['locations']['user']['altitude']))
                $data_array['alt'] = $data['uplink_message']['locations']['user']['altitude'];

            //die(print_r($data_array));

        }

        return $data_array;
    }

    private function decode_simpoint_payload($data)
    {
        $data_array = [];
        
        if (!isset($data['payload_hex']) || !isset($data['FPort']))
            return $data_array;

        $payload = $data['payload_hex'];
        $port    = $data['FPort'];

        return $this->decode_beep_payload($payload, $port);
    }

    private function decode_kpnthings_payload($data)
    {
        $data_array = [];

        if (!isset($data['payload']) || !isset($data['port']))
            return $data_array;
        
        $payload = $data['payload'];    
        $port    = $data['port'];

        return $this->decode_beep_payload($payload, $port);
    }

    private function decode_helium_payload($data)
    {
        $data_array = [];

        if (!isset($data['payload']) || !isset($data['port']))
            return $data_array;

        $payload = bin2hex(base64_decode($data['payload']));
        $port    = $data['port'];

        return $this->decode_beep_payload($payload, $port);
    }

    private function decode_swisscom_payload($data)
    {
        $data_array = [];
        
        if (!isset($data['payload']) || !isset($data['port']))
            return $data_array;

        $payload = $data['payload'];
        $port    = $data['port'];

        return $this->decode_beep_payload($payload, $port);
    }


    //  03   31                1b0bf10bea64 0a01019889 0400 0c0a00ff008e001d0010000f000e000c000b000900090008 070849160703f8 25 5f5b73d2 0a
    //  type length (49 bytes) payload(78)
    //                         bat          weight     temo fft                                              bme280         time unixts \n
    private function decode_flashlog_payload($flashlog_line, $show=false)
    {
        $p      = strtolower($flashlog_line);
        $port   = hexdec(substr($p, 0, 2));
        $length = hexdec(substr($p, 2, 2));
        $payload= substr($flashlog_line, 4);

        $parsed = $this->decode_beep_payload($payload, $port);
        $parsed['port'] = $port;
        
        if ($show)
        {
            $parsed['len']     = $length;
            $parsed['pl']      = $payload;
            $parsed['pl_bytes']= strlen($payload)/2;
        }

        return $parsed;
    }

    // Calculte 2's complement signed integer from hex value
    private function hexdecs($hex)
    {
        // ignore non hex characters
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
       
        // converted decimal value:
        $dec = hexdec($hex);
       
        // maximum decimal value based on length of hex + 1:
        //   number of bits in hex number is 8 bits for each 2 hex -> max = 2^n
        //   use 'pow(2,n)' since '1 << n' is only for integers and therefore limited to integer size.
        $max = pow(2, 4 * (strlen($hex) + (strlen($hex) % 2)));
       
        // complement = maximum - converted hex:
        $_dec = $max - $dec;
       
        // if dec value is larger than its complement we have a negative value (first bit is set)
        return $dec > $_dec ? -$_dec : $dec;
    }

    private function createOrUpdateDefinition($device, $abbr_in, $abbr_out, $offset=null, $multiplier=null)
    {
        $measurement_in  = Measurement::where('abbreviation',$abbr_in)->first();
        $measurement_out = Measurement::where('abbreviation',$abbr_out)->first();

        if ($measurement_in && $measurement_out)
        {
            $def = $device->sensorDefinitions->where('input_measurement_id', $measurement_in->id)->where('output_measurement_id', $measurement_out->id)->last();
            if ($def && (isset($offset) || isset($multiplier)) ) 
            {
                if (isset($offset))
                    $def->offset = $offset;

                if (isset($multiplier))
                    $def->multiplier = $multiplier;

                $def->save();
            }
            else
            {
                $device->sensorDefinitions()->create(['input_measurement_id'=>$measurement_in->id, 'output_measurement_id'=>$measurement_out->id, 'offset'=>$offset, 'multiplier'=>$multiplier]);
            }
        }
    }

    private function decode_beep_payload($payload, $port)
    {
        $out = [];
        $out['payload_hex'] = $payload;
        // distighuish BEEP base v2 and v3 payload
        
        if ($port != 1) // BEEP base v3 firmware
        {
            $p  = strtolower($payload);
            $pu = strtoupper($payload);
            
            if ($port == 2)
            {
                if (substr($p, 0, 2) == '01' && (strlen($p) == 52 || strlen($p) == 60 || strlen($p) == 70 || strlen($p) == 76 || strlen($p) == 86)) // BEEP base fw 1.3.3+ start-up message)
                {
                    $out['beep_base'] = true;
                    // 0100010003000502935cbdd3ffff94540e0123af9aed3527beee1d000001 (60)
                    // 0100010003000402935685E6FFFF94540E01237A26A67D24D8EE1D000001 (60)
                    // 010001000300050293569434FFFF94540E012385039722D342EE1F0000000803091D0000010A (76 -> 74 + 0A) (or + time = 86)
                    // 0100010005000902C350B359FFFF60090E0123EEC27300DF41EE1D010005 25 607061A9 (70) (60 + time) // From 1.5.9 time is added to startup message
                    // 7ECDD9423C26E3237497841B5F12915F5CD681E554FC1C2B7466ACBBEDE44C8670162B

                    //                                                 0e01236dada5c40a28ee
                    // 01 00 01 00 03 00 04 02 93 56 85 E6 FF FF 94 54 0E 01 23 7A 26 A6 7D 24 D8 EE 1D 00 00 01 25 60 70 61 A9 
                    // 0  1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 (length == 35 bytes = 70 char)
                    //    pl fw version     hw version                 ATTEC ID (14)                 app config  Time


                    if (substr($p, 0, 2) == '01')
                        $out['firmware_version'] = hexdec(substr($p, 2, 4)).'.'.hexdec(substr($p, 6, 4)).'.'.hexdec(substr($p, 10, 4)); // 2-13

                    if (substr($p, 14, 2) == '02')
                        $out['hardware_version'] = hexdec(substr($p, 16, 4)).'.'.hexdec(substr($p, 20, 4)).' ID:'.hexdec(substr($p, 24, 8)); // 16-32
                    
                    if (substr($p, 32, 2) == '0e')
                        $out['hardware_id'] = substr($p, 34, 18); // 34-51
                    
                    
                    if (strlen($p) == 60 || strlen($p) == 70)
                    {
                        if (substr($p, 52, 2) == "1d")
                        {
                            $out['measurement_transmission_ratio'] = hexdec(substr($p, 54, 2)); 
                            $out['measurement_interval_min']       = hexdec(substr($p, 56, 4)); 
                        }
                        // From 1.5.9 time is added to startup message
                        if (strlen($p) == 70)
                        {
                            $time_id        = substr($pu, 60, 2); 
                            $time_available = $time_id == '25' || $time_id == '26' || $time_id == '2D' ? true : false;

                            if ($time_available)
                            {
                                $unixts = hexdec(substr($p, 62, 8));
                                if ($unixts)
                                {

                                    $out['time_clock']  = $time_id == '26' || $time_id == '2D' ? 'rtc' : 'mcu'; // 2023-08-16 added to FW 1.5.14+: 25 == PCB clock. 26/2D == RTC clock
                                    $out['time_device'] = $unixts;
                                }
                            }
                        }
                    }
                    else if (strlen($p) > 70) // 71 - 86
                    {
                        if (substr($p, 52, 2) == "1f") // 52-62
                            $out['boot_count'] = hexdec(substr($p, 54, 8)); 

                        if (substr($p, 62, 2) == "03") // 62-66
                            $out['ds18b20_state'] = hexdec(substr($p, 64, 2)); 

                        if (substr($p, 66, 2) == "1d") // 66-74
                        {
                            $out['measurement_transmission_ratio'] = hexdec(substr($p, 68, 2)); 
                            $out['measurement_interval_min']       = hexdec(substr($p, 70, 4));
                        }

                        // From 1.5.9 time is added to startup message
                        if (strlen($p) == 86) 
                        {
                            $time_id        = substr($pu, 74, 2); 
                            $time_available = $time_id == '25' || $time_id == '26' || $time_id == '2D' || $time_id == '2E' ? true : false;

                            if ($time_available)
                            {
                                $unixts = hexdec(substr($p, 76, 8));
                                if ($unixts)
                                {
                                    $out['time_clock']  = $time_id == '26' || $time_id == '2D' || $time_id == '2E' ? 'rtc' : 'mcu'; // 2023-08-16 added to FW 1.5.14+: 25 == PCB clock. 26/2D == RTC clock
                                    $out['time_device'] = $unixts; // This sets $device->datetime and $device->datetime_offset_sec in MeasurementController::addDeviceMeta();
                                }
                            }
                        }
                    }
                }
            }
            else if ($port == 3 || $port == 4)
            {
                if (($port == 3 && substr($pu, 0, 2) == '1B') || ($port == 4 && substr($pu, 2, 2) == '1B'))  // BEEP base fw 1.2.0+ measurement message, and alarm message starts with battery voltage (1B)
                {
                    $out['beep_base'] = true;

                    // Value        Batt (1B)             Weight (0A)     Temp (04)          FFT (0C)                                                                 BME280 (07)           Device time (25)
                    //              6  batt               5  1 weight     5 1-5 temp (1 to 5)
                    // Bytes        0  1  2  3  4  5      6  7  8  9  10  11 12              13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36  37 38 39 40 41 42 43
                    // Characters   0  2  4  6  8  10     12 14 16 18 20  22 24              26 28 30 32 34 36 38 40 42 44 46 48 50 52 54 56 58 60 62 64 66 68 70 72  74 76 78 80 82 84 86

                    // raw pl       1B 0C 4B 0C 44 64     0A 01 01 2D 2D  04 01 07 D6
                    // Flashlog:    1B 0D 2D 0D 2F 64     0A001 0F D2 1D  04 02 07 7E 06 2D  0C 0A 00 FF 00 6C 00 18 00 28 00 0E 00 0B 00 0A 00 0B 00 1B 00 0A 00 07  07 00 00 00 00 00 00 0A
                    // Flashlog:    1B 0D 2A 0D 1C 64     0A001 13 8A CB  04 02 0D 99 06 07  0C 0A 09 46 00 C9 01 67 00 30 00 6F 00 87 00 49 00 67 00 2C 00 23 00 0C  07 00 00 00 00 00 00 0A
                    // Flashlog:    1B 0D 21 0D 1B 64     0A001 13 8B 09  04 02 0D 93 05 EE  
                    // Flashlog:    1B 0D 67 0D 59 64     0A001 FF FB EB  04 01 08 34        0C 0A 09 46 00 0C 00 07 00 06 00 04 00 02 00 03 00 03 00 02 00 03 00 00  07 00 00 00 00 00 00  25 60 AD 41 03 0A
                    
                    // <1.5.11:0333 1B 0C 9E 0C 9B 64     0A 01 02 0D 3F  04 01 07 D6        0C 0A 09 46 00 0A 00 06 00 05 00 05 00 03 00 04 00 05 00 04 00 03 00 01  07 00 00 00 00 00 00  25 60 22 AB E9 0A
                    // >1.5.11:0333 1B 0D C1 0D BC 64     0A001 00 25 64  04 01 09 7F        0C 0A 09 46 00 0B 00 07 00 06 00 15 00 05 00 06 00 0A 00 06 00 05 00 01  07 00 00 00 00 00 00  25 61 2C A9 0B 0A

                    // LoRa:        1B 0C 1B 0C 0E 64     0A 01 FF F6 98  04 02 0A D7 0A DD  0C 0A 00 FF 00 58 00 12 00 10 00 0C 00 0D 00 0A 00 0A 00 09 00 08 00 07  07 00 00 00 00 00 00
                    // pl incl fft: 1B 0D 15 0D 0A 64     0A 01 00 00 93  04 00              0C 0A 00 FF 00 20 00 05 00 0C 00 03 00 05 00 09 00 04 00 11 00 06 00 02  07 00 00 00 00 00 00
                    // Payload      1B 0C 4B 0C 44 64     0A 01 01 2D 2D  04 01 07 D6

                    // Errors
                    // FL 1.6.0     1B 0D 0A       64     0A 00115 CA 73  04 01 0D A5        0C 0A 06 46 00 0A 00 07 00 07 00 05 00 06 00 02 00 01 00 01 00 01 00 01  07 00 00 00 00 00 00  2D 68 30 42 F8 0A
                    // FL 1.5.15    1B 0C F4 0C 43 64     0A 01 0A 0D 44A 04 01 06 6B        0C 0A 00 FF 00 24 00 20 00 10 00 0D 00 0D 00 0B 00 0B 00 09 00 15 00 07  07 00 00 00 00 00 00  2E 7C 9B 5A 8C 0A
                    // FL 1.5.15    1B 0D 36 0C 38 64     0A 01 0A 0F 267 04 01 06 40        0C 0A 00 FF 00 22 00 2B 00 0E 00 0C 00 0B 00 0A 00 0A 00 09 00 1E 00 09  07 00 00 00 00 00 00  2E 7C 9B 92 CD 0A
                    // FL 1.3.4     1B 0D 1B 0D 9C0D9264  0A 01 0B A5 09  04 00              0C 0A 09 46 00 01 00 01 00 00 00 00 00 01 00 00 00 00 00 00 00 00 00 00  07 00 00 00 00 00 00 0A
                    // FL 1.3.4     1B 0D 1B 0D 780D6364  0A 01 0E E1 7F  04 00              0C 0A 09 46 00 01 00 00 00 00 00 01 00 00 00 00 00 00 00 00 00 00 00 00  07 00 00 00 00 00 00 0A

                    $sb = $port == 4 ? 2 : 0; // start byte

                    // Battery: 0x1B
                    if (substr($pu, $sb+6, 4) == '640A') // missing bv
                    {
                        $out['vcc']      = hexdec(substr($p, $sb+2, 4))/1000;
                        $out['bv']       = $out['vcc'];
                        $out['bat_perc'] = hexdec(substr($p, $sb+6, 2));

                        $sb = $sb+8;
                    }
                    else
                    {
                        $out['vcc']      = hexdec(substr($p, $sb+2, 4))/1000;
                        $out['bv']       = hexdec(substr($p, $sb+6, 4))/1000;
                        $out['bat_perc'] = hexdec(substr($p, $sb+10, 2));
                        
                        // Weight (0 - 2): 0x0A
                        if (substr($pu, $sb+12, 2) == '0A')
                            $sb = $sb+12; // normal payload
                        elseif (substr($pu, $sb+16, 2) == '0A')
                            $sb = $sb+16; // fw 1.3.4 flashlog payload
                    }

                    // Weight: 0x0A
                    $weight_amount   = hexdec(substr($p, $sb+2, 2));
                    $out['weight_sensor_amount'] = $weight_amount;
                    $weight_val_len  = $weight_amount * 6;
                    
                    // fix Flashlog extra 0 error by removing it from the payload: 0A001 -> 0A01
                    if ($weight_amount == 0 && substr($pu, $sb, 5) == '0A001')
                    {
                        $replace_index      = $sb+2; // 0A 0 01 -> 0A 01
                        $p                  = substr_replace($p,                  '', $replace_index, 1);
                        $pu                 = substr_replace($pu,                 '', $replace_index, 1);
                        $payload            = substr_replace($payload,            '', $replace_index, 1);
                        $out['payload_hex'] = substr_replace($out['payload_hex'], '', $replace_index, 1);
                        // update weight amount
                        $weight_amount   = hexdec(substr($p, $sb+2, 2));
                        $out['weight_sensor_amount'] = $weight_amount;
                        $weight_val_len  = $weight_amount * 6;
                    }

                    if (substr($pu, $sb, 2) == '0A')
                    {
                        if ($weight_amount > 0 && $weight_amount < 3)
                        {
                            if ($weight_amount == 1)
                            {
                                $out['w_v'] = self::hexdecs(substr($p, $sb+4, 6));
                            }
                            else if ($weight_amount > 1)
                            {
                                for ($i=0; $i < $weight_amount; $i++)
                                { 
                                    $out['w_v_'.$i+1] = self::hexdecs(substr($p, $sb+4+($i*6), 6)); // w_v_1, w_v_2, etc
                                }
                            }
                        }
                    }
                    else
                    {
                        unset($out['weight_sensor_amount']);
                    }

                    // Temperature 1-10x DS18b20: 0x04
                    $sb            = $sb + 4 + $weight_val_len;
                    $temp_amount   = hexdec(substr($p, $sb+2, 2));
                    $out['ds18b20_sensor_amount'] = $temp_amount;
                    
                    if (substr($pu, $sb, 2) == '04' && $temp_amount > 0)
                    {
                        if ($temp_amount == 1)
                        {
                            $out['t_i'] = self::hexdecs(substr($p, $sb+4, 4))/100;
                        }
                        else if ($temp_amount > 1 && $temp_amount < 11)
                        {
                            for ($i=0; $i < $temp_amount; $i++)
                            { 
                                $out['t_'.$i] = self::hexdecs(substr($p, $sb+4+($i*4), 4))/100;
                            }
                        }
                    }

                    // Audio FFT: 0x0C
                    $sb                     = $sb + 4 + $temp_amount * 4;
                    $fft_char_len           = 4;
                    $fft_bin_amount         = hexdec(substr($p, $sb+2, 2));
                    $fft_bin_freq           = 3.937752016; // = about 2000 / 510
                    $fft_start_bin          = hexdec(substr($p, $sb+4, 2));
                    $fft_stop_bin           = hexdec(substr($p, $sb+6, 2));
                    $fft_bin_total          = $fft_stop_bin - $fft_start_bin;
                    $out['fft_bin_amount']  = $fft_bin_amount;
                    $out['fft_start_bin']   = $fft_start_bin;
                    $out['fft_stop_bin']    = $fft_stop_bin;
                    $fft_sb                 = $sb + 8;
                    
                    if (substr($pu, $sb, 2) == '0C' && $fft_bin_amount > 0 && $fft_bin_amount < 13 && $fft_bin_total)
                    {
                        $summed_bins = ceil($fft_bin_total * 2 / $fft_bin_amount) ;
                        
                        for ($i=0; $i < $fft_bin_amount; $i++)
                        { 
                            $fftValueIndex    = $fft_sb + ($i * $fft_char_len);
                            $fftValue         = hexdec(substr($p, $fftValueIndex, $fft_char_len));

                            $start_freq = round( ( ($fft_start_bin * 2) + $i * $summed_bins) * $fft_bin_freq);
                            $stop_freq  = round( ( ($fft_start_bin * 2) + ($i+1) * $summed_bins) * $fft_bin_freq);

                            //$out['s_bin_'.$i] = [$start_freq, $stop_freq, $fftValue];
                            $out['s_bin_'.$start_freq.'_'.$stop_freq] = $fftValue;
                        }
                    }

                    // BME280: 0x07
                    $sb           = $fft_sb + $fft_bin_amount * $fft_char_len;
                    $bme_start    = $sb+2;

                    if (strlen($pu) > $sb && substr($pu, $sb, 2) == '07')
                    {
                      $bme280_t = self::hexdecs(substr($p, $bme_start, 4)); // signed int
                      $bme280_h = hexdec(substr($p, $bme_start+4, 4));
                      $bme280_p = hexdec(substr($p, $bme_start+8, 4));
                      if (($bme280_t + $bme280_h + $bme280_p) != 0)
                      {
                        $out['t'] = $bme280_t/100;
                        $out['h'] = $bme280_h/100;
                        $out['p'] = $bme280_p;
                      }
                    }

                    // Get time from payload (flash log)
                    // 1b0bf10bea64 0a01019889 0400 0c0a00ff008e001d0010000f000e000c000b000900090008 070849160703f8 25 5f5b73d2 0a
                    // bat          weight     temo fft                                              bme280         time unixts \n
                    $sb             = $sb+14;
                    if (strlen($pu) > $sb)
                    {
                        $time_id        = substr($pu, $sb, 2);
                        $time_available = $time_id == '25' || $time_id == '26' || $time_id == '2D' || $time_id == '2E' ? true : false;

                        if ($time_available)
                        {
                            $time_start = $sb+2;
                            $max_time   = time();
                            $unixts     = hexdec(substr($p, $time_start, 8));
                           
                            if ($unixts) // unix timestamp > Tue Jan 01 2019 00:00:00 GMT+0000
                            {
                                $out['time_clock']  = $time_id == '26' || $time_id == '2D' || $time_id == '2E' ? 'rtc' : 'mcu'; // 2023-08-16 added to FW 1.5.14+: 25 == PCB clock. 26/2D == RTC clock
                                $out['time_device'] = $unixts; // This sets $device->datetime and $device->datetime_offset_sec in MeasurementController::addDeviceMeta();
                                
                                if ($unixts < 1546300800)
                                    $out['time_error'] = 'too_low';
                                else if ($unixts > $max_time)
                                    $out['time_error'] = 'too_high';
                            }
                        }
                    }
                }
            }
        }
        else // BEEP base v2 firmware (fport == 1)
        {
            $beep_sensors = [
                't'  , // 0
                'h'  , // 1
                'w_v',
                't_i',
                'a_i',
                'bv' ,
                's_tot',
                's_fan_4',
                's_fan_6',
                's_fan_9',
                's_fly_a',
                'w_fl_hb',
                'w_fl_lb',
                'w_fr_hb',
                'w_fr_lb',
                'w_bl_hb',
                'w_bl_lb',
                'w_br_hb',       
                'w_br_lb', // 18  
            ];

            $minLength = min(strlen($payload)/2, count($beep_sensors));

            for ($i=0; $i < $minLength; $i++) 
            { 
                if (strlen($payload) > count($beep_sensors)*2)
                {
                    $index = $i * 4 + 2; 
                }
                else
                {
                    $index = $i * 2;
                }
                $sensor = $beep_sensors[$i];
                $hexval = substr($payload, $index, 2);

                if (strpos($sensor, '_hb') !== false) // step 1 of 2 byte value
                {
                    $sensor = substr($sensor, 0, strpos($sensor, '_hb'));
                    $out[$sensor] = $hexval;
                } 
                else if (strpos($sensor, '_lb') !== false) // step 2 of 2 byte value
                {
                    $sensor = substr($sensor, 0, strpos($sensor, '_lb'));
                    $totalHexVal  = $out[$sensor].$hexval;
                    $out[$sensor] = hexdec($totalHexVal);
                }
                else
                {
                    $out[$sensor] = hexdec($hexval);
                }
            }
        }
        //die(print_r($out));
        return $out;
    }


}