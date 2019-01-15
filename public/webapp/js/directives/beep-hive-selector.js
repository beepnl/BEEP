app.directive('beepHiveSelector', ['$rootScope', function($rootScope) {
    return {
      restrict: 'EA',
      template: '<div class="hive-container small">'+
                  '<a ng-click="selecthive(hive)" title="{{ hive.name }} - {{ hive.location}}">'+
                    '<div class="hive small">'+
                      '<p class="lid" style="width: {{width}}px;"></p>'+
                      '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{width}}px;"></p>'+
                      '<p class="bottom" style="width: {{width}}px;"></p>'+
                    '</div>'+
                    '<div ng-if="selectedids.indexOf(hive.id) > -1" class="select-icon">'+
                      '<i class="fa fa-2x fa-eye"></i>'+
                    '</div>'+
                    '<div ng-if="editableids.indexOf(hive.id) > -1" class="select-icon edit">'+
                      '<i class="fa fa-2x fa-pencil"></i>'+
                    '</div>'+
                    '<p class="title" ng-class="{\'selected\':selectedids.indexOf(scope.hive.id) > -1}">{{ hive.name }}</p>'+
                  '</a>'+
                '</div>',
           
      scope: {
        hive: '=?',
        selecthive: '=?',
        selectedids: '=?',
        editableids: '=?'
      },
      link: function(scope, element, attributes) {
        scope.lang   = $rootScope.lang;
        scope.mobile = $rootScope.mobile;
        scope.width  = 15 + scope.hive.frames * 3;
      }
    };
  }
]);