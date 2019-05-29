/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Password recovery controller
 */
app.controller('PasswordCtrl', function($scope, $rootScope, $window, $location, $routeParams, api) 
{

	// set the title
	$rootScope.title = $rootScope.lang.password_recovery_title;


	$scope.formStatus = '';

	$scope.message = null;
	$scope.error   = null;
	$scope.fields  = {};


	$scope.resetErrors = function()
	{
		$scope.formStatus = '';
		$scope.message = 
		{
			show 	      : false,
			resultType    : 'error',
			resultMessage : '',
		};

		$scope.error = {
			email			 : false,
			password 	     : false,
			password_confirm : false,
			token            : false,
		};	
	};

	$scope.resetErrors();



	// reminder
	$scope.fields.reminder = 
	{
		email : '',
	};

	// reset
	$scope.fields.reset = 
	{
		email            : '',
		password 		 : '',
		password_confirm : '',
		token			 : '', 
	};


	$scope.init = function()
	{	
		// copy the emailadres from the reminder
		if(typeof api.cache.email != undefined)
			$scope.fields.reset.email = api.cache.email;

		// hide splash
		$rootScope.showSplash = false;
		
		// Set reset token
		if ($routeParams.token != undefined)
		{
			$scope.fields.reset.token = $routeParams.token;
		}

		if ($routeParams.email != undefined && $routeParams.email != '')
		{
			$scope.fields.reminder.email = $routeParams.email;
			$scope.fields.reset.email 	 = $routeParams.email;
		}
		
		// check if we're authenticated
		if (api.getApiToken() != null)
		{
			$location.path('/load');
		}
	};



	$scope.sendReminder = function(e)
	{
		e.preventDefault();

		$scope.resetErrors();

		// check if errors
		var validate = $rootScope.validateFields($scope.fields.reminder, $scope.reminder, $scope.error);
		if(validate === true)
		{
			// data
			var input = $scope.fields.reminder;

			// do the call
			api.passwordReminder(input.email);
		}
		else
		{
			$scope.message = validate;
		}
	};




	$scope.doReset = function(e)
	{
		e.preventDefault();

		$scope.resetErrors();

		// check if errors
		var validate = $rootScope.validateFields($scope.fields.reset, $scope.reset, $scope.error);
		if(validate === true)
		{
			// data
			var input = $scope.fields.reset;

			// do the call
			api.passwordReset(input.email, input.password, input.password_confirm, input.token);
		}
		else
		{
			$scope.message = validate;
		}
	};



	$scope.responseSuccess = function(e, data)
	{
		if(typeof data.message != 'undefined')
		{
			let msg = data.message;
			switch(msg)
			{
				case 'reminder_sent':
					$scope.formStatus = 'reminder_sent';
				  break;
			}
		}
		else
		{
			var result = data.data;
			if(result.api_token != null)
				api.setApiToken(result.api_token);

			// redirect to the main page
			$scope.formStatus = 'password_reset';
		}
	};



	$scope.responseError = function(e, err)
	{
		var message = null;
		var error   = $scope.error;

		switch(err.message)
		{
			case 'invalid_user':
				error.email = true;
				message     = $rootScope.lang.invalid_user;
			  break;

			case 'invalid_password':
				error.password = true;
				message		   = $rootScope.lang.invalid_password;				  
			  break;

			case 'invalid_token':
				error.token = true;
				message     = $rootScope.lang.invalid_token;
			  break;

			default:
				message = $rootScope.lang.server_error + ": "+err.status;
			  break;
		}

		// check for errors
		if(message != null)
		{
			$scope.message = 
			{
				show 	      : true,
				resultType    : 'error',
				resultMessage : message,
			};
		}
	};


	$scope.reminderHandler 		= $rootScope.$on('passwordReminderLoaded', $scope.responseSuccess);
	$scope.reminderErrorHandler = $rootScope.$on('passwordReminderError', $scope.responseError);
	$scope.resetHandler    		= $rootScope.$on('passwordResetLoaded', $scope.responseSuccess);
	$scope.resetErrorHandler    = $rootScope.$on('passwordResetError', $scope.responseError);



	$scope.back = function()
	{
		$location.path('/login');
	};


	$scope.init();


	// remove the listeners
	$scope.$on('$destroy', function() 
    {
        $scope.removeListeners();
    });



    // remove listeners
    $scope.removeListeners = function()
    {
    	$scope.reminderHandler();
    	$scope.reminderErrorHandler();
    	$scope.resetHandler();
    	$scope.resetErrorHandler();
    };

});