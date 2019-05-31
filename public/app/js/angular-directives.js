function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

angular.module("uiSwitch", []).directive("switch", function () {
  return {
    restrict: "AE",
    replace: !0,
    transclude: !0,
    template: function template(n, e) {
      var s = "";
      return s += "<span", s += ' class="switch' + (e["class"] ? " " + e["class"] : "") + '"', s += e.ngModel ? ' ng-click="' + e.disabled + " ? " + e.ngModel + " : " + e.ngModel + "=!" + e.ngModel + (e.ngChange ? "; " + e.ngChange + '()"' : '"') : "", s += ' ng-class="{ checked:' + e.ngModel + ", disabled:" + e.disabled + ' }"', s += ">", s += "<small></small>", s += '<input type="checkbox"', s += e.id ? ' id="' + e.id + '"' : "", s += e.name ? ' name="' + e.name + '"' : "", s += e.ngModel ? ' ng-model="' + e.ngModel + '"' : "", s += ' style="display:none" />', s += '<span class="switch-text">', s += e.on ? '<span class="on">' + e.on + "</span>" : "", s += e.off ? '<span class="off">' + e.off + "</span>" : " ", s += "</span>";
    }
  };
});
angular.module('revolunet.stepper', []).directive('rnStepper', function ($rootScope, $interval, $timeout) {
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
    template: '<a ng-if="mobile == false" class="btn btn-primary rn-stepper left" ng-disabled="isOverMin()" ng-click="decrement()" ng-mousedown="startTimer(-1)" ng-mouseup="stopTimer()" ng-mouseout="stopTimer()" >-</a>' + '<a ng-if="mobile == true" class="btn btn-primary rn-stepper left" ng-disabled="isOverMin()" ng-click="decrement()" ng-mouseup="stopTimer()" hm-press="startTimer(-1)" hm-release="stopTimer()" hm-pressup="stopTimer()">-</a>' + '<input ng-show="step < 1" type="text" ng-model="ngModel" ng-disabled="{{ngDisabled}}" placeholder="0" class="rn-stepper" restrict-input="{type: \'{{step < 1 ? \'digitsAndDotOnly\' : \'digitsOnly\'}}\'}">' + '<input ng-show="step >= 1" type="tel" ng-model="ngModel" ng-disabled="{{ngDisabled}}" placeholder="0" class="rn-stepper" restrict-input="{type: \'{{step < 1 ? \'digitsAndDotOnly\' : \'digitsOnly\'}}\'}">' + '<a ng-if="mobile == false" class="btn btn-primary rn-stepper right" ng-disabled="isOverMax()" ng-click="increment()" ng-mousedown="startTimer(1)" ng-mouseup="stopTimer()" ng-mouseout="stopTimer()" >+</a>' + '<a ng-if="mobile == true" class="btn btn-primary rn-stepper right" ng-disabled="isOverMax()" ng-click="increment()" ng-mouseup="stopTimer()" hm-press="startTimer(1)" hm-release="stopTimer()" hm-pressup="stopTimer()" >+</a>',
    link: function link(scope, iElement, iAttrs, ngModelController) {
      scope.label = '';
      scope.mobile = $rootScope.mobile;

      if (angular.isDefined(iAttrs.label)) {
        iAttrs.$observe('label', function (value) {
          scope.label = ' ' + value;
          ngModelController.$render();
        });
      }

      ngModelController.$render = function () {
        // update the validation status
        checkValidity();
      }; // when model change, cast to integer


      ngModelController.$formatters.push(function (value) {
        return Math.round(value * 100000) / 100000;
      }); // when view change, cast to integer

      ngModelController.$parsers.push(function (value) {
        return Math.round(value * 100000) / 100000;
      });

      function checkValidity() {
        // check if min/max defined to check validity
        var valid = !(scope.isOverMin(true) || scope.isOverMax(true)); // set our model validity
        // the outOfBounds is an arbitrary key for the error.
        // will be used to generate the CSS class names for the errors

        if (valid == false) {
          if (scope.isOverMin(true)) ngModelController.$setViewValue(scope.min);
          if (scope.isOverMax(true)) ngModelController.$setViewValue(scope.max);
        }

        ngModelController.$setValidity('outOfBounds', valid);
      }

      function updateModel(offset) {
        // update the model, call $parsers pipeline...
        if (ngModelController.$viewValue == null || ngModelController.$viewValue == undefined || isNaN(ngModelController.$viewValue)) ngModelController.$setViewValue(0);
        ngModelController.$setViewValue(ngModelController.$viewValue + offset); // update the local view

        ngModelController.$render();
      }

      scope.isOverMin = function (strict) {
        var offset = strict ? 0 : scope.step ? scope.step : 0.1;
        return angular.isDefined(scope.min) && ngModelController.$viewValue - offset < Math.round(scope.min * 100000) / 100000;
      };

      scope.isOverMax = function (strict) {
        var offset = strict ? 0 : scope.step ? scope.step : 0.1;
        return angular.isDefined(scope.max) && ngModelController.$viewValue + offset > Math.round(scope.max * 100000) / 100000;
      }; // update the value when user clicks the buttons


      scope.increment = function () {
        if (scope.ngDisabled == false) updateModel(scope.step ? scope.step : 0.1);
      };

      scope.decrement = function () {
        if (scope.ngDisabled == false) updateModel(-(scope.step ? scope.step : 0.1));
      };

      scope.incInterval = null;

      function startInterval(inc) {
        if (inc > 0) scope.incInterval = $interval(scope.increment, 100);else scope.incInterval = $interval(scope.decrement, 100);
      }

      scope.incTimer = null;

      scope.startTimer = function (inc) {
        scope.stopTimer();
        scope.incTimer = $timeout(startInterval, 300, true, inc);
      };

      scope.stopTimer = function () {
        if (scope.incTimer) $timeout.cancel(scope.incTimer);
        if (scope.incInterval) $interval.cancel(scope.incInterval);
      }; // check validity on start, in case we're directly out of bounds


      checkValidity(); // watch out min/max and recheck validity when they change

      scope.$watch('min+max', function () {
        checkValidity();
      });
    }
  };
});
/*
 * BEEP app
 * Author: Iconize <pim@iconize.nl>
 *
 * Background directive
 */

