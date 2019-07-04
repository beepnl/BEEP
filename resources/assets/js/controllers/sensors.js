/*
 * Kweecker iPad app
 * Author: Neat projects <pim@expertees.nl>
 *
 * Measurements controller for chart measurements 
 */
app.controller('SensorsCtrl', function($scope, $rootScope, $timeout, $interval, $location, measurements, hives, api, settings) 
{
    $rootScope.title    = $rootScope.lang.sensors;
    
    $scope.sensors      = [];
    $scope.hives        = [];
    $scope.sensortypes  = null;
    $scope.selectedSensor = null;
    $scope.selectedSensorId = null;
    $scope.measurementData = null;
    $scope.success_msg  = null;
    $scope.error_msg    = null;
    $scope.sensorTimer  = null;

    // handle loading of all the settings
    $scope.init = function()
    {
        if ($rootScope.pageSlug == 'sensors')
        {
            $scope.updateSensors();
        }
    };

    $scope.selectSensorHive = function(sensorIndex, hiveId)
    {
        var s = measurements.getSensorOwnedByIndex(sensorIndex);
        if (s != null)
        {
            s.selected_hive_id = {id:hiveId};
            s.hive_id = hiveId;
            s.hive    = hives.getHiveOwnedById(hiveId);
            //console.log('selectSensorHive', sensorIndex, hiveId, s.hive.name);
        }
    }
    

    $scope.selectSensorType = function(sensorIndex, type)
    {
        var s = measurements.getSensorOwnedByIndex(sensorIndex);
        if (s != null)
        {
            s.selected_type = {name:type};
            s.type = type;
            console.log('selectSensorType', sensorIndex, type);
        }
    }

    $scope.addSensor = function()
    {
        var key = randomString(16);
        $scope.sensors.push({'name':'Sensor '+($scope.sensors.length+1), 'key':key});
    }

    $scope.removeSensorByIndex = function(i)
    {
        return typeof $scope.sensors[i] != 'undefined' ? $scope.sensors.splice(i,1) : null;
    }

    $scope.showMeasurements = function(sensorIndex)
    {
        var s = measurements.getSensorByIndex(sensorIndex);
        return $location.path('/measurements/'+s.id);
    }

    $scope.deleteSensor = function(sensorIndex)
    {
        var s = measurements.getSensorOwnedByIndex(sensorIndex);

        if (typeof s.id == 'undefined')
            return $scope.removeSensorByIndex(sensorIndex);

        if (typeof s.delete == 'undefined')
            s.delete = true;
        else
            s.delete = s.delete ? false : true;
    }

    $scope.saveSensors = function()
    {
        $scope.success_msg  = null;
        $scope.error_msg    = null;
        api.postApiRequest('saveSensors', 'sensors/store', $scope.sensors);
    }
    $scope.showSuccess = function(type, data)
    {
        $scope.success_msg = $rootScope.lang.succesfully_saved+"!";
    }
    $scope.showError = function(type, error)
    {
        var msg = [];
        if (typeof error.message == 'object' && typeof error.message.errors == 'object' )
        {
            for (type in error.message.errors) 
            {
                var err = error.message.errors[type].join(' ');
                if (err.indexOf('is required') > -1)
                {
                    var typeName = type;
                    switch(type){
                        case 'hive_id':
                            typeName = $rootScope.lang.Hive;
                            break;
                        case 'key':
                            typeName = $rootScope.lang.sensor_key;
                            break;
                        case 'type':
                            typeName = $rootScope.lang.Type;
                            break;
                    }
                    err = $rootScope.lang.the_field + ' \'' + typeName + '\' ' + $rootScope.lang.is_required + '.';
                }
                if (typeof err != 'udefined')
                    msg.push( err );
            }
        }
        if (msg.length > 0)
            $scope.error_msg = msg.join(' ');
        else
            $scope.error_msg = error.message;
    }
    $scope.saveSensorsSuccessHandler = $rootScope.$on('saveSensorsLoaded', $scope.showSuccess);
    $scope.saveSensorsErrorHandler   = $rootScope.$on('saveSensorsError', $scope.showError);

    $scope.updateSensors = function()
    {
        $scope.sensortypes  = settings.sensortypes;
        $scope.sensors      = measurements.sensors_owned;

        if ($scope.sensors.length == 0 && $scope.sensorTimer == null)
        {
            $scope.sensorTimer = $timeout( function()
                {
                    measurements.loadRemoteSensors();
                }
            , 500);
            return;
        }

        $scope.hives = hives.hives_owned;
        
        for (var i = $scope.sensors.length - 1; i >= 0; i--) 
        {
            var s = $scope.sensors[i];
            var h = hives.getHiveById(s.hive_id);
            if (h != null)
            {
                s.selected_hive_id = {id:h.id};
                s.hive = h;
            }
            s.selected_type = s.type != null ? {name:s.type} : ''; 
        }
        $scope.selectedSensorId  = measurements.sensorId;
        $scope.selectedSensor    = measurements.getSensorById($scope.selectedSensorId);
    }
    $scope.sensorHandler = $rootScope.$on('sensorsUpdated', $scope.updateSensors);
    $scope.hivesHandler  = $rootScope.$on('hivesUpdated', $scope.updateSensors);

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
        $scope.saveSensorsSuccessHandler();
        $scope.saveSensorsErrorHandler();
        $scope.sensorHandler();
        $scope.hivesHandler();
        $scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

});