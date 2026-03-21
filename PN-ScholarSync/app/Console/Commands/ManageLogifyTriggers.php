<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ManageLogifyTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:triggers 
                            {action : Action to perform (install|uninstall|status|enable|disable|test)}
                            {--force : Force the action without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Logify database triggers for real-time ScholarSync integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $force = $this->option('force');

        $this->info("Logify Triggers Manager - Action: {$action}");
        $this->newLine();

        switch ($action) {
            case 'install':
                $this->installTriggers($force);
                break;
            case 'uninstall':
                $this->uninstallTriggers($force);
                break;
            case 'status':
                $this->checkTriggerStatus();
                break;
            case 'enable':
                $this->enableTriggers();
                break;
            case 'disable':
                $this->disableTriggers();
                break;
            case 'test':
                $this->testTriggers();
                break;
            default:
                $this->error("Invalid action: {$action}");
                $this->info("Available actions: install, uninstall, status, enable, disable, test");
                return 1;
        }

        return 0;
    }

    /**
     * Install Logify triggers
     */
    protected function installTriggers($force = false)
    {
        $this->info('Installing Logify database triggers...');
        
        if (!$force && !$this->confirm('This will install database triggers in the Logify database. Continue?')) {
            $this->info('Installation cancelled.');
            return;
        }

        try {
            // Setup Logify connection
            $this->setupLogifyConnection();
            
            // Read the SQL file
            $sqlFile = database_path('migrations/2025_01_15_000001_create_logify_triggers.sql');
            
            if (!file_exists($sqlFile)) {
                $this->error("SQL file not found: {$sqlFile}");
                return;
            }

            $sql = file_get_contents($sqlFile);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $this->info('Executing SQL statements...');
            $progressBar = $this->output->createProgressBar(count($statements));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        DB::connection('logify')->unprepared($statement);
                    } catch (\Exception $e) {
                        // Skip errors for statements that might already exist
                        if (!str_contains($e->getMessage(), 'already exists')) {
                            $this->warn("Warning: " . $e->getMessage());
                        }
                    }
                }
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
            $this->info('✅ Triggers installed successfully!');
            $this->info('Run "php artisan logify:triggers status" to verify installation.');
            
        } catch (\Exception $e) {
            $this->error("Failed to install triggers: " . $e->getMessage());
            Log::error('Logify triggers installation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Uninstall Logify triggers
     */
    protected function uninstallTriggers($force = false)
    {
        $this->info('Uninstalling Logify database triggers...');
        
        if (!$force && !$this->confirm('This will remove all Logify triggers from the database. Continue?')) {
            $this->info('Uninstallation cancelled.');
            return;
        }

        try {
            $this->setupLogifyConnection();
            
            $triggers = [
                'tr_academics_after_insert',
                'tr_academics_after_update', 
                'tr_going_outs_after_insert',
                'tr_going_outs_after_update'
            ];
            
            $procedures = [
                'SyncLateRecord',
                'SyncAbsentRecord',
                'CreateLateViolations',
                'CreateAbsentViolations',
                'EnableLogifyTriggers',
                'DisableLogifyTriggers',
                'CheckLogifyTriggerStatus'
            ];
            
            $this->info('Removing triggers...');
            foreach ($triggers as $trigger) {
                try {
                    DB::connection('logify')->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
                    $this->line("  ✓ Removed trigger: {$trigger}");
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Could not remove trigger {$trigger}: " . $e->getMessage());
                }
            }
            
            $this->info('Removing procedures...');
            foreach ($procedures as $procedure) {
                try {
                    DB::connection('logify')->unprepared("DROP PROCEDURE IF EXISTS {$procedure}");
                    $this->line("  ✓ Removed procedure: {$procedure}");
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Could not remove procedure {$procedure}: " . $e->getMessage());
                }
            }
            
            $this->info('✅ Triggers uninstalled successfully!');
            
        } catch (\Exception $e) {
            $this->error("Failed to uninstall triggers: " . $e->getMessage());
            Log::error('Logify triggers uninstallation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check trigger status
     */
    protected function checkTriggerStatus()
    {
        try {
            $this->setupLogifyConnection();
            
            $this->info('Checking Logify trigger status...');
            
            $triggers = DB::connection('logify')
                ->select("
                    SELECT 
                        TRIGGER_NAME,
                        EVENT_MANIPULATION,
                        EVENT_OBJECT_TABLE
                    FROM information_schema.TRIGGERS 
                    WHERE TRIGGER_NAME LIKE 'tr_academics%' 
                    OR TRIGGER_NAME LIKE 'tr_going_outs%'
                    ORDER BY EVENT_OBJECT_TABLE, EVENT_MANIPULATION
                ");
            
            if (empty($triggers)) {
                $this->warn('No Logify triggers found. Run "php artisan logify:triggers install" to install them.');
                return;
            }
            
            $this->table(
                ['Trigger Name', 'Event', 'Table'],
                array_map(function($trigger) {
                    return [
                        $trigger->TRIGGER_NAME,
                        $trigger->EVENT_MANIPULATION,
                        $trigger->EVENT_OBJECT_TABLE
                    ];
                }, $triggers)
            );
            
            // Check procedures
            $procedures = DB::connection('logify')
                ->select("
                    SELECT ROUTINE_NAME, ROUTINE_TYPE
                    FROM information_schema.ROUTINES 
                    WHERE ROUTINE_NAME IN (
                        'SyncLateRecord', 'SyncAbsentRecord', 'CreateLateViolations', 
                        'CreateAbsentViolations', 'EnableLogifyTriggers', 
                        'DisableLogifyTriggers', 'CheckLogifyTriggerStatus'
                    )
                    ORDER BY ROUTINE_NAME
                ");
            
            if (!empty($procedures)) {
                $this->newLine();
                $this->info('Installed procedures:');
                foreach ($procedures as $procedure) {
                    $this->line("  ✓ {$procedure->ROUTINE_NAME} ({$procedure->ROUTINE_TYPE})");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Failed to check trigger status: " . $e->getMessage());
        }
    }

    /**
     * Enable triggers
     */
    protected function enableTriggers()
    {
        try {
            $this->setupLogifyConnection();
            
            $this->info('Enabling Logify triggers...');
            
            DB::connection('logify')->unprepared("CALL EnableLogifyTriggers()");
            
            $this->info('✅ Triggers enabled successfully!');
            
        } catch (\Exception $e) {
            $this->error("Failed to enable triggers: " . $e->getMessage());
        }
    }

    /**
     * Disable triggers
     */
    protected function disableTriggers()
    {
        try {
            $this->setupLogifyConnection();
            
            $this->info('Disabling Logify triggers...');
            
            DB::connection('logify')->unprepared("CALL DisableLogifyTriggers()");
            
            $this->info('✅ Triggers disabled successfully!');
            
        } catch (\Exception $e) {
            $this->error("Failed to disable triggers: " . $e->getMessage());
        }
    }

    /**
     * Test triggers
     */
    protected function testTriggers()
    {
        $this->info('Testing Logify triggers...');
        $this->warn('This feature requires manual testing by inserting/updating records in Logify tables.');
        $this->newLine();
        
        $this->info('To test triggers manually:');
        $this->line('1. Connect to Logify database');
        $this->line('2. Insert a test record in academics table with late/absent status');
        $this->line('3. Check ScholarSync logify_late_records and logify_absent_records tables');
        $this->line('4. Check ScholarSync violations table for new violations');
        $this->newLine();
        
        $this->info('Example test queries:');
        $this->line('-- Test late login');
        $this->line('INSERT INTO academics (student_id, academic_date, time_in_remark, is_deleted) VALUES ("2025010001C1", NOW(), "late", 0);');
        $this->newLine();
        $this->line('-- Test absent');
        $this->line('INSERT INTO academics (student_id, academic_date, time_in_absent_validation, is_deleted) VALUES ("2025010001C1", NOW(), 1, 0);');
    }

    /**
     * Setup Logify database connection
     */
    protected function setupLogifyConnection()
    {
        Config::set('database.connections.logify', [
            'driver' => env('LOGIFY_DB_DRIVER', 'mysql'),
            'host' => env('LOGIFY_DB_HOST', '127.0.0.1'),
            'port' => env('LOGIFY_DB_PORT', '3306'),
            'database' => env('LOGIFY_DB_DATABASE', 'logify'),
            'username' => env('LOGIFY_DB_USERNAME', 'root'),
            'password' => env('LOGIFY_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }
}
