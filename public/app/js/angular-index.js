var _LANG$nl, _LANG$en, _LANG$de, _LANG$es, _LANG$fr, _LANG$ro, _LANG$pt;

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/*
 * BEEP - Bee monitoring
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */
var app = angular.module('app', ['ngRoute', 'angularMoment', 'chart.js', 'ngDialog', 'iconFilters', 'textFilters', 'uiSwitch', 'revolunet.stepper', 'ngMap', 'mp.colorPicker', 'rzModule', 'ngJsTree', 'angular-atc', 'angular-gestures', 'angularjs-gauge']);
app.config(function (hammerDefaultOptsProvider) {
  hammerDefaultOptsProvider.set({
    recognizers: [[Hammer.Press, {
      time: 250
    }, Hammer.Release]]
  });
});
/* Run some basic functions */

app.run(function ($rootScope, $location, $window, $route, $routeParams, amMoment, ngDialog, settings, api) {
  // set fastclick
  // FastClick.attach(document.body,{
  //   excludeNode: '^pac-'
  // }); 
  $rootScope.browser = navigator.userAgent;
  $rootScope.host = $location.host();
  $rootScope.supportedLocales = {
    "nl": "Nederlands",
    "de": "Deutsch",
    "en": "English",
    "fr": "Français",
    "pt": "português",
    "ro": "română",
    "es": "Spanish"
  };
  var setLang = api.getLocalStoreValue('lang');
  var navLang = navigator.language || navigator.userLanguage;
  var urlLang = $routeParams.language;

  if (typeof urlLang != 'undefined' && typeof $rootScope.supportedLocales[urlLang] != 'undefined') {
    navLang = urlLang;
  } else if (setLang != null) {
    navLang = setLang;
  }

  var navLocale = navLang.substr(0, 2); // set the language

  $rootScope.locale = typeof $rootScope.supportedLocales[navLocale] != 'undefined' ? navLocale : 'nl'; // set the chart colors 

  Chart.defaults.global.defaultFontFamily = "'DinPro', 'MAIN', 'Roboto Condensed', sans-serif";
  Chart.defaults.global.defaultFontSize = 16;
  Chart.defaults.global.defaultFontStyle = "normal";
  Chart.defaults.global.defaultFontColor = "#444444";
  Chart.defaults.global.animation.easing = "easeInOutCubic";
  Chart.defaults.global.animation.duration = 500;
  Chart.defaults.global.tooltips.enabled = true;
  Chart.defaults.global.tooltips.mode = "nearest";
  Chart.defaults.global.responsive = true;
  Chart.defaults.global.maintainAspectRatio = false;
  Chart.defaults.global.elements.line.borderCapStyle = "round";
  Chart.defaults.global.elements.line.borderJoinStyle = "round";
  Chart.defaults.global.elements.line.borderWidth = 3;
  Chart.defaults.global.elements.line.borderColor = "#000000";
  Chart.defaults.global.elements.point.radius = 2;
  Chart.defaults.global.elements.point.borderColor = "#444444";
  Chart.defaults.global.elements.point.borderWidth = 1;
  Chart.defaults.global.elements.rectangle.borderWidth = 0;
  Chart.defaults.global.elements.rectangle.borderColor = "#444444";
  Chart.defaults.global.elements.line.cubicInterpolationMode = "monotone";
  Chart.defaults.global.elements.line.lineTension = 0;
  $rootScope.device = 'ios'; // loading 

  $rootScope.loading = true;
  $rootScope.controller_id = null;
  $rootScope.status = ''; // set some root variables

  $rootScope.showMainMenu = false;
  $rootScope.showBack = false;
  $rootScope.showHeaderDetails = false;
  $rootScope.showSplash = false; //true;

  $rootScope.hasSensors = false;
  $rootScope.keyboardIsOpen = false;
  $rootScope.pageSlug = '';
  $rootScope.templateClass = '';
  $rootScope.showAdminTemplate = false;
  $rootScope.user = {
    name: '',
    img: API_URL + '../uploads/avatars/default.jpg'
  };

  init = function init() {
    $rootScope.switchLocale($rootScope.locale);
  }; //go to page


  $rootScope.goToPage = function (page) {
    console.log('$rootScope.goToPage: ' + page);
    $location.url(page);
  };

  $rootScope.loadUrl = function (url) {
    //console.log('$rootScope.goToPage: '+page);
    $window.open(url, "_blank");
  };

  $rootScope.sendMail = function (to, subject, body) {
    //console.log('$rootScope.goToPage: '+page);
    $window.open('mailto:' + to + '?subject=' + subject + '&body=' + body, "_self");
  };

  $rootScope.switchLocale = function (locale) {
    if ($rootScope.supportedLocales[locale] != undefined) {
      api.setLocalStoreValue('lang', locale);
      amMoment.changeLocale(locale);
      $rootScope.locale = locale;
      $rootScope.lang = LANG[locale];
      $window.document.title = $rootScope.lang.Site_title;
      console.log('Locale changed to: ' + locale);
      $rootScope.$broadcast('localeChange', locale);
    } else {
      console.log('Locale not available: ' + locale);
    }
  }; //device check


  $rootScope.setDevice = function () {
    if (runsNative()) {
      $rootScope.device = device.platform.toLowerCase() == 'ios' ? 'ios' : 'android';

      if ($rootScope.device == 'android') {} //document.getElementsByTagName('body')[0].className+='android';
      // check for tablet


      if (window.isTablet) {
        $rootScope.mobile = false;
        $rootScope.screenType = 'landscape';
        window.screen.lockOrientation('landscape');
      } else {
        $rootScope.mobile = true;
        $rootScope.screenType = 'mobile';
        window.screen.lockOrientation('portrait');
      }
    } else {
      // browser code
      var width = window.innerWidth;
      $rootScope.mobile = false;
      $rootScope.screenType = 'ipad';

      var isMobile = function isMobile() {
        var check = false;

        (function (a) {
          if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) check = true;
        })(navigator.userAgent || navigator.vendor || window.opera);

        return check;
      };

      if (isMobile()) {
        $rootScope.mobile = true;
        $rootScope.screenType = 'mobile';
      }
    }

    console.info('mobile=' + $rootScope.mobile, $rootScope.screenType);
    $rootScope.$broadcast('screenSizeChange');
    $rootScope.$digest();
  };

  $rootScope.setDevice(); // add the resize listener

  $window.addEventListener('resize', $rootScope.setDevice); // listen to the colorwheel event

  document.addEventListener('colorwheel.select', function (e) {
    $rootScope.$apply(function () {
      $rootScope.$broadcast('colorwheelSelect', e);
    });
  }); //api.reset();

  $rootScope.logout = function () {
    // check if we want to do this native
    if (runsNative()) {
      navigator.notification.confirm("Weet u zeker dat u wilt uitloggen", $scope.doLogout, "Uitloggen", ["Uitloggen", "Cancel"]);
    } else {
      $rootScope.doLogout(1);
    }
  };

  $rootScope.doLogout = function (index) {
    if (index > 1) return; // remove the data

    $rootScope.$broadcast('reset'); // redirect to the login

    $location.path('/login');
  }; // check if we have an api token


  $rootScope.checkToken = function () {
    if ($routeParams.token != undefined) {// stay at reset password page
    } else if (api.getApiToken() == null) {
      // redirect to login
      console.log('$rootScope.checkToken: no token -> login');
      $location.path('/login');
    } else {
      // fetch the settings
      console.log('$rootScope.checkToken: token available');
      settings.fetchSettings(); //$location.path('/locations');
    }
  };

  $rootScope.checkPolicy = function (e, data) {
    if ($rootScope.user.policy_accepted != $rootScope.lang.policy_version) {
      //console.log($rootScope.user);
      $location.path('/user/edit');
      $rootScope.showMessage($rootScope.lang.approve_policy);
    }
  };

  $rootScope.$on('userUpdated', $rootScope.checkPolicy);
  setTimeout(function () {
    $rootScope.$apply(function () {
      $rootScope.loading = false;
      $rootScope.checkToken();
    });
  }, 200); // check if we want header details

  $rootScope.$on('$routeChangeSuccess', function () {
    // reset the vars
    $rootScope.showBack = false;
    $rootScope.showHeaderDetails = true; // get the path

    var p = $location.path();
    var slug = p.split('/')[1];
    $rootScope.pageSlug = slug;
    $rootScope.defineTemplateClass(slug); // hide the details

    if (slug == 'login' || slug == 'settings') {
      // show the backbutton
      if (p == '/login/create') {
        $rootScope.showBack = true;
      }

      $rootScope.showHeaderDetails = false;
    }

    $window.scrollTo(0, 0);
  });

  $rootScope.defineTemplateClass = function (slug) {
    var className = '';
    var showAdmin = false;

    if ($rootScope.showSplash) {
      className = 'splash';
    } else {
      switch (slug) {
        case 'create':
          className = 'register-page';
          break;

        case 'login':
        case 'reminder':
        case 'reset':
        case 'logout':
          className = 'login-page';
          break;

        default:
          showAdmin = true;
      }

      className += $rootScope.mobile ? ' layout-top-nav' : ' fixed';
    }

    $rootScope.showAdminTemplate = showAdmin;
    $rootScope.templateClass = className;
  }; // switch to a menu item


  $rootScope.switchMenu = function (e, doLink, link) {
    // check if we want to link
    doLink = typeof doLink !== 'undefined' ? doLink : false;
    e.preventDefault();

    if (doLink) {
      $location.search('success', null);
      $location.path(link);
    } // switch the class


    $rootScope.showMainMenu = $rootScope.showMainMenu == false ? true : false;
  }; //close menu overlay


  $rootScope.closeMenu = function () {
    // switch the class
    $rootScope.showMainMenu = $rootScope.showMainMenu == false ? true : false;
  }; // $rootScope.scrollToView = function(view)
  // {
  //     setTimeout(function()
  //     {
  //         $rootScope.$apply(function()
  //         {
  //             var element = document.querySelector('#view-'+view);
  //             var options = 
  //             {
  //                 duration    : 100,
  //                 easing      : 'easeOutCubic',
  //                 offset      : 0,
  //                 containerId : 'view-container',
  //                 direction   : 'horizontal',
  //             }
  //             smoothScroll(element, options);
  //         });
  //     }, 0);
  // };


  $rootScope.loginStatus = ''; // basic history function 

  $rootScope.history = [];
  $rootScope.$on('$routeChangeSuccess', function () {
    if ($location.path().indexOf('/locations') > -1) $rootScope.history = [];
    $rootScope.history.push($location.path());
  });

  $rootScope.back = function () {
    $rootScope.$broadcast('backbutton');
  };

  $rootScope.historyBack = function () {
    $window.history.back();
  }; // handle the native backbutton


  $rootScope.handleBackButton = function () {
    document.addEventListener("backbutton", function (e) {
      // prevent default
      e.preventDefault(); // apply

      $rootScope.$apply(function () {
        $rootScope.$broadcast('backbutton');
      });
    });
  };

  $rootScope.handleBackButton(); //***************/

  /*   MESSAGES   */

  /***************/

  $rootScope.showMessage = function (message, callback, title, buttonName) {
    title = title || "";
    buttonName = buttonName || 'OK';

    if (navigator.notification && navigator.notification.alert) {
      navigator.notification.alert(message, // message
      callback, // callback
      title, // title
      buttonName // buttonName
      );
    } else {
      window.alert(message);

      if (callback != null) {
        callback();
      }
    }
  };

  $rootScope.showConfirm = function (message, callbackOk, callbackVariable, callbackCancel) {
    if (navigator.notification && navigator.notification.confirm) {
      navigator.notification.confirm(message, // message
      callback, // callback
      title, // title
      buttonNames // buttonNames
      );
    } else {
      var c = window.confirm(message);
      if (c && typeof callbackOk == 'function') callbackOk(callbackVariable);else if (typeof callbackCancel == 'function') callbackCancel(callbackVariable);
    }
  };
  /***************/

  /*    FORMS    */

  /***************/


  $rootScope.validateFields = function (inputs, form, fields) {
    var valid = true;
    var error = null;

    for (var i in inputs) {
      if (form[i] != undefined && !form[i].$valid) {
        var required = !!form[i].$error.required;
        var email = !!form[i].$error.email;
        var password = !!form[i].$error.passwordMatch;
        var msg = '';

        if (required) {
          msg = $rootScope.lang.empty_fields;
        } else if (email) {
          msg = $rootScope.lang.no_valid_email;
        } else if (password) {
          msg = $rootScope.lang.match_passwords;
        }

        fields[i] = true;
        error = {
          show: true,
          resultType: 'error',
          resultMessage: msg
        };
        valid = false;
      }
    } // check if its valid


    if (!valid) return error;
    return true;
  };
  /***************/

  /*   LOADING   */

  /***************/
  // set the basic loading listeners


  $rootScope.$on('startLoading', function (e, args) {
    $rootScope.loading = true;
  }); // set the basic loading listeners

  $rootScope.$on('endLoading', function () {
    $rootScope.loading = false; //console.log('endLoading');
  });
});
/* Load angular when our device is ready */

var onDeviceReady = function onDeviceReady() {
  // bootstrap angular
  angular.bootstrap(document.querySelector("body#app"), ["app"]); // check for cordova

  if (runsNative()) {
    cordova.plugins.Keyboard.disableScroll(true);
  }

  init();
};
/* check if we're running an app or development version */


window.onload = function () {
  var app = document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1;

  if (app) {
    document.addEventListener("deviceready", onDeviceReady, false);
  } else {
    onDeviceReady();
  }
};
/*
 * Bee Monitor
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */


