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

  .when('/user/edit', {
        controller:'UserCtrl',
        templateUrl:'views/user.html'
  })

  // load
  .when('/load', {
      controller:'LoadCtrl',
      templateUrl:'views/loading.html'
  })


  // overview
  .when('/measurements/:sensorId',
  {
      controller  : 'MeasurementsCtrl',
      templateUrl : 'views/measurements.html?v=2',
  })

  .when('/measurements',
  {
      controller  : 'MeasurementsCtrl',
      templateUrl : 'views/measurements.html?v=2',
  })

   // locations
  // .when('/locations/:locationId/inspect',
  // {
  //     controller  : 'InspectionCreateCtrl',
  //     templateUrl : 'views/inspect.html',
  // })
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
      templateUrl : 'views/hive_edit.html?v=2',
  })
  .when('/hives/:hiveId/inspect',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : 'views/inspect.html?v=5',
  })
  .when('/hives/:hiveId/inspections/:inspectionId',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : 'views/inspect.html?v=5',
  })
  .when('/hives/:hiveId/inspections',
  {
      controller  : 'InspectionsCtrl',
      templateUrl : 'views/inspections.html?v=4',
  })
  .when('/hives/:hiveId/edit',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/hive_edit.html?v=2',
  })
  .when('/hives',
  {
      controller  : 'HivesCtrl',
      templateUrl : 'views/hives.html?v=3',
  })

  // groups
  .when('/groups',
  {
      controller  : 'GroupsCtrl',
      templateUrl : 'views/groups.html?v=2',
  })
  .when('/groups/create',
  {
      controller  : 'GroupsCtrl',
      templateUrl : 'views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/token/:token',
  {
      controller  : 'GroupsCtrl',
      templateUrl : 'views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/edit',
  {
      controller  : 'GroupsCtrl',
      templateUrl : 'views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/inspections',
  {
      controller  : 'InspectionsCtrl',
      templateUrl : 'views/inspections.html?v=4',
  })

  // checklist
  .when('/checklist/:checklistId/edit',
  {
      controller  : 'ChecklistCtrl',
      templateUrl : 'views/checklist.html',
  })

   // sensors
  .when('/sensors',
  {
      controller  : 'SensorsCtrl',
      templateUrl : 'views/sensors.html',
  })


  // settings
 .when('/settings', 
 {
        controller:'SettingsCtrl',
        templateUrl:'views/forms/settings.html?v=2'
 })

 .when('/export', 
 {
        controller:'ExportCtrl',
        templateUrl:'views/export.html'
 })

  // none...
  .otherwise(
  {
    redirectTo : '/load'
  });


}]);