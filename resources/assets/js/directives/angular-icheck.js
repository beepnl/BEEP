app.directive('icheck', ['$timeout', '$parse', function($timeout, $parse) {
  return {
    compile: function(element, $attrs) {
      var icheckOptions = {
        checkboxClass: 'icheckbox_flat-orange',
        radioClass: 'iradio_flat-orange'
      };

      var modelAccessor = $parse($attrs['ngModel']);
      return function ($scope, element, $attrs, controller) {

        var modelChanged = function(event) {
          $scope.$apply(function() {
            modelAccessor.assign($scope, event.target.checked);
          });
        };

        $scope.$watch(modelAccessor, function (val) {
          var action = val ? 'check' : 'uncheck';
          element.iCheck(icheckOptions,action).on('ifChanged', modelChanged);
        });
      };
    }
  };
}]);