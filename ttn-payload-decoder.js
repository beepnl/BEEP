function Decoder(bytes, port) {
  // BEEP TTN mesurement system LoRa payload decoder
  // Decode an uplink message from a buffer
  // (array) of bytes to an object of fields.
  var decoded = {};

  function toHexString( number, width )
  {
    width -= number.toString(16).length;
    if ( width > 0 )
    {
      return new Array( width + (/\./.test( number.toString(16) ) ? 2 : 1) ).join( '0' ) + number.toString(16);
    }
    return number.toString(16) + ""; // always return a string
  }

  if (port == 1)
  {
    decoded.length = bytes.length;

    if (bytes.length == 7) // fw v1-3
    {
      decoded.t     =  bytes[0];
      decoded.h     =  bytes[1];
      decoded.w_v   =  bytes[2];
      decoded.t_i   =  bytes[3];
      decoded.a_i   =  bytes[4];
      decoded.bv    =  bytes[5];
      decoded.s_tot =  bytes[6];
      decoded.long  =  false;
    }
    else if (bytes.length == 14) // fw v4-6
    {
      decoded.t     =  (bytes[0]  << 8) + bytes[1];
      decoded.h     =  (bytes[2]  << 8) + bytes[3];
      decoded.w_v   =  (bytes[4]  << 8) + bytes[5];
      decoded.t_i   =  (bytes[6]  << 8) + bytes[7];
      decoded.a_i   =  (bytes[8]  << 8) + bytes[9];
      decoded.bv    =  (bytes[10] << 8) + bytes[11];
      decoded.s_tot =  (bytes[12] << 8) + bytes[13];
      decoded.long  =  false;
    }
    else if (bytes.length == 19)  // fw v7+
    {
      decoded.t     =  bytes[0];
      decoded.h     =  bytes[1];
      decoded.w_v   =  bytes[2];
      decoded.t_i   =  bytes[3];
      decoded.a_i   =  bytes[4];
      decoded.bv    =  bytes[5];
      decoded.s_tot =  bytes[6];
      decoded.s_fan_4= bytes[7];
      decoded.s_fan_6= bytes[8];
      decoded.s_fan_9= bytes[9];
      decoded.s_fly_a= bytes[10];
      decoded.w_fl   = (bytes[11] << 8) + bytes[12];
      decoded.w_fr   = (bytes[13] << 8) + bytes[14];
      decoded.w_bl   = (bytes[15] << 8) + bytes[16];
      decoded.w_br   = (bytes[17] << 8) + bytes[18];
      decoded.long   = true;
    }
  }
  else if (port == 2) // BEEP base fw 1.2.0+ start-up message
  {
    if (bytes[0] == 0x02 && bytes.length == 40) // BEEP base fw 1.3.3+ start-up message
    {
      // 02250100010003000002000100000002e70e0e01233d2308ec8e91ee1f0000000b03091d0000010a
      // 02 25  01 00 01 00 03 00 00  02 00 01 00 00 00 02 e7 0e  0e 01 23 3d 23 08 ec 8e 91 ee  1f 00 00 00 0b  03 09  1d 00 00 01 0a 
      // 0  1   2  3  4  5  6  7  8   9  10 11 12 13 14 15 16 17  18 19 20 21 22 23 24 25 26 27  28 29 30 31 32  33 34  35 36 37 38 39 
      //    pl  fw version            hw version                  ATTEC ID (14)                     Boot count      ds#    app config
      decoded.beep_base        = true;
      decoded.firmware_version = ((bytes[3] << 8) + bytes[4]) + "." + ((bytes[5] << 8) + bytes[6]) + "." + ((bytes[7] << 8) + bytes[8]);
      decoded.hardware_version = ((bytes[10] << 8) + bytes[11]) + "." + ((bytes[12] << 8) + bytes[13]) + " ID:" + ((bytes[14] << 32) + (bytes[15] << 16) + (bytes[16] << 8) + bytes[17]);
      decoded.hardware_id      = toHexString(bytes[18], 2) + toHexString(bytes[19], 2) + toHexString(bytes[20], 2) + toHexString(bytes[21], 2) + toHexString(bytes[22], 2) + toHexString(bytes[23], 2) + toHexString(bytes[24], 2) + toHexString(bytes[25], 2) + toHexString(bytes[26], 2) + toHexString(bytes[27], 2);
      decoded.bootcount        = ((bytes[29] << 32) + (bytes[30] << 16) + (bytes[31] << 8) + bytes[32]);
      decoded.ds18b20_sensor_amount          = (bytes[34]);
      decoded.measurement_transmission_ratio = (bytes[36]);
      decoded.measurement_interval_min       = ((bytes[37] << 8) + bytes[38]);
    }
    else if (bytes[0] == 0x01 && bytes.length == 26) // Beep base fw < 1.3.2 start-up message
    {
      // 01 00 01 00 03 00 01 02 93 56 85 E6 FF FF 94 54 0E 01 23 7A 26 A6 7D 24 D8 EE 
      // 0  1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 
      //       pl  fw version hw version                 ATTEC ID (14)
      decoded.beep_base        = true;
      decoded.firmware_version = ((bytes[1] << 8) + bytes[2]) + "." + ((bytes[3] << 8) + bytes[4]) + "." + ((bytes[5] << 8) + bytes[6]);
      decoded.hardware_version = ((bytes[8] << 8) + bytes[9]) + "." + ((bytes[10] << 8) + bytes[11]) + " ID:" + ((bytes[12] << 32) + (bytes[13] << 16) + (bytes[14] << 8) + bytes[15]);
      decoded.hardware_id      = toHexString(bytes[16], 2) + toHexString(bytes[17], 2) + toHexString(bytes[18], 2) + toHexString(bytes[19], 2) + toHexString(bytes[20], 2) + toHexString(bytes[21], 2) + toHexString(bytes[22], 2) + toHexString(bytes[23], 2) + toHexString(bytes[24], 2) + toHexString(bytes[25], 2);
    }
  }
  else if (bytes.length >= 15 && ( (port == 3 && bytes[0] == 0x1B) || (port == 4 && bytes[1] == 0x1B) ) )  // BEEP base fw 1.2.0+ measurement message
  {
    //              1B 0C 1B 0C 0E 64  0A 01 FF F6 98  04 02 0A D7 0A DD  0C 0A 00 FF 00 58 00 12 00 10 00 0C 00 0D 00 0A 00 0A 00 09 00 08 00 07  07 00 00 00 00 00 00
    // pl incl fft: 1B 0D 15 0D 0A 64  0A 01 00 00 93  04 00              0C 0A 00 FF 00 20 00 05 00 0C 00 03 00 05 00 09 00 04 00 11 00 06 00 02  07 00 00 00 00 00 00
    //              0  1  2  3  4  5   6  7  8  9  10  11 12              13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36  37 38 39 40 41 42 43
    
    // raw pl  1B0C4B0C44640A01012D2D040107D6
    // Payload 1B 0C4B0C4464 0A 01 012D2D 04 01 07D6
    //         6  batt       5  1 weight  5 1-5 temp (1 to 5)
    
    decoded.beep_base = true;
    decoded.alarm     = null; 

    var bv_start_byte = 3;

    if (port == 4)
    {
      bv_start_byte = 4;
      switch(bytes[0])
      {
        case 0:
          decoded.alarm = 'ds18b20';
          break;
        case 1:
          decoded.alarm = 'bme280';
          break;
        case 2:
          decoded.alarm = 'hx711';
          break;
        case 3:
          decoded.alarm = 'audio_adc';
          break;
        case 4:
          decoded.alarm = 'nrf_adc';
          break;
        case 5:
          decoded.alarm = 'sq_min';
          break;
        case 6:
          decoded.alarm = 'attec';
          break;
        case 7:
          decoded.alarm = 'buzzer';
          break;
        case 8:
          decoded.alarm = 'lorawan';
          break;
        case 9:
          decoded.alarm = 'mx_flash';
          break;
        case 10:
          decoded.alarm = 'nrf_flash';
          break;
        case 11:
          decoded.alarm = 'application';
          break;
      }
    }

    // Battery: 0x1B
    decoded.bv = (bytes[bv_start_byte] << 8) + bytes[bv_start_byte+1]; // 1B (0 batt) 0C4B (1-2 vcc) 0C44 (3-4 vbat) 64 (5 %bat)
    decoded.bat_perc = bytes[bv_start_byte+2];

    // Weight (1 or 2): 0x0A
    var weight_byte_length           = 3;
    var weight_start_byte            = bv_start_byte + 3;
    decoded.weight_sensor_amount     = bytes[weight_start_byte + 1];
    var weight_values_start_byte     = weight_start_byte + 2;
    if (bytes[weight_start_byte] == 0x0A && decoded.weight_sensor_amount > 0)
    {
      decoded.weight_present = true;
      if (decoded.weight_sensor_amount == 1)
      {
        decoded['w_v'] = (bytes[weight_values_start_byte] << 16) + (bytes[weight_values_start_byte+1] << 8) + bytes[weight_values_start_byte+2]; // 0A (6 weight) 01 (7 1x) 012D2D (8-10 value)
      }
      else
      {
        for(var i = 0; i < decoded.weight_sensor_amount; i++)
        {
          var valueByteIndex = weight_values_start_byte + (i * weight_byte_length); 
          decoded['w_v_' + i] = (bytes[valueByteIndex] << 16) + (bytes[valueByteIndex+1] << 8) + bytes[valueByteIndex+2]; 
        }
      }
    }
    else
    {
      decoded.weight_present = false;
    }
    var weight_values_end_byte = weight_values_start_byte + (decoded.weight_sensor_amount * weight_byte_length);


    // Temperature 1-5x DS18b20: 0x04
    var ds18b20_byte_length           = 2;
    var ds18b20_start_byte            = weight_values_end_byte;
    var ds18b20_values_start_byte     = ds18b20_start_byte + 2;
    decoded.ds18b20_sensor_amount     = bytes[ds18b20_start_byte + 1];
    
    console.log(bytes[7], decoded.weight_sensor_amount, weight_start_byte, weight_values_end_byte, ds18b20_start_byte, bytes[ds18b20_start_byte]);

    if (bytes[ds18b20_start_byte] == 0x04 && decoded.ds18b20_sensor_amount > 0)
    {
      decoded.ds18b20_present = true;
      if (decoded.ds18b20_sensor_amount == 1)
      {
        decoded.t_i =  (bytes[ds18b20_values_start_byte] << 8) + bytes[ds18b20_values_start_byte+1]; // 04 (11 temp) 01 (12 1x) 07D6 (13-14 value)
      }
      else
      {
        for(var j = 0; j < decoded.ds18b20_sensor_amount; j++)
        {
          var tempValueByteIndex = ds18b20_values_start_byte + (j * ds18b20_byte_length); 
          decoded['t_' + j] = (bytes[tempValueByteIndex] << 8) + bytes[tempValueByteIndex+1]; 
        }
      }
    }
    else
    {
      decoded.ds18b20_present = false;
    }
    var ds18b20_values_end_byte = ds18b20_values_start_byte + (decoded.ds18b20_sensor_amount * ds18b20_byte_length);


    // Audio FFT: 0x0C
    var fft_byte_length        = 2;
    var fft_bin_freq           = 3.937752016; // = about 2000 / 510
    var fft_start_byte         = ds18b20_values_end_byte;
    decoded.fft_bin_amount     = bytes[fft_start_byte+1];
    decoded.fft_start_bin      = bytes[fft_start_byte+2];
    decoded.fft_stop_bin       = bytes[fft_start_byte+3];
    var fft_bin_total          = decoded.fft_stop_bin - decoded.fft_start_bin;
    var fft_values_start_byte  = fft_start_byte + 4;

    if (bytes[fft_start_byte] == 0x0C && fft_bin_total > 0 && decoded.fft_bin_amount > 0)
    {
      decoded.fft_present      = true;
      var summed_bins          = Math.ceil(fft_bin_total * 2 / decoded.fft_bin_amount) ;
      decoded.fft_hz_per_bin   = Math.round(summed_bins * fft_bin_freq);
      
      for(var k = 0; k < decoded.fft_bin_amount; k++)
      {
        var fftValueByteIndex = fft_values_start_byte + (k * fft_byte_length); 
        var start_freq = Math.round( ( (decoded.fft_start_bin * 2) + k * summed_bins) * fft_bin_freq);
        var stop_freq  = Math.round( ( (decoded.fft_start_bin * 2) + (k+1) * summed_bins) * fft_bin_freq);

        decoded['s_bin_' + start_freq + '_' + stop_freq] = (bytes[fftValueByteIndex] << 8) + bytes[fftValueByteIndex+1];
      }
    }
    else
    {
      decoded.fft_present = false;
    }
    var fft_values_end_byte = fft_values_start_byte + (decoded.fft_bin_amount * fft_byte_length);


    // BME280: 0x07
    var bme280_start_byte        = fft_values_end_byte;
    var bme280_values_start_byte = bme280_start_byte + 1;
    var bme280_t = (bytes[bme280_values_start_byte+0] << 8) + bytes[bme280_values_start_byte+1];
    var bme280_h = (bytes[bme280_values_start_byte+2] << 8) + bytes[bme280_values_start_byte+3];
    var bme280_p = (bytes[bme280_values_start_byte+4] << 8) + bytes[bme280_values_start_byte+5];
    if (bytes[bme280_start_byte] == 0x07 && (bme280_t + bme280_h + bme280_p) != 0)
    {
      decoded.bme280_present = true;
      decoded.bme280_t = (bytes[bme280_values_start_byte+0] << 8) + bytes[bme280_values_start_byte+1];
      decoded.bme280_h = (bytes[bme280_values_start_byte+2] << 8) + bytes[bme280_values_start_byte+3];
      decoded.bme280_p = (bytes[bme280_values_start_byte+4] << 8) + bytes[bme280_values_start_byte+5];
    }
    else
    {
      decoded.bme280_present = false;
    }
    var bme280_values_end_byte = bme280_values_start_byte + 6;


  }
  return decoded;
}

