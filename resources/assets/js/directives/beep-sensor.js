app.directive('beepSensor', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template:
         
          // Table row
            '<td>'+
              '<p ng-bind="index+1"></p>'+
            '</td>'+
            '<td>'+
              '<input class="form-control" ng-model="sensor.name" ng-disabled="sensor.delete">'+
            '</td>'+
            '<td>'+
              '<input class="form-control" ng-model="sensor.key" ng-disabled="sensor.delete">'+
            '</td>'+
            '<td>'+
              '<select ng-change="changetype(index, sensor.selected_type.name)" ng-model="sensor.selected_type" ng-options="item as item.trans[locale] for item in sensortypes | orderBy:transSort track by item.name" class="form-control" ng-disabled="sensor.delete">'+
              '<option value="">{{lang.Select}} {{lang.type}}...</option>'+
              '</select>'+
            '</td>'+
            '<td>'+
              '<p class="hive-name-mobile" ng-bind="sensor.hive.name"></p>'+
              '<p class="location notes" ng-bind="sensor.hive.location"></p>'+
            '</td>'+
            '<td>'+
              '<select ng-change="change(index, sensor.selected_hive_id)" ng-model="sensor.selected_hive_id" ng-options="item.id as item.name group by item.location for item in hives | orderBy:\'name\' track by item.id" class="form-control" ng-disabled="sensor.delete">'+
              '<option value="">{{lang.Select}} {{lang.hive}}...</option>'+
              '</select>'+
            '</td>'+
            '<td>'+
              '<a ng-click="delete(index)" class="btn" ng-class="{\'btn-danger\':sensor.delete, \'btn-warning\':!sensor.delete}" title="{{sensor.delete ? lang.Undelete : lang.Delete}}"><i class="fa fa-trash"></i></a>'+
            '</td>',

      scope: {
        hives: '=?', // show location name
        sensortypes: '=?',
        sensor: '=?',
        change: '=?',
        changetype: '=?',
        delete: '=?',
        index: '=?'
      },
      link: function(scope, element, attributes) {
        scope.locale = $rootScope.locale;
        scope.lang   = $rootScope.lang;
        scope.mobile = $rootScope.mobile;
      }
    };
  }
]);