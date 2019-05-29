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
      templateUrl:'/app/views/forms/logout.html'
  })

  .when('/login', {
      controller:'UserCtrl',
      templateUrl:'/app/views/forms/login.html?v=1'
  })

  // login/create
  .when('/login/create', {
        controller:'UserCtrl',
        templateUrl:'/app/views/forms/user/create.html'
  })

    // login/reminder
  .when('/login/reminder', {
        controller:'PasswordCtrl',
        templateUrl:'/app/views/forms/user/reminder.html'
  })

   // login/reset
  .when('/login/reset/:token', {
        controller:'PasswordCtrl',
        templateUrl:'/app/views/forms/user/reset.html'
  })
    // login/reset
  .when('/login/reset', {
        controller:'PasswordCtrl',
        templateUrl:'/app/views/forms/user/reset.html'
  })

  .when('/user/edit', {
        controller:'UserCtrl',
        templateUrl:'/app/views/user.html'
  })

  // load
  .when('/load', {
      controller:'LoadCtrl',
      templateUrl:'/app/views/loading.html'
  })


  // overview
  .when('/measurements/:sensorId',
  {
      controller  : 'MeasurementsCtrl',
      templateUrl : '/app/views/measurements.html?v=2',
  })

  .when('/measurements',
  {
      controller  : 'MeasurementsCtrl',
      templateUrl : '/app/views/measurements.html?v=2',
  })

   // locations
  // .when('/locations/:locationId/inspect',
  // {
  //     controller  : 'InspectionCreateCtrl',
  //     templateUrl : '/app/views/inspect.html',
  // })
  .when('/locations/:locationId/edit',
  {
      controller  : 'LocationsCtrl',
      templateUrl : '/app/views/location_edit.html',
  })
  .when('/locations/create',
  {
      controller  : 'LocationsCtrl',
      templateUrl : '/app/views/forms/location_create.html',
  })
  .when('/locations',
  {
      controller  : 'LocationsCtrl',
      templateUrl : '/app/views/locations.html',
  })

   // hives
  .when('/hives/create',
  {
      controller  : 'HivesCtrl',
      templateUrl : '/app/views/hive_edit.html?v=3',
  })
  .when('/hives/:hiveId/inspect',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : '/app/views/inspect.html?v=6',
  })
  .when('/hives/:hiveId/inspections/:inspectionId',
  {
      controller  : 'InspectionCreateCtrl',
      templateUrl : '/app/views/inspect.html?v=6',
  })
  .when('/hives/:hiveId/inspections',
  {
      controller  : 'InspectionsCtrl',
      templateUrl : '/app/views/inspections.html?v=4',
  })
  .when('/hives/:hiveId/edit',
  {
      controller  : 'HivesCtrl',
      templateUrl : '/app/views/hive_edit.html?v=3',
  })
  .when('/hives',
  {
      controller  : 'HivesCtrl',
      templateUrl : '/app/views/hives.html?v=3',
  })

  // groups
  .when('/groups',
  {
      controller  : 'GroupsCtrl',
      templateUrl : '/app/views/groups.html?v=2',
  })
  .when('/groups/create',
  {
      controller  : 'GroupsCtrl',
      templateUrl : '/app/views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/token/:token',
  {
      controller  : 'GroupsCtrl',
      templateUrl : '/app/views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/edit',
  {
      controller  : 'GroupsCtrl',
      templateUrl : '/app/views/group_edit.html?v=2',
  })
  .when('/groups/:groupId/inspections',
  {
      controller  : 'InspectionsCtrl',
      templateUrl : '/app/views/inspections.html?v=4',
  })

  // checklist
  .when('/checklist/:checklistId/edit',
  {
      controller  : 'ChecklistCtrl',
      templateUrl : '/app/views/checklist.html',
  })

   // sensors
  .when('/sensors',
  {
      controller  : 'SensorsCtrl',
      templateUrl : '/app/views/sensors.html',
  })


  // settings
 .when('/settings', 
 {
        controller:'SettingsCtrl',
        templateUrl:'/app/views/forms/settings.html?v=2'
 })

 .when('/export', 
 {
        controller:'ExportCtrl',
        templateUrl:'/app/views/export.html'
 })

  // none...
  .otherwise(
  {
    redirectTo : '/load'
  });


}]);