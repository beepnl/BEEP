<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect('webapp');
});

// save sensor data of multiple sensors
Route::post('sensors', [Api\MeasurementController::class, 'storeMeasurementData'])->middleware('throttle:10,1,sensors');
Route::post('lora_sensors', [Api\MeasurementController::class, 'lora_sensors'])->middleware('throttle:10,1,lora_sensors');
Route::get('sensors/measurement_types', [Api\MeasurementController::class, 'sensor_measurement_types'])->middleware('throttle:3,1,measurement_types');

// save sensor data of multiple sensors (unsecure)
// Route::post('unsecure_sensors', 'Api\MeasurementController@storeMeasurementData')->middleware('throttle:1,1');

// User functions
Route::post('register', [Api\UserController::class, 'register'])->middleware('throttle:6,1,register');
Route::post('login', [Api\UserController::class, 'login'])->middleware('throttle:20,1,login');
Route::post('user/reminder', [Api\UserController::class, 'reminder'])->middleware('throttle:3,1,user_reminder');
Route::post('user/reset', [Api\UserController::class, 'reset'])->middleware('throttle:3,1,user_reset');

// // Email Verification Routes...
Route::get('email/verify/{id}', [Api\Auth\VerificationController::class, 'verify'])->name('apiverification.verify')->middleware('throttle:6,1,verify');
Route::post('email/resend', [Api\Auth\VerificationController::class, 'resend'])->name('apiverification.resend')->middleware('throttle:3,1,resend');

Route::post('groups/checktoken', [Api\GroupController::class, 'checktoken'])->middleware('throttle:6,1,checktoken');

// Public Dashboard
Route::get('dashboard/{code}', [Api\DashboardGroupController::class, 'public'])->middleware('throttle:60,1,publicdashboard');

// high traffic routes
Route::middleware('auth:api', 'verifiedApi', 'throttle:global_rate_limit_per_min_sensors,1,sensor_traffic')->group(function () {
    Route::post('sensors_auth', [Api\MeasurementController::class, 'storeMeasurementData']);
    Route::post('lora_sensors_auth', [Api\MeasurementController::class, 'lora_sensors']);
});

