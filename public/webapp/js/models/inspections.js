/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('inspections', ['$http', '$rootScope', 'api', function($http, $rootScope, api)
{

	var self = this;

	this.reset = function()
	{
		this.refreshCount = 0;
		this.inspections  = [];
		this.conditions   = [];
		this.actions   	  = [];
		this.beeraces  	  = [];
		this.hivetypes 	  = [];
		this.DATE_FORMAT_API = 'YYYY-MM-DD';
	}

	self.reset();
	$rootScope.$on('reset', self.reset);

	var STD_VALUES = {
		'default': null,
		'boolean': -1,
		'date': "",
		'number': null,
		'text': "",
		'select': "",
		'score': -1,
	}
	
	// Inspection Lists
	this.loadinspectionLists = function()
	{
		api.getApiRequest('inspectionLists', 'inspections/lists');
	};

	this.inspectionListsHandler = function(e, data)
	{
		// console.log('conditionsUpdated')
		//console.table(data);
		for (var i = 0; i < data.categories.length; i++) 
		{
			var cat = data.categories[i];
			switch (cat.name)
			{
				case "action":
					self.actions 	= self.addStdValues(cat.children);
					break;
				case "condition":
					self.conditions = self.addStdValues(cat.children);
					break;
			}
		}

		self.beeraces  = data.beeraces;
		self.hivetypes = data.hivetypes;

		$rootScope.$broadcast('inspectionListsUpdated');
	};


	this.addStdValues = function(data)
	{
		for (var i = data.length - 1; i >= 0; i--) 
		{
			c = data[i];
			if (typeof c.children == 'object' && c.children.length > 0)
			{
				for (var j = c.children.length - 1; j >= 0; j--) 
				{
					v = c.children[j];
					if (STD_VALUES[v.type] != undefined)
						v.value = STD_VALUES[v.type];
				}
			}
			else
			{
				data.splice(i, 1); // remove item
			}
		}
		//console.table(data);
		return data;
	}

	this.createSaveArray = function(data, inspection_date, hive_id)
	{
		saveArray = [];
		for (var i = data.length - 1; i >= 0; i--) 
		{
			var c = data[i];
			if (typeof c.children == 'object' && c.children.length > 0)
			{
				for (var j = c.children.length - 1; j >= 0; j--) 
				{
					var v = c.children[j];
					console.log(v.name, v.type, v.value, 'std val: ',STD_VALUES[v.type]);
					if (STD_VALUES[v.type] !== v.value && v.value !== null && v.value !== "" && v.value !== undefined) // value is NOT set, so remove
					{
						saveArray.push(v);
					}
				}
			}
		}

		if (saveArray.length > 0)
		{
			return {'multiple_items': saveArray, 'date': inspection_date, 'hive_id': hive_id};
		}
		else
		{
			console.log(saveArray);
		}

		return null;
	}

	this.correctMobileDateValue = function(d) // Convert date to YYYY-MM-DD
	{
	    date_start = d; //.substring(0,10);
	    console.log('date to correct: ',d);
	    date_corr =  moment(d, [self.DATE_FORMAT_API, moment.ISO_8601]).format(self.DATE_FORMAT_API);
	    console.log('date corrected : ',date_corr);
	    return date_corr;
	}

	$rootScope.$on('inspectionListsLoaded', self.inspectionListsHandler);

	// Inspections
	this.loadRemoteInspections = function(hive_id)
	{
		api.getApiRequest('inspections', 'inspections/'+hive_id);
	};

	this.inspectionsHandler = function(e, data)
	{
		// get the result
		self.inspections = data;
		self.refresh();
		//console.table(self.inspections);
	};

	this.inspectionsError = function(e, error)
	{
		console.log('inspections error '+error.message+' status: '+error.status);
	};

	$rootScope.$on('inspectionsLoaded', self.inspectionsHandler);
	$rootScope.$on('inspectionsError', self.inspectionsError);



	this.refresh = function()
	{
		//update refresh count
		self.refreshCount++;

		// announce the update
		$rootScope.$broadcast('inspectionsUpdated');
	};

	// Init
	self.loadinspectionLists();

}]);