<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AlertRuleFormulaController;
use App\Http\Controllers\CategoryInputsController;
use App\Http\Controllers\ChecklistSvgController;
use App\Http\Controllers\DashboardGroupController;
use App\Http\Controllers\HiveTagsController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InspectionItemsController;
use App\Http\Controllers\InspectionsController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PhysicalQuantityController;
use App\Http\Controllers\SensorDefinitionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\CalculationModelController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FlashLogController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SampleCodeController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

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

Route::get('code', [SampleCodeController::class, 'code'])->name('sample-code.code');
Route::post('codecheck', [SampleCodeController::class, 'check'])->name('sample-code.check')->middleware('throttle:3,1');
Route::patch('coderesult', [SampleCodeController::class, 'resultsave'])->name('sample-code.resultsave');

// Hack for redirecting e-mail reset password link to webapp
// Route::get('password/reset/{token}',['as'=>'password.reset', function ($token) {
//     return redirect(env('APP_URL').'/#!/login/reset/'.$token);
// }]);

// Secured by login
Route::prefix(LaravelLocalization::setLocale())->middleware('auth', 'localeSessionRedirect', 'localizationRedirect', 'verified')->group(
    function () {
        Route::middleware('role:superadmin|admin|lab')->group(
            function () {
                Route::get('code-upload', [SampleCodeController::class, 'upload'])->name('sample-code.upload');
                Route::post('code-upload-store', [SampleCodeController::class, 'upload_store'])->name('sample-code.upload-store');
            });

        Route::middleware('role:superadmin|admin|translator')->group(
            function () {

                // Routes
                Route::get('languages', [LanguageController::class, 'index'])->name('languages.index')->middleware('permission:language-list|language-create|language-edit|language-delete');
                Route::get('languages/create', [LanguageController::class, 'create'])->name('languages.create')->middleware('permission:language-create');
                Route::post('languages/create', [LanguageController::class, 'store'])->name('languages.store')->middleware('permission:language-create');
                Route::get('languages/{id}', [LanguageController::class, 'show'])->name('languages.show');
                Route::get('languages/{id}/edit', [LanguageController::class, 'edit'])->name('languages.edit')->middleware('permission:language-edit');
                Route::patch('languages/{id}', [LanguageController::class, 'update'])->name('languages.update')->middleware('permission:language-edit');
                Route::delete('languages/{id}', [LanguageController::class, 'destroy'])->name('languages.destroy')->middleware('permission:language-delete');

                Route::get('translations', [TranslationController::class, 'index'])->name('translations.index')->middleware('permission:translation-list');
                Route::get('translations/{language}', [TranslationController::class, 'edit'])->name('translations.edit')->middleware('permission:translation-create');
                Route::patch('translations/{language}', [TranslationController::class, 'update'])->name('translations.update')->middleware('permission:translation-edit');

            });

        Route::middleware('role:superadmin|admin|manager')->group(
            function () {

                Route::get('devices', [DeviceController::class, 'index'])->name('devices.index')->middleware('permission:sensor-list|sensor-create|sensor-edit|sensor-delete');
                Route::get('devices/create', [DeviceController::class, 'create'])->name('devices.create')->middleware('permission:sensor-create');
                Route::get('devices/data', [DeviceController::class, 'data'])->name('devices.data');
                Route::post('devices/create', [DeviceController::class, 'store'])->name('devices.store')->middleware('permission:sensor-create');
                Route::get('devices/{id}', [DeviceController::class, 'show'])->name('devices.show');
                Route::get('devices/{id}/sync', [DeviceController::class, 'sync'])->name('devices.sync');
                Route::get('devices/{id}/undelete', [DeviceController::class, 'undelete'])->name('devices.undelete');
                Route::get('devices/{id}/edit', [DeviceController::class, 'edit'])->name('devices.edit')->middleware('permission:sensor-edit');
                Route::get('devices/{id}/flashlog/{fl_id}', [DeviceController::class, 'flashlog'])->name('devices.flashlog')->middleware('permission:sensor-edit');
                Route::patch('devices/{id}', [DeviceController::class, 'update'])->name('devices.update')->middleware('permission:sensor-edit');
                Route::delete('devices/{id}', [DeviceController::class, 'destroy'])->name('devices.destroy')->middleware('permission:sensor-delete');
            });

        Route::middleware('role:superadmin|admin')->group(
            function () {
                Route::get('groups', [GroupController::class, 'index'])->name('groups.index')->middleware('permission:group-list|group-create|group-edit|group-delete');
                Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create')->middleware('permission:group-create');
                Route::post('groups/create', [GroupController::class, 'store'])->name('groups.store')->middleware('permission:group-create');
                Route::get('groups/{id}', [GroupController::class, 'show'])->name('groups.show');
                Route::get('groups/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit')->middleware('permission:group-edit');
                Route::patch('groups/{id}', [GroupController::class, 'update'])->name('groups.update')->middleware('permission:group-edit');
                Route::delete('groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy')->middleware('permission:group-delete');

                Route::resource('physicalquantity', PhysicalQuantityController::class);
                Route::resource('categoryinputs', CategoryInputsController::class);
                Route::resource('inspection-items', InspectionItemsController::class);
                Route::resource('measurement', MeasurementController::class);
                Route::resource('sensordefinition', SensorDefinitionController::class);
                Route::resource('flash-log', FlashLogController::class);
                Route::get('flash-log/parse/{id}', [FlashLogController::class, 'parse'])->name('flash-log.parse');

                // Create new research
                Route::get('research/create', [ResearchController::class, 'create'])->name('research.create');
                Route::post('research/create', [ResearchController::class, 'store'])->name('research.store');

                Route::resource('categories', CategoriesController::class);
                Route::delete('categories/{id}/pop', [CategoriesController::class, 'pop'])->name('categories.pop')->middleware('permission:taxonomy-delete');
                Route::get('categories/{id}/fix', [CategoriesController::class, 'fix'])->name('categories.fix');
                Route::get('categories/{id}/duplicate', [CategoriesController::class, 'duplicate'])->name('categories.duplicate');
                Route::get('taxonomy/display', [TaxonomyController::class, 'display'])->name('taxonomy.display');

                Route::resource('dashboard-group', DashboardGroupController::class);
            });

        Route::middleware('role:superadmin')->group(
            function () {
                Route::get('info', function () {
                    return view('phpinfo');
                });

                // Roles
                Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:role-list|role-create|role-edit|role-delete');
                Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:role-create');
                Route::post('roles/create', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:role-create');
                Route::get('roles/{id}', [RoleController::class, 'show'])->name('roles.show');
                Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:role-edit');
                Route::patch('roles/{id}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:role-edit');
                Route::delete('roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:role-delete');
                Route::get('alert-rule/{id}/parse', [AlertRuleController::class, 'parse'])->name('alert-rule.parse');

                // Resource controllers
                Route::resource('permissions', PermissionController::class);
                Route::resource('image', ImageController::class);
                Route::resource('sample-code', SampleCodeController::class);
                Route::resource('alert', AlertController::class);
                Route::resource('alert-rule', AlertRuleController::class);
                Route::resource('alert-rule-formula', AlertRuleFormulaController::class);
                Route::resource('calculation-model', CalculationModelController::class);

                Route::get('calculation-model/{id}/run', [CalculationModelController::class, 'run'])->name('calculation-model.run');

                Route::delete('checklists/destroy/copies', [ChecklistController::class, 'destroyCopies'])->name('checklists.copies');

            });

        // Open research routes based on database access
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        Route::resource('checklists', ChecklistController::class);
        Route::resource('inspections', InspectionsController::class);
        Route::resource('users', UserController::class);
        Route::resource('hive-tags', HiveTagsController::class);
        Route::resource('checklist-svg', ChecklistSvgController::class);

        Route::get('research', [ResearchController::class, 'index'])->name('research.index');
        Route::get('research/{id}', [ResearchController::class, 'show'])->name('research.show');
        Route::get('research/{id}/data', [ResearchController::class, 'data'])->name('research.data');
        Route::get('research/{id}/consent', [ResearchController::class, 'consent'])->name('research.consent');
        Route::get('research/{id}/consent/{c_id}', [ResearchController::class, 'consent_edit'])->name('research.consent_edit');
        Route::patch('research/{id}/consent/{c_id}', [ResearchController::class, 'consent_edit'])->name('research.consent_edit');
        Route::delete('research/{id}/consent/{c_id}', [ResearchController::class, 'consent_edit'])->name('research.consent_edit');
        Route::get('research/{id}/consent/{c_id}', [ResearchController::class, 'consent_edit'])->name('research.consent_edit');
        Route::get('research/{id}/edit', [ResearchController::class, 'edit'])->name('research.edit');
        Route::patch('research/{id}', [ResearchController::class, 'update'])->name('research.update');
        Route::delete('research/{id}', [ResearchController::class, 'destroy'])->name('research.destroy');
    }
);
