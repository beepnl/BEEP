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
	$scope.addedUser 		= false;
	$scope.deletedUser 		= false;

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
				$scope.checkToken($routeParams.token, $routeParams.groupId);
			}
			else if ($routeParams.groupId != undefined || $location.path().indexOf('/groups/create') > -1)
			{
				$scope.success_msg = $routeParams.success;

				$scope.initGroups();
				if ($location.path().indexOf('/groups/create') > -1)
				{
					$scope.pageTitle = $rootScope.mobile ? $rootScope.lang.New + ' ' +$rootScope.lang.group : $rootScope.lang.create_new + ' ' +$rootScope.lang.group;
				}
				else
				{
					groups.loadRemoteGroups();
				}
			} 
			else
			{
				groups.loadRemoteGroups();
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
		
		if (groups.invitations.length > 0)
		{
			$scope.invitations = groups.invitations;
		}
		
		if ($location.path().indexOf('/groups/create') > -1)
		{
			$scope.group = {'creator':true, 'name':$rootScope.lang.Group+' '+($scope.groups.length+1) ,'color':'', 'description':'', 'hives_selected':[], 'hives_editable':[], 'users':[{'name':$rootScope.user.name, 'email':$rootScope.user.email, 'admin':true, 'creator':true, 'invited':null}]};
			//console.log($scope.group);
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

	$scope.checkToken = function(token, groupId)
	{
		$scope.redirect    = "/groups/"+groupId+"/edit";
		$scope.success_msg = $rootScope.lang.Invitation_accepted;
		api.postApiRequest('checkToken', 'groups/checktoken', {'group_id':groupId, 'token':token});
	}

	$scope.addGroupUser = function()
    {
        $scope.addedUser = true;
        $scope.group.users.push({'name':'', 'email':'', 'admin':false, 'creator':false});
    }

    $scope.removeGroupUserByIndex = function(i)
	{
		return typeof $scope.group.users[i] != 'undefined' ? $scope.group.users.splice(i,1) : null;
	}

    $scope.deleteGroupUser = function(userIndex)
    {
        var u = $scope.group.users[userIndex];
        
        if (typeof u.id == 'undefined')
            return $scope.removeGroupUserByIndex(userIndex);

        if (typeof u.delete == 'undefined')
            u.delete = true;
        else
            u.delete = u.delete ? false : true;

       	$scope.deletedUser = u.delete;
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
		//console.log(a,b);
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

	$scope.saveGroup = function()
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

	$scope.detachGroup = function()
	{
		var detach = false;
		$scope.redirect = "/groups";
		var i = 0;
		for(var id in $scope.group.users)
		{
			var user = $scope.group.users[id];
			if (user.id == $rootScope.user.id)
			{
				//console.log('detach user',user.id);
				$scope.removeGroupUserByIndex(i);
				detach = true;
				break;
			}
			i++;
		}
		if (detach)
		{
			var group = groups.getGroupById($routeParams.groupId);
			api.deleteApiRequest('detachGroup', 'groups/detach/'+$scope.group.id);
		}
	}

	$scope.confirmDetachGroup = function()
	{
		$scope.redirect = "/groups";
		$rootScope.showConfirm($rootScope.lang.Detach_from_group+'?', $scope.detachGroup);
	}


	$scope.deleteGroup = function()
	{
		$scope.redirect = "/groups";
		api.deleteApiRequest('deleteGroup', 'groups/'+$scope.group.id, $scope.group);
	}

	$scope.confirmDeleteGroup = function()
	{
		$rootScope.showConfirm($rootScope.lang.Remove_group+'?', $scope.deleteGroup);
	}

	$scope.groupsError = function(type, error)
	{
		$scope.error_msg = error.status == 422 ? "Error: "+convertOjectToArray(error.message).join(', ') : $rootScope.lang.empty_fields+'.';
	}

	$scope.groupsMessage = function(type, data)
	{
		//console.log(data);
		$scope.success_msg = data.message;
		$scope.error_msg   = null;
	}

	$scope.groupChanged = function(type, data)
	{
		if ($scope.redirect != null && ( typeof data.message == 'undefined' || (data.message == 'group_detached' || data.message == 'group_activated')) )
		{
			$location.path($scope.redirect);
			if ($scope.success_msg != null)
				$location.search('success', $scope.success_msg);

			$scope.success_msg = null;
			$scope.redirect    = null;
		}
	}

	$scope.groupsDetachHandler 	= $rootScope.$on('detachGroupLoaded', $scope.groupChanged);
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
		$scope.groupsDetachHandler();
		$scope.groupsDeleteError();
		$scope.groupsSaveError();
		$scope.groupsDeleteHandler();
		$scope.groupsSaveHandler();
		$scope.groupsTokenHandler();
		$scope.groupsHandler();
		$scope.hivesHandler();
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