/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

app.service('settings', ['$http', '$rootScope', 'api', function($http, $rootScope, api)
{

	var self = this;

	this.reset = function()
	{
		// user type (readonly)
		this.type 				= null; // admin, user
		this.fetchedSettings   	= false;
		this.updated_at			= null;
		this.created_at			= null;
		this.settings 			= {};
		this.hives 				= {};

		this.beeraces  	  		= [];
		this.hivetypes 	  		= [];
		this.sensortypes 	  	= [];
		this.taxonomy			= [];
		this.settings_array		= [];
	}

	self.reset();
	$rootScope.$on('reset', self.reset);


		// Inspection Lists
	this.loadTaxonomy = function()
	{
		api.getApiRequest('taxonomyLists', 'taxonomy/lists');
		//api.getApiRequest('taxonomyItems', 'taxonomy/taxonomy', 'order=1&flat=0');
	};

	this.taxonomyHandler = function(e, data)
	{
		if (typeof data.taxonomy != 'undefined')
		{
			self.taxonomy = data.taxonomy;
			$rootScope.$broadcast('taxonomyItemsUpdated');
		}
		if (typeof data.beeraces != 'undefined')
			self.beeraces  = data.beeraces;

		if (typeof data.sensortypes != 'undefined')
			self.sensortypes = data.sensortypes;

		if (typeof data.hivetypes != 'undefined')
			self.hivetypes = data.hivetypes;

		if (typeof data.hivetypes != 'undefined' || typeof data.beeraces != 'undefined')
			$rootScope.$broadcast('taxonomyListsUpdated');

	};
	$rootScope.$on('taxonomyListsLoaded', self.taxonomyHandler);
	$rootScope.$on('taxonomyItemsLoaded', self.taxonomyHandler);

	this.saveSettings = function(settings)
	{
		if (typeof settings != 'undefined')
		{
			api.postApiRequest('saveSettings', 'settings', settings);
			console.log('settings.saveSettings', settings);
		}
	};

	this.fetchSettings = function()
	{	
		console.log('start loading the settings via API');
		// start loading the settings
		api.getApiRequest('settings', 'settings');
		self.loadTaxonomy();
	};

	this.handleSettings = function(e, data)//, status)
	{
		self.fetchedSettings = true;
		self.settings_array  = data;
		self.settings 		 = convertSettingJsonToObject(self.settings_array);
		//console.log(self.settings);
	};

	this.settingsError = function(e, error)
	{
		self.fetchedSettings = false;
	};

	// listen to the setting changes
	$rootScope.$on('saveSettingsLoaded', self.handleSettings);
	$rootScope.$on('settingsLoaded', self.handleSettings);
	$rootScope.$on('settingsError', self.settingsError);


}]);