app.config(['$routeProvider', '$locationProvider', function ($routeProvider) {
  $routeProvider // logout
  .when('/logout', {
    controller: 'UserCtrl',
    templateUrl: '/app/views/forms/logout.html'
  }).when('/login', {
    controller: 'UserCtrl',
    templateUrl: '/app/views/forms/login.html?v=1'
  }) // login/create
  .when('/login/create', {
    controller: 'UserCtrl',
    templateUrl: '/app/views/forms/user/create.html'
  }) // login/reminder
  .when('/login/reminder', {
    controller: 'PasswordCtrl',
    templateUrl: '/app/views/forms/user/reminder.html'
  }) // login/reset
  .when('/login/reset/:token', {
    controller: 'PasswordCtrl',
    templateUrl: '/app/views/forms/user/reset.html'
  }) // login/reset
  .when('/login/reset', {
    controller: 'PasswordCtrl',
    templateUrl: '/app/views/forms/user/reset.html'
  }).when('/user/edit', {
    controller: 'UserCtrl',
    templateUrl: '/app/views/user.html'
  }) // load
  .when('/load', {
    controller: 'LoadCtrl',
    templateUrl: '/app/views/loading.html'
  }) // overview
  .when('/measurements/:sensorId', {
    controller: 'MeasurementsCtrl',
    templateUrl: '/app/views/measurements.html?v=2'
  }).when('/measurements', {
    controller: 'MeasurementsCtrl',
    templateUrl: '/app/views/measurements.html?v=2'
  }) // locations
  // .when('/locations/:locationId/inspect',
  // {
  //     controller  : 'InspectionCreateCtrl',
  //     templateUrl : '/app/views/inspect.html',
  // })
  .when('/locations/:locationId/edit', {
    controller: 'LocationsCtrl',
    templateUrl: '/app/views/location_edit.html'
  }).when('/locations/create', {
    controller: 'LocationsCtrl',
    templateUrl: '/app/views/forms/location_create.html'
  }).when('/locations', {
    controller: 'LocationsCtrl',
    templateUrl: '/app/views/locations.html'
  }) // hives
  .when('/hives/create', {
    controller: 'HivesCtrl',
    templateUrl: '/app/views/hive_edit.html?v=3'
  }).when('/hives/:hiveId/inspect', {
    controller: 'InspectionCreateCtrl',
    templateUrl: '/app/views/inspect.html?v=6'
  }).when('/hives/:hiveId/inspections/:inspectionId', {
    controller: 'InspectionCreateCtrl',
    templateUrl: '/app/views/inspect.html?v=6'
  }).when('/hives/:hiveId/inspections', {
    controller: 'InspectionsCtrl',
    templateUrl: '/app/views/inspections.html?v=4'
  }).when('/hives/:hiveId/edit', {
    controller: 'HivesCtrl',
    templateUrl: '/app/views/hive_edit.html?v=3'
  }).when('/hives', {
    controller: 'HivesCtrl',
    templateUrl: '/app/views/hives.html?v=3'
  }) // groups
  .when('/groups', {
    controller: 'GroupsCtrl',
    templateUrl: '/app/views/groups.html?v=2'
  }).when('/groups/create', {
    controller: 'GroupsCtrl',
    templateUrl: '/app/views/group_edit.html?v=2'
  }).when('/groups/:groupId/token/:token', {
    controller: 'GroupsCtrl',
    templateUrl: '/app/views/group_edit.html?v=2'
  }).when('/groups/:groupId/edit', {
    controller: 'GroupsCtrl',
    templateUrl: '/app/views/group_edit.html?v=2'
  }).when('/groups/:groupId/inspections', {
    controller: 'InspectionsCtrl',
    templateUrl: '/app/views/inspections.html?v=4'
  }) // checklist
  .when('/checklist/:checklistId/edit', {
    controller: 'ChecklistCtrl',
    templateUrl: '/app/views/checklist.html'
  }) // sensors
  .when('/sensors', {
    controller: 'SensorsCtrl',
    templateUrl: '/app/views/sensors.html'
  }) // settings
  .when('/settings', {
    controller: 'SettingsCtrl',
    templateUrl: '/app/views/forms/settings.html?v=2'
  }).when('/export', {
    controller: 'ExportCtrl',
    templateUrl: '/app/views/export.html'
  }) // none...
  .otherwise({
    redirectTo: '/load'
  });
}]);
/*
 * Beep - Translations
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

LANG['nl'] = (_LANG$nl = {
  /* Date picker */
  monthsFull: ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
  monthsShort: ['jan', 'feb', 'maa', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
  weekdaysFull: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
  weekdaysShort: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
  Today: 'Vandaag',
  Clear: 'Verwijderen',
  Close: 'Sluiten',
  firstDay: 1,
  format: 'dddd d mmmm yyyy',

  /* main */
  Website: 'Website',
  Feedback: 'Feedback',
  Feedback_mail_header: 'Beep app feedback',
  Feedback_mail_body: 'Beste Stichting Beep,%0D%0A%0D%0AHierbij mijn feedback over de Beep app.%0D%0A%0D%0AIk kwam het volgende tegen:%0D%0A%0D%0AVoordat dit gebeurde deed ik het volgende:%0D%0A%0D%0AHet beeld zag er zo uit (graag screenshot meesturen):%0D%0A%0D%0A%0D%0A%0D%0A',
  Diagnostic_info: 'Diagnostische informatie (voor het traceren van evt. problemen):%0D%0A',
  back: 'Terug',
  menu: 'Menu',
  lighting: 'Sfeerverlichting',
  camera: 'Camera',
  weather: 'Weer',
  sensors: 'Sensoren',
  sensors_na: 'Hier komt straks een overzicht van jouw BEEP sensoren, waarmee je kasten op afstand kunt monitoren. Je kunt ook sensoren van andere partijen koppelen, mits ze een API hebben, waarmee je de data kunt uitlezen.',
  no_valid_authentication: 'Geen geldige login ontvangen',
  succesfully_saved: 'Succesvol opgeslagen',
  switch_language: 'Andere taal',
  Delete: 'Verwijderen',
  Search: 'Zoeken...',

  /* user error messages */
  User: 'Gebruiker',
  User_data: 'Gebruikersgegevens',
  user_data: 'gebruikersgegevens',
  updated: 'aangepast',
  delete_complete_account: 'Weet je zeker dat je je volledige account, inclusief alle bijenstaden, kasten en inspecties wilt verwijderen? Dit is niet terug te draaien.',
  username_is_required: 'Vul een gebruikersnaam in.',
  username_already_exists: 'De gebruikersnaam is al in gebruik',
  password_is_required: 'Vul een wachtwoord in.',
  email_is_required: 'Vul een e-mailadres in',
  email_already_exists: 'Het e-mailadres is al in gebruik',
  'policy accepted_is_required': 'Om te registreren, dien je de Servicevoorwaarden te accepteren',
  already_registered: 'Ik heb al een account',
  invalid_user: 'Gebruikersnaam of wachtwoord incorrect',
  invalid_password: 'Wachtwoord te kort (min. 8 tekens)',
  no_password_match: 'De wachtwoorden komen niet overeen',
  invalid_token: 'Ongeldige code',
  no_valid_email: 'Ongeldig e-mailadres',
  empty_fields: 'U heeft niet alle velden goed ingevuld.',
  match_passwords: 'De wachtwoorden komen niet overeen.',
  succesfully_registered: 'Je bent succesvol geregistreerd.',
  authentication_failed: 'Inloggen niet gelukt',
  no_valid_input_received: 'Data kon niet worden opgeslagen, geen geldige gegevens.',
  remove_all_settings: 'Verwijder app data',
  remove_apiary: 'Verwijder bijenstand',
  remove_hive: 'Verwijder kast',
  remove_inspection: 'Verwijder inspectie',
  Error: 'Fout',
  Warning: 'Let op',
  first_remove_hives: 'Let op: er zijn nog kasten op deze bijenstand aanwezig. U kunt specifieke kasten (en hun inspecties) bewaren door ze eerst te verplaatsen naar een andere bijenstand. Als u doorgaat met verwijderen, verwijdert u ALLE kasten en inspecties die op deze locatie aanwezig zijn.',
  Date: 'Datum',
  ok: 'Ok',
  previous: 'Vorige',
  prev: 'vorige',
  next: 'Volgende',
  add: 'Toevoegen',
  create_new: 'Maak een nieuwe',
  New: 'Nieuwe',
  warning: 'Let op',
  apply: 'Toepassen',
  Cancel: 'Annuleren',
  automatic: 'Automatisch',
  manually: 'Handmatig',
  on: 'Aan',
  off: 'Uit',

  /* login */
  login_title: 'Inloggen',
  login: 'Aanmelden',
  back_to_login: 'Terug naar inloggen',
  forgot_password: 'Wachtwoord vergeten?',
  username: 'Gebruikersnaam',
  password: 'Wachtwoord',
  confirm_password: 'Bevestig wachtwoord',
  email: 'E-mail',
  token: 'Code',
  create_login_question: 'Nog geen account? Registreer als een nieuwe gebruiker',
  create_login: 'Registreer als een nieuwe gebruiker',
  create_login_summary: 'Creeër een nieuw account',
  save: 'Opslaan',
  save_and_return: 'Opslaan en terug',
  logout: 'Uitloggen',
  logout_title: 'Uitloggen als ',
  logout_now: 'Weet je zeker dat je wil uitloggen?',
  member_since: 'Beept sinds',

  /* password recovery */
  password_recovery_title: 'Wachtwoord vergeten?',
  password_recovery_remembered: 'Oh wacht, ik weet mijn wachtwoord weer!',
  password_recovery_user: 'Gebruikersinformatie',
  password_recovery_send_mail: 'Verstuur code',
  password_recovery_code_not_received: 'Code niet ontvangen binnen 5 minuten?',
  password_recovery_enter_code: 'Voer de ontvangen code in',
  password_recovery_reset_title: 'Stel een nieuw wachtwoord in',
  password_recovery_reset_password: 'Verander wachtwoord',
  password_recovery_reminder_success: 'Er is een e-mail verstuurd, klik op de link in de e-mail om uw wachtwoord opnieuw in te stellen.',
  password_recovery_reminder_summary: 'Vul je e-mailadres in. Je ontvangt vervolgens een link waarmee je een nieuw wachtwoord kunt instellen in de volgende stap.',
  password_recovery_reset_summary: 'Gebruik de ontvangen code om een nieuw wachtwoord voor je account in te stellen',
  password_recovery_reset_success: 'Je wachtwoord is succesvol aangepast, je bent nu ingelogd.',
  new_password: 'Nieuw wachtwoord',
  confirm_new_password: 'Bevestig nieuw wachtwoord',
  go_to_dashboard: 'Ga direct naar het overzicht',

  /* overview */
  overview_title: 'Overzicht',
  overview: 'Overzicht',
  color: 'Kleur',
  state: 'Stand',
  climate: 'Klimaatregeling',
  plant_state: 'Status planten',
  connection_state: 'Status verbinding',

  /* hives */
  locations_title: 'Beep',
  hives_title: 'Beep',
  Hive: 'Bijenkast',
  hive: 'bijenkast',
  Location: 'Bijenstand',
  location: 'bijenstand',
  Hives: 'Bijenkasten',
  hives: 'Bijenkasten',
  Locations: 'Bijenstanden',
  locations: 'Bijenstanden',
  Name: 'Naam',
  name: 'naam',
  Type: 'Type',
  type: 'type',
  Layer: 'Laag',
  layer: 'laag',
  brood: 'Broed',
  honey: 'Honing',
  inspect: 'Inspecteren',
  inspection: 'inspectie',
  Inspection: 'Inspectie',
  Inspections: 'Inspecties',
  New_inspection: 'Nieuwe inspectie',
  Edit_inspection: 'Inspectie aanpassen',
  Actions: 'Acties',
  Conditions: 'Bevindingen (geïnspecteerd)',
  edit: 'Aanpassen',
  Hive_brood_layers: 'Broedkamers',
  Hive_honey_layers: 'Honingkamers',
  Hive_layer_amount: 'Aantal kamers',
  Bee_race: 'Bijenras',
  Birth_date: 'Geboortedatum',
  Color: 'Kleur',
  Queen_colored: 'Moer gemerkt',
  Queen_clipped: 'Moer geknipt',
  Queen_fertilized: 'Moer bevrucht',
  Age: 'Leeftijd',
  year: 'jaar oud',

  /* Hive check items */
  Date_of_inspection: 'Inspectiedatum',
  reminder: 'Herinnering',
  remind_date: 'Herinneringsdatum',
  condition: 'Inspectie',
  overall: 'Algemeen',
  positive_impression: 'Totaalindruk',
  needs_attention: 'Extra aandacht nodig',
  notes: 'Notities',
  notes_for_next_inspection: 'Korte notitie voor volgende inspectie (zichtbaar in overzicht)',
  Not_implemented_yet: 'Dit item is nog niet geïmplementeerd',
  save_input_first: 'Wil je je ingevoerde gegevens eerst opslaan?',

  /* dashboard */
  dashboard_title: 'Dashboard',
  dashboard: 'Dashboard',
  measurements: 'Metingen',
  measurementsError: 'Kan geen metingen laden, controleer de netwerkverbinding',
  last_measurement: 'Laatste meetwaarde',
  at: 'op',
  measurement_system: 'Beep meetsysteem',
  no_data: 'Geen data beschikbaar',
  no_chart_data: 'Geen sensordata beschikbaar voor de geselecteerde periode',

  /* settings */
  General: 'Algemeen'
}, _defineProperty(_LANG$nl, "Location", 'Locatie'), _defineProperty(_LANG$nl, "Country", 'Land'), _defineProperty(_LANG$nl, "City", 'Stad'), _defineProperty(_LANG$nl, "Address", 'Adres'), _defineProperty(_LANG$nl, "Lattitude", 'Lengtegraad'), _defineProperty(_LANG$nl, "Longitude", 'Breedtegraad'), _defineProperty(_LANG$nl, "Street", 'Straat'), _defineProperty(_LANG$nl, "Number", 'Nr.'), _defineProperty(_LANG$nl, "Postal_code", 'Postcode'), _defineProperty(_LANG$nl, "Description", 'Beschrijving'), _defineProperty(_LANG$nl, "Hive_settings", 'Bijenkast instellingen'), _defineProperty(_LANG$nl, "Hive_amount", 'Aantal kasten op deze locatie'), _defineProperty(_LANG$nl, "Hive_prefix", 'Kastnaam voorvoegsel (vòòr nummer)'), _defineProperty(_LANG$nl, "Hive_number_offset", 'Startnummer kasten'), _defineProperty(_LANG$nl, "Hive_type", 'Kasttype'), _defineProperty(_LANG$nl, "Hive_layers", 'Kamers per kast'), _defineProperty(_LANG$nl, "Hive_frames", 'Ramen per kamer'), _defineProperty(_LANG$nl, "Hive_color", 'Kastkleur'), _defineProperty(_LANG$nl, "Queen", 'Moer'), _defineProperty(_LANG$nl, "queen", 'moer'), _defineProperty(_LANG$nl, "settings_title", 'Instellingen overzicht'), _defineProperty(_LANG$nl, "settings_description", 'Overzicht van de account instellingen'), _defineProperty(_LANG$nl, "settings", 'Instellingen'), _defineProperty(_LANG$nl, "sensors_title", 'Sensorinstellingen'), _defineProperty(_LANG$nl, "sensors_description", 'Sensoren status en registratie'), _defineProperty(_LANG$nl, "sensors", 'Sensoren'), _defineProperty(_LANG$nl, "sensor", 'Sensor'), _defineProperty(_LANG$nl, "Select", 'Selecteer'), _defineProperty(_LANG$nl, "Not_selected", 'Niet geselecteerd'), _defineProperty(_LANG$nl, "Poor", 'Slecht'), _defineProperty(_LANG$nl, "Fair", 'Matig'), _defineProperty(_LANG$nl, "Average", 'Gemiddeld'), _defineProperty(_LANG$nl, "Good", 'Goed'), _defineProperty(_LANG$nl, "Excellent", 'Zeer goed'), _defineProperty(_LANG$nl, "Low", 'Laag'), _defineProperty(_LANG$nl, "Medium", 'Gemiddeld'), _defineProperty(_LANG$nl, "High", 'Hoog'), _defineProperty(_LANG$nl, "Extreme", 'Extreem'), _defineProperty(_LANG$nl, "select_color", 'Selecteer een kleur'), _defineProperty(_LANG$nl, "advanced", 'Geavanceerd'), _defineProperty(_LANG$nl, "Select_sensor", 'Selecteer een sensor'), _defineProperty(_LANG$nl, "t", 'Temperatuur'), _defineProperty(_LANG$nl, "temperature", 'Temperatuur'), _defineProperty(_LANG$nl, "l", 'Zonlicht'), _defineProperty(_LANG$nl, "light", 'Zonlicht'), _defineProperty(_LANG$nl, "water", 'Water'), _defineProperty(_LANG$nl, "w", 'Water'), _defineProperty(_LANG$nl, "humidity", 'Luchtvochtigheid'), _defineProperty(_LANG$nl, "h", 'Luchtvochtigheid'), _defineProperty(_LANG$nl, "air_pressure", 'Luchtdruk'), _defineProperty(_LANG$nl, "p", 'Luchtdruk'), _defineProperty(_LANG$nl, "weight", 'Gewicht'), _defineProperty(_LANG$nl, "w_v", 'Gewicht sensorwaarde gecombineerd'), _defineProperty(_LANG$nl, "w_fl", 'Gewicht sensorwaarde links voor'), _defineProperty(_LANG$nl, "w_fr", 'Gewicht sensorwaarde rechts voor'), _defineProperty(_LANG$nl, "w_bl", 'Gewicht sensorwaarde links achter'), _defineProperty(_LANG$nl, "w_br", 'Gewicht sensorwaarde rechts achter'), _defineProperty(_LANG$nl, "weight_kg", 'Gewicht'), _defineProperty(_LANG$nl, "weight_kg_corrected", 'Gewicht (corr)'), _defineProperty(_LANG$nl, "weight_combined_kg", 'Gewicht combi'), _defineProperty(_LANG$nl, "bat_volt", 'Batterij'), _defineProperty(_LANG$nl, "bv", 'Batterij'), _defineProperty(_LANG$nl, "sound_fanning_4days", 'Vent 4d bijen'), _defineProperty(_LANG$nl, "s_fan_4", 'Vent 4d bijen'), _defineProperty(_LANG$nl, "sound_fanning_6days", 'Vent 6d bijen'), _defineProperty(_LANG$nl, "s_fan_6", 'Vent 6d bijen'), _defineProperty(_LANG$nl, "sound_fanning_9days", 'Vent 9d bijen'), _defineProperty(_LANG$nl, "s_fan_9", 'Vent 9d bijen'), _defineProperty(_LANG$nl, "sound_flying_adult", 'Vlieggeluid'), _defineProperty(_LANG$nl, "s_fly_a", 'Vlieggeluid'), _defineProperty(_LANG$nl, "sound_total", 'Totaal geluid'), _defineProperty(_LANG$nl, "s_tot", 'Totaal geluid'), _defineProperty(_LANG$nl, "bee_count_in", 'Bijen naar binnen'), _defineProperty(_LANG$nl, "bc_i", 'Bijen naar binnen'), _defineProperty(_LANG$nl, "bee_count_out", 'Bijen naar buiten'), _defineProperty(_LANG$nl, "bc_o", 'Bijen naar buiten'), _defineProperty(_LANG$nl, "t_i", 'Temp. in kast'), _defineProperty(_LANG$nl, "rssi", 'Zendsterkte'), _defineProperty(_LANG$nl, "snr", 'Zendruis'), _defineProperty(_LANG$nl, "lat", 'Noorderbreedte'), _defineProperty(_LANG$nl, "lon", 'Oosterlengte'), _defineProperty(_LANG$nl, "Sound_measurements", 'Geluidsmetingen'), _defineProperty(_LANG$nl, "Sensor_info", 'Sensorinformatie'), _defineProperty(_LANG$nl, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$nl, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$nl, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$nl, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$nl, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$nl, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$nl, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$nl, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$nl, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$nl, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$nl, "hour", 'Uur'), _defineProperty(_LANG$nl, "day", 'Dag'), _defineProperty(_LANG$nl, "week", 'Week'), _defineProperty(_LANG$nl, "month", 'Maand'), _defineProperty(_LANG$nl, "year", 'Jaar'), _defineProperty(_LANG$nl, "could_not_load_settings", 'De instellingen konden niet worden geladen'), _defineProperty(_LANG$nl, "offline", 'Geen verbinding'), _defineProperty(_LANG$nl, "remote", 'Op afstand'), _defineProperty(_LANG$nl, "connected", 'Direct'), _defineProperty(_LANG$nl, "yes", 'Ja'), _defineProperty(_LANG$nl, "no", 'Nee'), _defineProperty(_LANG$nl, "footer_text", 'Open source bijenmonitor'), _defineProperty(_LANG$nl, "beep_foundation", 'Stichting BEEP'), _defineProperty(_LANG$nl, "Checklist", 'Kastkaart'), _defineProperty(_LANG$nl, "Checklist_items", 'Kastkaartelementen'), _defineProperty(_LANG$nl, "edit_hive_checklist", 'Vink items in de onderstaande lijst van beschikbare kastkaartitems aan/uit om ze aan je eigen kastkaart toe te voegen/te verwijderen. Voor meer overzicht, kun je de categorieën in- en uitklappen. Ook kun je ze naar boven/beneden slepen om de volgorde van jouw kastkaart te bepalen. Tip: Als je in het zoekveld een term invult, worden alle items die de zoekterm bevatten rood en klappen ze uit.'), _defineProperty(_LANG$nl, "Data_export", 'Data exporteren'), _defineProperty(_LANG$nl, "Export_your_data", 'Exporteer alle data die is opgeslagen in je Beep account en verstuur deze in een e-mail met als bijlage een Excel (.xslx) bestand. Het bestand heeft meerdere tabbladen met daarop je persoonlijke-, bijenstand-, kast- en inspectiegegevens.'), _defineProperty(_LANG$nl, "Terms_of_use", 'Servicevoorwaarden'), _defineProperty(_LANG$nl, "accept_policy", 'Ik accepteer de BEEP servicevoorwaarden, die in lijn zijn met de nieuwe Europese privacywetgeving'), _defineProperty(_LANG$nl, "policy_url", 'https://beep.nl/servicevoorwaarden'), _defineProperty(_LANG$nl, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$nl, "approve_policy", 'Je hebt nog geen akkoord gegeven op de aangepaste gebruikersvoorwaarden.'), _defineProperty(_LANG$nl, "calibrate_weight", 'Gewicht calibreren'), _defineProperty(_LANG$nl, "calibrate_explanation", 'Gewicht van de sensoren bij de volgende meting op 0 zetten door de huidige waarde ervanaf te trekken.'), _defineProperty(_LANG$nl, "set_as_zero_value", 'Stel deze waarde(n) in als 0-waarde(n)'), _defineProperty(_LANG$nl, "set_weight_factor", 'Gewichtsfactor bepalen'), _defineProperty(_LANG$nl, "own_weight_kg", 'Wat is je eigen gewicht in kg?'), _defineProperty(_LANG$nl, "start_calibration", 'Stap nu op de weegschaal en druk de onderstaande knop in zodra je er op staat. Verdeel je gewicht gelijkmatig.'), _defineProperty(_LANG$nl, "currently_there_is", 'Er staat nu'), _defineProperty(_LANG$nl, "nothing", 'niets'), _defineProperty(_LANG$nl, "on_the_scale", 'op de weegschaal'), _defineProperty(_LANG$nl, "calibration_started", 'Calibratie gestart... Wacht op de volgende meting.'), _defineProperty(_LANG$nl, "calibration_ended", 'Calibratie geslaagd!'), _defineProperty(_LANG$nl, "server_down", 'De app is tijdelijk niet beschikbaar door onderhoud, probeer het later opnieuw'), _defineProperty(_LANG$nl, "add_to_calendar", 'Zet in agenda'), _defineProperty(_LANG$nl, "sort_on", 'Sorteer op'), _defineProperty(_LANG$nl, "Whats_new", 'Nieuw in v2.1!'), _defineProperty(_LANG$nl, "Manual", 'Handleiding'), _defineProperty(_LANG$nl, "Site_title", 'BEEP | Bijenmonitor'), _defineProperty(_LANG$nl, "could_not_create_user", 'Gebruiker kan op dit moment niet aangemaakt worden, probeer het a.u.b. later opnieuw.'), _defineProperty(_LANG$nl, "email_verified", 'Je e-mail adres is gevalideerd.'), _defineProperty(_LANG$nl, "email_not_verified", 'Je e-mail adres is nog niet gevalideerd.'), _defineProperty(_LANG$nl, "email_new_verification", 'Klik op deze link om een nieuwe validatie e-mail te versturen.'), _defineProperty(_LANG$nl, "email_verification_sent", 'Er is een bericht met een validatie-link naar je e-mail adres gestuurd. Klik op de link in de e-mail om je account te activeren en in te loggen.'), _defineProperty(_LANG$nl, "not_filled", 'is verplicht, maar niet ingevuld'), _defineProperty(_LANG$nl, "cannot_deselect", 'Dit item kan niet worden verwijderd, omdat het een verplicht item bevat'), _defineProperty(_LANG$nl, "sensor_key", 'Sensor code'), _defineProperty(_LANG$nl, "Undelete", 'Niet verwijderen'), _defineProperty(_LANG$nl, "the_field", 'Vul een'), _defineProperty(_LANG$nl, "is_required", 'in'), _defineProperty(_LANG$nl, "No_groups", 'Geen groepen beschikbaar'), _defineProperty(_LANG$nl, "not_available_yet", 'nog niet beschikbaar. Maak de eerste aan.'), _defineProperty(_LANG$nl, "Users", 'Gebruikers'), _defineProperty(_LANG$nl, "Member", 'Groepslid'), _defineProperty(_LANG$nl, "Members", 'Groepsleden'), _defineProperty(_LANG$nl, "Invite", 'Uitnodigen'), _defineProperty(_LANG$nl, "Invited", 'Uitgenodigd'), _defineProperty(_LANG$nl, "invitations", 'uitnodigingen'), _defineProperty(_LANG$nl, "Admin", 'Beheerder'), _defineProperty(_LANG$nl, "Creator", 'Groep eigenaar'), _defineProperty(_LANG$nl, "Groups", 'Samenwerken'), _defineProperty(_LANG$nl, "Group", 'Samenwerkingsgroep'), _defineProperty(_LANG$nl, "group", 'samenwerkingsgroep'), _defineProperty(_LANG$nl, "to_share", 'om te delen met de groep. 1x klikken = delen om te bekijken, 2x klikken is delen met aanpassingsmogelijkheid'), _defineProperty(_LANG$nl, "Invitation_accepted", 'Uitnodiging geaccepteerd'), _defineProperty(_LANG$nl, "Accept", 'Accepteer'), _defineProperty(_LANG$nl, "My_shared", 'Mijn gedeelde'), _defineProperty(_LANG$nl, "invitee_name", 'Naam genodigde'), _defineProperty(_LANG$nl, "Remove_group", 'Weet u zeker dat u deze gedeelde groep voor alle leden wilt verwijderen'), _defineProperty(_LANG$nl, "Detach_from_group", 'Verwijder mij en mijn kasten uit deze groep'), _defineProperty(_LANG$nl, "my_hive", 'Mijn kast'), _defineProperty(_LANG$nl, "created", 'aangemakt'), _defineProperty(_LANG$nl, "group_detached", 'Succesvol uit de groep gestapt'), _defineProperty(_LANG$nl, "group_activated", 'Groepsuitnodiging geaccepteerd'), _defineProperty(_LANG$nl, "group_explanation_1", '1. Maak een nieuwe samenwerkingsgroep aan met een duidelijke titel en evt. beschrijving'), _defineProperty(_LANG$nl, "group_explanation_2", '2. Nodig andere Beep gebruikers uit op hun Beep e-mail adres'), _defineProperty(_LANG$nl, "group_explanation_3", '3. Deel specifieke kasten om te bekijken, of om samen aan te werken'), _defineProperty(_LANG$nl, "Filter_and_sort_on", 'Filter en sorteer op:'), _LANG$nl);
/*
 * Beep - Translations
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

LANG['en'] = (_LANG$en = {
  /* Date picker */
  monthsFull: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
  monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  weekdaysFull: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  weekdaysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
  Today: 'Today',
  Clear: 'Clear',
  Close: 'Close',
  firstDay: 1,
  format: 'dddd d mmmm yyyy',

  /* main */
  Website: 'Website',
  Feedback: 'Feedback',
  Feedback_mail_header: 'Beep app feedback',
  Feedback_mail_body: 'Dear Beep foundation,%0D%0A%0D%0AHereby my feedback about the Beep app.%0D%0A%0D%0AI discovered this:%0D%0A%0D%0AJust before that happened, i did:%0D%0A%0D%0AThe screen was looking like (please include a screenshot):%0D%0A%0D%0A%0D%0A%0D%0A',
  Diagnostic_info: 'Diagnostic information (in case of a bug):%0D%0A',
  back: 'Back',
  menu: 'Menu',
  lighting: 'Lighting',
  camera: 'Camera',
  weather: 'Weather',
  sensors: 'Sensors',
  sensors_na: 'BEEP sensors to remotely monitor your hive are soon to be available...',
  no_valid_authentication: 'No valid authentication data received',
  succesfully_saved: 'Succesfully saved',
  switch_language: 'Switch language',
  Delete: 'Delete',
  Search: 'Search...',

  /* user error messages */
  User: 'User',
  User_data: 'User data',
  user_data: 'user data',
  updated: 'updated',
  delete_complete_account: 'Are you sure that you want to delete your full account, inluding all apiaries, hives, and inspections? It is unrecoverable.',
  username_is_required: 'Please enter the username',
  username_already_exists: 'Username already exists',
  password_is_required: 'Please enter a password',
  email_is_required: 'Please enter a e-mailadres',
  email_already_exists: 'The e-mailaddress is already in use',
  'policy accepted_is_required': 'You need to accept the terms of service to register',
  already_registered: 'I am already registered',
  invalid_user: 'Unknown user, or wrong password',
  invalid_password: 'Password too short (min. 8 characters)',
  no_password_match: 'The passwords do not match',
  invalid_token: 'Invalid code',
  no_valid_email: 'Invalid e-mailaddress',
  empty_fields: 'Please fill in all the fields',
  match_passwords: 'Passwords do not match',
  succesfully_registered: 'You are succesfully registered.',
  authentication_failed: 'Failed to authenticate',
  no_valid_input_received: 'Data could not be saved, no valid input received.',
  remove_all_settings: 'Remove all settings',
  remove_apiary: 'Remove apiary',
  remove_hive: 'Remove hive',
  remove_inspection: 'Remove inspection',
  Error: 'Error',
  Warning: 'Warning',
  first_remove_hives: 'Attention: there are still hives at this apiary. You can save specific hives (and their inspections) by first moving them to another apiary. If you continue with the deletion, you will delete ALL hives and inspections present at this location.',
  Date: 'Date',
  ok: 'Ok',
  previous: 'Previous',
  prev: 'previous',
  next: 'Next',
  add: 'Add',
  create_new: 'Create new',
  New: 'New',
  warning: 'Warning',
  apply: 'Apply',
  Cancel: 'Cancel',
  automatic: 'Automatic',
  manually: 'Manual',
  on: 'On',
  off: 'Off',

  /* login */
  login_title: 'Login',
  login: 'Login',
  back_to_login: 'Back to login',
  forgot_password: 'Forgot your password?',
  username: 'Username',
  password: 'Password',
  confirm_password: 'Confirm password',
  email: 'E-mail',
  token: 'Code',
  create_login_question: 'No account yet? Register as a new user',
  create_login: 'Register as a new user',
  create_login_summary: 'Create a new user account',
  save: 'Save',
  save_and_return: 'Save and return',
  logout: 'Log out',
  logout_title: 'Log out as ',
  logout_now: 'Do you realy want to log out now?',
  member_since: 'Beeping since',

  /* password recovery */
  password_recovery_title: 'Forgot your password?',
  password_recovery_remembered: 'Oh, now I remembered my password again!',
  password_recovery_user: 'User information',
  password_recovery_send_mail: 'Send verification code',
  password_recovery_code_not_received: 'Code not received within 5 minutes?',
  password_recovery_enter_code: 'Already got a verification code? Enter it here',
  password_recovery_reset_title: 'Enter a new password',
  password_recovery_reset_password: 'Change password',
  password_recovery_reminder_success: 'An e-mail has been sent. Click the link in your e-mail to reset your password for this account.',
  password_recovery_reminder_summary: 'Enter your e-mail address. You will receive an e-mail with a link to change your password in the next step.',
  password_recovery_reset_summary: 'Use the code that you received to set a new password for your account',
  password_recovery_reset_success: 'You passowrd is successfully changed, and you are logged in.',
  new_password: 'New password',
  confirm_new_password: 'Confirm new password',
  go_to_dashboard: 'Go to my dashboard',

  /* overview */
  overview_title: 'Overview',
  overview: 'Overview',
  color: 'Color',
  state: 'On/off',
  connection_state: 'Connection status',

  /* hives */
  locations_title: 'Beep',
  hives_title: 'Beep',
  Hive: 'Hive',
  hive: 'hive',
  Location: 'Apiary',
  location: 'apiary',
  Hives: 'Hives',
  hives: 'Hives',
  Locations: 'Apiaries',
  locations: 'Apiaries',
  Name: 'Name',
  name: 'name',
  Type: 'Type',
  type: 'type',
  Layer: 'Layer',
  layer: 'layer',
  brood: 'Brood',
  honey: 'Honey',
  inspect: 'Inspect',
  inspection: 'inspection',
  Inspection: 'Inspection',
  Inspections: 'Inspections',
  New_inspection: 'New inspection',
  Edit_inspection: 'Edit inspection',
  Actions: 'Actions',
  Conditions: 'Conditions (inspected)',
  edit: 'Edit',
  Hive_brood_layers: 'Brood layers',
  Hive_honey_layers: 'Honey layers',
  Hive_layer_amount: 'Amount of layers',
  Bee_race: 'Bee race',
  Birth_date: 'Birth date',
  Color: 'Color',
  Queen_colored: 'Queen colored',
  Queen_clipped: 'Queen clipped',
  Queen_fertilized: 'Queen fertilized',
  Age: 'Age',
  year: 'years old',

  /* Hive check items */
  Date_of_inspection: 'Date of inspection',
  action: 'Action',
  reminder: 'Remember',
  remind_date: 'Notification date',
  overall: 'Overall',
  positive_impression: 'Total impression',
  needs_attention: 'Needs attention',
  notes: 'Notes',
  notes_for_next_inspection: 'Short note for next inspection (visible on overview)',
  Not_implemented_yet: 'This item is not implemented yet',
  save_input_first: 'Do you want to save your input first?',

  /* dashboard */
  dashboard_title: 'Dashboard',
  dashboard: 'Dashboard',
  measurements: 'Measurements',
  measurementsError: 'Cannot load measurements, check network connection',
  last_measurement: 'Last measurement',
  at: 'at',
  measurement_system: 'Beep measurement system',
  no_data: 'No data available',
  no_chart_data: 'No chart data for the selected period',

  /* settings */
  General: 'General'
}, _defineProperty(_LANG$en, "Location", 'Location'), _defineProperty(_LANG$en, "Country", 'Country'), _defineProperty(_LANG$en, "City", 'City'), _defineProperty(_LANG$en, "Address", 'Address'), _defineProperty(_LANG$en, "Lattitude", 'Lattitude'), _defineProperty(_LANG$en, "Longitude", 'Longitude'), _defineProperty(_LANG$en, "Street", 'Street'), _defineProperty(_LANG$en, "Number", 'No.'), _defineProperty(_LANG$en, "Postal_code", 'Postal code'), _defineProperty(_LANG$en, "Description", 'Description'), _defineProperty(_LANG$en, "Hive_settings", 'Hive settings'), _defineProperty(_LANG$en, "Hive_amount", 'Hive amount at this location'), _defineProperty(_LANG$en, "Hive_prefix", 'Hive name prefix (before numer)'), _defineProperty(_LANG$en, "Hive_number_offset", 'Start number hives'), _defineProperty(_LANG$en, "Hive_type", 'Hive type'), _defineProperty(_LANG$en, "Hive_layers", 'Hive layers'), _defineProperty(_LANG$en, "Hive_frames", 'Frames per layer'), _defineProperty(_LANG$en, "Hive_color", 'Hive color'), _defineProperty(_LANG$en, "Queen", 'Queen'), _defineProperty(_LANG$en, "queen", 'queen'), _defineProperty(_LANG$en, "settings_title", 'Settings'), _defineProperty(_LANG$en, "settings_description", 'Settings of the sensors'), _defineProperty(_LANG$en, "settings", 'Settings'), _defineProperty(_LANG$en, "sensors_title", 'Sensor settings'), _defineProperty(_LANG$en, "sensors_description", 'Sensors status and registration'), _defineProperty(_LANG$en, "sensors", 'Sensors'), _defineProperty(_LANG$en, "sensor", 'Sensor'), _defineProperty(_LANG$en, "Select", 'Select'), _defineProperty(_LANG$en, "Not_selected", 'Not selected'), _defineProperty(_LANG$en, "Poor", 'Poor'), _defineProperty(_LANG$en, "Fair", 'Fair'), _defineProperty(_LANG$en, "Average", 'Average'), _defineProperty(_LANG$en, "Good", 'Good'), _defineProperty(_LANG$en, "Excellent", 'Excellent'), _defineProperty(_LANG$en, "Low", 'Low'), _defineProperty(_LANG$en, "Medium", 'Medium'), _defineProperty(_LANG$en, "High", 'High'), _defineProperty(_LANG$en, "Extreme", 'Extreme'), _defineProperty(_LANG$en, "select_color", 'Select a color'), _defineProperty(_LANG$en, "advanced", 'Advanced'), _defineProperty(_LANG$en, "Select_sensor", 'Select a sensor'), _defineProperty(_LANG$en, "temperature", 'Temperature'), _defineProperty(_LANG$en, "t", 'Temperature'), _defineProperty(_LANG$en, "light", 'Sunlight'), _defineProperty(_LANG$en, "l", 'Sunlight'), _defineProperty(_LANG$en, "water", 'Water'), _defineProperty(_LANG$en, "w", 'Water'), _defineProperty(_LANG$en, "humidity", 'Humidity'), _defineProperty(_LANG$en, "h", 'Humidity'), _defineProperty(_LANG$en, "air_pressure", 'Air pressure'), _defineProperty(_LANG$en, "p", 'Air pressure'), _defineProperty(_LANG$en, "weight", 'Weight'), _defineProperty(_LANG$en, "w_v", 'Weight sensor value all sensors'), _defineProperty(_LANG$en, "w_fl", 'Weight sensor value front left'), _defineProperty(_LANG$en, "w_fr", 'Weight sensor value front right'), _defineProperty(_LANG$en, "w_bl", 'Weight sensor value back left'), _defineProperty(_LANG$en, "w_br", 'Weight sensor value back right'), _defineProperty(_LANG$en, "weight_kg", 'Weight'), _defineProperty(_LANG$en, "weight_kg_corrected", 'Weight (corr)'), _defineProperty(_LANG$en, "weight_combined_kg", 'Weight combi'), _defineProperty(_LANG$en, "bat_volt", 'Battery'), _defineProperty(_LANG$en, "bv", 'Battery'), _defineProperty(_LANG$en, "sound_fanning_4days", 'Fan 4d bees'), _defineProperty(_LANG$en, "s_fan_4", 'Fan 4d bees'), _defineProperty(_LANG$en, "sound_fanning_6days", 'Fan 6d bees'), _defineProperty(_LANG$en, "s_fan_6", 'Fan 6d bees'), _defineProperty(_LANG$en, "sound_fanning_9days", 'Fan 9d bees'), _defineProperty(_LANG$en, "s_fan_9", 'Fan 9d bees'), _defineProperty(_LANG$en, "sound_flying_adult", 'Flying bees'), _defineProperty(_LANG$en, "s_fly_a", 'Flying bees'), _defineProperty(_LANG$en, "sound_total", 'Total sound'), _defineProperty(_LANG$en, "s_tot", 'Total sound'), _defineProperty(_LANG$en, "bee_count_in", 'Bee count in'), _defineProperty(_LANG$en, "bc_i", 'Bee count in'), _defineProperty(_LANG$en, "bee_count_out", 'Bee count out'), _defineProperty(_LANG$en, "bc_o", 'Bee count out'), _defineProperty(_LANG$en, "t_i", 'Temp. inside'), _defineProperty(_LANG$en, "rssi", 'Signal strength'), _defineProperty(_LANG$en, "snr", 'Signal noise'), _defineProperty(_LANG$en, "lat", 'Lattitude'), _defineProperty(_LANG$en, "lon", 'Longitude'), _defineProperty(_LANG$en, "Sound_measurements", 'Sound measurements'), _defineProperty(_LANG$en, "Sensor_info", 'Sensor info'), _defineProperty(_LANG$en, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$en, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$en, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$en, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$en, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$en, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$en, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$en, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$en, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$en, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$en, "hour", 'Hour'), _defineProperty(_LANG$en, "day", 'Day'), _defineProperty(_LANG$en, "week", 'Week'), _defineProperty(_LANG$en, "month", 'Month'), _defineProperty(_LANG$en, "year", 'Year'), _defineProperty(_LANG$en, "could_not_load_settings", 'Settings could not be loaded'), _defineProperty(_LANG$en, "offline", 'No connection'), _defineProperty(_LANG$en, "remote", 'Remote'), _defineProperty(_LANG$en, "connected", 'Direct'), _defineProperty(_LANG$en, "yes", 'Yes'), _defineProperty(_LANG$en, "no", 'No'), _defineProperty(_LANG$en, "footer_text", 'Open source beekeeping'), _defineProperty(_LANG$en, "beep_foundation", 'the BEEP foundation'), _defineProperty(_LANG$en, "Checklist", 'Checklist'), _defineProperty(_LANG$en, "Checklist_items", 'Checklist items'), _defineProperty(_LANG$en, "edit_hive_checklist", 'Check/unckeck the boxes in the list below to add/remove items from your hive checklist. You can also unfold/fold and drag/drop the items to re-order them to your own style. Tip: if you enter a term in the search field, all items containing that term will fold out and color red.'), _defineProperty(_LANG$en, "Data_export", 'Data export'), _defineProperty(_LANG$en, "Export_your_data", 'Export all data that is in your Beep account and send an e-mail cointaining the data as an Excel file. The Excel file has different tabs containing your personal, hive, location, and inspection data.'), _defineProperty(_LANG$en, "Terms_of_use", 'Terms of service'), _defineProperty(_LANG$en, "accept_policy", 'I accept the BEEP terms of service, that are compatible with the new European privacy law'), _defineProperty(_LANG$en, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$en, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$en, "approve_policy", 'You did not yet comply with the latest terms of service.'), _defineProperty(_LANG$en, "calibrate_weight", 'Calibrate weight'), _defineProperty(_LANG$en, "calibrate_explanation", 'Set the weight of the sensors to 0 by subtracting the current measurement value.'), _defineProperty(_LANG$en, "set_as_zero_value", 'Set these values as 0 values'), _defineProperty(_LANG$en, "set_weight_factor", 'Define weight factor'), _defineProperty(_LANG$en, "own_weight_kg", 'What is your own weight in kg?'), _defineProperty(_LANG$en, "start_calibration", 'Now step on the scale, and press the button below to define the weight factor. Distribute your weight equally.'), _defineProperty(_LANG$en, "currently_there_is", 'There is a weight of'), _defineProperty(_LANG$en, "nothing", 'nothing'), _defineProperty(_LANG$en, "on_the_scale", 'on the scale'), _defineProperty(_LANG$en, "calibration_started", 'Calibration started... Wait for the next measurement to take effect.'), _defineProperty(_LANG$en, "calibration_ended", 'Calibration succeeded!'), _defineProperty(_LANG$en, "server_down", 'The app is unavailable due to maintenance work, please try again later'), _defineProperty(_LANG$en, "add_to_calendar", 'Add to calendar'), _defineProperty(_LANG$en, "sort_on", 'Sort on'), _defineProperty(_LANG$en, "Whats_new", 'New in v2.1!'), _defineProperty(_LANG$en, "Manual", 'Manual'), _defineProperty(_LANG$en, "Site_title", 'BEEP | Bee monitor'), _defineProperty(_LANG$en, "could_not_create_user", 'User cannot be created at this moment. Sorry for the inconvenience, please try again later.'), _defineProperty(_LANG$en, "email_verified", 'Your e-mail address has been verified.'), _defineProperty(_LANG$en, "email_not_verified", 'Your e-mail address has not yet been verified.'), _defineProperty(_LANG$en, "email_new_verification", 'Click on this link to send a new verification e-mail.'), _defineProperty(_LANG$en, "email_verification_sent", 'A message with a verification link has been sent to your e-mail address. Click the link in the e-mail to activate your account and log in.'), _defineProperty(_LANG$en, "not_filled", 'is required, but not filled out'), _defineProperty(_LANG$en, "cannot_deselect", 'Unable to remove this item, because it contains a required item'), _defineProperty(_LANG$en, "sensor_key", 'Sensor key'), _defineProperty(_LANG$en, "Undelete", 'Do not delete'), _defineProperty(_LANG$en, "the_field", 'The'), _defineProperty(_LANG$en, "is_required", 'is required'), _defineProperty(_LANG$en, "No_groups", 'No groups available'), _defineProperty(_LANG$en, "not_available_yet", 'not yet available. Please create the first one here.'), _defineProperty(_LANG$en, "Users", 'Users'), _defineProperty(_LANG$en, "Member", 'Group member'), _defineProperty(_LANG$en, "Members", 'Group members'), _defineProperty(_LANG$en, "Invite", 'Invite'), _defineProperty(_LANG$en, "Invited", 'Invited'), _defineProperty(_LANG$en, "invitations", 'invitations'), _defineProperty(_LANG$en, "Admin", 'Administrator'), _defineProperty(_LANG$en, "Creator", 'Group owner'), _defineProperty(_LANG$en, "Groups", 'Collaborate'), _defineProperty(_LANG$en, "Group", 'Collaboration group'), _defineProperty(_LANG$en, "group", 'collaboration group'), _defineProperty(_LANG$en, "to_share", 'to share with this group. 1 click = group members can view only, 2 clicks = group members can edit'), _defineProperty(_LANG$en, "Invitation_accepted", 'Invitation accepted'), _defineProperty(_LANG$en, "Accept", 'Accept'), _defineProperty(_LANG$en, "My_shared", 'My shared'), _defineProperty(_LANG$en, "invitee_name", 'Invitee name'), _defineProperty(_LANG$en, "Remove_group", 'Are you sure you want to competely remove this shared group for all it\'s members'), _defineProperty(_LANG$en, "Detach_from_group", 'Remove me and my hives from this group'), _defineProperty(_LANG$en, "my_hive", 'My hive'), _defineProperty(_LANG$en, "created", 'created'), _defineProperty(_LANG$en, "group_detached", 'Successfully left the group'), _defineProperty(_LANG$en, "group_activated", 'Group invitation accepted'), _defineProperty(_LANG$en, "group_explanation_1", '1. Create a new cooperation group with a clear title, and an optional description'), _defineProperty(_LANG$en, "group_explanation_2", '2. Invite other Beep users on their Beep e-mail address'), _defineProperty(_LANG$en, "group_explanation_3", '3. Share specific hives to be viewed by others, of to cooperate on'), _defineProperty(_LANG$en, "Filter_and_sort_on", 'Filter and sort on:'), _LANG$en);
/* 
* Beep - Translations 
* Author: Pim van Gennip (pim@iconize.nl) 
* 
*/

