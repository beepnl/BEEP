<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta charset="UTF-8">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-tap-highlight" content="no">

        <meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE">
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height">

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Beep">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">

        <link rel="manifest" href="/manifest.json">
        <link rel="shortcut icon" type="image/png" href="/img/icons/icon_beep.png">
        <link rel="apple-touch-icon" href="/img/icons/Icon-60.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/img/icons/Icon-76.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/img/icons/Icon-60@2x.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/img/icons/Icon-72@2x.png">

         <!-- iPhone SPLASHSCREEN 320x460 -->
        <link href="/img/splash/apple-touch-startup-iphone-portrait.png" media="only screen and (device-width: 320px)" rel="apple-touch-startup-image">
        <!-- iPhone (Retina) SPLASHSCREEN 640x920 -->
        <link href="/img/splash/apple-touch-startup-iphone-retina-portrait.png" media="only screen and (device-width: 320px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
        <!-- iPhone 5 (Retina) SPLASHSCREEN 640x1136 -->
        <link href="/img/splash/apple-touch-startup-iphone5-retina-portrait.png" media="only screen and (device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
        <!-- iPhone 6 (Retina) SPLASHSCREEN 750x1344 -->
        <link href="/img/splash/apple-touch-startup-iphone6-portrait.png" media="only screen and (device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
        <!-- iPhone 6 plus (Retina) SPLASHSCREEN 1080x1920 -->
        <link href="/img/splash/apple-touch-startup-iphone6plus-portrait.png" media="only screen and (device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image">
        <!-- iPad (portrait) SPLASHSCREEN 768x1004 -->
        <link href="/img/splash/apple-touch-startup-ipad-portrait.png" media="only screen and (device-width: 768px) and (orientation: portrait)" rel="apple-touch-startup-image">
        <!-- iPad (landscape) SPLASHSCREEN 1024x748 -->
        <link href="/img/splash/apple-touch-startup-ipad-landscape.png" media="only screen and (device-width: 768px) and (orientation: landscape)" rel="apple-touch-startup-image">
        <!-- iPad (Retina, portrait) SPLASHSCREEN 1536x2008 -->
        <link href="/img/splash/apple-touch-startup-ipad-retina-portrait.png" media="only screen and (device-width: 1536px) and (orientation: portrait) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">
        <!-- iPad (Retina, landscape) SPLASHSCREEN 2048x1496 -->
        <link href="/img/splash/apple-touch-startup-ipad-retina-landscape.png" media="only screen and (device-width: 1536px) and (orientation: landscape) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image">

        <!-- admin LTE template -->
        <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,700" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('css/skin-base.css') }}" media="screen">
        <link rel="stylesheet" href="{{ mix('app/css/skin.css') }}" media="screen">
        <link rel="stylesheet" href="{{ mix('css/skin-additions.css') }}" media="screen">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

        <!--[if lt IE 9]>
            <link rel="stylesheet" type="text/css" href="vendor/datetimepicker/src/DateTimePicker-ltie9.css" />
            <script type="text/javascript" src="vendor/datetimepicker/src/DateTimePicker-ltie9.js"></script>
        <![endif]-->

        <title>BEEP</title>
        
    </head>

    <body id="app" class="hold-transition skin-beep" ng-class="templateClass" maps-autocomplete-mobile>

        <div ng-class="{'wrapper':showAdminTemplate}">

          <!-- Main Header -->
          <div ng-include="'/app/views/template/template-block-header.html?v=3'" ng-show="showAdminTemplate"></div>

          <!-- Left side column. contains the logo and sidebar -->
          <div ng-include="'/app/views/template/template-block-sidebar-left.html?v=3'" ng-show="showAdminTemplate"></div>

          <!-- Content Wrapper. Contains page content -->
          <div ng-class="{'content-wrapper':showAdminTemplate}">
            <main ng-view></main>
          </div>
          
          <!-- Main Footer -->
          <div ng-include="'/app/views/template/template-block-footer.html'" ng-show="showAdminTemplate"></div>
          
        </div>

        <!-- AdminLTE App -->
        <script src="{{ mix('js/jquery.js') }}"></script>
        <script src="{{ mix('js/scripts-base.js') }}"></script>

        <!-- Angular app -->
        <script src="{{ mix('app/js/constants.js') }}"></script>
        <script src="{{ mix('app/js/angular.js') }}"></script>
        <script src="{{ mix('app/js/angular-modules.js') }}"></script>
        <script src="{{ mix('app/js/angular-helpers.js') }}"></script>
        <script src="{{ mix('app/js/angular-index.js') }}"></script>
        <script src="{{ mix('app/js/angular-directives.js') }}"></script>
        <script src="{{ mix('app/js/angular-code.js') }}"></script>

        <!-- external: Google maps API for easy address input -->
        <script src="https://maps.google.com/maps/api/js?libraries=places&key={{ env('GOOGLE_MAPS_KEY') }}"></script>

    </body>
</html>
