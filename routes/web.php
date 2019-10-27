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
	return redirect('dashboard');
});

Route::get('admin', function(){
	return redirect('dashboard');
});

Route::get('webapp', function(){
	return view('app/index');
});

// Hack for redirecting e-mail reset password link to webapp
// Route::get('password/reset/{token}',['as'=>'password.reset', function ($token) {
//     return redirect(env('APP_URL').'/#!/login/reset/'.$token);
// }]);

// Secured by login
Route::group(
	[
		'prefix' => LaravelLocalization::setLocale(), 
		'middleware' => ['auth', 'localeSessionRedirect', 'localizationRedirect', 'verified']
	], 
	function() 
	{
		Route::get('dashboard', ['as'=>'dashboard.index','uses'=>'DashboardController@index']);

		Route::resource('checklists', 'ChecklistController');
		Route::resource('inspections', 'InspectionsController');
		Route::resource('users', 'UserController');		
		

		Route::group(
			['middleware' => ['role:superadmin|admin|translator']],
			function()
			{				
				
				// Routes
				Route::get('languages',				['as'=>'languages.index','uses'=>'LanguageController@index','middleware' => ['permission:language-list|language-create|language-edit|language-delete']]);
				Route::get('languages/create',		['as'=>'languages.create','uses'=>'LanguageController@create','middleware' => ['permission:language-create']]);
				Route::post('languages/create',		['as'=>'languages.store','uses'=>'LanguageController@store','middleware' => ['permission:language-create']]);
				Route::get('languages/{id}',		['as'=>'languages.show','uses'=>'LanguageController@show']);
				Route::get('languages/{id}/edit',	['as'=>'languages.edit','uses'=>'LanguageController@edit','middleware' => ['permission:language-edit']]);
				Route::patch('languages/{id}',		['as'=>'languages.update','uses'=>'LanguageController@update','middleware' => ['permission:language-edit']]);
				Route::delete('languages/{id}',		['as'=>'languages.destroy','uses'=>'LanguageController@destroy','middleware' => ['permission:language-delete']]);

				Route::get('translations',			['as'=>'translations.index','uses'=>'TranslationController@index','middleware' => ['permission:translation-list']]);
				Route::get('translations/{language}',['as'=>'translations.edit','uses'=>'TranslationController@edit','middleware' => ['permission:translation-create']]);
				Route::patch('translations/{language}',['as'=>'translations.update','uses'=>'TranslationController@update','middleware' => ['permission:translation-edit']]);

				Route::get('groups',				['as'=>'groups.index','uses'=>'GroupController@index','middleware' => ['permission:group-list|group-create|group-edit|group-delete']]);
				Route::get('groups/create',			['as'=>'groups.create','uses'=>'GroupController@create','middleware' => ['permission:group-create']]);
				Route::post('groups/create',		['as'=>'groups.store','uses'=>'GroupController@store','middleware' => ['permission:group-create']]);
				Route::get('groups/{id}',			['as'=>'groups.show','uses'=>'GroupController@show']);
				Route::get('groups/{id}/edit',		['as'=>'groups.edit','uses'=>'GroupController@edit','middleware' => ['permission:group-edit']]);
				Route::patch('groups/{id}',			['as'=>'groups.update','uses'=>'GroupController@update','middleware' => ['permission:group-edit']]);
				Route::delete('groups/{id}',		['as'=>'groups.destroy','uses'=>'GroupController@destroy','middleware' => ['permission:group-delete']]);

				Route::get('sensors',				['as'=>'sensors.index','uses'=>'SensorController@index','middleware' => ['permission:sensor-list|sensor-create|sensor-edit|sensor-delete']]);
				Route::get('sensors/create',		['as'=>'sensors.create','uses'=>'SensorController@create','middleware' => ['permission:sensor-create']]);
				Route::post('sensors/create',		['as'=>'sensors.store','uses'=>'SensorController@store','middleware' => ['permission:sensor-create']]);
				Route::get('sensors/{id}',			['as'=>'sensors.show','uses'=>'SensorController@show']);
				Route::get('sensors/{id}/edit',		['as'=>'sensors.edit','uses'=>'SensorController@edit','middleware' => ['permission:sensor-edit']]);
				Route::patch('sensors/{id}',		['as'=>'sensors.update','uses'=>'SensorController@update','middleware' => ['permission:sensor-edit']]);
				Route::delete('sensors/{id}',		['as'=>'sensors.destroy','uses'=>'SensorController@destroy','middleware' => ['permission:sensor-delete']]);
				
		});

		Route::group(
			['middleware' => ['role:superadmin']],
			function()
			{
				Route::get('info', function(){
					return view('phpinfo');
				});
				
				// Roles
				Route::get('roles',				['as'=>'roles.index','uses'=>'RoleController@index','middleware' => ['permission:role-list|role-create|role-edit|role-delete']]);
				Route::get('roles/create',		['as'=>'roles.create','uses'=>'RoleController@create','middleware' => ['permission:role-create']]);
				Route::post('roles/create',		['as'=>'roles.store','uses'=>'RoleController@store','middleware' => ['permission:role-create']]);
				Route::get('roles/{id}',		['as'=>'roles.show','uses'=>'RoleController@show']);
				Route::get('roles/{id}/edit',	['as'=>'roles.edit','uses'=>'RoleController@edit','middleware' => ['permission:role-edit']]);
				Route::patch('roles/{id}',		['as'=>'roles.update','uses'=>'RoleController@update','middleware' => ['permission:role-edit']]);
				Route::delete('roles/{id}',		['as'=>'roles.destroy','uses'=>'RoleController@destroy','middleware' => ['permission:role-delete']]);

				// Resource controllers 
				Route::resource('permissions', 		'PermissionController');
				Route::resource('physicalquantity', 'PhysicalQuantityController');
				Route::resource('categoryinputs', 	'CategoryInputsController');
				Route::resource('inspection-items', 'InspectionItemsController');
				Route::resource('measurement', 		'MeasurementController');

				Route::resource('categories', 		'CategoriesController');
				Route::delete('categories/{id}/pop',['as'=>'categories.pop','uses'=>'CategoriesController@pop','middleware' => ['permission:taxonomy-delete']]);
				Route::get('categories/{id}/fix',	['as'=>'categories.fix','uses'=>'CategoriesController@fix']);
				Route::get('taxonomy/display',	['as'=>'taxonomy.display','uses'=>'TaxonomyController@display']);

				Route::delete('checklists/destroy/copies',	['as'=>'checklists.copies','uses'=>'ChecklistController@destroyCopies']);
				
			}
		);

	}
);
Auth::routes(['verify' => true]);

