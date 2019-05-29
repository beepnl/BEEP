/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('measurements', ['$http', '$rootScope', '$interval', 'api', 'settings', function($http, $rootScope, $interval, api, settings)
{

	var self = this;

	this.reset = function()
	{
		this.sensors 	  	  	= [];
		this.lastSensorValues 	= {};
		this.lastSensorDate     = null;
	    this.sensorId 	  	  	= null;
	    this.lightAmounts 		= ['sun_lux_dark','sun_lux_dusk','sun_lux_low','sun_lux_cloudy','sun_lux_half','sun_lux_sunny']; // power of the amount, 0=index:0, 10=index:1, 100=index:2, etc. 
		this.weightSensors		= {'w_fl':0, 'w_fr':0, 'w_bl':0, 'w_br':0, 'w_v':0};

		// sensorMeasurements
		this.interval 	  		= 'day';
		this.timeIndex 	  		= 0;
		this.timeGroup 	  		= 'day';
		this.timeZone 	  		= 'Europe/Amsterdam';
	}

	self.reset();
	$rootScope.$on('reset', self.reset);

	
	this.updateWeightSensors = function(data)
	{
		var updated = false;
		for(var s in data)
		{
			var val = parseFloat(data[s]);
			if (typeof self.weightSensors[s] != 'undefined' && !isNaN(val))
			{
				self.weightSensors[s] = val;
				updated = true;
			}
		}
		if (updated)
			$rootScope.$broadcast('weightSensorsUpdated');
	}

	this.getSensorById = function(id)
	{
		for(var i in this.sensors)
		{
			var sensor = this.sensors[i]
			if (sensor.id == id)
				return sensor;
		}
		return null;
	}

	this.getSensorByIndex = function(i)
	{
		return typeof this.sensors[i] != 'undefined' ? this.sensors[i] : null;
	}

	// Data from one sensor
	this.loadRemoteSensorMeasurements = function(interval, timeIndex, timeGroup, timeZone, sensorId)
	{
		// start loading the measurements
		//api.getApiRequest('sensorMeasurements', 'sensors/measurements');
		if (typeof interval != 'undefined')
			self.interval = interval;
		if (typeof timeIndex != 'undefined')
			self.timeIndex = timeIndex;
		if (typeof timeGroup != 'undefined')
			self.timeGroup = timeGroup;
		if (typeof timeZone != 'undefined')
			self.timeZone = timeZone;
		if (typeof sensorId != 'undefined')
			self.sensorId = sensorId;

		self.startLoadingMeasurements();
	};

	this.sensorMeasurementRequest = function()
	{
		api.getApiRequest('dataRequest', 'sensors/measurements', 'id='+self.sensorId+'&interval='+self.interval+'&index='+self.timeIndex+'&timeGroup='+self.timeGroup+'&timezone='+self.timeZone);
		
		if (self.timeIndex > 0)
			self.stopLoadingMeasurements(); // no need to refresh, because no new values
	}

	this.loadRemoteSensors = function()
	{
		api.getApiRequest('sensors', 'sensors');
	};

	this.handleSensors = function(e, result)
	{
		if (result.length > 0)
		{
			self.sensors = result;
			$rootScope.hasSensors = true;
			$rootScope.$broadcast('sensorsUpdated');
		}
		//console.log(self.sensors);
	};

	this.loadLastSensorValues = function(sensorId = self.sensorId)
	{
		api.getApiRequest('lastSensorValues', 'sensors/lastvalues', 'id='+sensorId);
	};

	this.loadLastWeightSensorValues = function(sensorId = self.sensorId)
	{
		api.getApiRequest('lastSensorValues', 'sensors/lastweight', 'id='+sensorId);
	};

	this.handleLastSensorValues = function(e, result)
	{
		//console.log('measurements handleLastSensorValues', result);
		if (result != null)
		{
			self.lastSensorValues = result;
			self.lastSensorDate   = result.time;
			self.updateWeightSensors(result);
			$rootScope.$broadcast('lastSensorValuesUpdated');
		}
	};
	$rootScope.$on('lastSensorValuesLoaded', self.handleLastSensorValues);

	this.weightCalibration = function(data)
	{
		api.postApiRequest('weightCalibration', 'sensors/calibrateweight', data);
	};

	this.weightOffset = function(data)
	{
		api.postApiRequest('weightOffset', 'sensors/offsetweight', data);
	};

	this.sensorsError = function(e, error)
	{
		console.log('measurements sensorsError '+error.message+' status: '+error.status);
		if (error.status == 404)
		{
			$rootScope.hasSensors = false;
			self.stopLoadingMeasurements();
		}
	};

	$rootScope.$on('sensorsLoaded', self.handleSensors);
	$rootScope.$on('saveSensorsLoaded', self.handleSensors);
	$rootScope.$on('sensorsError', self.sensorsError);



	this.measurementLoadTimer = null;
	this.startLoadingMeasurements = function()
    {
        if (self.sensorId === null)
        	return;

        if (angular.isDefined(self.measurementLoadTimer))
        	$interval.cancel(self.measurementLoadTimer);

        // Start loading interval
        self.measurementLoadTimer = $interval(function()
        {
            self.sensorMeasurementRequest();
        }, CONNECTION_FREQ_REMOTE);

        self.sensorMeasurementRequest();
    };

    this.stopLoadingMeasurements = function()
    {
        if (angular.isDefined(self.measurementLoadTimer))
        {
        	$interval.cancel(self.measurementLoadTimer);
        }
    };

    // Check if measurements are available
	self.loadRemoteSensors();

}]);