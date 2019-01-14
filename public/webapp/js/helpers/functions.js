/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */


var runsNative = function()
{
	if(typeof cordova == 'undefined')
	{
		return false;
	}

	return true;	
};

var propertyValueInObject = function(obj, property, value, callback)
{
  if (typeof obj == 'array')
  {
    for (var i = obj.length - 1; i >= 0; i--) 
    {
      var item = obj[i];
      if (item.hasOwnProperty(property) && item[property] == value)
      {
        callback(value);
      }
    }
  }
  else if (typeof obj == 'object')
  {
    for (var o in obj) 
    {
      var item = obj[o];
      //console.log(item.hasOwnProperty(property), property, item[property], value, item.children.length, item);
      if (item.children.length > 0)
      {
        propertyValueInObject(item.children, property, value, callback);
      }
      else if (item.hasOwnProperty(property) && item[property] == value)
      {
        callback(value);
      }
    }
  }
}

var range = function(n)
{
  return new Array(n);
};

var convertOjectToArray = function(obj)
{
  var array = [];
  for (var i in obj) {
    array.push(obj[i]);
  }
  return array;
}

var convertOjectToNameArray = function(obj)
{
  var array = [];
  for (var i in obj) {
    array.push({'name':i, 'value':obj[i]});
  }
  return array;
}


var fixOjectNames = function(obj, postFix='', prefix='')
{
  for (var i in obj) 
  {
    obj[prefix+i+postFix] = obj[i];
    delete obj[i];
  }
  return obj;
}

var convertOjectToFormDataArray = function(obj, nameAdd)
{
  if (typeof(nameAdd) == 'undefined')
    nameAdd = '';

  var array = [];
  for (var i in obj) {
    array.push(i+nameAdd+"="+obj[i]);
  }
  return array;
}

// Chart functions
var solidColorObj = function(rgbaStr, borderRgbaStr) // This is the only way to pass a losid color value, RGB values get converted to alpha 0.2 in angular-chart.js
{
    cObj = {
        backgroundColor: rgbaStr,
        pointBackgroundColor: rgbaStr,
        pointHoverBackgroundColor: rgbaStr
    };
    
    if (borderRgbaStr)
    {
        cObj.borderColor = borderRgbaStr;
        cObj.pointBorderColor = borderRgbaStr;
        cObj.pointHoverBorderColor = borderRgbaStr;
    }

    return cObj;
};

