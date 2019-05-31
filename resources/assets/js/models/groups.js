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
		this.invitations	  = [];
		this.hives		  	  = [];
		this.open_group_ids	  = [];
	}

	this.toggle_open_group = function(id)
	{
		var group = self.getGroupById(id);
		if (group)
		{
			group.open = !group.open;

			if (group.open && self.open_group_ids.indexOf(group.id) == -1)
			{
				self.open_group_ids.push(group.id);
			}
			else if (self.open_group_ids.indexOf(group.id) > -1)
			{
				var index = self.open_group_ids.indexOf(group.id)
				self.open_group_ids.splice(index, 1);
			}
			self.open_group_ids = self.open_group_ids.getUnique();
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

	// Load groups (including hives, to not interfere with your own hives in hives.hives)
	this.loadRemoteGroups = function()
	{
		api.getApiRequest('groups', 'groups');
	};

	
	this.groupsHandler = function(e, data)
	{
		// get the result
		var result = data;
		
		if (result != null && typeof result.groups != 'undefined')
			self.groups = result.groups;

		if (result != null && typeof result.invitations != 'undefined')
			self.invitations = result.invitations;

		var group_ids = [];
		var open_group_ids = api.getLocalStoreValue('open_group_ids');
		//console.log('open_group_ids', open_group_ids);

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
				if (self.open_group_ids.indexOf(group.id) == -1)
					self.open_group_ids.push(group.id);
			}
			else if (group_ids.indexOf(group.id) > -1)
			{
				group.open = true;
				if (self.open_group_ids.indexOf(group.id) == -1)
					self.open_group_ids.push(group.id);
			}
			else
			{
				group.open = false;
			}
		}
		self.processGroupHives();
		self.refresh();
	};

	// Put all group-hives in hives array and add id's to selected and editable arrays
	this.processGroupHives = function(e, data)
	{
		self.hives = [];

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
						hive.group_name = group.name;

						self.hives.push(hive);
					}
				}
			}
		}
		//console.log(self.hives);
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