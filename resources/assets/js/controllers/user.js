/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */
app.controller('UserCtrl', function($scope, $rootScope, $window, $location, $routeParams, api) 
{

	// set the title
	$rootScope.title  = $rootScope.lang.login_title;
	$scope.formStatus = '';
	$scope.message	  = null;
	$scope.error   	  = null;
	$scope.fields     = {};

	$scope.init = function()
	{
		// hide splash
		$rootScope.showSplash = false;
		
		// check if we're authenticated

		if(api.getApiToken() != null)
		{
			if ($location.path() != '/user/edit')
			{
				$location.path('/load');
			}
			else
			{
				$rootScope.title  = $rootScope.lang.User;
				$scope.setEditFields();
			}
		}

		// Check locale
		if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale)
		{
            $rootScope.switchLocale($routeParams.language);
			$location.search('language', null);
		}

		if ($routeParams.msg != undefined && $routeParams.msg != '')
		{
			$scope.message = 
			{
				show          : true,
				resultType    : 'success',
				resultMessage : $rootScope.lang[$routeParams.msg],
				verifyLink 	  : false
			};
		}

		if ($routeParams.email != undefined && $routeParams.email != '')
		{
			$scope.fields.login.email = $routeParams.email;
			$scope.fields.register.email = $routeParams.email;
		}

	};


	$scope.confirmDeleteUser = function()
	{
		$rootScope.showConfirm($rootScope.lang.Delete+' '+$rootScope.lang.user_data+'?', $scope.reallyConfirmDeleteUser);
	}
	$scope.reallyConfirmDeleteUser = function()
	{
		$rootScope.showConfirm($rootScope.lang.delete_complete_account, $scope.deleteUser);
	}

	$scope.deleteUser = function()
	{
		api.deleteApiRequest('deleteUser', 'user'); // delete myself
	}
	
	$scope.userDeleteLoadedHandler = $rootScope.$on('deleteUserLoaded', function(e, data)
	{
		$rootScope.doLogout(0);
	});

	$scope.setEditFields = function()
	{
		$scope.fields.edit = 
		{
			name					: $rootScope.user.name,
			email					: $rootScope.user.email,
			password 				: '',		
			password_confirmation	: '',
			policy_accepted 		: ($rootScope.user.policy_accepted == $rootScope.lang.policy_version)
		};
	}

	$scope.editUser = function()
	{
		// reset the errors
		$scope.formStatus = '';
		$scope.resetErrors();

		// set the errors
		var validate = $rootScope.validateFields($scope.fields.edit, $scope.edit, $scope.error);
		if(validate === true)
		{
			if ($scope.fields.edit.policy_accepted)
				$scope.fields.edit.policy_accepted = $rootScope.lang.policy_version;

			api.patchApiRequest('editUser', 'user', $scope.fields.edit);
		}
		else
		{
			$scope.message = validate;
		}
	};

	$scope.userEditLoadedHandler = $rootScope.$on('editUserLoaded', function(e, data)
	{
		$scope.formStatus = 'edited';
		api.handleAuthentication(data);
	});

	$scope.userUpdatedHandler 	 = $rootScope.$on('userUpdated', $scope.setEditFields);
	

	$scope.resetErrors = function()
	{
		$scope.message = 
		{
			show 	      : false,
			resultType    : 'error',
			resultMessage : '',
		};

		$scope.error = {
			email			: false,
			password 	    : false,
			password_retype : false,
		};	
	};

	$scope.resetErrors();




	$scope.fields.login = 
	{
		email    : '',
		password : '',
	};



	$scope.retreiveToken = function(e)
	{
		e.preventDefault();

		$scope.resetErrors();

		// check if errors
		var validate = $rootScope.validateFields($scope.fields.login, $scope.login, $scope.error);
		if(validate === true)
		{
			// data
			var input = $scope.fields.login;

			// go register the user
			api.login(input.email, input.password);
		}
		else
		{
			$scope.message = validate;
		}
	};



	$scope.fields.register = 
	{
		email			: '',
		password 		: '',		
		password_retype	: '',
		policy_accepted : ''
	};


	
	$scope.registerUser = function(e)
	{
		// prevent default
		e.preventDefault();

		// reset the errors
		$scope.resetErrors();

		// set the errors
		var validate = $rootScope.validateFields($scope.fields.register, $scope.register, $scope.error);
		if(validate === true)
		{
			if ($scope.fields.register.policy_accepted)
				$scope.fields.register.policy_accepted = $rootScope.lang.policy_version;

			// go register the user
			var input = $scope.fields.register;

			$rootScope.user = input;
			$rootScope.user.name = input.email;

			api.registerUser(input.password, input.email, input.policy_accepted);
		}
		else
		{
			$scope.message = validate;
		}
	};

	// Auth handlers
	$scope.sendVerificationEmail = function()
	{
		api.postApiRequest('verify', 'email/resend', $scope.fields.login);
	}

	$scope.authError = function(e, error)
	{
		// check email
		console.log(error);

		msg = error.message != undefined ? error.message : error;

		if (error.status == 503)
		{
			$scope.error.password 		 = false;
			$scope.error.password_retype = false;
			$scope.error.email 		     = false;
			msg = 'server_down';
		}

		// add a link
		var transMessage = $rootScope.lang[msg];
		var verifyOn 	 = false;
		var resultStyle  = 'error';

		if(msg.indexOf('email') !== -1)
			$scope.error.email = true;

		if(msg.indexOf('password') !== -1)
		{
			$scope.error.password 		 = true;
			$scope.error.password_retype = true;
		}

		// check password
		if(msg == 'no_password_match')
		{
			$scope.error.password 		 = false;
			$scope.error.password_retype = true;
		}	
		else if(msg == 'email_not_verified')
		{
			verifyOn 	= true;
		}
		else if(msg == 'email_verification_sent')
		{
			$scope.error.email= false;
			$scope.formStatus = 'registered';
			verifyOn 		  = true;
			resultStyle 	  = 'success';
		}
		

		// set the message
		$scope.message = 
		{
			show          : true,
			resultType    : resultStyle,
			resultMessage : transMessage,
			verifyLink 	  : verifyOn
		};

	};
	
	$scope.userAuthenticateHandler = $rootScope.$on('authenticateLoaded', function(e, data)
	{
		$location.path('/load');
	});

	$scope.userDeleteErrorHandler    	= $rootScope.$on('deleteUserError', $scope.authError);
	$scope.userEditErrorHandler    		= $rootScope.$on('editUserError', $scope.authError);
	$scope.userAuthenticateErrorHandler = $rootScope.$on('authenticateError', $scope.authError);
	$scope.userRegisteredErrorHandler 	= $rootScope.$on('registerError', $scope.authError);

	$scope.userRegisteredHandler = $rootScope.$on('registerLoaded', function(e, data)
	{
		var result = data;

		if(result.api_token != null)
			api.setApiToken(result.api_token);

		// set the status on registered
		$scope.formStatus = 'registered';
	});

	$scope.back = function()
	{
		$location.path('/login');
	};


	$scope.backListener = $rootScope.$on('backbutton', $scope.back);


	$scope.init();


	// remove the listeners
	$scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });



    // remove listeners
    $scope.removeListeners = function()
    {
    	$scope.userUpdatedHandler();
    	$scope.userAuthenticateHandler();
    	$scope.userAuthenticateErrorHandler();
		$scope.userRegisteredHandler();
    	$scope.userRegisteredErrorHandler();
    	$scope.userEditErrorHandler();
    	$scope.userEditLoadedHandler();
    	$scope.userDeleteLoadedHandler();
    	$scope.userDeleteErrorHandler();

    	$scope.backListener();
    };

});