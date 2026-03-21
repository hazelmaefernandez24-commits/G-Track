<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * Now uses dynamic settings from General Settings instead of hardcoded values
     */
    protected function schedule(Schedule $schedule): void
    {
        // Get dynamic monthly reminder settings
        try {
            $monthlySettings = \App\Models\FinanceSetting::getMonthlyReminderSettings();

            // Only schedule if monthly reminders are enabled
            if ($monthlySettings['enabled']) {
                $reminderDay = $monthlySettings['day']; // Dynamic day (1-31)
                $reminderTime = $monthlySettings['time']; // Dynamic time (HH:MM)

                // Send monthly counterpart payment reminders on configured day and time
                $schedule->command('counterpart:remind-students')
                         ->monthlyOn($reminderDay, $reminderTime)
                         ->timezone('Asia/Manila')
                         ->description("Send monthly counterpart payment reminders to students (Day: {$reminderDay}, Time: {$reminderTime})");
            }

            // Schedule automatic payment reminders based on finance settings
            $paymentReminderSettings = \App\Models\FinanceSetting::getPaymentReminderSettings();
            if ($paymentReminderSettings['auto_enabled']) {
                // Run payment reminders daily at 9 AM to check for overdue students
                $schedule->command('payment:send-reminders')
                         ->dailyAt('09:00')
                         ->timezone('Asia/Manila')
                         ->description('Check and send payment reminders to overdue students');
            }

        } catch (\Exception $e) {
            // Fallback to default if settings can't be loaded
            \Log::warning('Could not load reminder settings, using defaults', [
                'error' => $e->getMessage()
            ]);

            $schedule->command('counterpart:remind-students')
                     ->monthlyOn(1, '09:00')
                     ->timezone('Asia/Manila')
                     ->description('Send monthly counterpart payment reminders to students (fallback)');
                     
            // Fallback payment reminders
            $schedule->command('payment:send-reminders')
                     ->dailyAt('09:00')
                     ->timezone('Asia/Manila')
                     ->description('Check and send payment reminders to overdue students (fallback)');
        }
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
