app.directive('beepUserSelector', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template:
         
          // Table row
            '<td>'+
              '<p ng-bind="index+1"></p>'+
            '</td>'+
            '<td>'+
              '<input class="form-control" ng-model="user.name" ng-disabled="user.id != null">'+
            '</td>'+
            '<td>'+
              '<input class="form-control" ng-model="user.email" ng-disabled="user.id != null">'+
            '</td>'+
            '<td>'+
              '<p ng-if="user.accepted == null">{{user.invited}}</p>'+
            '</td>'+
            '<td>'+
              '<p ng-if="!user.creator">{{lang.Admin}} <input type="checkbox" ng-model="user.admin" ng-disabled="user.delete" ng-checked="user.admin"></p>'+
              '<p ng-if="user.creator">{{lang.Creator}}</p>'+
            '</td>'+
            '<td>'+
              '<a ng-if="user.creator" ng-click="delete(index)" class="btn" ng-class="{\'btn-warning\':user.delete, \'btn-danger\':!user.delete}" title="{{user.delete ? lang.Undelete : lang.Delete}}"><i class="fa fa-trash"></i></a>'+
            '</td>',

      scope: {
        user: '=?',
        change: '=?',
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