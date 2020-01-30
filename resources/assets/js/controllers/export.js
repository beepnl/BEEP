/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */
app.controller('ExportCtrl', function($scope, $rootScope, $window, $location, $routeParams, api, measurements, moment) 
{

	// set the title
	$rootScope.title  = $rootScope.lang.Data_export;
	
	$scope.message	  = null;
	$scope.error   	  = null;
	$scope.error_msg  = null;

	$scope.timeZone   = 'Europe/Amsterdam';

	$scope.startDate    = null;
    $scope.endDate      = null;
    $scope.fileName     = null;
    $scope.separator    = ';';

    $scope.dataAvailable  = false;
    $scope.devices        = [];
    $scope.selectedDevice = null;
    $scope.selectedDeviceId = null;
    
    $scope.measurementTypes = [];
    $scope.selectedMeasurementTypes = [];
    $scope.selectedMeasurementNames = [];

	$scope.init = function()
	{
		// Check locale
		if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale)
		{
            $rootScope.switchLocale($routeParams.language);
			$location.search('language', null);
		}
		$scope.updateDevices();
	};

	$scope.exportData = function()
	{
		api.getApiRequest('export', 'export');
	}
	

	$scope.updateDevices = function()
    {
        $scope.devices           = measurements.sensors;
        $scope.selectedDeviceId  = measurements.sensorId;
        
        // set selected device, load first if not set 
        if ($scope.selectedDeviceId == null && $scope.devices.length > 0)
            $scope.selectedDeviceId = $scope.devices[0].id;

        $scope.loadDeviceData($scope.selectedDeviceId);
    }
    $scope.deviceHandler = $rootScope.$on('devicesUpdated', $scope.updateDevices);


    $scope.loadDeviceData = function(id)
    {
        if (id != null && typeof id != 'undefined')
        {
			$scope.error_msg 		= null;
			
			var resetDates 			= false;
			if ($scope.selectedDeviceId != id)
				resetDates = true;

            $scope.selectedDeviceId = id;
            $scope.selectedDevice   = measurements.getSensorById(id);

            if ($scope.startDate == null || resetDates)
            	$scope.startDate 	= moment().add(-1, 'weeks').toDate(); //$scope.selectedDevice.start.substr(0,10);

            if ($scope.endDate == null || resetDates)
        		$scope.endDate   		= moment().toDate(); //$scope.selectedDevice.end.substr(0,10);

        	$scope.fileName = $scope.selectedDevice.name + '_' + moment($scope.startDate).format('YYYY-MM-DD') + '_' + moment($scope.endDate).format('YYYY-MM-DD') + '.csv';
        	
        	// load measurement types
        	$scope.measurementTypes = [];
        	$scope.loadMeasurementNamesAvailable();
        }
    }

    $scope.updateMeasurementTypes = function(e, data)
    {
    	$scope.dataAvailable    = Object.keys(data).length > 0 ? true : false;
    	$scope.measurementTypes = data;
    }
	

    $scope.selectMeasurementTypes = function(types)
    {
    	$scope.selectedMeasurementNames = [];
    	for (var i = 0; i < types.length; i++) 
    	{
    		var typeName = types[i].abbreviation;
    		$scope.selectedMeasurementNames.push(typeName);
    	}
    	//console.log($scope.selectedMeasurementNames); 
    }
    
    $scope.loadMeasurementNamesAvailable = function()
    {
		var options 	 = {'device_id': $scope.selectedDeviceId, 'start': moment($scope.startDate).format('YYYY-MM-DD'), 'end':moment($scope.endDate).format('YYYY-MM-DD')};
		api.getApiRequest('measurementTypesAvailable', 'sensors/measurement_types_available', options);
    }
    $scope.measurementTypeHandler = $rootScope.$on('measurementTypesAvailableLoaded', $scope.updateMeasurementTypes);

    $scope.setSeparator = function(separator)
    {
    	$scope.separator = separator;
    }

	$scope.exportSensorData = function()
	{
		$scope.error_msg = null;
		var options = {'device_id': $scope.selectedDeviceId, 'start': moment($scope.startDate).format('YYYY-MM-DD'), 'end':moment($scope.endDate).format('YYYY-MM-DD'), 'separator':$scope.separator, 'measurements':$scope.selectedMeasurementNames};
		api.postApiRequest('export', 'export/csv', options);
	}

	$scope.downloadData = function(e, data)
	{
		exportToCsv($scope.fileName, data);
	}
	$scope.exportHandler = $rootScope.$on('exportLoaded', $scope.downloadData);


	$scope.errorHandler = function(type, data)
	{
		console.log('Export errorHandler', type, data);
		if (data.status === -1)
			$scope.error_msg = $rootScope.lang.too_much_data;
		else if (data.message === 'influx-query-empty')
		{
			$scope.error_msg = $rootScope.lang.no_chart_data;
			$scope.dataAvailable = false;
		}
		else
			$scope.error_msg = $rootScope.lang.no_data;

	}
	$scope.exportError   		= $rootScope.$on('exportError', $scope.errorHandler);
    $scope.measurementTypeError = $rootScope.$on('measurementTypesAvailableError', $scope.errorHandler);
	
	$scope.back = function()
	{
		$location.path('/login');
	};

	$scope.backListener = $rootScope.$on('backbutton', $scope.back);

	$scope.init();


	// remove the listeners
	$scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });


    // remove listeners
    $scope.removeListeners = function()
    {
    	$scope.backListener();
    	$scope.exportHandler();
    	$scope.exportError();
    	$scope.measurementTypeHandler();
    	$scope.measurementTypeError();
    };


});