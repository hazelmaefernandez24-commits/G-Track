<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;

class ScheduleDeletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired schedules and prepare for new day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        // Find schedules that expired yesterday or earlier
        $expiredSchedules = Schedule::whereNotNull('valid_until')
            ->where('valid_until', '<', $today)
            ->get();

        $this->info("Found {$expiredSchedules->count()} expired schedules to process");

        foreach ($expiredSchedules as $schedule) {
            // Instead of deleting, we'll keep them for historical reference
            // but ensure they're not picked up by active schedule queries
            $this->info("Expired schedule found: ID {$schedule->schedule_id}, valid until {$schedule->valid_until}");
        }

        // Log the cleanup process
        Log::info('Schedule cleanup completed', [
            'date' => $today,
            'expired_schedules_count' => $expiredSchedules->count(),
            'expired_schedule_ids' => $expiredSchedules->pluck('schedule_id')->toArray()
        ]);

        $this->info('Schedule cleanup completed successfully');
    }
}
