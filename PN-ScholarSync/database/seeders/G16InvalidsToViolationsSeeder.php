<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Services\TaskViolationIntegrationService;

class G16InvalidsToViolationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var TaskViolationIntegrationService $service */
        $service = app(TaskViolationIntegrationService::class);

        $this->command?->info('Seeding: Sync invalid task submissions from G16_CAPSTONE into PN-ScholarSync violations...');

        try {
            $result = $service->syncInvalidTaskSubmissions();

            if (($result['success'] ?? false) === true) {
                $synced = $result['synced_count'] ?? 0;
                $found = $result['total_found'] ?? 0;
                $this->command?->info("Done. Found: {$found}, Synced: {$synced}");

                $errors = $result['errors'] ?? [];
                if (!empty($errors)) {
                    foreach ($errors as $err) {
                        $this->command?->warn(" - {$err}");
                    }
                }
            } else {
                $error = $result['error'] ?? 'Unknown error';
                $this->command?->error("Failed: {$error}");
                Log::error('[G16InvalidsToViolationsSeeder] ' . $error);
            }
        } catch (\Throwable $e) {
            $this->command?->error('Seeder error: ' . $e->getMessage());
            Log::error('[G16InvalidsToViolationsSeeder] Exception: ' . $e->getMessage());
        }
    }
}
