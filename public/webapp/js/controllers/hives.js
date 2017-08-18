/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('HivesCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.hives_title;
	$scope.showMore 		= false; // multiple hives
	$scope.redirect 		= null;
	$scope.hives 			= null;
	$scope.hive 			= null;
	$scope.beeraces 		= null;
	$scope.hivetypes 		= null;
	$scope.locations 		= null;
	$scope.error_msg 		= null;
	$scope.selectedHiveIndex= 0;
	$scope.queen_colors     = ['#4A90E2','#F4F4F4','#F8DB31','#D0021B','#7ED321','#4A90E2','#F4F4F4','#F8DB31','#D0021B','#7ED321']; // year ending of birth year is index

	$scope.datePickerOptions = {
	  format: 'yyyy-mm-dd', // ISO formatted date
	  onClose: function(e) 
	  {
	  }
	}

	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else if ($location.path().indexOf('/hives') > -1)
		{
			if (hives.hives.length > 0)
			{
				if (hives.locations.length > 0)
					$scope.locations = hives.locations;
	
				$scope.hives 	 = hives.hives;
				$scope.beeraces  = inspections.beeraces;
				$scope.hivetypes = inspections.hivetypes;

				if ($location.path().indexOf('/hives/create') > -1)
				{
					$scope.hive = {'location_id':$routeParams.location_id != undefined ? parseInt($routeParams.location_id) : 1, 'color':'', 'hive_type_id':1, 'brood_layers':1, 'honey_layers':1, 'frames':10, 'queen':{}};
					//console.log($scope.hive);
				}
				$scope.loadHiveIndex();
			}
			else
			{
				$location.path('/locations/create');
			}
		}

	};

	$scope.natSort = function(a, b) 
	{
    	return naturalSort(a.value,b.value);
	};

	$scope.hivesUpdate = function(e, type)
	{
		$scope.showMore = hives.hives.length > 1 ? true : false;

		if (hives.hives.length > 0)
		{
			$scope.hives = hives.hives;
			$scope.loadHiveIndex();
		}
	};

	$scope.loadHiveIndex = function()
	{
		if ($routeParams.hiveId != undefined)
			$scope.hive	= hives.getHiveById($routeParams.hiveId);
			
		if ($location.path().indexOf('/hives/create') > -1 || $location.path().indexOf('/edit') > -1)
		{
			if ($scope.hive.queen.created_at == null)
				$scope.hive.queen.created_at = moment().format(inspections.DATE_FORMAT_API);

			$scope.queenBirthColor();
			$scope.hive_loc   = {id:$scope.hive.location_id};
			$scope.hive_type  = {id:$scope.hive.hive_type_id};
			$scope.queen_race = {id:$scope.hive.queen.race_id};

			// Watch layers and frames
			$scope.$watch('hive.brood_layers', function(o,n){ if (n != o) $scope.layersChange(o-n, 'brood') });
		    $scope.$watch('hive.honey_layers', function(o,n){ if (n != o) $scope.layersChange(o-n, 'honey') });
			$scope.$watch('hive.frames', 	   function(o,n){ if (n != o) $scope.framesChange(o-n) });
			$scope.$watch('hive.queen.created_at', function(o,n){ if (n != o) $scope.queenBirthColor(true) });
			$scope.$watch('hive_loc', function(o,n){ if (n != o) $scope.hive.location_id = $scope.hive_loc.id });
			$scope.$watch('hive_type', function(o,n){ if (n != o) $scope.hive.hive_type_id = $scope.hive_type.id });
			$scope.$watch('queen_race', function(o,n){ if (n != o) $scope.hive.queen.race_id = $scope.queen_race.id });
		}
	}

	$scope.queenBirthColor = function(forceChangeColor)
	{
		format 	 = $scope.datePickerOptions.format.toUpperCase();
		date 	 = $scope.hive.queen.created_at;
		dateNow  = moment();
		dateBirth= moment(date, format);
		yearsOld = dateNow.diff(dateBirth, 'years', true);
		//console.log(format, yearsOld);
		$scope.hive.queen.age = isNaN(yearsOld) ? 0 : round_dec(yearsOld,1);

		year 	 = moment(date).year();
		yearEnd  = year.toString().substr(3, 1);
		
		if ($scope.hive.queen.color == '' || forceChangeColor)
			$scope.hive.queen.color = $scope.queen_colors[yearEnd];
	}

	$scope.layersChange = function(amount, type)
	{
		l = angular.copy($scope.hive.layers[0]);
		l.type = type;
		
		if (amount > 0)
		{
			$scope.hive.layers.push(l);
		}
		else if (amount < 0 && $scope.hive.layers.length > 1)
		{
			for (var i = 0; i < $scope.hive.layers.length; i++) 
			{
				l = $scope.hive.layers[i];
				if (l.type == type)
				{
					$scope.hive.layers.splice(i, 1);
					break;
				}
			}
		}
	}	

	$scope.framesChange = function(amount)
	{
		f = angular.copy($scope.hive.layers[0].frames);
		
		for (var i = 0; i < $scope.hive.layers.length; i++) 
		{
			frames = $scope.hive.layers[i].frames;
			if (amount > 0)
			{
				frames.push(f);
			}
			else if (frames.length > 1)
			{
				frames.pop();
			}
		}
		$scope.hive = hives.calculateHiveWidth($scope.hive);
	}

	$scope.saveHive = function(back)
	{
		if ($location.path().indexOf('/hives/create') > -1)
		{
			api.postApiRequest('saveHive', 'hives', $scope.hive);
		}
		else
		{
			api.patchApiRequest('saveHive', 'hives/'+$scope.hive.id, $scope.hive);
		}
		$scope.redirect = "/locations";
	}

	
	$scope.deleteHive = function()
	{
		api.deleteApiRequest('deleteHive', 'hives/'+$scope.hive.id, $scope.hive);
		$scope.redirect = "/locations";
	}

	$scope.hivesError = function(type, error)
	{
		$scope.error_msg = $rootScope.lang.empty_fields+". Status: "+error.status;
	}

	$scope.hiveChanged = function()
	{
		if ($scope.redirect != null)
		{
			$location.path($scope.redirect);
			$scope.redirect = null;
		}
	}

	$scope.hivesDeleteError 	= $rootScope.$on('deleteHiveError', $scope.hivesError);
	$scope.hivesSaveError 		= $rootScope.$on('saveHiveError', $scope.hivesError);
	$scope.hivesDeleteHandler 	= $rootScope.$on('deleteHiveLoaded', $scope.hiveChanged);
	$scope.hivesSaveHandler 	= $rootScope.$on('saveHiveLoaded', $scope.hiveChanged);
	$scope.hivesHandler 		= $rootScope.$on('hivesUpdated', $scope.hivesUpdate);
	$scope.hivesErrorHandler 	= $rootScope.$on('hivesError', $scope.hivesError);

	


	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.hivesDeleteError();
		$scope.hivesSaveError();
		$scope.hivesDeleteHandler();
		$scope.hivesSaveHandler();
		$scope.hivesHandler();
		$scope.hivesErrorHandler();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});