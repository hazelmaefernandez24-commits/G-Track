<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogifyTriggerService;
use Illuminate\Support\Facades\DB;

class TestLogifyTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:test-triggers 
                            {--detailed : Show detailed test results}
                            {--simulate : Simulate trigger execution without actual database changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Logify database triggers integration';

    protected $triggerService;

    /**
     * Create a new command instance.
     */
    public function __construct(LogifyTriggerService $triggerService)
    {
        parent::__construct();
        $this->triggerService = $triggerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Logify Database Triggers Integration');
        $this->newLine();

        $detailed = $this->option('detailed');
        $simulate = $this->option('simulate');

        if ($simulate) {
            $this->warn('SIMULATION MODE - No actual database changes will be made');
            $this->newLine();
        }

        // Test 1: Configuration Check
        $this->testConfiguration($detailed);

        // Test 2: Database Connection
        $this->testDatabaseConnection($detailed);

        // Test 3: Trigger Installation
        $this->testTriggerInstallation($detailed);

        // Test 4: ScholarSync Tables
        $this->testScholarSyncTables($detailed);

        // Test 5: Violation Types
        $this->testViolationTypes($detailed);

        // Test 6: Trigger Health
        $this->testTriggerHealth($detailed);

        // Test 7: Performance Monitoring
        $this->testPerformanceMonitoring($detailed);

        // Test 8: Simulate Trigger Execution (if not in simulate mode)
        if (!$simulate) {
            $this->testTriggerExecution($detailed);
        } else {
            $this->info('Skipping trigger execution test (simulation mode)');
        }

        $this->newLine();
        $this->info('✅ Trigger testing completed!');
    }

    /**
     * Test configuration
     */
    protected function testConfiguration($detailed = false)
    {
        $this->info('1. Testing Configuration...');
        
        $config = $this->triggerService->getConfigurationSummary();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Triggers Enabled', $config['triggers_enabled'] ? '✅ Yes' : '❌ No'],
                ['Auto Create Violations', $config['auto_create_violations'] ? '✅ Yes' : '❌ No'],
                ['Update Existing Records', $config['update_existing_records'] ? '✅ Yes' : '❌ No'],
                ['Process Deleted Records', $config['process_deleted_records'] ? '✅ Yes' : '❌ No'],
                ['Log Trigger Executions', $config['log_trigger_executions'] ? '✅ Yes' : '❌ No'],
                ['Fallback to Import', $config['fallback_to_import'] ? '✅ Yes' : '❌ No'],
                ['Database Host', $config['database_host']],
                ['Database Name', $config['database_name']],
            ]
        );

        if ($detailed) {
            $this->line('Configuration details:');
            foreach ($config as $key => $value) {
                $this->line("  {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
            }
        }

        $this->newLine();
    }

    /**
     * Test database connection
     */
    protected function testDatabaseConnection($detailed = false)
    {
        $this->info('2. Testing Database Connection...');
        
        try {
            // Test Logify connection
            DB::connection('logify')->getPdo();
            $this->line('✅ Logify database connection: OK');
            
            // Test ScholarSync connection
            DB::connection()->getPdo();
            $this->line('✅ ScholarSync database connection: OK');
            
            if ($detailed) {
                $logifyConfig = config('database.connections.logify');
                $this->line('Logify connection details:');
                $this->line("  Host: {$logifyConfig['host']}");
                $this->line("  Database: {$logifyConfig['database']}");
                $this->line("  Username: {$logifyConfig['username']}");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Test trigger installation
     */
    protected function testTriggerInstallation($detailed = false)
    {
        $this->info('3. Testing Trigger Installation...');
        
        $testResults = $this->triggerService->testTriggers();
        
        $this->table(
            ['Component', 'Status'],
            [
                ['Triggers Exist', $testResults['triggers_exist'] ? '✅ Yes' : '❌ No'],
                ['ScholarSync Tables', $testResults['all_tables_exist'] ? '✅ Yes' : '❌ No'],
                ['Violation Types', $testResults['violation_types_exist'] ? '✅ Yes' : '❌ No'],
                ['Overall Status', $testResults['overall_status']],
            ]
        );

        if ($detailed) {
            if (isset($testResults['procedures_found'])) {
                $this->line('Found procedures:');
                foreach ($testResults['procedures_found'] as $procedure) {
                    $this->line("  ✅ {$procedure}");
                }
            }

            if (isset($testResults['triggers_found'])) {
                $this->line('Found triggers:');
                foreach ($testResults['triggers_found'] as $trigger) {
                    $status = $trigger['status'] === 'ENABLED' ? '✅' : '❌';
                    $this->line("  {$status} {$trigger['name']} ({$trigger['status']})");
                }
            }

            if (isset($testResults['violation_types_found'])) {
                $this->line('Found violation types:');
                foreach ($testResults['violation_types_found'] as $type) {
                    $this->line("  ✅ {$type}");
                }
            }
        }

        $this->newLine();
    }

    /**
     * Test ScholarSync tables
     */
    protected function testScholarSyncTables($detailed = false)
    {
        $this->info('4. Testing ScholarSync Tables...');
        
        $tables = [
            'logify_late_records',
            'logify_absent_records',
            'violations',
            'violation_types',
            'student_details'
        ];

        $tableStatus = [];
        foreach ($tables as $table) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $tableStatus[] = [$table, $exists ? '✅ Exists' : '❌ Missing'];
            
            if ($detailed && $exists) {
                $count = DB::table($table)->count();
                $this->line("  {$table}: {$count} records");
            }
        }

        $this->table(['Table', 'Status'], $tableStatus);
        $this->newLine();
    }

    /**
     * Test violation types
     */
    protected function testViolationTypes($detailed = false)
    {
        $this->info('5. Testing Violation Types...');
        
        $requiredTypes = [
            'Academic Login Late',
            'Academic Logout Late',
            'Going-out Login Late',
            'Academic Absent'
        ];

        $typeStatus = [];
        foreach ($requiredTypes as $type) {
            $exists = DB::table('violation_types')->where('violation_name', $type)->exists();
            $typeStatus[] = [$type, $exists ? '✅ Exists' : '❌ Missing'];
            
            if ($detailed && $exists) {
                $violationType = DB::table('violation_types')->where('violation_name', $type)->first();
                $this->line("  {$type}: ID {$violationType->id}, Severity: {$violationType->severity}");
            }
        }

        $this->table(['Violation Type', 'Status'], $typeStatus);
        $this->newLine();
    }

    /**
     * Test trigger health
     */
    protected function testTriggerHealth($detailed = false)
    {
        $this->info('6. Testing Trigger Health...');
        
        $health = $this->triggerService->checkTriggerHealth();
        
        $statusIcon = $health['status'] === 'healthy' ? '✅' : '❌';
        $this->line("Overall Health: {$statusIcon} {$health['status']}");
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Triggers Installed', $health['triggers_installed'] ? '✅ Yes' : '❌ No'],
                ['Triggers Enabled', $health['triggers_enabled'] ? '✅ Yes' : '❌ No'],
                ['Total Triggers', $health['total_triggers']],
                ['Enabled Triggers', $health['enabled_triggers']],
            ]
        );

        if ($detailed && isset($health['error'])) {
            $this->error('Error details: ' . $health['error']);
        }

        $this->newLine();
    }

    /**
     * Test performance monitoring
     */
    protected function testPerformanceMonitoring($detailed = false)
    {
        $this->info('7. Testing Performance Monitoring...');
        
        $stats = $this->triggerService->getTriggerStatistics(7);
        
        if (isset($stats['error'])) {
            $this->error('❌ Failed to get statistics: ' . $stats['error']);
            return;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Period (days)', $stats['period_days']],
                ['Recent Late Records', $stats['recent_late_records']],
                ['Recent Absent Records', $stats['recent_absent_records']],
                ['Recent Violations', $stats['recent_violations']],
                ['Trigger-Generated Violations', $stats['trigger_generated_violations']],
                ['Trigger Efficiency', $stats['trigger_efficiency'] . '%'],
            ]
        );

        if ($detailed) {
            $performance = $this->triggerService->monitorPerformance(24);
            if (!isset($performance['error'])) {
                $this->line('24-hour performance:');
                $this->line("  Trigger Late Records: {$performance['trigger_late_records']}");
                $this->line("  Trigger Absent Records: {$performance['trigger_absent_records']}");
                $this->line("  Trigger Violations: {$performance['trigger_violations']}");
                $this->line("  Violation Rate: {$performance['violation_rate']}%");
                $this->line("  Performance Status: {$performance['performance_status']}");
            }
        }

        $this->newLine();
    }

    /**
     * Test trigger execution
     */
    protected function testTriggerExecution($detailed = false)
    {
        $this->info('8. Testing Trigger Execution...');
        
        $this->warn('This test requires manual verification in the Logify database.');
        $this->newLine();
        
        $this->line('To test trigger execution manually:');
        $this->line('1. Connect to Logify database');
        $this->line('2. Run these test queries:');
        $this->newLine();
        
        $this->line('-- Test late login (replace with actual student_id):');
        $this->line('INSERT INTO academics (student_id, academic_date, time_in_remark, is_deleted) VALUES ("2025010001C1", NOW(), "late", 0);');
        $this->newLine();
        
        $this->line('-- Test absent (replace with actual student_id):');
        $this->line('INSERT INTO academics (student_id, academic_date, time_in_absent_validation, is_deleted) VALUES ("2025010001C1", NOW(), 1, 0);');
        $this->newLine();
        
        $this->line('-- Test going out late (replace with actual student_id):');
        $this->line('INSERT INTO going_outs (student_id, going_out_date, time_in_remark, is_deleted) VALUES ("2025010001C1", NOW(), "late", 0);');
        $this->newLine();
        
        $this->line('3. Check ScholarSync tables:');
        $this->line('   - logify_late_records');
        $this->line('   - logify_absent_records');
        $this->line('   - violations');
        $this->newLine();
        
        if ($detailed) {
            $this->line('Expected behavior:');
            $this->line('- Triggers should fire automatically');
            $this->line('- Records should appear in ScholarSync tables');
            $this->line('- Violations should be created automatically');
            $this->line('- Sync batch IDs should start with "TRIGGER_"');
        }
    }
}
