/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Meaurements model
 */
app.service('images', ['$http', '$rootScope', 'api', function($http, $rootScope, api)
{

	var self = this;

	this.reset = function()
	{
		this.refreshCount 	  = 0;
		this.activeImage	  = null;
		this.images		  	  = [];
	}

	this.getImageByThumbUrl = function(thumbUrl)
	{
		for (var i = 0; i < self.images.length; i++) 
		{
			var image = self.images[i];
			if (image.thumb_url == thumbUrl)
				return image;
		}
		return null;
	}

	this.getImageByImageUrl = function(imageUrl)
	{
		for (var i = 0; i < self.images.length; i++) 
		{
			var image = self.images[i];
			if (image.image_url == imageUrl)
				return image;
		}
		return null;
	}

	this.setActiveImage = function(image)
	{
		self.activeImage 	   = image;
		$rootScope.activeImage = image;
	}

	this.setActiveImageByUrl = function(imageUrl) // can be thumb. image, or blob
	{
		console.log(typeof imageUrl, imageUrl);

		var image = {'image_url':null, 'thumb_url':null};

		if (typeof imageUrl == 'object') // load local image
		{
			image.image_url = imageUrl.$ngfBlobUrl;
			var d = imageUrl.lastModifiedDate;
			image.date      = d.getFullYear()+'-'+d.getMonth()+'-'+d.getDate()+' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();
		}
		else if (typeof imageUrl == 'string' && imageUrl.indexOf('/images/') > -1)
		{
			image = self.getImageByImageUrl(imageUrl);
		}
		else
		{
			image = self.getImageByThumbUrl(imageUrl);
		}

		self.setActiveImage(image);
	}


	this.deleteImageByUrl = function(image) // can be thumb. image, or blob
	{
		var imageUrl = image;

		if (typeof imageUrl == 'object') // load local image
		{
			imageUrl = imageUrl.$ngfBlobUrl;
		}
		api.deleteApiRequest('imageDelete', 'images', {'image_url':imageUrl});
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

	$rootScope.$on('imageDeleteLoaded', self.loadRemoteImages);
	$rootScope.$on('imagesLoaded', self.imagesHandler);
	$rootScope.$on('imagesError', self.imagesError);


	this.refresh = function()
	{
		// 
		self.setActiveImage(null);

		//update refresh count
		self.refreshCount++;

		// announce the update
		$rootScope.$broadcast('imagesUpdated');
	};


	self.reset();
	$rootScope.$on('reset', self.reset);
	$rootScope.$on('reloadImages', self.loadRemoteImages);
	self.loadRemoteImages();
	

}]);