const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/*
For RTL add
<link rel="stylesheet" href="node_modules/bootstrap-rtl/dist/css/bootstrap-rtl.min.css">
<link rel="stylesheet" href="node_modules/admin-lte-rtl/dist/css/AdminLTE-rtl.css">
*/

// Styling
mix.styles([
	'node_modules/normalize-css/normalize.css',
	'node_modules/admin-lte/bootstrap/css/bootstrap.min.css',
	'node_modules/jstree/dist/themes/default/style.min.css',
	'resources/assets/css/portal.css',
	'resources/assets/css/skin-beep.css',
	], 'public/css/skin-base.css').version();


mix.copy([
	'node_modules/admin-lte/bootstrap/css/bootstrap.min.css.map',
	], 'public/css');

mix.copy([
	'node_modules/hammerjs/hammer.min.js.map',
	], 'public/js');

mix.copy([
    'resources/assets/js/lang/*',
    ], 'public/js/languages');

mix.styles([
	'node_modules/animate.css/animate.min.css',
	'node_modules/ng-dialog/css/ngDialog..min.css',
	'node_modules/angular-ui-switch/angular-ui-switch.min.css',
	'node_modules/angular-color-picker/angular-color-picker.css',
	'node_modules/angularjs-slider/dist/rzslider.min.css',
	'node_modules/datetimepicker/dist/DateTimePicker.min.css',
	'resources/assets/css/measurements.css',
	], 'public/app/css/skin.css').version();

mix.styles([
	'resources/assets/css/skin-beep-additions.css'
	], 'public/css/skin-additions.css').version();

mix.copy('resources/assets/img', 'public/img');
mix.copy([
	'node_modules/jstree/dist/themes/default/*.png', 
	'node_modules/jstree/dist/themes/default/*.gif'
	], 'public/css');

mix.copy('resources/assets/fonts', 'public/fonts');
mix.copy('node_modules/admin-lte/bootstrap/fonts/', 'public/fonts');

mix.scripts(
	['node_modules/admin-lte/plugins/jQuery/jquery-2.2.3.min.js'],
	'public/js/jquery.js');

mix.scripts([
	'node_modules/admin-lte/bootstrap/js/bootstrap.min.js',
    'node_modules/admin-lte/dist/js/app.js',
    'node_modules/admin-lte/plugins/slimScroll/jquery.slimscroll.min.js',
	'node_modules/hammerjs/hammer.min.js',
    'node_modules/jstree/dist/jstree.min.js',   
	], 'public/js/scripts-base.js').version();

mix.babel([
    'resources/assets/js/beep.js'
    ], 'public/js/scripts-portal.js').version();

mix.copyDirectory('node_modules/datatables.net-plugins/i18n', 'public/js/datatables/i18n');

mix.babel([
	'resources/assets/js/constants.js',
    ], 'public/app/js/constants.js').version();

mix.scripts([
    'node_modules/angular/angular.min.js',
    ], 'public/app/js/angular.js').version();

mix.babel([
    'node_modules/angular-route/angular-route.min.js',
    'node_modules/angular-animate/angular-animate.min.js',
    'node_modules/moment/moment.js',
    'node_modules/angular-moment/angular-moment.min.js',
    'node_modules/chart.js/dist/Chart.min.js',
    'node_modules/angular-chart.js/angular-chart.js',
    'node_modules/ng-dialog/js/ngDialog.js',
    'node_modules/ngmap/build/scripts/ng-map.min.js',
    'node_modules/angular-color-picker/angular-color-picker.js',
    'node_modules/angularjs-slider/dist/rzslider.min.js',
    'node_modules/ng-js-tree/dist/ngJsTree.min.js',
    'node_modules/angular-addtocalendar/dist/addtocalendar.js',
    'node_modules/iscroll/build/iscroll.js',
    'node_modules/ng-iScroll/src/ng-iscroll.js',
    'node_modules/angularjs-gauge/src/angularjs-gauge.js', 
    'node_modules/datetimepicker/src/DateTimePicker.js', 
	], 'public/app/js/angular-modules.js').version();

