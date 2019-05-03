app.directive('yesNoRating', function() {
    return {
      restrict: 'EA',
      template:
        '<ul class="yes-no-rating" ng-class="{readonly: readonly}">' +
        '  <li class="option" ng-class="{filled: smile[1].filled}" ng-click="toggle(1)">' +
        '    <span><i class="fa fa-check-circle fa-2x face yes {{itemName}}"></i><p>{{yes}}</p></span>' + 
        '  </li>' +
        '  <li class="option" ng-class="{filled: smile[0].filled}" ng-click="toggle(0)">' +
        '    <span><i class="fa fa-times-circle fa-2x face no {{itemName}}"></i><p>{{no}}</p></span>' + 
        '  </li>' +
        '</ul>',
      scope: {
        ratingValue: '=ngModel',
        max: '=?', // optional (default is 3)
        onRatingSelect: '&?',
        readonly: '=?',
        yes: '=?',
        no: '=?',
        itemName: '=?',
      },
      link: function(scope, element, attributes) {
        if (scope.max == undefined) {
          scope.max = 1;
        }
        if (scope.readonly == undefined) {
          scope.readonly = false;
        }
        if (scope.yes == undefined) {
          scope.yes = '';
        }
        if (scope.no == undefined) {
          scope.no = '';
        }
        if (scope.itemName == undefined) {
          scope.itemName = '';
        }
        function updateStars() {
          scope.smile = [{filled: false},{filled: false}];
          for (var i = 0; i <= scope.max; i++) 
          {
            scope.smile.push({filled: false});
          }
          if (scope.smile[scope.ratingValue] != undefined)
            scope.smile[scope.ratingValue].filled = true;
        };
        scope.toggle = function(index) {
          if (scope.readonly == undefined || scope.readonly === false)
          {
            if (scope.ratingValue == index) // deselect
            {
              scope.ratingValue = -1;
            }
            else
            {
              scope.ratingValue = index;
            }
            if (typeof scope.onRatingSelect == 'function')
            {
              scope.onRatingSelect({
                rating: scope.ratingValue
              });
            }
          }
        };
        scope.$watch('ratingValue', function(oldValue, newValue) {
          if (oldValue > -1 || newValue === -1 || oldValue != newValue) {
            updateStars();
          }
        });
      }
    };
  }
);