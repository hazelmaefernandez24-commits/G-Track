<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupExpiredIndividualSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:cleanup-expired-individual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired individual going-out schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            
            // Find expired individual going-out schedules
            $expiredSchedules = Schedule::whereNotNull('student_id') // Individual schedules
                ->whereNotNull('valid_until') // Has expiry date
                ->where('valid_until', '<', $now) // Expired
                ->get();

            $deletedCount = 0;

            foreach ($expiredSchedules as $schedule) {
                Log::info('Deleting expired individual going-out schedule', [
                    'schedule_id' => $schedule->id,
                    'student_id' => $schedule->student_id,
                    'day_of_week' => $schedule->day_of_week,
                    'valid_until' => $schedule->valid_until,
                    'expired_at' => $now
                ]);

                $schedule->delete();
                $deletedCount++;
            }

            $this->info("Cleaned up {$deletedCount} expired individual going-out schedules.");
            
            Log::info('Individual going-out schedule cleanup completed', [
                'deleted_count' => $deletedCount,
                'executed_at' => $now
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during schedule cleanup: ' . $e->getMessage());
            
            Log::error('Error during individual going-out schedule cleanup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'executed_at' => Carbon::now()
            ]);

            return 1;
        }
    }
}
