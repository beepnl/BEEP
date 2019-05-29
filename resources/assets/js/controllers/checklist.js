/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('ChecklistCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, settings, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.Checklist;
	$scope.showMore 		= false; // multiple inspections
	$scope.inspections 		= null;

	$scope.checklist    	= null;
	$scope.checklists 		= null;
	$scope.checklist_id 	= null;

	$scope.hive 			= null;
	$scope.location 		= null;

	$scope.treeData 		= [];
	$scope.search 			= "";

	$scope.ac = function()
    {
        return false;
    }

    function treeError(error) 
    {
        console.log('JsTree: error from js tree - ' + angular.toJson(error));
    };

    function readyCB() 
    {
        //console.log('JsTree ready called');
    };

    function deselectNodeCB(node, selected, event) 
    {
        if (typeof $scope.checklist.required_ids != 'undefined' && $scope.checklist.required_ids.length > 0 && $scope.checklist.required_ids.indexOf(parseInt(selected.node.id)) > -1)
        {
        	console.log('Do not deselectNodeCB', selected.node.id);
        	$scope.treeInstance.jstree(true).select_node(selected.node);
        	$rootScope.showMessage($rootScope.lang.cannot_deselect);
        	return false;
        }

        $scope.treeInstance.jstree(true).deselect_node(selected.node.children_d);
    };
    
    function selectNodeCB(node, selected, event) 
    {
        $scope.treeInstance.jstree(true).select_node(selected.node.children_d);
    };

	function checkCallback(operation, node, node_parent, node_position, more) 
	{
        // operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
        // in case of 'rename_node' node_position is filled with the new node name
        if (operation === "move_node")
        {
          	if (node.parent === "#") // root item is dragged
          		return node_parent.id === "#"; // and dropped on a root node
          	else
          		return node_parent.id === node.parent;
        }
        return false;  //allow all other operations
    }

    function checkDraggable(nodes, e)
	{
		var drag = nodes[0].parents.length > 2 ? false : true;
		return drag;
	}

	$scope.applySearch = function ()
	{
        var to = false;
        if(to) {
           clearTimeout(to);
        }
        to = setTimeout(function () {
           if($scope.treeInstance) {
              $scope.treeInstance.jstree(true).search($scope.search);
           }
        }, 250);
     };

    $scope.treeConfig = 
	{
	    "core" 	  : { "check_callback":checkCallback, "error":treeError, "themes": { "variant":"large", "stripes":true } },
	    "plugins" : [ "search", "checkbox", "dnd" ],
	    "checkbox": { "cascade":"undetermined", "three_state":false, "cascade_to_hidden":true, "keep_selected_style":true},
	    "dnd"	  : { "check_while_dragging":true, "drag_selection":false, "touch":true, "copy":false, "use_html5":false },
	    "version" : 1
	};

	$scope.treeEventsObj = {
      'ready': readyCB,
      'select_node': selectNodeCB,
      'deselect_node': deselectNodeCB,
    }

	// sorting categories
	$scope.init = function()
	{
		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else
		{
			$scope.checklistsUpdated(); // only show name if multiple checklists

			if ($routeParams.checklistId == $scope.checklist_id)
				$scope.updateLists();
			else
				$scope.selectChecklist($routeParams.checklistId);

			console.log('checklist.js');
		}

	};

	$scope.saveChecklist = function()
	{
		console.log("saveChecklist");
		var check = {'name':$scope.checklist.name};
		var tree  = $scope.treeInstance.jstree(false).get_json(null, {"no_icon":true,"no_id":true,"no_data":true,"no_li_attr":true,"no_a_attr":true,"flat":true});
		var cats  = [];
		Object.entries(tree).forEach(([i, item]) => {
			if (item.state.selected && typeof item.state.cat != 'undefined')
				cats.push(item.state.cat);
		});
		if (cats.length > 0)
		{
			check.categories = cats.join(',');
			console.log(check);
			if ($scope.checklist_id == null)
				api.postApiRequest('saveChecklist', 'checklists', check);
			else
				api.patchApiRequest('saveChecklist', 'checklists/'+$scope.checklist.id, check);
		}
		else
		{
			$scope.showError(null, {'status':'No items selected'});
		}
	}
	
	$scope.showError = function(type, error)
	{
		$scope.error_msg = $rootScope.lang.empty_fields+". Status: "+error.status;
	}

	$scope.refreshAndGoHome = function()
	{
		inspections.loadChecklist($scope.checklist.id);
		$scope.back();
	};

	$scope.saveChecklistHandler 	  = $rootScope.$on('saveChecklistLoaded', $scope.refreshAndGoHome);
	$scope.saveChecklistErrorHandler = $rootScope.$on('saveChecklistError', $scope.showError);


    $scope.updateLists = function(force = false)
	{
		if (inspections.checklistTree == null || force)
		{
			var id = $scope.checklist ? $scope.checklist.id : null
			$scope.selectChecklist(id, force);
		}
		else
		{
			$scope.checklistUpdated(null, null);
		}
	};
	
	$scope.checklistUpdated = function(e, type)
	{
		$scope.checklist = inspections.checklistTree;
		$scope.treeData  = $scope.checklist.taxonomy;
		if ($scope.checklist)
			$scope.treeConfig.version++;

		if (typeof e != 'undefined' && e != null && typeof e.name != 'undefined' && e.name == 'localeChange')
			$scope.updateLists(true);
	};
	$scope.checklistHandler    = $rootScope.$on('checklistTreeUpdated', $scope.checklistUpdated);
	$scope.localeChangeHandler = $rootScope.$on('localeChange', $scope.checklistUpdated);

	$scope.checklistsUpdated = function(e, type)
	{
		$scope.checklists = inspections.checklists;
	};
	$scope.checklistsHandler = $rootScope.$on('checklistsUpdated', $scope.checklistsUpdated);


	$scope.selectChecklist = function(id, force=false)
	{
		if ($scope.checklist && id == $scope.checklist.id && force == false)
			return;
		
		console.log('selectChecklist', id);

		if ($routeParams.checklistId != id)
		{
			$location.path('/checklist/'+id+'/edit');
		}
		else
		{
			$scope.checklist_id = id;
			inspections.loadChecklistTree(id);
		}
	}

	
	$scope.back = function()
	{
		if ($rootScope.optionsDialog)
		{
			$rootScope.optionsDialog.close();
		}
		else if($routeParams.inspection_edit && $routeParams.hive_id)
		{
			$location.replace();
			$location.path('/hives/'+$routeParams.hive_id+'/inspections/'+$routeParams.inspection_edit).search({});
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
		$scope.saveChecklistHandler();
		$scope.saveChecklistErrorHandler();
		$scope.checklistHandler();
		$scope.checklistsHandler();
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