LANG['de'] = (_LANG$de = {
  /* Date picker */
  monthsFull: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
  monthsShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
  weekdaysFull: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
  weekdaysShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'],
  Today: 'Heute',
  Clear: 'Löschen',
  Close: 'Schließen',
  firstDay: 1,
  format: 'dddd d mmmm yyyy',

  /* main */
  Website: 'Webseite',
  Feedback: 'Feedback',
  Feedback_mail_header: 'Beep app feedback',
  Feedback_mail_body: 'Dear Beep foundation,%0D%0A%0D%0AHereby my feedback about the Beep app.%0D%0A%0D%0AI discovered this:%0D%0A%0D%0AJust before that happened, i did:%0D%0A%0D%0AThe screen was looking like (please include a screenshot):%0D%0A%0D%0A%0D%0A%0D%0A',
  Diagnostic_info: 'Diagnostic information (in case of a bug):%0D%0A',
  back: 'Zurück',
  menu: 'Menü',
  lighting: 'Beleuchtung',
  camera: 'Kamera',
  weather: 'Wetter',
  sensors: 'Sensoren',
  sensors_na: 'BEEP Sensoren zum Anzeigen der Beuten-Daten werden bald verfügbar sein...',
  no_valid_authentication: 'Keine gültigen Authentifizierungsdaten erhalten',
  succesfully_saved: 'Erfolgreich gesichert',
  switch_language: 'Sprache wechseln',
  Delete: 'Löschen',
  Search: 'Suchen...',

  /* user error messages */
  User: 'Benutzer',
  User_data: 'Benutzerdaten',
  user_data: 'Benutzerdaten',
  updated: 'aktualisiert',
  delete_complete_account: 'Bist Du sicher, dass Du Deinen kompletten Account sämtlich aller Daten löschen möchtest? Es ist unwiderruflich',
  username_is_required: 'Bitte Benutzernamen eingeben',
  username_already_exists: 'Der Benutzername existiert bereits',
  password_is_required: 'Bitte gib ein Passwort ein',
  email_is_required: 'Bitte gib eine Email-Adresse an',
  email_already_exists: 'Die Email-Adresse ist bereits in Verwendung',
  'policy accepted_is_required': 'Um Dich zu registrieren, musst Du die Benutzungsbedingungen akzeptieren',
  already_registered: 'Ich bin bereits registriert',
  invalid_user: 'Der Benutzer konnte nicht gefunden werden',
  invalid_password: 'Das Passwort ist zu kurz (min. 8 Zeichen)',
  no_password_match: 'Falsches Passwort',
  invalid_token: 'Falscher Code',
  no_valid_email: 'Falsche Emailadresse',
  empty_fields: 'Bitte alle Felder ausfüllen',
  match_passwords: 'Falsches Passwort',
  succesfully_registered: 'Du wurdest erfolgreich registriert',
  authentication_failed: 'Authentifizierung fehlgeschlagen',
  no_valid_input_received: 'Die Daten konnten nicht gesichert werden- keine gültigen Daten erhalten.',
  remove_all_settings: 'Entferne alle Einstellungen',
  remove_apiary: 'Entferne Bienenstand',
  remove_hive: 'Entferne Beute',
  remove_inspection: 'Entferne Durchsicht',
  Error: 'Fehler',
  Warning: 'Warnung',
  first_remove_hives: 'Da sind bereits Beuten an diesem Ort. Der Ort kann gelöscht werden, wenn alle Beuten an einen anderen Ort transferiert oder gelöscht wurden.',
  Date: 'Datum',
  ok: 'Ok',
  previous: 'Vorherige',
  prev: 'vorherige',
  next: 'Nächste',
  add: 'hinzufügen',
  create_new: 'Neu anlegen:',
  New: 'Neu',
  warning: 'Warnung',
  apply: 'Bestätigen',
  Cancel: 'Abbrechen',
  automatic: 'Automatisch',
  manually: 'Manuel',
  on: 'An',
  off: 'Aus',

  /* login */
  login_title: 'Login',
  login: 'Login',
  back_to_login: 'Zurück zum Login',
  forgot_password: 'Passwort vergessen?',
  username: 'Benutzername',
  password: 'Passwort',
  confirm_password: 'Password bestätigen',
  email: 'Email',
  token: 'Code',
  create_login_question: 'Noch keinen Account? Registriere Dich als neuer Benutzer',
  create_login: 'Als neuer Benutzer registrieren',
  create_login_summary: 'Lege einen neuen Benutzer an',
  save: 'Sichern',
  save_and_return: 'speichern und zurück',
  logout: 'Ausloggen',
  logout_title: 'Ausloggen als ',
  logout_now: 'Willst Du Dich jetzt wirklich ausloggen?',
  member_since: 'BEEPler seit',

  /* password recovery */
  password_recovery_title: 'Hast Du Dein Passwort vergessen?',
  password_recovery_remembered: 'Oh, jetzt erinnere ich mich wieder an mein Passwort!',
  password_recovery_user: 'Benutzer information',
  password_recovery_send_mail: 'Sende Verificationscode',
  password_recovery_code_not_received: 'Verifikationscode nicht innerhalb von 5 minuten erhalten?',
  password_recovery_enter_code: 'Verifikationscode bekommen? Bitte hier eingeben',
  password_recovery_reset_title: 'Gib ein neues Passwort an',
  password_recovery_reset_password: 'Passwort ändern',
  password_recovery_reminder_success: 'Eine Email wurde versendet. Bitte klicke auf den link in Deiner Email um das Passwort für diesen Account zurückzusetzen.',
  password_recovery_reminder_summary: 'Bitte Email-Addresse eingeben. Du wirst eine Email erhalten mit einem Link um Dein Passwort ändern zu können',
  password_recovery_reset_summary: 'Bitte benutze den Code den Du erhalten hast um ein neues Passwort für Deinen Account eingeben zu können',
  password_recovery_reset_success: 'Dein Passwort wurde erfolgreich geändert und Du bist eingeloggt.',
  new_password: 'Neues Passwort',
  confirm_new_password: 'Bestätige das neue Passwort',
  go_to_dashboard: 'Gehe zu meiner Übersichtstabelle',

  /* overview */
  overview_title: 'Übersicht',
  overview: 'Übersicht',
  color: 'Farbe',
  state: 'An/Aus',
  connection_state: 'Status der Verbindung',

  /* hives */
  locations_title: 'Standorte',
  hives_title: 'Beuten',
  Hive: 'Beute',
  hive: 'Beute',
  Location: 'Standort',
  location: 'Standort',
  Hives: 'Beuten',
  hives: 'Beuten',
  Locations: 'Standorte',
  locations: 'Standorte',
  Name: 'Name',
  name: 'Name',
  Type: 'Typ',
  type: 'Typ',
  Layer: 'Zarge',
  layer: 'Zarge',
  brood: 'Brut',
  honey: 'Honig',
  inspect: 'durchsehen',
  inspection: 'Durchsicht',
  Inspection: 'Durchsicht',
  Inspections: 'Durchsichten',
  New_inspection: 'Neue Durchsicht',
  Edit_inspection: 'Bearbeite Durchsicht',
  Actions: 'Bearbeitungen',
  Conditions: 'Bedingungen (geprüft)',
  edit: 'Bearbeite',
  Hive_brood_layers: 'Brutzargen',
  Hive_honey_layers: 'Honigzargen',
  Hive_layer_amount: 'Zargenanzahl',
  Bee_race: 'Bienenrasse',
  Birth_date: 'Geburtstag',
  Color: 'Farbe',
  Queen_colored: 'Königin gezeichnet',
  Queen_clipped: 'Flügel beschnitten',
  Queen_fertilized: 'Königin begattet',
  Age: 'Alter',
  year: 'Jahre alt',

  /* Hive check items */
  Date_of_inspection: 'Datum der Durchsicht',
  action: 'Aktion',
  reminder: 'Erinnerung',
  remind_date: 'Aufzeichnungsdatum',
  overall: 'Im Ganzen',
  positive_impression: 'Gesamteindruck',
  needs_attention: 'braucht Aufmerksamkeit',
  notes: 'Anmerkungen',
  notes_for_next_inspection: 'Kurze Anmerkung für die nächste Durchsicht (in der Übersicht zu sehen)',
  Not_implemented_yet: 'Dieser Punkt ist noch nicht implementiert',
  save_input_first: 'Möchtest Du Deine Eingabe erst sichern?',

  /* dashboard */
  dashboard_title: 'Übersichtstabelle',
  dashboard: 'Übersichtstabelle',
  measurements: 'Messungen',
  measurementsError: 'Kann keine Messungen laden, bitte Netzwerkverbindung prüfen',
  last_measurement: 'Letzte aufgezeichnete Messung war',
  at: 'am',
  measurement_system: 'Beep Meßsystem',
  no_data: 'Kein Data',
  no_chart_data: 'Kein Graph für den gewählten Zeitraum',

  /* settings */
  General: 'Generell'
}, _defineProperty(_LANG$de, "Location", 'Standort'), _defineProperty(_LANG$de, "Country", 'Land'), _defineProperty(_LANG$de, "City", 'Stadt'), _defineProperty(_LANG$de, "Address", 'Addresse'), _defineProperty(_LANG$de, "Lattitude", 'Lattitude'), _defineProperty(_LANG$de, "Longitude", 'Longitude'), _defineProperty(_LANG$de, "Street", 'Straße'), _defineProperty(_LANG$de, "Number", 'Hausnummer.'), _defineProperty(_LANG$de, "Postal_code", 'Postleitzahl'), _defineProperty(_LANG$de, "Description", 'Beschreibung'), _defineProperty(_LANG$de, "Hive_settings", 'Beute Einstellung'), _defineProperty(_LANG$de, "Hive_amount", 'Anzahl der Beuten an diesem Ort'), _defineProperty(_LANG$de, "Hive_prefix", 'Beutenprefix (vor der Zahl)'), _defineProperty(_LANG$de, "Hive_number_offset", 'Startnummer Beute'), _defineProperty(_LANG$de, "Hive_type", 'Beutentyp'), _defineProperty(_LANG$de, "Hive_layers", 'Zargen'), _defineProperty(_LANG$de, "Hive_frames", 'Rähmchen per Zarge'), _defineProperty(_LANG$de, "Hive_color", 'Beutenfarbe'), _defineProperty(_LANG$de, "Queen", 'Königin'), _defineProperty(_LANG$de, "queen", 'Königin'), _defineProperty(_LANG$de, "settings_title", 'Einstellungen'), _defineProperty(_LANG$de, "settings_description", 'Einstellungen der Sensoren'), _defineProperty(_LANG$de, "settings", 'Einstellungen'), _defineProperty(_LANG$de, "sensors_title", 'Sensoreinstellungen'), _defineProperty(_LANG$de, "sensors_description", 'Sensor Status und Registrierung'), _defineProperty(_LANG$de, "sensors", 'Sensoren'), _defineProperty(_LANG$de, "sensor", 'Sensor'), _defineProperty(_LANG$de, "Select", 'Wähle'), _defineProperty(_LANG$de, "Not_selected", 'Nicht gewählt'), _defineProperty(_LANG$de, "Poor", 'Arm'), _defineProperty(_LANG$de, "Fair", 'Fair'), _defineProperty(_LANG$de, "Average", 'Durchschnitt'), _defineProperty(_LANG$de, "Good", 'Gut'), _defineProperty(_LANG$de, "Excellent", 'Excellent'), _defineProperty(_LANG$de, "Low", 'Tief'), _defineProperty(_LANG$de, "Medium", 'Mitte'), _defineProperty(_LANG$de, "High", 'Hoch'), _defineProperty(_LANG$de, "Extreme", 'Extrem'), _defineProperty(_LANG$de, "select_color", 'Wähle eine Farbe'), _defineProperty(_LANG$de, "advanced", 'Erweitert'), _defineProperty(_LANG$de, "Select_sensor", 'Wähle einen Sensor'), _defineProperty(_LANG$de, "temperature", 'Temperatur'), _defineProperty(_LANG$de, "t", 'Temperatur'), _defineProperty(_LANG$de, "light", 'Sonnenlicht'), _defineProperty(_LANG$de, "l", 'Sonnenlicht'), _defineProperty(_LANG$de, "water", 'Wasser'), _defineProperty(_LANG$de, "w", 'Wasser'), _defineProperty(_LANG$de, "humidity", 'Feuchtigkeit'), _defineProperty(_LANG$de, "h", 'Feuchtigkeit'), _defineProperty(_LANG$de, "air_pressure", 'Luftdruck'), _defineProperty(_LANG$de, "p", 'Luftdruck'), _defineProperty(_LANG$de, "weight", 'Gewicht'), _defineProperty(_LANG$de, "w_v", 'Gewichtssensor Wert für alle'), _defineProperty(_LANG$de, "w_fl", 'Gewichtssensor Wert vorne links'), _defineProperty(_LANG$de, "w_fr", 'Gewichtssensor Wert vorne rechts'), _defineProperty(_LANG$de, "w_bl", 'Gewichtssensor Wert hinten links'), _defineProperty(_LANG$de, "w_br", 'Gewichtssensor Wert hinten rechts'), _defineProperty(_LANG$de, "weight_kg", 'Gewicht'), _defineProperty(_LANG$de, "weight_kg_corrected", 'Gewicht (korrigiert)'), _defineProperty(_LANG$de, "weight_combined_kg", 'Gewicht kombiniert'), _defineProperty(_LANG$de, "bat_volt", 'Batterie'), _defineProperty(_LANG$de, "bv", 'Batterie'), _defineProperty(_LANG$de, "sound_fanning_4days", 'Fan 4d Bienen'), _defineProperty(_LANG$de, "s_fan_4", 'Fan 4d Bienens'), _defineProperty(_LANG$de, "sound_fanning_6days", 'Fan 6d Bienen'), _defineProperty(_LANG$de, "s_fan_6", 'Fan 6d Bienen'), _defineProperty(_LANG$de, "sound_fanning_9days", 'Fan 9d Bienens'), _defineProperty(_LANG$de, "s_fan_9", 'Fan 9d Bienen'), _defineProperty(_LANG$de, "sound_flying_adult", 'Fliegende Bienen'), _defineProperty(_LANG$de, "s_fly_a", 'Fliegende Bienen'), _defineProperty(_LANG$de, "sound_total", 'Totaler Sound'), _defineProperty(_LANG$de, "s_tot", 'Totaler Sound'), _defineProperty(_LANG$de, "bee_count_in", 'Bienenzähler nach innen'), _defineProperty(_LANG$de, "bc_i", 'Bienenzähler nach innen'), _defineProperty(_LANG$de, "bee_count_out", 'Bienenzähler nach außen'), _defineProperty(_LANG$de, "bc_o", 'Bienenzähler nach außen'), _defineProperty(_LANG$de, "t_i", 'Temp. innen'), _defineProperty(_LANG$de, "rssi", 'Signal Stärke'), _defineProperty(_LANG$de, "snr", 'Signal Krach'), _defineProperty(_LANG$de, "Sound_measurements", 'Soundmessungen'), _defineProperty(_LANG$de, "Sensor_info", 'Sensor info'), _defineProperty(_LANG$de, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$de, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$de, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$de, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$de, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$de, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$de, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$de, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$de, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$de, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$de, "hour", 'Stunde'), _defineProperty(_LANG$de, "day", 'Tag'), _defineProperty(_LANG$de, "week", 'Woche'), _defineProperty(_LANG$de, "month", 'Monat'), _defineProperty(_LANG$de, "year", 'Jahr'), _defineProperty(_LANG$de, "could_not_load_settings", 'Die Einstellungen konnten nicht geladen werden'), _defineProperty(_LANG$de, "offline", 'Keine Verbindung'), _defineProperty(_LANG$de, "remote", 'Fernbedienung'), _defineProperty(_LANG$de, "connected", 'Direkt'), _defineProperty(_LANG$de, "yes", 'Ja'), _defineProperty(_LANG$de, "no", 'Nein'), _defineProperty(_LANG$de, "footer_text", 'Open source beekeeping'), _defineProperty(_LANG$de, "beep_foundation", 'the BEEP foundation'), _defineProperty(_LANG$de, "Checklist", 'Stockkarte'), _defineProperty(_LANG$de, "Checklist_items", 'Stockkarte Artikel'), _defineProperty(_LANG$de, "edit_hive_checklist", 'Aktivieren / deaktivieren Sie die Kästchen in der Liste, um Artikel aus Ihrer Stockkarte hinzuzufügen / zu entfernen. Sie können die Artikel auch entfalten / falten und ziehen / ablegen, um sie an Ihren eigenen Stil anzupassen. Tipp: Wenn Sie einen Suchbegriff in das Suchfeld eingeben, werden alle Artikel, die diesen Begriff enthalten, ausgeklappt und rot gefärbt.'), _defineProperty(_LANG$de, "Data_export", 'Daten Export'), _defineProperty(_LANG$de, "Export_your_data", 'Exportiere alle Daten aus Deinem Account per Email (Exceldatei).'), _defineProperty(_LANG$de, "Terms_of_use", 'Nutzungsbedingungen (EN)'), _defineProperty(_LANG$de, "accept_policy", 'Ich akzeptiere die BEEP-Nutzungsbedingungen, die mit dem neuen europäischen Datenschutzgesetz vereinbar sind'), _defineProperty(_LANG$de, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$de, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$de, "approve_policy", 'Sie haben die aktuellen Nutzungsbedingungen noch nicht erfüllt.'), _defineProperty(_LANG$de, "calibrate_weight", 'Kalibriere Gewicht'), _defineProperty(_LANG$de, "calibrate_explanation", 'Stellen Sie das Gewicht der Sensoren auf 0 ein, indem Sie den aktuellen Messwert subtrahieren.'), _defineProperty(_LANG$de, "set_as_zero_value", 'Setzen Sie diese Werte als 0 Werte'), _defineProperty(_LANG$de, "set_weight_factor", 'Definiere den Gewichtsfaktor'), _defineProperty(_LANG$de, "own_weight_kg", 'Wie hoch ist Ihr Eigengewicht in kg??'), _defineProperty(_LANG$de, "start_calibration", 'Treten Sie nun auf die Waage und drücken Sie die Taste unten, um den Gewichtsfaktor festzulegen. Verteilen Sie Ihr Gewicht gleichmäßig.'), _defineProperty(_LANG$de, "currently_there_is", 'Da ist ein Gewicht von'), _defineProperty(_LANG$de, "nothing", 'nichts'), _defineProperty(_LANG$de, "on_the_scale", 'auf der Skala'), _defineProperty(_LANG$de, "calibration_started", 'Calibration started... Wait for the next measurement to take effect.'), _defineProperty(_LANG$de, "calibration_ended", 'Calibration succeeded!'), _defineProperty(_LANG$de, "server_down", 'Die App ist aufgrund von Wartungsarbeiten nicht verfügbar. Bitte versuche es später erneut'), _defineProperty(_LANG$de, "add_to_calendar", 'Zum Kalender hinzufügen'), _defineProperty(_LANG$de, "sort_on", 'Sortieren nach'), _defineProperty(_LANG$de, "Whats_new", 'Neu in v2.1!'), _defineProperty(_LANG$de, "Manual", 'Anleitung'), _defineProperty(_LANG$de, "Site_title", 'BEEP | Bienenmonitor'), _defineProperty(_LANG$de, "could_not_create_user", 'Benutzer kann derzeit nicht erstellt werden. Entschuldigen Sie die Unannehmlichkeiten und versuchen Sie es später erneut. '), _defineProperty(_LANG$de, "email_verified", 'Ihre E-Mail-Adresse wurde verifiziert.'), _defineProperty(_LANG$de, "email_not_verified", 'Ihre E-Mail-Adresse wurde noch nicht bestätigt.'), _defineProperty(_LANG$de, "email_new_verification", 'Klicken Sie auf diesen Link, um eine neue Bestätigungs-E-Mail zu senden.'), _defineProperty(_LANG$de, "email_verification_sent", 'Eine Nachricht mit einem Bestätigungslink wurde an Ihre E-Mail-Adresse gesendet. Klicken Sie auf den Link in der E-Mail, um Ihr Konto zu aktivieren und sich anzumelden. '), _defineProperty(_LANG$de, "not_filled", 'ist erforderlich, aber nicht ausgefüllt'), _defineProperty(_LANG$de, "cannot_deselect", 'Dieses Objekt kann nicht entfernt werden, da es ein erforderliches Objekt enthält'), _defineProperty(_LANG$de, "sensor_key", 'Sensor key'), _defineProperty(_LANG$de, "Undelete", 'Nicht löschen'), _defineProperty(_LANG$de, "the_field", 'The'), _defineProperty(_LANG$de, "is_required", 'ist erforderlich'), _defineProperty(_LANG$de, "No_groups", 'Keine Gruppen verfügbar'), _defineProperty(_LANG$de, "not_available_yet", 'noch nicht verfügbar. Bitte erstelle hier das erste.'), _defineProperty(_LANG$de, "Users", 'Benutzer'), _defineProperty(_LANG$de, "Member", 'Gruppenmitglied'), _defineProperty(_LANG$de, "Members", 'Gruppenmitglieder'), _defineProperty(_LANG$de, "Invite", 'Einladen'), _defineProperty(_LANG$de, "Invited", 'Eingeladen'), _defineProperty(_LANG$de, "invitations", 'einladungen'), _defineProperty(_LANG$de, "Admin", 'Administrator'), _defineProperty(_LANG$de, "Creator", 'Gruppeninhaber'), _defineProperty(_LANG$de, "Groups", 'Kooperieren'), _defineProperty(_LANG$de, "Group", 'Kollaborationsgruppe'), _defineProperty(_LANG$de, "group", "Kollaborationsgruppe"), _defineProperty(_LANG$de, "to_share", 'mit dieser Gruppe zu teilen. 1 Klick = Gruppenmitglieder können nur anzeigen, 2 Klicks = Gruppenmitglieder können bearbeiten'), _defineProperty(_LANG$de, "Invitation_accepted", "Einladung angenommen"), _defineProperty(_LANG$de, "Accept", 'Akzeptieren'), _defineProperty(_LANG$de, "My_shared", "Mein geteiltes"), _defineProperty(_LANG$de, "invitee_name", 'Name des eingeladenen Teilnehmers'), _defineProperty(_LANG$de, "Remove_group", 'Sind Sie sicher, dass Sie diese freigegebene Gruppe für alle Mitglieder der Gruppe vollständig entfernen möchten?'), _defineProperty(_LANG$de, "Detach_from_group", 'Entferne mich und meine Bienenstöcke aus dieser Gruppe'), _defineProperty(_LANG$de, "my_hive", 'Mein Beute'), _defineProperty(_LANG$de, "created", 'erstellt'), _defineProperty(_LANG$de, "group_detached", 'Die Gruppe erfolgreich verlassen'), _defineProperty(_LANG$de, "group_activated", 'Gruppeneinladung angenommen'), _defineProperty(_LANG$de, "group_explanation_1", '1. Erstellen Sie eine neue Kooperationsgruppe mit einem eindeutigen Titel und einer optionalen Beschreibung.'), _defineProperty(_LANG$de, "group_explanation_2", '2. Laden Sie andere Beep-Benutzer zu ihrer Beep-E-Mail-Adresse ein.'), _defineProperty(_LANG$de, "group_explanation_3", '3. Teilen Sie bestimmte Bienenstöcke, die von anderen gesehen werden sollen, oder arbeiten Sie zusammen an'), _defineProperty(_LANG$de, "Filter_and_sort_on", 'Filtern und sortieren nach:'), _LANG$de);
/*
 * Beep - Translations
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

LANG['es'] = (_LANG$es = {
  /* Date picker */
  monthsFull: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
  monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dec'],
  weekdaysFull: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
  weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
  Today: 'Hoy',
  Clear: 'Borrar',
  Close: 'Cerrar',
  firstDay: 1,
  format: 'dddd d mmmm aaaa',

  /* main */
  Website: 'Sitio web',
  Feedback: 'Comentarios',
  Feedback_mail_header: 'Beep app feedback',
  Feedback_mail_body: 'Querida fundación Beep,%0D%0A%0D%0A Aquí le envio mis comentarios sobre la aplicación Beep.%0 D%0A%0D%0AI descubrió esto:%0D%0A%0D%0AJusto antes de que ocurriera, hice:%0D%0A%0D%0ALa pantalla se veía como (por favor incluya una captura de pantalla):%0D%0A%0D%0A%0D%0A%0A%0D%0A',
  Diagnostic_info: 'Información de diagnóstico (en caso de error):%0D%0A',
  back: ' Atrás ',
  menu: ' Menú ',
  lighting: ' Iluminación ',
  camera: ' Cámara',
  weather: ' Tiempo',
  sensors: ' Sensores ',
  sensors_na: 'Los sensores BEEP para monitorear remotamente su colmena pronto estarán disponibles...',
  no_valid_authentication: ' No se han recibido datos de autenticación válidos',
  succesfully_saved: 'Guardado con éxito',
  switch_language: 'Cambiar idioma',
  Delete: 'Eliminar',
  Search: 'Buscar...',

  /* user error messages */
  User: 'Usuario',
  User_data: 'Datos de usuario',
  user_data: 'datos de usuario',
  updated: 'actualizado',
  delete_complete_account: '¿Está seguro de que desea eliminar su cuenta completa, inludiendo todos los apiarios, colmenas e inspecciones? Es irrecuperable',
  username_is_required: 'Por favor introduzca el nombre de usuario',
  username_already_exists: 'El nombre de usuario ya existe',
  password_is_required: 'Por favor introduzca una contraseña',
  email_is_required: 'Por favor introduzca una dirección de correo electrónico',
  email_already_exists: 'La dirección de correo electrónico ya está en uso',
  'policy accepted_is_required': 'Es necesario que Usted acepte los términos del servicio para registrarse',
  already_registered: 'Ya estoy registrado',
  invalid_user: 'Usuario desconocido, o contraseña incorrecta',
  invalid_password: 'Contraseña demasiado corta (mín. 8 caracteres)',
  no_password_match: 'Las contraseñas no coinciden',
  invalid_token: 'Código no válido',
  no_valid_email: 'Dirección de correo electrónico no válida',
  empty_fields: 'Por favor rellene todos los campos',
  match_passwords: 'Las contraseñas no coinciden',
  succesfully_registered: 'Usted está registrado correctamente.',
  authentication_failed: 'No se pudo autenticar',
  no_valid_input_received: 'No se pudieron guardar los datos, ninguna entrada válida fue recibida.',
  remove_all_settings: 'Eliminar todos los ajustes',
  remove_apiary: 'Eliminar apiario',
  remove_hive: 'Eliminar colmena',
  remove_inspection: 'Eliminar inspección',
  Error: 'Error',
  Warning: 'Advertencia',
  first_remove_hives: 'Atención: aun hay colmenas en este apiario. Usted puede guardar colmenas específicas (y sus inspecciones) trasladándolas primero a otro apiario. Si continúa con la eliminación, eliminará TODAS las colmenas e inspecciones presentes en esta ubicación.',
  Date: 'Fecha',
  ok: 'Ok',
  previous: 'Anterior',
  prev: 'anterior',
  next: 'Siguiente',
  add: 'Agregar',
  create_new: 'Crear nuevo',
  New: 'Nuevo',
  warning: 'Advertencia',
  apply: 'Aplicar',
  Cancel: 'Cancelar',
  automatic: 'Automático',
  manually: 'Manual',
  on: 'Encendido',
  off: 'Apagado',

  /* inicio de sesión */
  login_title: 'Iniciar sesión',
  login: 'Iniciar sesión',
  back_to_login: 'Volver a iniciar sesión',
  forgot_password: '¿Olvidó su contraseña?',
  username: 'Nombre de usuario',
  password: 'Contraseña',
  confirm_password: 'Confirmar contraseña',
  email: ' Correo electrónico ',
  token: 'Código',
  create_login_question: '¿Aún no tienes cuenta? Regístrese como nuevo usuario',
  create_login: 'Registrarse como nuevo usuario',
  create_login_summary: 'Crear una nueva cuenta de usuario',
  save: 'Guardar',
  save_and_return: 'Guardar y volver',
  logout: 'Cerrar sesión',
  logout_title: 'Cerrar sesión como ',
  logout_now: '¿Realmente quieres cerrar sesión ahora?',
  member_since: 'Beeping desde',

  /* Recuperación de contraseñas */
  password_recovery_title: '¿Olvidó su contraseña?',
  password_recovery_remembered: 'Oh, ahora recordé mi contraseña de nuevo!',
  password_recovery_user: 'Información del usuario',
  password_recovery_send_mail: 'Enviar código de verificación',
  password_recovery_code_not_received: '¿Código no recibido dentro de 5 minutos?',
  password_recovery_enter_code: '¿Ya tiene un código de verificación? Introdúzcalo aquí',
  password_recovery_reset_title: 'Introduzca una nueva contraseña',
  password_recovery_reset_password: 'Cambie contraseña',
  password_recovery_reminder_success: 'Un correo electrónico se ha enviado. Haga clic en el enlace de su correo electrónico para restablecer la contraseña de esta cuenta.',
  password_recovery_reminder_summary: 'Introduzca su dirección de correo electrónico. Usted recibirá un correo electrónico con un enlace para cambiar la contraseña en el siguiente paso.',
  password_recovery_reset_summary: 'Utilice el código que Usted recibió para establecer una nueva contraseña para su cuenta',
  password_recovery_reset_success: ' Su contraseña ha sido modificada exitosamente, Usted ha iniciado sesión',
  new_password: 'Nueva contraseña',
  confirm_new_password: 'Confirmar nueva contraseña',
  go_to_dashboard: 'Ir a mi tablero',

  /* overview */
  overview_title: 'título del resumen',
  overview: 'Resumen',
  color: 'Color',
  state: 'Encendido/Apagado',
  connection_state: 'Estado de conexión',

  /* hives */
  locations_title: 'Señal Sonora',
  hives_title: 'Señal Sonora',
  Hive: 'Colmena',
  hive: 'colmena',
  Location: 'Apiario',
  location: 'apiario',
  Hives: 'Colmenas',
  hives: 'Colmenas',
  Locations: 'Apiarios',
  locations: 'apiarios',
  Name: 'Nombre',
  name: 'nombre',
  Type: 'Tipo',
  type: 'tipo',
  Layer: 'Capa',
  layer: 'capa',
  brood: 'Cría',
  honey: 'Miel',
  inspect: 'Inspeccionar',
  inspection: ' inspección ',
  Inspection: ' Inspección ',
  Inspections: ' Inspecciones',
  New_inspection: 'Nuevas Inspección',
  Edit_inspection: 'Editar inspección',
  Actions: 'Acciones',
  Conditions: 'Condiciones (inspeccionado)',
  edit: 'Editar',
  Hive_brood_layers: 'Capas de Cría',
  Hive_honey_layers: 'Capas de Miel',
  Hive_layer_amount: 'Conteo de Capas',
  Bee_race: 'Raza de abeja',
  Birth_date: 'Fecha de nacimiento',
  Color: 'Color',
  Queen_colored: 'Reina Marcada',
  Queen_clipped: 'Reina Recortada',
  Queen_fertilized: 'Reina Fertilizada',
  Age: 'Edad',
  year: 'años de edad',

  /* Elementos de verificación de colmena */
  Date_of_inspection: 'Fecha de inspección',
  action: 'Acción',
  reminder: 'Recordar',
  remind_date: 'Fecha de notificación',
  overall: 'General',
  positive_impression: 'Impresión total',
  needs_attention: 'Necesita Atención',
  notes: 'Notas',
  notes_for_next_inspection: 'Nota corta para la próxima inspección (visible en el resumen)',
  Not_implemented_yet: 'Este elemento aún no se ha implementado',
  save_input_first: '¿Desea guardar primero la entrada?',

  /* tablero */
  dashboard_title: 'Tablero',
  dashboard: 'Tablero',
  measurements: 'Medidas',
  measurementsError: 'No es posible cargar las mediciones, comprobar la conexión de red',
  last_measurement: 'Última medición',
  at: 'en',
  measurement_system: 'Sistema de medición ',
  no_data: 'No hay datos disponibles',
  no_chart_data: 'No hay datos de gráfico para el período seleccionado',

  /* Ajustes */

  /* settings */
  General: 'General'
}, _defineProperty(_LANG$es, "Location", 'Ubicación'), _defineProperty(_LANG$es, "Country", 'País'), _defineProperty(_LANG$es, "City", 'Ciudad'), _defineProperty(_LANG$es, "Address", 'Dirección'), _defineProperty(_LANG$es, "Lattitude", 'Latitud'), _defineProperty(_LANG$es, "Longitude", 'Longitud'), _defineProperty(_LANG$es, "Street", 'Calle'), _defineProperty(_LANG$es, "Number", 'No.'), _defineProperty(_LANG$es, "Postal_code", 'Codigo postal'), _defineProperty(_LANG$es, "Description", 'Descripcion'), _defineProperty(_LANG$es, "Hive_settings", 'Configuración de la colmena'), _defineProperty(_LANG$es, "Hive_amount", 'Cantidad de colmenas en esta ubicación'), _defineProperty(_LANG$es, "Hive_prefix", 'Prefijo del nombre de la colmena (antes del numéro)'), _defineProperty(_LANG$es, "Hive_number_offset", '??????? '), _defineProperty(_LANG$es, "Hive_type", 'Tipo de colmena'), _defineProperty(_LANG$es, "Hive_layers", 'Capas de la colmena'), _defineProperty(_LANG$es, "Hive_frames", 'Marcos por capa'), _defineProperty(_LANG$es, "Hive_color", 'Color de la colmena'), _defineProperty(_LANG$es, "Queen", 'Reina'), _defineProperty(_LANG$es, "queen", 'reina'), _defineProperty(_LANG$es, "settings_title", 'Configuración'), _defineProperty(_LANG$es, "settings_description", 'Configuración de los sensores'), _defineProperty(_LANG$es, "settings", 'Configuración'), _defineProperty(_LANG$es, "sensors_title", 'Configuración de los sensores'), _defineProperty(_LANG$es, "sensors_description", 'Descripción de sensores'), _defineProperty(_LANG$es, "sensors", 'Sensores'), _defineProperty(_LANG$es, "sensor", 'Sensor'), _defineProperty(_LANG$es, "Select", 'Seleccionar'), _defineProperty(_LANG$es, "Not_selected", 'No seleccionado'), _defineProperty(_LANG$es, "Poor", 'Pobre'), _defineProperty(_LANG$es, "Fair", 'Justo'), _defineProperty(_LANG$es, "Average", 'Promedio'), _defineProperty(_LANG$es, "Good", 'Bueno'), _defineProperty(_LANG$es, "Excellent", 'Excelente'), _defineProperty(_LANG$es, "Low", 'Bajo'), _defineProperty(_LANG$es, "Medium", 'Mediano'), _defineProperty(_LANG$es, "High", 'Alto'), _defineProperty(_LANG$es, "Extreme", 'Extremo'), _defineProperty(_LANG$es, "select_color", 'Seleccionar un color'), _defineProperty(_LANG$es, "advanced", 'Avanzado'), _defineProperty(_LANG$es, "Select_sensor", 'Seleccionar un sensor'), _defineProperty(_LANG$es, "temperature", 'Temperatura'), _defineProperty(_LANG$es, "t", 'Temperatura'), _defineProperty(_LANG$es, "light", 'Luz Solar'), _defineProperty(_LANG$es, "l", 'Luz Solar'), _defineProperty(_LANG$es, "water", 'Agua'), _defineProperty(_LANG$es, "w", 'Agua'), _defineProperty(_LANG$es, "humidity", 'Humedad'), _defineProperty(_LANG$es, "h", 'Humedad'), _defineProperty(_LANG$es, "air_pressure", 'Presión de Aire'), _defineProperty(_LANG$es, "p", 'Presión de Aire'), _defineProperty(_LANG$es, "weight", 'Peso'), _defineProperty(_LANG$es, "w_v", 'El sensor de peso valora todos los sensores'), _defineProperty(_LANG$es, "w_fl", 'Valor del sensor de peso delantero izquierdo'), _defineProperty(_LANG$es, "w_fr", 'Valor del sensor de peso delantero derecho'), _defineProperty(_LANG$es, "w_bl", 'Valor del sensor de peso trasero izquierda'), _defineProperty(_LANG$es, "w_br", 'Valor del sensor de peso trasero derecha'), _defineProperty(_LANG$es, "weight_kg", 'Peso'), _defineProperty(_LANG$es, "weight_kg_corrected", 'Peso (corr)'), _defineProperty(_LANG$es, "weight_combined_kg", 'Peso combi'), _defineProperty(_LANG$es, "bat_volt", 'Batería'), _defineProperty(_LANG$es, "bv", 'Batería'), _defineProperty(_LANG$es, "sound_fanning_4days", 'Fan 4d abejas'), _defineProperty(_LANG$es, "s_fan_4", 'Fan 4d abejas'), _defineProperty(_LANG$es, "sound_fanning_6days", 'Fan 6d abejas'), _defineProperty(_LANG$es, "s_fan_6", 'Fan 6d abejas'), _defineProperty(_LANG$es, "sound_fanning_9days", 'Fan 9d abejas'), _defineProperty(_LANG$es, "s_fan_9", 'Fan 9d abejas'), _defineProperty(_LANG$es, "sound_flying_adult", 'Abejas voladoras'), _defineProperty(_LANG$es, "s_fly_a", 'Abejas voladoras'), _defineProperty(_LANG$es, "sound_total", 'Sonido total'), _defineProperty(_LANG$es, "s_tot", 'Sonido total'), _defineProperty(_LANG$es, "bee_count_in", 'Cuenta de abejas en el interior'), _defineProperty(_LANG$es, "bc_i", 'Cuenta de abejas en el interior'), _defineProperty(_LANG$es, "bee_count_out", 'Cuenta de abejas en el exterior'), _defineProperty(_LANG$es, "bc_o", 'Cuenta de abejas en el exterior'), _defineProperty(_LANG$es, "t_i", 'Temp. dentro'), _defineProperty(_LANG$es, "rssi", 'Fuerza de la señal'), _defineProperty(_LANG$es, "snr", 'Ruido de la señal'), _defineProperty(_LANG$es, "lat", 'Latitud'), _defineProperty(_LANG$es, "lon", 'Longitud'), _defineProperty(_LANG$es, "Sound_measurements", 'Medidas de sonido?'), _defineProperty(_LANG$es, "Sensor_info", ' Información del sensor'), _defineProperty(_LANG$es, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$es, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$es, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$es, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$es, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$es, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$es, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$es, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$es, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$es, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$es, "hour", 'Horas'), _defineProperty(_LANG$es, "day", 'Día'), _defineProperty(_LANG$es, "week", 'Semana'), _defineProperty(_LANG$es, "month", 'Mes'), _defineProperty(_LANG$es, "year", 'Año'), _defineProperty(_LANG$es, "could_not_load_settings", 'No se pudo cargar la configuración'), _defineProperty(_LANG$es, "offline", 'Sin conexión'), _defineProperty(_LANG$es, "remote", 'Remoto'), _defineProperty(_LANG$es, "connected", 'Directo'), _defineProperty(_LANG$es, "yes", 'Si'), _defineProperty(_LANG$es, "no", 'No'), _defineProperty(_LANG$es, "footer_text", 'Apicultura de código abierto'), _defineProperty(_LANG$es, "beep_foundation", 'la fundación BEEP'), _defineProperty(_LANG$es, "Checklist", 'Lista de verificación'), _defineProperty(_LANG$es, "Checklist_items", 'Artículos de la lista de verificación'), _defineProperty(_LANG$es, "edit_hive_checklist", 'Marque/desmarque las casillas de la lista anterior para agregar/eliminar elementos de su lista de comprobación de la colmena. También puede desplegar/doblar y arrastrar/soltar los elementos para reordenarlos a su propio estilo. Consejo: si introduce un término en el campo de búsqueda, todos los elementos que contengan ese término se retirarán y colorearán de rojo.'), _defineProperty(_LANG$es, "Data_export", 'Exportación de datos'), _defineProperty(_LANG$es, "Export_your_data", 'Exportar todos los datos que se encuentran en su cuenta de Beep y enviar un correo electrónico que contenga los datos como un archivo de Excel. El archivo de Excel tiene diferentes pestañas que contienen sus datos personales, colmena, ubicación y datos de inspección.'), _defineProperty(_LANG$es, "Terms_of_use", 'Términos del servicio'), _defineProperty(_LANG$es, "accept_policy", 'Acepto las condiciones del servicio BEEP, que son compatibles con la nueva ley europea de privacidad'), _defineProperty(_LANG$es, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$es, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$es, "approve_policy", 'Usted aún no ha cumplido con los últimos términos del servicio.'), _defineProperty(_LANG$es, "calibrate_weight", 'Calibrar peso'), _defineProperty(_LANG$es, "calibrate_explanation", 'Establecer el peso de los sensores en 0 restando el valor de la medición actual.'), _defineProperty(_LANG$es, "set_as_zero_value", 'Establecer estos valores como valores 0'), _defineProperty(_LANG$es, "set_weight_factor", 'Definir factor de peso'), _defineProperty(_LANG$es, "own_weight_kg", '¿Cuál es su propio peso en kg?'), _defineProperty(_LANG$es, "start_calibration", 'Ahora pise la pesa y presione el botón de abajo para definir el factor de peso. Distribuya su peso por igual.'), _defineProperty(_LANG$es, "currently_there_is", 'Hay un peso de'), _defineProperty(_LANG$es, "nothing", 'nada'), _defineProperty(_LANG$es, "on_the_scale", 'sobre la pesa'), _defineProperty(_LANG$es, "calibration_started", 'La calibración comenzó... Espere a que la próxima medición surta  efecto.'), _defineProperty(_LANG$es, "calibration_ended", '¡La calibración tuvo éxito!'), _defineProperty(_LANG$es, "server_down", 'La aplicación no está disponible debido a trabajos de mantenimiento, por favor inténtelo de nuevo más tarde'), _defineProperty(_LANG$es, "add_to_calendar", 'Agregar al calendario'), _defineProperty(_LANG$es, "sort_on", 'Ordenar en'), _defineProperty(_LANG$es, "Whats_new", 'Nuevo en v2.1!'), _defineProperty(_LANG$es, "Manual", 'Manual'), _defineProperty(_LANG$es, "Site_title", 'BEEP ? Monitor de abejas'), _defineProperty(_LANG$es, "could_not_create_user", 'El usuario no se puede crear en este momento. Lo siento por la molestia, por favor inténtelo de nuevo más tarde.'), _defineProperty(_LANG$es, "email_verified", 'Su dirección de correo electrónico ha sido verificada.'), _defineProperty(_LANG$es, "email_not_verified", 'Su dirección de correo electrónico aún no ha sido verificada.'), _defineProperty(_LANG$es, "email_new_verification", 'Haga clic en este enlace para enviar un nuevo correo electrónico de verificación.'), _defineProperty(_LANG$es, "email_verification_sent", 'Se ha enviado un mensaje con un enlace de verificación a su dirección de correo electrónico. Haga clic en el enlace del correo electrónico para activar su cuenta e iniciar sesión.'), _defineProperty(_LANG$es, "not_filled", 'es necesario, pero no rellenado'), _defineProperty(_LANG$es, "cannot_deselect", 'No se puede quitar este elemento, porque contiene un elemento necesario'), _defineProperty(_LANG$es, "sensor_key", 'Tecla del sensor'), _defineProperty(_LANG$es, "Undelete", 'No borrar'), _defineProperty(_LANG$es, "No_groups", 'No hay grupos disponibles'), _defineProperty(_LANG$es, "not_available_yet", 'todavía no disponible. Por favor, cree el primero aquí.'), _defineProperty(_LANG$es, "Users", 'Users'), _defineProperty(_LANG$es, "Member", 'Miembros del Grupo'), _defineProperty(_LANG$es, "Members", 'Miembros del Grupo'), _defineProperty(_LANG$es, "Invite", 'Invitar'), _defineProperty(_LANG$es, "Invited", 'Invitado'), _defineProperty(_LANG$es, "invitations", 'invitaciones'), _defineProperty(_LANG$es, "Admin", 'Administrador'), _defineProperty(_LANG$es, "Creator", 'Propietario del Grupo'), _defineProperty(_LANG$es, "Groups", 'Colaboración'), _defineProperty(_LANG$es, "Group", 'Grupo de colaboración'), _defineProperty(_LANG$es, "group", 'grupo de colaboración'), _defineProperty(_LANG$es, "to_share", 'para compartir con este grupo. 1 click= miembros del grupo que solo pueden ver, 2 clicks= miembros del grupo que pueden editar'), _defineProperty(_LANG$es, "Invitation_accepted", 'Invitación aceptada'), _defineProperty(_LANG$es, "Accept", 'Aceptar'), _defineProperty(_LANG$es, "My_shared", 'Mi compartido'), _defineProperty(_LANG$es, "invitee_name", 'Nombre invitado'), _defineProperty(_LANG$es, "Remove_group", '¿Está seguro que desea eliminar completamente este grupo compartido para todos sus miembros'), _defineProperty(_LANG$es, "Detach_from_group", 'Quita a mí y a mis colmenas de este grupo'), _defineProperty(_LANG$es, "my_hive", 'Mi colmena'), _defineProperty(_LANG$es, "created", 'creado'), _defineProperty(_LANG$es, "group_detached", 'Salió con éxito del grupo'), _defineProperty(_LANG$es, "group_activated", 'Invitación de grupo aceptada'), _defineProperty(_LANG$es, "group_explanation_1", '1. Crear un nuevo grupo de cooperación con un título claro y una descripción opcional'), _defineProperty(_LANG$es, "group_explanation_2", '2. Invitar a otros usuarios de Beep en su dirección de correo electrónico Beep'), _defineProperty(_LANG$es, "group_explanation_3", '3. Compartir colmenas específicas para ser vistas por otros, de cooperar en'), _defineProperty(_LANG$es, "Filter_and_sort_on", 'Filtrar y ordenar:'), _LANG$es);
/*  
 * Beep - Translations  
 * Author: Pim van Gennip (pim@iconize.nl)  
 *  
 */

