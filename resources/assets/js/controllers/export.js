/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * User controller
 */
app.controller('ExportCtrl', function($scope, $rootScope, $window, $location, $routeParams, api) 
{

	// set the title
	$rootScope.title  = $rootScope.lang.Data_export;
	$scope.message	  = null;
	$scope.error   	  = null;

	$scope.init = function()
	{
		// Check locale
		if ($routeParams.language != undefined && $routeParams.language != $rootScope.locale)
		{
            $rootScope.switchLocale($routeParams.language);
			$location.search('language', null);
		}

	};



	$scope.exportData = function()
	{
		api.getApiRequest('export', 'export');
	}
	
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
    	$scope.backListener();
    };

});