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
	}

	self.reset();
	$rootScope.$on('reset', self.reset);


	this.saveSettings = function(settings)
	{
		if (typeof(settings) == 'undefined')
			settings = self.settings;
		// post the settings to the server
		api.postApiRequest('saveSettings', 'settings', settings);
	};

	this.saveHives = function()
	{
		// post the settings to the server
		api.postApiRequest('saveHives', 'hives', self.hives);
	};


	this.fetchSettings = function()
	{	
		console.log('start loading the settings via API');
		// start loading the settings
		api.getApiRequest('settings', 'settings');
	};

	this.fetchHives = function()
	{	
		console.log('start loading hives via API');
		// start loading the settings
		api.getApiRequest('hives', 'hives');
	};


	this.handleSettings = function(e, data)//, status)
	{
		self.fetchedSettings = true;
		self.settings = convertSettingJsonToObject(data);
		//console.log(self.settings);
	};

	this.handleHives = function(e, data)//, status)
	{
		self.hives = data;
	};


	this.settingsError = function(e, error)
	{
		self.fetchedSettings = false;
	};



	// listen to the setting changes
	$rootScope.$on('saveSettingsLoaded', self.handleSettings);
	$rootScope.$on('saveHivesLoaded', self.handleHives);
	$rootScope.$on('settingsLoaded', self.handleSettings);
	$rootScope.$on('settingsError', self.settingsError);


}]);