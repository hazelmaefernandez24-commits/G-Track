<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class LogifyAutoSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('LogifyAutoSync: Starting automatic sync');
            
            // Run the import command
            $exitCode = Artisan::call('logify:import');
            
            if ($exitCode === 0) {
                Log::info('LogifyAutoSync: Sync completed successfully');
            } else {
                Log::warning('LogifyAutoSync: Sync completed with warnings');
            }
            
        } catch (\Exception $e) {
            Log::error('LogifyAutoSync: Sync failed', [
                'error' => $e->getMessage()
            ]);
        } finally {
            // Schedule the next sync in 60 seconds
            static::dispatch()->delay(now()->addSeconds(60));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('LogifyAutoSync: Job failed', [
            'error' => $exception->getMessage()
        ]);
        
        // Reschedule even if failed to ensure continuous sync
        static::dispatch()->delay(now()->addSeconds(60));
    }
}
