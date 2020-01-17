app.directive('checklistFieldset', ['$rootScope', function($rootScope) {
    return {
        restrict: 'EA',
        scope: {
            cat: '=',
            cols: '=',
        },
        link: function (scope, iElement, iAttrs) 
        {
          scope.lang   = $rootScope.lang;
          scope.locale = $rootScope.locale;
          scope.hive   = $rootScope.hive;
          scope.colony_size = 0;

          scope.calculateTpaColonySize = function() 
          { 
            //console.log(scope.cat.children);
            for (var i = scope.cat.children.length - 1; i >= 0; i--) 
            {
                var child = scope.cat.children[i];
                var pixelsTotal = 0;
                var pixelsBees  = 0;

                if (child.name == 'pixels_with_bees')
                    pixelsBees = child.value;
                else if (child.name == 'pixels_total_top')
                    pixelsTotal = child.value;
            }
            if (pixelsTotal == 0)
                scope.colony_size = 0;
            else
                scope.colony_size = Math.round( (pixelsBees / pixelsTotal) * parseFloat(scope.hive.scope.fr_width_cm) * parseFloat(scope.hive.fr_height_cm) * scope.hive.frames );
          };
        },
        templateUrl: '/app/views/forms/checklist_fieldset.html'
    };
}]);