var convertInfluxMeasurementsArrayToChartObject = function(obj_arr, lang, labelSize, timeParseFormat)
{
  if(obj_arr.length == 0)
  {
    console.log('convertSensorMeasurementsArrayToChartObject has no data');
    return null;
  }

  console.log('Converting '+obj_arr.length+' Influx measurements to chart object');
  //console.log(labelSize);

  var yAxisL  = {display: true, position: 'left', id:'y1', scaleLabel:{display:true, labelArray:[], labelString:'', fontSize: labelSize}, ticks: { fontSize: labelSize } };
  var yAxisR  = {display: false, position: 'right', id:'y2', scaleLabel:{display:true, labelArray:[], labelString:'', fontSize: labelSize}, ticks: { fontSize: labelSize } };
  var noAxis  = {display: false, offsetGridLines: true};
  
  var sensors  = {datasets:[], series:[], data:[], colors:[], yAxes:[angular.copy(yAxisL), angular.copy(yAxisR)]};
  var debug    = {datasets:[], series:[], data:[], colors:[], yAxes:[angular.copy(yAxisL), angular.copy(yAxisR)]};
  var sound    = {datasets:[], series:[], data:[], colors:[], yAxes:[angular.copy(yAxisL), angular.copy(yAxisR)]};
  var actuators= {datasets:[], series:[], data:[], colors:[], yAxes:[noAxis], labels:[]};
  var obj_out  = {sensors:sensors, actuators:actuators, sound:sound, debug:debug};
  
  var unitLenMx= 10; // max length of unit in y-scale

  var dataset = {label:'', name:'', unit:'', visible:false, yAxisID:'y1', cubicInterpolationMode:'linear', lineTension:0, fill:false, steppedLine:false};

  // Fill datasets with sensor/actuator names
  for (var name in obj_arr[0])
  {
    if (name != 'time')
    {
      var quantity = (typeof SENSOR_NAMES[name] !== 'undefined') ? SENSOR_NAMES[name] : null;
      
      if (quantity != null)
      {
        var isSensor     = SENSORS.indexOf(name) > -1 ? true : false; 
        var isSound      = SOUND.indexOf(name) > -1 ? true : false; 
        var isDebug      = DEBUG.indexOf(name) > -1 ? true : false; 
        var isActuator   = isSensor || isSound || isDebug ? false : true; 
        
        var chart         = isSensor ? obj_out.sensors : isSound ? obj_out.sound : isDebug? obj_out.debug : obj_out.actuators; // sensor or other output
        var new_dataset   = angular.copy(dataset); 
        
        var quantityUnit  = (SENSOR_UNITS[name] !== 'undefined') ? SENSOR_UNITS[name] : null;
        var readableName  = (typeof lang[quantity] !== 'undefined') ? lang[quantity] : quantity;
        var nameAndUnit   = (quantityUnit != null && quantityUnit != '') ? readableName + ' ('+quantityUnit+')' : readableName;
        var abbrName      = readableName.substring(0, unitLenMx);
       
        var rgb   = (typeof SENSOR_COLOR[name] !== 'undefined') ? SENSOR_COLOR[name] : {r:150, g: 150, b:150};
        var color = solidColorObj('rgba('+rgb.r+','+rgb.g+','+rgb.b+', 0.1)', 'rgba('+rgb.r+','+rgb.g+','+rgb.b+', 1)');
        //console.log(name, color);

        if (!isActuator)
        {
          // set y-axis label
          var axisUnit = quantityUnit == null || quantityUnit == '' ? abbrName : quantityUnit;
          axisUnit = ' ' + axisUnit;
          if (axisUnit != ' ' && chart.yAxes[0].scaleLabel.labelArray.indexOf(axisUnit) == -1)
            chart.yAxes[0].scaleLabel.labelArray.push(axisUnit);

          new_dataset.pointBorderWidth = 2;    // dots
        }
        else
        {
          new_dataset.yAxisID              = null; // no y axis
          new_dataset.pointBorderColor     = 'rgba('+rgb.r+','+rgb.g+','+rgb.b+', 1)'; // solid dots
          new_dataset.pointBackgroundColor = 'rgba('+rgb.r+','+rgb.g+','+rgb.b+', 1)'; // solid dots
        }

        new_dataset.label = nameAndUnit;
        new_dataset.name  = readableName;
        new_dataset.unit  = quantityUnit;

        chart.colors.push(color);
        chart.datasets.push(new_dataset);
        chart.series.push(name);
        chart.data.push([]);

        //console.table(chart);
      }
    }
  }


  // Fill datasets with sensor/actuator data
  for (var i = 0; i < obj_arr.length; i++)
  {
    var obj        = obj_arr[i];
    var time       = obj['time'].length > 19 ? obj['time'].substr(0,19) + 'Z' : obj['time']; // YYYY-MM-DD[T]HH:mm:ss[Z]
    if (obj['time'].length <= 19)
    {
      var timeParsed = moment(time, timeParseFormat).format('X');
      time = parseInt(timeParsed);
      // console.log('time (<=19): '+obj['time'],'cut-off: '+time, 'parsed: '+timeParsed);
    }

    var firstLast= i == 0 || i == obj_arr.length-1 ? true : false;
    var highestActuatorY = 1;
    var afterNow = false;

    if (typeof time !== 'undefined')
    {
      afterNow = (moment(time).format('X') > moment().format('X'));
    }
    //obj_out.actuators.labels.push(time);

    var dataSetIndex = -1;
    for (var name in obj)
    {
      if (name != 'time')
      {
        var val          = obj[name];
        var unit         = (SENSOR_UNITS[name] !== 'undefined') ? SENSOR_UNITS[name] : null;
        var isSensor     = SENSORS.indexOf(name) > -1 ? true : false; 
        var isSound      = SOUND.indexOf(name) > -1 ? true : false; 
        var isDebug      = DEBUG.indexOf(name) > -1 ? true : false; 
        var isActuator   = isSensor || isSound || isDebug ? false : true; 

        var chart        = isSensor ? obj_out.sensors : isSound ? obj_out.sound : isDebug? obj_out.debug : obj_out.actuators; // sensor or other output
        var dataSetIndex = chart.series.indexOf(name);
        
        if (dataSetIndex > -1)
        {
          if (typeof chart.data[dataSetIndex] == 'undefined')
            console.log('chart.data has no index: '+dataSetIndex);

          // fill sensor data
          if (!isActuator)
          {
            if (val != null || firstLast)
            {
              //console.log(name, val, dataSetIndex, firstLast);
              if (Math.abs(val) > 100 && chart.series.length > 1) // transfer unit to y-scale 2
              {
                chart.datasets[dataSetIndex].yAxisID = 'y2';
                chart.yAxes[1].display = true;
                var label = '';
                
                if (unit != null && unit != '' && chart.yAxes[1].scaleLabel.labelArray.indexOf(unit) == -1)
                {
                  label = unit;
                }
                else // try to transfer abbr name
                { 
                  var abbrName = ' ';
                  var quantity = (typeof SENSOR_NAMES[name] !== 'undefined') ? SENSOR_NAMES[name] : null;
                  if (quantity != null)
                  {
                    var readableName  = (typeof lang[quantity] !== 'undefined') ? lang[quantity] : quantity;
                    abbrName += readableName.substring(0, unitLenMx);
                  }
                  if (abbrName != null && abbrName != ' ' && chart.yAxes[1].scaleLabel.labelArray.indexOf(abbrName) == -1)
                    label = abbrName;
                }
                // set axis label
                if (label != '')
                {
                  var index = chart.yAxes[0].scaleLabel.labelArray.indexOf(label);
                  if (index > -1)
                  {
                    chart.yAxes[0].scaleLabel.labelArray.splice(index, 1);
                    chart.yAxes[1].scaleLabel.labelArray.push(label);
                  }
                }
              }
              chart.data[dataSetIndex].push({x:time, y:val});
            }
          }
          else // fill actuator horizontal lines
          {
            // var dataIndex     = chart.data[dataSetIndex].length;
            // var actuatorY     = (ACTUATOR_INDEX[name] !== 'undefined') ? ACTUATOR_INDEX[name] : dataIndex + 1;
            // var actuatorUnit  = (SENSOR_UNITS[name] !== 'undefined') ? SENSOR_UNITS[name] : '';
            // highestActuatorY  = Math.max(actuatorY, highestActuatorY);
            // var previousVal   = dataIndex == 0 ? null : chart.data[dataSetIndex][dataIndex-1];
            // var valueOn       = actuatorUnit == '' && val > 0.5 ? actuatorY : actuatorUnit == '%' && val > 50 ? actuatorY : null;
            // var continuousVal = afterNow ? null : val == null ? previousVal : valueOn;
            // //var continuousVal = val > 0 ? actuatorY : null;
            // chart.data[dataSetIndex].push(continuousVal);
          }
        }
      }
    }
  }

  // Fill sensor axis labels
  for (axisIndex in obj_out.sensors.yAxes) 
  {
    var axisLabels = obj_out.sensors.yAxes[axisIndex].scaleLabel.labelArray.join();
    obj_out.sensors.yAxes[axisIndex].scaleLabel.labelString = axisLabels;
  }
  // Fill actuator axis 
  var labelAmount = obj_out.actuators.series.length;
  obj_out.actuators.yAxes[0].ticks = {min:0, max:highestActuatorY+1};
  
  //console.log(obj_out);

  return obj_out;
}


