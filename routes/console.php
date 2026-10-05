<?php

use App\Models\AlertRule;
use App\Models\FlashLog;
use App\Weather;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(
    function () {
        Weather::updateLocations();
    }
)->everyFiveMinutes();

Schedule::call(
    function () {
        AlertRule::parseRules();
    }
)->everyMinute();

Schedule::call(
    function () {
        FlashLog::parseUnparsedFlashlogs();
    }
)->everyFiveMinutes();
