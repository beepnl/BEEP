/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('groups', ['$http', '$rootScope', 'api', 'hives', function($http, $rootScope, api, hives)
{

	var self = this;

	this.reset = function()
	{
		this.refreshCount 	  = 0;
		this.groups		  	  = [];
		this.open_group_ids	  = [];
	}

	this.toggle_open_group = function(id)
	{
		var loc = self.getGroupById(id);
		if (loc)
		{
			loc.open = !loc.open;

			if (loc.open && self.open_group_ids.indexOf(loc.id) == -1)
			{
				self.open_group_ids.push(loc.id);
				//console.log('open loc', loc.name, self.open_group_ids);
			}
			else if (self.open_group_ids.indexOf(loc.id) > -1)
			{
				var index = self.open_group_ids.indexOf(loc.id)
				self.open_group_ids.splice(index, 1);
				//console.log('close loc', loc.name, self.open_group_ids);
			}
			api.setLocalStoreValue('open_group_ids', self.open_group_ids.join(','));
		}
		self.refresh();
	}

	this.getGroupById = function(id)
	{
		for (var i = 0; i < self.groups.length; i++) 
		{
			var hive = self.groups[i];
			if (hive.id == id)
				return hive;
		}
		return null;
	}

	this.getGroupIndex = function(hiveId)
	{
		for (var i = 0; i < self.groups.length; i++) 
		{
			var hive = self.groups[i];
			if (hive.id == hiveId)
				return i;
		}
		return null;
	}

	this.getGroupNameById = function(id)
	{
		var hive = self.getGroupById(id);
		return hive != null ? hive.name : null;
	}

	// Locations
	this.loadRemoteGroups = function()
	{
		api.getApiRequest('groups', 'groups');
	};

	

	this.groupsHandler = function(e, data)
	{
		// get the result
		var result = data;
		
		if (typeof result.message != 'undefined')
			return $rootScope.$broadcast('groupsMessage', result);

		if (result != null && result.length > 0)
			self.groups = result;
		
		var group_ids = [];
		var open_group_ids = api.getLocalStoreValue('open_group_ids');

		if (open_group_ids != null)
		{
			group_ids = open_group_ids.split(',');
			for (var i = group_ids.length - 1; i >= 0; i--) {
				group_ids[i] = parseInt(group_ids[i]);
			}
		}

		for (var i = 0; i < self.groups.length; i++) 
		{
			var group = self.groups[i];

			if (self.groups.length == 1)
			{
				group.open = true;
				self.open_group_ids.push(group.id);
			}
			else if (group_ids.indexOf(group.id) > -1)
			{
				group.open = true;
				self.open_group_ids.push(group.id);
			}
			else
			{
				group.open = false;
			}
		}
		self.addHivesToGroups();
		self.refresh();
	};

	this.addHivesToGroups = function(e, data)
	{
		for (var i = 0; i < self.groups.length; i++) 
		{
			var group = self.groups[i];

			if (typeof group.hives != 'undefined' && group.hives.length > 0)
			{
				group.hives_selected = [];
				group.hives_editable = [];

				for (var j = group.hives.length - 1; j >= 0; j--)
				{
					var hive = group.hives[j];
					if (hive != null && typeof hive.id != 'undefined')
					{
						if (hive.editable)
							group.hives_editable.push(hive.id);

						group.hives_selected.push(hive.id);
						hive = hives.addHiveCalculations(hive);
					}
				}
			}
		}
		//console.log(self.groups);
	}

	this.groupsError = function(e, error)
	{
		console.log('groups error '+error.message+' status: '+error.status);
	};

	$rootScope.$on('groupsLoaded', self.groupsHandler);
	$rootScope.$on('saveGroupLoaded', self.groupsHandler);
	$rootScope.$on('deleteGroupLoaded', self.groupsHandler);
	$rootScope.$on('groupsError', self.groupsError);



	this.refresh = function()
	{
		//update refresh count
		self.refreshCount++;

		// announce the update
		$rootScope.$broadcast('groupsUpdated');
	};


	self.reset();
	$rootScope.$on('reset', self.reset);
	self.loadRemoteGroups();
	

}]);