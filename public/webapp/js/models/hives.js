/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('hives', ['$http', '$rootScope', 'api', 'settings', function($http, $rootScope, api, settings)
{

	var self = this;

	this.reset = function()
	{
		this.refreshCount 	  = 0;
		this.hives 		  	  = [];
		this.locations 	  	  = [];
		this.frame_width      = 11;
		this.hive_width_start = 30;
		this.frame_width_mobile 	 = 3;
		this.hive_width_start_mobile = 10;
	}
	
	self.reset();
	$rootScope.$on('reset', self.reset);

	// Hives
	this.loadRemoteHives = function()
	{
		api.getApiRequest('hives', 'hives');
	};

	this.getHiveById = function(id)
	{
		for (var i = 0; i < self.hives.length; i++) 
		{
			var hive = self.hives[i];
			if (hive.id == id)
				return hive;
		}
	}

	this.getHiveIndex = function(hiveId)
	{
		for (var i = 0; i < self.hives.length; i++) 
		{
			var hive = self.hives[i];
			if (hive.id == hiveId)
				return i;
		}
	}

	// NB: Watch out with undefined variables called 'location' because they affect the url location!!
	this.getHiveLocationById = function(id)
	{
		for (var i = 0; i < self.locations.length; i++) 
		{
			var loc = self.locations[i];
			if (loc.id == id)
				return loc;
		}
	}

	this.hivesHandler = function(e, data)
	{
		// get the result
		var hives = data.hives;
		self.hives = [];
		for (var i = 0; i < hives.length; i++) 
		{
			var h = self.addHiveCalculations(hives[i]);
			self.hives.push(h);
		}

		self.refresh();
	};

	this.hivesError = function(e, error)
	{
		console.log('hives error '+error.message+' status: '+error.status);
	};

	$rootScope.$on('hivesLoaded', self.hivesHandler);
	$rootScope.$on('hivesError', self.hivesError);


	// Locations
	this.loadRemoteLocations = function()
	{
		api.getApiRequest('locations', 'locations');
	};

	this.calculateHiveWidth = function(hive)
	{
		if (hive.frames != undefined && hive.frames > 0)
		{
			if ($rootScope.mobile)
			{
				hive.width = self.hive_width_start_mobile + (self.frame_width_mobile * hive.frames);
			}
			else
			{
				hive.width = self.hive_width_start + (self.frame_width * hive.frames);
			}
			//console.log(hive.name, hive.width);
		}

		return hive;
	}

	this.addHiveCalculations = function(hive)
	{
		hive.brood_layers = 0;
		hive.honey_layers = 0;
		hive.frames 	  = hive.layers[0].frames != undefined ? hive.layers[0].frames.length : 10;
		hive 		  	  = self.calculateHiveWidth(hive);
		for (var i = 0; i < hive.layers.length; i++) 
		{
			l = hive.layers[i];
			hive.brood_layers += l.type == 'brood' ? 1 : 0;
			hive.honey_layers += l.type == 'honey' ? 1 : 0;
		}
		// Queen
		if (hive.queen == null)
		{
			hive.queen = {'created_at':null, 'name':'', 'age':'', 'color':''};
		}
		else
		{
			hive.queen.created_at = hive.queen.created_at.substr(0,10); // YYYY-MM-DD
			hive.queen.clipped 	  = parseInt(hive.queen.clipped);
			hive.queen.fertilized = parseInt(hive.queen.fertilized);
		}
		return hive;
	}

	this.locationsHandler = function(e, data)
	{
		// get the result
		var result = data.locations;
		self.locations = result;
		
		if (self.locations.length > 0)
			self.hives = [];

		for (var i = 0; i < self.locations.length; i++) 
		{
			loc = self.locations[i];
			for (var j = 0; j < loc.hives.length; j++) 
			{
				hive = loc.hives[j];
				self.hives.push(self.addHiveCalculations(hive));
			}
		}
		
		//console.table(self.hives);
		self.refresh();
	};

	this.locationsError = function(e, error)
	{
		console.log('locations error '+error.message+' status: '+error.status);
	};

	$rootScope.$on('locationsLoaded', self.locationsHandler);
	$rootScope.$on('locationsError', self.locationsError);



	this.refresh = function()
	{
		//update refresh count
		self.refreshCount++;

		// announce the update
		$rootScope.$broadcast('hivesUpdated');
	};

}]);