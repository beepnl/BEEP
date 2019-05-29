/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('SensorsOldCtrl', function($scope, $rootScope, $window, $location, $filter, settings, api, measurements, moment) 
{

	$rootScope.title    	= $rootScope.lang.overview_title;
	$scope.showMore 		= false; // multiple hives
	$scope.sensorSelected 	= null;
	$scope.measurementDate 	= null;
	$scope.measurementError	= false;
	$scope.measurementData 	= {};

    var grey     			= solidColorObj('rgba(169, 132, 49, 0.5)', 'rgba(51, 51, 51, 1)');
	$scope.chart 			= {};
    $scope.chart.colors  	= [grey];
    $scope.chart.options 	= {
        datasets:[{
        	fill: false
        }],
        scales: {
            xAxes: [{
                display: true,
                position: "bottom",
                type:"time",
                time: {
                	displayFormats: {
                        second: "H:mm:ss",
                        minute: "H:mm",
                        hour: "H:mm",
                    }
                }
            }],
            yAxes: [{
                display: true,
                position:"left"
            }]
        }
    };
    $scope.onClick = function (points, evt) {
	    console.log(points, evt);
	  };

	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}

		// get the measurements
		measurements.startLoadingMeasurements();
	};



	$scope.updateMeasurements = function(e, type)
	{
		// parse the sensors
		$scope.sensors        		= convertOjectToArray(measurements.sensors);
		$scope.measurementDate 		= measurements.measurementDate;
		$scope.measurementsHeader	= $rootScope.lang.measurements + " - " + moment($scope.measurementDate).format('llll');
		$scope.measurementError 	= (moment().diff( moment($scope.measurementDate), 'days', true ) > 2);
		$scope.measurementData		= convertSensorMeasurementsArrayToChartObject(measurements.sensor_measurements);
		
		if ($scope.sensorSelected == null)
			$scope.selectSensor(measurements.getSensorById('weight_kg_corrected'));
	};

	$scope.showMeasurementError = function()
	{
		if ($scope.measurementError)
		{
			var msec_behind	= moment($scope.measurementDate).diff(moment()); // negative duration
			console.log('sec_behind='+msec_behind/1000);
			var time_behind	= moment.duration(msec_behind).humanize(true); // textual moment ago
			var message_text= $rootScope.lang.last_measurement_was + " " + time_behind + ", " + $rootScope.lang.at + " " + moment($scope.measurementDate).format('LLLL');
			$rootScope.showMessage(message_text, null, $rootScope.lang.error);
		}
		else
		{
			console.log('no measurement error');
		}
	}

	$scope.updateMeasurementsError = function(e, type)
	{
		// parse the sensors
		$scope.measurementsHeader 	= $rootScope.lang.measurementsError;
	};

	$scope.loadMeasurements = function()
	{
		$scope.selectSensor($scope.sensorSelected);
	}

	$scope.sensorMeasurementHandler = $rootScope.$on('measurementsLoaded', $scope.loadMeasurements);
	$scope.measurementHandler 		= $rootScope.$on('measurementsUpdated', $scope.updateMeasurements);
	$scope.measurementErrorHandler 	= $rootScope.$on('measurementsError', $scope.updateMeasurementsError);


	$scope.back = function()
	{
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else
		{
			$rootScope.historyBack();
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	$scope.selectSensor = function(sensor)
	{
		if (typeof sensor != 'undefined' && sensor != null && typeof(sensor) == "object")
		{
			$scope.sensorSelected = sensor;
		}
		measurements.loadRemoteSensorMeasurements($scope.sensorSelected.id);
	}

	$scope.showMeasurement = function(obj, expected)
    {
        if (obj != null && typeof(obj) == "object")
        {
            return obj.value == "-" ? false : true
        }
        return false;
    }



   	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.sensorMeasurementHandler();
		$scope.measurementHandler();
		$scope.measurementErrorHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        measurements.stopLoadingMeasurements();
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});