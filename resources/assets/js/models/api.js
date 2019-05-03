/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * API model
 */
app.service('api', ['$http', '$rootScope', function($http, $rootScope)
{

	var self    	   = this;
	
	this.token = null;

	this.reset = function()
	{
		// api token
		self.removeApiToken();
	}
	$rootScope.$on('reset', self.reset);

	this.getApiToken = function()
	{
		// get the api token
		if(localStorage.getItem('bee_api_token'))
		{
			var checkToken = self.token == null ? true : false;

			self.token = localStorage.getItem('bee_api_token');
			
			if (checkToken)
				self.checkAuthentication();

		}
		return self.token;
	};


	this.setApiToken = function(token)
	{
		// set the api token
		if(token != null)
		{
			localStorage.setItem('bee_api_token', token);
			self.token = token;
		}
	};

	this.setLocalStoreValue = function(name, value)
	{
		if (typeof name != 'undefined' && name != null && typeof value != 'undefined' && value != null)
		{
			//console.log('setLocalStoreValue', name, value);
			localStorage.setItem(name, value);
		}
	}

	this.getLocalStoreValue = function(name)
	{
		if(localStorage.getItem(name))
		{
			var value = localStorage.getItem(name);
			//console.log('getLocalStoreValue', name, value);
			return value;
		}
		return null;
	}


	this.removeApiToken = function()
	{
		// remove from the storage
		localStorage.removeItem('bee_api_token');
		// remove from memory
		self.token = null;
	};

	this.registerUser = function(password, email, policy_accepted)
	{
		var data = 
		{
			password	  : password,
			email		  : email,
			policy_accepted: policy_accepted
		};

		self.postApiRequest('register', 'register', data);
	};


	this.checkAuthentication = function()
	{
		self.postApiRequest('checkAuthentication', 'authenticate');
	};


	this.login = function(email, password)
	{
		var credentials = 
		{
			email 	 : email,
			password : password
		};

		self.postApiRequest('authenticate', 'login', credentials);
	};

	this.cache = {};

	this.passwordReminder = function(email)
	{
		self.cache.email = email;

		self.postApiRequest('passwordReminder', 'user/reminder', {email : email});
	};


	this.passwordReset = function(email, password, password_confirm, token)
	{
		var credentials = 
		{
			email 			 : email,
			password 		 : password,
			password_confirm : password_confirm,
			token 			 : token
		};

		self.postApiRequest('passwordReset', 'user/reset', credentials);
	};

	this.handleAuthentication = function(result)
	{
		$rootScope.user = result;
		$rootScope.user.img = API_URL + "../uploads/avatars/" + result.avatar;

		// token
		if(result.api_token != null)
			self.setApiToken(result.api_token);

		$rootScope.$broadcast('userUpdated');

	};

	this.handleAuthenticationError = function(error)
	{
		self.reAuthenticate();
	};

	this.reAuthenticate = function()
	{
		if (self.token != null)
		{
			$rootScope.showMessage($rootScope.lang.no_valid_authentication, null, $rootScope.lang.login_title);
			$rootScope.doLogout(); // will broadcast reset
		}
	}


	this.handleResponses = function(type, result)
	{
		console.info(type, (result != undefined ? typeof result == 'object' ? result.length == undefined ? Object.keys(result).length : result.length : '' : ''));
		$rootScope.$broadcast(type, result);
		
		switch(type)
		{
			case "authenticateLoaded":
			case "checkAuthenticationLoaded":
				self.handleAuthentication(result);
				break;
			case "authenticateError":
			case "checkAuthenticationError":
				self.handleAuthenticationError(result);
				break;
		}

	}


	this.deleteApiRequest = function(type, request, data, params)
	{
		self.postApiRequest(type, request, data, params, 'DELETE');
	};


	this.putApiRequest = function(type, request, data, params)
	{
		self.postApiRequest(type, request, data, params, 'PUT');
	};

	this.patchApiRequest = function(type, request, data, params)
	{
		self.postApiRequest(type, request, data, params, 'PATCH');
	};

	this.postApiRequest = function(type, request, data, params, method)
	{
		var params = typeof params !== 'undefined' ? params+'&' : '';
		var method = typeof method !== 'undefined' ? method : 'POST';

		var url    = API_URL+request
		url += params == '' ? '' : '?'+params;

		// set the request
		var req = 
		{
			method  : method,
			headers : 
			{
    			'Content-Type'  : 'application/json',
 			},
 			data : data,
			url  : url,
		};

		// if (method == 'PUT' || 'PATCH')
		// {
		// 	req.headers['X-HTTP-Method-Override'] = method;
		// 	req.method = 'POST';
		// }


		// check if it has to be authorized
		if(type != 'authenticate' && type != 'register' && self.getApiToken() != null)
		{
			req.headers['Authorization'] = 'Bearer '+self.getApiToken()+'';
		}
		req.headers['Accept-Language'] = $rootScope.locale;

		// do the request
		self.doApiRequest(type, req);
	};


	this.getApiRequest = function(type, request, params)
	{
		var params = typeof params !== 'undefined' ? params+'&' : '';
		// var count  = (typeof count !== 'undefined') ? count : 0;
		// var offset = (typeof offset !== 'undefined') ? offset : 0;
		var url    = API_URL+request+'?'+params+'';

		// set the request
		var req = 
		{
			method  : 'GET',
			headers : 
			{
    			'Content-Type'  : 'application/json',
 			},
			url  : url,
		};

		// check if it has to be authorized
		if(type != 'register' && self.getApiToken() != null)
		{
			req.headers['Authorization'] = 'Bearer '+self.getApiToken()+'';
		}
		req.headers['Accept-Language'] = $rootScope.locale;
		
		// do the request
		self.doApiRequest(type, req);
	}



	this.doApiRequest = function(type, req)
	{
		// start loading
		$rootScope.$broadcast('startLoading');

		// set a request timeout
		//req.timeout = (PING_FREQ_CONNECTED-1000);

		// do the request
		$http(req).then(
			function(response) // success
			{
				// set the data
				var result = (response.data != undefined) ? response.data : response;

				// set the listeners
				self.handleResponses(type+'Loaded', result);
				$rootScope.$broadcast('endLoading');
			}
			, function(response) // error
			{
				var error = (typeof response != 'undefined') ? (typeof response.data != 'undefined') ? (typeof response.data.errors != 'undefined') ? response.data.errors : (typeof response.data.message != 'undefined') ? response.data.message : response.data : response : 'error';
				var status= (typeof response != 'undefined') ? response.status : 0;
				// set the listeners
				self.handleResponses(type+'Error', {'message':error, 'status':status});
				$rootScope.$broadcast('endLoading');

				if(status == 401 || (type == 'checkAuthentication' && status == 302)) // re-authenticate
				{
					self.reAuthenticate();
				}
			}
		);	
		
	};


	self.getApiToken();
}]);