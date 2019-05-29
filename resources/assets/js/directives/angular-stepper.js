angular.module('revolunet.stepper', [])

.directive('rnStepper', function($rootScope, $interval, $timeout) {
    return {
        restrict: 'AE',
        require: 'ngModel',
        scope: {
            min: '=',
            max: '=',
            step: '=',
            ngModel: '=',
            ngDisabled: '='
        },
        template: '<a ng-if="mobile == false" class="btn btn-primary rn-stepper left" ng-disabled="isOverMin()" ng-click="decrement()" ng-mousedown="startTimer(-1)" ng-mouseup="stopTimer()" ng-mouseout="stopTimer()" >-</a>' +
                  '<a ng-if="mobile == true" class="btn btn-primary rn-stepper left" ng-disabled="isOverMin()" ng-click="decrement()" ng-mouseup="stopTimer()" hm-press="startTimer(-1)" hm-release="stopTimer()" hm-pressup="stopTimer()">-</a>' +
                  '<input ng-show="step < 1" type="text" ng-model="ngModel" ng-disabled="{{ngDisabled}}" placeholder="0" class="rn-stepper" restrict-input="{type: \'{{step < 1 ? \'digitsAndDotOnly\' : \'digitsOnly\'}}\'}">' +
                  '<input ng-show="step >= 1" type="tel" ng-model="ngModel" ng-disabled="{{ngDisabled}}" placeholder="0" class="rn-stepper" restrict-input="{type: \'{{step < 1 ? \'digitsAndDotOnly\' : \'digitsOnly\'}}\'}">' +
                  '<a ng-if="mobile == false" class="btn btn-primary rn-stepper right" ng-disabled="isOverMax()" ng-click="increment()" ng-mousedown="startTimer(1)" ng-mouseup="stopTimer()" ng-mouseout="stopTimer()" >+</a>' +
                  '<a ng-if="mobile == true" class="btn btn-primary rn-stepper right" ng-disabled="isOverMax()" ng-click="increment()" ng-mouseup="stopTimer()" hm-press="startTimer(1)" hm-release="stopTimer()" hm-pressup="stopTimer()" >+</a>',
        
        link: function(scope, iElement, iAttrs, ngModelController) {

            scope.label  = '';
            scope.mobile = $rootScope.mobile;

            if (angular.isDefined(iAttrs.label)) {
                iAttrs.$observe('label', function(value) {
                    scope.label = ' ' + value;
                    ngModelController.$render();
                });
            }

            ngModelController.$render = function() {
                // update the validation status
                checkValidity();
            };

            // when model change, cast to integer
            ngModelController.$formatters.push(function(value) {
                return Math.round(value * 100000)/100000;
            });

            // when view change, cast to integer
            ngModelController.$parsers.push(function(value) {
                return Math.round(value * 100000)/100000;
            });

            function checkValidity() {
                // check if min/max defined to check validity
                var valid = !(scope.isOverMin(true) || scope.isOverMax(true));
                // set our model validity
                // the outOfBounds is an arbitrary key for the error.
                // will be used to generate the CSS class names for the errors
                if (valid == false)
                {
                    if (scope.isOverMin(true))
                        ngModelController.$setViewValue(scope.min);

                     if (scope.isOverMax(true))
                        ngModelController.$setViewValue(scope.max);
                }

                ngModelController.$setValidity('outOfBounds', valid);
            }

            function updateModel(offset) {
                // update the model, call $parsers pipeline...
                if (ngModelController.$viewValue == null || ngModelController.$viewValue == undefined || isNaN(ngModelController.$viewValue))
                    ngModelController.$setViewValue(0);

                ngModelController.$setViewValue(ngModelController.$viewValue + offset);
                // update the local view
                ngModelController.$render();
            }

            scope.isOverMin = function(strict) {
                var offset = strict?0:scope.step?scope.step:0.1;
                return (angular.isDefined(scope.min) && (ngModelController.$viewValue - offset) < (Math.round(scope.min * 100000)/100000));
            };
            scope.isOverMax = function(strict) {
                var offset = strict?0:scope.step?scope.step:0.1;
                return (angular.isDefined(scope.max) && (ngModelController.$viewValue + offset) > (Math.round(scope.max * 100000)/100000));
            };


            // update the value when user clicks the buttons
            scope.increment = function() {
                if (scope.ngDisabled == false)
                    updateModel(scope.step?scope.step:0.1);
            };
            scope.decrement = function() {
                if (scope.ngDisabled == false)
                    updateModel(-(scope.step?scope.step:0.1));
            };

            scope.incInterval = null;
            function startInterval(inc)
            {
                if (inc > 0)
                    scope.incInterval = $interval(scope.increment, 100);
                else
                    scope.incInterval = $interval(scope.decrement, 100);
            }

            scope.incTimer = null;
            scope.startTimer = function(inc)
            {
                scope.stopTimer();
                scope.incTimer = $timeout(startInterval, 300, true, inc);
            }

            scope.stopTimer = function()
            {
                if(scope.incTimer)
                    $timeout.cancel(scope.incTimer);

                if (scope.incInterval)
                    $interval.cancel(scope.incInterval);
            }

            // check validity on start, in case we're directly out of bounds
            checkValidity();

            // watch out min/max and recheck validity when they change
            scope.$watch('min+max', function() {
                checkValidity();
            });
        }
    };
});
