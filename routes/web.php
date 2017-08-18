<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

Route::get('/',function(){
	return redirect('https://beep.nl');
});

Route::get('home', function(){
	return redirect('admin');
});

// Hack for redirecting e-mail reset password link to webapp
Route::get('password/reset/{token}',['as'=>'password.reset', function ($token) {
    return redirect('https://app.beep.nl/#!/login/reset/'.$token);
}]);

// Secured by login
Route::group(
	[
		'prefix' => LaravelLocalization::setLocale(), 
		'middleware' => ['auth', 'localeSessionRedirect', 'localizationRedirect']
	], 
	function() 
	{
		Route::get('admin', 'UserController@index');
		
		// Resource controllers 
		Route::resource('users', 'UserController');

		// Routes
		Route::get('roles',['as'=>'roles.index','uses'=>'RoleController@index','middleware' => ['permission:role-list|role-create|role-edit|role-delete']]);
		Route::get('roles/create',['as'=>'roles.create','uses'=>'RoleController@create','middleware' => ['permission:role-create']]);
		Route::post('roles/create',['as'=>'roles.store','uses'=>'RoleController@store','middleware' => ['permission:role-create']]);
		Route::get('roles/{id}',['as'=>'roles.show','uses'=>'RoleController@show']);
		Route::get('roles/{id}/edit',['as'=>'roles.edit','uses'=>'RoleController@edit','middleware' => ['permission:role-edit']]);
		Route::patch('roles/{id}',['as'=>'roles.update','uses'=>'RoleController@update','middleware' => ['permission:role-edit']]);
		Route::delete('roles/{id}',['as'=>'roles.destroy','uses'=>'RoleController@destroy','middleware' => ['permission:role-delete']]);

		Route::get('groups',['as'=>'groups.index','uses'=>'GroupController@index','middleware' => ['permission:group-list|group-create|group-edit|group-delete']]);
		Route::get('groups/create',['as'=>'groups.create','uses'=>'GroupController@create','middleware' => ['permission:group-create']]);
		Route::post('groups/create',['as'=>'groups.store','uses'=>'GroupController@store','middleware' => ['permission:group-create']]);
		Route::get('groups/{id}',['as'=>'groups.show','uses'=>'GroupController@show']);
		Route::get('groups/{id}/edit',['as'=>'groups.edit','uses'=>'GroupController@edit','middleware' => ['permission:group-edit']]);
		Route::patch('groups/{id}',['as'=>'groups.update','uses'=>'GroupController@update','middleware' => ['permission:group-edit']]);
		Route::delete('groups/{id}',['as'=>'groups.destroy','uses'=>'GroupController@destroy','middleware' => ['permission:group-delete']]);

		Route::get('sensors',['as'=>'sensors.index','uses'=>'SensorController@index','middleware' => ['permission:sensor-list|sensor-create|sensor-edit|sensor-delete']]);
		Route::get('sensors/create',['as'=>'sensors.create','uses'=>'SensorController@create','middleware' => ['permission:sensor-create']]);
		Route::post('sensors/create',['as'=>'sensors.store','uses'=>'SensorController@store','middleware' => ['permission:sensor-create']]);
		Route::get('sensors/{id}',['as'=>'sensors.show','uses'=>'SensorController@show']);
		Route::get('sensors/{id}/edit',['as'=>'sensors.edit','uses'=>'SensorController@edit','middleware' => ['permission:sensor-edit']]);
		Route::patch('sensors/{id}',['as'=>'sensors.update','uses'=>'SensorController@update','middleware' => ['permission:sensor-edit']]);
		Route::delete('sensors/{id}',['as'=>'sensors.destroy','uses'=>'SensorController@destroy','middleware' => ['permission:sensor-delete']]);

	}
);