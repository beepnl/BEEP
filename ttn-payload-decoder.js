function Decoder(bytes, port) {
  // Decode an uplink message from a buffer
  // (array) of bytes to an object of fields.
  var decoded = {};

  if (port == 1)
  {
    decoded.length = bytes.length;

    if (bytes.length == 7)
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
    else if (bytes.length == 14)
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
    else if (bytes.length == 19)
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

  return decoded;
}