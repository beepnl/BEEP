/*
 * BEEP - Bee monitoring
 * Author: Pim van Gennip (pim@iconize.nl)
 *
 */

var app = angular.module('app', ['ngRoute', 'angularMoment', 'chart.js', 'ngDialog', 'iconFilters', 'textFilters', 'uiSwitch', 'revolunet.stepper', 'ngMap', 'mp.colorPicker', 'rzModule', 'ngJsTree', 'angular-atc', 'angular-gestures', 'angularjs-gauge']);

app.config(function (hammerDefaultOptsProvider) {

    hammerDefaultOptsProvider.set({
        recognizers: [[Hammer.Press, {time: 250}, Hammer.Release]]
    });
});

/* Run some basic functions */
app.run(function($rootScope, $location, $window, $route, $routeParams, amMoment, ngDialog, settings, api) 
{

    // set fastclick
    // FastClick.attach(document.body,{
    //   excludeNode: '^pac-'
    // }); 
    $rootScope.browser = navigator.userAgent;
    $rootScope.host    = $location.host();
    $rootScope.supportedLocales = {
        "nl":"Nederlands", 
        "de":"Deutsch",
        "en":"English",
        "fr":"Français",
        "pt":"português",
        "ro":"română",
        "es":"Spanish"
    };

    var setLang     = api.getLocalStoreValue('lang');
    var navLang     = navigator.language || navigator.userLanguage; 
    var urlLang     = $routeParams.language;

    if (typeof urlLang != 'undefined' && typeof $rootScope.supportedLocales[urlLang] != 'undefined')
    {
        navLang = urlLang;
    }
    else if (setLang != null)
    {
        navLang = setLang;
    }
    var navLocale = navLang.substr(0,2);
    
    // set the language
    $rootScope.locale = typeof $rootScope.supportedLocales[navLocale] != 'undefined' ? navLocale : 'nl';
    
    // set the chart colors 
    Chart.defaults.global.defaultFontFamily             = "'DinPro', 'MAIN', 'Roboto Condensed', sans-serif";
    Chart.defaults.global.defaultFontSize               = 16;
    Chart.defaults.global.defaultFontStyle              = "normal";
    Chart.defaults.global.defaultFontColor              = "#444444";
    Chart.defaults.global.animation.easing              = "easeInOutCubic";
    Chart.defaults.global.animation.duration            = 500;
    Chart.defaults.global.tooltips.enabled              = true;
    Chart.defaults.global.tooltips.mode                 = "nearest";
    Chart.defaults.global.responsive                    = true;
    Chart.defaults.global.maintainAspectRatio           = false;
    Chart.defaults.global.elements.line.borderCapStyle  = "round";
    Chart.defaults.global.elements.line.borderJoinStyle = "round";
    Chart.defaults.global.elements.line.borderWidth     = 3;
    Chart.defaults.global.elements.line.borderColor     = "#000000";
    Chart.defaults.global.elements.point.radius         = 2;
    Chart.defaults.global.elements.point.borderColor    = "#444444";
    Chart.defaults.global.elements.point.borderWidth    = 1;
    Chart.defaults.global.elements.rectangle.borderWidth= 0;
    Chart.defaults.global.elements.rectangle.borderColor= "#444444";
    Chart.defaults.global.elements.line.cubicInterpolationMode = "monotone";
    Chart.defaults.global.elements.line.lineTension     = 0;

    $rootScope.device                = 'ios';

    // loading 
    $rootScope.loading               = true;
    $rootScope.controller_id         = null;
    $rootScope.status                = '';

    // set some root variables
    $rootScope.showMainMenu          = false;
    $rootScope.showBack              = false;
    $rootScope.showHeaderDetails     = false;
    $rootScope.showSplash            = false; //true;
    $rootScope.hasSensors            = false;

    $rootScope.keyboardIsOpen        = false;

    $rootScope.pageSlug              = '';
    $rootScope.templateClass         = '';
    $rootScope.showAdminTemplate     = false;


    $rootScope.user                  = {name:'', img: API_URL+'../uploads/avatars/default.jpg'};

    
    init = function()
    {
        $rootScope.switchLocale($rootScope.locale);
    }

    //go to page
    $rootScope.goToPage = function(page)
    {
        console.log('$rootScope.goToPage: '+page);
        $location.url(page);
    };

    $rootScope.loadUrl = function(url)
    {
        //console.log('$rootScope.goToPage: '+page);
        $window.open(url, "_blank");
    };

    $rootScope.sendMail = function(to, subject, body)
    {
        //console.log('$rootScope.goToPage: '+page);
        $window.open('mailto:'+to+'?subject='+subject+'&body='+body, "_self");
    };

    $rootScope.switchLocale = function(locale)
    {
        if ($rootScope.supportedLocales[locale] != undefined)
        {
            api.setLocalStoreValue('lang',locale);
            amMoment.changeLocale(locale);
            $rootScope.locale       = locale;
            $rootScope.lang         = LANG[locale];
            $window.document.title  = $rootScope.lang.Site_title;
            console.log('Locale changed to: '+locale);
            $rootScope.$broadcast('localeChange', locale);
        }
        else
        {
            console.log('Locale not available: '+locale);
        }
    }

    //device check
    $rootScope.setDevice = function()
    {
        if(runsNative())
        {
            $rootScope.device = (device.platform.toLowerCase() == 'ios') ? 'ios' : 'android';
            if($rootScope.device == 'android')
            {
                //document.getElementsByTagName('body')[0].className+='android';
            }
         
            // check for tablet
            if(window.isTablet)
            {
                $rootScope.mobile     = false;
                $rootScope.screenType = 'landscape';
                window.screen.lockOrientation('landscape');
            }
            else
            {
                $rootScope.mobile     = true;
                $rootScope.screenType = 'mobile';
                window.screen.lockOrientation('portrait');
            }
        }
        else
        {
            // browser code
            var width = window.innerWidth;
            $rootScope.mobile     = false;
            $rootScope.screenType = 'ipad';
            
            var isMobile = function()
            {
                var check = false;
                  (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
                  return check;
            }
            
            if(isMobile())
            {
                $rootScope.mobile = true; 
                $rootScope.screenType = 'mobile';
            }
        }
        console.info('mobile='+$rootScope.mobile, $rootScope.screenType);
        $rootScope.$broadcast('screenSizeChange');
        $rootScope.$digest();
    }
    $rootScope.setDevice();

    // add the resize listener
    $window.addEventListener('resize', $rootScope.setDevice);

    // listen to the colorwheel event
    document.addEventListener('colorwheel.select', function(e)
    {
        $rootScope.$apply(function()
        {
            $rootScope.$broadcast('colorwheelSelect', e);
        });
    });

    //api.reset();

    $rootScope.logout = function()
    {
        // check if we want to do this native
        if(runsNative())
        {
            navigator.notification.confirm("Weet u zeker dat u wilt uitloggen", $scope.doLogout, "Uitloggen", ["Uitloggen", "Cancel"]);
        }
        else
        {
            $rootScope.doLogout(1);
        }
    };


    $rootScope.doLogout = function(index)
    {   
        if(index > 1)
            return;

        // remove the data
        $rootScope.$broadcast('reset');
        
        // redirect to the login
        $location.path('/login');
    };


    // check if we have an api token
    $rootScope.checkToken = function()
    {
        if ($routeParams.token != undefined)
        {
            // stay at reset password page
        }
        else if (api.getApiToken() == null)
        {
            // redirect to login
            console.log('$rootScope.checkToken: no token -> login');
            $location.path('/login');
        }
        else
        {
            // fetch the settings
            console.log('$rootScope.checkToken: token available');
            settings.fetchSettings();
            //$location.path('/locations');
        }
    };

    $rootScope.checkPolicy = function(e, data)
    {
        if ($rootScope.user.policy_accepted != $rootScope.lang.policy_version)
        {
            //console.log($rootScope.user);
            $location.path('/user/edit');
            $rootScope.showMessage($rootScope.lang.approve_policy);
        }
    }
    $rootScope.$on('userUpdated', $rootScope.checkPolicy);

    setTimeout(function()
    {  
        $rootScope.$apply(function()
        {
            $rootScope.loading = false;
            $rootScope.checkToken();
        });
    }, 200);



    // check if we want header details
    $rootScope.$on('$routeChangeSuccess', function() 
    {
        // reset the vars
        $rootScope.showBack          = false;
        $rootScope.showHeaderDetails = true;

        // get the path
        var p           = $location.path();
        var slug        = p.split('/')[1];

        $rootScope.pageSlug = slug;
        $rootScope.defineTemplateClass(slug);

        // hide the details
        if(slug == 'login' || slug == 'settings')
        {
            // show the backbutton
            if(p == '/login/create')
            {
                $rootScope.showBack = true;
            }
            $rootScope.showHeaderDetails = false;
        }
        $window.scrollTo(0, 0);
    });

    $rootScope.defineTemplateClass = function(slug)
    {
        var className = '';
        var showAdmin = false;

        if ($rootScope.showSplash)
        {
            className = 'splash';
        }
        else
        {
            switch(slug)
            {
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
        $rootScope.templateClass     = className;
    }


    // switch to a menu item
    $rootScope.switchMenu = function(e, doLink, link)
    {
        // check if we want to link
        doLink = typeof doLink !== 'undefined' ? doLink : false;

        e.preventDefault();
        if(doLink)
        {
            $location.search('success', null);
            $location.path(link);
        }

        // switch the class
        $rootScope.showMainMenu = ($rootScope.showMainMenu == false) ? true : false;
    };


    //close menu overlay
    $rootScope.closeMenu = function()
    {
        // switch the class
        $rootScope.showMainMenu = ($rootScope.showMainMenu == false) ? true : false;
    };    



    // $rootScope.scrollToView = function(view)
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


    $rootScope.loginStatus = '';



    // basic history function 
    $rootScope.history = [];

    $rootScope.$on('$routeChangeSuccess', function() 
    {
        if ($location.path().indexOf('/locations') > -1)
            $rootScope.history = [];

        $rootScope.history.push($location.path());
    });


    $rootScope.back = function()
    {
        $rootScope.$broadcast('backbutton');
    };

    $rootScope.historyBack = function()
    {
        $window.history.back();
    }

    // handle the native backbutton
    $rootScope.handleBackButton = function()
    {
        document.addEventListener("backbutton", function(e)
        {
            // prevent default
            e.preventDefault();

            // apply
            $rootScope.$apply(function()
            {
                $rootScope.$broadcast('backbutton');
            });
        });
    };

    $rootScope.handleBackButton();


    //***************/
    /*   MESSAGES   */
    /***************/
    $rootScope.showMessage = function(message, callback, title, buttonName) 
    {
        title      = title || "";
        buttonName = buttonName || 'OK';

        if(navigator.notification && navigator.notification.alert) 
        {
            navigator.notification.alert(
                message,    // message
                callback,   // callback
                title,      // title
                buttonName  // buttonName
            );
        } 
        else 
        {
            window.alert(message);
            if(callback != null)
            {
                callback();
            }
        }
    };

    $rootScope.showConfirm = function(message, callbackOk, callbackVariable, callbackCancel) 
    {
        if(navigator.notification && navigator.notification.confirm) 
        {
            navigator.notification.confirm(
                message,    // message
                callback,   // callback
                title,      // title
                buttonNames  // buttonNames
            );
        } 
        else 
        {
            var c = window.confirm(message);
            if (c && typeof callbackOk == 'function')
                callbackOk(callbackVariable);
            else if (typeof callbackCancel == 'function')
                callbackCancel(callbackVariable);
        }
    };



    /***************/
    /*    FORMS    */
    /***************/
    $rootScope.validateFields = function(inputs, form, fields)
    {
        var valid = true;
        var error = null;

        for(var i in inputs)
        {
            if(form[i] != undefined && !form[i].$valid)
            {
                var required = !!form[i].$error.required;
                var email    = !!form[i].$error.email;
                var password = !!form[i].$error.passwordMatch;

                var msg = '';
                if(required)
                {
                    msg = $rootScope.lang.empty_fields;
                }
                else if(email)
                {
                    msg = $rootScope.lang.no_valid_email;
                }
                else if(password)
                {
                    msg = $rootScope.lang.match_passwords;
                }

                fields[i] = true;
                error = 
                {
                    show          : true,
                    resultType    : 'error',
                    resultMessage : msg,
                };

                valid = false;
            }
        }

        // check if its valid
        if(!valid)
            return error;

        return true;
    };





    /***************/
    /*   LOADING   */
    /***************/
    // set the basic loading listeners
    $rootScope.$on('startLoading', function(e, args)
    {
        $rootScope.loading = true;
    });

     // set the basic loading listeners
    $rootScope.$on('endLoading', function()
    {
        $rootScope.loading = false;
        //console.log('endLoading');
    });

});



/* Load angular when our device is ready */
var onDeviceReady = function()
{   
    // bootstrap angular
    angular.bootstrap(document.querySelector("body#app"), ["app"]);

    // check for cordova
    if(runsNative())
    {
        cordova.plugins.Keyboard.disableScroll(true);
    }

    init();
};


/* check if we're running an app or development version */
window.onload = function()
{   
    var app = document.URL.indexOf('http://') === -1 && document.URL.indexOf('https://') === -1;
    if (app) 
    {
        document.addEventListener("deviceready", onDeviceReady, false);
    } 
    else
    {
        onDeviceReady();
    } 
};


