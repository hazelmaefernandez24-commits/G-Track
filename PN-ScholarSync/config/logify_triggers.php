<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Logify Database Triggers Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls the real-time database triggers that sync
    | student attendance data from Logify to ScholarSync automatically.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Trigger Integration Settings
    |--------------------------------------------------------------------------
    */
    'triggers_enabled' => env('LOGIFY_TRIGGERS_ENABLED', true),
    
    /*
    |--------------------------------------------------------------------------
    | Database Connection Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Trigger Behavior Settings
    |--------------------------------------------------------------------------
    */
    'behavior' => [
        // Whether to create violations automatically when triggers fire
        'auto_create_violations' => env('LOGIFY_AUTO_CREATE_VIOLATIONS', true),
        
        // Whether to update existing records or create new ones
        'update_existing_records' => env('LOGIFY_UPDATE_EXISTING', true),
        
        // Whether to process deleted records (is_deleted = 1)
        'process_deleted_records' => env('LOGIFY_PROCESS_DELETED', false),
        
        // Maximum number of violations to create per student per month
        'max_violations_per_month' => env('LOGIFY_MAX_VIOLATIONS_PER_MONTH', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Violation Type Mappings
    |--------------------------------------------------------------------------
    */
    'violation_types' => [
        'academic_login_late' => 'Academic Login Late',
        'academic_logout_late' => 'Academic Logout Late',
        'going_out_login_late' => 'Going-out Login Late',
        'academic_absent' => 'Academic Absent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        // Whether to log trigger executions
        'log_trigger_executions' => env('LOGIFY_LOG_TRIGGERS', true),
        
        // Log level for trigger events
        'log_level' => env('LOGIFY_LOG_LEVEL', 'info'),
        
        // Whether to log detailed trigger information
        'log_detailed' => env('LOGIFY_LOG_DETAILED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        // Whether to use batch processing for multiple records
        'use_batch_processing' => env('LOGIFY_BATCH_PROCESSING', true),
        
        // Batch size for processing multiple records
        'batch_size' => env('LOGIFY_BATCH_SIZE', 100),
        
        // Whether to use database transactions
        'use_transactions' => env('LOGIFY_USE_TRANSACTIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        // Whether to track trigger performance metrics
        'track_performance' => env('LOGIFY_TRACK_PERFORMANCE', true),
        
        // Whether to send alerts for trigger failures
        'send_failure_alerts' => env('LOGIFY_SEND_ALERTS', false),
        
        // Email addresses to receive alerts
        'alert_emails' => env('LOGIFY_ALERT_EMAILS', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Settings
    |--------------------------------------------------------------------------
    */
    'fallback' => [
        // Whether to fallback to scheduled import if triggers fail
        'fallback_to_import' => env('LOGIFY_FALLBACK_TO_IMPORT', true),
        
        // How often to check if triggers are working (in minutes)
        'health_check_interval' => env('LOGIFY_HEALTH_CHECK_INTERVAL', 60),
        
        // Maximum number of consecutive failures before fallback
        'max_consecutive_failures' => env('LOGIFY_MAX_FAILURES', 5),
    ],
];
