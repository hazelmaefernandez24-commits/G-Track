<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run Logify import every minute (60 seconds)
        $schedule->command('logify:import')
                 ->everyMinute()
                 ->withoutOverlapping(10) // Prevent overlapping for 10 minutes max
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/logify-sync.log'))
                 ->emailOutputOnFailure('admin@example.com'); // Optional: email on failure

        // Resolve expired consequences every minute for faster resolution
        $schedule->command('consequences:resolve-expired')
                 ->everyMinute()
                 ->withoutOverlapping(5)
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/consequence-resolution.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
