/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('InspectionsCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.Inspections;
	$scope.showMore 		= false; // multiple inspections
	$scope.inspections 		= null;
	$scope.conditions 		= null;
	$scope.actions 			= null;
	$scope.inspection 		= null;
	$scope.hive 			= null;
	$scope.hiveId 			= null;
	$scope.location 		= null;
	$scope.selectedInspectionIndex= 0;
	
	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			$scope.hiveId 	= $routeParams.hiveId;
			$scope.hive 	= hives.getHiveById($routeParams.hiveId);
			inspections.loadRemoteInspections($routeParams.hiveId);
			$scope.showMore = hives.hives.length > 1 ? true : false;
		}
	};

	$scope.rounddec = function(v, d)
	{
		return round_dec(v, d).toString();
	}
	$scope.parseBool = function(v)
	{
		//console.log(name, v);
		return parseInt(v);
	}

	$scope.inspectionsUpdate = function(e, type)
	{
		$scope.inspections = inspections.inspections; 

		if ($scope.inspections.dates.length > 0)
		{
			console.log('Inspections have '+$scope.inspections.dates.length+' dates');
		}
		else
		{
			$location.path('/hives/'+$routeParams.hiveId+'/inspect'); // create first inspection
		}

	};

	$scope.inspectionsError = function()
	{
		$scope.conditions = null;
		$scope.actions 	  = null;
	}

	$scope.inspectionsHandler 		= $rootScope.$on('inspectionsUpdated', $scope.inspectionsUpdate);
	$scope.inspectionsErrorHandler 	= $rootScope.$on('inspectionsError', $scope.inspectionsError);

	$scope.loadHiveIndex = function(direction)
	{
		var i   = hives.getHiveIndex($routeParams.hiveId);
		console.log('inspections loadedHiveIndex:', i);
		var max = hives.hives.length-1;
		if (i < max && direction > 0)
		{
			$scope.hive = hives.hives[i+1];
		}
		else if (i > 0 && direction < 0)
		{
			$scope.hive = hives.hives[i-1];
		}
		else
		{
			if (direction > 0)
			{
				$scope.hive = hives.hives[0];
			}
			else
			{
				$scope.hive = hives.hives[max];
			}
		}
		$routeParams.hiveId = $scope.hive.id;
		inspections.loadRemoteInspections($scope.hive.id);
	}

	$scope.prevHive = function(e)
	{
		$scope.loadHiveIndex(-1);
	}
	$scope.nextHive = function(e)
	{
		$scope.loadHiveIndex(1);
	}

	$scope.back = function()
	{
		$rootScope.optionsDialog.close();
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.inspectionsHandler();
		$scope.inspectionsErrorHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});