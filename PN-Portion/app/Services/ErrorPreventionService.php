<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErrorPreventionService
{
    /**
     * Check if a method exists on a model before calling it
     */
    public static function safeMethodCall($object, $method, $parameters = [], $default = null)
    {
        if (!is_object($object)) {
            Log::warning('ErrorPrevention: Attempted to call method on non-object', [
                'method' => $method,
                'object_type' => gettype($object)
            ]);
            return $default;
        }

        if (!method_exists($object, $method)) {
            Log::warning('ErrorPrevention: Method does not exist', [
                'class' => get_class($object),
                'method' => $method,
                'available_methods' => get_class_methods($object)
            ]);
            return $default;
        }

        try {
            return call_user_func_array([$object, $method], $parameters);
        } catch (\Exception $e) {
            Log::error('ErrorPrevention: Method call failed', [
                'class' => get_class($object),
                'method' => $method,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Check if a database table exists before querying
     */
    public static function safeTableQuery($table, $callback, $default = [])
    {
        if (!Schema::hasTable($table)) {
            Log::warning('ErrorPrevention: Table does not exist', [
                'table' => $table,
                'available_tables' => self::getAvailableTables()
            ]);
            return $default;
        }

        try {
            return $callback();
        } catch (\Exception $e) {
            Log::error('ErrorPrevention: Table query failed', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Check if a column exists before using it in queries
     */
    public static function safeColumnQuery($table, $column, $callback, $default = [])
    {
        if (!Schema::hasTable($table)) {
            Log::warning('ErrorPrevention: Table does not exist for column check', [
                'table' => $table,
                'column' => $column
            ]);
            return $default;
        }

        if (!Schema::hasColumn($table, $column)) {
            Log::warning('ErrorPrevention: Column does not exist', [
                'table' => $table,
                'column' => $column,
                'available_columns' => self::getTableColumns($table)
            ]);
            return $default;
        }

        try {
            return $callback();
        } catch (\Exception $e) {
            Log::error('ErrorPrevention: Column query failed', [
                'table' => $table,
                'column' => $column,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Get all available tables in the database
     */
    public static function getAvailableTables()
    {
        try {
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            return collect($tables)->pluck($tableColumn)->toArray();
        } catch (\Exception $e) {
            Log::error('ErrorPrevention: Failed to get available tables', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get all columns for a specific table
     */
    public static function getTableColumns($table)
    {
        try {
            $columns = DB::select("DESCRIBE {$table}");
            return collect($columns)->pluck('Field')->toArray();
        } catch (\Exception $e) {
            Log::error('ErrorPrevention: Failed to get table columns', [
                'table' => $table,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Safe status getter for meals (replaces getCurrentStatus)
     */
    public static function getMealStatus($meal, $date = null)
    {
        if (!$meal) {
            return 'Not Planned';
        }

        // Since meal_statuses table was removed, use simple logic
        if (is_object($meal)) {
            return 'Planned';
        }

        return 'Not Planned';
    }

    /**
     * Validate API response structure
     */
    public static function validateApiResponse($response, $requiredFields = [])
    {
        if (!is_array($response) && !is_object($response)) {
            return false;
        }

        $responseArray = is_object($response) ? (array) $response : $response;

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $responseArray)) {
                Log::warning('ErrorPrevention: Missing required field in API response', [
                    'missing_field' => $field,
                    'available_fields' => array_keys($responseArray)
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * System health check
     */
    public static function systemHealthCheck()
    {
        $health = [
            'database' => self::checkDatabaseHealth(),
            'tables' => self::checkRequiredTables(),
            'columns' => self::checkRequiredColumns(),
            'models' => self::checkModelMethods(),
            'routes' => self::checkCriticalRoutes()
        ];

        Log::info('ErrorPrevention: System health check completed', $health);
        return $health;
    }

    /**
     * Check database connectivity
     */
    private static function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if required tables exist
     */
    private static function checkRequiredTables()
    {
        $requiredTables = [
            'users', 'meals', 'feedback', 'kitchen_menu_polls',
            'notifications', 'pre_orders', 'post_assessments'
        ];

        $results = [];
        foreach ($requiredTables as $table) {
            $results[$table] = Schema::hasTable($table) ? 'exists' : 'missing';
        }

        return $results;
    }

    /**
     * Check if required columns exist in critical tables
     */
    private static function checkRequiredColumns()
    {
        $requiredColumns = [
            'feedback' => ['student_id', 'meal_type', 'meal_date', 'rating', 'comments', 'suggestions', 'food_quality', 'dietary_concerns', 'is_anonymous'],
            'meals' => ['name', 'ingredients', 'meal_type', 'day_of_week', 'week_cycle'],
            'kitchen_menu_polls' => ['meal_name', 'meal_type', 'poll_date', 'deadline', 'status'],
            'users' => ['name', 'email', 'role']
        ];

        $results = [];
        foreach ($requiredColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $results[$table] = 'table_missing';
                continue;
            }

            $tableResults = [];
            foreach ($columns as $column) {
                $tableResults[$column] = Schema::hasColumn($table, $column) ? 'exists' : 'missing';
            }
            $results[$table] = $tableResults;
        }

        return $results;
    }

    /**
     * Check if critical model methods exist
     */
    private static function checkModelMethods()
    {
        $checks = [
            'App\Models\Meal' => ['forWeekCycle', 'forDay'],
            'App\Models\User' => ['hasRole'],
            'App\Models\KitchenMenuPoll' => ['isActive']
        ];

        $results = [];
        foreach ($checks as $model => $methods) {
            if (class_exists($model)) {
                foreach ($methods as $method) {
                    $results["{$model}::{$method}"] = method_exists($model, $method) ? 'exists' : 'missing';
                }
            } else {
                $results[$model] = 'class_missing';
            }
        }

        return $results;
    }

    /**
     * Check if critical routes are accessible
     */
    private static function checkCriticalRoutes()
    {
        // This would need to be implemented based on your specific needs
        return ['status' => 'not_implemented'];
    }
}
