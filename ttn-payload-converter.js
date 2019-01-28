function Converter(decoded, port) {
  // Merge, split or otherwise
  // mutate decoded fields.
  var converted = decoded;

  if (port === 1 && decoded.long === false)
  {
    converted.t     =  decoded.t / 100;
    converted.h     =  decoded.h / 100;
    converted.w_v   =  decoded.w_v / 100;
    converted.s_tot =  decoded.s_tot;
    converted.t_i   =  decoded.t_i / 100;
    converted.bv    =  decoded.bv / 100;
  }
  else if (port === 1 && decoded.long === true)
  {
    // leave sounds and w_v as is
    converted.t       =  (decoded.t / 5) - 10;
    converted.h       =  decoded.h / 2;
    converted.t_i     =  (decoded.t_i / 5) - 10;
    converted.bv      =  decoded.bv / 10;
    converted.w_fl    =  decoded.w_fl / 300;
    converted.w_fr    =  decoded.w_fr / 300;
    converted.w_bl    =  decoded.w_bl / 300;
    converted.w_br    =  decoded.w_br / 300;
  }

  return converted;
}