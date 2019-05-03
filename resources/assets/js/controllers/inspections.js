/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('InspectionsCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections, groups) 
{

	$rootScope.title    	= $rootScope.lang.Inspections;
	$scope.showMore 		= false; // multiple inspections
	$scope.inspections 		= null;
	$scope.items_by_date 	= null;
	$scope.inspection 		= null;
	$scope.location 		= null;
	$scope.hive 			= null;
	$scope.hiveId 			= null;
	$scope.selectedInspectionIndex= 0;
	
	$scope.setScales = function()
	{
		$scope.gradeColor = function(value) 
		{
	        if (value == 0)
	            return '#CCC';
	        if (value < 4)
	            return '#8F1619';
	        if (value < 6)
	            return '#5F3F90';
	        if (value < 8)
	            return '#243D80';
	        if (value < 11)
	            return '#069518';

	        return '#F29100';
	    };
		    
		$scope.scoreQualityOptions = 
		{
			1: $rootScope.lang.Poor,
			2: $rootScope.lang.Fair,
			3: $rootScope.lang.Good,
			4: $rootScope.lang.Excellent
		};
		$scope.qualityColor = function(value) 
		{
	        if (value == 0)
	            return '#CCC';
	        if (value == 1)
	            return '#8F1619';
	        if (value == 2)
	            return '#5F3F90';
	        if (value == 3)
	            return '#243D80';
	        if (value == 4)
	            return '#069518';

	        return '#F29100';
	    };

		$scope.scoreAmountOptions = {
			1: $rootScope.lang.Low,
			2: $rootScope.lang.Medium,
			3: $rootScope.lang.High,
			4: $rootScope.lang.Extreme
		};
		$scope.amountColor = function(value) 
		{
	        if (value == 0)
	            return '#CCC';
	        if (value == 1)
	            return '#069518';
	        if (value == 2)
	            return '#243D80';
	        if (value == 3)
	            return '#5F3F90';
	        if (value == 4)
	            return '#8F1619';

	        return '#F29100';
	    };
	}

	$scope.init = function()
	{
		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			$scope.hiveId 	= $routeParams.hiveId;
			$scope.hive 	= hives.getHiveById($scope.hiveId);
			if ($scope.hive == null)
				$scope.hive = groups.getHiveById($scope.hiveId);
			
			$scope.showMore = hives.hives_inspected.length > 1 ? true : false;
			$scope.setScales();
			$scope.loadInspections();
			//console.log($scope.hive);
		}
	};

	$scope.localeChange = function(e)
	{
		$scope.setScales();
		$scope.loadInspections(e);
	}

	$scope.loadInspections = function(e)
	{
		inspections.loadRemoteInspections($scope.hiveId);
	}

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
		$scope.inspections 	 = inspections.inspections.inspections; 
		$scope.items_by_date = inspections.inspections.items_by_date; 
		
		if ($scope.inspections && $scope.inspections.length > 0)
		{
			console.log('Inspections have '+$scope.inspections.length+' dates');
		}
		else
		{
			//$location.path('/hives/'+$routeParams.hiveId+'/inspect'); // create first inspection
		}

	};

	$scope.inspectionsError = function()
	{
		$scope.conditions = null;
		$scope.actions 	  = null;
	}

	function deleteInspection(id)
	{
		if (id)
			api.deleteApiRequest('deleteInspection', 'inspections/'+id);
	}

	$scope.confirmDeleteInspection = function(id)
	{
		if (id)
			$rootScope.showConfirm($rootScope.lang.remove_inspection+'?', deleteInspection, id);
	}

	$scope.inspectionsDeleteHandler	= $rootScope.$on('deleteInspectionLoaded', $scope.loadInspections);
	$scope.inspectionsHandler 		= $rootScope.$on('inspectionsUpdated', $scope.inspectionsUpdate);
	$scope.inspectionsErrorHandler 	= $rootScope.$on('inspectionsError', $scope.inspectionsError);
	$scope.localeChangeHandler 		= $rootScope.$on('localeChange', $scope.localeChange);

	$scope.getHiveName = function(id)
	{
		console.log('getHiveName', id);
		var name = hives.getHiveNameById(id);
		return name != null ? name : $rootScope.lang.Hive + ' id: ' + id;
	}

	$scope.getApiaryName = function(id)
	{
		var loc = hives.getHiveLocationById(id);
		return loc != null ? loc.name : '';
	}

	$scope.loadHiveIndex = function(direction)
	{
		var i   = hives.getHiveInspectedIndex($routeParams.hiveId);
		console.log('inspections loadedHiveIndex:', i);
		var max = hives.hives_inspected.length-1;
		if (i < max && direction > 0)
		{
			$scope.hive = hives.hives_inspected[i+1];
		}
		else if (i > 0 && direction < 0)
		{
			$scope.hive = hives.hives_inspected[i-1];
		}
		else
		{
			if (direction > 0)
			{
				$scope.hive = hives.hives_inspected[0];
			}
			else
			{
				$scope.hive = hives.hives_inspected[max];
			}
		}
		$location.path('/hives/'+$scope.hive.id+'/inspections'); // create first inspection
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
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else
		{
			for (var i = $rootScope.history.length - 1; i >= 0; i--) // make sure that back goes to the previous main screen
			{
				var path = $rootScope.history[i];
				var go   = false;
				var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null ? $scope.hive.id : '';

				if (path.indexOf('/locations') > -1 || (path.indexOf('/hives') > -1 && path.indexOf('/hives/') == -1) || path.indexOf('/groups') > -1)
					go = true;

				if (go)
					return $location.path(path);
			}
			$location.path('/locations');
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.inspectionsHandler();
		$scope.inspectionsErrorHandler();
		$scope.localeChangeHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});