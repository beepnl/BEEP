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
        $scope.calibrate_weight  = settings.settings.calibrate_weight;
        $scope.updateWeightSensors();
        $scope.handleLastSensorValues();
        $scope.refreshIscroll();
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
            measurements.loadLastSensorValues($scope.selectedSensorId);
            $scope.loadLastSensorValues();
        }
    }
    
    $scope.handleLastSensorValues = function()
    {
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
        if (measurements.weightSensors && $scope.selectedSensorId)
        {
            var weightObj = measurements.weightSensors;
            weightObj.id  = $scope.selectedSensorId;
            settings.saveSettings(weightObj);
        }
    }

    $scope.weightSensorsCalibrateHandler = null;
    $scope.calibrateWeight = function(weight_kg)
    {
        if ($scope.selectedSensorId && weight_kg)
        {
            $scope.calibrate_weight       = weight_kg;
            $scope.weightSensorsCalibrate = angular.copy(measurements.weightSensors);
            var weightObj = {'calibrate_weight':weight_kg};
            weightObj.id  = $scope.selectedSensorId;
            //console.log(weightObj);
            settings.saveSettings(weightObj);
            $scope.calibrate();
            $scope.weightSensorsCalibrateHandler = $rootScope.$on('weightSensorsUpdated', $scope.calibrate);
        }
    }

    $scope.calibrate = function()
    {
        $scope.calibrating  = true;

        var changed         = 0;
        var change_perc     = 2;
        var calibrate_obj   = {};
        var sensor_amnt     = measurements.weightSensors.w_v !== 0 ? 1 : 4; // 1 or 4 sensors depending on setup

        var settings_array  = [];
        for (var i = 0; i < settings.settings_array.length; i++) 
        {
            var obj = settings.settings_array[i];
            if ($scope.selectedSensorId && obj.number == $scope.selectedSensorId)
                settings_array.push(obj);
        }
        var settings_obj = $scope.weightSensorsCalibrate;

        console.info(settings_obj);

        for(var w in measurements.weightSensors)
        {
            if (typeof settings_obj[w] != 'undefined' && w != 'id')
            {
                var oldVal = parseFloat(settings_obj[w]);
                var newVal = parseFloat(measurements.weightSensors[w]);

                if (percDiffOf(newVal, oldVal) > change_perc){
                    changed++;
                    var kg_portion = $scope.calibrate_weight / sensor_amnt;
                    var kg_per_val = kg_portion / (newVal - oldVal);
                    calibrate_obj[w+'_kg_per_val'] = kg_per_val;
                    console.log(newVal, oldVal, calibrate_obj);
                }
            }
        }
        if (changed == sensor_amnt)
        {
            $scope.calibrating   = false;
            $scope.calibrate_msg = "Calibratie geslaagd! Wacht op de volgende meting om te controleren of het gewicht inderdaad het aangegeven gewicht in kg aangeeft.";
            settings_obj.id      = $scope.selectedSensorId;
            settings.saveSettings(settings_obj); // save the offset values first
            calibrate_obj.id     = $scope.selectedSensorId;
            settings.saveSettings(calibrate_obj);
            if (typeof $scope.weightSensorsCalibrateHandler == 'function')
                $scope.weightSensorsCalibrateHandler(); // remove handler
        }
        else
        {
            var msg = changed + " van " + sensor_amnt + " sensoren > "+change_perc+" % veranderd..."
            if ($scope.calibrating == false || $scope.calibrate_msg == null || $scope.calibrate_msg.indexOf(msg) == -1)
            {
                $scope.calibrate_msg = msg;
            }
            else
            {
                $scope.calibrate_msg += ".";
            }
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
        $scope.weightSensorsUpdatedHandler();
    };

});

