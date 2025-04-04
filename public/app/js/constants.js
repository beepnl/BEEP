/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */
var LANG = [];
var API_URL = document.URL.indexOf('beep.test') > -1 ? 'https://beep.test/api/' : ((document.URL.indexOf('test.beep.nl') > -1) ? 'https://test.beep.nl/api/' : ((document.URL.indexOf('app.beep.nl') > -1) ? 'https://api.beep.nl/api/' : 'http://localhost:8087/api/')); //var API_URL 				= 'https://api.beep.nl/api/';

var CONNECTION_FREQ_REMOTE = 60 * 1000;
var COLORS = {
  orange: {
    r: 245,
    g: 166,
    b: 35
  },
  red: {
    r: 208,
    g: 2,
    b: 27
  },
  yellow: {
    r: 248,
    g: 231,
    b: 28
  },
  darkblue: {
    r: 24,
    g: 64,
    b: 111
  },
  lightblue: {
    r: 120,
    g: 178,
    b: 246
  },
  lightgreen: {
    r: 126,
    g: 211,
    b: 33
  },
  darkgreen: {
    r: 65,
    g: 117,
    b: 5
  },
  purple: {
    r: 189,
    g: 16,
    b: 224
  },
  pink: {
    r: 237,
    g: 79,
    b: 126
  },
  lightgrey: {
    r: 200,
    g: 200,
    b: 200
  },
  lightgrey1: {
    r: 220,
    g: 220,
    b: 220
  },
  lightgrey2: {
    r: 230,
    g: 230,
    b: 230
  },
  darkgrey: {
    r: 100,
    g: 100,
    b: 100
  },
  darkergrey: {
    r: 50,
    g: 50,
    b: 50
  },
  black: {
    r: 0,
    g: 0,
    b: 0
  }
};
var WEATHER = ['icon', 'precipIntensity', 'precipProbability', 'precipType', 'temperature', 'apparentTemperature', 'dewPoint', 'humidity', 'pressure', 'windSpeed', 'windGust', 'windBearing', 'cloudCover', 'uvIndex', 'visibility', 'ozone']; // weather indicators

var SENSORS = ['t', 'h', 'p', 'l', 'bc_i', 'bc_o', 'bc_tot', 'weight_kg_corrected', 'weight_kg', 't_i', 't_0', 't_1', 't_2', 't_3', 't_4', 't_5', 't_6', 't_7', 't_8', 't_9']; // not actuators

var SOUND = ['s_fan_4', 's_fan_6', 's_fan_9', 's_fly_a', 's_tot', 's_bin', 's_spl', 's_bin098_146Hz', 's_bin146_195Hz', 's_bin195_244Hz', 's_bin244_293Hz', 's_bin293_342Hz', 's_bin342_391Hz', 's_bin391_439Hz', 's_bin439_488Hz', 's_bin488_537Hz', 's_bin537_586Hz', 's_bin_71_122', 's_bin_122_173', 's_bin_173_224', 's_bin_224_276', 's_bin_276_327', 's_bin_327_378', 's_bin_378_429', 's_bin_429_480', 's_bin_480_532', 's_bin_532_583', 's_bin_0_201', 's_bin_201_402', 's_bin_402_602', 's_bin_602_803', 's_bin_803_1004', 's_bin_1004_1205', 's_bin_1205_1406', 's_bin_1406_1607', 's_bin_1607_1807', 's_bin_1807_2008']; // all sound releated sensors

var DEBUG = ['bv', 'rssi', 'snr']; // all debugging info sensors

