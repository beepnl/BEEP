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
		this.sensors = { // translation from JSON sensor quantity to : internal quantity id
	        "temperature"       : {id:'temperature', value:'-', unit: '°C'},
	        "humidity"          : {id:'humidity', value:'-', unit: '%'},
	        "sunlight"			: {id:'sunlight', value:'-', unit: 'lux'},
	        "rainfall"          : {id:'rainfall', value:'-', unit: 'mm'},
	        "air_pressure"      : {id:'air_pressure', value:'-', unit: 'hPa'},
	        "weight_kg"      	: {id:'weight_kg', value:'-', unit: 'Kg'},
	        "weight_combined_kg": {id:'weight_combined_kg', value:'-', unit: 'Kg'},
	        "sound_fanning_4days":{id:'sound_fanning_4days', value:'-', unit: ''},
	        "sound_fanning_6days":{id:'sound_fanning_6days', value:'-', unit: ''},
	        "sound_fanning_9days":{id:'sound_fanning_9days', value:'-', unit: ''},
	        "sound_flying_adult": {id:'sound_flying_adult', value:'-', unit: ''},
	        "sound_total"     	: {id:'sound_total', value:'-', unit: ''},
	        "bee_count_in"     	: {id:'bee_count_in', value:'-', unit: '#'},
	        "bee_count_out"     : {id:'bee_count_out', value:'-', unit: '#'},
	    };

	    this.sensor_measurements = {};
	    this.measurementDate 	 = null;

	    /*
	    Volle zon: 100 000 - 130 000 lux (100 - 130 klx)
	    Half bewolkt: 10 000 - 20 000 lux (10 - 20 klx)
	    Bewolkt: 1000 lux (1 klx)
	    Weinig zon: 500 lux
	    Bijna donker: 100 lux
	    Schemer: 10 lux
	    Donker: 1 lux
	    */
	    this.lightAmounts = ['sun_lux_dark','sun_lux_dusk','sun_lux_low','sun_lux_cloudy','sun_lux_half','sun_lux_sunny']; // power of the amount, 0=index:0, 10=index:1, 100=index:2, etc. 
		this.weightSensors= {'w_fl':0, 'w_fr':0, 'w_bl':0, 'w_br':0};

		//refreshed?
		this.refreshCount = 0;
	}

	self.reset();
	$rootScope.$on('reset', self.reset);

	/*
	this.calculateWeight = function(data)
	{
		var totalWeight = 0;
		for(var s in data)
		{
			var sensor = data[s];
			if (typeof(self.weightSensors[sensor.name]) != 'undefined')
			{
				if (typeof(settings.settings) != 'undefined' && typeof(settings.settings[sensor.name]) != 'undefined') // offset available
				{
					var factor = (typeof(settings.settings[sensor.name+'_kg_per_val']) != 'undefined') ? parseFloat(settings.settings[sensor.name+'_kg_per_val']) : 1;
					var weight = ( parseFloat(sensor.value) - parseFloat(settings.settings[sensor.name]) ) * factor;
					totalWeight += weight;
				}
				else
				{
					totalWeight += parseFloat(sensor.value);
				}
				self.weightSensors[sensor.name] = parseFloat(sensor.value);
			}
		}
		self.sensors['weight_kg'].value = totalWeight;
		self.sensors['weight_kg'].name = $rootScope.lang['weight'];
	}
	*/
	
	this.updateWeightSensors = function(data)
	{
		for(var s in data)
		{
			var sensor = data[s];
			if (typeof(self.weightSensors[sensor.name]) != 'undefined')
			{
				self.weightSensors[sensor.name] = parseFloat(sensor.value);
			}
		}
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

	this.loadRemoteMeasurements = function()
	{
		if (api.token == null)
		{
			if (angular.isDefined(self.measurementLoadTimer))
        		$interval.cancel(self.measurementLoadTimer);
			return false;
		}

		// start loading the measurements
		api.getApiRequest('measurements', 'sensors');
	};


	this.handleMeasurements = function(e, data)
	{
		// get the result
		var result = data.data;
		
		if (result.length > 0)
			$rootScope.hasSensors = true;

		// loop trough the results
		for(var i in result)
		{
			var curr     = result[i];
			
			var id 	 	 = curr.name;
			var value    = (typeof curr.value == 'number') ? (Math.round(curr.value*10)/10) : curr.value;
			var date     = moment(curr.time);

			// check if we have a measurement
			if(typeof self.sensors[id] != 'undefined')
			{	
				self.sensors[id].name  = $rootScope.lang[id]; 
				self.sensors[id].value = value;
				self.sensors[id].date  = date;
			}

			if (self.measurementDate == null || date > self.measurementDate)
			{
				self.measurementDate = date;
			}

		}

		// make sure raw weight sensor values are updated (for offset settings)
		self.updateWeightSensors(result);

		self.refresh();
	};

	this.measurementsError = function(e, error)
	{
		console.log('measurements error '+error.message+' status: '+error.status);
		if (error.status == 404)
		{
			$rootScope.hasSensors = false;
			self.stopLoadingMeasurements();
		}
	};

	$rootScope.$on('measurementsLoaded', self.handleMeasurements);
	$rootScope.$on('measurementsError', self.measurementsError);




	// Data from one sensor
	this.loadRemoteSensorMeasurements = function(sensorName)
	{
		// start loading the measurements
		api.getApiRequest('sensorMeasurements', 'sensors/'+sensorName);
	};

	this.handleSensorMeasurements = function(e, data)
	{
		// get the result
		var result = data.data;
		
		self.sensor_measurements = result;
		console.log(self.sensor_measurements);

		self.refresh();
	};

	$rootScope.$on('sensorMeasurementsLoaded', self.handleSensorMeasurements);
	$rootScope.$on('sensorMeasurementsError', self.measurementsError);



	this.refresh = function()
	{
		//update refresh count
		this.refreshCount++;

		// announce the update
		$rootScope.$broadcast('measurementsUpdated');
	};


	this.measurementLoadTimer = null;
	this.initMeasurements = function()
    {
        if (angular.isDefined(self.measurementLoadTimer))
        	$interval.cancel(self.measurementLoadTimer);

        // Start loading interval
        self.measurementLoadTimer = $interval(function()
        {
            self.loadRemoteMeasurements();
        }, CONNECTION_FREQ_REMOTE);

        self.loadRemoteMeasurements();
    };

    this.stopLoadingMeasurements = function()
    {
        if (angular.isDefined(self.measurementLoadTimer))
        {
        	$interval.cancel(self.measurementLoadTimer);
        }
    };

    // Check if measurements are available
	self.loadRemoteMeasurements();

}]);