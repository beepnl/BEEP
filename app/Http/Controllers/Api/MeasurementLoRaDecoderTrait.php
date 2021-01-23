<?php
namespace App\Http\Controllers\Api;

/**
 * @group Api\MeasurementLoRaDecoderTrait
 * Measurement device LoRa payload decoding
 */
trait MeasurementLoRaDecoderTrait
{

    private function decode_ttn_payload($data)
    {
        $out = [];
        
        if (isset($data['payload_raw']) == false)
            return $out;

        $payload = bin2hex(base64_decode($data['payload_raw']));
        $port    = $data['port'];

        return $this->decode_beep_payload($payload, $port);
    }

    private function decode_simpoint_payload($data)
    {
        $out = [];
        
        if (isset($data['payload_hex']) == false)
            return $out;

        $payload = $data['payload_hex'];
        $port    = $data['FPort'];

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

        if ($show)
        {
            $parsed['port']    = $port;
            $parsed['len']     = $length;
            $parsed['pl']      = $payload;
            $parsed['pl_bytes']= strlen($payload)/2;
        }

        return $parsed;
    }


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

    private function decode_beep_payload($payload, $port)
    {
        $out = [];
        
        // distighuish BEEP base v2 and v3 payload
        
        if ($port != 1) // BEEP base v3 firmware
        {
            $p  = strtolower($payload);
            $pu = strtoupper($payload);

            if ($port == 2)
            {
                if (substr($p, 0, 2) == '01' && (strlen($p) == 52 || strlen($p) == 60)) // BEEP base fw 1.3.3+ start-up message)
                {
                    $out['beep_base'] = true;
                    // 0100010003000502935cbdd3ffff94540e0123af9aed3527beee1d000001
                    // 0100010003000402935685E6FFFF94540E01237A26A67D24D8EE1D000001
                    //                                                 0e01236dada5c40a28ee
                    // 01 00 01 00 03 00 04 02 93 56 85 E6 FF FF 94 54 0E 01 23 7A 26 A6 7D 24 D8 EE 1D 00 00 01 
                    // 0  1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 
                    //    pl fw version     hw version                 ATTEC ID (14)                 app config

                    $out['firmware_version'] = hexdec(substr($p, 2, 4)).'.'.hexdec(substr($p, 6, 4)).'.'.hexdec(substr($p, 10, 4)); // 2-13
                    // $out['hardware_version'] = hexdec(substr($p, 16, 16)); // 14-31
                    $out['hardware_id']      = substr($p, 34, 18); // 34-51
                    
                    if (strlen($p) > 52)
                    {
                        $out['measurement_transmission_ratio'] = hexdec(substr($p, 54, 2)); 
                        $out['measurement_interval_min']       = hexdec(substr($p, 56, 4)); 
                    }
                }
            }
            else if ($port == 3 || $port == 4)
            {
                if (($port == 3 && substr($pu, 0, 2) == '1B') || ($port == 4 && substr($pu, 2, 2) == '1B'))  // BEEP base fw 1.2.0+ measurement message, and alarm message
                {
                    $out['beep_base'] = true;
                    //              1B 0C 1B 0C 0E 64  0A 01 FF F6 98  04 02 0A D7 0A DD  0C 0A 00 FF 00 58 00 12 00 10 00 0C 00 0D 00 0A 00 0A 00 09 00 08 00 07  07 00 00 00 00 00 00
                    // pl incl fft: 1B 0D 15 0D 0A 64  0A 01 00 00 93  04 00              0C 0A 00 FF 00 20 00 05 00 0C 00 03 00 05 00 09 00 04 00 11 00 06 00 02  07 00 00 00 00 00 00
                    //              0  1  2  3  4  5   6  7  8  9  10  11 12              13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36  37 38 39 40 41 42 43
                    //              0  2  4  6  8  10  12 14 16 18 20  22 24              26 28 30 32 34 36 38 40 42 44 46 48 50 52 54 56 58 60 62 64 66 68 70 72  74 76 78 80 82 84 86
                    //                 Batt            Weight          Temp               FFT                                                                      BME280
                    // raw pl  1B0C4B0C44640A01012D2D040107D6
                    // Payload 1B 0C4B0C4464 0A 01 012D2D 04 01 07D6
                    //         6  batt       5  1 weight  5 1-5 temp (1 to 5)

                    $sb = $port == 4 ? 2 : 0; // start byte

                    // Battery: 0x1B
                    $out['vcc']      = hexdec(substr($p, $sb+2, 4))/1000;
                    $out['bv']       = hexdec(substr($p, $sb+6, 4))/1000;
                    $out['bat_perc'] = hexdec(substr($p, $sb+10, 2));

                    // Weight (1 or 2): 0x0A
                    $sb = $sb+12;
                    $weight_amount   = hexdec(substr($p, $sb+2, 2));
                    $out['weight_sensor_amount'] = $weight_amount;
                    
                    if (substr($pu, $sb, 2) == '0A' && $weight_amount > 0)
                    {
                        if ($weight_amount == 1)
                        {
                            $out['w_v'] = hexdec(substr($p, $sb+4, 6));
                        }
                        else if ($weight_amount > 1)
                        {
                            for ($i=0; $i < $weight_amount; $i++)
                            { 
                                $out['w_v_'.$i] = hexdec(substr($p, $sb+4+($i*6), 6));
                            }
                        }
                    }

                    // Temperature 1-5x DS18b20: 0x04
                    $sb            = $sb + 4 + $weight_amount * 6;
                    $temp_amount   = hexdec(substr($p, $sb+2, 2));
                    $out['ds18b20_sensor_amount'] = $temp_amount;
                    
                    if (substr($pu, $sb, 2) == '04' && $temp_amount > 0)
                    {
                        if ($temp_amount == 1)
                        {
                            $out['t_i'] = $this->hexdecs(substr($p, $sb+4, 4))/100;
                        }
                        else if ($temp_amount > 1)
                        {
                            for ($i=0; $i < $temp_amount; $i++)
                            { 
                                $out['t_'.$i] = $this->hexdecs(substr($p, $sb+4+($i*4), 4))/100;
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
                    
                    if (substr($pu, $sb, 2) == '0C' && $fft_bin_amount > 0 && $fft_bin_total)
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
                      $bme280_t = hexdec(substr($p, $bme_start, 4));
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
                    $sb = $sb+14;
                    if (strlen($pu) > $sb && substr($pu, $sb, 2) == '25')
                    {
                        $time_start = $sb+2;
                        $unixts = hexdec(substr($p, $time_start, 8));
                        if ($unixts)
                        {
                            $out['time'] = $unixts;
                        }
                    }
                }
            }
        }
        else // BEEP base v2 firmware
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