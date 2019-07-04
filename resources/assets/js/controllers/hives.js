/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('HivesCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections, groups) 
{

	$rootScope.title    	= $rootScope.lang.hives_title;
	$scope.pageTitle       = '';
	$scope.showMore 		= false; // multiple hives
	$scope.redirect 		= null;
	$scope.hives 			= [];
	$scope.hive 			= null;
	$scope.hive_loc 		= null;
	$scope.hive_type 		= null;
	$scope.bee_race			= null;
	$scope.beeraces 		= null;
	$scope.hivetypes 		= null;
	$scope.locations 		= null;
	$scope.error_msg 		= null;
	$scope.selectedHiveIndex= 0;
	$scope.queen_colored    = false;
	$scope.queen_colors     = ['#4A90E2','#F4F4F4','#F8DB31','#D0021B','#7ED321','#4A90E2','#F4F4F4','#F8DB31','#D0021B','#7ED321']; // year ending of birth year is index
	$scope.orderName        = 'name';
	$scope.orderDirection   = false;

	$scope.dateFormat   	= 'yyyy-MM-dd';

	$scope.setDateLanguage = function()
	{
		$("#dtBox").DateTimePicker(
        {
            dateFormat 		: $scope.dateFormat, // ISO formatted date
			language 		: $rootScope.locale,
			mode 			: 'date',
			formatHumanDate : function(dateObj, mode, format)
						        {
					        		var output = '';
					        		output 	  += dateObj.day + ' ';
					        		output 	  += parseInt(dateObj.dd) + ' ';
					        		output 	  += dateObj.month + ' ';
					        		output 	  += dateObj.yyyy;
					        		return output;
						    	},
			afterShow 		: function(inputElement)
								{
					        		$("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set mobile input keyboard to numeric
								}
        });
	};


	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else if ($location.path().indexOf('/hives') > -1)
		{
			if ($routeParams.hiveId != undefined || $location.path().indexOf('/hives/create') > -1)
			{
				$scope.setDateLanguage();
				$scope.initHives();
				$rootScope.title = $rootScope.lang.Hive;
				if ($location.path().indexOf('/hives/create') > -1)
				{
					$scope.pageTitle = $rootScope.mobile ? $rootScope.lang.New + ' ' +$rootScope.lang.hive : $rootScope.lang.create_new + ' ' +$rootScope.lang.hive;
				}
			} 
			else
			{
				if (hives.hives.length > 0)
				{
					$scope.initHives();
				}
				else
				{
					$location.path('/locations');
				}
			}
		}

	};

	$scope.initHives = function()
	{
		$scope.beeraces  = settings.beeraces;
		$scope.hivetypes = settings.hivetypes;
		$scope.locations = hives.locations_owned;
		
		if (hives.hives.length > 0)
		{
			$scope.hives = hives.hives_owned;
		}
		$scope.showMore = $scope.hives.length > 1 ? true : false;
		
		if ($routeParams.location_id)
		{
			$scope.hive_loc   = {id:parseInt($routeParams.location_id)};
		}

		if ($location.path().indexOf('/hives/create') > -1)
		{
			$scope.hive = {'location_id': $scope.hive_loc != null ? $scope.hive_loc.id : null, 'name':$rootScope.lang.Hive+' '+($scope.hives.length+1) ,'color':'', 'hive_type_id':'', 'brood_layers':1, 'honey_layers':1, 'frames':10, 'queen':{}};
			//console.log($scope.hive);
			$scope.add_hive_watchers();
		}
		else
		{
			$scope.loadHiveIndex();
		}
	}

	$scope.updateTaxonomy = function()
	{
		$scope.beeraces  = settings.beeraces;
		$scope.hivetypes = settings.hivetypes;
		//console.log('taxonomyListsUpdated');
	}
	$scope.taxonomyHandler = $rootScope.$on('taxonomyListsUpdated', $scope.updateTaxonomy);


	$scope.hiveFilter = function(a, b)
	{
		console.log(a,b);
	}

	$scope.setOrder = function(name)
	{
		if ($scope.orderName == name)
		{
			$scope.orderDirection = !$scope.orderDirection;
		}
		else 
		{
			if (name == 'attention' || 'impression')
			{
				$scope.orderDirection = true;
			}
			else
			{
				$scope.orderDirection = false;	
			}
		}
		$scope.orderName = name;
	}

	$scope.natSort = function(a, b) 
	{
    	//console.log($scope.orderName, a.value, b.value);
    	if ($scope.orderName == 'impression')
    	{
    		return b.value - a.value;
    	}
    	else if ($scope.orderName == 'attention')
    	{
    		if (a.value != 1)
    			return -1;

    		if (b.value != 1)
    			return 1;

    		return b.value - a.value;
    	}
    	else if ($scope.orderName == 'reminder_date')
    	{
    		if (a.value == null || a.value == '')
    			return -1;

    		if (b.value == null || b.value == '')
    			return 1;
    	}

    	return naturalSort(a.value,b.value);
	};

	$scope.transSort = function(a) 
	{
    	var locale = $rootScope.locale;
    	return a.trans[locale];
	};

	$scope.hivesUpdate = function(e, type)
	{
		$scope.initHives();
	};

	$scope.selectLocation = function(item)
	{
		$scope.hive.location_id = item.id;
	}
	$scope.selectHiveType = function(item)
	{
		$scope.hive.hive_type_id = item.id;
	}
	$scope.selectBeeRace = function(item)
	{
		$scope.hive.queen.race_id = item.id;
	}

	$scope.add_hive_watchers = function()
	{
		if ($scope.hive.queen != undefined && $scope.hive.queen != null && $scope.hive.queen.created_at == null)
			$scope.hive.queen.created_at = moment().format($scope.dateFormat.toUpperCase());

		$scope.queen_colored = ($scope.hive.queen.color != '' && $scope.hive.queen.color != null);
		$scope.queenBirthColor();
		$scope.hive_loc   = {id:$scope.hive.location_id};
		if ($scope.hive.hive_type_id && $scope.hive.hive_type_id != '')
			$scope.hive_type  = {id:$scope.hive.hive_type_id};
		if ($scope.hive.queen.race_id && $scope.hive.queen.race_id != '')
			$scope.bee_race   = {id:$scope.hive.queen.race_id};
		
		// Watch layers and frames
		$scope.$watch('hive.brood_layers', function(o,n){ if (n != o) $scope.layersChange(o-n, 'brood') });
	    $scope.$watch('hive.honey_layers', function(o,n){ if (n != o) $scope.layersChange(o-n, 'honey') });
		$scope.$watch('hive.frames', 	   function(o,n){ if (n != o) $scope.framesChange(o-n) });
		$scope.$watch('hive.queen.created_at', function(o,n){ if (n != o) $scope.queenBirthColor(true) });
		// $scope.$watch('hive_loc', function(o,n){ if (n != o && $scope.hive_loc != null) $scope.hive.location_id = $scope.hive_loc.id });
		// $scope.$watch('hive_type', function(o,n){ if (n != o && $scope.hive_type != null) $scope.hive.hive_type_id = $scope.hive_type.id; });
		//$scope.$watch('bee_race', function(o,n){ if (n != o && $scope.bee_race != null) $scope.hive.queen.race_id = $scope.bee_race.id });
	}

	$scope.loadHiveIndex = function()
	{
		$scope.hive	= hives.getHiveById($routeParams.hiveId);

		if ($scope.hive == null)
			$scope.hive = groups.getHiveById($routeParams.hiveId);

		if ($scope.hive != undefined && ($location.path().indexOf('/hives/create') > -1 || $location.path().indexOf('/edit') > -1))
		{
			//console.log('loadHiveIndex', $routeParams.hiveId, $scope.hive.name);
			$scope.pageTitle = $scope.hive.name;

			$scope.add_hive_watchers();
		}
	}

	$scope.queen_colored_change = function()
	{
		if ($scope.queen_colored)
		{
			$scope.hive.queen.color = '#FFFFFF';
		}
		else
		{
			$scope.hive.queen.color = '';
		}
	}

	$scope.queenBirthColor = function(forceChangeColor)
	{
		format 	 = $scope.dateFormat.toUpperCase();
		date 	 = $scope.hive.queen.created_at;
		dateNow  = moment();
		dateBirth= moment(date, format);
		yearsOld = dateNow.diff(dateBirth, 'years', true);
		//console.log(format, yearsOld);
		$scope.hive.queen.age = isNaN(yearsOld) ? 0 : round_dec(yearsOld,1);

		year 	 = moment(date).year();
		yearEnd  = year.toString().substr(3, 1);
		
		if ($scope.queen_colored && ($scope.hive.queen.color == '' || forceChangeColor))
			$scope.hive.queen.color = $scope.queen_colors[yearEnd];
	}

	$scope.layersChange = function(amount, type)
	{
		if ($scope.hive.layers == undefined || $scope.hive.layers.length == 0)
			return;

		l = angular.copy($scope.hive.layers[0]);
		l.type = type;
		
		if (amount > 0)
		{
			$scope.hive.layers.push(l);
		}
		else if (amount < 0 && $scope.hive.layers.length > 1)
		{
			for (var i = $scope.hive.layers.length-1; i >= 0; i--) 
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
		if ($scope.hive.layers == undefined || $scope.hive.layers.length == 0)
			return;
		
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
		$scope.redirect = "/locations";
		api.deleteApiRequest('deleteHive', 'hives/'+$scope.hive.id, $scope.hive);
	}

	$scope.confirmDeleteHive = function()
	{
		$rootScope.showConfirm($rootScope.lang.remove_hive+'?', $scope.deleteHive);
	}

	$scope.hivesError = function(type, error)
	{
		if (typeof error.errors != 'undefined')
			$scope.error_msg = $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: "+convertOjectToArray(error.errors).join(', ') : '');
		else
			$scope.error_msg = $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: "+convertOjectToArray(error.message).join(', ') : '');
	}

	$scope.hiveChanged = function()
	{
		if ($scope.redirect != null)
		{
			$scope.redirect = null;
			$scope.back();
		}
	}

	$scope.hivesDeleteError 	= $rootScope.$on('deleteHiveError', $scope.hivesError);
	$scope.hivesSaveError 		= $rootScope.$on('saveHiveError', $scope.hivesError);
	$scope.hivesDeleteHandler 	= $rootScope.$on('deleteHiveLoaded', $scope.hiveChanged);
	$scope.hivesSaveHandler 	= $rootScope.$on('saveHiveLoaded', $scope.hiveChanged);
	$scope.hivesHandler 		= $rootScope.$on('hivesUpdated', $scope.hivesUpdate);
	$scope.hivesErrorHandler 	= $rootScope.$on('hivesError', $scope.hivesError);

	$scope.back = function()
	{
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else
		{
			for (var i = $rootScope.history.length - 1; i >= 0; i--) 
			{
				var path = $rootScope.history[i];
				var go   = false;
				var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null  ? $scope.hive.id : '';
				
				if (path.indexOf('/locations') > -1 || (path.indexOf('/hives') > -1 && path.indexOf('/hives/'+hive_id) == -1) || path.indexOf('/groups') > -1)
					go = true;

				if (go)
					return $location.path(path);
			}
			$rootScope.historyBack();
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);



	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.taxonomyHandler();
		$scope.hivesDeleteError();
		$scope.hivesSaveError();
		$scope.hivesDeleteHandler();
		$scope.hivesSaveHandler();
		$scope.hivesHandler();
		$scope.hivesErrorHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});