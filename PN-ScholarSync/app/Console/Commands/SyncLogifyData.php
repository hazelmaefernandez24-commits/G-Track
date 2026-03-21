<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LogifyIntegrationController;
use App\Services\LogifyApiService;
use Illuminate\Support\Facades\Log;

class SyncLogifyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:sync 
                            {--force : Force sync even if no recent updates}
                            {--test : Test connection only}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync late and absent student data from Logify and create violations';

    protected $logifyService;
    protected $integrationController;

    /**
     * Create a new command instance.
     */
    public function __construct(LogifyApiService $logifyService)
    {
        parent::__construct();
        $this->logifyService = $logifyService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Logify data synchronization...');

        // Test connection first
        if ($this->option('test')) {
            return $this->testConnection();
        }

        try {
            // Initialize the integration controller
            $this->integrationController = app(LogifyIntegrationController::class);

            // Check if we should force sync
            $forceSync = $this->option('force');

            if (!$forceSync) {
                // Check if there are recent updates
                $this->info('📡 Checking for recent updates in Logify...');
                
                if (!$this->logifyService->hasRecentUpdates()) {
                    $this->info('✅ No recent updates found. Sync not needed.');
                    return Command::SUCCESS;
                }
                
                $this->info('🆕 Recent updates detected. Starting sync...');
            } else {
                $this->warn('⚠️  Force sync enabled. Syncing regardless of updates...');
            }

            // Perform the sync
            $result = $this->integrationController->syncLogifyData();

            if ($result['success']) {
                $this->displaySyncResults($result['data']);
                $this->info('✅ Logify sync completed successfully!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Sync failed: ' . $result['message']);
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('💥 Sync failed with exception: ' . $e->getMessage());
            
            if ($this->option('detailed')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }

            Log::error('LogifySync Command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Test connection to Logify API
     */
    protected function testConnection()
    {
        $this->info('🔍 Testing connection to Logify API...');

        // Check if API is enabled
        if (!$this->logifyService->isApiEnabled()) {
            $this->warn('⚠️  Logify API integration is DISABLED');
            $this->info('💡 The system is configured to use DATABASE IMPORT instead');
            $this->info('🔧 Use "php artisan logify:import" for database integration');
            return Command::SUCCESS;
        }

        try {
            if ($this->logifyService->testConnection()) {
                $this->info('✅ Connection to Logify API successful!');

                // Show some basic stats
                $this->info('📊 Getting sync status...');
                $integrationController = app(LogifyIntegrationController::class);
                $statusResponse = $integrationController->getSyncStatus();

                if ($statusResponse->getStatusCode() === 200) {
                    $data = json_decode($statusResponse->getContent(), true)['data'];
                    $this->displaySyncStatus($data);
                }

                return Command::SUCCESS;
            } else {
                $this->error('❌ Connection to Logify API failed!');
                $this->info('💡 Consider using database import: php artisan logify:import');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('💥 Connection test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display sync results
     */
    protected function displaySyncResults($data)
    {
        $this->info('📈 Sync Results:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Late Students Processed', $data['late_students_processed']],
                ['Absent Students Processed', $data['absent_students_processed']],
                ['Violations Created', $data['violations_created']],
                ['Errors', count($data['errors'])]
            ]
        );

        if (!empty($data['errors']) && $this->option('detailed')) {
            $this->warn('⚠️  Errors encountered:');
            foreach ($data['errors'] as $error) {
                $this->error('  • ' . $error);
            }
        }
    }

    /**
     * Display sync status
     */
    protected function displaySyncStatus($data)
    {
        $this->info('📊 Current Status:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Last Sync', $data['last_sync'] ?? 'Never'],
                ['Connection Status', $data['connection_status'] ? '✅ Connected' : '❌ Disconnected'],
                ['Total Late Records', $data['late_records_count']],
                ['Total Absent Records', $data['absent_records_count']],
                ['Recent Late Records (7 days)', $data['recent_late_records']],
                ['Recent Absent Records (7 days)', $data['recent_absent_records']]
            ]
        );
    }
}