app.directive('background', function ($q) {
  return {
    restrict: 'E',
    link: function link(scope, element, attrs, tabsCtrl) {
      scope.preload = function (url) {
        element.addClass('loading');
        var deffered = $q.defer(),
            image = new Image();
        image.src = url;

        if (image.complete) {
          deffered.resolve();
        } else {
          image.addEventListener('load', function () {
            deffered.resolve();
          });
          image.addEventListener('error', function () {
            deffered.reject();
          });
        }

        return deffered.promise;
      };

      scope.fadeImage = function () {
        element.css({
          "background-image": "url('" + attrs.url + "')"
        });
        element.addClass('animated fadeIn');
        element.removeClass('loading');
        setTimeout(function () {
          element.removeClass('animated fadeIn');
        }, 1000);
      };

      scope.preload(attrs.url).then(function () {
        scope.fadeImage();
      });
      scope.$watch(function () {
        return attrs.url;
      }, function () {
        element.css({
          "background-image": "none"
        });
        scope.preload(attrs.url).then(function () {
          scope.fadeImage();
        });
      });
    }
  };
});
app.directive('starRating', function () {
  return {
    restrict: 'EA',
    require: 'ngModel',
    template: '<ul class="star-rating" ng-class="{readonly: readonly}">' + '  <li ng-repeat="star in stars" class="star" ng-class="{filled: star.filled}" ng-click="toggle($index)">' + '    <i class="fa fa-2x fa-star"></i>' + // or &#9733
    '  </li>' + '</ul>',
    scope: {
      ratingValue: '=ngModel',
      max: '=?',
      // optional (default is 5)
      onRatingSelect: '&?',
      readonly: '=?'
    },
    link: function link(scope, element, attributes) {
      if (scope.max == undefined) {
        scope.max = 5;
      }

      function updateStars() {
        scope.stars = [];

        for (var i = 0; i < scope.max; i++) {
          scope.stars.push({
            filled: i < scope.ratingValue
          });
        }

        if (scope.stars[scope.ratingValue - 1] != undefined) scope.stars[scope.ratingValue - 1].filled = true;
      }

      ;

      scope.toggle = function (index) {
        if (scope.readonly == undefined || scope.readonly === false) {
          if (scope.ratingValue == index + 1) // deselect
            {
              scope.ratingValue = -1;
            } else {
            scope.ratingValue = index + 1;
          }

          if (typeof scope.onRatingSelect == 'function') {
            scope.onRatingSelect({
              rating: scope.ratingValue
            });
          }
        }
      };

      scope.$watch('ratingValue', function (oldValue, newValue) {
        if (oldValue || newValue === 0) {
          updateStars();
        }
      });
      updateStars();
    }
  };
});
app.directive('smileRating', function () {
  return {
    restrict: 'EA',
    require: 'ngModel',
    template: '<ul class="smile-rating" ng-class="{readonly: readonly}">' + '  <li class="fa-stack smile" ng-class="{filled: smile[0].filled}" ng-click="toggle(0)">' + '    <i class="fa fa-circle frown fa-stack-2x"></i>' + '    <i class="fa fa-frown-o fa-stack-2x face"></i>' + '  </li>' + '  <li class="fa-stack smile" ng-class="{filled: smile[1].filled}" ng-click="toggle(1)">' + '    <i class="fa fa-circle meh fa-stack-2x"></i>' + '    <i class="fa fa-meh-o fa-stack-2x face"></i>' + '  </li>' + '  <li class="fa-stack smile" ng-class="{filled: smile[2].filled}" ng-click="toggle(2)">' + '    <i class="fa fa-circle smile fa-stack-2x"></i>' + '    <i class="fa fa-smile-o fa-stack-2x face"></i>' + '  </li>' + '</ul>',
    scope: {
      ratingValue: '=ngModel',
      max: '=?',
      // optional (default is 3)
      onRatingSelect: '&?',
      readonly: '=?'
    },
    link: function link(scope, element, attributes) {
      if (scope.max == undefined) {
        scope.max = 3;
      }

      function updateStars() {
        scope.smile = [];

        for (var i = 0; i < scope.max; i++) {
          scope.smile.push({
            filled: false
          });
        }

        if (scope.smile[scope.ratingValue - 1] != undefined) scope.smile[scope.ratingValue - 1].filled = true;
      }

      ;

      scope.toggle = function (index) {
        if (scope.readonly == undefined || scope.readonly === false) {
          if (scope.ratingValue == index + 1) // deselect
            {
              scope.ratingValue = -1;
            } else {
            scope.ratingValue = index + 1;
          }

          if (typeof scope.onRatingSelect == 'function') {
            scope.onRatingSelect({
              rating: scope.ratingValue
            });
          }
        }
      };

      scope.$watch('ratingValue', function (oldValue, newValue) {
        if (oldValue || newValue === 0) {
          updateStars();
        }
      });
    }
  };
});
app.directive('yesNoRating', function () {
  return {
    restrict: 'EA',
    template: '<ul class="yes-no-rating" ng-class="{readonly: readonly}">' + '  <li class="option" ng-class="{filled: smile[1].filled}" ng-click="toggle(1)">' + '    <span><i class="fa fa-check-circle fa-2x face yes {{itemName}}"></i><p>{{yes}}</p></span>' + '  </li>' + '  <li class="option" ng-class="{filled: smile[0].filled}" ng-click="toggle(0)">' + '    <span><i class="fa fa-times-circle fa-2x face no {{itemName}}"></i><p>{{no}}</p></span>' + '  </li>' + '</ul>',
    scope: {
      ratingValue: '=ngModel',
      max: '=?',
      // optional (default is 3)
      onRatingSelect: '&?',
      readonly: '=?',
      yes: '=?',
      no: '=?',
      itemName: '=?'
    },
    link: function link(scope, element, attributes) {
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
        scope.smile = [{
          filled: false
        }, {
          filled: false
        }];

        for (var i = 0; i <= scope.max; i++) {
          scope.smile.push({
            filled: false
          });
        }

        if (scope.smile[scope.ratingValue] != undefined) scope.smile[scope.ratingValue].filled = true;
      }

      ;

      scope.toggle = function (index) {
        if (scope.readonly == undefined || scope.readonly === false) {
          if (scope.ratingValue == index) // deselect
            {
              scope.ratingValue = -1;
            } else {
            scope.ratingValue = index;
          }

          if (typeof scope.onRatingSelect == 'function') {
            scope.onRatingSelect({
              rating: scope.ratingValue
            });
          }
        }
      };

      scope.$watch('ratingValue', function (oldValue, newValue) {
        if (oldValue > -1 || newValue === -1 || oldValue != newValue) {
          updateStars();
        }
      });
    }
  };
});
app.directive('mapsAutocompleteMobile', function ($timeout) {
  return {
    link: function link() {
      $timeout(function () {
        container = document.getElementsByClassName('pac-container'); // disable ionic data tab

        angular.element(container).attr('data-tap-disabled', 'true'); // leave input field if google-address-entry is selected

        angular.element(container).on("click", function () {
          document.getElementById('autocomplete').blur();
        });
      }, 500);
    }
  };
});
app.directive('checklistFieldset', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    scope: {
      cat: '=',
      cols: '='
    },
    link: function link(scope, iElement, iAttrs) {
      scope.lang = $rootScope.lang;
      scope.locale = $rootScope.locale;
    },
    templateUrl: '/app/views/forms/checklist_fieldset.html'
  };
}]);
app.directive('checklistInput', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    scope: {
      item: '=',
      cols: '='
    },
    link: function link(scope, iElement, iAttrs) {
      scope.lang = $rootScope.lang;
      scope.locale = $rootScope.locale;
      scope.hives = $rootScope.hives;
      scope.locations = $rootScope.hives;
      scope.beeraces = $rootScope.beeraces;
      scope.hivetypes = $rootScope.hivetypes;
      scope.$watch('item.value', function (oldValue, newValue) {
        if (newValue != oldValue) {
          //console.log(scope.item.input, scope.item.id, oldValue);
          if (scope.item.input == 'list' && (oldValue === true || oldValue === false)) // boolean list
            {// only carry out addRemoveFromList (from item html)
            } else {
            $rootScope.changeChecklistItem(scope.item.input, scope.item.id, oldValue, true);
          }
        }
      });

      scope.addRemoveFromList = function (listItem) {
        var id = listItem.id;
        var add = typeof listItem.value == 'undefined' ? true : listItem.value;
        console.log('ar', scope.item.id, id, add, scope.item.value);
        var selected_array = typeof scope.item.value != 'undefined' ? scope.item.value.split(',') : [];
        var i = selected_array.indexOf(id + '');

        if (add && i == -1) // add -> listItem.value == true
          {
            selected_array.push(id + '');
          } else if (i > -1) {
          selected_array.splice(i, 1);
        }

        var value = selected_array.join(',');
        scope.item.value = value;
        $rootScope.changeChecklistItem(scope.item.input, scope.item.id, value, true);
      };

      scope.gradeOptions = {
        showTicksValues: true,
        floor: 0,
        ceil: 10,
        stepsArray: [{
          value: 0,
          legend: '-'
        }, {
          value: 1,
          legend: scope.lang.Poor
        }, {
          value: 2,
          legend: ''
        }, {
          value: 3,
          legend: ''
        }, {
          value: 4,
          legend: ''
        }, {
          value: 5,
          legend: scope.lang.Average
        }, {
          value: 6,
          legend: ''
        }, {
          value: 7,
          legend: ''
        }, {
          value: 8,
          legend: ''
        }, {
          value: 9,
          legend: ''
        }, {
          value: 10,
          legend: scope.lang.Excellent
        }],
        getPointerColor: function getPointerColor(value) {
          if (value == 0) return '#CCC';
          if (value < 4) return '#8F1619';
          if (value < 6) return '#5F3F90';
          if (value < 8) return '#243D80';
          if (value < 11) return '#069518';
          return '#F29100';
        },
        getTickColor: function getTickColor(value) {
          if (value == 0) return '#CCC';
          if (value < 4) return '#8F1619';
          if (value < 6) return '#5F3F90';
          if (value < 8) return '#243D80';
          if (value < 11) return '#069518';
          return '#F29100';
        }
      };
      scope.scoreQualityOptions = {
        showTicksValues: true,
        floor: 0,
        ceil: 4,
        stepsArray: [{
          value: 0,
          legend: '-'
        }, {
          value: 1,
          legend: scope.lang.Poor
        }, {
          value: 2,
          legend: scope.lang.Fair
        }, {
          value: 3,
          legend: scope.lang.Good
        }, {
          value: 4,
          legend: scope.lang.Excellent
        }],
        getPointerColor: function getPointerColor(value) {
          if (value == 0) return '#CCC';
          if (value == 1) return '#8F1619';
          if (value == 2) return '#5F3F90';
          if (value == 3) return '#243D80';
          if (value == 4) return '#069518';
          return '#F29100';
        },
        getTickColor: function getTickColor(value) {
          if (value == 0) return '#CCC';
          if (value == 1) return '#8F1619';
          if (value == 2) return '#5F3F90';
          if (value == 3) return '#243D80';
          if (value == 4) return '#069518';
          return '#F29100';
        }
      };
      scope.scoreAmountOptions = {
        showTicksValues: true,
        floor: 0,
        ceil: 4,
        stepsArray: [{
          value: 0,
          legend: '-'
        }, {
          value: 1,
          legend: scope.lang.Low
        }, {
          value: 2,
          legend: scope.lang.Medium
        }, {
          value: 3,
          legend: scope.lang.High
        }, {
          value: 4,
          legend: scope.lang.Extreme
        }],
        getPointerColor: function getPointerColor(value) {
          if (value == 0) return '#CCC';
          if (value == 1) return '#069518';
          if (value == 2) return '#243D80';
          if (value == 3) return '#5F3F90';
          if (value == 4) return '#8F1619';
          return '#F29100';
        },
        getTickColor: function getTickColor(value) {
          if (value == 0) return '#CCC';
          if (value == 1) return '#069518';
          if (value == 2) return '#243D80';
          if (value == 3) return '#5F3F90';
          if (value == 4) return '#8F1619';
          return '#F29100';
        }
      };
      scope.scorePercentageOptions = {
        ticksArray: [0, 25, 50, 75, 100],
        floor: -1,
        ceil: 100,
        translate: function translate(value) {
          if (value == -1 || isNaN(value)) return '-';
          return value + '%';
        },
        getPointerColor: function getPointerColor(value) {
          if (value == -1 || isNaN(value)) return '#CCC';
          return '#F29100';
        }
      };
    },
    templateUrl: '/app/views/forms/checklist_input.html?v=1'
  };
}]);
app.directive('beepHive', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: // Desktop
    '<div class="hive" ng-if="mobile == false && new == false">' + '<h4 class="title" ng-class="{\'hiveview\':hiveview}">{{hive.name}}</h4>' + '<p ng-if="hiveview" class="location notes">({{ hive.location }})</p>' + '<p ng-if="hive.reminder != null && hive.reminder != \'\'" class="notes reminder" title="{{ hive.reminder }}">{{hive.reminder}}</p>' + '<p ng-if="hive.reminder_date != null && hive.reminder_date != \'\'" class="notes reminder-date">{{hive.reminder_date | amDateFormat:\'dd D MMMM YYYY HH:mm\'}}</p>' + '<div class="info">' + '<a ng-if="hive.attention == 1" href="#!/hives/{{hive.id}}/inspections" class="attention-icon" title="{{lang.needs_attention}}">!</a>' + '<a ng-if="hive.queen.color != null && hive.queen.color != \'\'" href="#!/hives/{{hive.id}}/edit#queen" class="queen-icon" style="background-color: {{hive.queen.color}};" title="{{hive.queen.name}}"></a>' + '<a ng-if="hive.impression > 0" href="#!/hives/{{hive.id}}/inspections" class="impression-icon" ng-class="{\'frown\':hive.impression==1, \'meh\':hive.impression==2, \'smile\':hive.impression==3}">' + '<i class="fa fa-2x" ng-class="{\'fa-frown-o\':hive.impression==1, \'fa-meh-o\':hive.impression==2, \'fa-smile-o\':hive.impression==3}"></i>' + '</a>' + '<a ng-if="hive.sensors.length > 0" ng-repeat="sensorId in hive.sensors" href="#!/measurements/{{sensorId}}" class="sensor-icon" title="{{lang.sensor}} {{sensorId}}">' + '<i class="fa fa-feed"></i>' + '</a>' + '</div>' + '<a ng-if="hive.id" href="#!/hives/{{hive.id}}/edit" title="{{lang.edit}}">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;">' + '<span ng-repeat="(key, frame) in layer.frames track by $index" class="frame" ng-class="layer.type"></span>' + '</p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</a>' + '<div class="btn-group" role="group" style="margin-bottom: 10px;">' + '<a href="#!/hives/{{hive.id}}/inspections" class="btn btn-default" title="{{lang.Inspections}}"><i class="fa fa-search"></i></a>' + '<a href="#!/hives/{{hive.id}}/inspect" class="btn btn-default" title="{{lang.inspect}}"><i class="fa fa-pencil"></i></a>' + '</div>' + '</div>' + //New
    '<div class="hive new" ng-if="mobile == false && new == true">' + '<h4 class="title">{{lang.New}} {{lang.hive}}</h4>' + '<a href="#!/hives/create?location_id={{loc.id}}">' + '<p class="lid"></p>' + '<p class="layer honey"></p>' + '<p class="layer brood"></p>' + '<p class="bottom"></p>' + '<a href="#!/hives/create?location_id={{loc.id}}">' + '<span class="icon fa-stack fa-lg">' + '<i class="fa fa-circle fa-stack-2x"></i>' + '<i class="fa fa-plus-circle fa-stack-2x fa-inverse"></i>' + '</span>' + '</a>' + '</a>' + '<div class="btn-group" role="group" style="margin-bottom: 10px;">' + '<a href="#!/hives/create?location_id={{loc.id}}" class="btn btn-default" title="{{lang.create_new}} {{lang.hive}}"><i class="fa fa-plus"></i></a>' + '</div>' + '</div>' + // Mobile
    '<div ng-if="mobile == true && new == false" class="row">' + '<div class="col-xs-3">' + '<div class="hive-container">' + '<a href="#!/hives/{{hive.id}}/edit" title="{{lang.edit}}">' + '<div class="hive small">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;"></p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</div>' + '</a>' + '</div>' + '</div>' + '<div class="col-xs-6 hive mobile">' + '<p class="hive-name-mobile">{{hive.name}}</p>' + '<p ng-if="hiveview" class="location notes">({{ hive.location }})</p>' + '<p ng-if="hive.reminder != null && hive.reminder != \'\'" class="reminder notes" title="{{ hive.reminder }}">{{hive.reminder}}</p>' + '<p ng-if="hive.reminder_date != null && hive.reminder_date != \'\'" class="notes reminder-date">{{hive.reminder_date | amDateFormat:\'dd D MMM YYYY HH:mm\'}}</p>' + '<div class="info mobile">' + '<a ng-if="hive.attention == 1" href="#!/hives/{{hive.id}}/inspections" class="attention-icon">!</a>' + '<a ng-if="hive.queen.color != null && hive.queen.color != \'\'" href="#!/hives/{{hive.id}}/edit#queen" class="queen-icon" style="background-color: {{hive.queen.color}};"></a>' + '<a ng-if="hive.impression > 0" href="#!/hives/{{hive.id}}/inspections" class="impression-icon" ng-class="{\'frown\':hive.impression==1, \'meh\':hive.impression==2, \'smile\':hive.impression==3}">' + '<i class="fa fa-2x" ng-class="{\'fa-frown-o\':hive.impression==1, \'fa-meh-o\':hive.impression==2, \'fa-smile-o\':hive.impression==3}"></i>' + '</a>' + '<a ng-if="hive.sensors.length > 0" ng-repeat="sensorId in hive.sensors" href="#!/measurements/{{sensorId}}" class="sensor-icon">' + '<i class="fa fa-feed"></i>' + '</a>' + '</div>' + '</div>' + '<div class="col-xs-2 text-right">' + '<a href="#!/hives/{{hive.id}}/inspections" class="btn btn-default" title="{{lang.Inspections}}"><i class="fa fa-search"></i></a>' + '<br><a href="#!/hives/{{hive.id}}/inspect" class="btn btn-default" title="{{lang.inspect}}"><i class="fa fa-pencil"></i></a>' + '</div>' + '</div>' + // New
    '<div ng-if="mobile == true && new == true" class="row">' + '<div class="col-xs-3"></div>' + '<div class="col-xs-6">' + '<p class="hive-name-mobile">{{lang.New}} {{lang.hive}}</p>' + '</div>' + '<div class="col-xs-2 text-right">' + '<a href="#!/hives/create?location_id={{loc.id}}" class="btn btn-default" title="{{lang.create_new}} {{lang.hive}}"><i class="fa fa-plus"></i></a>' + '</div>' + '</div>',
    scope: {
      hiveview: '=?',
      // show location name
      hive: '=?',
      "new": '=?',
      loc: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.lang = $rootScope.lang;
      scope.mobile = $rootScope.mobile;
      if (typeof scope["new"] == 'undefined') scope["new"] = false;else if (scope["new"] == 'true') scope["new"] = true;
      if (typeof scope.hiveview == 'undefined') scope.hiveview = false;else if (scope.hiveview == 'true') scope.hiveview = true;
    }
  };
}]);
app.directive('beepHiveSelector', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: '<div class="hive-container small">' + '<a ng-click="selecthive(hive)" title="{{ hive.name }} - {{ hive.location}}">' + '<div class="hive small">' + '<p class="lid" style="width: {{width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{width}}px;"></p>' + '<p class="bottom" style="width: {{width}}px;"></p>' + '</div>' + '<div ng-if="selectedids.indexOf(hive.id) > -1 && editableids.indexOf(hive.id) == -1" class="select-icon">' + '<i class="fa fa-2x fa-eye"></i>' + '</div>' + '<div ng-if="editableids.indexOf(hive.id) > -1" class="select-icon edit">' + '<i class="fa fa-2x fa-pencil"></i>' + '</div>' + '<p class="title" ng-class="{\'selected\':selectedids.indexOf(scope.hive.id) > -1}">{{ hive.name }}</p>' + '</a>' + '</div>',
    scope: {
      hive: '=?',
      selecthive: '=?',
      selectedids: '=?',
      editableids: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.lang = $rootScope.lang;
      scope.mobile = $rootScope.mobile;
      scope.width = 15 + scope.hive.frames * 3;
    }
  };
}]);
app.directive('beepGroupHive', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: // Desktop
    '<div class="hive" ng-if="mobile == false && new == false">' + '<h4 class="title" ng-class="{\'hiveview\':hiveview}">{{hive.name}}</h4>' + '<p ng-if="hiveview" class="location notes">({{ hive.location }})</p>' + '<p ng-if="hive.owner" class="location notes">({{ lang.my_hive }})</p>' + '<p ng-if="hive.reminder != null && hive.reminder != \'\'" class="notes reminder" title="{{ hive.reminder }}">{{hive.reminder}}</p>' + '<p ng-if="hive.reminder_date != null && hive.reminder_date != \'\'" class="notes reminder-date">{{hive.reminder_date | amDateFormat:\'dd D MMMM YYYY HH:mm\'}}</p>' + '<div class="info">' + '<a ng-if="hive.attention == 1" href="#!/hives/{{hive.id}}/inspections" class="attention-icon" title="{{lang.needs_attention}}">!</a>' + '<a ng-if="hive.queen.color != null && hive.queen.color != \'\' && (hive.editable || hive.owner)" href="#!/hives/{{hive.id}}/edit" class="queen-icon" style="background-color: {{hive.queen.color}};" title="{{hive.queen.name}}"></a>' + '<div ng-if="hive.queen.color != null && hive.queen.color != \'\' && !(hive.editable || hive.owner)" class="queen-icon" style="background-color: {{hive.queen.color}};" title="{{hive.queen.name}}"></div>' + '<a ng-if="hive.impression > 0" href="#!/hives/{{hive.id}}/inspections" class="impression-icon" ng-class="{\'frown\':hive.impression==1, \'meh\':hive.impression==2, \'smile\':hive.impression==3}">' + '<i class="fa fa-2x" ng-class="{\'fa-frown-o\':hive.impression==1, \'fa-meh-o\':hive.impression==2, \'fa-smile-o\':hive.impression==3}"></i>' + '</a>' + '<a ng-if="hive.sensors.length > 0" ng-repeat="sensorId in hive.sensors" href="#!/measurements/{{sensorId}}" class="sensor-icon" title="{{lang.sensor}} {{sensorId}}">' + '<i class="fa fa-feed"></i>' + '</a>' + '</div>' + '<a ng-if="hive.id && (hive.editable || hive.owner)" href="#!/hives/{{hive.id}}/edit" title="{{ lang.edit }}">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;">' + '<span ng-repeat="(key, frame) in layer.frames track by $index" class="frame" ng-class="layer.type"></span>' + '</p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</a>' + '<div ng-if="hive.id && !(hive.editable || hive.owner)">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;">' + '<span ng-repeat="(key, frame) in layer.frames track by $index" class="frame" ng-class="layer.type"></span>' + '</p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</div>' + '<div class="btn-group" role="group" style="margin-bottom: 10px;">' + '<a href="#!/hives/{{hive.id}}/inspections" class="btn btn-default" title="{{lang.Inspections}}"><i class="fa fa-search"></i></a>' + '<a ng-if="hive.editable || hive.owner" href="#!/hives/{{hive.id}}/inspect" class="btn btn-default" title="{{lang.inspect}}"><i class="fa fa-pencil"></i></a>' + '</div>' + '</div>' + // Mobile
    '<div ng-if="mobile == true && new == false" class="row">' + '<div class="col-xs-3">' + '<div class="hive-container">' + '<a ng-if="hive.id && (hive.editable || hive.owner)" href="#!/hives/{{hive.id}}/edit" title="{{ lang.edit }}">' + '<div class="hive small">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;"></p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</div>' + '</a>' + '<div ng-if="hive.id && !(hive.editable || hive.owner)">' + '<div class="hive small">' + '<p class="lid" style="width: {{hive.width}}px;"></p>' + '<p ng-repeat="(key, layer) in hive.layers | orderBy : \'-type\' " class="layer" ng-class="layer.type" style="background-color: {{hive.color}}; width: {{hive.width}}px;"></p>' + '<p class="bottom" style="width: {{hive.width}}px;"></p>' + '</div>' + '</div>' + '</div>' + '</div>' + '<div class="col-xs-6 hive mobile">' + '<p class="hive-name-mobile">{{hive.name}}</p>' + '<p ng-if="hiveview" class="location notes mobile">({{ hive.location }})</p>' + '<p ng-if="hive.owner" class="location notes mobile">({{ lang.my_hive }})</p>' + '<p ng-if="hive.reminder != null && hive.reminder != \'\'" class="reminder notes mobile" title="{{ hive.reminder }}">{{hive.reminder}}</p>' + '<p ng-if="hive.reminder_date != null && hive.reminder_date != \'\'" class="notes reminder-date">{{hive.reminder_date | amDateFormat:\'dd D MMM YYYY HH:mm\'}}</p>' + '<div class="info mobile">' + '<a ng-if="hive.attention == 1" href="#!/hives/{{hive.id}}/inspections" class="attention-icon">!</a>' + '<a ng-if="hive.queen.color != null && hive.queen.color != \'\' && (hive.editable || hive.owner)" href="#!/hives/{{hive.id}}/edit" class="queen-icon" style="background-color: {{hive.queen.color}};"></a>' + '<div ng-if="hive.queen.color != null && hive.queen.color != \'\' && !(hive.editable || hive.owner)" class="queen-icon" style="background-color: {{hive.queen.color}};"></div>' + '<a ng-if="hive.impression > 0" href="#!/hives/{{hive.id}}/inspections" class="impression-icon" ng-class="{\'frown\':hive.impression==1, \'meh\':hive.impression==2, \'smile\':hive.impression==3}">' + '<i class="fa fa-2x" ng-class="{\'fa-frown-o\':hive.impression==1, \'fa-meh-o\':hive.impression==2, \'fa-smile-o\':hive.impression==3}"></i>' + '</a>' + '<a ng-if="hive.sensors.length > 0" ng-repeat="sensorId in hive.sensors" href="#!/measurements/{{sensorId}}" class="sensor-icon">' + '<i class="fa fa-feed"></i>' + '</a>' + '</div>' + '</div>' + '<div class="col-xs-2 text-right">' + '<a href="#!/hives/{{hive.id}}/inspections" class="btn btn-default" title="{{lang.Inspections}}"><i class="fa fa-search"></i></a>' + '<br><a ng-if="hive.editable || hive.owner" href="#!/hives/{{hive.id}}/inspect" class="btn btn-default" title="{{lang.inspect}}"><i class="fa fa-pencil"></i></a>' + '</div>' + '</div>',
    scope: {
      hiveview: '=?',
      // show location name
      hive: '=?',
      "new": '=?',
      loc: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.lang = $rootScope.lang;
      scope.mobile = $rootScope.mobile;
      if (typeof scope["new"] == 'undefined') scope["new"] = false;else if (scope["new"] == 'true') scope["new"] = true;
      if (typeof scope.hiveview == 'undefined') scope.hiveview = false;else if (scope.hiveview == 'true') scope.hiveview = true;
    }
  };
}]);
app.directive('beepUserSelector', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: // Table row
    '<td>' + '<p ng-bind="index+1"></p>' + '</td>' + '<td>' + '<input class="form-control" ng-model="user.name" placeholder="{{lang.invitee_name}}" ng-disabled="user.id != null">' + '</td>' + '<td>' + '<input class="form-control" ng-model="user.email" placeholder="{{lang.email_is_required}}" ng-disabled="user.id != null">' + '</td>' + '<td>' + '<p ng-if="user.accepted == null">{{user.invited}}</p>' + '</td>' + '<td>' + '<p ng-if="!user.creator">{{lang.Admin}} <input type="checkbox" ng-model="user.admin" ng-disabled="user.delete" ng-checked="user.admin"></p>' + '<p ng-if="user.creator">{{lang.Creator}}</p>' + '</td>' + '<td>' + '<a ng-if="!user.creator" ng-click="delete(index)" class="btn" ng-class="{\'btn-danger\':user.delete, \'btn-warning\':!user.delete}" title="{{user.delete ? lang.Undelete : lang.Delete}}"><i class="fa fa-trash"></i></a>' + '</td>',
    scope: {
      user: '=?',
      change: '=?',
      "delete": '=?',
      index: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.locale = $rootScope.locale;
      scope.lang = $rootScope.lang;
      scope.mobile = $rootScope.mobile;
    }
  };
}]);
app.directive('beepSensor', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: // Table row
    '<td>' + '<span ng-bind="index+1" style="margin-right: 5px;"></span>' + '<a ng-click="show(index)" class="btn btn-default"><i class="fa fa-line-chart"></i></a>' + '</td>' + '<td>' + '<input class="form-control" ng-model="sensor.name" ng-disabled="sensor.delete">' + '</td>' + '<td>' + '<input class="form-control" ng-model="sensor.key" ng-disabled="sensor.delete">' + '</td>' + '<td>' + '<select ng-change="changetype(index, sensor.selected_type.name)" ng-model="sensor.selected_type" ng-options="item as item.trans[locale] for item in sensortypes | orderBy:transSort track by item.name" class="form-control" ng-disabled="sensor.delete">' + '<option value="">{{lang.Select}} {{lang.type}}...</option>' + '</select>' + '</td>' + '<td>' + '<p class="hive-name-mobile" ng-bind="sensor.hive.name"></p>' + '<p class="location notes" ng-bind="sensor.hive.location"></p>' + '</td>' + '<td>' + '<select ng-change="change(index, sensor.selected_hive_id)" ng-model="sensor.selected_hive_id" ng-options="item.id as item.name group by item.location for item in hives | orderBy:\'name\' track by item.id" class="form-control" ng-disabled="sensor.delete">' + '<option value="">{{lang.Select}} {{lang.hive}}...</option>' + '</select>' + '</td>' + '<td>' + '<a ng-click="delete(index)" class="btn" ng-class="{\'btn-danger\':sensor.delete, \'btn-warning\':!sensor.delete}" title="{{sensor.delete ? lang.Undelete : lang.Delete}}"><i class="fa fa-trash"></i></a>' + '</td>',
    scope: {
      hives: '=?',
      // show location name
      sensortypes: '=?',
      sensor: '=?',
      change: '=?',
      changetype: '=?',
      "delete": '=?',
      show: '=?',
      index: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.locale = $rootScope.locale;
      scope.lang = $rootScope.lang;
      scope.mobile = $rootScope.mobile;
    }
  };
}]);
app.directive('countrySelect', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'EA',
    template: '<select ng-model="model" name="country_code" class="form-control">' + '<option value="" label="Select a country..." selected="selected">Select a country...</option>' + '<optgroup id="country-optgroup-Europe" label="Europe">' + '<option value="al" label="Albania">Albania</option>' + '<option value="ad" label="Andorra">Andorra</option>' + '<option value="at" label="Austria">Austria</option>' + '<option value="by" label="Belarus">Belarus</option>' + '<option value="be" label="België">Belgium</option>' + '<option value="ba" label="Bosnia and Herzegovina">Bosnia and Herzegovina</option>' + '<option value="bg" label="Bulgaria">Bulgaria</option>' + '<option value="hr" label="Croatia">Croatia</option>' + '<option value="cy" label="Cyprus">Cyprus</option>' + '<option value="cz" label="Czech Republic">Czech Republic</option>' + '<option value="dk" label="Denmark">Denmark</option>' + '<option value="dd" label="East Germany">East Germany</option>' + '<option value="ee" label="Estonia">Estonia</option>' + '<option value="fo" label="Faroe Islands">Faroe Islands</option>' + '<option value="fi" label="Finland">Finland</option>' + '<option value="fr" label="France">France</option>' + '<option value="de" label="Germany">Germany</option>' + '<option value="gi" label="Gibraltar">Gibraltar</option>' + '<option value="gr" label="Greece">Greece</option>' + '<option value="gg" label="Guernsey">Guernsey</option>' + '<option value="hu" label="Hungary">Hungary</option>' + '<option value="is" label="Iceland">Iceland</option>' + '<option value="ie" label="Ireland">Ireland</option>' + '<option value="im" label="Isle of Man">Isle of Man</option>' + '<option value="it" label="Italy">Italy</option>' + '<option value="je" label="Jersey">Jersey</option>' + '<option value="lv" label="Latvia">Latvia</option>' + '<option value="li" label="Liechtenstein">Liechtenstein</option>' + '<option value="lt" label="Lithuania">Lithuania</option>' + '<option value="lu" label="Luxembourg">Luxembourg</option>' + '<option value="mk" label="Macedonia">Macedonia</option>' + '<option value="mt" label="Malta">Malta</option>' + '<option value="fx" label="Metropolitan France">Metropolitan France</option>' + '<option value="md" label="Moldova">Moldova</option>' + '<option value="mc" label="Monaco">Monaco</option>' + '<option value="me" label="Montenegro">Montenegro</option>' + '<option value="nl" label="Nederland">Netherlands</option>' + '<option value="no" label="Norway">Norway</option>' + '<option value="pl" label="Poland">Poland</option>' + '<option value="pt" label="Portugal">Portugal</option>' + '<option value="ro" label="Romania">Romania</option>' + '<option value="ru" label="Russia">Russia</option>' + '<option value="sm" label="San Marino">San Marino</option>' + '<option value="rs" label="Serbia">Serbia</option>' + '<option value="cs" label="Serbia and Montenegro">Serbia and Montenegro</option>' + '<option value="sk" label="Slovakia">Slovakia</option>' + '<option value="si" label="Slovenia">Slovenia</option>' + '<option value="es" label="Spain">Spain</option>' + '<option value="sj" label="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>' + '<option value="se" label="Sweden">Sweden</option>' + '<option value="ch" label="Switzerland">Switzerland</option>' + '<option value="ua" label="Ukraine">Ukraine</option>' + '<option value="su" label="Union of Soviet Socialist Republics">Union of Soviet Socialist Republics</option>' + '<option value="gb" label="United Kingdom">United Kingdom</option>' + '<option value="va" label="Vatican City">Vatican City</option>' + '<option value="ax" label="Åland Islands">Åland Islands</option>' + '</optgroup>' + '<optgroup id="country-optgroup-Africa" label="Africa">' + '<option value="dz" label="Algeria">Algeria</option>' + '<option value="ao" label="Angola">Angola</option>' + '<option value="bj" label="Benin">Benin</option>' + '<option value="bw" label="Botswana">Botswana</option>' + '<option value="bf" label="Burkina Faso">Burkina Faso</option>' + '<option value="bi" label="Burundi">Burundi</option>' + '<option value="cm" label="Cameroon">Cameroon</option>' + '<option value="cv" label="Cape Verde">Cape Verde</option>' + '<option value="cf" label="Central African Republic">Central African Republic</option>' + '<option value="td" label="Chad">Chad</option>' + '<option value="km" label="Comoros">Comoros</option>' + '<option value="cg" label="Congo - Brazzaville">Congo - Brazzaville</option>' + '<option value="cd" label="Congo - Kinshasa">Congo - Kinshasa</option>' + '<option value="ci" label="Côte d’Ivoire">Côte d’Ivoire</option>' + '<option value="dj" label="Djibouti">Djibouti</option>' + '<option value="eg" label="Egypt">Egypt</option>' + '<option value="gq" label="Equatorial Guinea">Equatorial Guinea</option>' + '<option value="er" label="Eritrea">Eritrea</option>' + '<option value="et" label="Ethiopia">Ethiopia</option>' + '<option value="ga" label="Gabon">Gabon</option>' + '<option value="gm" label="Gambia">Gambia</option>' + '<option value="gh" label="Ghana">Ghana</option>' + '<option value="gn" label="Guinea">Guinea</option>' + '<option value="gw" label="Guinea-Bissau">Guinea-Bissau</option>' + '<option value="ke" label="Kenya">Kenya</option>' + '<option value="ls" label="Lesotho">Lesotho</option>' + '<option value="lr" label="Liberia">Liberia</option>' + '<option value="ly" label="Libya">Libya</option>' + '<option value="mg" label="Madagascar">Madagascar</option>' + '<option value="mw" label="Malawi">Malawi</option>' + '<option value="ml" label="Mali">Mali</option>' + '<option value="mr" label="Mauritania">Mauritania</option>' + '<option value="mu" label="Mauritius">Mauritius</option>' + '<option value="yt" label="Mayotte">Mayotte</option>' + '<option value="ma" label="Morocco">Morocco</option>' + '<option value="mz" label="Mozambique">Mozambique</option>' + '<option value="na" label="Namibia">Namibia</option>' + '<option value="ne" label="Niger">Niger</option>' + '<option value="ng" label="Nigeria">Nigeria</option>' + '<option value="rw" label="Rwanda">Rwanda</option>' + '<option value="re" label="Réunion">Réunion</option>' + '<option value="sh" label="Saint Helena">Saint Helena</option>' + '<option value="sn" label="Senegal">Senegal</option>' + '<option value="sc" label="Seychelles">Seychelles</option>' + '<option value="sl" label="Sierra Leone">Sierra Leone</option>' + '<option value="so" label="Somalia">Somalia</option>' + '<option value="za" label="South Africa">South Africa</option>' + '<option value="sd" label="Sudan">Sudan</option>' + '<option value="sz" label="Swaziland">Swaziland</option>' + '<option value="st" label="São Tomé and Príncipe">São Tomé and Príncipe</option>' + '<option value="tz" label="Tanzania">Tanzania</option>' + '<option value="tg" label="Togo">Togo</option>' + '<option value="tn" label="Tunisia">Tunisia</option>' + '<option value="ug" label="Uganda">Uganda</option>' + '<option value="eh" label="Western Sahara">Western Sahara</option>' + '<option value="zm" label="Zambia">Zambia</option>' + '<option value="zw" label="Zimbabwe">Zimbabwe</option>' + '</optgroup>' + '<optgroup id="country-optgroup-Americas" label="Americas">' + '<option value="ai" label="Anguilla">Anguilla</option>' + '<option value="ag" label="Antigua and Barbuda">Antigua and Barbuda</option>' + '<option value="ar" label="Argentina">Argentina</option>' + '<option value="aw" label="Aruba">Aruba</option>' + '<option value="bs" label="Bahamas">Bahamas</option>' + '<option value="bb" label="Barbados">Barbados</option>' + '<option value="bz" label="Belize">Belize</option>' + '<option value="bm" label="Bermuda">Bermuda</option>' + '<option value="bo" label="Bolivia">Bolivia</option>' + '<option value="br" label="Brazil">Brazil</option>' + '<option value="vg" label="British Virgin Islands">British Virgin Islands</option>' + '<option value="ca" label="Canada">Canada</option>' + '<option value="ky" label="Cayman Islands">Cayman Islands</option>' + '<option value="cl" label="Chile">Chile</option>' + '<option value="co" label="Colombia">Colombia</option>' + '<option value="cr" label="Costa Rica">Costa Rica</option>' + '<option value="cu" label="Cuba">Cuba</option>' + '<option value="dm" label="Dominica">Dominica</option>' + '<option value="do" label="Dominican Republic">Dominican Republic</option>' + '<option value="ec" label="Ecuador">Ecuador</option>' + '<option value="sv" label="El Salvador">El Salvador</option>' + '<option value="fk" label="Falkland Islands">Falkland Islands</option>' + '<option value="gf" label="French Guiana">French Guiana</option>' + '<option value="gl" label="Greenland">Greenland</option>' + '<option value="gd" label="Grenada">Grenada</option>' + '<option value="gp" label="Guadeloupe">Guadeloupe</option>' + '<option value="gt" label="Guatemala">Guatemala</option>' + '<option value="gy" label="Guyana">Guyana</option>' + '<option value="ht" label="Haiti">Haiti</option>' + '<option value="hn" label="Honduras">Honduras</option>' + '<option value="jm" label="Jamaica">Jamaica</option>' + '<option value="mq" label="Martinique">Martinique</option>' + '<option value="mx" label="Mexico">Mexico</option>' + '<option value="ms" label="Montserrat">Montserrat</option>' + '<option value="an" label="Netherlands Antilles">Netherlands Antilles</option>' + '<option value="ni" label="Nicaragua">Nicaragua</option>' + '<option value="pa" label="Panama">Panama</option>' + '<option value="py" label="Paraguay">Paraguay</option>' + '<option value="pe" label="Peru">Peru</option>' + '<option value="pr" label="Puerto Rico">Puerto Rico</option>' + '<option value="bl" label="Saint Barthélemy">Saint Barthélemy</option>' + '<option value="kn" label="Saint Kitts and Nevis">Saint Kitts and Nevis</option>' + '<option value="lc" label="Saint Lucia">Saint Lucia</option>' + '<option value="mf" label="Saint Martin">Saint Martin</option>' + '<option value="pm" label="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>' + '<option value="vc" label="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>' + '<option value="sr" label="Suriname">Suriname</option>' + '<option value="tt" label="Trinidad and Tobago">Trinidad and Tobago</option>' + '<option value="tc" label="Turks and Caicos Islands">Turks and Caicos Islands</option>' + '<option value="vi" label="U.S. Virgin Islands">U.S. Virgin Islands</option>' + '<option value="us" label="United States">United States</option>' + '<option value="uy" label="Uruguay">Uruguay</option>' + '<option value="ve" label="Venezuela">Venezuela</option>' + '</optgroup>' + '<optgroup id="country-optgroup-Asia" label="Asia">' + '<option value="af" label="Afghanistan">Afghanistan</option>' + '<option value="am" label="Armenia">Armenia</option>' + '<option value="az" label="Azerbaijan">Azerbaijan</option>' + '<option value="bh" label="Bahrain">Bahrain</option>' + '<option value="bd" label="Bangladesh">Bangladesh</option>' + '<option value="bt" label="Bhutan">Bhutan</option>' + '<option value="bn" label="Brunei">Brunei</option>' + '<option value="kh" label="Cambodia">Cambodia</option>' + '<option value="cn" label="China">China</option>' + '<option value="cy" label="Cyprus">Cyprus</option>' + '<option value="ge" label="Georgia">Georgia</option>' + '<option value="hk" label="Hong Kong SAR China">Hong Kong SAR China</option>' + '<option value="in" label="India">India</option>' + '<option value="id" label="Indonesia">Indonesia</option>' + '<option value="ir" label="Iran">Iran</option>' + '<option value="iq" label="Iraq">Iraq</option>' + '<option value="il" label="Israel">Israel</option>' + '<option value="jp" label="Japan">Japan</option>' + '<option value="jo" label="Jordan">Jordan</option>' + '<option value="kz" label="Kazakhstan">Kazakhstan</option>' + '<option value="kw" label="Kuwait">Kuwait</option>' + '<option value="kg" label="Kyrgyzstan">Kyrgyzstan</option>' + '<option value="la" label="Laos">Laos</option>' + '<option value="lb" label="Lebanon">Lebanon</option>' + '<option value="mo" label="Macau SAR China">Macau SAR China</option>' + '<option value="my" label="Malaysia">Malaysia</option>' + '<option value="mv" label="Maldives">Maldives</option>' + '<option value="mn" label="Mongolia">Mongolia</option>' + '<option value="mm" label="Myanmar [Burma]">Myanmar [Burma]</option>' + '<option value="np" label="Nepal">Nepal</option>' + '<option value="nt" label="Neutral Zone">Neutral Zone</option>' + '<option value="kp" label="North Korea">North Korea</option>' + '<option value="om" label="Oman">Oman</option>' + '<option value="pk" label="Pakistan">Pakistan</option>' + '<option value="ps" label="Palestinian Territories">Palestinian Territories</option>' + '<option value="yd" label="People\'s Democratic Republic of Yemen">People\'s Democratic Republic of Yemen</option>' + '<option value="ph" label="Philippines">Philippines</option>' + '<option value="qa" label="Qatar">Qatar</option>' + '<option value="sa" label="Saudi Arabia">Saudi Arabia</option>' + '<option value="sg" label="Singapore">Singapore</option>' + '<option value="kr" label="South Korea">South Korea</option>' + '<option value="lk" label="Sri Lanka">Sri Lanka</option>' + '<option value="sy" label="Syria">Syria</option>' + '<option value="tw" label="Taiwan">Taiwan</option>' + '<option value="tj" label="Tajikistan">Tajikistan</option>' + '<option value="th" label="Thailand">Thailand</option>' + '<option value="tl" label="Timor-Leste">Timor-Leste</option>' + '<option value="tr" label="Turkey">Turkey</option>' + '<option value="tm" label="Turkmenistan">Turkmenistan</option>' + '<option value="ae" label="United Arab Emirates">United Arab Emirates</option>' + '<option value="uz" label="Uzbekistan">Uzbekistan</option>' + '<option value="vn" label="Vietnam">Vietnam</option>' + '<option value="ye" label="Yemen">Yemen</option>' + '</optgroup>' + '<optgroup id="country-optgroup-Oceania" label="Oceania">' + '<option value="as" label="American Samoa">American Samoa</option>' + '<option value="aq" label="Antarctica">Antarctica</option>' + '<option value="au" label="Australia">Australia</option>' + '<option value="bv" label="Bouvet Island">Bouvet Island</option>' + '<option value="io" label="British Indian Ocean Territory">British Indian Ocean Territory</option>' + '<option value="cx" label="Christmas Island">Christmas Island</option>' + '<option value="cc" label="Cocos [Keeling] Islands">Cocos [Keeling] Islands</option>' + '<option value="ck" label="Cook Islands">Cook Islands</option>' + '<option value="fj" label="Fiji">Fiji</option>' + '<option value="pf" label="French Polynesia">French Polynesia</option>' + '<option value="tf" label="French Southern Territories">French Southern Territories</option>' + '<option value="gu" label="Guam">Guam</option>' + '<option value="hm" label="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>' + '<option value="ki" label="Kiribati">Kiribati</option>' + '<option value="mh" label="Marshall Islands">Marshall Islands</option>' + '<option value="fm" label="Micronesia">Micronesia</option>' + '<option value="nr" label="Nauru">Nauru</option>' + '<option value="nc" label="New Caledonia">New Caledonia</option>' + '<option value="nz" label="New Zealand">New Zealand</option>' + '<option value="nu" label="Niue">Niue</option>' + '<option value="nf" label="Norfolk Island">Norfolk Island</option>' + '<option value="mp" label="Northern Mariana Islands">Northern Mariana Islands</option>' + '<option value="pw" label="Palau">Palau</option>' + '<option value="pg" label="Papua New Guinea">Papua New Guinea</option>' + '<option value="pn" label="Pitcairn Islands">Pitcairn Islands</option>' + '<option value="ws" label="Samoa">Samoa</option>' + '<option value="sb" label="Solomon Islands">Solomon Islands</option>' + '<option value="gs" label="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>' + '<option value="tk" label="Tokelau">Tokelau</option>' + '<option value="to" label="Tonga">Tonga</option>' + '<option value="tv" label="Tuvalu">Tuvalu</option>' + '<option value="um" label="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>' + '<option value="vu" label="Vanuatu">Vanuatu</option>' + '<option value="wf" label="Wallis and Futuna">Wallis and Futuna</option>' + '</optgroup>' + '</select>',
    scope: {
      model: '=?'
    },
    link: function link(scope, element, attributes) {
      scope.lang = $rootScope.lang;
    }
  };
}]);
app.directive('restrictInput', function () {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function link(scope, element, attr, ctrl) {
      ctrl.$parsers.unshift(function (viewValue) {
        var options = scope.$eval(attr.restrictInput);

        if (!options.regex && options.type) {
          switch (options.type) {
            case 'digitsOnly':
              options.regex = '^[0-9]*$';
              break;

            case 'digitsAndDotOnly':
              options.regex = '^[0-9.]*$';
              break;

            case 'lettersOnly':
              options.regex = '^[a-zA-Z]*$';
              break;

            case 'lowercaseLettersOnly':
              options.regex = '^[a-z]*$';
              break;

            case 'uppercaseLettersOnly':
              options.regex = '^[A-Z]*$';
              break;

            case 'lettersAndDigitsOnly':
              options.regex = '^[a-zA-Z0-9]*$';
              break;

            case 'validPhoneCharsOnly':
              options.regex = '^[0-9 ()/-]*$';
              break;

            default:
              options.regex = '';
          }
        }

        var reg = new RegExp(options.regex);

        if (reg.test(viewValue)) {
          //if valid view value, return it
          return viewValue;
        } else {
          //if not valid view value, use the model value (or empty string if that's also invalid)
          var overrideValue = reg.test(ctrl.$modelValue) ? ctrl.$modelValue : '';
          element.val(overrideValue);
          return overrideValue;
        }
      });
    }
  };
});
!function () {
  "use strict";

  function a() {
    function a(a, e, r, n) {
      var s = n[1],
          u = n[0],
          o = s[r.matchPassword],
          t = function t() {
        return o.$viewValue;
      };

      a.$watch(t, function () {
        u.$$parseAndValidate();
      }), u.$validators ? u.$validators.passwordMatch = function (a) {
        return !a && !o.$modelValue || a === o.$modelValue;
      } : u.$parsers.push(function (a) {
        return u.$setValidity("passwordMatch", !a && !o.$viewValue || a === o.$viewValue), a;
      }), o.$parsers.push(function (a) {
        return u.$setValidity("passwordMatch", !a && !u.$viewValue || a === u.$viewValue), a;
      });
    }

    var e = ["^ngModel", "^form"];
    return {
      restrict: "A",
      require: e,
      link: a
    };
  }

  angular.module("ngPassword", []).directive("matchPassword", a), angular.module("angular.password", ["ngPassword"]), angular.module("angular-password", ["ngPassword"]), "object" == (typeof module === "undefined" ? "undefined" : _typeof(module)) && "function" != typeof define && (module.exports = angular.module("ngPassword"));
}();
