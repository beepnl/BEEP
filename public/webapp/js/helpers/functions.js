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

$("[data-header='collapse']").click(function() {
    //Find the box parent........
    var box = $(this).parents(".box").first();
    //Find the body and the footer
    var bf = box.find(".box-body, .box-footer");

    if (!$(this).children().find(".box-tools").children().hasClass("fa-plus")) {
        $(this).children().find(".box-tools").children(".fa-minus").removeClass("fa-minus").addClass("fa-plus");
        bf.slideUp();
    } else {
        //Convert plus into minus
        $(this).children().find(".box-tools").children(".fa-plus").removeClass("fa-plus").addClass("fa-minus");
        bf.slideDown();
    }
});