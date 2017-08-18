/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */
app.controller('UserCtrl', function($scope, $rootScope, $window, $location, $routeParams, api) 
{

	// set the title
	$rootScope.title = $rootScope.lang.login_title;

	$scope.init = function()
	{
		// hide splash
		$rootScope.showSplash = false;
		
		// check if we're authenticated
		if(api.getApiToken() != null)
		{
			$location.path('/load');
		}

		// Check locale
		if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale)
		{
            $rootScope.switchLocale($routeParams.language);
			$location.search('language', null);
		}

	};


	$scope.formStatus = '';
	$scope.message	  = null;
	$scope.error   	  = null;
	$scope.fields     = {};



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
			// go register the user
			var input = $scope.fields.register;

			api.registerUser(input.password, input.email);
		}
		else
		{
			$scope.message = validate;
		}
	};

	// Auth handlers

	$scope.authError = function(e, error)
	{
		// check email
		console.log(error);

		msg = error.message != undefined ? error.message : error;

		if(msg.indexOf('email') !== -1)
			$scope.error.email = true;

		// check password
		if(msg.indexOf('password') !== -1)
		{
			$scope.error.password 		 = true;
			$scope.error.password_retype = true;
		}

		// set the message
		$scope.message = 
		{
			show          : true,
			resultType    : 'error',
			resultMessage : $rootScope.lang[msg],
		};
	};

	$scope.userAuthenticateHandler = $rootScope.$on('authenticateLoaded', function(e, data)
	{
		$location.path('/load');
	});

	$scope.userAuthenticateErrorHandler = $rootScope.$on('authenticateError', $scope.authError);
	$scope.userRegisteredErrorHandler = $rootScope.$on('registerError', $scope.authError);

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
    	$scope.userAuthenticateHandler();
    	$scope.userAuthenticateErrorHandler();
		$scope.userRegisteredHandler();
    	$scope.userRegisteredErrorHandler();

    	$scope.backListener();
    };

});