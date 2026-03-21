<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskViolationIntegrationService;

class SyncTaskViolations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:task-violations 
                            {--dry-run : Show what would be synced without actually syncing}
                            {--force : Force sync even if there are potential issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync invalid task submissions from G16_CAPSTONE to PN-ScholarSync violations';

    protected $integrationService;

    public function __construct(TaskViolationIntegrationService $integrationService)
    {
        parent::__construct();
        $this->integrationService = $integrationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Task Violation Sync...');
        $this->newLine();

        try {
            if ($this->option('dry-run')) {
                $this->info('🔍 DRY RUN MODE - No changes will be made');
                $this->runDryRun();
            } else {
                $this->runActualSync();
            }

        } catch (\Exception $e) {
            $this->error('❌ Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function runDryRun()
    {
        $this->info('📊 Analyzing invalid task submissions...');
        
        $invalidSubmissions = $this->integrationService->getInvalidSubmissionsWithStudentNames();
        
        if ($invalidSubmissions->count() === 0) {
            $this->info('✅ No invalid task submissions found to sync.');
            return;
        }

        $this->info("📋 Found {$invalidSubmissions->count()} invalid submissions:");
        $this->newLine();

        $table = $this->table(
            ['Student Name', 'Student ID', 'Task Category', 'Validated At'],
            $invalidSubmissions->take(20)->map(function($submission) {
                return [
                    $submission['student_name'],
                    $submission['student_id'] ?? 'N/A',
                    ucfirst($submission['task_category']),
                    $submission['validated_at'] ? 
                        \Carbon\Carbon::parse($submission['validated_at'])->format('M d, Y H:i') : 
                        'N/A'
                ];
            })->toArray()
        );

        if ($invalidSubmissions->count() > 20) {
            $this->info("... and " . ($invalidSubmissions->count() - 20) . " more submissions");
        }

        $this->newLine();
        $this->info("💡 These would be converted to 'Center Tasking' violations with 'Low' severity and 'Verbal Warning' penalty.");
        $this->info("🚀 Run without --dry-run to perform the actual sync.");
    }

    private function runActualSync()
    {
        $this->info('🔄 Performing actual sync...');
        
        $result = $this->integrationService->syncInvalidTaskSubmissions();

        if ($result['success']) {
            $this->info("✅ Sync completed successfully!");
            $this->info("📊 Results:");
            $this->info("   • Found: {$result['total_found']} invalid submissions");
            $this->info("   • Synced: {$result['synced_count']} new violations created");
            
            if (!empty($result['errors'])) {
                $this->warn("⚠️  Errors encountered: " . count($result['errors']));
                foreach ($result['errors'] as $error) {
                    $this->warn("   • {$error}");
                }
            }

            if ($result['synced_count'] > 0) {
                $this->info("🎯 New violations are now visible in the PN-ScholarSync violations table.");
            }

        } else {
            $this->error("❌ Sync failed: {$result['error']}");
        }
    }
}