var SENSOR_COLOR = {
  t: COLORS.pink,
  // Measured Temperature (degrees Celsius) (displayed in main screen at temp icon)
  h: COLORS.darkblue,
  // Measured Humidity (RH% 0_100) (displayed in main screen at humi icon)
  l: COLORS.yellow,
  // Measured Light measurement value (lux) (displayed in main screen at sun icon)
  p: COLORS.darkgreen,
  bv: COLORS.darkergrey,
  s_fan_4: COLORS.lightblue,
  s_fan_6: COLORS.lightgreen,
  s_fan_9: COLORS.darkgreen,
  s_fly_a: COLORS.purple,
  s_tot: COLORS.black,
  s_spl: COLORS.black,
  bc_i: COLORS.purple,
  bc_o: COLORS.pink,
  bc_tot: COLORS.black,
  weight_kg: COLORS.orange,
  weight_kg_corrected: COLORS.darkgrey,
  t_i: COLORS.red,
  t_0: COLORS.red,
  t_1: COLORS.red,
  t_2: COLORS.red,
  t_3: COLORS.red,
  t_4: COLORS.red,
  t_5: COLORS.red,
  t_6: COLORS.red,
  t_7: COLORS.red,
  t_8: COLORS.red,
  t_9: COLORS.red,
  rssi: COLORS.lightgrey,
  snr: COLORS.lightgrey1,
  lat: COLORS.lightgrey2,
  lon: COLORS.lightgrey2,
  's_bin098_146Hz': COLORS.darkgreen,
  's_bin146_195Hz': COLORS.lightgreen,
  's_bin195_244Hz': COLORS.lightblue,
  's_bin244_293Hz': COLORS.darkblue,
  's_bin293_342Hz': COLORS.purple,
  's_bin342_391Hz': COLORS.pink,
  's_bin391_439Hz': COLORS.red,
  's_bin439_488Hz': COLORS.orange,
  's_bin488_537Hz': COLORS.yellow,
  's_bin537_586Hz': COLORS.lightgrey2,
  's_bin_71_122': COLORS.darkgreen,
  's_bin_122_173': COLORS.lightgreen,
  's_bin_173_224': COLORS.lightblue,
  's_bin_224_276': COLORS.darkblue,
  's_bin_276_327': COLORS.purple,
  's_bin_327_378': COLORS.pink,
  's_bin_378_429': COLORS.red,
  's_bin_429_480': COLORS.orange,
  's_bin_480_532': COLORS.yellow,
  's_bin_532_583': COLORS.lightgrey2,
  's_bin_0_201': COLORS.darkgreen,
  's_bin_201_402': COLORS.lightgreen,
  's_bin_402_602': COLORS.lightblue,
  's_bin_602_803': COLORS.darkblue,
  's_bin_803_1004': COLORS.purple,
  's_bin_1004_1205': COLORS.pink,
  's_bin_1205_1406': COLORS.red,
  's_bin_1406_1607': COLORS.orange,
  's_bin_1607_1807': COLORS.yellow,
  's_bin_1807_2008': COLORS.lightgrey2,
  'icon': COLORS.pink,
  'precipIntensity': COLORS.darkblue,
  'precipProbability': COLORS.darkblue,
  'precipType': COLORS.darkblue,
  'temperature': COLORS.red,
  'apparentTemperature': COLORS.pink,
  'dewPoint': COLORS.pink,
  'humidity': COLORS.lightblue,
  'pressure': COLORS.lightgreen,
  'windSpeed': COLORS.lightgrey,
  'windGust': COLORS.lightblue,
  'windBearing': COLORS.lightblue,
  'cloudCover': COLORS.lightgrey1,
  'uvIndex': COLORS.pink,
  'visibility': COLORS.lightgrey2,
  'ozone': COLORS.lightgrey
};
var SENSOR_NAMES = {
  t: 't',
  h: 'h',
  l: 'l',
  p: 'p',
  bv: 'bv',
  s_fan_4: 's_fan_4',
  s_fan_6: 's_fan_6',
  s_fan_9: 's_fan_9',
  s_fly_a: 's_fly_a',
  s_tot: 's_tot',
  s_spl: 's_spl',
  bc_i: 'bc_i',
  bc_o: 'bc_o',
  bc_tot: 'bc_tot',
  weight_kg: 'weight_kg',
  weight_kg_corrected: 'weight_kg_corrected',
  t_i: 't_i',
  t_0: 't_0',
  t_1: 't_1',
  t_2: 't_2',
  t_3: 't_3',
  t_4: 't_4',
  t_5: 't_5',
  t_6: 't_6',
  t_7: 't_7',
  t_8: 't_8',
  t_9: 't_9',
  rssi: 'rssi',
  snr: 'snr',
  lat: 'lat',
  lon: 'lon',
  's_bin098_146Hz': '098-146Hz',
  's_bin146_195Hz': '146-195Hz',
  's_bin195_244Hz': '195-244Hz',
  's_bin244_293Hz': '244-293Hz',
  's_bin293_342Hz': '293-342Hz',
  's_bin342_391Hz': '342-391Hz',
  's_bin391_439Hz': '391-439Hz',
  's_bin439_488Hz': '439-488Hz',
  's_bin488_537Hz': '488-537Hz',
  's_bin537_586Hz': '537-586Hz',
  's_bin_71_122': '071-122Hz',
  's_bin_122_173': '122-173Hz',
  's_bin_173_224': '173-224Hz',
  's_bin_224_276': '224-276Hz',
  's_bin_276_327': '276-327Hz',
  's_bin_327_378': '327-378Hz',
  's_bin_378_429': '378-429Hz',
  's_bin_429_480': '429-480Hz',
  's_bin_480_532': '480-532Hz',
  's_bin_532_583': '532-583Hz',
  's_bin_0_201': '0-201Hz',
  's_bin_201_402': '201-402Hz',
  's_bin_402_602': '402-602Hz',
  's_bin_602_803': '602-803Hz',
  's_bin_803_1004': '803-1004Hz',
  's_bin_1004_1205': '1004-1205Hz',
  's_bin_1205_1406': '1205-1406Hz',
  's_bin_1406_1607': '1406-1607Hz',
  's_bin_1607_1807': '1607-1807Hz',
  's_bin_1807_2008': '1807-2008Hz',
  'icon': 'icon',
  'precipIntensity': 'precipIntensity',
  'precipProbability': 'precipProbability',
  'precipType': 'precipType',
  'temperature': 'temperature',
  'apparentTemperature': 'apparentTemperature',
  'dewPoint': 'dewPoint',
  'humidity': 'humidity',
  'pressure': 'pressure',
  'windSpeed': 'windSpeed',
  'windGust': 'windGust',
  'windBearing': 'windBearing',
  'cloudCover': 'cloudCover',
  'uvIndex': 'uvIndex',
  'visibility': 'visibility',
  'ozone': 'ozone'
};
var SENSOR_MIN = {
  t: -10,
  t_i: 0,
  t_0: 0,
  t_1: 0,
  t_2: 0,
  t_3: 0,
  t_4: 0,
  t_5: 0,
  t_6: 0,
  t_7: 0,
  t_8: 0,
  t_9: 0,
  h: 0,
  l: 0,
  p: 900,
  bv: 0,
  s_fan_4: 0,
  s_fan_6: 0,
  s_fan_9: 0,
  s_fly_a: 0,
  s_tot: 0,
  s_spl: 0,
  bc_i: 0,
  bc_o: 0,
  bc_tot: 0,
  weight_kg: 0,
  weight_kg_corrected: 0,
  rssi: -200,
  snr: -20,
  lat: 0,
  lon: 0
};
var SENSOR_LOW = {
  t: 0,
  t_i: 33,
  t_0: 33,
  t_1: 33,
  t_2: 33,
  t_3: 33,
  t_4: 33,
  t_5: 33,
  t_6: 33,
  t_7: 33,
  t_8: 33,
  t_9: 33,
  h: 40,
  l: 0,
  p: 1013,
  bv: 3.0,
  s_fan_4: 0,
  s_fan_6: 0,
  s_fan_9: 0,
  s_fly_a: 0,
  s_tot: 0,
  s_spl: 30,
  bc_i: 0,
  bc_o: 0,
  bc_tot: 0,
  weight_kg: 1,
  weight_kg_corrected: 1,
  rssi: -120,
  snr: -10,
  lat: 0,
  lon: 0
};
var SENSOR_HIGH = {
  t: 30,
  t_i: 37,
  t_0: 37,
  t_1: 37,
  t_2: 37,
  t_3: 37,
  t_4: 37,
  t_5: 37,
  t_6: 37,
  t_7: 37,
  t_8: 37,
  t_9: 37,
  h: 90,
  l: 10000,
  p: 1100,
  bv: 3.5,
  s_fan_4: 5,
  s_fan_6: 5,
  s_fan_9: 5,
  s_fly_a: 5,
  s_tot: 20,
  s_spl: 80,
  bc_i: 5000,
  bc_o: 5000,
  bc_tot: 10000,
  weight_kg: 100,
  weight_kg_corrected: 100,
  rssi: -50,
  snr: 15,
  lat: 180,
  lon: 180
};
var SENSOR_MAX = {
  t: 50,
  t_i: 50,
  t_0: 50,
  t_1: 50,
  t_2: 50,
  t_3: 50,
  t_4: 50,
  t_5: 50,
  t_6: 50,
  t_7: 50,
  t_8: 50,
  t_9: 50,
  h: 100,
  l: 100000,
  p: 1200,
  bv: 4,
  s_fan_4: 10,
  s_fan_6: 10,
  s_fan_9: 10,
  s_fly_a: 10,
  s_tot: 50,
  s_spl: 140,
  bc_i: 10000,
  bc_o: 10000,
  bc_tot: 20000,
  weight_kg: 125,
  weight_kg_corrected: 125,
  rssi: -40,
  snr: 20,
  lat: 180,
  lon: 180
};
var SENSOR_UNITS = {
  t: '°C',
  t_i: '°C',
  t_0: '°C',
  t_1: '°C',
  t_2: '°C',
  t_3: '°C',
  t_4: '°C',
  t_5: '°C',
  t_6: '°C',
  t_7: '°C',
  t_8: '°C',
  t_9: '°C',
  h: '%RH',
  l: 'lux',
  p: 'mbar',
  bv: 'V',
  s_fan_4: '',
  s_fan_6: '',
  s_fan_9: '',
  s_fly_a: '',
  s_tot: '',
  s_spl: 'dB',
  bc_i: '#',
  bc_o: '#',
  bc_tot: '#',
  weight_kg: 'kg',
  weight_kg_corrected: 'kg',
  rssi: 'dBm',
  snr: 'dB',
  lat: '°',
  lon: '°',
  's_bin098_146Hz': '',
  's_bin146_195Hz': '',
  's_bin195_244Hz': '',
  's_bin244_293Hz': '',
  's_bin293_342Hz': '',
  's_bin342_391Hz': '',
  's_bin391_439Hz': '',
  's_bin439_488Hz': '',
  's_bin488_537Hz': '',
  's_bin537_586Hz': '',
  's_bin_71_122': '',
  's_bin_122_173': '',
  's_bin_173_224': '',
  's_bin_224_276': '',
  's_bin_276_327': '',
  's_bin_327_378': '',
  's_bin_378_429': '',
  's_bin_429_480': '',
  's_bin_480_532': '',
  's_bin_532_583': '',
  's_bin_0_201': '',
  's_bin_201_402': '',
  's_bin_402_602': '',
  's_bin_602_803': '',
  's_bin_803_1004': '',
  's_bin_1004_1205': '',
  's_bin_1205_1406': '',
  's_bin_1406_1607': '',
  's_bin_1607_1807': '',
  's_bin_1807_2008': '',
  'icon': '',
  'precipIntensity': 'mm/h',
  'precipProbability': 'mm/h',
  'precipType': '',
  'temperature': '°C',
  'apparentTemperature': '°C',
  'dewPoint': '°C',
  'humidity': 'x100%RH',
  'pressure': 'hPa',
  'windSpeed': 'm/s',
  'windGust': 'm/s',
  'windBearing': '°',
  'cloudCover': 'x100%',
  'uvIndex': '',
  'visibility': 'km',
  'ozone': 'DU'
};
