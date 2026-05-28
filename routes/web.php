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

Route::get('/', function () {
    return redirect()->away('https://app.beep.nl');
});

Route::get('home', function () {
    return redirect('dashboard');
});

Route::get('admin', function () {
    return redirect('dashboard');
});

Route::get('webapp', function () {
    return redirect()->away('https://app.beep.nl');
});

Route::get('code', 'SampleCodeController@code')->name('sample-code.code');
Route::post('codecheck', 'SampleCodeController@check')->name('sample-code.check')->middleware('throttle:3,1');
Route::patch('coderesult', 'SampleCodeController@resultsave')->name('sample-code.resultsave');

// Hack for redirecting e-mail reset password link to webapp
// Route::get('password/reset/{token}',['as'=>'password.reset', function ($token) {
//     return redirect(env('APP_URL').'/#!/login/reset/'.$token);
// }]);

// Secured by login
Route::prefix(LaravelLocalization::setLocale())->middleware('auth', 'localeSessionRedirect', 'localizationRedirect', 'verified')->group(
    function () {
        Route::middleware('role:superadmin|admin|lab')->group(
            function () {
                Route::get('code-upload', 'SampleCodeController@upload')->name('sample-code.upload');
                Route::post('code-upload-store', 'SampleCodeController@upload_store')->name('sample-code.upload-store');
            });

        Route::middleware('role:superadmin|admin|translator')->group(
            function () {

                // Routes
                Route::get('languages', 'LanguageController@index')->name('languages.index')->middleware('permission:language-list|language-create|language-edit|language-delete');
                Route::get('languages/create', 'LanguageController@create')->name('languages.create')->middleware('permission:language-create');
                Route::post('languages/create', 'LanguageController@store')->name('languages.store')->middleware('permission:language-create');
                Route::get('languages/{id}', 'LanguageController@show')->name('languages.show');
                Route::get('languages/{id}/edit', 'LanguageController@edit')->name('languages.edit')->middleware('permission:language-edit');
                Route::patch('languages/{id}', 'LanguageController@update')->name('languages.update')->middleware('permission:language-edit');
                Route::delete('languages/{id}', 'LanguageController@destroy')->name('languages.destroy')->middleware('permission:language-delete');

                Route::get('translations', 'TranslationController@index')->name('translations.index')->middleware('permission:translation-list');
                Route::get('translations/{language}', 'TranslationController@edit')->name('translations.edit')->middleware('permission:translation-create');
                Route::patch('translations/{language}', 'TranslationController@update')->name('translations.update')->middleware('permission:translation-edit');

            });

        Route::middleware('role:superadmin|admin|manager')->group(
            function () {

                Route::get('devices', 'DeviceController@index')->name('devices.index')->middleware('permission:sensor-list|sensor-create|sensor-edit|sensor-delete');
                Route::get('devices/create', 'DeviceController@create')->name('devices.create')->middleware('permission:sensor-create');
                Route::get('devices/data', 'DeviceController@data')->name('devices.data');
                Route::post('devices/create', 'DeviceController@store')->name('devices.store')->middleware('permission:sensor-create');
                Route::get('devices/{id}', 'DeviceController@show')->name('devices.show');
                Route::get('devices/{id}/sync', 'DeviceController@sync')->name('devices.sync');
                Route::get('devices/{id}/undelete', 'DeviceController@undelete')->name('devices.undelete');
                Route::get('devices/{id}/edit', 'DeviceController@edit')->name('devices.edit')->middleware('permission:sensor-edit');
                Route::get('devices/{id}/flashlog/{fl_id}', 'DeviceController@flashlog')->name('devices.flashlog')->middleware('permission:sensor-edit');
                Route::patch('devices/{id}', 'DeviceController@update')->name('devices.update')->middleware('permission:sensor-edit');
                Route::delete('devices/{id}', 'DeviceController@destroy')->name('devices.destroy')->middleware('permission:sensor-delete');
            });

        Route::middleware('role:superadmin|admin')->group(
            function () {
                Route::get('groups', 'GroupController@index')->name('groups.index')->middleware('permission:group-list|group-create|group-edit|group-delete');
                Route::get('groups/create', 'GroupController@create')->name('groups.create')->middleware('permission:group-create');
                Route::post('groups/create', 'GroupController@store')->name('groups.store')->middleware('permission:group-create');
                Route::get('groups/{id}', 'GroupController@show')->name('groups.show');
                Route::get('groups/{id}/edit', 'GroupController@edit')->name('groups.edit')->middleware('permission:group-edit');
                Route::patch('groups/{id}', 'GroupController@update')->name('groups.update')->middleware('permission:group-edit');
                Route::delete('groups/{id}', 'GroupController@destroy')->name('groups.destroy')->middleware('permission:group-delete');

                Route::resource('physicalquantity', 'PhysicalQuantityController');
                Route::resource('categoryinputs', 'CategoryInputsController');
                Route::resource('inspection-items', 'InspectionItemsController');
                Route::resource('measurement', 'MeasurementController');
                Route::resource('sensordefinition', 'SensorDefinitionController');
                Route::resource('flash-log', 'FlashLogController');
                Route::get('flash-log/parse/{id}', 'FlashLogController@parse')->name('flash-log.parse');

                // Create new research
                Route::get('research/create', 'ResearchController@create')->name('research.create');
                Route::post('research/create', 'ResearchController@store')->name('research.store');

                Route::resource('categories', 'CategoriesController');
                Route::delete('categories/{id}/pop', 'CategoriesController@pop')->name('categories.pop')->middleware('permission:taxonomy-delete');
                Route::get('categories/{id}/fix', 'CategoriesController@fix')->name('categories.fix');
                Route::get('categories/{id}/duplicate', 'CategoriesController@duplicate')->name('categories.duplicate');
                Route::get('taxonomy/display', 'TaxonomyController@display')->name('taxonomy.display');

                Route::resource('dashboard-group', 'DashboardGroupController');
            });

        Route::middleware('role:superadmin')->group(
            function () {
                Route::get('info', function () {
                    return view('phpinfo');
                });

                // Roles
                Route::get('roles', 'RoleController@index')->name('roles.index')->middleware('permission:role-list|role-create|role-edit|role-delete');
                Route::get('roles/create', 'RoleController@create')->name('roles.create')->middleware('permission:role-create');
                Route::post('roles/create', 'RoleController@store')->name('roles.store')->middleware('permission:role-create');
                Route::get('roles/{id}', 'RoleController@show')->name('roles.show');
                Route::get('roles/{id}/edit', 'RoleController@edit')->name('roles.edit')->middleware('permission:role-edit');
                Route::patch('roles/{id}', 'RoleController@update')->name('roles.update')->middleware('permission:role-edit');
                Route::delete('roles/{id}', 'RoleController@destroy')->name('roles.destroy')->middleware('permission:role-delete');
                Route::get('alert-rule/{id}/parse', 'AlertRuleController@parse')->name('alert-rule.parse');

                // Resource controllers
                Route::resource('permissions', 'PermissionController');
                Route::resource('image', 'ImageController');
                Route::resource('sample-code', 'SampleCodeController');
                Route::resource('alert', 'AlertController');
                Route::resource('alert-rule', 'AlertRuleController');
                Route::resource('alert-rule-formula', 'AlertRuleFormulaController');
                Route::resource('calculation-model', 'CalculationModelController');

                Route::get('calculation-model/{id}/run', 'CalculationModelController@run')->name('calculation-model.run');

                Route::delete('checklists/destroy/copies', 'ChecklistController@destroyCopies')->name('checklists.copies');

            });

        // Open research routes based on database access
        Route::get('dashboard', 'DashboardController@index')->name('dashboard.index');

        Route::resource('checklists', 'ChecklistController');
        Route::resource('inspections', 'InspectionsController');
        Route::resource('users', 'UserController');
        Route::resource('hive-tags', 'HiveTagsController');
        Route::resource('checklist-svg', 'ChecklistSvgController');

        Route::get('research', 'ResearchController@index')->name('research.index');
        Route::get('research/{id}', 'ResearchController@show')->name('research.show');
        Route::get('research/{id}/data', 'ResearchController@data')->name('research.data');
        Route::get('research/{id}/consent', 'ResearchController@consent')->name('research.consent');
        Route::get('research/{id}/consent/{c_id}', 'ResearchController@consent_edit')->name('research.consent_edit');
        Route::patch('research/{id}/consent/{c_id}', 'ResearchController@consent_edit')->name('research.consent_edit');
        Route::delete('research/{id}/consent/{c_id}', 'ResearchController@consent_edit')->name('research.consent_edit');
        Route::get('research/{id}/consent/{c_id}', 'ResearchController@consent_edit')->name('research.consent_edit');
        Route::get('research/{id}/edit', 'ResearchController@edit')->name('research.edit');
        Route::patch('research/{id}', 'ResearchController@update')->name('research.update');
        Route::delete('research/{id}', 'ResearchController@destroy')->name('research.destroy');
    }
);
