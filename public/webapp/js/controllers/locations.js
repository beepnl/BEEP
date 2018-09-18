/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('LocationsCtrl', function($scope, $rootScope, $window, $location, $routeParams, $filter, settings, api, moment, hives, NgMap, inspections, measurements) 
{

	$rootScope.title    	= $rootScope.lang.locations_title;
	$scope.showMore 		= false; // multiple locations
	$scope.redirect 		= null;
	$scope.locations 		= null;
	$scope.hiveLocation 	= null;
	$scope.selectedLocationIndex= 0;
	$scope.error_msg 		= null;
	$scope.hivetypes 		= null;
	$scope.hive_type 		= null;
	$scope.hive 			= 
	{
		"name":$rootScope.lang.Location+' '+(hives.hives.length+1),
		"color":"#F29100",
		"hive_type_id":"",
		"hive_amount":"1",
		"brood_layers":"2",
		"honey_layers":"1",
		"frames":"10",
		"offset":"1",
		"prefix":"Kast",
		"country_code":"nl",
		"city":"",
		"postal_code":"",
		"street":"",
		"street_no":"",
		"lat":52,
		"lon":5,
	};

	$scope.types = "['address']";
	$scope.mybounds = {center: {lat: $scope.hive.lat, lng: $scope.hive.lon}, radius: 200000};
	
	
	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else if ($location.path().indexOf('/locations') > -1)
		{
			$scope.hivetypes = settings.hivetypes;
			
			if (hives.locations.length > 0)
			{
				$scope.locations = hives.locations;
			}
			
			if ($location.path() == 'locations/create' || $location.path().indexOf('/edit') > -1)
			{
				$rootScope.title = $rootScope.lang.Location;
				$scope.getGPS();
				// NgMap.getMap().then(function(map) 
				// {
				// 	$scope.map = map;
				// });

				if ($routeParams.locationId != undefined)
				{
					$scope.locationsUpdate();
				}
			}
			else
			{
				hives.loadRemoteLocations();
			}
		}
	};

	$scope.toggleLoc = function(loc)
	{
		hives.toggle_open_loc(loc.id);
	}

	$scope.setHiveType = function(id)
	{
		console.log(id);
		$scope.hive.hive_type_id = id;
	}

	$scope.updateTaxonomy = function()
	{
		$scope.hivetypes = settings.hivetypes;
	}
	$scope.taxonomyHandler = $rootScope.$on('taxonomyListsUpdated', $scope.updateTaxonomy);

	$scope.natSort = function(a, b) 
	{
    	return naturalSort(a.value,b.value);
	};

	$scope.transSort = function(a) 
	{
    	var locale = $rootScope.locale;
    	return a.trans[locale];
	};

	$scope.placeChanged = function() 
	{
		$scope.place = this.getPlace();
		//console.log($scope.place);
		if ($scope.map != undefined)
		{
			$scope.map.setCenter($scope.place.geometry.location);
			$scope.map.setZoom(16);
		}
		var lat = round_dec($scope.place.geometry.viewport.f.f, 3);
		var lon = round_dec($scope.place.geometry.viewport.b.b, 3);
		$scope.hive.lat = lat;
		$scope.hive.lon = lon;
		if ($scope.hiveLocation != undefined)
		{
			$scope.hiveLocation.lat = lat;
			$scope.hiveLocation.lon = lon;
		}
		// Fill Hive address
		if ($scope.place.address_components.length > 0)
		{
			for (var i = 0; i < $scope.place.address_components.length; i++) 
			{
				comp = $scope.place.address_components[i];
				compName = comp.types.length > 0 ? comp.types[0] : null;
				// See Google maps API spec: https://developers.google.com/maps/documentation/geocoding/start#Types
				switch(compName)
				{
					case "route":
						$scope.hive.street = comp.short_name;
						if ($scope.hiveLocation != undefined) $scope.hiveLocation.street = comp.short_name;
						break;
					case "street_number":
						$scope.hive.street_no = parseInt(comp.short_name);
						if ($scope.hiveLocation != undefined) $scope.hiveLocation.street_no = parseInt(comp.short_name);
						break;
					case "country":
						$scope.hive.country_code = comp.short_name.toLowerCase();
						if ($scope.hiveLocation != undefined) $scope.hiveLocation.country_code = comp.short_name.toLowerCase();
						break;
					case "postal_code":
						$scope.hive.postal_code = comp.short_name;
						if ($scope.hiveLocation != undefined) $scope.hiveLocation.postal_code = comp.short_name;
						break;
					case "locality":
						$scope.hive.city = comp.short_name;
						if ($scope.hiveLocation != undefined) $scope.hiveLocation.city = comp.short_name
						break;
				}
			}
		}
	}
	


	$scope.getGPS = function() 
	{
	    if (navigator.geolocation) {
	        navigator.geolocation.getCurrentPosition(function (position) 
	        {
                $scope.hive.lat = position.coords.latitude; 
                $scope.hive.lon = position.coords.longitude;
	        });
	    }
	}
	

	$scope.refreshAndGoHome = function()
	{
		$location.path('/locations');
	};

	$scope.showError = function(type, error)
	{
		$scope.error_msg = error.status == 500 ? $rootScope.lang.Error : $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: "+convertOjectToArray(error.message).join(', ') : '');
	}



	$scope.loadLocationIndex = function()
	{
		//console.log(id);
		if ($routeParams.locationId != undefined)
		{
			$scope.hiveLocation = hives.getHiveLocationById($routeParams.locationId);
		}
	}

	$scope.locationsUpdate = function(e, type)
	{
		$scope.showMore = hives.locations.length > 1 ? true : false;

		if (hives.locations.length > 0)
		{
			$scope.locations = hives.locations;
			$scope.loadLocationIndex();
		}
		else
		{
			$location.path('/locations/create');
		}
	};

	$scope.locationsError = function()
	{
		$scope.hiveLocation = null;
	}

	
	$scope.hivesHandler 			= $rootScope.$on('hivesUpdated', $scope.locationsUpdate);
	$scope.locationsHandler 		= $rootScope.$on('locationsLoaded', $scope.locationsUpdate);
	$scope.locationsErrorHandler 	= $rootScope.$on('locationsError', $scope.locationsError);


	$scope.createLocation = function()
	{
		api.postApiRequest('saveLocation', 'locations', $scope.hive);
		$scope.redirect = "/locations";
	};

	$scope.saveLocation = function(back)
	{
		api.patchApiRequest('saveLocation', 'locations/'+$scope.hiveLocation.id, $scope.hiveLocation);
		$scope.redirect = "/locations";
	}

	$scope.deleteLocation = function()
	{
		var text = $scope.hiveLocation.hives.length > 0 ? $rootScope.lang.first_remove_hives : '';
		text += "\r\n"+$rootScope.lang.Delete+' '+$rootScope.lang.location+' '+$scope.hiveLocation.name+'?'
		
		$rootScope.showConfirm(text, $scope.performDeleteLocation);
	}

	$scope.performDeleteLocation = function()
	{
		$scope.redirect = "/locations";
		api.deleteApiRequest('saveLocation', 'locations/'+$scope.hiveLocation.id, $scope.hiveLocation);
	}

	$scope.locationChanged = function()
	{
		if ($scope.redirect != null)
		{
			$location.path($scope.redirect);
			$scope.redirect = null;
		}
	}

	$scope.locationsSaveHandler 	= $rootScope.$on('saveLocationLoaded', $scope.locationChanged);
	$scope.locationsErrorHandler    = $rootScope.$on('saveLocationError', $scope.showError);
	

	$scope.back = function()
	{
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else
		{
			$rootScope.historyBack();
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.taxonomyHandler();
		$scope.locationsSaveHandler();
		$scope.locationsErrorHandler();
		$scope.hivesHandler();
		$scope.locationsHandler();
		$scope.locationsErrorHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});