var convertSensorMeasurementsArrayToChartObject = function(obj_arr)
{
  var obj_out = {data:[], labels:[], series:[]};
  var series_index = -1;
  for (var i = 0; i < obj_arr.length; i++)
  {
    var m = obj_arr[i];
    if (obj_out.series.indexOf(m.name) == -1)
    {
      series_index ++;
      obj_out.series.push(m.name);
      //obj_out.series[series_index] = m.name;
      //obj_out.data[series_index] = [];
      //obj_out.labels[series_index] = [];
    }
    obj_out.data.push(m.value);
    obj_out.labels.push(m.time);
  }
  return obj_out;
}


// Settings
var convertSettingJsonToObject = function(json)
{
  var out = {};
  for (var i in json) {
    var o = json[i];
    if (o.name != "")
      out[o.name] = o.value;
  }
  return out;
}

var percDiffOf = function(tot, num)
{
  return tot > 0 ? (Math.abs(tot - num) / tot) * 100 : num > 0 ? 100 : 0;
}

var round_dec = function(num, dec)
{
  return Math.round(num * Math.pow(10, dec)) /Math.pow(10, dec);
}

var number_format = function(number, decimals, decPoint, thousandsSep) 
{
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
  var n = !isFinite(+number) ? 0 : +number
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
  var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
  var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
  var s = ''

  var toFixedFix = function (n, prec) {
    var k = Math.pow(10, prec)
    return '' + (Math.round(n * k) / k)
      .toFixed(prec)
  }

  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || ''
    s[1] += new Array(prec - s[1].length + 1).join('0')
  }

  return s.join(dec)
};




