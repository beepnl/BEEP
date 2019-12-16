function Converter(decoded, port) {
  // BEEP TTN mesurement system LoRa payload converter
  // Converts byte payload from BEEP fw to readable values
  // Only adds data if available (> 0)
  //
  // Merge, split or otherwise
  // mutate decoded fields.
  var converted = {};

  if (port === 1 && decoded.long === false)
  {
    if (decoded.t > 0)
      converted.t     =  decoded.t / 100;
    if (decoded.h > 0)
      converted.h     =  decoded.h / 100;
    if (decoded.w_v > 0)
      converted.w_v   =  decoded.w_v / 100;
    if (decoded.t_i > 0)
      converted.t_i   =  decoded.t_i / 100;
    if (decoded.a_i > 0)
      converted.a_i     = decoded.a_i;
    if (decoded.bv > 0)
      converted.bv    =  decoded.bv / 100;
    if (decoded.s_tot > 0)
      converted.s_tot =  decoded.s_tot;
  }
  else if (port === 1 && decoded.long === true)
  {
    // leave sounds and w_v as is
    if (decoded.t > 0)
      converted.t       =  (decoded.t / 5) - 10;
    if (decoded.h > 0)
      converted.h       =  decoded.h / 2;
    if (decoded.w_v > 0)
      converted.w_v     = decoded.w_v
    if (decoded.t_i > 0)
      converted.t_i     =  (decoded.t_i / 5) - 10;
    if (decoded.a_i > 0)
      converted.a_i     = decoded.a_i;
    if (decoded.bv > 0)
      converted.bv      =  decoded.bv / 10;
    if (decoded.s_tot > 0)
      converted.s_tot   = decoded.s_tot;
    if (decoded.s_fan_4 > 0)
      converted.s_fan_4 = decoded.s_fan_4;
    if (decoded.s_fan_6 > 0)
      converted.s_fan_6 = decoded.s_fan_6;
    if (decoded.s_fan_9 > 0)
      converted.s_fan_9 = decoded.s_fan_9;
    if (decoded.s_fly_a > 0)
      converted.s_fly_a = decoded.s_fly_a;
    if (decoded.w_fl > 0)
      converted.w_fl    =  decoded.w_fl / 300;
    if (decoded.w_fr > 0)
      converted.w_fr    =  decoded.w_fr / 300;
    if (decoded.w_bl > 0)
      converted.w_bl    =  decoded.w_bl / 300;
    if (decoded.w_br > 0)
      converted.w_br    =  decoded.w_br / 300;
  }
  else if (port == 3)
  {
    converted = decoded;
    
    // battery
    if (decoded.bv > 0)
      converted.bv =  decoded.bv / 1000;
    
    // weight is not converted

    // temperature
    if (decoded.amount_of_temperature_sensors == 1)
    {
      if (decoded.t_i > 0)
        converted.t_i =  decoded.t_i / 100;
      
    }
    else if (decoded.amount_of_temperature_sensors > 1)
    {
      for (var i = 0; i < decoded.amount_of_temperature_sensors; i++) 
      {
        converted['t_i_'+i] = decoded['t_i_'+i] / 100;
      }
    }
  }

  return converted;
}