/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Settings controller
 */
app.controller('SettingsCtrl', function($scope, $rootScope, $window, $timeout, $location, $filter, $interval, api, $routeParams, ngDialog, settings, hives, measurements) 
{

    // settings
    $scope.hives                 = [];
    $scope.sensors               = [];
    $scope.weightSensors         = [];
    $scope.weightSensorsCalibrate= {};
    $scope.lastSensorValues      = [];
    $scope.lastSensorDate        = null;
    $scope.selectedSensorId      = null;
    $scope.selectedSensor        = null;
    $scope.calibrate_msg         = null;
    $scope.calibrating           = false;

    // settings (readonly)
    $scope.firmware              = settings.firmware_version;
    $scope.updated_at            = settings.updated_at;
    $scope.userType              = settings.type;

    // handlers
    $scope.isChecking            = false;
    $scope.isLoading             = false;
    $scope.isAutomatic           = ($scope.connection_type == 'automatic') ? true : false;
    //$scope.changed               = false;

    $scope.init = function()
    {
        $scope.hives             = hives.hives;
        $scope.sensors           = measurements.sensors;
        $scope.selectedSensorId  = measurements.sensorId;
        $scope.selectedSensor    = measurements.getSensorById($scope.selectedSensorId);
        $scope.calibrate_weight  = settings.settings.calibrate_weight;
        $scope.getSensorValues();
    };

    $scope.inSensorNames = function(a, b)
    {
        return typeof SENSOR_NAMES[a] != 'undefined';
    }
    $scope.nonZeroWeight = function(a)
    {
        return (a.value != 0 && a.name != 'id');
    }

    $scope.updateSensors = function()
    {
        $scope.sensors = measurements.sensors;
    }
    $scope.sensorsUpdatedHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);

    $scope.updateWeightSensors = function()
    {
        $scope.weightSensors = convertOjectToNameArray(measurements.weightSensors);
    }
    $scope.weightSensorsUpdatedHandler = $rootScope.$on('weightSensorsUpdated', $scope.updateWeightSensors);


    $scope.getSensorValues = function(id)
    {
        if (typeof id != 'undefined')
            $scope.selectedSensorId = id;

        if ($scope.selectedSensorId)
        {
            $scope.selectedSensor = measurements.getSensorById($scope.selectedSensorId);
            $scope.lastSensorDate = null;
            measurements.loadLastWeightSensorValues($scope.selectedSensorId);
            $scope.loadLastSensorValues();
        }
    }
    
    $scope.handleLastSensorValues = function()
    {
        if (measurements.lastSensorValues.calibrating_weight)
        {
            $scope.calibrating   = true;
            $scope.calibrate_msg = $rootScope.lang.calibration_started;
            //$scope.loading       = true;
            $scope.calibrate_weight = measurements.lastSensorValues.calibrating_weight;
        }
        else
        {
            $scope.calibrating   = false;
            $scope.calibrate_msg = $rootScope.lang.calibration_ended;
        }

        //console.log(measurements.lastSensorValues);
        
        $scope.lastSensorValues = convertOjectToNameArray(measurements.lastSensorValues);
        $scope.lastSensorDate   = moment(measurements.lastSensorDate).format('llll');

    }
    $scope.lastSensorValuesUpdatedHandler = $rootScope.$on('lastSensorValuesUpdated', $scope.handleLastSensorValues);

    $scope.loadLastSensorValuesTimer = null
    $scope.loadLastSensorValues = function(activate=true)
    {
        if (angular.isDefined($scope.loadLastSensorValuesTimer))
            $interval.cancel($scope.loadLastSensorValuesTimer);

        // Start loading interval
        if (activate && $scope.selectedSensorId)
        {
            $scope.loadLastSensorValuesTimer = $interval(function()
            {
                $scope.getSensorValues();
            }, 10000);
        }
    }


    $scope.offsetWeight = function()
    {
        // get weight from sensors, save offsets
        if ($scope.selectedSensorId)
            measurements.weightOffset({'id':$scope.selectedSensorId});
    }

    $scope.calibrateWeight = function(weight_kg)
    {
        if ($scope.selectedSensorId && weight_kg)
            measurements.weightCalibration({'id':$scope.selectedSensorId,'weight_kg':weight_kg});
    }
    $scope.calibrateWeightHandler = $rootScope.$on('weightCalibrationLoaded', $scope.calibrate);

    $scope.calibrate = function(e, data)
    {
        if (data == 'calibrating_weight')
        {
            $scope.calibrating   = true;
            $scope.calibrate_msg = $rootScope.lang.calibration_started;
        }
        else
        {
            $scope.calibrating   = false;
            $scope.calibrate_msg = $rootScope.lang.calibration_ended;
        }
    }


    $scope.saveSettings = function(e)
    {
        var data = 
        {
            hives        : $scope.hives,
            sensors      : $scope.sensors,
            // actuator_settings :
        };

        // set loading
        $scope.isLoading = true;

        // save the settings to the server
        settings.saveSettings(data);


        console.log('settings saved');
    };



    $scope.settingsSavedHandler = $rootScope.$on('saveSettingsLoaded', function(e, data)
    {
        // reset the save button
        $scope.isLoading = false;

    });


    $scope.settingsErrorHandler = $rootScope.$on('saveSettingsError', function(e, data)
    {
        // message
        $rootScope.showMessage('Instellingen konden niet worden opgeslagen', null, 'Instelligen', $rootScope.lang.ok);

        $scope.isLoading = false;

        // set the status to offline
        //settings.setStatus('offline'); // Why taking the status of the app offline? Only because the settings can not be saved on the server?
    });


    //refresh iscroll
    $scope.refreshIscroll = function()
    {
        // $timeout( function()
        // {
        // 	if(typeof $rootScope.myScroll['settings-form-wrapper'] != 'undefined')
        //     	$rootScope.myScroll['settings-form-wrapper'].refresh();
        // }, 200);
    }

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


    $scope.init();


    // remove the listeners
    $scope.$on('$destroy', function() 
    {
        $scope.loadLastSensorValues(false);
        $scope.removeListeners();
    });


    // remove listeners
    $scope.removeListeners = function()
    {
        $scope.settingsSavedHandler();
        $scope.settingsErrorHandler();
        $scope.sensorsUpdatedHandler();
        $scope.lastSensorValuesUpdatedHandler();
        $scope.calibrateWeightHandler();
        $scope.weightSensorsUpdatedHandler();
        $scope.backListener();
    };

});

