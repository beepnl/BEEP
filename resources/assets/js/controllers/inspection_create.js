/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('InspectionCreateCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, $timeout, settings, api, moment, hives, groups, inspections) 
{

	$rootScope.title    	= $rootScope.lang.Inspections;
	$scope.showMore 		= false; // multiple inspections
	
	$scope.checklist 		= null;
	$scope.checklists 		= null;
	$scope.checklist_id 	= null;
	$scope.memory 			= null; // rember object for storing info before navigating

	$scope.inspection    	= {};

	$scope.hive 			= null;
	$scope.hives 			= null;
	$scope.location 		= null;

	$scope.langScript	    = $rootScope.lang.pick_a_date_lang_file;

	$scope.init = function()
	{
		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			$scope.setDateLanguage();

			$rootScope.beeraces   = settings.beeraces;
			$rootScope.hivetypes  = settings.hivetypes;
			
			$scope.setHiveFromRoute();
			$scope.inspectionInit();
			inspections.getChecklists();
		}
	};

	$scope.setHiveFromRoute = function()
	{
		if (hives.hives.length > 0)
		{
			$rootScope.hives  	  = hives.hives;
			$rootScope.locations  = hives.locations;
			$scope.showMore   	  = hives.hives.length > 1 ? true : false;

			if (typeof $routeParams.hiveId != 'undefined')
				$scope.hive = hives.getHiveById($routeParams.hiveId);
		}

		if ($scope.hive == null && groups.hives.length > 0 && typeof $routeParams.hiveId != 'undefined')
			$scope.hive = groups.getHiveById($routeParams.hiveId);

		if ($scope.hive == null && $rootScope.hive != null)
			$scope.hive = $rootScope.hive;

		// for tpa interface and calculation
		if ($scope.hive != null)
		{
			if (typeof $routeParams.inspectionId != 'undefined')
			{
				$scope.hive.brood_layers_tpa = null;
				$scope.hive.frames_tpa = null;
			}
			else
			{
				if (typeof $scope.hive.brood_layers != 'undefined')
					$scope.hive.brood_layers_tpa = $scope.hive.brood_layers;

				if (typeof $scope.hive.frames != 'undefined')
					$scope.hive.frames_tpa = $scope.hive.frames;
			}
		}

		if ($rootScope.hive != $scope.hive)
			$rootScope.hive = $scope.hive;


	}
	$scope.hivesHandler  = $rootScope.$on('hivesUpdated', $scope.setHiveFromRoute);
	$scope.groupsHandler = $rootScope.$on('groupsUpdated', $scope.setHiveFromRoute);

    // Datepicker
	$scope.setDateLanguage = function()
	{
		$("#dtBox").DateTimePicker(
        {
            dateTimeFormat 	: 'yyyy-MM-dd HH:mm', // ISO formatted date
			language 		: $rootScope.locale,
			mode 			: 'datetime',
			formatHumanDate : function(dateObj, mode, format)
						        {
					        		var output = '';
					        		output 	  += dateObj.day + ' ';
					        		output 	  += parseInt(dateObj.dd) + ' ';
					        		output 	  += dateObj.month + ' ';
					        		output 	  += dateObj.yyyy + ', ';
					        		output 	  += dateObj.HH + ':';
					        		output 	  += dateObj.mm + ' ';
					        		return output;
						    	},
			afterShow 		: function(inputElement)
								{
					        		$("#dtBox .dtpicker-compValue").attr('type', 'tel'); // set monbile input keyboard to numeric
								}
        });
	}

	$rootScope.changeChecklistItem = function(type, id, value, items)
	{
		//console.log(type, id, value, items);
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
		var data  	 = inspections.validateChecklist();
		// set general items
		data.date 			= $scope.inspection.date;
		data.impression 	= $scope.inspection.impression;
		data.attention	 	= $scope.inspection.attention;
		data.notes 			= $scope.inspection.notes;
		data.reminder_date 	= $scope.inspection.reminder_date;
		data.reminder 		= $scope.inspection.reminder;
		data.checklist_id   = $scope.checklist_id;

		data.hive_id = $routeParams.hiveId;
		console.log("saveInspection", data);
		if (data.valid === false)
		{
			var msg = '\'' + data.unfilled.join('\', \'') + '\' ' + $rootScope.lang['not_filled'];
			$scope.showError(null, {message:msg});
		}
		else if (data != null)
		{
			api.postApiRequest('saveInspection', 'inspections/store', data);
		}
			
	}
	
	$scope.showError = function(type, error)
	{
		var msg = typeof error.status !== 'undefined' ? "Status: "+error.status : typeof error.message !== 'undefined' ? error.message : '';
		$scope.error_msg = $rootScope.lang.empty_fields+" "+msg;
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
		var so = $scope.inspection;
		//console.log('editChecklist', so);
		if (so && (Object.keys(inspections.saveObject.items).length > 0 || so.impression != -1 || so.attention != -1 || so.notes != '' || so.reminder != '' || so.reminder_date != ''))
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
		var lastUsedChecklistId = api.getLocalStoreValue('open_checklist_id');
		var currentChecklistId  = $scope.checklist ? $scope.checklist.id : lastUsedChecklistId
		if (inspections.checklist == null || force)
		{
			$scope.setDateLanguage();
			$scope.selectChecklist(currentChecklistId, force);
		}
		else
		{
			//console.log('selected checklist id NOT NULL', id, force);
			$scope.checklistUpdated(null, null);
		}
	};
	
	$scope.checklistUpdated = function(e, type)
	{
		$scope.checklist = inspections.checklist;
		var id = $scope.checklist ? $scope.checklist.id : null
		
		if (id != null)
		{
			$scope.checklist_id 	= id;
			$scope.checklists 		= null;
			$scope.checklists 		= inspections.checklists;
		}

		if (typeof e != 'undefined' && e != null && typeof e.name != 'undefined' && e.name == 'localeChange')
			$scope.updateLists(true);

		if ($scope.memory != null)
		{
			if (typeof $scope.memory.update_inspection != 'undefined')
			{
				console.log('updateInspection from memorized flag');
				$scope.memory = null;
				$scope.updateInspection();
			}
			else if(typeof $scope.memory.init_inspection != 'undefined')
			{
				console.log('inspectionInit from memorized flag');
				$scope.memory = null;
				$scope.inspectionInit();
			}
		}
	};
	$scope.checklistHandler = $rootScope.$on('checklistUpdated', $scope.checklistUpdated);
	$scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.checklistUpdated);


	$scope.checklistsUpdated = function(e, type)
	{
		$scope.checklists = inspections.checklists;
		$scope.checklist  = inspections.checklist;
		$scope.updateLists(false);
		if ($routeParams.inspectionId)
		{
			$scope.inspection_id = $routeParams.inspectionId;
			inspections.loadRemoteInspection($routeParams.inspectionId);
		}
	};
	$scope.checklistsHandler = $rootScope.$on('checklistsUpdated', $scope.checklistsUpdated);


	$scope.selectChecklist = function(id, force=false, updateInspection=false)
	{
		// check if a checklist_id to load was in memory (from saveBeforeNavigate)
		
		if ($scope.memory != null && typeof $scope.memory.checklist_id != 'undefined')
		{
			id = $scope.memory.checklist_id;
			console.log('selectChecklist from memorized id ',id);
			$scope.memory = {init_inspection:true};
		}

		if ($scope.checklist && id == $scope.checklist.id && force==false)
		{
			console.log('DO NOT selectChecklist', id, force);
			return;
		}
		else
		{
			console.log('selectChecklist id', id, force);
		}
		$scope.checklist_id 	= id;
		inspections.loadChecklist(id);
	}

	$scope.updateInspection = function(data=null, init=false)
	{
		$scope.inspection = inspections.newSaveObject(data, init);
		$rootScope.inspection = $scope.inspection; // for beep-checklist-input.js directive
	}

	$scope.inspectionInit = function(e, type)
	{
		console.log('$scope.inspectionInit');
		$scope.inspection = null;
		$rootScope.inspection = null;
		$scope.updateInspection(null, true);
	}
	//$scope.saveInspectionLoadedHandler= $rootScope.$on('saveInspectionLoaded', $scope.inspectionInit);
	

	$scope.inspectionUpdate = function(e, data, init=false)
	{
		if ($scope.checklist_id != inspections.checklistId)
		{
			$scope.selectChecklist(inspections.checklistId, false);
			$scope.memory = {update_inspection:true};
		}
		else
		{	
			$scope.updateInspection(data, init);
		}

	};
	$scope.inspectionHandler = $rootScope.$on('inspectionUpdated', $scope.inspectionUpdate);


	$scope.loadHiveIndex = function(direction)
	{
		$scope.inspectionInit();

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
		$rootScope.hive = $scope.hive;

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

	$scope.saveAndGoCallback = function(callback)
	{
		$scope.saveInspectionHandler();
		$scope.saveInspectionHandler = $rootScope.$on('saveInspectionLoaded', callback);
		$scope.saveInspection();
	}

	$scope.saveBeforeNavigate = function(callback=null, memory=null)
	{
		$scope.memory = memory;
		var so = $scope.inspection;
		if (so && (Object.keys(inspections.saveObject.items).length > 0 || so.impression != -1 || so.attention != -1 || so.notes != '' || so.reminder != '' || so.reminder_date != ''))
		{
			console.log('saveBeforeNavigate items', Object.keys(inspections.saveObject.items).length, so);
			return $rootScope.showConfirm($rootScope.lang.save_input_first, $scope.saveAndGoCallback, callback, callback);
		}
		else
		{
			if (typeof callback == 'function')
				return callback();
		}
	}

	$scope.back = function()
	{
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else
		{
			// make sure that back goes to the last main screen in the history
			for (var i = $rootScope.history.length - 1; i >= 0; i--) 
			{
				var path = $rootScope.history[i];
				var go   = false;
				var hive_id = typeof $scope.hive != 'undefined' && $scope.hive != null  ? $scope.hive.id : '';
				
				// go if history item contains main menu item
				if ( (path.indexOf('/inspections') > -1 && path.indexOf('/inspections/') == -1) || path.indexOf('/locations') > -1 || (path.indexOf('/hives') > -1 && path.indexOf('/hives/'+hive_id) == -1 && path.indexOf('/inspect') == -1) || path.indexOf('/groups') > -1)
					go = true;

				if (go)
					return $location.path(path);
			}
			return $rootScope.historyBack();
		}
	};

	//close options dialog
	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	// remove references to the controller
    $scope.removeListeners = function()
    {
		//$scope.saveInspectionLoadedHandler();
		$scope.hivesHandler();
		$scope.groupsHandler();
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