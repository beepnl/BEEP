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

Auth::routes(['verify' => true]);

Route::get('/',function(){
	return redirect('webapp');
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

Route::get('code', 	 	 ['as'=>'sample-code.code','uses'=>'SampleCodeController@code']);
Route::post('codecheck', ['as'=>'sample-code.check','uses'=>'SampleCodeController@check'])->middleware('throttle:3,1');
Route::patch('coderesult',['as'=>'sample-code.resultsave','uses'=>'SampleCodeController@resultsave']);

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
		Route:: group(
			['middleware' => ['role:superadmin|admin|lab']],
			function()
			{		
				Route::get('code-upload', 	 	 ['as'=>'sample-code.upload','uses'=>'SampleCodeController@upload']);
				Route::post('code-upload-store', ['as'=>'sample-code.upload-store','uses'=>'SampleCodeController@upload_store']);
			});

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

			});

		Route::group(
			['middleware' => ['role:superadmin|admin|manager']],
			function()
			{				
				
				Route::get('devices',				['as'=>'devices.index','uses'=>'DeviceController@index','middleware' => ['permission:sensor-list|sensor-create|sensor-edit|sensor-delete']]);
				Route::get('devices/create',		['as'=>'devices.create','uses'=>'DeviceController@create','middleware' => ['permission:sensor-create']]);
				Route::get('devices/data',		    ['as'=>'devices.data','uses'=>'DeviceController@data']);
				Route::post('devices/create',		['as'=>'devices.store','uses'=>'DeviceController@store','middleware' => ['permission:sensor-create']]);
				Route::get('devices/{id}',			['as'=>'devices.show','uses'=>'DeviceController@show']);
				Route::get('devices/{id}/undelete',	['as'=>'devices.undelete','uses'=>'DeviceController@undelete']);
				Route::get('devices/{id}/edit',		['as'=>'devices.edit','uses'=>'DeviceController@edit','middleware' => ['permission:sensor-edit']]);
				Route::get('devices/{id}/flashlog/{fl_id}',	['as'=>'devices.flashlog','uses'=>'DeviceController@flashlog','middleware' => ['permission:sensor-edit']]);
				Route::patch('devices/{id}',		['as'=>'devices.update','uses'=>'DeviceController@update','middleware' => ['permission:sensor-edit']]);
				Route::delete('devices/{id}',		['as'=>'devices.destroy','uses'=>'DeviceController@destroy','middleware' => ['permission:sensor-delete']]);
			});
				

		Route::group(
			['middleware' => ['role:superadmin|admin']],
			function()
			{		
				Route::get('groups',				['as'=>'groups.index','uses'=>'GroupController@index','middleware' => ['permission:group-list|group-create|group-edit|group-delete']]);
				Route::get('groups/create',			['as'=>'groups.create','uses'=>'GroupController@create','middleware' => ['permission:group-create']]);
				Route::post('groups/create',		['as'=>'groups.store','uses'=>'GroupController@store','middleware' => ['permission:group-create']]);
				Route::get('groups/{id}',			['as'=>'groups.show','uses'=>'GroupController@show']);
				Route::get('groups/{id}/edit',		['as'=>'groups.edit','uses'=>'GroupController@edit','middleware' => ['permission:group-edit']]);
				Route::patch('groups/{id}',			['as'=>'groups.update','uses'=>'GroupController@update','middleware' => ['permission:group-edit']]);
				Route::delete('groups/{id}',		['as'=>'groups.destroy','uses'=>'GroupController@destroy','middleware' => ['permission:group-delete']]);

				Route::resource('physicalquantity', 'PhysicalQuantityController');
				Route::resource('categoryinputs', 	'CategoryInputsController');
				Route::resource('inspection-items', 'InspectionItemsController');
				Route::resource('measurement', 		'MeasurementController');
				Route::resource('sensordefinition' ,'SensorDefinitionController');
				Route::resource('flash-log', 		'FlashLogController');
				Route::get('flash-log/parse/{id}',		['as'=>'flash-log.parse','uses'=>'FlashLogController@parse']);

				// Create new research
				Route::get('research/create',		['as'=>'research.create','uses'=>'ResearchController@create']);
				Route::post('research/create',		['as'=>'research.store','uses'=>'ResearchController@store']);
				
				Route::resource('categories', 		'CategoriesController');
				Route::delete('categories/{id}/pop',['as'=>'categories.pop','uses'=>'CategoriesController@pop','middleware' => ['permission:taxonomy-delete']]);
				Route::get('categories/{id}/fix',	['as'=>'categories.fix','uses'=>'CategoriesController@fix']);
				Route::get('categories/{id}/duplicate',	['as'=>'categories.duplicate','uses'=>'CategoriesController@duplicate']);
				Route::get('taxonomy/display',	['as'=>'taxonomy.display','uses'=>'TaxonomyController@display']);
				
				Route::resource('dashboard-group', 'DashboardGroupController');
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
				Route::get('alert-rule/{id}/parse',['as'=>'alert-rule.parse','uses'=>'AlertRuleController@parse']);

				// Resource controllers 
				Route::resource('permissions', 		'PermissionController');
				Route::resource('image', 			'ImageController');
				Route::resource('sample-code', 		'SampleCodeController');
				Route::resource('alert', 			'AlertController');
				Route::resource('alert-rule', 		'AlertRuleController');
				Route::resource('alert-rule-formula','AlertRuleFormulaController');
				Route::resource('calculation-model', 'CalculationModelController');

				Route::get('calculation-model/{id}/run',['as'=>'calculation-model.run','uses'=>'CalculationModelController@run']);

				Route::delete('checklists/destroy/copies',	['as'=>'checklists.copies','uses'=>'ChecklistController@destroyCopies']);
				
			});

		// Open research routes based on database access
		Route::get('dashboard', ['as'=>'dashboard.index','uses'=>'DashboardController@index']);

		Route::resource('checklists', 			'ChecklistController');
		Route::resource('inspections', 			'InspectionsController');
		Route::resource('users', 				'UserController');		
		Route::resource('hive-tags', 			'HiveTagsController');
		Route::resource('checklist-svg',		'ChecklistSvgController');
		
		Route::get('research', 				['as'=>'research.index','uses'=>'ResearchController@index']);
		Route::get('research/{id}', 		['as'=>'research.show','uses'=>'ResearchController@show']);
		Route::get('research/{id}/data',    ['as'=>'research.data','uses'=>'ResearchController@data']);
		Route::get('research/{id}/consent', ['as'=>'research.consent','uses'=>'ResearchController@consent']);
		Route::get('research/{id}/consent/{c_id}', ['as'=>'research.consent_edit','uses'=>'ResearchController@consent_edit']);
		Route::patch('research/{id}/consent/{c_id}', ['as'=>'research.consent_edit','uses'=>'ResearchController@consent_edit']);
		Route::delete('research/{id}/consent/{c_id}', ['as'=>'research.consent_edit','uses'=>'ResearchController@consent_edit']);
		Route::get('research/{id}/consent/{c_id}', ['as'=>'research.consent_edit','uses'=>'ResearchController@consent_edit']);
		Route::get('research/{id}/edit',	['as'=>'research.edit','uses'=>'ResearchController@edit']);
		Route::patch('research/{id}',		['as'=>'research.update','uses'=>'ResearchController@update']);
		Route::delete('research/{id}',		['as'=>'research.destroy','uses'=>'ResearchController@destroy']);
	}
);