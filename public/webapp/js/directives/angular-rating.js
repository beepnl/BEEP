app.directive('starRating', function() {
    return {
      restrict: 'EA',
      require: 'ngModel',
      template:
        '<ul class="star-rating" ng-class="{readonly: readonly}">' +
        '  <li ng-repeat="star in stars" class="star" ng-class="{filled: star.filled}" ng-click="toggle($index)">' +
        '    <i class="fa fa-2x fa-star"></i>' + // or &#9733
        '  </li>' +
        '</ul>',
      scope: {
        ratingValue: '=ngModel',
        max: '=?', // optional (default is 5)
        onRatingSelect: '&?',
        readonly: '=?'
      },
      link: function(scope, element, attributes) {
        if (scope.max == undefined) {
          scope.max = 5;
        }
        function updateStars() {
          scope.stars = [];
          for (var i = 0; i < scope.max; i++) {
            scope.stars.push({
              filled: (i < scope.ratingValue)
            });
          }
          if (scope.stars[scope.ratingValue-1] != undefined)
            scope.stars[scope.ratingValue-1].filled = true;
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
        updateStars();
      }
    };
  }
);