// normal traffic routes
Route::middleware('auth:api', 'verifiedApi', 'throttle:global_rate_limit_per_min,1,normal_traffic')->group(function () {
    // Route::post('devices/tts/{step}/{dev_id}/{dev_eui}/{app_key}', 'Api\DeviceController@debugTtsDevice');

    // Authenticate and provide the token
    Route::post('authenticate', [Api\UserController::class, 'authenticate']);

    // get more data of 1 sensor (Device)
    Route::post('devices/multiple', [Api\DeviceController::class, 'storeMultiple']);
    Route::get('devices/ttn/{dev_id}', [Api\DeviceController::class, 'getTTNDevice']);
    Route::post('devices/ttn/{dev_id}', [Api\DeviceController::class, 'postTTNDevice']);

    Route::get('sensors/measurements', [Api\MeasurementController::class, 'data']);
    Route::get('sensors/comparemeasurements', [Api\MeasurementController::class, 'comparedata']);
    Route::get('sensors/cleanedweight', [Api\MeasurementController::class, 'cleanedweight']);
    Route::get('sensors/lastvalues', [Api\MeasurementController::class, 'lastvalues']);
    Route::get('sensors/lastweight', [Api\MeasurementController::class, 'lastweight']);
    Route::post('sensors/calibrateweight', [Api\MeasurementController::class, 'calibrateweight']);
    Route::post('sensors/offsetweight', [Api\MeasurementController::class, 'offsetweight']);
    Route::get('sensors/measurement_types_available', [Api\MeasurementController::class, 'sensor_measurement_types_available']);
    Route::post('sensors/flashlog', [Api\MeasurementController::class, 'flashlog']);
    Route::get('sensors/decode/p/{port}/pl/{payload}', [Api\MeasurementController::class, 'decode_beep_lora_payload']);

    Route::post('settings', [Api\SettingController::class, 'store']);
    Route::get('settings', [Api\SettingController::class, 'index']);

    Route::get('taxonomy/lists', [Api\TaxonomyController::class, 'lists']);
    Route::get('taxonomy/taxonomy', [Api\TaxonomyController::class, 'taxonomy']);

    Route::get('inspections', [Api\InspectionsController::class, 'index']);
    Route::get('inspections/lists', [Api\InspectionsController::class, 'lists']);
    Route::get('inspections/{id}', [Api\InspectionsController::class, 'show']);
    Route::get('inspections/hive/{hive_id}', [Api\InspectionsController::class, 'hive']);
    Route::post('inspections/store', [Api\InspectionsController::class, 'store']);
    Route::delete('inspections/{id}', [Api\InspectionsController::class, 'destroy']);

    Route::get('research', [Api\ResearchController::class, 'index']);
    Route::post('research/{id}/add_consent', [Api\ResearchController::class, 'add_consent']);
    Route::post('research/{id}/remove_consent', [Api\ResearchController::class, 'remove_consent']);
    Route::patch('research/{id}/edit/{consent_id}', [Api\ResearchController::class, 'edit_consent']);
    Route::delete('research/{id}/delete/{consent_id}', [Api\ResearchController::class, 'delete_no_consent']);

    Route::get('researchdata', [Api\ResearchDataController::class, 'index']);
    Route::get('researchdata/{id}', [Api\ResearchDataController::class, 'show']);
    Route::get('researchdata/{id}/data/{item}', [Api\ResearchDataController::class, 'research_data']);
    Route::get('researchdata/{id}/user/{user_id}/{item}', [Api\ResearchDataController::class, 'user_data']);

    Route::delete('user', [Api\UserController::class, 'destroy']);
    Route::patch('user', [Api\UserController::class, 'edit']);
    Route::patch('userlocale', [Api\UserController::class, 'userlocale']);

    // Device specific routes (must be before resource route)
    Route::post('devices/clocksync', [Api\DeviceController::class, 'clocksync']);
    Route::post('devices/lora_reset', [Api\DeviceController::class, 'lora_reset']);
    Route::post('devices/interval', [Api\DeviceController::class, 'interval']);

    // Control resources
    Route::resource('devices', Api\DeviceController::class)->except('create', 'edit');
    Route::resource('checklists', Api\ChecklistController::class)->except('create', 'edit');
    Route::resource('categories', Api\CategoryController::class)->except('create', 'edit', 'store', 'update', 'destroy');
    Route::resource('groups', Api\GroupController::class)->except('create', 'edit');
    Route::resource('hives', Api\HiveController::class)->except('create', 'edit');
    Route::resource('hive-tags', Api\HiveTagsController::class)->except('create', 'edit');
    Route::resource('locations', Api\LocationController::class)->except('create', 'edit');
    Route::resource('queens', Api\QueenController::class)->except('create', 'edit');
    Route::resource('images', Api\ImageController::class)->except('create', 'edit', 'destroy');
    Route::resource('sensordefinition', Api\SensorDefinitionController::class)->except('create', 'edit');
    Route::resource('samplecode', Api\SampleCodeController::class)->except('create', 'edit', 'destroy');
    Route::resource('alerts', Api\AlertController::class)->except('create', 'edit');
    Route::resource('alert-rules', Api\AlertRuleController::class)->except('create', 'edit');
    Route::resource('dashboardgroups', Api\DashboardGroupController::class)->except('create', 'edit');
    Route::resource('checklist-svg', Api\ChecklistSvgController::class)->except('create', 'edit');

    Route::get('alert-rules-default', [Api\AlertRuleController::class, 'default_rules']);

    Route::delete('samplecode', [Api\SampleCodeController::class, 'destroy']);
    Route::delete('images', [Api\ImageController::class, 'destroyByUrl']);
    Route::delete('groups/detach/{id}', [Api\GroupController::class, 'detach']);

    Route::get('categoryinputs', [Api\CategoryController::class, 'inputs']);
    Route::post('export/csv', [Api\ExportController::class, 'generate_csv']);

    Route::get('flashlogs', [Api\FlashLogController::class, 'index']);
    Route::get('flashlogs/{id}', [Api\FlashLogController::class, 'show']);
    Route::post('flashlogs/{id}', [Api\FlashLogController::class, 'persist']);
    Route::delete('flashlogs/{id}', [Api\FlashLogController::class, 'delete']);
});

// low traffic routes
Route::middleware('auth:api', 'verifiedApi', 'throttle:3,1,low_traffic')->group(function () {
    Route::get('export', [Api\ExportController::class, 'all']);
    Route::get('weather', [Api\WeatherController::class, 'index']);
});

