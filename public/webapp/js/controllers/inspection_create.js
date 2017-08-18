/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('InspectionCreateCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.Inspections;
	$scope.showMore 		= false; // multiple inspections
	$scope.inspections 		= null;
	$scope.conditions 		= null;
	$scope.actions 			= null;
	$scope.inspection 		= null;
	$scope.hive 			= null;
	$scope.location 		= null;
	$scope.selectedInspectionIndex= 0;
	$scope.rating 			= -1;

	$scope.datePickerOptions = {
	  format: 'yyyy-mm-dd', // ISO formatted date
	  onClose: function(e) {
	    // do something when the picker closes   
	  }
	}


	$scope.init = function()
	{
		$scope.inspectionDate 	= moment().format(inspections.DATE_FORMAT_API);
		console.log('inspectionDate',$scope.inspectionDate);
		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			$scope.hive = hives.getHiveById($routeParams.hiveId);
			$scope.updateLists();
			$scope.showMore = hives.hives.length > 1 ? true : false;
		}

	};

	$scope.addRemoveOption = function(opt, item)
	{
		var add 	= true;
		var options = item.options == '' ? [] : item.options.split(',');
		var values  = item.value   == '' ? [] : item.value.split(',');

		for (var i = 0; i < values.length; i++) 
		{
			if (values[i] == opt) // remove
			{
				add = false;
				values.splice(i,1);
				break;
			}
		}
		if (add && options.indexOf(opt) > -1) // only add if present in item options list
			values.push(opt); // add

		item.value = values.join(',');
		//console.log(item.name+' length='+values.length+' values='+item.value);
	}


	$scope.saveInspection = function()
	{
		$scope.saveConditions(inspections.createSaveArray($scope.conditions, $scope.inspectionDate, $routeParams.hiveId));
		$scope.saveActions(inspections.createSaveArray($scope.actions, $scope.inspectionDate, $routeParams.hiveId));
	}

	
	
	$scope.saveConditions = function(data)
	{
		console.log("saveConditions");
		console.table(data);
		if (data != null) api.postApiRequest('saveConditions', 'conditions/multiple', data);
	};
	
	
	$scope.saveActions = function(data)
	{
		console.log("saveActions");
		console.table(data);
		if (data != null) api.postApiRequest('saveActions', 'actions/multiple', data);
	};

	$scope.showError = function(type, error)
	{
		$scope.error_msg = $rootScope.lang.empty_fields+". Status: "+error.status;
	}

	$scope.refreshAndGoHome = function()
	{
		$location.path('/hives/'+$routeParams.hiveId+'/inspections');
	};

	$scope.saveConditionsHandler 	  = $rootScope.$on('saveConditionsLoaded', $scope.refreshAndGoHome);
	$scope.saveConditionsErrorHandler = $rootScope.$on('saveConditionsError', $scope.showError);
	$scope.saveActionsHandler 	   = $rootScope.$on('saveActionsLoaded', $scope.refreshAndGoHome);
	$scope.saveActionsErrorHandler = $rootScope.$on('saveActionsError', $scope.showError);
	

	
	$scope.rateFunction = function(rating) {
      console.log('Rating selected: ' + rating);
    };

    $scope.updateLists = function(e, type)
	{
		if (inspections.conditions.length == 0 && inspections.actions.length == 0)
		{
			inspections.loadinspectionLists();
		}
		else
		{
			$scope.inspectionListsUpdated(null, null);
		}
	};
	
	$scope.inspectionListsUpdated = function(e, type)
	{
		console.log("inspection_create -> inspectionListsUpdated");
		$scope.conditions 	= angular.copy(inspections.conditions);
		$scope.actions 		= angular.copy(inspections.actions);
	};

	$scope.inspectionListsHandler = $rootScope.$on('inspectionListsUpdated', $scope.inspectionListsUpdated);


	$scope.inspectionsUpdate = function(e, type)
	{
		$scope.inspections = inspections.inspections; 

		if ($scope.inspections.dates.length > 0)
		{
			$scope.refreshAndGoHome();
		}
	};

	$scope.inspectionsHandler = $rootScope.$on('inspectionsUpdated', $scope.inspectionsUpdate);

	$scope.loadHiveIndex = function(direction)
	{
		var i   = hives.getHiveIndex($routeParams.hiveId);
		console.log('inspection_create loadedHiveIndex:', i);
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
		$scope.saveConditionsHandler();
		$scope.saveConditionsErrorHandler();
		$scope.saveActionsHandler();
		$scope.saveActionsErrorHandler();
		$scope.inspectionListsHandler();
		$scope.inspectionsHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});