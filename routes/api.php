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

Route::group(['middleware' => 'cors'], function()
{    


	Route::get('/',function(){
		return redirect('webapp');
	});

	// User functions
	Route::post('register', 	'Api\UserController@register');
	Route::post('login', 		'Api\UserController@login');
	Route::post('user/reminder','Api\UserController@reminder');
	Route::post('user/reset', 	'Api\UserController@reset');

	// save sensor data of multiple sensors
	Route::post('sensors', 		'Api\SensorController@store');

	// save sensor data of multiple sensors (unsecure)
	Route::post('unsecure_sensors', 'Api\SensorController@store');

	Route::group(['middleware'=>'auth:api'], function()
	{  
		// Authenticate and provide the token
		Route::post('authenticate', 		'Api\UserController@authenticate');

		// get list of sensors
		Route::get('sensors', 				'Api\SensorController@index');

		// get more data of 1 sensors
		Route::get('sensors/{name}', 		'Api\SensorController@data');

		// save setting 
		Route::post('settings', 			'Api\SettingController@store');
		// get settings
		Route::get('settings', 				'Api\SettingController@index');
		

		// Get Inspections and categories (actions, conditions, beeraces, hivetypes)
		Route::get('inspections/lists', 	'Api\InspectionController@lists');
		Route::get('inspections/{hive_id}', 'Api\InspectionController@index');
		Route::delete('inspections/{hive_id}/{date}', 'Api\InspectionController@destroy');

		// Store multiple inspection values at once
		Route::post('actions/multiple', 	'Api\ActionController@storeMultiple');
		Route::post('conditions/multiple', 	'Api\ConditionController@storeMultiple');


		// Control resources 
		Route::resource('categories', 		'Api\CategoryController',		 		['except'=>['create','edit']]);
		Route::resource('actions', 			'Api\ActionController',		 			['except'=>['create','edit']]);
		Route::resource('conditions', 		'Api\ConditionController', 				['except'=>['create','edit']]);
		Route::resource('hives', 			'Api\HiveController', 			 		['except'=>['create','edit']]);
		Route::resource('locations', 		'Api\LocationController', 	 			['except'=>['create','edit']]);
		Route::resource('productions',		'Api\ProductionController',				['except'=>['create','edit']]);
		Route::resource('queens', 			'Api\QueenController',		 			['except'=>['create','edit']]);

		
	});

});