var versionCompare = function(v1, v2, options) 
{
    var lexicographical = options && options.lexicographical,
        zeroExtend = options && options.zeroExtend,
        v1parts = v1.split('.'),
        v2parts = v2.split('.');

    var isValidPart = function(x) {
        return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
    }

    if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
        return NaN;
    }

    if (zeroExtend) {
        while (v1parts.length < v2parts.length) v1parts.push("0");
        while (v2parts.length < v1parts.length) v2parts.push("0");
    }

    if (!lexicographical) {
        v1parts = v1parts.map(Number);
        v2parts = v2parts.map(Number);
    }

    for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length == i) {
            return 1;
        }

        if (v1parts[i] == v2parts[i]) {
            continue;
        }
        else if (v1parts[i] > v2parts[i]) {
            return 1;
        }
        else {
            return -1;
        }
    }

    if (v1parts.length != v2parts.length) {
        return -1;
    }

    return 0;
}

function randomString(length=16) {
  var text = "";
  var possible = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz0123456789"; // excluded o and O to avoid confusion with 0

  for (var i = 0; i < length; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));

  return text;
}

/*
 * Natural Sort algorithm for Javascript - Version 0.7 - Released under MIT license
 * Author: Jim Palmer (based on chunking idea from Dave Koelle)
 */
 function naturalSort (a, b) {
    var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
        sre = /(^[ ]*|[ ]*$)/g,
        dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
        hre = /^0x[0-9a-f]+$/i,
        ore = /^0/,
        i = function(s) { return naturalSort.insensitive && (''+s).toLowerCase() || ''+s },
        // convert all to strings strip whitespace
        x = i(a).replace(sre, '') || '',
        y = i(b).replace(sre, '') || '',
        // chunk/tokenize
        xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
        yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
        // numeric, hex or date detection
        xD = parseInt(x.match(hre)) || (xN.length != 1 && x.match(dre) && Date.parse(x)),
        yD = parseInt(y.match(hre)) || xD && y.match(dre) && Date.parse(y) || null,
        oFxNcL, oFyNcL;
    // first try and sort Hex codes or Dates
    if (yD)
        if ( xD < yD ) return -1;
        else if ( xD > yD ) return 1;
    // natural sorting through split numeric strings and default strings
    for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
        // find floats not starting with '0', string or 0 if not defined (Clint Priest)
        oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
        oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0;
        // handle numeric vs string comparison - number < string - (Kyle Adams)
        if (isNaN(oFxNcL) !== isNaN(oFyNcL)) { return (isNaN(oFxNcL)) ? 1 : -1; }
        // rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
        else if (typeof oFxNcL !== typeof oFyNcL) {
            oFxNcL += '';
            oFyNcL += '';
        }
        if (oFxNcL < oFyNcL) return -1;
        if (oFxNcL > oFyNcL) return 1;
    }
    return 0;
}

// $(document).ready(function() {

//   $("[data-widget='collapse']").click(function() {
//       //Find the box parent........
//       var box = $(this).parents(".box").first();
//       //Find the body and the footer
//       var bf = box.find(".box-body, .box-footer");

//       if (!$(this).children().find(".box-tools").children().hasClass("fa-plus")) {
//           $(this).children().find(".box-tools").children(".fa-minus").removeClass("fa-minus").addClass("fa-plus");
//           bf.slideUp();
//       } else {
//           //Convert plus into minus
//           $(this).children().find(".box-tools").children(".fa-plus").removeClass("fa-plus").addClass("fa-minus");
//           bf.slideDown();
//       }
//   });

// });