<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Clean up expired schedules (but don't delete, just mark as inactive)
        $schedule->command('schedule:cleanup')->dailyAt('00:01');

        // Clean up expired individual going-out schedules (delete them)
        $schedule->command('schedule:cleanup-expired-individual')->dailyAt('00:02');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
