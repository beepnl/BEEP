app.directive('checklistFieldset', ['$rootScope', function($rootScope) {
    return {
        restrict: 'EA',
        scope: {
            cat: '=',
            cols: '=',
        },
        link: function (scope, iElement, iAttrs) {
          scope.lang = $rootScope.lang;
          scope.locale = $rootScope.locale;
        },
        templateUrl: '/app/views/forms/checklist_fieldset.html'
    };
}]);
