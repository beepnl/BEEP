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
    $scope.exploading = null;
    $scope.csvloading = null;
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
		$scope.exploading = true;
        api.getApiRequest('export', 'export');
	}

    $scope.exportLoaded = function()
    {
        $scope.exploading = false;
    }

    $scope.exportHandler = $rootScope.$on('exportLoaded', $scope.exportLoaded);
    $scope.exportError   = $rootScope.$on('exportError', $scope.exportLoaded);
	

	$scope.updateDevices = function()
    {
        $scope.devices = measurements.sensors;

        if ($scope.devices.length > 0)
        {
            $scope.selectedDeviceId  = measurements.sensorId;
            
            // set selected device, load first if not set 
            if ($scope.selectedDeviceId == null && $scope.devices.length > 0)
                $scope.selectedDeviceId = $scope.devices[0].id;

            $scope.loadDeviceData($scope.selectedDeviceId);
        }
    }
    $scope.deviceHandler = $rootScope.$on('devicesUpdated', $scope.updateDevices);


    $scope.loadDeviceData = function(id, dateIsStart, date)
    {
        if (id != null && typeof id != 'undefined')
        {
			$scope.error_msg 		= null;
			
			var resetDates 			= false;
			// if ($scope.selectedDeviceId != id)
			// 	resetDates = true;

            var start = $scope.startDate;
            var end   = $scope.endDate;

            $scope.selectedDeviceId = id;
            $scope.selectedDevice   = measurements.getSensorById(id);

            if ($scope.startDate == null || resetDates)
                start = moment().add(-1, 'weeks').toDate(); //$scope.selectedDevice.start.substr(0,10);

            if ($scope.endDate == null || resetDates)
                end = moment().toDate(); //$scope.selectedDevice.end.substr(0,10);

            if (typeof date != 'undefined')
            {
                if (dateIsStart)
                    start = date;
                else
                    end = date;
            }

            $scope.startDate = start;
            $scope.endDate = end;

        	$scope.fileName = $scope.selectedDevice.name + '_' + moment(start).format('YYYY-MM-DD') + '_' + moment(end).format('YYYY-MM-DD') + '.csv';

        	// load measurement types
        	$scope.measurementTypes = [];
        	$scope.loadMeasurementNamesAvailable();
        }
    }

    $scope.updateMeasurementTypes = function(e, data)
    {
    	$scope.dataAvailable    = Object.keys(data).length > 0 ? true : false;
    	$scope.measurementTypes = data;
        $scope.refreshSelectedMeasurementTypes();
    }
	
    $scope.refreshSelectedMeasurementTypes = function()
    {
        $scope.selectMeasurementTypes($scope.selectedMeasurementTypes);
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
		$scope.error_msg  = null;
		$scope.csvloading = true;
		var options = {'device_id': $scope.selectedDeviceId, 'start': moment($scope.startDate).format('YYYY-MM-DD'), 'end':moment($scope.endDate).format('YYYY-MM-DD'), 'separator':$scope.separator, 'measurements':$scope.selectedMeasurementNames};
		api.postApiRequest('exportCsv', 'export/csv', options);
	}

	$scope.downloadCsvData = function(e, data)
	{
        $scope.csvloading = false;
        exportToCsv($scope.fileName, data);
	}
	$scope.exportCsvHandler = $rootScope.$on('exportCsvLoaded', $scope.downloadCsvData);


	$scope.errorCsvHandler = function(type, data)
	{
		$scope.csvloading = false;

		if (data.status === -1)
			$scope.error_msg = $rootScope.lang.too_much_data;
		else if (type.name == 'exportCsvError')
        {
            $scope.error_msg = $rootScope.lang.no_chart_data;
        }
        else if (type.name == 'measurementTypesAvailableError' && data.message == 'influx-query-empty')
		{
			$scope.error_msg = $rootScope.lang.no_chart_data;
			$scope.dataAvailable = false;
		}
		else
			$scope.error_msg = $rootScope.lang.no_data;

	}
	$scope.exportCsvError   	= $rootScope.$on('exportCsvError', $scope.errorCsvHandler);
    $scope.measurementTypeError = $rootScope.$on('measurementTypesAvailableError', $scope.errorCsvHandler);
	
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
    	$scope.deviceHandler();
        $scope.backListener();
    	$scope.exportHandler();
        $scope.exportCsvError();
        $scope.exportCsvHandler();
    	$scope.exportCsvError();
    	$scope.measurementTypeHandler();
    	$scope.measurementTypeError();
    };


});