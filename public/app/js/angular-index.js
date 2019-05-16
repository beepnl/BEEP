var _LANG$nl, _LANG$en, _LANG$de;

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
    "en": "English",
    "de": "Deutsch"
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
    templateUrl: '/app/views/forms/login.html'
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
}, _defineProperty(_LANG$nl, "Location", 'Locatie'), _defineProperty(_LANG$nl, "Country", 'Land'), _defineProperty(_LANG$nl, "City", 'Stad'), _defineProperty(_LANG$nl, "Address", 'Adres'), _defineProperty(_LANG$nl, "Lattitude", 'Lengtegraad'), _defineProperty(_LANG$nl, "Longitude", 'Breedtegraad'), _defineProperty(_LANG$nl, "Street", 'Straat'), _defineProperty(_LANG$nl, "Number", 'Nr.'), _defineProperty(_LANG$nl, "Postal_code", 'Postcode'), _defineProperty(_LANG$nl, "Description", 'Beschrijving'), _defineProperty(_LANG$nl, "Hive_settings", 'Bijenkast instellingen'), _defineProperty(_LANG$nl, "Hive_amount", 'Aantal kasten op deze locatie'), _defineProperty(_LANG$nl, "Hive_prefix", 'Kastnaam voorvoegsel (vòòr nummer)'), _defineProperty(_LANG$nl, "Hive_number_offset", 'Startnummer kasten'), _defineProperty(_LANG$nl, "Hive_type", 'Kasttype'), _defineProperty(_LANG$nl, "Hive_layers", 'Kamers per kast'), _defineProperty(_LANG$nl, "Hive_frames", 'Ramen per kamer'), _defineProperty(_LANG$nl, "Hive_color", 'Kastkleur'), _defineProperty(_LANG$nl, "Queen", 'Moer'), _defineProperty(_LANG$nl, "queen", 'moer'), _defineProperty(_LANG$nl, "settings_title", 'Instellingen overzicht'), _defineProperty(_LANG$nl, "settings_description", 'Overzicht van de account instellingen'), _defineProperty(_LANG$nl, "settings", 'Instellingen'), _defineProperty(_LANG$nl, "sensors_title", 'Sensorinstellingen'), _defineProperty(_LANG$nl, "sensors_description", 'Sensoren status en registratie'), _defineProperty(_LANG$nl, "sensors", 'Sensoren'), _defineProperty(_LANG$nl, "sensor", 'Sensor'), _defineProperty(_LANG$nl, "Select", 'Selecteer'), _defineProperty(_LANG$nl, "Not_selected", 'Niet geselecteerd'), _defineProperty(_LANG$nl, "Poor", 'Slecht'), _defineProperty(_LANG$nl, "Fair", 'Matig'), _defineProperty(_LANG$nl, "Average", 'Gemiddeld'), _defineProperty(_LANG$nl, "Good", 'Goed'), _defineProperty(_LANG$nl, "Excellent", 'Zeer goed'), _defineProperty(_LANG$nl, "Low", 'Laag'), _defineProperty(_LANG$nl, "Medium", 'Gemiddeld'), _defineProperty(_LANG$nl, "High", 'Hoog'), _defineProperty(_LANG$nl, "Extreme", 'Extreem'), _defineProperty(_LANG$nl, "select_color", 'Selecteer een kleur'), _defineProperty(_LANG$nl, "advanced", 'Geavanceerd'), _defineProperty(_LANG$nl, "Select_sensor", 'Selecteer een sensor'), _defineProperty(_LANG$nl, "t", 'Temperatuur'), _defineProperty(_LANG$nl, "temperature", 'Temperatuur'), _defineProperty(_LANG$nl, "l", 'Zonlicht'), _defineProperty(_LANG$nl, "light", 'Zonlicht'), _defineProperty(_LANG$nl, "water", 'Water'), _defineProperty(_LANG$nl, "w", 'Water'), _defineProperty(_LANG$nl, "humidity", 'Luchtvochtigheid'), _defineProperty(_LANG$nl, "h", 'Luchtvochtigheid'), _defineProperty(_LANG$nl, "air_pressure", 'Luchtdruk'), _defineProperty(_LANG$nl, "p", 'Luchtdruk'), _defineProperty(_LANG$nl, "weight", 'Gewicht'), _defineProperty(_LANG$nl, "w_v", 'Gewicht sensorwaarde gecombineerd'), _defineProperty(_LANG$nl, "w_fl", 'Gewicht sensorwaarde links voor'), _defineProperty(_LANG$nl, "w_fr", 'Gewicht sensorwaarde rechts voor'), _defineProperty(_LANG$nl, "w_bl", 'Gewicht sensorwaarde links achter'), _defineProperty(_LANG$nl, "w_br", 'Gewicht sensorwaarde rechts achter'), _defineProperty(_LANG$nl, "weight_kg", 'Gewicht'), _defineProperty(_LANG$nl, "weight_kg_corrected", 'Gewicht (corr)'), _defineProperty(_LANG$nl, "weight_combined_kg", 'Gewicht combi'), _defineProperty(_LANG$nl, "bat_volt", 'Batterij'), _defineProperty(_LANG$nl, "bv", 'Batterij'), _defineProperty(_LANG$nl, "sound_fanning_4days", 'Vent 4d bijen'), _defineProperty(_LANG$nl, "s_fan_4", 'Vent 4d bijen'), _defineProperty(_LANG$nl, "sound_fanning_6days", 'Vent 6d bijen'), _defineProperty(_LANG$nl, "s_fan_6", 'Vent 6d bijen'), _defineProperty(_LANG$nl, "sound_fanning_9days", 'Vent 9d bijen'), _defineProperty(_LANG$nl, "s_fan_9", 'Vent 9d bijen'), _defineProperty(_LANG$nl, "sound_flying_adult", 'Vlieggeluid'), _defineProperty(_LANG$nl, "s_fly_a", 'Vlieggeluid'), _defineProperty(_LANG$nl, "sound_total", 'Totaal geluid'), _defineProperty(_LANG$nl, "s_tot", 'Totaal geluid'), _defineProperty(_LANG$nl, "bee_count_in", 'Bijen naar binnen'), _defineProperty(_LANG$nl, "bc_i", 'Bijen naar binnen'), _defineProperty(_LANG$nl, "bee_count_out", 'Bijen naar buiten'), _defineProperty(_LANG$nl, "bc_o", 'Bijen naar buiten'), _defineProperty(_LANG$nl, "t_i", 'Temp. in kast'), _defineProperty(_LANG$nl, "rssi", 'Zendsterkte'), _defineProperty(_LANG$nl, "snr", 'Zendruis'), _defineProperty(_LANG$nl, "lat", 'Noorderbreedte'), _defineProperty(_LANG$nl, "lon", 'Oosterlengte'), _defineProperty(_LANG$nl, "Sound_measurements", 'Geluidsmetingen'), _defineProperty(_LANG$nl, "Sensor_info", 'Sensorinformatie'), _defineProperty(_LANG$nl, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$nl, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$nl, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$nl, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$nl, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$nl, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$nl, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$nl, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$nl, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$nl, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$nl, "hour", 'Uur'), _defineProperty(_LANG$nl, "day", 'Dag'), _defineProperty(_LANG$nl, "week", 'Week'), _defineProperty(_LANG$nl, "month", 'Maand'), _defineProperty(_LANG$nl, "year", 'Jaar'), _defineProperty(_LANG$nl, "could_not_load_settings", 'De instellingen konden niet worden geladen'), _defineProperty(_LANG$nl, "offline", 'Geen verbinding'), _defineProperty(_LANG$nl, "remote", 'Op afstand'), _defineProperty(_LANG$nl, "connected", 'Direct'), _defineProperty(_LANG$nl, "yes", 'Ja'), _defineProperty(_LANG$nl, "no", 'Nee'), _defineProperty(_LANG$nl, "footer_text", 'Open source bijenmonitor'), _defineProperty(_LANG$nl, "beep_foundation", 'Stichting BEEP'), _defineProperty(_LANG$nl, "Checklist", 'Kastkaart'), _defineProperty(_LANG$nl, "Checklist_items", 'Kastkaartelementen'), _defineProperty(_LANG$nl, "edit_hive_checklist", 'Vink items in de onderstaande lijst van beschikbare kastkaartitems aan/uit om ze aan je eigen kastkaart toe te voegen/te verwijderen. Voor meer overzicht, kun je de categorieën in- en uitklappen. Ook kun je ze naar boven/beneden slepen om de volgorde van jouw kastkaart te bepalen. Tip: Als je in het zoekveld een term invult, worden alle items die de zoekterm bevatten rood en klappen ze uit.'), _defineProperty(_LANG$nl, "Data_export", 'Data exporteren'), _defineProperty(_LANG$nl, "Export_your_data", 'Exporteer alle data die is opgeslagen in je Beep account en verstuur deze in een e-mail met als bijlage een Excel (.xslx) bestand. Het bestand heeft meerdere tabbladen met daarop je persoonlijke-, bijenstand-, kast- en inspectiegegevens.'), _defineProperty(_LANG$nl, "Terms_of_use", 'Servicevoorwaarden'), _defineProperty(_LANG$nl, "accept_policy", 'Ik accepteer de BEEP servicevoorwaarden, die in lijn zijn met de nieuwe Europese privacywetgeving'), _defineProperty(_LANG$nl, "policy_url", 'https://beep.nl/servicevoorwaarden'), _defineProperty(_LANG$nl, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$nl, "approve_policy", 'Je hebt nog geen akkoord gegeven op de aangepaste gebruikersvoorwaarden.'), _defineProperty(_LANG$nl, "calibrate_weight", 'Gewicht calibreren'), _defineProperty(_LANG$nl, "calibrate_explanation", 'Gewicht van de sensoren bij de volgende meting op 0 zetten door de huidige waarde ervanaf te trekken.'), _defineProperty(_LANG$nl, "set_as_zero_value", 'Stel deze waarde(n) in als 0-waarde(n)'), _defineProperty(_LANG$nl, "set_weight_factor", 'Gewichtsfactor bepalen'), _defineProperty(_LANG$nl, "own_weight_kg", 'Wat is je eigen gewicht in kg?'), _defineProperty(_LANG$nl, "start_calibration", 'Stap nu op de weegschaal en druk de onderstaande knop in zodra je er op staat. Verdeel je gewicht gelijkmatig.'), _defineProperty(_LANG$nl, "currently_there_is", 'Er staat nu'), _defineProperty(_LANG$nl, "nothing", 'niets'), _defineProperty(_LANG$nl, "on_the_scale", 'op de weegschaal'), _defineProperty(_LANG$nl, "calibration_started", 'Calibratie gestart... Wacht op de volgende meting.'), _defineProperty(_LANG$nl, "calibration_ended", 'Calibratie geslaagd!'), _defineProperty(_LANG$nl, "server_down", 'De app is tijdelijk niet beschikbaar door onderhoud, probeer het later opnieuw'), _defineProperty(_LANG$nl, "add_to_calendar", 'Zet in agenda'), _defineProperty(_LANG$nl, "sort_on", 'Sorteer op'), _defineProperty(_LANG$nl, "Whats_new", 'Nieuw in v2!'), _defineProperty(_LANG$nl, "Manual", 'Handleiding'), _defineProperty(_LANG$nl, "Site_title", 'BEEP | Bijenmonitor'), _defineProperty(_LANG$nl, "could_not_create_user", 'Gebruiker kan op dit moment niet aangemaakt worden, probeer het a.u.b. later opnieuw.'), _defineProperty(_LANG$nl, "email_verified", 'Je e-mail adres is gevalideerd.'), _defineProperty(_LANG$nl, "email_not_verified", 'Je e-mail adres is nog niet gevalideerd.'), _defineProperty(_LANG$nl, "email_new_verification", 'Klik op deze link om een nieuwe validatie e-mail te versturen.'), _defineProperty(_LANG$nl, "email_verification_sent", 'Er is een bericht met een validatie-link naar je e-mail adres gestuurd. Klik op de link in de e-mail om je account te activeren en in te loggen.'), _defineProperty(_LANG$nl, "not_filled", 'is verplicht, maar niet ingevuld'), _defineProperty(_LANG$nl, "cannot_deselect", 'Dit item kan niet worden verwijderd, omdat het een verplicht item bevat'), _defineProperty(_LANG$nl, "sensor_key", 'Sensor code'), _defineProperty(_LANG$nl, "Undelete", 'Niet verwijderen'), _defineProperty(_LANG$nl, "the_field", 'Vul een'), _defineProperty(_LANG$nl, "is_required", 'in'), _defineProperty(_LANG$nl, "No_groups", 'Geen groepen beschikbaar'), _defineProperty(_LANG$nl, "not_available_yet", 'nog niet beschikbaar. Maak de eerste aan.'), _defineProperty(_LANG$nl, "Users", 'Gebruikers'), _defineProperty(_LANG$nl, "Member", 'Groepslid'), _defineProperty(_LANG$nl, "Members", 'Groepsleden'), _defineProperty(_LANG$nl, "Invite", 'Uitnodigen'), _defineProperty(_LANG$nl, "Invited", 'Uitgenodigd'), _defineProperty(_LANG$nl, "invitations", 'uitnodigingen'), _defineProperty(_LANG$nl, "Admin", 'Beheerder'), _defineProperty(_LANG$nl, "Creator", 'Groep eigenaar'), _defineProperty(_LANG$nl, "Groups", 'Samenwerken'), _defineProperty(_LANG$nl, "Group", 'Samenwerkingsgroep'), _defineProperty(_LANG$nl, "group", 'samenwerkingsgroep'), _defineProperty(_LANG$nl, "to_share", 'om te delen met de groep. 1x klikken = delen om te bekijken, 2x klikken is delen met aanpassingsmogelijkheid'), _defineProperty(_LANG$nl, "Invitation_accepted", 'Uitnodiging geaccepteerd'), _defineProperty(_LANG$nl, "Accept", 'Accepteer'), _defineProperty(_LANG$nl, "My_shared", 'Mijn gedeelde'), _defineProperty(_LANG$nl, "invitee_name", 'Naam genodigde'), _defineProperty(_LANG$nl, "Remove_group", 'Weet u zeker dat u deze gedeelde groep voor alle leden wilt verwijderen'), _defineProperty(_LANG$nl, "Detach_from_group", 'Verwijder mij en mijn kasten uit deze groep'), _defineProperty(_LANG$nl, "my_hive", 'Mijn kast'), _defineProperty(_LANG$nl, "created", 'aangemakt'), _LANG$nl);
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
}, _defineProperty(_LANG$en, "Location", 'Location'), _defineProperty(_LANG$en, "Country", 'Country'), _defineProperty(_LANG$en, "City", 'City'), _defineProperty(_LANG$en, "Address", 'Address'), _defineProperty(_LANG$en, "Lattitude", 'Lattitude'), _defineProperty(_LANG$en, "Longitude", 'Longitude'), _defineProperty(_LANG$en, "Street", 'Street'), _defineProperty(_LANG$en, "Number", 'No.'), _defineProperty(_LANG$en, "Postal_code", 'Postal code'), _defineProperty(_LANG$en, "Description", 'Description'), _defineProperty(_LANG$en, "Hive_settings", 'Hive settings'), _defineProperty(_LANG$en, "Hive_amount", 'Hive amount at this location'), _defineProperty(_LANG$en, "Hive_prefix", 'Hive name prefix (before numer)'), _defineProperty(_LANG$en, "Hive_number_offset", 'Start number hives'), _defineProperty(_LANG$en, "Hive_type", 'Hive type'), _defineProperty(_LANG$en, "Hive_layers", 'Hive layers'), _defineProperty(_LANG$en, "Hive_frames", 'Frames per layer'), _defineProperty(_LANG$en, "Hive_color", 'Hive color'), _defineProperty(_LANG$en, "Queen", 'Queen'), _defineProperty(_LANG$en, "queen", 'queen'), _defineProperty(_LANG$en, "settings_title", 'Settings'), _defineProperty(_LANG$en, "settings_description", 'Settings of the sensors'), _defineProperty(_LANG$en, "settings", 'Settings'), _defineProperty(_LANG$en, "sensors_title", 'Sensor settings'), _defineProperty(_LANG$en, "sensors_description", 'Sensors status and registration'), _defineProperty(_LANG$en, "sensors", 'Sensors'), _defineProperty(_LANG$en, "sensor", 'Sensor'), _defineProperty(_LANG$en, "Select", 'Select'), _defineProperty(_LANG$en, "Not_selected", 'Not selected'), _defineProperty(_LANG$en, "Poor", 'Poor'), _defineProperty(_LANG$en, "Fair", 'Fair'), _defineProperty(_LANG$en, "Average", 'Average'), _defineProperty(_LANG$en, "Good", 'Good'), _defineProperty(_LANG$en, "Excellent", 'Excellent'), _defineProperty(_LANG$en, "Low", 'Low'), _defineProperty(_LANG$en, "Medium", 'Medium'), _defineProperty(_LANG$en, "High", 'High'), _defineProperty(_LANG$en, "Extreme", 'Extreme'), _defineProperty(_LANG$en, "select_color", 'Select a color'), _defineProperty(_LANG$en, "advanced", 'Advanced'), _defineProperty(_LANG$en, "Select_sensor", 'Select a sensor'), _defineProperty(_LANG$en, "temperature", 'Temperature'), _defineProperty(_LANG$en, "t", 'Temperature'), _defineProperty(_LANG$en, "light", 'Sunlight'), _defineProperty(_LANG$en, "l", 'Sunlight'), _defineProperty(_LANG$en, "water", 'Water'), _defineProperty(_LANG$en, "w", 'Water'), _defineProperty(_LANG$en, "humidity", 'Humidity'), _defineProperty(_LANG$en, "h", 'Humidity'), _defineProperty(_LANG$en, "air_pressure", 'Air pressure'), _defineProperty(_LANG$en, "p", 'Air pressure'), _defineProperty(_LANG$en, "weight", 'Weight'), _defineProperty(_LANG$en, "w_v", 'Weight sensor value all sensors'), _defineProperty(_LANG$en, "w_fl", 'Weight sensor value front left'), _defineProperty(_LANG$en, "w_fr", 'Weight sensor value front right'), _defineProperty(_LANG$en, "w_bl", 'Weight sensor value back left'), _defineProperty(_LANG$en, "w_br", 'Weight sensor value back right'), _defineProperty(_LANG$en, "weight_kg", 'Weight'), _defineProperty(_LANG$en, "weight_kg_corrected", 'Weight (corr)'), _defineProperty(_LANG$en, "weight_combined_kg", 'Weight combi'), _defineProperty(_LANG$en, "bat_volt", 'Battery'), _defineProperty(_LANG$en, "bv", 'Battery'), _defineProperty(_LANG$en, "sound_fanning_4days", 'Fan 4d bees'), _defineProperty(_LANG$en, "s_fan_4", 'Fan 4d bees'), _defineProperty(_LANG$en, "sound_fanning_6days", 'Fan 6d bees'), _defineProperty(_LANG$en, "s_fan_6", 'Fan 6d bees'), _defineProperty(_LANG$en, "sound_fanning_9days", 'Fan 9d bees'), _defineProperty(_LANG$en, "s_fan_9", 'Fan 9d bees'), _defineProperty(_LANG$en, "sound_flying_adult", 'Flying bees'), _defineProperty(_LANG$en, "s_fly_a", 'Flying bees'), _defineProperty(_LANG$en, "sound_total", 'Total sound'), _defineProperty(_LANG$en, "s_tot", 'Total sound'), _defineProperty(_LANG$en, "bee_count_in", 'Bee count in'), _defineProperty(_LANG$en, "bc_i", 'Bee count in'), _defineProperty(_LANG$en, "bee_count_out", 'Bee count out'), _defineProperty(_LANG$en, "bc_o", 'Bee count out'), _defineProperty(_LANG$en, "t_i", 'Temp. inside'), _defineProperty(_LANG$en, "rssi", 'Signal strength'), _defineProperty(_LANG$en, "snr", 'Signal noise'), _defineProperty(_LANG$en, "lat", 'Lattitude'), _defineProperty(_LANG$en, "lon", 'Longitude'), _defineProperty(_LANG$en, "Sound_measurements", 'Sound measurements'), _defineProperty(_LANG$en, "Sensor_info", 'Sensor info'), _defineProperty(_LANG$en, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$en, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$en, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$en, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$en, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$en, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$en, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$en, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$en, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$en, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$en, "hour", 'Hour'), _defineProperty(_LANG$en, "day", 'Day'), _defineProperty(_LANG$en, "week", 'Week'), _defineProperty(_LANG$en, "month", 'Month'), _defineProperty(_LANG$en, "year", 'Year'), _defineProperty(_LANG$en, "could_not_load_settings", 'Settings could not be loaded'), _defineProperty(_LANG$en, "offline", 'No connection'), _defineProperty(_LANG$en, "remote", 'Remote'), _defineProperty(_LANG$en, "connected", 'Direct'), _defineProperty(_LANG$en, "yes", 'Yes'), _defineProperty(_LANG$en, "no", 'No'), _defineProperty(_LANG$en, "footer_text", 'Open source beekeeping'), _defineProperty(_LANG$en, "beep_foundation", 'the BEEP foundation'), _defineProperty(_LANG$en, "Checklist", 'Checklist'), _defineProperty(_LANG$en, "Checklist_items", 'Checklist items'), _defineProperty(_LANG$en, "edit_hive_checklist", 'Check/unckeck the boxes in the list below to add/remove items from your hive checklist. You can also unfold/fold and drag/drop the items to re-order them to your own style. Tip: if you enter a term in the search field, all items containing that term will fold out and color red.'), _defineProperty(_LANG$en, "Data_export", 'Data export'), _defineProperty(_LANG$en, "Export_your_data", 'Export all data that is in your Beep account and send an e-mail cointaining the data as an Excel file. The Excel file has different tabs containing your personal, hive, location, and inspection data.'), _defineProperty(_LANG$en, "Terms_of_use", 'Terms of service'), _defineProperty(_LANG$en, "accept_policy", 'I accept the BEEP terms of service, that are compatible with the new European privacy law'), _defineProperty(_LANG$en, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$en, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$en, "approve_policy", 'You did not yet comply with the latest terms of service.'), _defineProperty(_LANG$en, "calibrate_weight", 'Calibrate weight'), _defineProperty(_LANG$en, "calibrate_explanation", 'Set the weight of the sensors to 0 by subtracting the current measurement value.'), _defineProperty(_LANG$en, "set_as_zero_value", 'Set these values as 0 values'), _defineProperty(_LANG$en, "set_weight_factor", 'Define weight factor'), _defineProperty(_LANG$en, "own_weight_kg", 'What is your own weight in kg?'), _defineProperty(_LANG$en, "start_calibration", 'Now step on the scale, and press the button below to define the weight factor. Distribute your weight equally.'), _defineProperty(_LANG$en, "currently_there_is", 'There is a weight of'), _defineProperty(_LANG$en, "nothing", 'nothing'), _defineProperty(_LANG$en, "on_the_scale", 'on the scale'), _defineProperty(_LANG$en, "calibration_started", 'Calibration started... Wait for the next measurement to take effect.'), _defineProperty(_LANG$en, "calibration_ended", 'Calibration succeeded!'), _defineProperty(_LANG$en, "server_down", 'The app is unavailable due to maintenance work, please try again later'), _defineProperty(_LANG$en, "add_to_calendar", 'Add to calendar'), _defineProperty(_LANG$en, "sort_on", 'Sort on'), _defineProperty(_LANG$en, "Whats_new", 'New in v2!'), _defineProperty(_LANG$en, "Manual", 'Manual'), _defineProperty(_LANG$en, "Site_title", 'BEEP | Bee monitor'), _defineProperty(_LANG$en, "could_not_create_user", 'User cannot be created at this moment. Sorry for the inconvenience, please try again later.'), _defineProperty(_LANG$en, "email_verified", 'Your e-mail address has been verified.'), _defineProperty(_LANG$en, "email_not_verified", 'Your e-mail address has not yet been verified.'), _defineProperty(_LANG$en, "email_new_verification", 'Click on this link to send a new verification e-mail.'), _defineProperty(_LANG$en, "email_verification_sent", 'A message with a verification link has been sent to your e-mail address. Click the link in the e-mail to activate your account and log in.'), _defineProperty(_LANG$en, "not_filled", 'is required, but not filled out'), _defineProperty(_LANG$en, "cannot_deselect", 'Unable to remove this item, because it contains a required item'), _defineProperty(_LANG$en, "sensor_key", 'Sensor key'), _defineProperty(_LANG$en, "Undelete", 'Do not delete'), _defineProperty(_LANG$en, "the_field", 'The'), _defineProperty(_LANG$en, "is_required", 'is required'), _defineProperty(_LANG$en, "No_groups", 'No groups available'), _defineProperty(_LANG$en, "not_available_yet", 'not yet available. Please create the first one here.'), _defineProperty(_LANG$en, "Users", 'Users'), _defineProperty(_LANG$en, "Member", 'Group member'), _defineProperty(_LANG$en, "Members", 'Group members'), _defineProperty(_LANG$en, "Invite", 'Invite'), _defineProperty(_LANG$en, "Invited", 'Invited'), _defineProperty(_LANG$en, "invitations", 'invitations'), _defineProperty(_LANG$en, "Admin", 'Administrator'), _defineProperty(_LANG$en, "Creator", 'Group owner'), _defineProperty(_LANG$en, "Groups", 'Collaborate'), _defineProperty(_LANG$en, "Group", 'Collaboration group'), _defineProperty(_LANG$en, "group", 'collaboration group'), _defineProperty(_LANG$en, "to_share", 'to share with this group. 1 click = group members can view only, 2 clicks = group members can edit'), _defineProperty(_LANG$en, "Invitation_accepted", 'Invitation accepted'), _defineProperty(_LANG$en, "Accept", 'Accept'), _defineProperty(_LANG$en, "My_shared", 'My shared'), _defineProperty(_LANG$en, "invitee_name", 'Invitee name'), _defineProperty(_LANG$en, "Remove_group", 'Are you sure you want to competely remove this shared group for all it\'s members'), _defineProperty(_LANG$en, "Detach_from_group", 'Remove me and my hives from this group'), _defineProperty(_LANG$en, "my_hive", 'My hive'), _defineProperty(_LANG$en, "created", 'created'), _LANG$en);
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
}, _defineProperty(_LANG$de, "Location", 'Standort'), _defineProperty(_LANG$de, "Country", 'Land'), _defineProperty(_LANG$de, "City", 'Stadt'), _defineProperty(_LANG$de, "Address", 'Addresse'), _defineProperty(_LANG$de, "Lattitude", 'Lattitude'), _defineProperty(_LANG$de, "Longitude", 'Longitude'), _defineProperty(_LANG$de, "Street", 'Straße'), _defineProperty(_LANG$de, "Number", 'Hausnummer.'), _defineProperty(_LANG$de, "Postal_code", 'Postleitzahl'), _defineProperty(_LANG$de, "Description", 'Beschreibung'), _defineProperty(_LANG$de, "Hive_settings", 'Beute Einstellung'), _defineProperty(_LANG$de, "Hive_amount", 'Anzahl der Beuten an diesem Ort'), _defineProperty(_LANG$de, "Hive_prefix", 'Beutenprefix (vor der Zahl)'), _defineProperty(_LANG$de, "Hive_number_offset", 'Startnummer Beute'), _defineProperty(_LANG$de, "Hive_type", 'Beutentyp'), _defineProperty(_LANG$de, "Hive_layers", 'Zargen'), _defineProperty(_LANG$de, "Hive_frames", 'Rähmchen per Zarge'), _defineProperty(_LANG$de, "Hive_color", 'Beutenfarbe'), _defineProperty(_LANG$de, "Queen", 'Königin'), _defineProperty(_LANG$de, "queen", 'Königin'), _defineProperty(_LANG$de, "settings_title", 'Einstellungen'), _defineProperty(_LANG$de, "settings_description", 'Einstellungen der Sensoren'), _defineProperty(_LANG$de, "settings", 'Einstellungen'), _defineProperty(_LANG$de, "sensors_title", 'Sensoreinstellungen'), _defineProperty(_LANG$de, "sensors_description", 'Sensor Status und Registrierung'), _defineProperty(_LANG$de, "sensors", 'Sensoren'), _defineProperty(_LANG$de, "sensor", 'Sensor'), _defineProperty(_LANG$de, "Select", 'Wähle'), _defineProperty(_LANG$de, "Not_selected", 'Nicht gewählt'), _defineProperty(_LANG$de, "Poor", 'Arm'), _defineProperty(_LANG$de, "Fair", 'Fair'), _defineProperty(_LANG$de, "Average", 'Durchschnitt'), _defineProperty(_LANG$de, "Good", 'Gut'), _defineProperty(_LANG$de, "Excellent", 'Excellent'), _defineProperty(_LANG$de, "Low", 'Tief'), _defineProperty(_LANG$de, "Medium", 'Mitte'), _defineProperty(_LANG$de, "High", 'Hoch'), _defineProperty(_LANG$de, "Extreme", 'Extrem'), _defineProperty(_LANG$de, "select_color", 'Wähle eine Farbe'), _defineProperty(_LANG$de, "advanced", 'Erweitert'), _defineProperty(_LANG$de, "Select_sensor", 'Wähle einen Sensor'), _defineProperty(_LANG$de, "temperature", 'Temperatur'), _defineProperty(_LANG$de, "t", 'Temperatur'), _defineProperty(_LANG$de, "light", 'Sonnenlicht'), _defineProperty(_LANG$de, "l", 'Sonnenlicht'), _defineProperty(_LANG$de, "water", 'Wasser'), _defineProperty(_LANG$de, "w", 'Wasser'), _defineProperty(_LANG$de, "humidity", 'Feuchtigkeit'), _defineProperty(_LANG$de, "h", 'Feuchtigkeit'), _defineProperty(_LANG$de, "air_pressure", 'Luftdruck'), _defineProperty(_LANG$de, "p", 'Luftdruck'), _defineProperty(_LANG$de, "weight", 'Gewicht'), _defineProperty(_LANG$de, "w_v", 'Gewichtssensor Wert für alle'), _defineProperty(_LANG$de, "w_fl", 'Gewichtssensor Wert vorne links'), _defineProperty(_LANG$de, "w_fr", 'Gewichtssensor Wert vorne rechts'), _defineProperty(_LANG$de, "w_bl", 'Gewichtssensor Wert hinten links'), _defineProperty(_LANG$de, "w_br", 'Gewichtssensor Wert hinten rechts'), _defineProperty(_LANG$de, "weight_kg", 'Gewicht'), _defineProperty(_LANG$de, "weight_kg_corrected", 'Gewicht (korrigiert)'), _defineProperty(_LANG$de, "weight_combined_kg", 'Gewicht kombiniert'), _defineProperty(_LANG$de, "bat_volt", 'Batterie'), _defineProperty(_LANG$de, "bv", 'Batterie'), _defineProperty(_LANG$de, "sound_fanning_4days", 'Fan 4d Bienen'), _defineProperty(_LANG$de, "s_fan_4", 'Fan 4d Bienens'), _defineProperty(_LANG$de, "sound_fanning_6days", 'Fan 6d Bienen'), _defineProperty(_LANG$de, "s_fan_6", 'Fan 6d Bienen'), _defineProperty(_LANG$de, "sound_fanning_9days", 'Fan 9d Bienens'), _defineProperty(_LANG$de, "s_fan_9", 'Fan 9d Bienen'), _defineProperty(_LANG$de, "sound_flying_adult", 'Fliegende Bienen'), _defineProperty(_LANG$de, "s_fly_a", 'Fliegende Bienen'), _defineProperty(_LANG$de, "sound_total", 'Totaler Sound'), _defineProperty(_LANG$de, "s_tot", 'Totaler Sound'), _defineProperty(_LANG$de, "bee_count_in", 'Bienenzähler nach innen'), _defineProperty(_LANG$de, "bc_i", 'Bienenzähler nach innen'), _defineProperty(_LANG$de, "bee_count_out", 'Bienenzähler nach außen'), _defineProperty(_LANG$de, "bc_o", 'Bienenzähler nach außen'), _defineProperty(_LANG$de, "t_i", 'Temp. innen'), _defineProperty(_LANG$de, "rssi", 'Signal Stärke'), _defineProperty(_LANG$de, "snr", 'Signal Krach'), _defineProperty(_LANG$de, "Sound_measurements", 'Soundmessungen'), _defineProperty(_LANG$de, "Sensor_info", 'Sensor info'), _defineProperty(_LANG$de, 's_bin098_146Hz', '098-146Hz'), _defineProperty(_LANG$de, 's_bin146_195Hz', '146-195Hz'), _defineProperty(_LANG$de, 's_bin195_244Hz', '195-244Hz'), _defineProperty(_LANG$de, 's_bin244_293Hz', '244-293Hz'), _defineProperty(_LANG$de, 's_bin293_342Hz', '293-342Hz'), _defineProperty(_LANG$de, 's_bin342_391Hz', '342-391Hz'), _defineProperty(_LANG$de, 's_bin391_439Hz', '391-439Hz'), _defineProperty(_LANG$de, 's_bin439_488Hz', '439-488Hz'), _defineProperty(_LANG$de, 's_bin488_537Hz', '488-537Hz'), _defineProperty(_LANG$de, 's_bin537_586Hz', '537-586Hz'), _defineProperty(_LANG$de, "hour", 'Stunde'), _defineProperty(_LANG$de, "day", 'Tag'), _defineProperty(_LANG$de, "week", 'Woche'), _defineProperty(_LANG$de, "month", 'Monat'), _defineProperty(_LANG$de, "year", 'Jahr'), _defineProperty(_LANG$de, "could_not_load_settings", 'Die Einstellungen konnten nicht geladen werden'), _defineProperty(_LANG$de, "offline", 'Keine Verbindung'), _defineProperty(_LANG$de, "remote", 'Fernbedienung'), _defineProperty(_LANG$de, "connected", 'Direkt'), _defineProperty(_LANG$de, "yes", 'Ja'), _defineProperty(_LANG$de, "no", 'Nein'), _defineProperty(_LANG$de, "footer_text", 'Open source beekeeping'), _defineProperty(_LANG$de, "beep_foundation", 'the BEEP foundation'), _defineProperty(_LANG$de, "Checklist", 'Stockkarte'), _defineProperty(_LANG$de, "Checklist_items", 'Stockkarte Artikel'), _defineProperty(_LANG$de, "edit_hive_checklist", 'Aktivieren / deaktivieren Sie die Kästchen in der Liste, um Artikel aus Ihrer Stockkarte hinzuzufügen / zu entfernen. Sie können die Artikel auch entfalten / falten und ziehen / ablegen, um sie an Ihren eigenen Stil anzupassen. Tipp: Wenn Sie einen Suchbegriff in das Suchfeld eingeben, werden alle Artikel, die diesen Begriff enthalten, ausgeklappt und rot gefärbt.'), _defineProperty(_LANG$de, "Data_export", 'Daten Export'), _defineProperty(_LANG$de, "Export_your_data", 'Exportiere alle Daten aus Deinem Account per Email (Exceldatei).'), _defineProperty(_LANG$de, "Terms_of_use", 'Nutzungsbedingungen (EN)'), _defineProperty(_LANG$de, "accept_policy", 'Ich akzeptiere die BEEP-Nutzungsbedingungen, die mit dem neuen europäischen Datenschutzgesetz vereinbar sind'), _defineProperty(_LANG$de, "policy_url", 'https://beep.nl/terms-of-service'), _defineProperty(_LANG$de, "policy_version", 'beep_terms_2018_05_25_avg_v1'), _defineProperty(_LANG$de, "approve_policy", 'Sie haben die aktuellen Nutzungsbedingungen noch nicht erfüllt.'), _defineProperty(_LANG$de, "calibrate_weight", 'Kalibriere Gewicht'), _defineProperty(_LANG$de, "calibrate_explanation", 'Stellen Sie das Gewicht der Sensoren auf 0 ein, indem Sie den aktuellen Messwert subtrahieren.'), _defineProperty(_LANG$de, "set_as_zero_value", 'Setzen Sie diese Werte als 0 Werte'), _defineProperty(_LANG$de, "set_weight_factor", 'Definiere den Gewichtsfaktor'), _defineProperty(_LANG$de, "own_weight_kg", 'Wie hoch ist Ihr Eigengewicht in kg??'), _defineProperty(_LANG$de, "start_calibration", 'Treten Sie nun auf die Waage und drücken Sie die Taste unten, um den Gewichtsfaktor festzulegen. Verteilen Sie Ihr Gewicht gleichmäßig.'), _defineProperty(_LANG$de, "currently_there_is", 'Da ist ein Gewicht von'), _defineProperty(_LANG$de, "nothing", 'nichts'), _defineProperty(_LANG$de, "on_the_scale", 'auf der Skala'), _defineProperty(_LANG$de, "calibration_started", 'Calibration started... Wait for the next measurement to take effect.'), _defineProperty(_LANG$de, "calibration_ended", 'Calibration succeeded!'), _defineProperty(_LANG$de, "server_down", 'Die App ist aufgrund von Wartungsarbeiten nicht verfügbar. Bitte versuche es später erneut'), _defineProperty(_LANG$de, "add_to_calendar", 'Zum Kalender hinzufügen'), _defineProperty(_LANG$de, "sort_on", 'Sortieren nach'), _defineProperty(_LANG$de, "Whats_new", 'Neu in v2!'), _defineProperty(_LANG$de, "Manual", 'Handbuch (EN)'), _defineProperty(_LANG$de, "Site_title", 'BEEP | Bienenmonitor'), _defineProperty(_LANG$de, "could_not_create_user", 'User cannot be created at this moment. Sorry for the inconvenience, please try again later.'), _defineProperty(_LANG$de, "email_verified", 'Your e-mail address has been verified.'), _defineProperty(_LANG$de, "email_not_verified", 'Your e-mail address has not yet been verified.'), _defineProperty(_LANG$de, "email_new_verification", 'Click on this link to send a new verification e-mail.'), _defineProperty(_LANG$de, "email_verification_sent", 'A message with a verification link has been sent to your e-mail address. Click the link in the e-mail to activate your account and log in.'), _defineProperty(_LANG$de, "not_filled", 'is required, but not filled out'), _defineProperty(_LANG$de, "cannot_deselect", 'Unable to remove this item, because it contains a required item'), _defineProperty(_LANG$de, "sensor_key", 'Sensor key'), _defineProperty(_LANG$de, "Undelete", 'Do not delete'), _defineProperty(_LANG$de, "the_field", 'The'), _defineProperty(_LANG$de, "is_required", 'is required'), _defineProperty(_LANG$de, "No_groups", 'No groups available'), _defineProperty(_LANG$de, "not_available_yet", 'not yet available. Please create the first one here.'), _defineProperty(_LANG$de, "Users", 'Benutzer'), _defineProperty(_LANG$de, "Member", 'Group member'), _defineProperty(_LANG$de, "Members", 'Group members'), _defineProperty(_LANG$de, "Invite", 'Invite'), _defineProperty(_LANG$de, "Invited", 'Invited'), _defineProperty(_LANG$de, "invitations", 'invitations'), _defineProperty(_LANG$de, "Admin", 'Administrator'), _defineProperty(_LANG$de, "Creator", 'Group owner'), _defineProperty(_LANG$de, "Groups", 'Kooperieren'), _defineProperty(_LANG$de, "Group", 'Collaboration group'), _defineProperty(_LANG$de, "group", 'collaboration group'), _defineProperty(_LANG$de, "to_share", 'to share with this group. 1 click = group members can view only, 2 clicks = group members can edit'), _defineProperty(_LANG$de, "Invitation_accepted", 'Invitation accepted'), _defineProperty(_LANG$de, "Accept", 'Accept'), _defineProperty(_LANG$de, "My_shared", 'My shared'), _defineProperty(_LANG$de, "invitee_name", 'Invitee name'), _defineProperty(_LANG$de, "Remove_group", 'Are you sure you want to competely remove this shared group for all it\'s members'), _defineProperty(_LANG$de, "Detach_from_group", 'Remove me and my hives from this group'), _defineProperty(_LANG$de, "my_hive", 'My hive'), _defineProperty(_LANG$de, "created", 'created'), _LANG$de); //! moment.js locale configuration

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
