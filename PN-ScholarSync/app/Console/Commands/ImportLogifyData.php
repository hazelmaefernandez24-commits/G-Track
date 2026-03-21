<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LogifyIntegrationController;
use App\Services\LogifyDataImportService;
use Illuminate\Support\Facades\Log;

class ImportLogifyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:import 
                            {--force : Force import even if no recent updates}
                            {--test : Test database connection only}
                            {--detailed : Show detailed output}
                            {--month= : Specific month to import (MM format)}
                            {--year= : Specific year to import (YYYY format)}
                            {--batch= : Specific batch to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import late and absent student data directly from Logify database and create violations';

    protected $importService;
    protected $integrationController;

    /**
     * Create a new command instance.
     */
    public function __construct(LogifyDataImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Logify data import...');

        // Test connection first
        if ($this->option('test')) {
            return $this->testConnection();
        }

        try {
            // Initialize the integration controller
            $this->integrationController = app(LogifyIntegrationController::class);

            // Get import parameters
            $month = $this->option('month') ?: now()->format('m');
            $year = $this->option('year') ?: now()->format('Y');
            $batch = $this->option('batch');
            $forceImport = $this->option('force');

            $this->info("📅 Import parameters: Month={$month}, Year={$year}" . ($batch ? ", Batch={$batch}" : ""));

            if (!$forceImport) {
                // Check if there are recent updates
                $this->info('📡 Checking for recent updates in Logify database...');
                
                if (!$this->importService->hasRecentUpdates()) {
                    $this->info('✅ No recent updates found. Import not needed.');
                    return Command::SUCCESS;
                }
                
                $this->info('🆕 Recent updates detected. Starting import...');
            } else {
                $this->warn('⚠️  Force import enabled. Importing regardless of updates...');
            }

            // Perform the import
            $result = $this->performImport($month, $year, $batch);

            if ($result['success']) {
                $this->displayImportResults($result['data']);
                $this->info('✅ Logify import completed successfully!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Import failed: ' . $result['message']);
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('💥 Import failed with exception: ' . $e->getMessage());
            
            if ($this->option('detailed')) {
                $this->error('Stack trace:');
                $this->error($e->getTraceAsString());
            }

            Log::error('LogifyImport Command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Test the database connection
     */
    protected function testConnection()
    {
        $this->info('🔍 Testing connection to Logify database...');

        try {
            if ($this->importService->testConnection()) {
                $this->info('✅ Connection to Logify database successful!');
                
                // Show some basic info about what we can access
                $this->info('📊 Testing data access...');
                
                $lateData = $this->importService->getLateStudents();
                $absentData = $this->importService->getAbsentStudents();
                
                $this->displayTestResults($lateData, $absentData);
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Connection to Logify database failed!');
                $this->error('💡 Please check your Logify database configuration in .env file');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('💥 Connection test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Perform the actual data import
     */
    protected function performImport($month, $year, $batch)
    {
        try {
            Log::info('LogifyImport: Starting import process', [
                'month' => $month,
                'year' => $year,
                'batch' => $batch
            ]);

            $syncBatchId = uniqid('import_', true);
            $violationsCreated = 0;
            $errors = [];

            // Import late students
            $this->info('📥 Importing late students data...');
            $lateData = $this->importService->getLateStudents($month, $year, $batch);
            if ($lateData) {
                $result = $this->integrationController->processLateStudents($lateData, $syncBatchId);
                $violationsCreated += $result['violations_created'];
                $errors = array_merge($errors, $result['errors']);
                $this->info("   ✓ Processed {$lateData['total_count']} late student records");
            } else {
                $errors[] = 'Failed to fetch late students data from Logify database';
                $this->warn('   ⚠ Failed to fetch late students data');
            }

            // Import absent students
            $this->info('📥 Importing absent students data...');
            $absentData = $this->importService->getAbsentStudents($month, $year, $batch);
            if ($absentData) {
                $result = $this->integrationController->processAbsentStudents($absentData, $syncBatchId);
                $violationsCreated += $result['violations_created'];
                $errors = array_merge($errors, $result['errors']);
                $this->info("   ✓ Processed {$absentData['total_count']} absent student records");
            } else {
                $errors[] = 'Failed to fetch absent students data from Logify database';
                $this->warn('   ⚠ Failed to fetch absent students data');
            }

            Log::info('LogifyImport: Import completed', [
                'violations_created' => $violationsCreated,
                'errors_count' => count($errors),
                'sync_batch_id' => $syncBatchId
            ]);

            return [
                'success' => true,
                'data' => [
                    'late_students_processed' => count($lateData['late_students'] ?? []),
                    'absent_students_processed' => count($absentData['absent_students'] ?? []),
                    'violations_created' => $violationsCreated,
                    'errors' => $errors,
                    'sync_batch_id' => $syncBatchId
                ]
            ];

        } catch (\Exception $e) {
            Log::error('LogifyImport: Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Display test results
     */
    protected function displayTestResults($lateData, $absentData)
    {
        $this->info('📊 Test Results:');
        $this->table(
            ['Data Type', 'Records Found'],
            [
                ['Late Students', $lateData ? $lateData['total_count'] : 'Failed to fetch'],
                ['Absent Students', $absentData ? $absentData['total_count'] : 'Failed to fetch']
            ]
        );

        if ($lateData && $this->option('detailed')) {
            $this->info('📋 Sample Late Students:');
            foreach (array_slice($lateData['late_students'], 0, 3) as $student) {
                $this->line("   • {$student['first_name']} {$student['last_name']} ({$student['student_id']}) - {$student['total_late_count']} late");
            }
        }
    }

    /**
     * Display import results
     */
    protected function displayImportResults($data)
    {
        $this->info('📈 Import Results:');
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

        if ($data['violations_created'] > 0) {
            $this->info("🎯 Successfully created {$data['violations_created']} violations from imported data!");
        }
    }
}
