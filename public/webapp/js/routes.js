/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */


app.config(['$routeProvider', '$locationProvider',  function($routeProvider) 
{
	
  $routeProvider

  // logout
  .when('/logout', {
      controller:'UserCtrl',
      templateUrl:'views/forms/logout.html'
  })

  .when('/login', {
      controller:'UserCtrl',
      templateUrl:'views/forms/login.html'
  })

  // login/create
  .when('/login/create', {
        controller:'UserCtrl',
        templateUrl:'views/forms/user/create.html'
  })

    // login/reminder
  .when('/login/reminder', {
        controller:'PasswordCtrl',
        templateUrl:'views/forms/user/reminder.html'
  })

   // login/reset
  .when('/login/reset/:token', {
        controller:'PasswordCtrl',
        templateUrl:'views/forms/user/reset.html'
  })
    // login/reset
  .when('/login/reset', {
        controller:'PasswordCtrl',
        templateUrl:'views/forms/user/reset.html'
  })

  // load
  .when('/load', {
      controller:'LoadCtrl',
      templateUrl:'views/loading.html'
  })


  // overview
  .when('/sensors_old',
  {
      controller  : 'SensorsOldCtrl',
      templateUrl : 'views/sensors_old.html',
  })

   // locations
  .when('/locations/:locationId/inspect',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : 'views/inspect.html',
  })
  .when('/locations/:locationId/edit',
  {
      controller  : 'LocationsCtrl',
      templateUrl : 'views/location_edit.html',
  })
  .when('/locations/create',
  {
      controller  : 'LocationsCtrl',
      templateUrl : 'views/forms/location_create.html',
  })
  .when('/locations',
  {
      controller  : 'LocationsCtrl',
      templateUrl : 'views/locations.html',
  })

   // hives
  .when('/hives/create',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/hive_edit.html',
  })
  .when('/hives/:hiveId/inspect',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : 'views/inspect.html',
  })
  .when('/hives/:hiveId/inspections',
  {
      controller  : 'InspectionsCtrl',
      templateUrl : 'views/inspections.html',
  })
  .when('/hives/:hiveId/edit',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/hive_edit.html',
  })
  .when('/hives',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/hives.html',
  })

   // sensors
  .when('/sensors',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/sensors_na.html',
  })


  // settings
 .when('/settings', 
 {
        controller:'SettingsCtrl',
        templateUrl:'views/forms/settings.html'
 })


  // none...
  .otherwise(
  {
    redirectTo : '/load'
  });


}]);