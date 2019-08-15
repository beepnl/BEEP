/*
 * Kweecker iPad app
 * Author: Neat projects <pim@expertees.nl>
 *
 * Measurements controller for chart measurements 
 */
app.controller('MeasurementsCtrl', function($scope, $rootScope, $timeout, $interval, $routeParams, $location, api, moment, measurements) 
{
    $rootScope.title    = $rootScope.lang.sensors;
    
    $scope.periods      = ['hour','day','week','month','year'];
    $scope.timeZone     = 'Europe/Amsterdam';
    $scope.periodIndex  = 0;
    $scope.activePeriod = 'day';
    $scope.activeUnit   = 'hour';
    $scope.parseFormat  = 'YYYY-MM-DD[T]HH:mm:ssZ';
    $scope.chartParseFmt= 'YYYY-MM-DD[T]HH:mm:ssZ';
    $scope.timeFormat   = 'ddd D MMM YYYY';
    $scope.tooltTimeFrmt= 'ddd D MMM YYYY';
    $scope.displayFormats = {
        "year": 'YYYY MMM D',
        "month": 'YYYY MMM D',
        "quarter": 'YYYYY MMM D',
        "week": '[w]W',
        "day": 'D MMM',
        "hour": 'ddd H[u]',
        "minute": 'HH:mm',
        "second": 'HH:mm:ss',
        "millisecond": 'HH:mm:ss',
    };
    $scope.showChart    = false;
    // $scope.showActuators= true;
    $scope.chartTitle   = null;
    $scope.fontSize     = 15;
    $scope.fontSizeMob  = 10;

    $scope.startTime    = null;
    $scope.endTime      = null;

    $scope.sensors      = [];
    $scope.selectedSensor = null;
    $scope.selectedSensorId = null;
    $scope.sensorMin    = SENSOR_MIN;
    $scope.sensorLow    = SENSOR_LOW;
    $scope.sensorHigh   = SENSOR_HIGH;
    $scope.sensorMax    = SENSOR_MAX;
    $scope.sensorUnits  = SENSOR_UNITS;
    $scope.allinone     = false;

    $scope.measurementData = null;

    $scope.chart         = {};
    $scope.chartLegend   =
        {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize,
                boxWidth: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize+2,
                padding: $rootScope.mobile ? 6 : 10,
                fullWidth: $rootScope.mobile ? false : true,
                // generateLabels: function(chart) 
                // {
                //     console.log('generateLabels');
                //     //console.log(chart.data);
                //     var text = [];
                //     for (var i=0; i<chart.data.datasets.length; i++) 
                //     {
                //         var ds = chart.data.datasets[i];
                //         text.push({text:ds.label, pointStyle:'pointStyleMad'});
                //     }
                //     console.log(text);
                //     return text;
                // },
            },
        };

    $scope.chartScales   =  
        {
            xAxes: 
            [{
                display: true,
                position: "bottom",
                ticks: 
                {
                  autoSkip: true,
                  maxRotation: 0,
                  minRotation: 0,
                  fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize,
                },
                type:"time",
                time: 
                {
                    round: false,
                    parser: $scope.chartParseFmt,
                    tooltipFormat: $scope.timeFormat,
                    displayFormats: $scope.displayFormats,
                },
            }],
            yAxes: 
            [{
                ticks: 
                {
                  fontSize: $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize,
                },
                display: false,
                position:"left",
            }],
        };

    $scope.chart.optionsSensors = 
    {
        legend: angular.copy($scope.chartLegend),
        scales: angular.copy($scope.chartScales),
        elements:
        {
            point: { radius: $rootScope.mobile ? 0.5 : 1, borderWidth: $rootScope.mobile ? 0 : 2, pointHoverBorderWidth: 2, pointBorderColor:'rgba(255,255,255,0)' },
            line: { borderWidth: $rootScope.mobile ? 2 : 3},
        },
        tooltips: 
        {
            mode: 'nearest',
            intersect: true,
            bodySpacing: 5,
            xPadding: 10,
            yPadding: 10,
            displayColors: false,
            callbacks: 
            {
                // title: function(tooltipItem, data) 
                // {
                //     var date = tooltipItem[0].xLabel; // .substr(0, 19)
                //     console.log(date, $scope.tooltTimeFrmt);
                //     return moment(date, $scope.chartParseFmt).format($scope.tooltTimeFrmt);
                // },
                label: function(tooltipItem, data) 
                {
                    var name = data.datasets[tooltipItem.datasetIndex].name;
                    var unit = data.datasets[tooltipItem.datasetIndex].unit;
                    return name + ': ' + round_dec(tooltipItem.yLabel, 1) + ' ' + unit;
                }
            }
        },
        animation: {
            onComplete: function () {
                var ctx = this.chart.ctx;
                ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                
                //console.log(this.data.datasets);
                this.data.datasets.forEach(function (dataset)
                {
                    for (var i = 0; i < dataset.data.length; i++) 
                    {
                        var point = dataset.data[i];
                        if (typeof point != 'undefined' && point.y != null && (i == dataset.data.length-1 || dataset.data[i+1].y == null)) // last point with value
                        {
                            var text      = Math.round(point.y*10)/10 + (dataset.unit != '' ? ' '+dataset.unit : '');
                            var textWidth = ctx.measureText(text).width;
                            for(var key in dataset._meta)
                            {
                                var model = dataset._meta[key].data[i]._model;
                                ctx.fillStyle = 'rgba(255,255,255,0.7)';
                                ctx.fillRect(model.x+3, model.y-8 ,textWidth+4, 15);
                                ctx.fillStyle = "black";
                                ctx.fillText(text, model.x+5, model.y);
                            }
                        }
                    }
                });
            }
        }
    };

    $scope.chart.optionsActuators = 
    {
        legend: angular.copy($scope.chartLegend),
        scales: angular.copy($scope.chartScales),
        elements:
        {
            point: { radius: $rootScope.mobile ? 0.5 : 2, borderWidth: $rootScope.mobile ? 2 : 4, pointHoverBorderWidth: $rootScope.mobile ? 0 : 2, pointBorderColor:'rgba(255,255,255,0)' },
            line: { borderWidth: $rootScope.mobile ? 3 : 8},
        },
        tooltips: 
        {
            mode: 'nearest',
            intersect: true,
            bodySpacing: 5,
            xPadding: 10,
            yPadding: 10,
            displayColors: false,
            callbacks: 
            {
                // title: function(tooltipItem, data) 
                // {
                //     var date = tooltipItem[0].xLabel;
                //     console.log(date, $scope.tooltTimeFrmt);
                //     return moment(date, $scope.chartParseFmt).format($scope.tooltTimeFrmt);
                // },
                label: function(tooltipItem, data) 
                {
                    var name = data.datasets[tooltipItem.datasetIndex].name;
                    var unit = data.datasets[tooltipItem.datasetIndex].unit;
                    return name + ': ' + $rootScope.lang['on'];
                }
            }
        }
        // tooltips: 
        // {
        //     enabled: false,
        // }
    };

    $scope.chart.optionsSound = angular.copy($scope.chart.optionsSensors);
    $scope.chart.optionsDebug = angular.copy($scope.chart.optionsSensors);
    $scope.chart.optionsActuators.legend.position = 'bottom';

    $scope.getSensorName = function(item){ return $rootScope.lang[item.name]; }

    // handle loading of all the settings
    $scope.init = function()
    {
        if ($rootScope.pageSlug == 'measurements')
        {
            if ($routeParams.sensorId != undefined)
                measurements.sensorId = $routeParams.sensorId;

            $scope.setDateLanguage();
            $scope.updateSensors();
            $scope.loadData();
        }
    };

    $scope.dateFormat      = 'yyyy-MM-dd';
    $scope.selectedDate    = '';
    $scope.setDateLanguage = function()
    {
        $("#dtBox").DateTimePicker(
        {
            dateFormat      : $scope.dateFormat, // ISO formatted date
            language        : $rootScope.locale,
            mode            : 'date',
            formatHumanDate : function(dateObj, mode, format)
                                {
                                    var output = '';
                                    output    += dateObj.day + ' ';
                                    output    += parseInt(dateObj.dd) + ' ';
                                    output    += dateObj.month + ' ';
                                    output    += dateObj.yyyy;
                                    return output;
                                },
            afterShow       : function(inputElement)
                                {
                                    $("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set mobile input keyboard to numeric
                                }
        });
    };

    $scope.selectDate = function(selectedDate)
    {
        var p = $scope.activePeriod;
        var d = p + 's';
        
        var selectedMoment = moment(selectedDate);
        var currentMoment  = moment();
        var periodeDiff    = currentMoment.diff(selectedMoment, d);

        if (!isNaN(periodeDiff))
        {
            $scope.periodIndex = periodeDiff;
            $scope.loadData();
        }
        else
        {
            console.log('Error selectDate: '+selectedDate);
        }
    }

    $scope.handleLastSensorValues = function()
    {
        $scope.lastSensorValues = convertOjectToNameArray(measurements.lastSensorValues);
        $scope.lastSensorDate   = moment(measurements.lastSensorDate).format('llll');
    }
    $scope.lastSensorValuesUpdatedHandler = $rootScope.$on('lastSensorValuesUpdated', $scope.handleLastSensorValues);

    $scope.loadLastSensorValuesTimer = null
    $scope.loadLastSensorValues = function(activate=true, force=false)
    {
        if (angular.isDefined($scope.loadLastSensorValuesTimer))
            $interval.cancel($scope.loadLastSensorValuesTimer);

        // Start loading interval
        if (activate && $scope.selectedSensorId)
        {
            // load direct
            if ($scope.lastSensorValues == null || force)
            {
                $scope.lastSensorValues = null;
                $scope.lastSensorDate   = null;
                measurements.loadLastSensorValues();
            }
            // set timer
            if ($scope.periodIndex == 0)
            {
                $scope.loadLastSensorValuesTimer = $interval(function()
                {
                    measurements.loadLastSensorValues();
                }, 20000);
            }
        }
    }



    $scope.updateSensors = function()
    {
        $scope.sensors           = measurements.sensors;
        $scope.selectedSensorId  = measurements.sensorId;
        $scope.selectedSensor    = measurements.getSensorById($scope.selectedSensorId);

        if ($scope.selectedSensorId == null && $scope.sensors.length > 0)
        {
            var id = $scope.sensors[$scope.sensors.length-1].id;
            $scope.loadData(id);
        }

    }
    $scope.sensorHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);

    $scope.loadData = function(id)
    {
        //console.log('sensors:', $scope.sensors);
        //$scope.selectedSensor = null;
        var sensorChanged     = false;
        
        if (($scope.selectedSensor == null || $scope.selectedSensorId != id) && id != null && typeof id != 'undefined')
        {
            $scope.selectedSensorId = id;
            measurements.sensorId   = id;
            $scope.selectedSensor   = measurements.getSensorById(id);
            sensorChanged           = true;
        }

        $scope.loadLastSensorValues(($scope.selectedSensorId != null), sensorChanged);

        var period    = $scope.activePeriod;
        var timeGroup = (period ==  'hour') ? null : period; // get all measurements
        //console.log('loadData', period, $scope.periodIndex);
        
        // $scope.showActuators = (period != 'year');
        $scope.setDataTitle();

        console.log('loadData id',id);
        //var sensorId = $scope.selectedSensorId != null ? $scope.selectedSensor['id'] : null;

        //api.getApiRequest('dataRequest', 'sensors/measurements', 'interval='+period+'&index='+$scope.periodIndex+'&timeGroup='+timeGroup+'&timezone='+$scope.timeZone);
        measurements.loadRemoteSensorMeasurements(period, $scope.periodIndex, timeGroup, $scope.timeZone, id);
    }

    $scope.setDataTitle = function()
    {
        var p = $scope.activePeriod;
        var pi= Math.max(1, $scope.periods.indexOf(p));
        $scope.activeUnit = $scope.periods[pi-1];
        //console.log(p, pi, $scope.activeUnit);

        var d = p + 's';
        var i = $scope.periodIndex; 
        var startTimeFormat = $scope.timeFormat;
        var endTimeFormat   = $scope.timeFormat;
        
        if (p == 'hour')
        {
            endTimeFormat   = 'HH:mm';
            startTimeFormat += ' '+endTimeFormat;
        }
        else if (p == 'day')
        {
            endTimeFormat = null;
        }
        else if (p == 'week')
        {
            p = 'isoweek';
        }

        var ep = p;

        var pStaTime = moment().subtract(i, d).startOf(p);
        var pEndTime = moment().subtract(i, d).endOf(ep);
        
        //console.log('selectedDate = '+$scope.selectedDate);
        var s = pStaTime.format(startTimeFormat);
        var e = pEndTime.format(endTimeFormat);  
        $scope.chartTitle = s + '' + ((endTimeFormat != null) ? ' - ' + e : '');

        $scope.startTime = pStaTime; //.format($scope.parseFormat);
        $scope.endTime   = pEndTime; //.format($scope.parseFormat);

        $scope.selectedDate = pStaTime.format($scope.dateFormat.toUpperCase()); // for moment formatting has to be uppercase
        //console.log(i, startTimeFormat, endTimeFormat, s, e, $scope.startTime.format($scope.timeFormat), $scope.endTime.format($scope.timeFormat));
    }

    $scope.handleDataResult = function(e, data)
    {
        if (data != null && typeof data.id != 'undefined' && ($scope.selectedSensorId == null || $scope.selectedSensorId != data.id))
            $scope.selectedSensorId = data.id;

        //console.log(data);
        if (data != null && typeof data.interval !== 'undefined' && data.interval == $scope.activePeriod && typeof data.index !== 'undefined' && data.index == $scope.periodIndex && typeof data.measurements !== 'undefined' && data.measurements.length > 0)
        {
            $scope.setDataTitle(); // update start and end time

            var fontSize           = $rootScope.mobile ? $scope.fontSizeMob : $scope.fontSize;
            var measurementData    = data.measurements;
            var resolutionCharacter= typeof data.resolution !== 'undefined' ? data.resolution.substr(-1, 1) : null;
            var resolutionFormat   = {
                'w':$scope.displayFormats['week'],
                'd':$scope.displayFormats['day'],
                'h':$scope.displayFormats['hour'],
                'm':$scope.displayFormats['minute'],
                's':$scope.displayFormats['second'],
            }
            var tooltipTimeFormat  = resolutionCharacter != null ? resolutionFormat[resolutionCharacter] : $scope.displayFormats[$scope.activeUnit];
            $scope.tooltTimeFrmt = tooltipTimeFormat;

            //console.log('Parsing '+measurementData.length+' '+data.interval+' '+data.index+' measurementData', 'resolutionCharacter: '+resolutionCharacter, 'tooltipTimeFormat: '+tooltipTimeFormat);
            $scope.measurementData = null;
            $scope.measurementData = convertInfluxMeasurementsArrayToChartObject(measurementData, $rootScope.lang, fontSize, $scope.chartParseFormat);
            
            if ($scope.measurementData != null)
            {
                //console.log($scope.measurementData);
                // Set axes
                $scope.chart.optionsSensors.scales.yAxes    = typeof $scope.measurementData.sensors.yAxes != 'undefined' ? $scope.measurementData.sensors.yAxes : [];
                $scope.chart.optionsSound.scales.yAxes      = typeof $scope.measurementData.sound.yAxes != 'undefined' ? $scope.measurementData.sound.yAxes : [];
                $scope.chart.optionsDebug.scales.yAxes      = typeof $scope.measurementData.debug.yAxes != 'undefined' ? $scope.measurementData.debug.yAxes : [];
                $scope.chart.optionsActuators.scales.yAxes  = typeof $scope.measurementData.actuators.yAxes != 'undefined' ? $scope.measurementData.actuators.yAxes : [];
                
                $scope.chart.optionsSensors.scales.xAxes[0].time.tooltipFormat   = tooltipTimeFormat;
                $scope.chart.optionsSound.scales.xAxes[0].time.tooltipFormat     = tooltipTimeFormat;
                $scope.chart.optionsDebug.scales.xAxes[0].time.tooltipFormat     = tooltipTimeFormat;
                $scope.chart.optionsActuators.scales.xAxes[0].time.tooltipFormat = tooltipTimeFormat;

                //$scope.chart.optionsSensors.scales.xAxes[0].time.unit   = $scope.activeUnit;
                $scope.chart.optionsSensors.scales.xAxes[0].time.min  = $scope.startTime;
                $scope.chart.optionsSound.scales.xAxes[0].time.min    = $scope.startTime;
                $scope.chart.optionsDebug.scales.xAxes[0].time.min    = $scope.startTime;
                $scope.chart.optionsSensors.scales.xAxes[0].time.max  = $scope.endTime;
                $scope.chart.optionsSound.scales.xAxes[0].time.max    = $scope.endTime;
                $scope.chart.optionsDebug.scales.xAxes[0].time.max    = $scope.endTime;
                //$scope.chart.optionsActuators.scales.xAxes[0].time.unit = $scope.activeUnit;
                $scope.chart.optionsActuators.scales.xAxes[0].time.min  = $scope.startTime;
                $scope.chart.optionsActuators.scales.xAxes[0].time.max  = $scope.endTime;

                // console.log($scope.measurementData);
            }
            $scope.showChart = ($scope.measurementData == null) ? false : true;
            //$rootScope.refreshInterface();
        }
        else
        {
            console.log(data, 'MeasurementsCtrl: Empty data result for '+$scope.activePeriod+' '+$scope.periodIndex);

            $scope.showChart = false;
            // $rootScope.refreshInterface();
        }

    }
    $scope.resultHandler      = $rootScope.$on('dataRequestLoaded', $scope.handleDataResult);
    $scope.resultErrorHandler = $rootScope.$on('dataRequestError', $scope.handleDataResult);

    $scope.setPeriod = function(period)
    {
        $scope.activePeriod = period;
        $scope.periodIndex  = 0;
        $scope.loadData();
    }

    $scope.setPeriodIndex = function(offset)
    {
        $scope.periodIndex += offset;
        $scope.loadData();
    }

    $scope.nativeBackbutton = function(e)
    {
        if (runsNative())
        {
            $rootScope.goToPage('/dashboard')
        }
    }
    $scope.backListener = $rootScope.$on('backbutton', $scope.nativeBackbutton);



	// call the init function
	$scope.init();


   	// remove references to the controller
    $scope.removeListeners = function()
    {
        $scope.lastSensorValuesUpdatedHandler();
        $scope.sensorHandler();
        $scope.resultHandler();
        $scope.resultErrorHandler();
        $scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        measurements.stopLoadingMeasurements();
        $scope.loadLastSensorValues(false);
        $scope.removeListeners();
    });

});
