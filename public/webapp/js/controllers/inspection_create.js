/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('InspectionCreateCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, $timeout, settings, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.Inspections;
	$scope.showMore 		= false; // multiple inspections
	
	$scope.checklist 		= null;
	$scope.checklists 		= null;
	$scope.checklist_id 	= null;

	$scope.inspection    	= {};

	$scope.hive 			= null;
	$scope.hives 			= null;
	$scope.location 		= null;
	$scope.locations 		= null;

	$scope.beeraces 		= null;
	$scope.hivetypes 		= null;

	$scope.langScript	    = $rootScope.lang.pick_a_date_lang_file;

	$scope.init = function()
	{
		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			// console.log('checklist', inspections.checklist);
			// console.log('checklists', inspections.checklists);
			$scope.setDateLanguage();
			$rootScope.beeraces   = settings.beeraces;
			$rootScope.hivetypes  = settings.hivetypes;
			$rootScope.hives  	  = hives.hives;
			$rootScope.locations  = hives.locations;
			$scope.hive 	  	  = hives.getHiveById($routeParams.hiveId);
			$scope.inspection 	  = inspections.newSaveObject();
			$scope.checklistsUpdated();
			$scope.checklist      = inspections.checklist;
			$scope.updateLists(false);
			$scope.showMore   	  = hives.hives.length > 1 ? true : false;
			if ($routeParams.inspectionId)
			{
				$scope.inspection_id = $routeParams.inspectionId;
				inspections.loadRemoteInspection($routeParams.inspectionId);
			}
		}
	};

	$scope.setDateLanguage = function()
	{
		$scope.datePickerOptions = // See vendor/pickadate/lib/translations
		{
			monthsFull 	: $rootScope.lang.monthsFull,
			monthsShort : $rootScope.lang.monthsShort,
			weekdaysFull: $rootScope.lang.weekdaysFull,
			weekdaysShort: $rootScope.lang.weekdaysShort,
			today 		: $rootScope.lang.Today,
			clear 		: $rootScope.lang.Clear,
			close 		: $rootScope.lang.Close,
			firstDay 	: $rootScope.lang.firstDay,
			// format 		: $rootScope.lang.format,
			// formatSubmit : 'yyyy-mm-dd',
			format 		: 'yyyy-mm-dd', // ISO formatted date
			onClose: function(e) {
			// do something when the picker closes   
			}
		}
	}

	$rootScope.changeChecklistItem = function(type, id, value, items)
	{
		//console.log(type, id, value, items);
		inspections.createInspectionObject(type, id, value, items);
	}

	$scope.inspectionGeneralItem = function(type, id, value, items)
	{
		inspections.createInspectionObject(type, id, value, items);
	}

	$scope.renderSliders = function()
	{
		$timeout(function() 
			{
				console.log('rzSliderForceRender');
				$scope.$broadcast('rzSliderForceRender');
			}, 100);
	}

	$scope.saveInspection = function()
	{
		var data  	 = inspections.saveObject;
		data.hive_id = $routeParams.hiveId;
		//console.log("saveInspection", data);
		if (data != null) api.postApiRequest('saveInspection', 'inspections/store', data);
	}
	
	$scope.showError = function(type, error)
	{
		$scope.error_msg = $rootScope.lang.empty_fields+". Status: "+error.status;
	}


	$scope.saveAndeditChecklist = function()
	{
		//console.log('saveAndeditChecklist');
		$scope.saveInspectionHandler = $rootScope.$on('saveInspectionLoaded', $scope.navigateToEditChecklist);
		$scope.saveInspection();
	}

	$scope.navigateToEditChecklist = function(type, data)
	{
		//console.log('navigateToEditChecklist', data);
		var inspection_id = data ? data : $scope.inspection_id;
		$location.path('/checklist/'+$scope.checklist.id+'/edit').search({hive_id:$routeParams.hiveId, inspection_edit:inspection_id});
	}
	
	$scope.editChecklist = function()
	{
		var so = inspections.saveObject;
		//console.log('editChecklist', so);
		if (so && (Object.keys(so.items).length > 0 || so.impression != -1 || so.attention != -1 || so.notes != '' || so.reminder != '' || so.reminder_date != ''))
		{
			$rootScope.showConfirm($rootScope.lang.save_input_first, $scope.saveAndeditChecklist, null, $scope.navigateToEditChecklist);
		}
		else
		{
			$scope.navigateToEditChecklist();
		}
	}

	$scope.refreshAndGoHome = function()
	{
		$location.path('/hives/'+$routeParams.hiveId+'/inspections');
	};

	$scope.saveInspectionHandler 	  = $rootScope.$on('saveInspectionLoaded', $scope.refreshAndGoHome);
	$scope.saveInspectionErrorHandler = $rootScope.$on('saveInspectionError', $scope.showError);


    $scope.updateLists = function(force=false)
	{
		var id = $scope.checklist ? $scope.checklist.id : null
		if (inspections.checklist == null || force)
		{
			$scope.setDateLanguage();
			$scope.selectChecklist(id, force);
			//console.log('selected checklist id NULL', id, force);
		}
		else
		{
			//console.log('selected checklist id NOT NULL', id, force);
			$scope.checklistUpdated(null, null);
		}
	};
	
	$scope.checklistUpdated = function(e, type)
	{
		$scope.checklist  = inspections.checklist;
		var id = $scope.checklist ? $scope.checklist.id : null
		
		if (id != null)
		{
			$scope.checklist_id = id;
			$scope.checklists = null;
			$scope.checklists = inspections.checklists;
			//console.log('checklistUpdated id', id, $scope.checklists);
		}

		if (typeof e != 'undefined' && e != null && typeof e.name != 'undefined' && e.name == 'localeChange')
			$scope.updateLists(true);
	};
	$scope.checklistHandler = $rootScope.$on('checklistUpdated', $scope.checklistUpdated);
	$scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.checklistUpdated);


	$scope.checklistsUpdated = function(e, type)
	{
		$scope.checklists = inspections.checklists;
	};
	$scope.checklistsHandler = $rootScope.$on('checklistsUpdated', $scope.checklistsUpdated);


	$scope.selectChecklist = function(id, force=false)
	{
		if ($scope.checklist && id == $scope.checklist.id && force==false)
		{
			console.log('DO NOT selectChecklist', id, force);
			return;
		}
		
		$scope.checklist_id = id;
		inspections.loadChecklist(id);
	}

	$scope.inspectionUpdate = function(e, data)
	{
		$scope.inspection = inspections.newSaveObject(data);
	};
	$scope.inspectionHandler = $rootScope.$on('inspectionUpdated', $scope.inspectionUpdate);


	$scope.loadHiveIndex = function(direction)
	{
		var i   = hives.getHiveIndex($routeParams.hiveId);
		//console.log('inspection_create loadedHiveIndex:', i);
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
		$location.path('/hives/'+$scope.hive.id+'/inspect');

		//inspections.loadRemoteInspections($scope.hive.id);
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
			$rootScope.historyBack();
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	// remove references to the controller
    $scope.removeListeners = function()
    {
		$scope.saveInspectionHandler();
		$scope.saveInspectionErrorHandler();
		$scope.checklistHandler();
		$scope.checklistsHandler();
		$scope.localeChangeHandler();
		$scope.inspectionHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});