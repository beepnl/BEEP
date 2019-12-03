function Decoder(bytes, port) {
  // BEEP TTN mesurement system LoRa payload decoder
  // Decode an uplink message from a buffer
  // (array) of bytes to an object of fields.
  var decoded = {};

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
  else if (port == 3 && bytes.length >= 15)  // BEEP base fw 1.2.0, 1+ weight, 1+temp
  {
    // TODO: Make dynamic based on multi weight and multi temp
    
    // raw pl  1B0C4B0C44640A01012D2D040107D6
    // Payload 1B 0C4B0C4464 0A 01 012D2D 04 01 07D6
    //         6  batt       5  1 weight  5 1-5 temp (1 to 5)
    
    // Battery 
    decoded.bv = (bytes[3] << 8) + bytes[4]; // 1B (0 batt) 0C4B (1-2 vcc) 0C44 (3-4 vbat) 64 (5 %bat)

    decoded.amount_of_weight_sensors = bytes[7];
    
    // Weight
    var weight_byte_length       = 3;
    if (decoded.amount_of_weight_sensors == 1)
    {
      decoded.w_v   =  (bytes[8] << 16) + (bytes[9] << 8) + bytes[10]; // 0A (6 weight) 01 (7 1x) 012D2D (8-10 value)
    }
    else
    {
      for(var i = 0; i < decoded.amount_of_weight_sensors; i++)
      {
        var valueByteIndex = 8 + (i * weight_byte_length); 
        decoded['w_v_' + i] = (bytes[valueByteIndex] << 16) + (bytes[valueByteIndex+1] << 8) + bytes[valueByteIndex+2]; 
      }
    }

    decoded.amount_of_temperature_sensors = bytes[9 + (decoded.amount_of_weight_sensors * weight_byte_length)];
    var temperature_byte_length       = 2;
    if (decoded.amount_of_temperature_sensors == 1)
    {
      decoded.t_i =  (bytes[13] << 8) + bytes[14]; // 04 (11 temp) 01 (12 1x) 07D6 (13-14 value)
    }
    else
    {
      for(var j = 0; j < decoded.amount_of_temperature_sensors; j++)
      {
        var tempValueByteIndex = 8 + (j * temperature_byte_length); 
        decoded['t_' + j] = (bytes[tempValueByteIndex] << 8) + bytes[tempValueByteIndex+1]; 
      }
    }
  }
  return decoded;
}