LANG['fr'] = (_LANG$fr = {
  /* Date picker */
  monthsFull: ['Janvier ', ' Février ', ' Mars ', ' Avril ', ' Mai ', ' Juin ', ' Juillet ', ' Août ', ' Septembre ', ' Octobre ', ' Novembre ', ' Décembre '],
  monthsShort: ['Jan ', ' Fév ', ' Mar ', ' Avr ', ' Mai ', ' Juin ', ' Juil ', ' Août ', ' Sep ', ' Oct ', ' Nov ', ' Dec'],
  weekdaysFull: ['Dimanche ', ' Lundi ', ' Mardi ', ' Mercredi ', ' Jeudi ', ' Vendredi ', ' Samedi '],
  weekdaysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
  Today: 'Aujourd\'hui',
  Clear: 'Effacer',
  Close: 'Fermer',
  firstDay: 1,
  format: 'jjjj j mmmm aaaa',

  /* main */
  Website: 'Site Web',
  Feedback: 'Avis',
  Feedback_mail_header: 'Commentaires de l\'application beep',
  Feedback_mail_body: 'Cher Beep foundation, % 0D % 0A % 0D % 0AVoici mon commentaire sur l\'application Beep. % 0D % 0A % 0D % 0AI a découvert ceci : % 0D % 0A % 0D % 0AJuste avant que cela ne se produise, j\'ai fait : % 0D % 0A % 0D % 0AL\'écran ressemblait (s\'il vous plaît inclure une capture d\'écran) : % 0D % 0A % 0A % 0D % 0A % 0A % 0A % 0A D % 0A % 0D % 0A',
  Diagnostic_info: 'Informations de diagnostic (en cas de bogue) : % 0D % 0A',
  back: 'Retourner',
  menu: 'Menu',
  lighting: 'Éclairage',
  camera: 'Appareil photo',
  weather: 'Météo',
  sensors: 'Capteurs',
  sensors_na: 'Des capteurs BEEP pour surveiller à distance votre ruche seront bientôt disponibles..',
  no_valid_authentication: 'Aucune donnée d\'authentification valide n\'a été reçue',
  succesfully_saved: 'Sauvegardé avec succès',
  switch_language: 'Changez de langue',
  Delete: 'Supprimer',
  Search: 'Recherche...',

  /* user error messages */
  User: 'Utilisateur',
  User_data: 'Données utilisateur',
  user_data: 'données utilisateur',
  updated: 'mis à jour',
  delete_complete_account: 'Êtes-vous sûr de vouloir supprimer votre compte complet, y compris tous les ruchers, ruches et inspections ? C\'est irréversible.',
  username_is_required: 'Veuillez saisir le nom d\'utilisateur',
  username_already_exists: 'Le nom d\'utilisateur existe déjà',
  password_is_required: 'Veuillez saisir un mot de passe',
  email_is_required: 'Veuillez saisir un e-mail',
  email_already_exists: 'L\'adresse e-mail est déjà utilisée',
  'policy accepted_is_required': 'Vous devez accepter les conditions de service pour vous inscrire',
  already_registered: 'Je suis déjà inscrit',
  invalid_user: 'Utilisateur inconnu ou mot de passe incorrect',
  invalid_password: 'Mot de passe trop court (min. 8 caractères)',
  no_password_match: 'Les mots de passe ne correspondent pas',
  invalid_token: 'Code non valide',
  no_valid_email: 'Adresse e-mail non valide',
  empty_fields: 'Veuillez remplir tous les champs',
  match_passwords: 'Les mots de passe ne correspondent pas',
  succesfully_registered: 'Vous êtes enregistré avec succès',
  authentication_failed: 'Échec de l\'authentification',
  no_valid_input_received: 'Les données n\'ont pas pu être enregistrées, aucune entrée valide n\'a été reçue',
  remove_all_settings: 'Supprimez tous les paramètres',
  remove_apiary: 'Enlevez le rucher',
  remove_hive: 'Enlevez la ruche',
  remove_inspection: 'Retirer l\'inspection',
  Error: 'Erreur',
  Warning: 'Attention',
  first_remove_hives: 'Attention : il y a encore des ruches à ce rucher. Vous pouvez enregistrer des ruches spécifiques (et leurs inspections) en les déplaçant d\'abord vers un autre rucher. Si vous continuez à supprimer, vous supprimerez TOUTES les ruches et les inspections présentes à cet emplacement',
  Date: 'Rendez-vous',
  ok: 'Ok',
  previous: 'Précédent',
  prev: 'précédent',
  next: 'Suivant',
  add: 'Ajouter',
  create_new: 'Créer un nouveau',
  New: 'Nouveau',
  warning: 'Attention',
  apply: 'Appliquer',
  Cancel: 'Annuler',
  automatic: 'Automatique',
  manually: 'Manuel',
  on: 'On',
  off: 'Off',

  /* login */
  login_title: 'Connectez-vous',
  login: 'Connectez-vous',
  back_to_login: 'Retour à la connexion',
  forgot_password: 'Vous avez oublié votre mot de passe ?',
  username: 'Nom d\'utilisateur',
  password: 'Mot de passe',
  confirm_password: 'Confirmez le mot de passe',
  email: 'E-mail',
  token: 'Code',
  create_login_question: 'Pas encore de compte ? Inscrivez-vous en tant que nouvel utilisateur',
  create_login: 'Inscrivez-vous en tant que nouvel utilisateur',
  create_login_summary: 'Créez un nouveau compte d\'utilisateur',
  save: 'Sauvegarder et retour',
  save_and_return: 'Sauvegarder et retour',
  logout: 'Se déconnecter',
  logout_title: 'Se déconnecter en tant que',
  logout_now: 'Voulez-vous vraiment vous déconnecter maintenant ?',
  member_since: 'Beep depuis',

  /* password recovery */
  password_recovery_title: 'Vous avez oublié votre mot de passe ?',
  password_recovery_remembered: 'Oh, maintenant je me suis rappelé mon mot de passe !',
  password_recovery_user: 'Informations utilisateur',
  password_recovery_send_mail: 'Envoyer le code de vérification',
  password_recovery_code_not_received: 'Code non reçu dans les 5 minutes ?',
  password_recovery_enter_code: 'Vous avez déjà un code de vérification ? Entrez le ici',
  password_recovery_reset_title: 'Entrez un nouveau mot de passe',
  password_recovery_reset_password: 'Changer le mot de passe',
  password_recovery_reminder_success: 'Un e-mail a été envoyé. Cliquez sur le lien dans l\'e-mail pour réinitialiser votre mot de passe pour ce compte',
  password_recovery_reminder_summary: 'Entrez votre adresse e-mail. Vous recevrez un e-mail avec un lien pour changer votre mot de passe à l\'étape suivante',
  password_recovery_reset_summary: 'Utilisez le code que vous avez reçu pour définir un nouveau mot de passe pour votre compte',
  password_recovery_reset_success: 'Vous passowrd est modifié avec succès, et vous êtes connecté.',
  new_password: 'Nouveau mot de passe',
  confirm_new_password: 'Confirmez le nouveau mot de passe',
  go_to_dashboard: 'Allez sur mon tableau de bord',

  /* overview */
  overview_title: 'Vue d\'ensemble',
  overview: 'Vue d\'ensemble',
  color: 'Couleur',
  state: 'Marche/arrêt',
  connection_state: 'État de la connexion',

  /* hives */
  locations_title: 'Beep',
  hives_title: 'Beep',
  Hive: 'Ruche',
  hive: 'ruche',
  Location: 'rucher',
  location: 'rucher',
  Hives: 'Ruches',
  hives: 'Ruches',
  Locations: 'Ruchers',
  locations: 'ruchers',
  Name: 'Nom',
  name: 'nom',
  Type: 'Tapez',
  type: 'tapez',
  Layer: 'Element',
  layer: 'element',
  brood: 'Couvain',
  honey: 'Miel',
  inspect: 'Inspecter',
  inspection: 'inspection',
  Inspection: 'Inspection',
  Inspections: 'Inspections',
  New_inspection: 'Nouvelle inspection',
  Edit_inspection: 'Modifier l\'inspection',
  Actions: 'Actions',
  Conditions: 'Conditions (inspectées)',
  edit: 'Modifier',
  Hive_brood_layers: 'Cadre de couvain',
  Hive_honey_layers: 'Cadre de miel',
  Hive_layer_amount: 'Quantité d\'éléments',
  Bee_race: 'Course d\'abeilles',
  Birth_date: 'Date de naissance',
  Color: 'Couleur',
  Queen_colored: 'Reine marquée',
  Queen_clipped: 'Reine clipée',
  Queen_fertilized: 'Reine fécondée',
  Age: 'Âge',
  year: 'ans',

  /* Hive check items */
  Date_of_inspection: 'Date de l\'inspection',
  action: 'Action',
  reminder: 'Rappel',
  remind_date: 'Date de rappel',
  overall: 'Dans l\'ensemble',
  positive_impression: 'Impression totale',
  needs_attention: 'Besoin d\'attention',
  notes: 'Notes',
  notes_for_next_inspection: 'Brève note pour la prochaine inspection (visible sur la vue d\'ensemble)',
  Not_implemented_yet: 'Cet élément n\'est pas encore implémenté',
  save_input_first: 'Voulez-vous d\'abord enregistrer votre saisie ?',

  /* dashboard */
  dashboard_title: 'Tableau de bord',
  dashboard: 'Tableau de bord',
  measurements: 'Mesures',
  measurementsError: 'Impossible de charger les mesures, vérifiez la connexion réseau',
  last_measurement: 'Dernière mesure',
  at: 'à',
  measurement_system: 'Système de mesure Beep',
  no_data: 'Aucune donnée disponible',
  no_chart_data: 'Aucune donnée graphique pour la période sélectionnée',

  /* settings */
  General: 'Général'
}, _defineProperty(_LANG$fr, "Location", 'Emplacement'), _defineProperty(_LANG$fr, "Country", 'Pays'), _defineProperty(_LANG$fr, "City", 'Ville'), _defineProperty(_LANG$fr, "Address", 'Adresse'), _defineProperty(_LANG$fr, "Lattitude", 'Lattitude'), _defineProperty(_LANG$fr, "Longitude", 'Longitude'), _defineProperty(_LANG$fr, "Street", 'Rue'), _defineProperty(_LANG$fr, "Number", 'Non'), _defineProperty(_LANG$fr, "Postal_code", 'Code postal'), _defineProperty(_LANG$fr, "Description", 'Description'), _defineProperty(_LANG$fr, "Hive_settings", 'Paramètres de la ruche'), _defineProperty(_LANG$fr, "Hive_amount", 'Nombe de ruches à cet emplacement'), _defineProperty(_LANG$fr, "Hive_prefix", 'Nom de la ruche préfixe (avant numéro)'), _defineProperty(_LANG$fr, "Hive_number_offset", 'Début de numérotation'), _defineProperty(_LANG$fr, "Hive_type", 'Type de ruche'), _defineProperty(_LANG$fr, "Hive_layers", 'Element de ruche'), _defineProperty(_LANG$fr, "Hive_frames", 'Cadres par élément'), _defineProperty(_LANG$fr, "Hive_color", 'Couleur ruche'), _defineProperty(_LANG$fr, "Queen", 'Reine'), _defineProperty(_LANG$fr, "queen", 'reine'), _defineProperty(_LANG$fr, "settings_title", 'Paramètres'), _defineProperty(_LANG$fr, "settings_description", 'Réglages des capteurs'), _defineProperty(_LANG$fr, "settings", 'Paramètres'), _defineProperty(_LANG$fr, "sensors_title", 'Réglages du capteur'), _defineProperty(_LANG$fr, "sensors_description", 'État et enregistrement des capteurs'), _defineProperty(_LANG$fr, "sensors", 'Capteurs'), _defineProperty(_LANG$fr, "sensor", 'Capteur'), _defineProperty(_LANG$fr, "Select", 'Sélectionner'), _defineProperty(_LANG$fr, "Not_selected", 'Non sélectionné'), _defineProperty(_LANG$fr, "Poor", 'Pauvre'), _defineProperty(_LANG$fr, "Fair", 'Juste'), _defineProperty(_LANG$fr, "Average", 'Moyenne'), _defineProperty(_LANG$fr, "Good", 'Bon'), _defineProperty(_LANG$fr, "Excellent", 'Très bien'), _defineProperty(_LANG$fr, "Low", 'Faible'), _defineProperty(_LANG$fr, "Medium", 'Moyenne'), _defineProperty(_LANG$fr, "High", 'Elevé'), _defineProperty(_LANG$fr, "Extreme", 'Extrême'), _defineProperty(_LANG$fr, "select_color", 'Sélectionnez une couleur'), _defineProperty(_LANG$fr, "advanced", 'Avancé'), _defineProperty(_LANG$fr, "Select_sensor", 'Sélectionnez un capteur'), _defineProperty(_LANG$fr, "temperature", 'Température'), _defineProperty(_LANG$fr, "t", 'Température'), _defineProperty(_LANG$fr, "light", 'Lumière du soleil'), _defineProperty(_LANG$fr, "l", 'Lumière du soleil'), _defineProperty(_LANG$fr, "water", 'Eau'), _defineProperty(_LANG$fr, "w", 'Eau'), _defineProperty(_LANG$fr, "humidity", 'Humidité'), _defineProperty(_LANG$fr, "h", 'Humidité'), _defineProperty(_LANG$fr, "air_pressure", 'Pression de l\'air'), _defineProperty(_LANG$fr, "p", 'Pression de l\'air'), _defineProperty(_LANG$fr, "weight", 'Poids'), _defineProperty(_LANG$fr, "w_v", 'Valeur du capteur de poids tous les capteurs'), _defineProperty(_LANG$fr, "w_fl", 'Valeur du capteur de poids avant gauche'), _defineProperty(_LANG$fr, "w_fr", 'Valeur du capteur de poids avant droit'), _defineProperty(_LANG$fr, "w_bl", 'Valeur du capteur de poids arrière gauche'), _defineProperty(_LANG$fr, "w_br", 'Valeur du capteur de poids retour à droite'), _defineProperty(_LANG$fr, "weight_kg", 'Poids'), _defineProperty(_LANG$fr, "weight_kg_corrected", 'Poids (corr)'), _defineProperty(_LANG$fr, "weight_combined_kg", 'Poids combiné'), _defineProperty(_LANG$fr, "bat_volt", 'Batterie'), _defineProperty(_LANG$fr, "bv", 'Batterie'), _defineProperty(_LANG$fr, "sound_fanning_4days", 'Fan 4d abeilles'), _defineProperty(_LANG$fr, "s_fan_4", 'Fan 4d abeilles'), _defineProperty(_LANG$fr, "sound_fanning_6days", 'Fan 6d abeilles'), _defineProperty(_LANG$fr, "s_fan_6", 'Fan 6d abeilles'), _defineProperty(_LANG$fr, "sound_fanning_9days", 'Fan 9d abeilles'), _defineProperty(_LANG$fr, "s_fan_9", 'Fan 9d abeilles'), _defineProperty(_LANG$fr, "sound_flying_adult", 'Abeilles volantes'), _defineProperty(_LANG$fr, "s_fly_a", 'Abeilles volantes'), _defineProperty(_LANG$fr, "sound_total", 'Son total'), _defineProperty(_LANG$fr, "s_tot", 'Son total'), _defineProperty(_LANG$fr, "bee_count_in", 'Comptez les abeilles'), _defineProperty(_LANG$fr, "bc_i", 'Comptez les abeilles'), _defineProperty(_LANG$fr, "bee_count_out", 'Comptage d\'abeilles sorties'), _defineProperty(_LANG$fr, "bc_o", 'Comptage d\'abeilles sorties'), _defineProperty(_LANG$fr, "t_i", 'Temp. à l\'intérieur'), _defineProperty(_LANG$fr, "rssi", 'Force du signal'), _defineProperty(_LANG$fr, "snr", 'Bruit de signal'), _defineProperty(_LANG$fr, "Sound_measurements", 'Mesures sonores'), _defineProperty(_LANG$fr, "Sensor_info", 'Informations sur le capteur'), _defineProperty(_LANG$fr, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$fr, 's_bin146_195Hz', '146-195 Hz'), _defineProperty(_LANG$fr, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$fr, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$fr, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$fr, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$fr, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$fr, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$fr, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$fr, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$fr, "hour", 'Heure'), _defineProperty(_LANG$fr, "day", 'Jour'), _defineProperty(_LANG$fr, "week", 'Semaine'), _defineProperty(_LANG$fr, "month", 'Mois'), _defineProperty(_LANG$fr, "year", 'Année'), _defineProperty(_LANG$fr, "could_not_load_settings", 'Impossible de charger les paramètres'), _defineProperty(_LANG$fr, "offline", 'Pas de connexion'), _defineProperty(_LANG$fr, "remote", 'À distance'), _defineProperty(_LANG$fr, "connected", 'Direct'), _defineProperty(_LANG$fr, "yes", 'Oui'), _defineProperty(_LANG$fr, "no", 'Non'), _defineProperty(_LANG$fr, "footer_text", 'Apiculture open source'), _defineProperty(_LANG$fr, "beep_foundation", 'la fondation BEEP'), _defineProperty(_LANG$fr, "Checklist", 'Liste de contrôle'), _defineProperty(_LANG$fr, "Checklist_items", 'Éléments de la liste de contrôle'), _defineProperty(_LANG$fr, "edit_hive_checklist", 'Cochez ou décochez les cases de la liste ci-dessous pour ajouter/supprimer des éléments de votre liste de vérification de la ruche. Vous pouvez également déplier/plier et glisser/déposer les éléments pour les réorganiser à votre propre style. Astuce : si vous entrez un terme dans le champ de recherche, tous les éléments contenant ce terme seront pliés et colorés en rouge'), _defineProperty(_LANG$fr, "Data_export", 'Exportation de données'), _defineProperty(_LANG$fr, "Export_your_data", 'Exportez toutes les données qui se trouvent dans votre compte BEEP et envoyez un e-mail contenant les données en tant que fichier Excel 2007'), _defineProperty(_LANG$fr, "Terms_of_use", 'Conditions d\'utilisation'), _defineProperty(_LANG$fr, "accept_policy", 'J\'accepte les conditions d\'utilisation de BEEP, qui sont compatibles avec la nouvelle loi européenne sur la protection de la vie privée'), _defineProperty(_LANG$fr, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$fr, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$fr, "approve_policy", 'Vous n\'avez pas encore respecté les dernières conditions d\'utilisation'), _defineProperty(_LANG$fr, "server_down", 'L\'application n\'est pas disponible en raison  de maintenance. Veuillez réessayer plus tard'), _defineProperty(_LANG$fr, "add_to_calendar", 'Ajouter au calendrier'), _defineProperty(_LANG$fr, "sort_on", 'Trier sur'), _defineProperty(_LANG$fr, "Whats_new", 'Nouveau dans v2 !'), _defineProperty(_LANG$fr, "Manual", 'Manuel (EN)'), _defineProperty(_LANG$fr, "calibrate_weight", 'Étalonner le poids'), _defineProperty(_LANG$fr, "calibrate_explanation", 'Réglez le poids des capteurs à 0 en soustrayant la valeur de mesure actuelle'), _defineProperty(_LANG$fr, "set_as_zero_value", 'Définissez ces valeurs en tant que valeurs 0'), _defineProperty(_LANG$fr, "set_weight_factor", 'Définissez le facteur de poids'), _defineProperty(_LANG$fr, "own_weight_kg", 'Quel est votre propre poids en kg ?'), _defineProperty(_LANG$fr, "start_calibration", 'Maintenant, marchez sur la balance et appuyez sur le bouton ci-dessous pour définir le facteur de poids. Répartissez votre poids également'), _defineProperty(_LANG$fr, "currently_there_is", 'Il y a un poids de'), _defineProperty(_LANG$fr, "nothing", 'rien'), _defineProperty(_LANG$fr, "on_the_scale", 'sur la balance'), _defineProperty(_LANG$fr, "calibration_started", 'L\'étalonnage a commencé... Attendez que la prochaine mesure prenne effet'), _defineProperty(_LANG$fr, "calibration_ended", 'L\'étalonnage a réussi !'), _defineProperty(_LANG$fr, "server_down", 'L\'application n\'est pas disponible en raison de maintenance. Veuillez réessayer plus tard'), _defineProperty(_LANG$fr, "add_to_calendar", 'Ajouter au calendrier'), _defineProperty(_LANG$fr, "sort_on", 'Trier sur'), _defineProperty(_LANG$fr, "Whats_new", 'Nouveau dans v2.1!'), _defineProperty(_LANG$fr, "Manual", 'Manuel (EN)'), _defineProperty(_LANG$fr, "Site_title", 'BEEP | Moniteur abeille'), _defineProperty(_LANG$fr, "could_not_create_user", 'L\'utilisateur ne peut pas être créé pour le moment. Désolé pour la gêne occasionnée, veuillez réessayer plus tard'), _defineProperty(_LANG$fr, "email_verified", 'Votre adresse e-mail a été vérifiée'), _defineProperty(_LANG$fr, "email_not_verified", 'Votre adresse e-mail n\'a pas encore été vérifiée'), _defineProperty(_LANG$fr, "email_new_verification", 'Cliquez sur ce lien pour envoyer un nouvel e-mail de vérification'), _defineProperty(_LANG$fr, "email_verification_sent", 'Un message avec un lien de vérification a été envoyé à votre adresse e-mail. Cliquez sur le lien dans l\'e-mail pour activer votre compte et vous connecter'), _defineProperty(_LANG$fr, "not_filled", 'non rempli'), _defineProperty(_LANG$fr, "cannot_deselect", 'Impossible de supprimer cet élément, car il contient un élément requis'), _defineProperty(_LANG$fr, "sensor_key", 'Clé du capteur'), _defineProperty(_LANG$fr, "Undelete", 'Ne pas supprimer'), _defineProperty(_LANG$fr, "the_field", 'Le'), _defineProperty(_LANG$fr, "is_required", 'est obligatoire'), _defineProperty(_LANG$fr, "No_groups", 'Aucun groupe n\'est disponible'), _defineProperty(_LANG$fr, "not_available_yet", 'pas encore disponible. S\'il vous plaît créer le premier ici.'), _defineProperty(_LANG$fr, "Users", 'Utilisateurs'), _defineProperty(_LANG$fr, "Member", 'Membre du groupe'), _defineProperty(_LANG$fr, "Members", 'Membres du groupe'), _defineProperty(_LANG$fr, "Invite", 'Inviter'), _defineProperty(_LANG$fr, "Invited", 'Invité'), _defineProperty(_LANG$fr, "invitations", 'invitations'), _defineProperty(_LANG$fr, "Admin", 'Administrateur'), _defineProperty(_LANG$fr, "Creator", 'Propriétaire du groupe'), _defineProperty(_LANG$fr, "Groups", 'Collaborer'), _defineProperty(_LANG$fr, "Group", 'Groupe de collaboration'), _defineProperty(_LANG$fr, "group", 'groupe de collaboration'), _defineProperty(_LANG$fr, "to_share", 'pour partager avec ce groupe. 1 clic = les membres du groupe peuvent afficher uniquement, 2 clics = les membres du groupe peuvent modifier'), _defineProperty(_LANG$fr, "Invitation_accepted", 'Invitation acceptée'), _defineProperty(_LANG$fr, "Accept", 'Accepter'), _defineProperty(_LANG$fr, "My_shared", 'Mon partage'), _defineProperty(_LANG$fr, "invitee_name", 'Nom de l\'invité'), _defineProperty(_LANG$fr, "Remove_group", 'Êtes-vous sûr de vouloir supprimer ce groupe partagé en compétition pour tous ses membres'), _defineProperty(_LANG$fr, "Detach_from_group", 'Retirez moi et mes ruches de ce groupe'), _defineProperty(_LANG$fr, "my_hive", 'Ma ruche'), _defineProperty(_LANG$fr, "created", 'créé'), _defineProperty(_LANG$fr, "group_detached", 'A quitté le groupe avec succès'), _defineProperty(_LANG$fr, "group_activated", 'Invitation de groupe acceptée'), _defineProperty(_LANG$fr, "group_explanation_1", '1. Créer un nouveau groupe de coopération avec un titre clair et une description facultative'), _defineProperty(_LANG$fr, "group_explanation_2", '2. Inviter d\'autres utilisateurs Beep sur leur adresse e-mail Beep'), _defineProperty(_LANG$fr, "group_explanation_3", '3. Partager des ruches spécifiques pour être vues par d\'autres, pour coopérer'), _defineProperty(_LANG$fr, "Filter_and_sort_on", 'Filtrer et trier sur:'), _LANG$fr);
/*
 * Beep - Translations
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

LANG['ro'] = (_LANG$ro = {
  /* Date picker */
  monthsFull: ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Junie', 'Julie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'],
  monthsShort: ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
  weekdaysFull: ['Duminică', 'Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă'],
  weekdaysShort: ['Dum.', 'Lun.', 'Mar.', 'Mie.', 'Joi', 'Vin.', 'Sâm.'],
  Today: 'Astăzi',
  Clear: 'Ștergeți',
  Close: 'închideți',
  firstDay: 1,
  format: 'dddd d mmmm yyyy',

  /* main */
  Website: 'Website',
  Feedback: 'Feedback',
  Feedback_mail_header: 'Feedback aplicație Beep ',
  Feedback_mail_body: 'Dragă fundație Beep,%0D%0A%0D%0APrin prezenta feedbackul meu pentru aplicația Beep%0D%0A%0D%0AI am descoperit această:%0D%0A%0D%0înainte să apară acest mesaj, am efectuat:%0D%0A%0D%0APe ecran a apărut (vă rugăm să includeți o captură de ecran):%0D%0A%0D%0A%0D%0A%0D%0A',
  Diagnostic_info: 'Informații diagnostic (în cazul unei erori):%0D%0A',
  back: 'Înapoi',
  menu: 'Meniu',
  lighting: 'Lumină',
  camera: 'Cameră foto',
  weather: 'Vreme',
  sensors: 'Senzori',
  sensors_na: 'Senzorii BEEP pentru monitorizarea la distanță a coloniei dvs. vor fi disponibili în curând...',
  no_valid_authentication: 'Nu au fost primite date valide de autentificare',
  succesfully_saved: 'Salvate cu succes',
  switch_language: 'Schimbați limba',
  Delete: 'Ștergeți',
  Search: 'Căutare...',

  /* user error messages */
  User: 'Utilizator',
  User_data: 'Datele utilizatorului',
  user_data: 'datele utilizatorului',
  updated: 'actualizare',
  delete_complete_account: 'Sunteți sigur că doriți să ștergeți complet contul dumneavoastră, inclusiv toate stupinele, stupii și inspecțiile? Datele nu vor putea fi recuperate',
  username_is_required: 'Vă rugăm introduceți numele de utilizator',
  username_already_exists: 'Acest nume de utilizator există deja',
  password_is_required: 'Vă rugăm intorduceți parola',
  email_is_required: 'Vă rugăm introduceți adresa de e-mail',
  email_already_exists: 'Această adresă de  e-mail este deja folosită',
  'policy accepted_is_required': 'Trebuie să acceptați termenii și condițiile de înregistrare',
  already_registered: 'Sunt deja înregistrat',
  invalid_user: 'Utilizator necunoscut sau parolă greșită',
  invalid_password: 'Parola este prea scurtă (min. 8 caractere)',
  no_password_match: 'Parolele nu se potrivesc',
  invalid_token: 'Cod invalid',
  no_valid_email: 'Adresă de e-mail invalidă',
  empty_fields: 'Vă rugăm să completați toate câmpurile',
  match_passwords: 'Parolele nu se potrivesc',
  succesfully_registered: 'Sunteți înregistrat cu succes.',
  authentication_failed: 'Nu sa reușit autentificarea.',
  no_valid_input_received: 'Datele nu au putut fi salvate, nu au fost primite intrări valide.',
  remove_all_settings: 'Eliminați toate setările',
  remove_apiary: 'Eliminați stupina',
  remove_hive: 'Eliminare stup',
  remove_inspection: 'Eliminați inspecția',
  Error: 'Eroare',
  Warning: 'Advertisment',
  first_remove_hives: 'Atenție încă există stupi în această stupină. Puteți salva stupi specifici (și inspecțiilelor) prin mutarea acestora într-o altă stupină. Dacă veți continua cu ștergerea, vor fi șterse toate inspecțiile și toți stupii din prezenta locație.',
  Date: 'Data',
  ok: 'Ok',
  previous: 'Anterior',
  prev: 'anterior',
  next: 'Următorul',
  add: 'Adăugațti',
  create_new: 'Creați un nou',
  New: 'Nou',
  warning: 'Advertisment',
  apply: 'Aplicați',
  Cancel: 'Anulați',
  automatic: 'Automat',
  manually: 'Manual',
  on: 'Pornit',
  off: 'Oprit',

  /* login */
  login_title: 'Autentificare',
  login: 'Autentificare',
  back_to_login: 'Înapoi la autentificare',
  forgot_password: 'Ați uitat parola?',
  username: 'Nume utilizator',
  password: 'Parolă',
  confirm_password: 'Confirmare parolă',
  email: 'E-mail',
  token: 'Cod',
  create_login_question: 'Nu aveți încă un cont? Înregistrați-vă ca utilizator nou',
  create_login: 'Înregistrați-vă ca un utilizator nou',
  create_login_summary: 'Creați un cont de utilizator nou',
  save: 'Salvați',
  save_and_return: 'Salvare și întoarcere.',
  logout: 'Deconectați-vă',
  logout_title: 'Deconectați-vă ca',
  logout_now: 'Doriți să vă deconectați acum?',
  member_since: 'Membru Beep din',

  /* password recovery */
  password_recovery_title: 'Ați uitat parola?',
  password_recovery_remembered: 'Oh, acum mi-am reamintit parola!',
  password_recovery_user: 'Informații despre utilizator',
  password_recovery_send_mail: 'Trimiteți cod de verificare',
  password_recovery_code_not_received: 'Codul nu a fost primit în mai puțin de 5 minute?',
  password_recovery_enter_code: 'Ați primit deja un cod de verificare? Introduceți codul aici',
  password_recovery_reset_title: 'Introduceți o parolă nouă',
  password_recovery_reset_password: 'Schimbarea parolei',
  password_recovery_reminder_success: 'A fost trimis un  e-mail. Faceți clic pe link-ul din e-mail pentru a vă putea reseta parola pentru acest cont.',
  password_recovery_reminder_summary: 'Introduceți adresa dvs. de e-mail. Veți primi un e-mail cu un link pentru a vă putea schimba parola în pasul următor.',
  password_recovery_reset_summary: 'Utilizați codul pe care l-ați primit pentru a seta o nouă parolă pentru contul dvs.',
  password_recovery_reset_success: 'Parola dvs. a fost modificată cu succes și ați fost logat.',
  new_password: 'Parolă nouă',
  confirm_new_password: 'Confirmare parolă nouă',
  go_to_dashboard: 'Mergeți la panoul de control',

  /* overview */
  overview_title: 'Prezentare generală',
  overview: 'Prezentare generală',
  color: 'Culoare',
  state: 'Pornit/oprit',
  connection_state: 'Starea conexiunii',

  /* hives */
  locations_title: 'Beep',
  hives_title: 'Beep',
  Hive: 'Stup',
  hive: 'stup',
  Location: 'Stupină',
  location: 'stupină',
  Hives: 'Stupi',
  hives: 'stupi',
  Locations: 'Stupine',
  locations: 'Stupine',
  Name: 'Nume',
  name: 'nume',
  Type: 'Tip',
  type: 'tip',
  Layer: 'Strat',
  layer: 'strat',
  brood: 'Puiet',
  honey: 'Miere',
  inspect: 'Inspect',
  inspection: 'inspecție',
  Inspection: 'Inspecție',
  Inspections: 'Inspecții',
  New_inspection: 'Inspecție nouă',
  Edit_inspection: 'Editați inspecția',
  Actions: 'Actiune',
  Conditions: 'Condiții(inspectate)',
  edit: 'Editare',
  Hive_brood_layers: 'Straturi cu puiet',
  Hive_honey_layers: 'Straturi cu miere',
  Hive_layer_amount: 'Numărul de straturi',
  Bee_race: 'Rasa albinelor',
  Birth_date: 'Data nașterii',
  Color: 'Culoare',
  Queen_colored: 'Matcă marcată',
  Queen_clipped: 'Matcă mutilată',
  Queen_fertilized: 'Matcă fertilizată',
  Age: 'Vârstă',
  year: 'ani',

  /* Hive check items */
  Date_of_inspection: 'Data inspecției',
  action: 'Acțiune',
  reminder: 'Memento',
  remind_date: 'Data notificării',
  overall: 'În ansamblu',
  positive_impression: 'Impresia generală',
  needs_attention: 'Necesită atenție',
  notes: 'Notițe',
  notes_for_next_inspection: 'Notă scurtă pentru inspecția următoare (vizibilă în ansamblu)',
  Not_implemented_yet: 'Acest element nu este încă implementat',
  save_input_first: 'Doriți să salvați prima intrare?',

  /* dashboard */
  dashboard_title: 'Tablou de bord',
  dashboard: 'Tablou de bord',
  measurements: 'Măsurători',
  measurementsError: 'Nu pot fi încărcate măsurătorile, verificați conexiunea la rețea',
  last_measurement: 'Ultima măsurare',
  at: 'la',
  measurement_system: 'Sistem de măsurare Beep',
  no_data: 'Nu există date disponibile',
  no_chart_data: 'Nu există date din grafic pentru perioada selectată',

  /* settings */
  General: 'General'
}, _defineProperty(_LANG$ro, "Location", 'Locație'), _defineProperty(_LANG$ro, "Country", 'Țară'), _defineProperty(_LANG$ro, "City", 'Oraș'), _defineProperty(_LANG$ro, "Address", 'Adresă'), _defineProperty(_LANG$ro, "Lattitude", 'Latitudine'), _defineProperty(_LANG$ro, "Longitude", 'Longitudine'), _defineProperty(_LANG$ro, "Street", 'Stradă'), _defineProperty(_LANG$ro, "Number", 'Nr.'), _defineProperty(_LANG$ro, "Postal_code", 'Cod poștal'), _defineProperty(_LANG$ro, "Description", 'Descriere'), _defineProperty(_LANG$ro, "Hive_settings", 'Setările stupului'), _defineProperty(_LANG$ro, "Hive_amount", 'Numărul de stupi în această locație'), _defineProperty(_LANG$ro, "Hive_prefix", 'Prefixul stupului (înainte de număr)'), _defineProperty(_LANG$ro, "Hive_number_offset", 'Numărul de pornire al stupilor'), _defineProperty(_LANG$ro, "Hive_type", 'Tipul de stup'), _defineProperty(_LANG$ro, "Hive_layers", 'Straturi stups'), _defineProperty(_LANG$ro, "Hive_frames", 'Rame pe strat'), _defineProperty(_LANG$ro, "Hive_color", 'Culoarea stupului'), _defineProperty(_LANG$ro, "Queen", 'Matcă'), _defineProperty(_LANG$ro, "queen", 'Matcă'), _defineProperty(_LANG$ro, "settings_title", 'Setări'), _defineProperty(_LANG$ro, "settings_description", 'Setări ale senzorilor'), _defineProperty(_LANG$ro, "settings", 'Setări'), _defineProperty(_LANG$ro, "sensors_title", 'Setările senzorului'), _defineProperty(_LANG$ro, "sensors_description", 'Statusul senzorilor și înregisrtarea'), _defineProperty(_LANG$ro, "sensors", 'Senzori'), _defineProperty(_LANG$ro, "sensor", 'Senzor'), _defineProperty(_LANG$ro, "Select", 'Selectați'), _defineProperty(_LANG$ro, "Not_selected", 'Nu a fost selectat'), _defineProperty(_LANG$ro, "Poor", 'Slab'), _defineProperty(_LANG$ro, "Fair", 'Potrivit'), _defineProperty(_LANG$ro, "Average", 'Mediu'), _defineProperty(_LANG$ro, "Good", 'Bine'), _defineProperty(_LANG$ro, "Excellent", 'Excelent'), _defineProperty(_LANG$ro, "Low", 'Scăzut'), _defineProperty(_LANG$ro, "Medium", 'Mediu'), _defineProperty(_LANG$ro, "High", 'Ridicat'), _defineProperty(_LANG$ro, "Extreme", 'Extrem'), _defineProperty(_LANG$ro, "select_color", 'Selectați o culoare'), _defineProperty(_LANG$ro, "advanced", 'Avansat'), _defineProperty(_LANG$ro, "Select_sensor", 'Selectați un senzor'), _defineProperty(_LANG$ro, "temperature", 'Temperatură'), _defineProperty(_LANG$ro, "t", 'Temperatură'), _defineProperty(_LANG$ro, "light", 'Lumina (soarelui)'), _defineProperty(_LANG$ro, "l", 'Lumina (soarelui)'), _defineProperty(_LANG$ro, "water", 'Apă'), _defineProperty(_LANG$ro, "w", 'Apă'), _defineProperty(_LANG$ro, "humidity", 'Umiditate'), _defineProperty(_LANG$ro, "h", 'Umiditate'), _defineProperty(_LANG$ro, "air_pressure", 'Presiune atmosferică'), _defineProperty(_LANG$ro, "p", 'Presiune atmosferică'), _defineProperty(_LANG$ro, "weight", 'Greutate'), _defineProperty(_LANG$ro, "w_v", 'Senzorul de greutate, valoarea tuturor senzorilor'), _defineProperty(_LANG$ro, "w_fl", 'Senzorul de greutate, valorare față stânga'), _defineProperty(_LANG$ro, "w_fr", 'Senzorul de greutate, valoare față dreapta'), _defineProperty(_LANG$ro, "w_bl", 'Senzorul de greutate, valoare spate stânga'), _defineProperty(_LANG$ro, "w_br", 'Senzorul de greutate, valoare spate dreapta'), _defineProperty(_LANG$ro, "weight_kg", 'Greutate'), _defineProperty(_LANG$ro, "weight_kg_corrected", 'Greutate (corecție)'), _defineProperty(_LANG$ro, "weight_combined_kg", 'Greutate, combinată '), _defineProperty(_LANG$ro, "bat_volt", 'Baterie'), _defineProperty(_LANG$ro, "bv", 'Baterie'), _defineProperty(_LANG$ro, "sound_fanning_4days", 'Ventilație albine 4z'), _defineProperty(_LANG$ro, "s_fan_4", 'Ventilație albine 4z'), _defineProperty(_LANG$ro, "sound_fanning_6days", 'Ventilație albine 6z'), _defineProperty(_LANG$ro, "s_fan_6", 'Ventilație albine 6z'), _defineProperty(_LANG$ro, "sound_fanning_9days", 'Ventilație albine 9z'), _defineProperty(_LANG$ro, "s_fan_9", 'Ventilație albine 9z'), _defineProperty(_LANG$ro, "sound_flying_adult", 'Albine care zboară'), _defineProperty(_LANG$ro, "s_fly_a", 'Albine care zboară'), _defineProperty(_LANG$ro, "sound_total", 'Sunet total'), _defineProperty(_LANG$ro, "s_tot", 'Sunet total'), _defineProperty(_LANG$ro, "bee_count_in", 'Număr albine intrate'), _defineProperty(_LANG$ro, "bc_i", 'Număr albine intrate'), _defineProperty(_LANG$ro, "bee_count_out", 'Număr albine ieșite'), _defineProperty(_LANG$ro, "bc_o", 'Număr albine ieșite'), _defineProperty(_LANG$ro, "t_i", 'Temp. interior'), _defineProperty(_LANG$ro, "rssi", 'Puterea semnalului'), _defineProperty(_LANG$ro, "snr", 'Raport semnal/zgomot'), _defineProperty(_LANG$ro, "lat", 'Latitudine'), _defineProperty(_LANG$ro, "lon", 'Longitudine'), _defineProperty(_LANG$ro, "Sound_measurements", 'Măsurători sunet'), _defineProperty(_LANG$ro, "Sensor_info", 'Informații senzor'), _defineProperty(_LANG$ro, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$ro, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$ro, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$ro, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$ro, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$ro, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$ro, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$ro, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$ro, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$ro, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$ro, "hour", 'Oră'), _defineProperty(_LANG$ro, "day", 'Zi'), _defineProperty(_LANG$ro, "week", 'Săptămână'), _defineProperty(_LANG$ro, "month", 'Lună'), _defineProperty(_LANG$ro, "year", 'An'), _defineProperty(_LANG$ro, "could_not_load_settings", 'Setările nu au putut fi încărcate'), _defineProperty(_LANG$ro, "offline", 'Nici o conexiune'), _defineProperty(_LANG$ro, "remote", 'La distanță'), _defineProperty(_LANG$ro, "connected", 'Direct'), _defineProperty(_LANG$ro, "yes", 'Da'), _defineProperty(_LANG$ro, "no", 'Nu'), _defineProperty(_LANG$ro, "footer_text", 'Apicultură cu sursă deschisă'), _defineProperty(_LANG$ro, "beep_foundation", 'fundația BEEP'), _defineProperty(_LANG$ro, "Checklist", 'Listă de verificare'), _defineProperty(_LANG$ro, "Checklist_items", 'Elemente din lista de verificare'), _defineProperty(_LANG$ro, "edit_hive_checklist", 'Bifați/debifați casetele din lista de mai jos pentru a adăuga/elimina elemente din lista de verificare a stupului. De asemenea, puteți desfășura/acoperi și glisa/fixa elementele pentru a le rearanja stilului dumneavoastră. Recomandare: dacă introduceți un termen în câmpul de căutare, toate elementele care conțin termenul respectiv vor apărea pe ecran și vor avea culoarea roșie.'), _defineProperty(_LANG$ro, "Data_export", 'Exportarea datelor'), _defineProperty(_LANG$ro, "Export_your_data", 'Exportați toate datele din contul dvs. BEEP și trimiteți un e-mail care conține datele într-un fișier Excel. Fișierul va avea file diferite care conțin datele dvs. personale despre stup, locație și inspecție. '), _defineProperty(_LANG$ro, "Terms_of_use", 'Termenii serviciului'), _defineProperty(_LANG$ro, "accept_policy", 'Accept termenii serviciului BEEP, care sunt compatibili cu noua lege Europeană privind confidențialitatea datelor.'), _defineProperty(_LANG$ro, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$ro, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$ro, "approve_policy", 'Nu v-ați dat încă acordul pentru ultimii termeni și condiții.'), _defineProperty(_LANG$ro, "calibrate_weight", 'Calivrați greutatea'), _defineProperty(_LANG$ro, "calibrate_explanation", 'Setați greutatea senzorilor la 0 scăzând valoarea măsurată curentă.'), _defineProperty(_LANG$ro, "set_as_zero_value", 'Setați aceste valori ca valoare 0.'), _defineProperty(_LANG$ro, "set_weight_factor", 'Definiți factorul de greutate'), _defineProperty(_LANG$ro, "own_weight_kg", 'Care este greutatea proprie în Kg?'), _defineProperty(_LANG$ro, "start_calibration", 'Acum, urcațivă pe cântar și apăsați butonul de mai jos pentru a defini factorul greutate. Distribuiți greutatea în mod egal.'), _defineProperty(_LANG$ro, "currently_there_is", 'există o greutate de'), _defineProperty(_LANG$ro, "nothing", 'nimic'), _defineProperty(_LANG$ro, "on_the_scale", 'pe cântar'), _defineProperty(_LANG$ro, "calibration_started", 'Calibrarea a început... Vă rugîm așteptați ca măsuratoarea următoare să intre în vigoare.'), _defineProperty(_LANG$ro, "calibration_ended", 'Calbrarea a reușit!'), _defineProperty(_LANG$ro, "server_down", 'Aplicația nu este disponibilă din cauza lucrărilor de întreținere, vă rugăm încercați din nou mai târziu.'), _defineProperty(_LANG$ro, "add_to_calendar", 'Adăugați în calendar'), _defineProperty(_LANG$ro, "sort_on", 'Sortează pe'), _defineProperty(_LANG$ro, "Whats_new", 'Nou în v2.1!'), _defineProperty(_LANG$ro, "Manual", 'Manual'), _defineProperty(_LANG$ro, "Site_title", 'BEEP | Bee monitor'), _defineProperty(_LANG$ro, "could_not_create_user", 'Utilizatorul nu poate fi creat în acest moment. Ne pare rău pentru neplăcerile cauzate, vă rugăm încercațimai târziu.'), _defineProperty(_LANG$ro, "email_verified", 'Adresa dvs. de e-mail a fost verificată.'), _defineProperty(_LANG$ro, "email_not_verified", 'Adresa dvs. de e-mail nu a fost încă verificată'), _defineProperty(_LANG$ro, "email_new_verification", 'Faceți clic pe acest linl pentru a trimite un nou e-mail de verificare.'), _defineProperty(_LANG$ro, "email_verification_sent", 'Un mesaj cu un link pentru verificare a fost trimis pe adresa dvs. de e-mail. Faceți click pe link-ul din e-mail pentru a vă activa contul și pentru a vă conecta.'), _defineProperty(_LANG$ro, "not_filled", 'este necesar, dar nu a fost completat'), _defineProperty(_LANG$ro, "cannot_deselect", 'Acest element nu poate fi eliminat, deoarece conține un element obligatoriu'), _defineProperty(_LANG$ro, "sensor_key", 'Cheie senzor'), _defineProperty(_LANG$ro, "Undelete", 'Nu ștergeți'), _defineProperty(_LANG$ro, "the_field", 'Acest'), _defineProperty(_LANG$ro, "is_required", 'este necesar.'), _defineProperty(_LANG$ro, "No_groups", 'Nu există grupuri disponibile'), _defineProperty(_LANG$ro, "not_available_yet", 'nu este încă disponibil. Vă rugăm să creați primul aici.'), _defineProperty(_LANG$ro, "Users", 'Utilizatori'), _defineProperty(_LANG$ro, "Member", 'Membrul grupului'), _defineProperty(_LANG$ro, "Members", 'Membrii grupului'), _defineProperty(_LANG$ro, "Invite", 'Invită'), _defineProperty(_LANG$ro, "Invited", 'Invitat'), _defineProperty(_LANG$ro, "invitations", 'invitații'), _defineProperty(_LANG$ro, "Admin", 'Administrator'), _defineProperty(_LANG$ro, "Creator", 'Proprietarul grupului'), _defineProperty(_LANG$ro, "Groups", 'Colabora'), _defineProperty(_LANG$ro, "Group", 'Grup de colaborare'), _defineProperty(_LANG$ro, "group", 'grup de colaborare'), _defineProperty(_LANG$ro, "to_share", 'pentru a partaja cu acest grup. 1 click = numai membrii grupului pot vedea, 2 click-uri = membrii grupului pot edita.'), _defineProperty(_LANG$ro, "Invitation_accepted", 'Invitație acceptată'), _defineProperty(_LANG$ro, "Accept", 'Accept'), _defineProperty(_LANG$ro, "My_shared", 'Partajarea mea'), _defineProperty(_LANG$ro, "invitee_name", 'Nume invitat'), _defineProperty(_LANG$ro, "Remove_group", 'Sigur doriți să eliminați complet acest grup partajat pentru toți membrii acestuia.'), _defineProperty(_LANG$ro, "Detach_from_group", 'Scoateți-mă pe mine și stupii mei din acest grup'), _defineProperty(_LANG$ro, "my_hive", 'Stupul meu'), _defineProperty(_LANG$ro, "created", 'creat'), _defineProperty(_LANG$ro, "group_detached", 'grupul a fost părăsit cu succes'), _defineProperty(_LANG$ro, "group_activated", 'Invitația în grup a fost acceptată.'), _defineProperty(_LANG$ro, "group_explanation_1", '1. Creați un nou grup de colaborare cu un titlu clar și o descriere opțională'), _defineProperty(_LANG$ro, "group_explanation_2", '2. Invitați alți utilizatori Beep adresa lor de e-mail Beep'), _defineProperty(_LANG$ro, "group_explanation_3", '3. Împărțiți informații specifice despre anumite colonii, care pot fi văzute de alți utilizatori pentru a coopera pe'), _defineProperty(_LANG$ro, "Filter_and_sort_on", 'Filtrați și sortați pe:'), _LANG$ro);
/*
 * Beep - Translations
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

LANG['pt'] = (_LANG$pt = {
  /* Date picker */
  monthsFull: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  weekdaysFull: ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'],
  weekdaysShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
  Today: 'Hoje',
  Clear: 'Limpar',
  Close: 'Fechar',
  firstDay: 1,
  format: 'dddd d mmmm yyyy',

  /* main */
  Website: 'Site da internet',
  Feedback: 'Comentários',
  Feedback_mail_header: 'Comentários da aplicação Beep',
  Feedback_mail_body: 'Cara fundação Beep,%0D%0A%0D%0ADeixo abaixo os meus comentários sobre a aplicação Beep.%0D%0A%0D%0AEncontrei isto:%0D%0A%0D%0AMesmo antes de isto acontecer, fiz o seguinte:%0D%0A%0D%0AO monitor estava assim (por favor inclua uma captura de ecrã):%0D%0A%0D%0A%0D%0A%0D%0A',
  Diagnostic_info: 'Informação de diagnóstico (no caso de um bug/problema):%0D%0A',
  back: 'Para trás',
  menu: 'Menu',
  lighting: 'Iluminação',
  camera: 'Câmera',
  weather: 'Tempo',
  sensors: 'Sensores',
  sensors_na: 'Os sensores BEEP sensors para monitorizar remotamente a sua colmeia estarão brevemente disponíveis...',
  no_valid_authentication: 'Nenhuns dados de autenticação recebidos',
  succesfully_saved: 'Salvo com sucesso',
  switch_language: 'Trocar linguagem',
  Delete: 'Apagar',
  Search: 'Procurar...',

  /* user error messages */
  User: 'Utilizador',
  User_data: 'Dados de utilizador',
  user_data: 'dados de utilizador ',
  updated: 'atualizado',
  delete_complete_account: 'Tem a certeza que quer apagar a sua conta por completo, incluindo todos os apiários, colmeias e inspeções? Esta ação é irrecuperável.',
  username_is_required: 'Por favor introduza o seu nome de usuário',
  username_already_exists: 'Nome de usuário já existe',
  password_is_required: 'Por favor introduza uma senha',
  email_is_required: 'Por favor introduza um email válido',
  email_already_exists: 'Este email já está a ser utilizado',
  'policy accepted_is_required': 'Precisa de aceitar os termos de serviço para se poder registar',
  already_registered: 'Já estou registado',
  invalid_user: 'Usuário desconhecido ou senha errada',
  invalid_password: 'Senha muito curta (min. 8 caracteres)',
  no_password_match: 'As senhas não coincidem',
  invalid_token: 'Código inválido',
  no_valid_email: 'Email inválido',
  empty_fields: 'Por favor preencha todos os campos',
  match_passwords: 'As passwords não coincidem',
  succesfully_registered: 'Foi registado com sucesso.',
  authentication_failed: 'Erro de autenticação',
  no_valid_input_received: 'Os dados não puderam ser salvos, nenhuma entrada válida recebida.',
  remove_all_settings: 'Remover todas as configurações',
  remove_apiary: 'Remover apiário',
  remove_hive: 'Remover colmeia',
  remove_inspection: 'Remover inspeção',
  Error: 'Erro',
  Warning: 'Aviso',
  first_remove_hives: 'Atenção: ainda há colmeias neste apiário. Pode salvar colmeias específicas (e suas inspeções) movendo-as primeiro para outro apiário. Se continuar com a eliminação, excluirá TODAS as colmeias e inspeções presentes neste local.',
  Date: 'Data',
  ok: 'Ok',
  previous: 'Anterior',
  prev: 'anterior',
  next: 'Próximo',
  add: 'Adicionar',
  create_new: 'Criar novo',
  New: 'Novo',
  warning: 'Aviso',
  apply: 'Aplicar',
  Cancel: 'Cancelar',
  automatic: 'Automático',
  manually: 'Manual',
  on: 'On',
  off: 'Off',

  /* login */
  login_title: 'Login',
  login: 'Login',
  back_to_login: 'Regressar ao login',
  forgot_password: 'Esqueceu-se da sua senha?',
  username: 'Nome de usuário',
  password: 'Password',
  confirm_password: 'Confirmar senha',
  email: 'Email',
  token: 'Código',
  create_login_question: 'Ainda não tem conta? Registe-se como novo usuário',
  create_login: 'Registo como novo usuário',
  create_login_summary: 'Criar uma nova conta de usuário',
  save: 'Salvar',
  save_and_return: 'Salvar e regressar',
  logout: 'Sair',
  logout_title: 'Sair como ',
  logout_now: 'Quer mesmo sair agora?',
  member_since: 'A usar o Beep desde',

  /* password recovery */
  password_recovery_title: ' Esqueceu-se da sua senha?',
  password_recovery_remembered: 'Oh, lembrei-me agora da minha senha!',
  password_recovery_user: 'Informação de usuário',
  password_recovery_send_mail: 'Enviar código de verificação',
  password_recovery_code_not_received: 'Código de verificação não recebido no espaço de 5 minutos? ',
  password_recovery_enter_code: 'Já tem um código de verificação? Coloque-o aqui',
  password_recovery_reset_title: 'Insira nova senha',
  password_recovery_reset_password: 'Alterar senha',
  password_recovery_reminder_success: ' Foi enviado um email. Clique no link do seu email para redefinir sua senha para esta conta.',
  password_recovery_reminder_summary: ' Insira o seu endereço de email. Receberá um email com um link para alterar sua senha na próxima etapa.',
  password_recovery_reset_summary: 'Use o código que recebeu para definir uma nova senha para a sua conta',
  password_recovery_reset_success: 'A sua senha foi alterada com sucesso e está logado.',
  new_password: 'Nova senha',
  confirm_new_password: 'Confirmar nova senha',
  go_to_dashboard: 'Ir para o meu painel',

  /* overview */
  overview_title: 'Visão global',
  overview: 'Visão global',
  color: 'Cor',
  state: 'On/off',
  connection_state: 'Estado da conexão',

  /* hives */
  locations_title: 'Beep',
  hives_title: 'Beep',
  Hive: 'Colmeia',
  hive: 'colmeia',
  Location: 'Apiário',
  location: 'apiário',
  Hives: 'Colmeias',
  hives: 'Colmeias',
  Locations: 'Apiários',
  locations: 'Apiários',
  Name: 'Nome',
  name: 'nome',
  Type: 'Tipo',
  type: 'tipo',
  Layer: 'Alça',
  layer: 'alça',
  brood: 'Criação',
  honey: 'Mel',
  inspect: 'Inspecionar',
  inspection: 'inspeção',
  Inspection: 'Inspeção',
  Inspections: 'Inspeções',
  New_inspection: 'Nova inspeção',
  Edit_inspection: 'Editar inspeção',
  Actions: 'Ações',
  Conditions: 'Condições (inspecionada)',
  edit: 'Edição',
  Hive_brood_layers: 'Alças de criação',
  Hive_honey_layers: 'Alças de mel',
  Hive_layer_amount: 'Quantidade de alças',
  Bee_race: 'Raça de abelha',
  Birth_date: 'Data de nascimento',
  Color: 'Cor',
  Queen_colored: 'Rainha com cor',
  Queen_clipped: 'Rainha com asa cortada',
  Queen_fertilized: 'Rainha fertilizada',
  Age: 'Idade',
  year: 'anos',

  /* Hive check items */
  Date_of_inspection: 'Data de inspeção',
  action: 'Ação',
  reminder: 'Lembrete',
  remind_date: 'Data de notificação',
  overall: 'No geral',
  positive_impression: 'Impressão total',
  needs_attention: 'Precisa de atenção',
  notes: 'Notas',
  notes_for_next_inspection: 'Nota curta para a próxima inspecção (visível na visão global)',
  Not_implemented_yet: ' Este item ainda não foi implementado',
  save_input_first: ' Deseja salvar a sua entrada primeiro?',

  /* dashboard */
  dashboard_title: 'Painel de controlo',
  dashboard: 'Painel de controlo',
  measurements: 'Medições',
  measurementsError: 'Não é possível carregar medições, verificar ligação à internet',
  last_measurement: 'Última medição',
  at: 'em',
  measurement_system: 'Sistema de medição Beep',
  no_data: 'Sem dados disponíveis',
  no_chart_data: 'Nenhum dado do gráfico para o período selecionado',

  /* settings */
  General: 'Geral'
}, _defineProperty(_LANG$pt, "Location", 'Localização'), _defineProperty(_LANG$pt, "Country", 'País'), _defineProperty(_LANG$pt, "City", 'Cidade'), _defineProperty(_LANG$pt, "Address", 'Morada'), _defineProperty(_LANG$pt, "Lattitude", 'Latitude'), _defineProperty(_LANG$pt, "Longitude", 'Longitude'), _defineProperty(_LANG$pt, "Street", 'Rua'), _defineProperty(_LANG$pt, "Number", 'Número'), _defineProperty(_LANG$pt, "Postal_code", 'Código Postal'), _defineProperty(_LANG$pt, "Description", 'Descrição'), _defineProperty(_LANG$pt, "Hive_settings", 'Configurações da colmeia'), _defineProperty(_LANG$pt, "Hive_amount", 'Quantidade de colmeias nesta localização'), _defineProperty(_LANG$pt, "Hive_prefix", 'Prefixo do nome da colmeia (antes do número)'), _defineProperty(_LANG$pt, "Hive_number_offset", 'Número de colmeias inciais'), _defineProperty(_LANG$pt, "Hive_type", 'Tipo de colmeias'), _defineProperty(_LANG$pt, "Hive_layers", 'Alças da colmeia'), _defineProperty(_LANG$pt, "Hive_frames", 'Quadros por alça'), _defineProperty(_LANG$pt, "Hive_color", 'Cor da colmeia'), _defineProperty(_LANG$pt, "Queen", 'Rainha'), _defineProperty(_LANG$pt, "queen", 'rainha'), _defineProperty(_LANG$pt, "settings_title", 'Definições'), _defineProperty(_LANG$pt, "settings_description", 'Definições dos sensores'), _defineProperty(_LANG$pt, "settings", 'Definições'), _defineProperty(_LANG$pt, "sensors_title", 'Definições do sensor'), _defineProperty(_LANG$pt, "sensors_description", 'Estado dos sensores e registo'), _defineProperty(_LANG$pt, "sensors", 'Sensores'), _defineProperty(_LANG$pt, "sensor", 'Sensor'), _defineProperty(_LANG$pt, "Select", 'Selecionar'), _defineProperty(_LANG$pt, "Not_selected", 'Não selecionado'), _defineProperty(_LANG$pt, "Poor", 'Pobre'), _defineProperty(_LANG$pt, "Fair", 'Razoável'), _defineProperty(_LANG$pt, "Average", 'Médio'), _defineProperty(_LANG$pt, "Good", 'Bom'), _defineProperty(_LANG$pt, "Excellent", 'Excelente'), _defineProperty(_LANG$pt, "Low", 'Baixo'), _defineProperty(_LANG$pt, "Medium", 'Médio'), _defineProperty(_LANG$pt, "High", 'Alto'), _defineProperty(_LANG$pt, "Extreme", 'Extremo'), _defineProperty(_LANG$pt, "select_color", 'Selecionar uma cor'), _defineProperty(_LANG$pt, "advanced", 'Avançado'), _defineProperty(_LANG$pt, "Select_sensor", 'Selecionar um sensor'), _defineProperty(_LANG$pt, "temperature", 'Temperatura'), _defineProperty(_LANG$pt, "t", 'Temperatura'), _defineProperty(_LANG$pt, "light", 'Luz solar'), _defineProperty(_LANG$pt, "l", 'Luz solar'), _defineProperty(_LANG$pt, "water", 'Água'), _defineProperty(_LANG$pt, "w", 'Água'), _defineProperty(_LANG$pt, "humidity", 'Humidade'), _defineProperty(_LANG$pt, "h", 'Humidade'), _defineProperty(_LANG$pt, "air_pressure", 'Pressão atmosférica'), _defineProperty(_LANG$pt, "p", 'Pressão atmosférica'), _defineProperty(_LANG$pt, "weight", 'Peso'), _defineProperty(_LANG$pt, "w_v", 'Sensor de peso valor de todos os sensores'), _defineProperty(_LANG$pt, "w_fl", 'Sensor de peso valor frente esquerda'), _defineProperty(_LANG$pt, "w_fr", 'Sensor de peso valor frente direita'), _defineProperty(_LANG$pt, "w_bl", 'Sensor de peso valor trás esquerda'), _defineProperty(_LANG$pt, "w_br", 'Sensor de peso valor trás direita'), _defineProperty(_LANG$pt, "weight_kg", 'Peso'), _defineProperty(_LANG$pt, "weight_kg_corrected", 'Peso (corr)'), _defineProperty(_LANG$pt, "weight_combined_kg", 'Peso combi'), _defineProperty(_LANG$pt, "bat_volt", 'Bateria'), _defineProperty(_LANG$pt, "bv", 'Bateria'), _defineProperty(_LANG$pt, "sound_fanning_4days", 'Ventilação 4d abelhas'), _defineProperty(_LANG$pt, "s_fan_4", 'Ventilação 4d abelhas'), _defineProperty(_LANG$pt, "sound_fanning_6days", 'Ventilação 6d abelhas'), _defineProperty(_LANG$pt, "s_fan_6", 'Ventilação 6d abelhas'), _defineProperty(_LANG$pt, "sound_fanning_9days", 'Ventilação 9d abelhas'), _defineProperty(_LANG$pt, "s_fan_9", 'Ventilação 9d abelhas'), _defineProperty(_LANG$pt, "sound_flying_adult", 'Abelhas em voo'), _defineProperty(_LANG$pt, "s_fly_a", 'Abelhas em voo'), _defineProperty(_LANG$pt, "sound_total", 'Som total'), _defineProperty(_LANG$pt, "s_tot", 'Som total'), _defineProperty(_LANG$pt, "bee_count_in", 'Contagem de abelhas dentro'), _defineProperty(_LANG$pt, "bc_i", 'Contagem de abelhas dentro'), _defineProperty(_LANG$pt, "bee_count_out", 'Contagem de abelhas fora'), _defineProperty(_LANG$pt, "bc_o", 'Contagem de abelhas fora'), _defineProperty(_LANG$pt, "t_i", 'Temp. dentro'), _defineProperty(_LANG$pt, "rssi", 'Força do sinal'), _defineProperty(_LANG$pt, "snr", 'Ruido do sinal'), _defineProperty(_LANG$pt, "lat", 'Latitude'), _defineProperty(_LANG$pt, "lon", 'Longitude'), _defineProperty(_LANG$pt, "Sound_measurements", 'Medições de som'), _defineProperty(_LANG$pt, "Sensor_info", 'Informação do sensor'), _defineProperty(_LANG$pt, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$pt, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$pt, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$pt, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$pt, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$pt, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$pt, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$pt, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$pt, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$pt, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$pt, "hour", 'Hora'), _defineProperty(_LANG$pt, "day", 'Dia'), _defineProperty(_LANG$pt, "week", 'Semana'), _defineProperty(_LANG$pt, "month", 'Mês'), _defineProperty(_LANG$pt, "year", 'Ano'), _defineProperty(_LANG$pt, "could_not_load_settings", 'Não foi possível carregar as configurações'), _defineProperty(_LANG$pt, "offline", 'Sem coneção'), _defineProperty(_LANG$pt, "remote", 'Remota'), _defineProperty(_LANG$pt, "connected", 'Direta'), _defineProperty(_LANG$pt, "yes", 'Sim'), _defineProperty(_LANG$pt, "no", 'Não'), _defineProperty(_LANG$pt, "footer_text", 'Apicultura em código aberto'), _defineProperty(_LANG$pt, "beep_foundation", 'A fundação BEEP'), _defineProperty(_LANG$pt, "Checklist", 'Lista de verificação'), _defineProperty(_LANG$pt, "Checklist_items", 'Itens da lista de verificação'), _defineProperty(_LANG$pt, "edit_hive_checklist", 'Marque/desmarque as caixas na lista abaixo para adicionar/remover itens da sua lista de verificação da colmeia. Também pode desdobrar/dobrar e arrastar/soltar os itens para reordená-los ao seu próprio estilo. Dica: se digitar um termo no campo de pesquisa, todos os itens que contenham esse termo serão dobrados e ficarão vermelhos.'), _defineProperty(_LANG$pt, "Data_export", 'Exportação de dados'), _defineProperty(_LANG$pt, "Export_your_data", 'Exportar todos os dados que estão na sua conta Beep e enviar um email contendo os dados como um ficheiro Excel. O ficheiro Excel possui seções diferentes que contêm os seus dados pessoais, das colmeias, de localização e de inspeção.'), _defineProperty(_LANG$pt, "Terms_of_use", 'Termos de serviço'), _defineProperty(_LANG$pt, "accept_policy", 'Aceito os termos de serviço do BEEP, compatíveis com a nova lei de privacidade europeia'), _defineProperty(_LANG$pt, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$pt, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$pt, "approve_policy", 'Você ainda não cumpriu com os últimos termos de serviço.'), _defineProperty(_LANG$pt, "calibrate_weight", 'Calibrar peso'), _defineProperty(_LANG$pt, "calibrate_explanation", 'Defina o peso dos sensores para 0 subtraindo o valor atual da medição.'), _defineProperty(_LANG$pt, "set_as_zero_value", 'D efina esses valores como 0'), _defineProperty(_LANG$pt, "set_weight_factor", 'Definir fator de peso'), _defineProperty(_LANG$pt, "own_weight_kg", 'Qual é o seu próprio peso em kg?'), _defineProperty(_LANG$pt, "start_calibration", 'Agora, pise na balança e pressione o botão abaixo para definir o fator de peso. Distribua seu peso igualmente.'), _defineProperty(_LANG$pt, "currently_there_is", 'Há um peso de'), _defineProperty(_LANG$pt, "nothing", 'nada'), _defineProperty(_LANG$pt, "on_the_scale", 'Na balança'), _defineProperty(_LANG$pt, "calibration_started", 'A calibração iniciou... Aguarde que a próxima medição entre em vigor.'), _defineProperty(_LANG$pt, "calibration_ended", 'Calibração bem sucedida!'), _defineProperty(_LANG$pt, "server_down", 'A app está indisponível devido a trabalhos de manutenção. Tente novamente mais tarde'), _defineProperty(_LANG$pt, "add_to_calendar", 'Adicionar ao calendário'), _defineProperty(_LANG$pt, "sort_on", 'Classificar em'), _defineProperty(_LANG$pt, "Whats_new", 'Novo na v2.1!'), _defineProperty(_LANG$pt, "Manual", 'Manual'), _defineProperty(_LANG$pt, "Site_title", 'BEEP | Monitorização de abelhas'), _defineProperty(_LANG$pt, "could_not_create_user", 'O usuário não pode ser criado neste momento. Desculpe pelo transtorno, tente novamente mais tarde.'), _defineProperty(_LANG$pt, "email_verified", 'O seu endereço de email foi verificado.'), _defineProperty(_LANG$pt, "email_not_verified", 'O seu endereço de email ainda não foi verificado.'), _defineProperty(_LANG$pt, "email_new_verification", ' Clique neste link para enviar um novo email de verificação.'), _defineProperty(_LANG$pt, "email_verification_sent", 'Uma mensagem com um link de verificação foi enviada para o seu endereço de email. Clique no link do email para ativar sua conta e fazer login.'), _defineProperty(_LANG$pt, "not_filled", 'é obrigatório, mas não está preenchido'), _defineProperty(_LANG$pt, "cannot_deselect", 'Não foi possível remover este item, pois contém um item obrigatório'), _defineProperty(_LANG$pt, "sensor_key", 'Sensor chave'), _defineProperty(_LANG$pt, "Undelete", 'Não apagar'), _defineProperty(_LANG$pt, "the_field", 'O'), _defineProperty(_LANG$pt, "is_required", 'é obrigatório'), _defineProperty(_LANG$pt, "No_groups", 'Nenhum grupo disponível'), _defineProperty(_LANG$pt, "not_available_yet", 'ainda não disponível. Por favor, crie o primeiro aqui.'), _defineProperty(_LANG$pt, "Users", 'Usuários'), _defineProperty(_LANG$pt, "Member", 'Membro do grupo'), _defineProperty(_LANG$pt, "Members", 'Membros do grupo'), _defineProperty(_LANG$pt, "Invite", 'Convidar'), _defineProperty(_LANG$pt, "Invited", 'Convidado'), _defineProperty(_LANG$pt, "invitations", 'Convites'), _defineProperty(_LANG$pt, "Admin", 'Administrador'), _defineProperty(_LANG$pt, "Creator", 'Proprietário do grupo'), _defineProperty(_LANG$pt, "Groups", 'Colaborar'), _defineProperty(_LANG$pt, "Group", 'Grupo de colaboração'), _defineProperty(_LANG$pt, "group", 'grupo de colaboração'), _defineProperty(_LANG$pt, "to_share", 'para partilhar com este grupo. 1 clique = os membros do grupo apenas podem visualizar, 2 cliques = os membros do grupo podem editar'), _defineProperty(_LANG$pt, "Invitation_accepted", 'Convite aceite'), _defineProperty(_LANG$pt, "Accept", 'Aceitar'), _defineProperty(_LANG$pt, "My_shared", 'Minha partilha'), _defineProperty(_LANG$pt, "invitee_name", 'Nome do convidado'), _defineProperty(_LANG$pt, "Remove_group", 'Tem a certeza de que deseja remover completamente este grupo de partilha para todos os seus membros '), _defineProperty(_LANG$pt, "Detach_from_group", 'Remova-me e às minhas colmeias deste grupo'), _defineProperty(_LANG$pt, "my_hive", 'Minha colmeia'), _defineProperty(_LANG$pt, "created", 'criada'), _defineProperty(_LANG$pt, "group_detached", 'Deixou o grupo com sucesso'), _defineProperty(_LANG$pt, "group_activated", 'Convite para o grupo aceite'), _defineProperty(_LANG$pt, "group_explanation_1", '1. Crie um novo grupo de cooperação com um título claro e uma descrição opcional'), _defineProperty(_LANG$pt, "group_explanation_2", '2. Convide outros usuários do Beep através do seu endereço de email do Beep'), _defineProperty(_LANG$pt, "group_explanation_3", '3. Compartilhar colmeias específicas para serem vistas por outras pessoas, ou para cooperar'), _defineProperty(_LANG$pt, "Filter_and_sort_on", 'Filtrar e classificar:'), _LANG$pt); //! moment.js locale configuration

