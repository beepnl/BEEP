app.directive('beepSensorDefinition', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template:
         
          // Table row
            '<td>'+
              '<input class="form-control" ng-model="def.name" ng-disabled="def.delete">'+
            '</td>'+
            '<td>'+
              '<div ng-model="def.inside" readonly="false" yes="lang.yes" no="lang.no" yes-no-rating>'+
            '</td>'+
            '<td>'+
              '<input class="form-control" type="number" ng-model="def.offset" ng-disabled="def.delete">'+
            '</td>'+
            '<td>'+
              '<input class="form-control" type="number" ng-model="def.multiplier" ng-disabled="def.delete">'+
            '</td>'+
            '<td>'+
              '<select ng-change="changein(index, def.input_measurement)" ng-model="def.input_measurement" ng-options="item.id as item.abbreviation for item in meas | orderBy:\'abbreviation\' track by item.id" class="form-control" ng-disabled="def.delete">'+
              '<option value="">{{lang.Select}} {{lang.measurement}}...</option>'+
              '</select>'+
            '</td>'+
            '<td>'+
              '<select ng-change="changeout(index, def.output_measurement)" ng-model="def.output_measurement" ng-options="item.id as item.abbreviation for item in meas | orderBy:\'abbreviation\' track by item.id" class="form-control" ng-disabled="def.delete">'+
              '<option value="">{{lang.Select}} {{lang.measurement}}...</option>'+
              '</select>'+
            '</td>'+
            '<td>'+
              '<a ng-click="save(index)" class="btn" ng-class="{\'btn-danger\':def.delete, \'btn-primary\':!def.delete}" title="{{ def.delete ? lang.Delete : lang.save }}"><i class="fa fa-save"></i></a>'+
              '<a ng-click="delete(index)" class="btn pull-right" ng-class="{\'btn-warning\':def.delete, \'btn-danger\':!def.delete}" title="{{ def.delete ? lang.Undelete : lang.Delete }}"><i class="fa fa-trash"></i></a>'+
            '</td>',

      scope: {
        meas: '=?',
        def: '=?',
        changein: '=?',
        changeout: '=?',
        delete: '=?',
        save: '=?',
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