mix.babel([
	'node_modules/angular-gestures/src/gestures.js',
    'resources/assets/js/helpers/nalert.js',
    'node_modules/fastclick/lib/fastclick.js',
    'resources/assets/js/helpers/functions.js',
	'resources/assets/js/filters/icon-filters.js',
    'resources/assets/js/filters/text-filters.js',
   	], 'public/app/js/angular-helpers.js').version();

mix.babel([
    'resources/assets/js/index.js',
    'resources/assets/js/routes.js',
	'resources/assets/js/lang/nl.js',
    'resources/assets/js/lang/en.js',
    'resources/assets/js/lang/de.js',
    'resources/assets/js/lang/es.js',
    'resources/assets/js/lang/fr.js',
    'resources/assets/js/lang/ro.js',
    'resources/assets/js/lang/pt.js',
    'node_modules/moment/locale/nl.js',
    'node_modules/moment/locale/de.js',
    'node_modules/moment/locale/es.js',
    'node_modules/moment/locale/fr.js',
    'node_modules/moment/locale/ro.js',
    'node_modules/moment/locale/pt.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-nl.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-de.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-es.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-fr.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-ro.js',
    'node_modules/datetimepicker/src/i18n/DateTimePicker-i18n-pt.js',
     ], 'public/app/js/angular-index.js').version();

mix.babel([   
    'node_modules/angular-ui-switch/angular-ui-switch.min.js',
    'resources/assets/js/directives/angular-stepper.js',
    'resources/assets/js/directives/angular-background.js',
    'resources/assets/js/directives/angular-rating.js',
    'resources/assets/js/directives/angular-smile-rating.js',
    'resources/assets/js/directives/angular-yes-no.js',
    'resources/assets/js/directives/angular-maps-autocomplete-mobile.js',
    'resources/assets/js/directives/beep-checklist-fieldset.js',
    'resources/assets/js/directives/beep-checklist-input.js',
    'resources/assets/js/directives/beep-hive.js',
    'resources/assets/js/directives/beep-hive-selector.js',
    'resources/assets/js/directives/beep-group-hive.js',
    'resources/assets/js/directives/beep-user-selector.js',
    'resources/assets/js/directives/beep-sensor.js',
    'resources/assets/js/directives/angular-country-select.js',
    'resources/assets/js/directives/angular-restrict-input.js',
    'node_modules/angular-password/angular-password.min.js',
	], 'public/app/js/angular-directives.js').version();

mix.babel([  
    'resources/assets/js/models/api.js',
    'resources/assets/js/models/settings.js',
    'resources/assets/js/models/hives.js',
    'resources/assets/js/models/measurements.js',
    'resources/assets/js/models/inspections.js',
    'resources/assets/js/models/groups.js',
    'resources/assets/js/controllers/load.js',
    'resources/assets/js/controllers/user.js',
    'resources/assets/js/controllers/settings.js',
    'resources/assets/js/controllers/locations.js',
    'resources/assets/js/controllers/hives.js',
    'resources/assets/js/controllers/password.js',
    'resources/assets/js/controllers/inspection_create.js',
    'resources/assets/js/controllers/inspections.js',
    'resources/assets/js/controllers/checklist.js',
    'resources/assets/js/controllers/measurements.js',
    'resources/assets/js/controllers/export.js',
    'resources/assets/js/controllers/sensors.js',
    'resources/assets/js/controllers/groups.js',
	], 'public/app/js/angular-code.js').version();


mix.copyDirectory('node_modules/components-font-awesome/webfonts', 'public/webfonts');
mix.copyDirectory('resources/assets/app-views', 'public/app/views').version();

mix.copy('resources/terms', 'public');

//mix.browserSync('https://beep.test');

