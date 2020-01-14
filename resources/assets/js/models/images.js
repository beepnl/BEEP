/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('images', ['$http', '$rootScope', 'api', 'hives', function($http, $rootScope, api, hives)
{

	var self = this;

	this.reset = function()
	{
		this.refreshCount 	  = 0;
		this.selectedImage	  = {};
		this.images		  	  = [];
	}

	this.getImageByThumbUrl = function(thumbUrl)
	{
		for (var i = 0; i < self.images.length; i++) 
		{
			var image = self.images[i];
			if (image.thumb == thumbUrl)
				return image;
		}
		return null;
	}

	this.getHiveByImageId = function(id)
	{
		for (var i = 0; i < self.images.length; i++) 
		{
			var image = self.images[i];
			if (image.id == id)
			{
				var hive = hive.getHiveById(image.hive_id);
				return hive;
			}
		}
		return null;
	}

	
	// Load images
	this.loadRemoteImages = function()
	{
		api.getApiRequest('images', 'images');
	};

	
	this.imagesHandler = function(e, data)
	{
		// get the result
		var result = data;
		
		if (typeof result != 'undefined' && result != null && result.length > 0)
			self.images = result;

		
		// for (var i = 0; i < self.images.length; i++) 
		// {
		// 	var image = self.images[i];

			
		// }
		self.refresh();
	};

	this.imagesError = function(e, error)
	{
		console.log('images error '+error.message+' status: '+error.status);
	};

	$rootScope.$on('imagesLoaded', self.imagesHandler);
	$rootScope.$on('imagesError', self.imagesError);


	this.refresh = function()
	{
		//update refresh count
		self.refreshCount++;

		// announce the update
		$rootScope.$broadcast('imagesUpdated');
	};


	self.reset();
	$rootScope.$on('reset', self.reset);
	self.loadRemoteImages();
	

}]);