;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  var monthsShortWithDots = 'jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.'.split('_'),
      monthsShortWithoutDots = 'jan_feb_mrt_apr_mei_jun_jul_aug_sep_okt_nov_dec'.split('_');
  var monthsParse = [/^jan/i, /^feb/i, /^maart|mrt.?$/i, /^apr/i, /^mei$/i, /^jun[i.]?$/i, /^jul[i.]?$/i, /^aug/i, /^sep/i, /^okt/i, /^nov/i, /^dec/i];
  var monthsRegex = /^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december|jan\.?|feb\.?|mrt\.?|apr\.?|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i;
  var nl = moment.defineLocale('nl', {
    months: 'januari_februari_maart_april_mei_juni_juli_augustus_september_oktober_november_december'.split('_'),
    monthsShort: function monthsShort(m, format) {
      if (!m) {
        return monthsShortWithDots;
      } else if (/-MMM-/.test(format)) {
        return monthsShortWithoutDots[m.month()];
      } else {
        return monthsShortWithDots[m.month()];
      }
    },
    monthsRegex: monthsRegex,
    monthsShortRegex: monthsRegex,
    monthsStrictRegex: /^(januari|februari|maart|april|mei|ju[nl]i|augustus|september|oktober|november|december)/i,
    monthsShortStrictRegex: /^(jan\.?|feb\.?|mrt\.?|apr\.?|mei|ju[nl]\.?|aug\.?|sep\.?|okt\.?|nov\.?|dec\.?)/i,
    monthsParse: monthsParse,
    longMonthsParse: monthsParse,
    shortMonthsParse: monthsParse,
    weekdays: 'zondag_maandag_dinsdag_woensdag_donderdag_vrijdag_zaterdag'.split('_'),
    weekdaysShort: 'zo._ma._di._wo._do._vr._za.'.split('_'),
    weekdaysMin: 'zo_ma_di_wo_do_vr_za'.split('_'),
    weekdaysParseExact: true,
    longDateFormat: {
      LT: 'HH:mm',
      LTS: 'HH:mm:ss',
      L: 'DD-MM-YYYY',
      LL: 'D MMMM YYYY',
      LLL: 'D MMMM YYYY HH:mm',
      LLLL: 'dddd D MMMM YYYY HH:mm'
    },
    calendar: {
      sameDay: '[vandaag om] LT',
      nextDay: '[morgen om] LT',
      nextWeek: 'dddd [om] LT',
      lastDay: '[gisteren om] LT',
      lastWeek: '[afgelopen] dddd [om] LT',
      sameElse: 'L'
    },
    relativeTime: {
      future: 'over %s',
      past: '%s geleden',
      s: 'een paar seconden',
      ss: '%d seconden',
      m: 'één minuut',
      mm: '%d minuten',
      h: 'één uur',
      hh: '%d uur',
      d: 'één dag',
      dd: '%d dagen',
      M: 'één maand',
      MM: '%d maanden',
      y: 'één jaar',
      yy: '%d jaar'
    },
    dayOfMonthOrdinalParse: /\d{1,2}(ste|de)/,
    ordinal: function ordinal(number) {
      return number + (number === 1 || number === 8 || number >= 20 ? 'ste' : 'de');
    },
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.

    }
  });
  return nl;
}); //! moment.js locale configuration


