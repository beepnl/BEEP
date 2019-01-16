/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Dashboard controller
 */
app.controller('GroupsCtrl', function($scope, $rootScope, $window, $location, $filter, $routeParams, groups, api, moment, hives, inspections) 
{

	$rootScope.title    	= $rootScope.lang.groups_title;
	$scope.pageTitle       = '';
	$scope.showMore 		= false; // multiple groups
	$scope.redirect 		= null;
	$scope.hives 			= [];
	$scope.groups 			= [];
	$scope.hive 			= null;
	$scope.locations 		= null;
	$scope.error_msg 		= null;
	$scope.success_msg 		= null;
	$scope.selectedGroupIndex= 0;
	$scope.orderName        = 'name';
	$scope.orderDirection   = false;

	$scope.init = function()
	{

		if(api.getApiToken() == null)
		{
			$location.path('/login');
		}
		else if ($location.path().indexOf('/groups') > -1)
		{
			if ($routeParams.token != undefined && $location.path().indexOf('/groups/') > -1 && $location.path().indexOf('/token/') > -1)
			{
				$rootScope.title = $rootScope.lang.Invitation_accepted;
				$scope.redirect  = "/groups/"+$routeParams.groupId;
				api.postApiRequest('checkToken', 'groups/checktoken', {'group_id':$routeParams.groupId, 'token':$routeParams.token});
			} 
			if ($routeParams.groupId != undefined || $location.path().indexOf('/groups/create') > -1)
			{
				$scope.initGroups();
				$rootScope.title = $rootScope.lang.Group;
				if ($location.path().indexOf('/groups/create') > -1)
				{
					$scope.pageTitle = $rootScope.mobile ? $rootScope.lang.New + ' ' +$rootScope.lang.group : $rootScope.lang.create_new + ' ' +$rootScope.lang.group;
				}
			} 
			else
			{
				if (groups.groups.length > 0)
				{
					$scope.initGroups();
				}
				else
				{
					$location.path('/groups');
				}
			}
		}

	};

	$scope.initGroups = function()
	{
		$scope.hivesUpdate();
		
		if (groups.groups.length > 0)
		{
			$scope.groups = groups.groups;
		}
		$scope.showMore = $scope.groups.length > 1 ? true : false;
		
		
		if ($location.path().indexOf('/groups/create') > -1)
		{
			$scope.group = {'name':$rootScope.lang.Group+' '+($scope.groups.length+1) ,'color':'', 'description':'', 'hives_selected':[], 'hives_editable':[], 'users':[{'name':$rootScope.user.name, 'email':$rootScope.user.email, 'admin':1, 'creator':1, 'invited':null}]};
			//console.log($scope.hive);
		}
		else
		{
			$scope.loadGroupIndex();
		}
	}


	$scope.toggleGroup = function(group)
	{
		groups.toggle_open_group(group.id);
	}

	$scope.addGroupUser = function()
    {
        $scope.group.users.push({'name':$rootScope.lang.Member+' '+($scope.group.users.length+1), 'email':'', 'admin':0, 'creator':0});
    }

    $scope.deleteGroupUser = function(userIndex)
    {
        var u = $scope.group.users[userIndex];
        if (typeof u.delete == 'undefined')
            u.delete = true;
        else
            u.delete = u.delete ? false : true;
    }


	$scope.selectGroupHive = function(hive)
	{
		if (typeof $scope.group == 'undefined')
			return;

		var hive_id = hive.id;

		if (typeof $scope.group.hives_selected == 'undefined')
			$scope.group.hives_selected = [];

		if (typeof $scope.group.hives_editable == 'undefined')
			$scope.group.hives_editable = [];


		var selected_ind = $scope.group.hives_selected.indexOf(hive_id);
		var editable_ind = $scope.group.hives_editable.indexOf(hive_id);

		if (selected_ind == -1)
		{
			$scope.group.hives_selected.push(hive_id);
		}
		else if(editable_ind == -1)
		{
			$scope.group.hives_editable.push(hive_id);
		}
		else if (selected_ind > -1 && editable_ind > -1)
		{
			$scope.group.hives_selected.splice(selected_ind, 1);
			$scope.group.hives_editable.splice(editable_ind, 1);
		}
		//console.log(hive_id, $scope.group.hives_selected, $scope.group.hives_editable)
	}


	$scope.groupsUpdate = function(e, type)
	{
		$scope.initGroups();
	};

	$scope.hivesUpdate = function(e, type)
	{
		$scope.locations = hives.locations;
	};


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
		$scope.orderName = name;
	}

	$scope.natSort = function(a, b) 
	{
    	//console.log($scope.orderName, a.value, b.value);
    	return naturalSort(a.value,b.value);
	};

	$scope.transSort = function(a) 
	{
    	var locale = $rootScope.locale;
    	return a.trans[locale];
	};


	$scope.loadGroupIndex = function()
	{
		$scope.group	= groups.getGroupById($routeParams.groupId);

		if ($scope.group != undefined && ($location.path().indexOf('/groups/create') > -1 || $location.path().indexOf('/edit') > -1))
		{
			$scope.pageTitle = $scope.group.name;
		}
	}

	$scope.saveGroup = function(back)
	{
		var postGroup = {'name':$scope.group.name, 'description':$scope.group.description, 'hex_color':$scope.group.hex_color, 'hives_selected':$scope.group.hives_selected, 'hives_editable':$scope.group.hives_editable, 'users':$scope.group.users};
		if ($location.path().indexOf('/groups/create') > -1)
		{
			api.postApiRequest('saveGroup', 'groups', postGroup);
		}
		else
		{
			api.patchApiRequest('saveGroup', 'groups/'+$scope.group.id, postGroup);
		}
		$scope.redirect = "/groups";
	}

	
	$scope.deleteGroup = function()
	{
		$scope.redirect = "/groups";
		api.deleteApiRequest('deleteGroup', 'groups/'+$scope.group.id, $scope.group);
	}

	$scope.confirmDeleteGroup = function()
	{
		$rootScope.showConfirm($rootScope.lang.remove_hive+'?', $scope.deleteGroup);
	}

	$scope.groupsError = function(type, error)
	{
		$scope.error_msg = $rootScope.lang.empty_fields + (error.status == 422 ? ". Error: "+convertOjectToArray(error.message).join(', ') : '');
	}

	$scope.groupsMessage = function(type, data)
	{
		//console.log(data);
		$scope.success_msg = data.message;
	}

	$scope.groupChanged = function(type, data)
	{
		if ($scope.redirect != null && typeof data.message == 'undefined')
		{
			$location.path($scope.redirect);
			$scope.redirect = null;
		}
	}

	$scope.groupsDeleteError 	= $rootScope.$on('deleteGroupError', $scope.groupsError);
	$scope.groupsSaveError 		= $rootScope.$on('saveGroupError', $scope.groupsError);
	$scope.groupsDeleteHandler 	= $rootScope.$on('deleteGroupLoaded', $scope.groupChanged);
	$scope.groupsSaveHandler 	= $rootScope.$on('saveGroupLoaded', $scope.groupChanged);
	$scope.groupsTokenHandler 	= $rootScope.$on('checkTokenLoaded', $scope.groupChanged);
	$scope.groupsHandler 		= $rootScope.$on('groupsUpdated', $scope.groupsUpdate);
	$scope.hivesHandler 		= $rootScope.$on('hivesUpdated', $scope.hivesUpdate);
	$scope.groupsErrorHandler 	= $rootScope.$on('groupsError', $scope.groupsError);
	$scope.groupsMessageHandler = $rootScope.$on('groupsMessage', $scope.groupsMessage);

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
		$scope.groupsDeleteError();
		$scope.groupsSaveError();
		$scope.groupsDeleteHandler();
		$scope.groupsSaveHandler();
		$scope.groupsHandler();
		$scope.groupsErrorHandler();
		$scope.groupsMessageHandler();
		$scope.backListener();
    };
    

    $scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });

    // call the init function
	$scope.init();
});