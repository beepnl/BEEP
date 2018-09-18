app.directive('smileRating', function() {
    return {
      restrict: 'EA',
      require: 'ngModel',
      template:
        '<ul class="smile-rating" ng-class="{readonly: readonly}">' +
        '  <li class="fa-stack smile" ng-class="{filled: smile[0].filled}" ng-click="toggle(0)">' +
        '    <i class="fa fa-circle frown fa-stack-2x"></i>' + 
        '    <i class="fa fa-frown-o fa-stack-2x face"></i>' + 
        '  </li>' +
        '  <li class="fa-stack smile" ng-class="{filled: smile[1].filled}" ng-click="toggle(1)">' +
        '    <i class="fa fa-circle meh fa-stack-2x"></i>' + 
        '    <i class="fa fa-meh-o fa-stack-2x face"></i>' + 
        '  </li>' +
        '  <li class="fa-stack smile" ng-class="{filled: smile[2].filled}" ng-click="toggle(2)">' +
        '    <i class="fa fa-circle smile fa-stack-2x"></i>' + 
        '    <i class="fa fa-smile-o fa-stack-2x face"></i>' + 
        '  </li>' +
        '</ul>',
      scope: {
        ratingValue: '=ngModel',
        max: '=?', // optional (default is 3)
        onRatingSelect: '&?',
        readonly: '=?'
      },
      link: function(scope, element, attributes) {
        if (scope.max == undefined) {
          scope.max = 3;
        }
        function updateStars() {
          scope.smile = [];
          for (var i = 0; i < scope.max; i++) 
          {
            scope.smile.push({
              filled: false
            });
          }
          if (scope.smile[scope.ratingValue-1] != undefined)
            scope.smile[scope.ratingValue-1].filled = true;
        };
        scope.toggle = function(index) {
          if (scope.readonly == undefined || scope.readonly === false)
          {
            if (scope.ratingValue == index + 1) // deselect
            {
              scope.ratingValue = -1;
            }
            else
            {
              scope.ratingValue = index + 1;
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
          if (oldValue || newValue === 0) {
            updateStars();
          }
        });
      }
    };
  }
);