;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  function processRelativeTime(number, withoutSuffix, key, isFuture) {
    var format = {
      'm': ['eine Minute', 'einer Minute'],
      'h': ['eine Stunde', 'einer Stunde'],
      'd': ['ein Tag', 'einem Tag'],
      'dd': [number + ' Tage', number + ' Tagen'],
      'M': ['ein Monat', 'einem Monat'],
      'MM': [number + ' Monate', number + ' Monaten'],
      'y': ['ein Jahr', 'einem Jahr'],
      'yy': [number + ' Jahre', number + ' Jahren']
    };
    return withoutSuffix ? format[key][0] : format[key][1];
  }

  var de = moment.defineLocale('de', {
    months: 'Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember'.split('_'),
    monthsShort: 'Jan._Feb._März_Apr._Mai_Juni_Juli_Aug._Sep._Okt._Nov._Dez.'.split('_'),
    monthsParseExact: true,
    weekdays: 'Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag'.split('_'),
    weekdaysShort: 'So._Mo._Di._Mi._Do._Fr._Sa.'.split('_'),
    weekdaysMin: 'So_Mo_Di_Mi_Do_Fr_Sa'.split('_'),
    weekdaysParseExact: true,
    longDateFormat: {
      LT: 'HH:mm',
      LTS: 'HH:mm:ss',
      L: 'DD.MM.YYYY',
      LL: 'D. MMMM YYYY',
      LLL: 'D. MMMM YYYY HH:mm',
      LLLL: 'dddd, D. MMMM YYYY HH:mm'
    },
    calendar: {
      sameDay: '[heute um] LT [Uhr]',
      sameElse: 'L',
      nextDay: '[morgen um] LT [Uhr]',
      nextWeek: 'dddd [um] LT [Uhr]',
      lastDay: '[gestern um] LT [Uhr]',
      lastWeek: '[letzten] dddd [um] LT [Uhr]'
    },
    relativeTime: {
      future: 'in %s',
      past: 'vor %s',
      s: 'ein paar Sekunden',
      ss: '%d Sekunden',
      m: processRelativeTime,
      mm: '%d Minuten',
      h: processRelativeTime,
      hh: '%d Stunden',
      d: processRelativeTime,
      dd: processRelativeTime,
      M: processRelativeTime,
      MM: processRelativeTime,
      y: processRelativeTime,
      yy: processRelativeTime
    },
    dayOfMonthOrdinalParse: /\d{1,2}\./,
    ordinal: '%d.',
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.

    }
  });
  return de;
}); //! moment.js locale configuration


