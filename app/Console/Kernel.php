<?php

namespace App\Console;

use App\Models\AlertRule;
use App\Models\FlashLog;
use App\Weather;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(
            function () {
                Weather::updateLocations();
            }
        )->everyFiveMinutes();

        $schedule->call(
            function () {
                AlertRule::parseRules();
            }
        )->everyMinute();

        $schedule->call(
            function () {
                FlashLog::parseUnparsedFlashlogs();
            }
        )->everyFiveMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
