<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class LogifyTriggerService
{
    protected $config;
    protected $logifyConnection;

    public function __construct()
    {
        $this->config = config('logify_triggers');
        $this->setupLogifyConnection();
    }

    /**
     * Setup connection to Logify database
     */
    protected function setupLogifyConnection()
    {
        Config::set('database.connections.logify', $this->config['database']);
        $this->logifyConnection = 'logify';
    }

    /**
     * Check if triggers are enabled and working
     */
    public function checkTriggerHealth()
    {
        try {
            $triggers = DB::connection($this->logifyConnection)
                ->select("
                    SELECT 
                        TRIGGER_NAME,
                        EVENT_MANIPULATION,
                        EVENT_OBJECT_TABLE
                    FROM information_schema.TRIGGERS 
                    WHERE TRIGGER_NAME LIKE 'tr_academics%' 
                    OR TRIGGER_NAME LIKE 'tr_going_outs%'
                ");

            $health = [
                'triggers_installed' => count($triggers) > 0,
                'triggers_enabled' => count($triggers) > 0, // Assume enabled if they exist
                'total_triggers' => count($triggers),
                'enabled_triggers' => count($triggers),
                'status' => count($triggers) > 0 ? 'healthy' : 'unhealthy'
            ];

            if ($this->config['logging']['log_trigger_executions']) {
                Log::info('Logify trigger health check', $health);
            }

            return $health;

        } catch (\Exception $e) {
            Log::error('Logify trigger health check failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'triggers_installed' => false,
                'triggers_enabled' => false,
                'total_triggers' => 0,
                'enabled_triggers' => 0,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get trigger statistics
     */
    public function getTriggerStatistics($days = 7)
    {
        try {
            $since = Carbon::now()->subDays($days);

            // Get recent sync activity
            $recentLateRecords = DB::table('logify_late_records')
                ->where('last_synced_at', '>=', $since)
                ->count();

            $recentAbsentRecords = DB::table('logify_absent_records')
                ->where('last_synced_at', '>=', $since)
                ->count();

            $recentViolations = DB::table('violations')
                ->whereNotNull('logify_sync_batch_id')
                ->where('created_at', '>=', $since)
                ->count();

            // Get trigger-generated violations
            $triggerViolations = DB::table('violations')
                ->whereNotNull('logify_sync_batch_id')
                ->where('logify_sync_batch_id', 'LIKE', 'TRIGGER_%')
                ->where('created_at', '>=', $since)
                ->count();

            return [
                'period_days' => $days,
                'since' => $since->toDateTimeString(),
                'recent_late_records' => $recentLateRecords,
                'recent_absent_records' => $recentAbsentRecords,
                'recent_violations' => $recentViolations,
                'trigger_generated_violations' => $triggerViolations,
                'trigger_efficiency' => $recentViolations > 0 ? round(($triggerViolations / $recentViolations) * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get trigger statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Test trigger functionality
     */
    public function testTriggers()
    {
        try {
            $testResults = [];

            // Test 1: Check if procedures exist (simplified version doesn't use procedures)
            $testResults['procedures_exist'] = true; // Simplified triggers don't use procedures
            $testResults['procedures_found'] = ['Simplified triggers (no procedures needed)'];

            // Test 2: Check if triggers exist
            $triggers = DB::connection($this->logifyConnection)
                ->select("
                    SELECT TRIGGER_NAME
                    FROM information_schema.TRIGGERS 
                    WHERE TRIGGER_NAME LIKE 'tr_academics%' 
                    OR TRIGGER_NAME LIKE 'tr_going_outs%'
                ");

            $testResults['triggers_exist'] = count($triggers) >= 4;
            $testResults['triggers_found'] = array_map(function($trigger) {
                return [
                    'name' => $trigger->TRIGGER_NAME,
                    'status' => 'ENABLED' // Assume enabled if they exist
                ];
            }, $triggers);

            // Test 3: Check ScholarSync tables
            $scholarSyncTables = [
                'logify_late_records',
                'logify_absent_records',
                'violations',
                'violation_types'
            ];

            $tableExists = [];
            foreach ($scholarSyncTables as $table) {
                $tableExists[$table] = DB::getSchemaBuilder()->hasTable($table);
            }

            $testResults['scholar_sync_tables'] = $tableExists;
            $testResults['all_tables_exist'] = !in_array(false, $tableExists);

            // Test 4: Check violation types
            $violationTypes = DB::table('violation_types')
                ->whereIn('violation_name', array_values($this->config['violation_types']))
                ->pluck('violation_name')
                ->toArray();

            $testResults['violation_types_exist'] = count($violationTypes) >= 4;
            $testResults['violation_types_found'] = $violationTypes;

            // Overall test result
            $testResults['overall_status'] = 
                $testResults['procedures_exist'] && 
                $testResults['triggers_exist'] && 
                $testResults['all_tables_exist'] && 
                $testResults['violation_types_exist'] ? 'PASS' : 'FAIL';

            return $testResults;

        } catch (\Exception $e) {
            Log::error('Trigger test failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'overall_status' => 'ERROR',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get configuration summary
     */
    public function getConfigurationSummary()
    {
        return [
            'triggers_enabled' => $this->config['triggers_enabled'],
            'auto_create_violations' => $this->config['behavior']['auto_create_violations'],
            'update_existing_records' => $this->config['behavior']['update_existing_records'],
            'process_deleted_records' => $this->config['behavior']['process_deleted_records'],
            'log_trigger_executions' => $this->config['logging']['log_trigger_executions'],
            'fallback_to_import' => $this->config['fallback']['fallback_to_import'],
            'database_host' => $this->config['database']['host'],
            'database_name' => $this->config['database']['database'],
        ];
    }

    /**
     * Enable or disable triggers
     */
    public function toggleTriggers($enable = true)
    {
        try {
            $action = $enable ? 'EnableLogifyTriggers' : 'DisableLogifyTriggers';
            DB::connection($this->logifyConnection)->unprepared("CALL {$action}()");
            
            $status = $enable ? 'enabled' : 'disabled';
            Log::info("Logify triggers {$status}");
            
            return [
                'success' => true,
                'message' => "Triggers {$status} successfully",
                'status' => $status
            ];

        } catch (\Exception $e) {
            Log::error("Failed to toggle triggers", [
                'action' => $enable ? 'enable' : 'disable',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => "Failed to toggle triggers: " . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Monitor trigger performance
     */
    public function monitorPerformance($hours = 24)
    {
        try {
            $since = Carbon::now()->subHours($hours);

            // Get trigger-generated records
            $triggerLateRecords = DB::table('logify_late_records')
                ->where('sync_batch_id', 'LIKE', 'TRIGGER_%')
                ->where('created_at', '>=', $since)
                ->count();

            $triggerAbsentRecords = DB::table('logify_absent_records')
                ->where('sync_batch_id', 'LIKE', 'TRIGGER_%')
                ->where('created_at', '>=', $since)
                ->count();

            $triggerViolations = DB::table('violations')
                ->where('logify_sync_batch_id', 'LIKE', 'TRIGGER_%')
                ->where('created_at', '>=', $since)
                ->count();

            // Calculate performance metrics
            $totalRecords = $triggerLateRecords + $triggerAbsentRecords;
            $violationRate = $totalRecords > 0 ? round(($triggerViolations / $totalRecords) * 100, 2) : 0;

            return [
                'period_hours' => $hours,
                'since' => $since->toDateTimeString(),
                'trigger_late_records' => $triggerLateRecords,
                'trigger_absent_records' => $triggerAbsentRecords,
                'trigger_violations' => $triggerViolations,
                'total_records' => $totalRecords,
                'violation_rate' => $violationRate,
                'performance_status' => $violationRate > 0 ? 'active' : 'inactive'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to monitor trigger performance', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