;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  var monthsShortDot = 'ene._feb._mar._abr._may._jun._jul._ago._sep._oct._nov._dic.'.split('_'),
      _monthsShort = 'ene_feb_mar_abr_may_jun_jul_ago_sep_oct_nov_dic'.split('_');

  var monthsParse = [/^ene/i, /^feb/i, /^mar/i, /^abr/i, /^may/i, /^jun/i, /^jul/i, /^ago/i, /^sep/i, /^oct/i, /^nov/i, /^dic/i];
  var monthsRegex = /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre|ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i;
  var es = moment.defineLocale('es', {
    months: 'enero_febrero_marzo_abril_mayo_junio_julio_agosto_septiembre_octubre_noviembre_diciembre'.split('_'),
    monthsShort: function monthsShort(m, format) {
      if (!m) {
        return monthsShortDot;
      } else if (/-MMM-/.test(format)) {
        return _monthsShort[m.month()];
      } else {
        return monthsShortDot[m.month()];
      }
    },
    monthsRegex: monthsRegex,
    monthsShortRegex: monthsRegex,
    monthsStrictRegex: /^(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)/i,
    monthsShortStrictRegex: /^(ene\.?|feb\.?|mar\.?|abr\.?|may\.?|jun\.?|jul\.?|ago\.?|sep\.?|oct\.?|nov\.?|dic\.?)/i,
    monthsParse: monthsParse,
    longMonthsParse: monthsParse,
    shortMonthsParse: monthsParse,
    weekdays: 'domingo_lunes_martes_miércoles_jueves_viernes_sábado'.split('_'),
    weekdaysShort: 'dom._lun._mar._mié._jue._vie._sáb.'.split('_'),
    weekdaysMin: 'do_lu_ma_mi_ju_vi_sá'.split('_'),
    weekdaysParseExact: true,
    longDateFormat: {
      LT: 'H:mm',
      LTS: 'H:mm:ss',
      L: 'DD/MM/YYYY',
      LL: 'D [de] MMMM [de] YYYY',
      LLL: 'D [de] MMMM [de] YYYY H:mm',
      LLLL: 'dddd, D [de] MMMM [de] YYYY H:mm'
    },
    calendar: {
      sameDay: function sameDay() {
        return '[hoy a la' + (this.hours() !== 1 ? 's' : '') + '] LT';
      },
      nextDay: function nextDay() {
        return '[mañana a la' + (this.hours() !== 1 ? 's' : '') + '] LT';
      },
      nextWeek: function nextWeek() {
        return 'dddd [a la' + (this.hours() !== 1 ? 's' : '') + '] LT';
      },
      lastDay: function lastDay() {
        return '[ayer a la' + (this.hours() !== 1 ? 's' : '') + '] LT';
      },
      lastWeek: function lastWeek() {
        return '[el] dddd [pasado a la' + (this.hours() !== 1 ? 's' : '') + '] LT';
      },
      sameElse: 'L'
    },
    relativeTime: {
      future: 'en %s',
      past: 'hace %s',
      s: 'unos segundos',
      ss: '%d segundos',
      m: 'un minuto',
      mm: '%d minutos',
      h: 'una hora',
      hh: '%d horas',
      d: 'un día',
      dd: '%d días',
      M: 'un mes',
      MM: '%d meses',
      y: 'un año',
      yy: '%d años'
    },
    dayOfMonthOrdinalParse: /\d{1,2}º/,
    ordinal: '%dº',
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.

    }
  });
  return es;
}); //! moment.js locale configuration


