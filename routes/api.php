<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => \Barryvdh\Cors\HandleCors::class], function()
{    

	Route::get('/',function(){
		return redirect('webapp');
	});

	// save sensor data of multiple sensors
	Route::post('sensors', 		'Api\MeasurementController@storeMeasurementData');
	Route::post('lora_sensors', 'Api\MeasurementController@lora_sensors');

	// save sensor data of multiple sensors (unsecure)
	Route::post('unsecure_sensors', 'Api\MeasurementController@storeMeasurementData');
	
	// User functions
	Route::post('register', 	'Api\UserController@register');
	Route::post('login', 		'Api\UserController@login');
	Route::post('user/reminder','Api\UserController@reminder');
	Route::post('user/reset', 	'Api\UserController@reset');

	// // Email Verification Routes...
	Route::get('email/verify', 'Api\Auth\VerificationController@show')->name('apiverification.notice');
	Route::get('email/verify/{id}', 'Api\Auth\VerificationController@verify')->name('apiverification.verify');
	Route::post('email/resend', 'Api\Auth\VerificationController@resend')->name('apiverification.resend');

	Route::post('groups/checktoken', 'Api\GroupController@checktoken');


	Route::group(['middleware'=>['auth:api', 'verifiedApi']], function()
	{  
		// Authenticate and provide the token
		Route::post('authenticate', 		'Api\UserController@authenticate');

		// get more data of 1 sensor (Device)
		Route::post('devices/multiple',		'Api\DeviceController@storeMultiple');
		Route::get('devices/ttn/{dev_id}',  'Api\DeviceController@getTTNDevice');
		Route::post('devices/ttn/{dev_id}', 'Api\DeviceController@postTTNDevice');
		Route::get('sensors/measurements', 	'Api\MeasurementController@data');
		Route::get('sensors/lastvalues', 	'Api\MeasurementController@lastvalues');
		Route::get('sensors/lastweight', 	'Api\MeasurementController@lastweight');
		Route::post('sensors/calibrateweight','Api\MeasurementController@calibrateweight');
		Route::post('sensors/offsetweight' ,'Api\MeasurementController@offsetweight');
		Route::get('sensors/measurement_types', 'Api\MeasurementController@sensor_measurement_types');
		Route::get('sensors/measurement_types_available', 'Api\MeasurementController@sensor_measurement_types_available');
		Route::post('lora_sensors_auth',  	'Api\MeasurementController@lora_sensors');

		Route::post('settings', 			'Api\SettingController@store');
		Route::get('settings', 				'Api\SettingController@index');

		Route::get('taxonomy/lists', 		'Api\TaxonomyController@lists');
		Route::get('taxonomy/taxonomy', 	'Api\TaxonomyController@taxonomy');

		Route::get('inspections/lists', 	'Api\InspectionsController@lists');
		Route::get('inspections/{hive_id}', 'Api\InspectionsController@show');
		Route::get('inspections/hive/{hive_id}', 'Api\InspectionsController@hive');
		Route::post('inspections/store', 	'Api\InspectionsController@store');
		Route::delete('inspections/{id}', 	'Api\InspectionsController@destroy');

		Route::get('weather', 				'Api\WeatherController@index');

		Route::get('research', 				'Api\ResearchController@index');
		Route::post('research/{id}/add_consent',   'Api\ResearchController@add_consent');
		Route::post('research/{id}/remove_consent','Api\ResearchController@remove_consent');

		Route::delete('user', 				'Api\UserController@destroy');
		Route::patch('user', 				'Api\UserController@edit');

		// Control resources 
		Route::resource('devices', 			'Api\DeviceController',		 			['except'=>['create','edit']]);
		Route::resource('checklists', 		'Api\ChecklistController',		 		['except'=>['create','edit']]);
		Route::resource('categories', 		'Api\CategoryController',		 		['except'=>['create','edit','store','update','destroy']]);
		Route::resource('groups', 			'Api\GroupController', 			 		['except'=>['create','edit']]);
		Route::resource('hives', 			'Api\HiveController', 			 		['except'=>['create','edit']]);
		Route::resource('locations', 		'Api\LocationController', 	 			['except'=>['create','edit']]);
		Route::resource('productions',		'Api\ProductionController',				['except'=>['create','edit']]);
		Route::resource('queens', 			'Api\QueenController',		 			['except'=>['create','edit']]);
		Route::resource('images', 			'Api\ImageController', 					['except'=>['create','edit','destroy']]);
		Route::resource('sensordefinition', 'Api\SensorDefinitionController', 		['except'=>['create','edit']]);

		Route::delete('images', 			'Api\ImageController@destroyByUrl');

		Route::delete('groups/detach/{id}', 'Api\GroupController@detach');

		Route::get('export',				'Api\ExportController@all');
		Route::post('export/csv',			'Api\ExportController@generate_csv');

	});

});