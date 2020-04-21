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
    $scope.selectedDevice = null;
    $scope.selectedSensor = null;
    $scope.selectedSensorId = null;
    $scope.measurementData = null;
    $scope.success_msg  = null;
    $scope.error_msg    = null;
    $scope.sensorTimer  = null;
    $scope.editMode     = false;
    $scope.sensormeasurements = [];
    $scope.defs         = [];

    // handle loading of all the settings
    $scope.init = function()
    {
        if ($rootScope.pageSlug == 'sensors')
        {
            $scope.updateDevices();
            $scope.updateSensormeasurements();
        }
    };

    $scope.updateSensormeasurements = function()
    {
        $scope.sensormeasurements = settings.sensormeasurements;
    }
    $scope.updateSensormeasurementsHandler = $rootScope.$on('taxonomyListsUpdated', $scope.updateSensormeasurements);
    

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
        var key = randomString(16).toLowerCase();
        $scope.sensors.push({'name':'Sensor '+($scope.sensors.length+1), 'key':key});
    }

    $scope.removeSensorByIndex = function(i)
    {
        return typeof $scope.sensors[i] != 'undefined' ? $scope.sensors.splice(i,1) : null;
    }

    $scope.showMeasurements = function(sensorIndex)
    {
        var s = measurements.getSensorOwnedByIndex(sensorIndex);
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

    $scope.saveDevices = function()
    {
        $scope.success_msg  = null;
        $scope.error_msg    = null;
        api.postApiRequest('saveDevices', 'devices/multiple', $scope.sensors);
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
    $scope.saveDevicesSuccessHandler = $rootScope.$on('saveDevicesLoaded', $scope.showSuccess);
    $scope.saveDevicesErrorHandler   = $rootScope.$on('saveDevicesError', $scope.showError);

    $scope.updateDevices = function()
    {
        $scope.sensortypes  = settings.sensortypes;
        $scope.sensors      = measurements.sensors_owned;

        if ($scope.sensors.length == 0 && $scope.sensorTimer == null)
        {
            $scope.sensorTimer = $timeout( function()
                {
                    measurements.loadRemoteDevices();
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

        if ($scope.selectedDevice)
            $scope.selectDeviceId($scope.selectedDevice.id);

    }
    $scope.devicesHandler = $rootScope.$on('devicesUpdated', $scope.updateDevices);
    $scope.hivesHandler  = $rootScope.$on('hivesUpdated', $scope.updateDevices);



    // Sensor Definition editing
    
    $scope.selectDevice = function(deviceIndex)
    {
        $scope.selectedDevice = measurements.getSensorOwnedByIndex(deviceIndex);
        $scope.setSelectedDefs();
    }

    $scope.selectDeviceId = function(deviceId)
    {
        $scope.selectedDevice = measurements.getSensorOwnedById(deviceId);
        $scope.setSelectedDefs();    
    }

    $scope.setSelectedDefs = function()
    {
        $scope.defs = [];
        if ($scope.selectedDevice.sensor_definitions.length > 0)
        {
            $scope.defs = $scope.selectedDevice.sensor_definitions;

            for (var i = $scope.defs.length - 1; i >= 0; i--) 
            {
                var d     = $scope.defs[i];
                if (d != null)
                {
                    d.input_measurement  = {id:d.input_measurement_id};
                    d.output_measurement = {id:d.output_measurement_id};
                }
            }
        }
    }
    
    $scope.addSensorDefinition = function()
    {
        $scope.defs.push({'device_id':$scope.selectedDevice.id, 'name':'Sensor '+($scope.defs.length+1), 'inside':null, 'offset':0, 'multiplier':1, 'input_measurement_id':null, 'output_measurement_id':null});
    }

    $scope.removeSensorDefinitionByIndex = function(i)
    {
        return typeof $scope.defs[i] != 'undefined' ? $scope.defs.splice(i,1) : null;
    }

    $scope.deleteSensorDefinition = function(i)
    {
        var s = typeof $scope.defs[i] != 'undefined' ? $scope.defs[i] : null;

        if (typeof s.id == 'undefined')
            return $scope.removeSensorDefinitionByIndex(i);

        if (typeof s.delete == 'undefined')
            s.delete = true;
        else
            s.delete = s.delete ? false : true;
    }

    $scope.selectInputSensorMeasurement = function(i, m_i)
    {
        $scope.defs[i].input_measurement_id = m_i;
        $scope.defs[i].input_measurement    = {id:m_i};
    }

    $scope.selectOutputSensorMeasurement = function(i, m_i)
    {
        $scope.defs[i].output_measurement_id = m_i;
        $scope.defs[i].output_measurement    = {id:m_i};
    }

    $scope.saveSensorDefinition = function(i)
    {
        var sensorDef   = $scope.defs[i];
        var sensorDefId = typeof sensorDef.id != 'undefined' ? '/'+sensorDef.id : '';

        if (sensorDef.delete == 1)
            api.deleteApiRequest('sensorDefinition', 'sensordefinition'+sensorDefId, sensorDef);
        else if (sensorDefId != '')
            api.putApiRequest('sensorDefinition', 'sensordefinition'+sensorDefId, sensorDef);
        else
            api.postApiRequest('sensorDefinition', 'sensordefinition', sensorDef);
    }
    $scope.saveSensorDefinitionHandler = $rootScope.$on('sensorDefinitionLoaded', measurements.loadRemoteDevices);



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
        $scope.updateSensormeasurementsHandler();
        $scope.saveSensorDefinitionHandler();
        $scope.saveDevicesSuccessHandler();
        $scope.saveDevicesErrorHandler();
        $scope.devicesHandler();
        $scope.hivesHandler();
        $scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

});