/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Settings controller
 */
app.controller('SettingsCtrl', function($scope, $rootScope, $window, $timeout, $location, $filter, $interval, api, $routeParams, ngDialog, settings, measurements) 
{

    // settings
    $scope.hives                 = settings.hives;
    $scope.sensors               = settings.sensors;
    $scope.calibrate_weight      = settings.settings.calibrate_weight;
    $scope.calibrate_msg         = null;

    // settings (readonly)
    $scope.firmware              = settings.firmware_version;
    $scope.updated_at            = settings.updated_at;
    $scope.userType              = settings.type;

    // handlers
    $scope.isChecking            = false;
    $scope.isLoading             = false;
    $scope.isAutomatic           = ($scope.connection_type == 'automatic') ? true : false;
    //$scope.changed               = false;

    $scope.watchTimeout = null;

    $scope.init = function()
    {
        $scope.refreshIscroll();
    };

    $scope.offsetWeight = function()
    {
        // get weight from sensors, save offsets
        if (measurements.weightSensors)
        {
            $scope.isLoading = true;
            settings.saveSettings(measurements.weightSensors);
        }
    }

    $scope.calibrateWeight = function()
    {
        // get weight from sensors, save offsets
        
        if (measurements.weightSensors)
        {
            $scope.isLoading = true;
            var weightObj = {calibrate_weight:$scope.calibrate_weight};
            settings.saveSettings(weightObj);
            $scope.waitForWeightChange();
        }
    }

    $scope.calibrateLoadTimer = null
    $scope.waitForWeightChange = function()
    {
        if (angular.isDefined($scope.calibrateLoadTimer))
            $interval.cancel($scope.calibrateLoadTimer);

        // Start loading interval
        $scope.calibrateLoadTimer = $interval(function()
        {
            $scope.calibrate();
        }, 5000);

        $scope.calibrate();
    }

    $scope.calibrate = function()
    {
        $scope.isLoading = true;
        var changed         = 0;
        var change_perc     = 2;
        var calibrate_obj   = {};
        var sensor_amnt     = Object.keys(measurements.weightSensors).length;
        for(var w in measurements.weightSensors)
        {
            if (typeof(settings.settings[w]) != 'undefined')
            {
                var oldVal = settings.settings[w];
                var newVal = measurements.weightSensors[w];
                if (percDiffOf(newVal, oldVal) > change_perc){
                    changed++;
                    var kg_portion = $scope.calibrate_weight / sensor_amnt;
                    var kg_per_val = kg_portion / Math.abs(newVal - oldVal);
                    calibrate_obj[w+'_kg_per_val'] = kg_per_val;
                }
            }
        }
        if (changed == sensor_amnt)
        {
            $scope.calibrate_msg = "Calibratie geslaagd!";
            settings.saveSettings(calibrate_obj);
        }
        else
        {
            $scope.calibrate_msg = changed + " van " + sensor_amnt + " sensoren > "+change_perc+"% veranderd...";
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
        $scope.watchTimeout = null;

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
        settings.setStatus('offline'); // Why taking the status of the app offline? Only because the settings can not be saved on the server?
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

    // watch the manual switch
    // $scope.$watch('isAutomatic', function(newVal, oldVal)
    // {
    //     if(newVal != oldVal)
    //     {
    //         // check the connection type
    //         $scope.connection_type = ($scope.isAutomatic) ? 'automatic' : 'manual';

    //         // save the changes
    //         $scope.saveSettings();

    //         // refresh iscroll
    //         $scope.refreshIscroll();
    //     }
    // });


    $scope.init();


    // remove the listeners
    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });


    // remove listeners
    $scope.removeListeners = function()
    {
        $scope.settingsSavedHandler();
        $scope.settingsErrorHandler();
    };

});