;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  var fr = moment.defineLocale('fr', {
    months: 'janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'.split('_'),
    monthsShort: 'janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'.split('_'),
    monthsParseExact: true,
    weekdays: 'dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'.split('_'),
    weekdaysShort: 'dim._lun._mar._mer._jeu._ven._sam.'.split('_'),
    weekdaysMin: 'di_lu_ma_me_je_ve_sa'.split('_'),
    weekdaysParseExact: true,
    longDateFormat: {
      LT: 'HH:mm',
      LTS: 'HH:mm:ss',
      L: 'DD/MM/YYYY',
      LL: 'D MMMM YYYY',
      LLL: 'D MMMM YYYY HH:mm',
      LLLL: 'dddd D MMMM YYYY HH:mm'
    },
    calendar: {
      sameDay: '[Aujourd’hui à] LT',
      nextDay: '[Demain à] LT',
      nextWeek: 'dddd [à] LT',
      lastDay: '[Hier à] LT',
      lastWeek: 'dddd [dernier à] LT',
      sameElse: 'L'
    },
    relativeTime: {
      future: 'dans %s',
      past: 'il y a %s',
      s: 'quelques secondes',
      ss: '%d secondes',
      m: 'une minute',
      mm: '%d minutes',
      h: 'une heure',
      hh: '%d heures',
      d: 'un jour',
      dd: '%d jours',
      M: 'un mois',
      MM: '%d mois',
      y: 'un an',
      yy: '%d ans'
    },
    dayOfMonthOrdinalParse: /\d{1,2}(er|)/,
    ordinal: function ordinal(number, period) {
      switch (period) {
        // TODO: Return 'e' when day of month > 1. Move this case inside
        // block for masculine words below.
        // See https://github.com/moment/moment/issues/3375
        case 'D':
          return number + (number === 1 ? 'er' : '');
        // Words with masculine grammatical gender: mois, trimestre, jour

        default:
        case 'M':
        case 'Q':
        case 'DDD':
        case 'd':
          return number + (number === 1 ? 'er' : 'e');
        // Words with feminine grammatical gender: semaine

        case 'w':
        case 'W':
          return number + (number === 1 ? 're' : 'e');
      }
    },
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.

    }
  });
  return fr;
}); //! moment.js locale configuration


;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  function relativeTimeWithPlural(number, withoutSuffix, key) {
    var format = {
      'ss': 'secunde',
      'mm': 'minute',
      'hh': 'ore',
      'dd': 'zile',
      'MM': 'luni',
      'yy': 'ani'
    },
        separator = ' ';

    if (number % 100 >= 20 || number >= 100 && number % 100 === 0) {
      separator = ' de ';
    }

    return number + separator + format[key];
  }

  var ro = moment.defineLocale('ro', {
    months: 'ianuarie_februarie_martie_aprilie_mai_iunie_iulie_august_septembrie_octombrie_noiembrie_decembrie'.split('_'),
    monthsShort: 'ian._febr._mart._apr._mai_iun._iul._aug._sept._oct._nov._dec.'.split('_'),
    monthsParseExact: true,
    weekdays: 'duminică_luni_marți_miercuri_joi_vineri_sâmbătă'.split('_'),
    weekdaysShort: 'Dum_Lun_Mar_Mie_Joi_Vin_Sâm'.split('_'),
    weekdaysMin: 'Du_Lu_Ma_Mi_Jo_Vi_Sâ'.split('_'),
    longDateFormat: {
      LT: 'H:mm',
      LTS: 'H:mm:ss',
      L: 'DD.MM.YYYY',
      LL: 'D MMMM YYYY',
      LLL: 'D MMMM YYYY H:mm',
      LLLL: 'dddd, D MMMM YYYY H:mm'
    },
    calendar: {
      sameDay: '[azi la] LT',
      nextDay: '[mâine la] LT',
      nextWeek: 'dddd [la] LT',
      lastDay: '[ieri la] LT',
      lastWeek: '[fosta] dddd [la] LT',
      sameElse: 'L'
    },
    relativeTime: {
      future: 'peste %s',
      past: '%s în urmă',
      s: 'câteva secunde',
      ss: relativeTimeWithPlural,
      m: 'un minut',
      mm: relativeTimeWithPlural,
      h: 'o oră',
      hh: relativeTimeWithPlural,
      d: 'o zi',
      dd: relativeTimeWithPlural,
      M: 'o lună',
      MM: relativeTimeWithPlural,
      y: 'un an',
      yy: relativeTimeWithPlural
    },
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 7 // The week that contains Jan 7th is the first week of the year.

    }
  });
  return ro;
}); //! moment.js locale configuration


;

(function (global, factory) {
  (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === 'object' && typeof module !== 'undefined' && typeof require === 'function' ? factory(require('../moment')) : typeof define === 'function' && define.amd ? define(['../moment'], factory) : factory(global.moment);
})(this, function (moment) {
  'use strict';

  var pt = moment.defineLocale('pt', {
    months: 'Janeiro_Fevereiro_Março_Abril_Maio_Junho_Julho_Agosto_Setembro_Outubro_Novembro_Dezembro'.split('_'),
    monthsShort: 'Jan_Fev_Mar_Abr_Mai_Jun_Jul_Ago_Set_Out_Nov_Dez'.split('_'),
    weekdays: 'Domingo_Segunda-feira_Terça-feira_Quarta-feira_Quinta-feira_Sexta-feira_Sábado'.split('_'),
    weekdaysShort: 'Dom_Seg_Ter_Qua_Qui_Sex_Sáb'.split('_'),
    weekdaysMin: 'Do_2ª_3ª_4ª_5ª_6ª_Sá'.split('_'),
    weekdaysParseExact: true,
    longDateFormat: {
      LT: 'HH:mm',
      LTS: 'HH:mm:ss',
      L: 'DD/MM/YYYY',
      LL: 'D [de] MMMM [de] YYYY',
      LLL: 'D [de] MMMM [de] YYYY HH:mm',
      LLLL: 'dddd, D [de] MMMM [de] YYYY HH:mm'
    },
    calendar: {
      sameDay: '[Hoje às] LT',
      nextDay: '[Amanhã às] LT',
      nextWeek: 'dddd [às] LT',
      lastDay: '[Ontem às] LT',
      lastWeek: function lastWeek() {
        return this.day() === 0 || this.day() === 6 ? '[Último] dddd [às] LT' : // Saturday + Sunday
        '[Última] dddd [às] LT'; // Monday - Friday
      },
      sameElse: 'L'
    },
    relativeTime: {
      future: 'em %s',
      past: 'há %s',
      s: 'segundos',
      ss: '%d segundos',
      m: 'um minuto',
      mm: '%d minutos',
      h: 'uma hora',
      hh: '%d horas',
      d: 'um dia',
      dd: '%d dias',
      M: 'um mês',
      MM: '%d meses',
      y: 'um ano',
      yy: '%d anos'
    },
    dayOfMonthOrdinalParse: /\d{1,2}º/,
    ordinal: '%dº',
    week: {
      dow: 1,
      // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.

    }
  });
  return pt;
});
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: Czech
	file: DateTimePicker-i18n-cs
  	author: aiphee (https://github.com/aiphee)

*/


(function ($) {
  $.DateTimePicker.i18n["cs"] = $.extend($.DateTimePicker.i18n["cs"], {
    language: "cs",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Ne", "Po", "Út", "St", "Čt", "Pá", "So"],
    fullDayNames: ["Neděle", "Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota"],
    shortMonthNames: ["Led", "Úno", "Bře", "Dub", "Kvě", "Čer", "čvc", "Srp", "Zář", "Říj", "Lis", "Pro"],
    fullMonthNames: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
    titleContentDate: "Nastavit datum",
    titleContentTime: "Nastavit čas",
    titleContentDateTime: "Nastavit datum a čas",
    setButtonContent: "Nastavit",
    clearButtonContent: "Resetovat"
  });
})(jQuery);
/*

	language: German
	file: DateTimePicker-i18n-de
	author: Lu, Feng (https://github.com/solala888)

*/


(function ($) {
  $.DateTimePicker.i18n["de"] = $.extend($.DateTimePicker.i18n["de"], {
    language: "de",
    dateTimeFormat: "dd-MMM-yyyy HH:mm:ss",
    dateFormat: "dd-MMM-yyyy",
    timeFormat: "HH:mm:ss",
    shortDayNames: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
    fullDayNames: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
    shortMonthNames: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
    fullMonthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
    titleContentDate: "Datum auswählen",
    titleContentTime: "Zeit auswählen",
    titleContentDateTime: "Datum & Zeit auswählen",
    setButtonContent: "Auswählen",
    clearButtonContent: "Zurücksetzen",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy + " " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/*

	language: English
	file: DateTimePicker-i18n-en

*/


(function ($) {
  $.DateTimePicker.i18n["en"] = $.extend($.DateTimePicker.i18n["en"], {
    language: "en",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
    fullDayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
    shortMonthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    fullMonthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    titleContentDate: "Set Date",
    titleContentTime: "Set Time",
    titleContentDateTime: "Set Date & Time",
    setButtonContent: "Set",
    clearButtonContent: "Clear"
  });
})(jQuery);
/*

	language: Spanish
	file: DateTimePicker-i18n-es
	author: kristophone(https://github.com/kristophone)

*/


(function ($) {
  $.DateTimePicker.i18n["es"] = $.extend($.DateTimePicker.i18n["es"], {
    language: "es",
    dateTimeFormat: "dd-MMM-yyyy HH:mm:ss",
    dateFormat: "dd-MMM-yyyy",
    timeFormat: "HH:mm:ss",
    shortDayNames: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
    fullDayNames: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
    shortMonthNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
    fullMonthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
    titleContentDate: "Ingresar fecha",
    titleContentTime: "Ingresar hora",
    titleContentDateTime: "Ingresar fecha y hora",
    setButtonContent: "Guardar",
    clearButtonContent: "Cancelar",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy + " " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/*

	language: French
	file: DateTimePicker-i18n-fr
	author: LivioGama(https://github.com/LivioGama)

*/


(function ($) {
  $.DateTimePicker.i18n["fr"] = $.extend($.DateTimePicker.i18n["fr"], {
    language: "fr",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
    fullDayNames: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
    shortMonthNames: ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"],
    fullMonthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
    titleContentDate: "Choisir une date",
    titleContentTime: "Choisir un horaire",
    titleContentDateTime: "Choisir une date et un horaire",
    setButtonContent: "Choisir",
    clearButtonContent: "Effacer",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + " " + oDate.dd + " " + oDate.month + " " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + " " + oDate.dd + " " + oDate.month + " " + oDate.yyyy + ", " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/*

	language: Italiano
	file: DateTimePicker-i18n-it
	author: Cristian Segattini

*/


(function ($) {
  $.DateTimePicker.i18n["it"] = $.extend($.DateTimePicker.i18n["it"], {
    language: "it",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Dom", "Lun", "Mar", "Mer", "Gio", "Ven", "Sab"],
    fullDayNames: ["Domenica", "Lunedì", "Martedì", "Mercoledì", "Giovedì", "Venerdì", "Sabato"],
    shortMonthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
    fullMonthNames: ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"],
    titleContentDate: "Imposta Data",
    titleContentTime: "Imposta Ora",
    titleContentDateTime: "Imposta Data & Ora",
    setButtonContent: "Imposta",
    clearButtonContent: "Pulisci"
  });
})(jQuery);
/*

  language: Japanese
  file: DateTimePicker-i18n-ja
  author: JasonYCHuang (https://github.com/JasonYCHuang)

*/


(function ($) {
  $.DateTimePicker.i18n["ja"] = $.extend($.DateTimePicker.i18n["ja"], {
    language: "ja",
    labels: {
      'year': '年',
      'month': '月',
      'day': '日',
      'hour': '時',
      'minutes': '分',
      'seconds': '秒',
      'meridiem': '昼'
    },
    dateTimeFormat: "yyyy-MM-dd HH:mm",
    dateFormat: "yyyy-MM-dd",
    timeFormat: "HH:mm",
    shortDayNames: ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
    fullDayNames: ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'],
    shortMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    fullMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    titleContentDate: "日付の設定",
    titleContentTime: "時刻の設定",
    titleContentDateTime: "日付と時間の設定",
    setButtonContent: "設定",
    clearButtonContent: "取消",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日";else if (sMode === "time") return oDate.HH + "時" + oDate.mm + "分" + oDate.ss + "秒";else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日 " + oDate.HH + "時" + oDate.mm + "分";
    }
  });
})(jQuery);
/*

  language: Norsk Bokmål
  file: DateTimePicker-i18n-nb
  author: Tommy Eliassen (https://github.com/pusle)

 */


(function ($) {
  $.DateTimePicker.i18n["nb"] = $.extend($.DateTimePicker.i18n["nb"], {
    language: "nb",
    dateTimeFormat: "dd.MM.yyyy HH:mm",
    dateFormat: "dd.MM.yyyy",
    timeFormat: "HH:mm",
    dateSeparator: ".",
    shortDayNames: ["Søn", "Man", "Tir", "Ons", "Tor", "Fre", "Lør"],
    fullDayNames: ["Søndag", "Mandag", "Tirsdag", "Onsdag", "Torsdag", "Fredag", "Lørdag"],
    shortMonthNames: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Des"],
    fullMonthNames: ["Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Desember"],
    titleContentDate: "Sett Dato",
    titleContentTime: "Sett Klokkeslett",
    titleContentDateTime: "Sett Dato & Klokkeslett",
    setButtonContent: "Bruk",
    clearButtonContent: "Nullstill"
  });
})(jQuery);
/*

	language: Dutch
	file: DateTimePicker-i18n-nl
	author: Bernardo(https://github.com/bhulsman)

*/


(function ($) {
  $.DateTimePicker.i18n["nl"] = $.extend($.DateTimePicker.i18n["nl"], {
    language: "nl",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["zo", "ma", "di", "wo", "do", "vr", "za"],
    fullDayNames: ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"],
    shortMonthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"],
    fullMonthNames: ["januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december"],
    titleContentDate: "Kies datum",
    titleContentTime: "Kies tijd",
    titleContentDateTime: "Kies datum & tijd",
    setButtonContent: "Kiezen",
    clearButtonContent: "Leegmaken"
  });
})(jQuery);
/*

	language: Romanian
	file: DateTimePicker-i18n-nl
	author: Radu Mogoș(https://github.com/pixelplant)

 */


(function ($) {
  $.DateTimePicker.i18n["ro"] = $.extend($.DateTimePicker.i18n["ro"], {
    language: "ro",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Dum", "Lun", "Mar", "Mie", "Joi", "Vim", "Sâm"],
    fullDayNames: ["Duminică", "Luni", "Marți", "Miercuri", "Joi", "Vineri", "Sâmbătă"],
    shortMonthNames: ["Ian", "Feb", "Mar", "Apr", "Mai", "Iun", "Iul", "Aug", "Sep", "Oct", "Noi", "Dec"],
    fullMonthNames: ["Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"],
    titleContentDate: "Setare Dată",
    titleContentTime: "Setare Oră",
    titleContentDateTime: "Setare Dată și Oră",
    setButtonContent: "Setează",
    clearButtonContent: "Șterge"
  });
})(jQuery);
/*

  language: Russian
  file: DateTimePicker-i18n-ru
  author: Valery Bogdanov (https://github.com/radkill)

*/


(function ($) {
  $.DateTimePicker.i18n["ru"] = $.extend($.DateTimePicker.i18n["ru"], {
    language: "ru",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
    fullDayNames: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"],
    shortMonthNames: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
    fullMonthNames: ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"],
    titleContentDate: "Выберите дату",
    titleContentTime: "Выберите время",
    titleContentDateTime: "Выберите дату и время",
    setButtonContent: "Выбрать",
    clearButtonContent: "Очистить",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + " " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + " " + oDate.yyyy + ", " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/*

  language: Ukrainian
  file: DateTimePicker-i18n-uk
  author: Valery Bogdanov (https://github.com/radkill)

*/


(function ($) {
  var _$$extend;

  $.DateTimePicker.i18n["uk"] = $.extend($.DateTimePicker.i18n["uk"], (_$$extend = {
    language: "uk",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
    fullDayNames: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"],
    shortMonthNames: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"],
    fullMonthNames: ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"]
  }, _defineProperty(_$$extend, "fullDayNames", ["неділя", "понеділок", "вівторок", "середа", "четвер", "п'ятниця", "субота"]), _defineProperty(_$$extend, "shortMonthNames", ["Січ", "Лют", "Бер", "Кві", "Тра", "Чер", "Лип", "Сер", "Вер", "Жов", "Лис", "Гру"]), _defineProperty(_$$extend, "fullMonthNames", ["січня", "лютого", "березня", "квітня", "травня", "червня", "липня", "серпня", "вересня", "жовтня", "листопада", "грудня"]), _defineProperty(_$$extend, "titleContentDate", "Виберіть дату"), _defineProperty(_$$extend, "titleContentTime", "Виберіть час"), _defineProperty(_$$extend, "titleContentDateTime", "Виберіть дату і час"), _defineProperty(_$$extend, "setButtonContent", "Вибрати"), _defineProperty(_$$extend, "clearButtonContent", "Очистити"), _defineProperty(_$$extend, "formatHumanDate", function formatHumanDate(oDate, sMode, sFormat) {
    if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + " " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + " " + oDate.yyyy + ", " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
  }), _$$extend));
})(jQuery);
/*

  language: Traditional Chinese
  file: DateTimePicker-i18n-zh-TW
  author: JasonYCHuang (https://github.com/JasonYCHuang)

*/


(function ($) {
  $.DateTimePicker.i18n["zh-TW"] = $.extend($.DateTimePicker.i18n["zh-TW"], {
    language: "zh-TW",
    labels: {
      'year': '年',
      'month': '月',
      'day': '日',
      'hour': '時',
      'minutes': '分',
      'seconds': '秒',
      'meridiem': '午'
    },
    dateTimeFormat: "yyyy-MM-dd HH:mm",
    dateFormat: "yyyy-MM-dd",
    timeFormat: "HH:mm",
    shortDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    fullDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    shortMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    fullMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    titleContentDate: "設置日期",
    titleContentTime: "設置時間",
    titleContentDateTime: "設置日期和時間",
    setButtonContent: "設置",
    clearButtonContent: "清除",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日";else if (sMode === "time") return oDate.HH + "時" + oDate.mm + "分" + oDate.ss + "秒";else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日 " + oDate.HH + "時" + oDate.mm + "分";
    }
  });
})(jQuery);
/*

	language: Simple Chinese
	file: DateTimePicker-i18n-zh-CN
	author: Calvin(https://github.com/Calvin-he)

*/


(function ($) {
  $.DateTimePicker.i18n["zh-CN"] = $.extend($.DateTimePicker.i18n["zh-CN"], {
    language: "zh-CN",
    labels: {
      'year': '年',
      'month': '月',
      'day': '日',
      'hour': '时',
      'minutes': '分',
      'seconds': '秒',
      'meridiem': '午'
    },
    dateTimeFormat: "yyyy-MM-dd HH:mm",
    dateFormat: "yyyy-MM-dd",
    timeFormat: "HH:mm",
    shortDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    fullDayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
    shortMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    fullMonthNames: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
    titleContentDate: "设置日期",
    titleContentTime: "设置时间",
    titleContentDateTime: "设置日期和时间",
    setButtonContent: "设置",
    clearButtonContent: "清除",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日";else if (sMode === "time") return oDate.HH + "时" + oDate.mm + "分" + oDate.ss + "秒";else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.yyyy + "年" + oDate.month + "月" + oDate.dd + "日 " + oDate.HH + "时" + oDate.mm + "分";
    }
  });
})(jQuery);
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: Dutch
	file: DateTimePicker-i18n-nl
	author: Bernardo(https://github.com/bhulsman)

*/


(function ($) {
  $.DateTimePicker.i18n["nl"] = $.extend($.DateTimePicker.i18n["nl"], {
    language: "nl",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["zo", "ma", "di", "wo", "do", "vr", "za"],
    fullDayNames: ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"],
    shortMonthNames: ["jan", "feb", "mrt", "apr", "mei", "jun", "jul", "aug", "sep", "okt", "nov", "dec"],
    fullMonthNames: ["januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december"],
    titleContentDate: "Kies datum",
    titleContentTime: "Kies tijd",
    titleContentDateTime: "Kies datum & tijd",
    setButtonContent: "Kiezen",
    clearButtonContent: "Leegmaken"
  });
})(jQuery);
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: German
	file: DateTimePicker-i18n-de
	author: Lu, Feng (https://github.com/solala888)

*/


(function ($) {
  $.DateTimePicker.i18n["de"] = $.extend($.DateTimePicker.i18n["de"], {
    language: "de",
    dateTimeFormat: "dd-MMM-yyyy HH:mm:ss",
    dateFormat: "dd-MMM-yyyy",
    timeFormat: "HH:mm:ss",
    shortDayNames: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
    fullDayNames: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
    shortMonthNames: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
    fullMonthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
    titleContentDate: "Datum auswählen",
    titleContentTime: "Zeit auswählen",
    titleContentDateTime: "Datum & Zeit auswählen",
    setButtonContent: "Auswählen",
    clearButtonContent: "Zurücksetzen",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy + " " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: Spanish
	file: DateTimePicker-i18n-es
	author: kristophone(https://github.com/kristophone)

*/


(function ($) {
  $.DateTimePicker.i18n["es"] = $.extend($.DateTimePicker.i18n["es"], {
    language: "es",
    dateTimeFormat: "dd-MMM-yyyy HH:mm:ss",
    dateFormat: "dd-MMM-yyyy",
    timeFormat: "HH:mm:ss",
    shortDayNames: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
    fullDayNames: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
    shortMonthNames: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
    fullMonthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
    titleContentDate: "Ingresar fecha",
    titleContentTime: "Ingresar hora",
    titleContentDateTime: "Ingresar fecha y hora",
    setButtonContent: "Guardar",
    clearButtonContent: "Cancelar",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + ", " + oDate.dd + " " + oDate.month + ", " + oDate.yyyy + " " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: French
	file: DateTimePicker-i18n-fr
	author: LivioGama(https://github.com/LivioGama)

*/


(function ($) {
  $.DateTimePicker.i18n["fr"] = $.extend($.DateTimePicker.i18n["fr"], {
    language: "fr",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"],
    fullDayNames: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
    shortMonthNames: ["Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aoû", "Sep", "Oct", "Nov", "Déc"],
    fullMonthNames: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
    titleContentDate: "Choisir une date",
    titleContentTime: "Choisir un horaire",
    titleContentDateTime: "Choisir une date et un horaire",
    setButtonContent: "Choisir",
    clearButtonContent: "Effacer",
    formatHumanDate: function formatHumanDate(oDate, sMode, sFormat) {
      if (sMode === "date") return oDate.dayShort + " " + oDate.dd + " " + oDate.month + " " + oDate.yyyy;else if (sMode === "time") return oDate.HH + ":" + oDate.mm + ":" + oDate.ss;else if (sMode === "datetime") return oDate.dayShort + " " + oDate.dd + " " + oDate.month + " " + oDate.yyyy + ", " + oDate.HH + ":" + oDate.mm + ":" + oDate.ss;
    }
  });
})(jQuery);
/* ----------------------------------------------------------------------------- 

  jQuery DateTimePicker - Responsive flat design jQuery DateTime Picker plugin for Web & Mobile
  Version 0.1.39
  Copyright (c)2014-2019 Lajpat Shah
  Contributors : https://github.com/nehakadam/DateTimePicker/contributors
  Repository : https://github.com/nehakadam/DateTimePicker
  Documentation : https://nehakadam.github.io/DateTimePicker

 ----------------------------------------------------------------------------- */

/*

	language: Romanian
	file: DateTimePicker-i18n-nl
	author: Radu Mogoș(https://github.com/pixelplant)

 */


(function ($) {
  $.DateTimePicker.i18n["ro"] = $.extend($.DateTimePicker.i18n["ro"], {
    language: "ro",
    dateTimeFormat: "dd-MM-yyyy HH:mm",
    dateFormat: "dd-MM-yyyy",
    timeFormat: "HH:mm",
    shortDayNames: ["Dum", "Lun", "Mar", "Mie", "Joi", "Vim", "Sâm"],
    fullDayNames: ["Duminică", "Luni", "Marți", "Miercuri", "Joi", "Vineri", "Sâmbătă"],
    shortMonthNames: ["Ian", "Feb", "Mar", "Apr", "Mai", "Iun", "Iul", "Aug", "Sep", "Oct", "Noi", "Dec"],
    fullMonthNames: ["Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"],
    titleContentDate: "Setare Dată",
    titleContentTime: "Setare Oră",
    titleContentDateTime: "Setare Dată și Oră",
    setButtonContent: "Setează",
    clearButtonContent: "Șterge"
  });
})(jQuery);
