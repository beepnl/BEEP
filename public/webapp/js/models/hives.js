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
		this.hives_inspected  = [];
		this.hives 		  	  = [];
		this.locations 	  	  = [];
		this.frame_width      = 11;
		this.hive_width_start = 30;
		this.frame_width_mobile 	 = 3;
		this.hive_width_start_mobile = 10;
		this.open_loc_ids 	  = [];
		this.sensors 	  	  = [];
	}

	this.toggle_open_loc = function(id)
	{
		var loc = self.getHiveLocationById(id);
		if (loc)
		{
			loc.open = !loc.open;

			if (loc.open && self.open_loc_ids.indexOf(loc.id) == -1)
			{
				self.open_loc_ids.push(loc.id);
				//console.log('open loc', loc.name, self.open_loc_ids);
			}
			else if (self.open_loc_ids.indexOf(loc.id) > -1)
			{
				var index = self.open_loc_ids.indexOf(loc.id)
				self.open_loc_ids.splice(index, 1);
				//console.log('close loc', loc.name, self.open_loc_ids);
			}
			api.setLocalStoreValue('open_loc_ids', self.open_loc_ids.join(','));
		}
		self.refresh();
	}

	this.getHiveById = function(id)
	{
		for (var i = 0; i < self.hives.length; i++) 
		{
			var hive = self.hives[i];
			if (hive.id == id)
				return hive;
		}
		return null;
	}

	this.getHiveIndex = function(hiveId)
	{
		for (var i = 0; i < self.hives.length; i++) 
		{
			var hive = self.hives[i];
			if (hive.id == hiveId)
				return i;
		}
		return null;
	}

	this.getHiveNameById = function(id)
	{
		var hive = self.getHiveById(id);
		return hive != null ? hive.name : null;
	}

	this.getHiveInspectedIndex = function(hiveId)
	{
		for (var i = 0; i < self.hives_inspected.length; i++) 
		{
			var hive = self.hives_inspected[i];
			if (hive.id == hiveId)
				return i;
		}
		return null;
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
		return null;
	}

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

		if (hive.layers.length > 0)
		{
			hive.frames 	  = hive.layers.length > 0 && hive.layers[0].framecount != undefined ? hive.layers[0].framecount : 10;
			hive 		  	  = self.calculateHiveWidth(hive);
			for (var i = 0; i < hive.layers.length; i++) 
			{
				l = hive.layers[i];
				if (typeof l.frames == 'undefined')
					l.frames = new Array(hive.frames);
				
				hive.brood_layers += l.type == 'brood' ? 1 : 0;
				hive.honey_layers += l.type == 'honey' ? 1 : 0;
			}
		}
		
		// Queen
		if (hive.queen == null)
		{
			hive.queen = {'created_at':null, 'name':'', 'age':'', 'color':''};
		}
		else
		{
			hive.queen.created_at = hive.queen.created_at != null ? hive.queen.created_at.substr(0,10) : null; // YYYY-MM-DD
			hive.queen.clipped 	  = parseInt(hive.queen.clipped);
			hive.queen.fertilized = parseInt(hive.queen.fertilized);
		}
		return hive;
	}

	this.locationsHandler = function(e, data)
	{
		// get the result
		var result = data.locations;
		//console.log(result);
		self.locations = result;
		
		if (self.locations.length > 0)
			self.hives = [];

		var loc_ids = [];
		var open_loc_ids = api.getLocalStoreValue('open_loc_ids');

		if (open_loc_ids != null)
		{
			loc_ids = open_loc_ids.split(',');
			for (var i = loc_ids.length - 1; i >= 0; i--) {
				loc_ids[i] = parseInt(loc_ids[i]);
			}
		}

		for (var i = 0; i < self.locations.length; i++) 
		{
			loc = self.locations[i];

			if (self.locations.length == 1)
			{
				loc.open = true;
				self.open_loc_ids.push(loc.id);
			}
			else if (loc_ids.indexOf(loc.id) > -1)
			{
				loc.open = true;
				self.open_loc_ids.push(loc.id);
			}
			else
			{
				loc.open = false;
			}

			if (typeof loc.coordinate_lat != 'undefined')
				loc.lat = parseFloat(loc.coordinate_lat);

			if (typeof loc.coordinate_lon != 'undefined')
				loc.lon = parseFloat(loc.coordinate_lon);

			for (var j = 0; j < loc.hives.length; j++) 
			{
				var h = loc.hives[j];
				if (typeof h != 'undefined')
				{
					hive = self.addHiveCalculations(h)
					self.hives.push(hive);
					
					if (hive.inspection_count > 0)
						self.hives_inspected.push(hive);
				}
				for (var k = 0; k < h.sensors.length; k++) 
				{
					var s = h.sensors[k];
					self.sensors.push(s);
				}
			}
			if (typeof loc.sensors != 'undefined')
			{
				for (var k = 0; k < loc.sensors.length; k++) 
				{
					var s = loc.sensors[k];
					self.sensors.push(s);
				}
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


	self.reset();
	$rootScope.$on('reset', self.reset);
	self.loadRemoteLocations();
	

}]);