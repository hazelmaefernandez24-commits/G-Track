<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds all missing columns that Logify system needs
     */
    public function up(): void
    {
        // Add monitor_name columns to academics table
        DB::connection('logify')->statement('
            ALTER TABLE academics 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS logify_sync_batch_id VARCHAR(255) NULL
        ');

        // Add monitor_name columns to going_outs table
        DB::connection('logify')->statement('
            ALTER TABLE going_outs 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS logify_sync_batch_id VARCHAR(255) NULL
        ');

        // Add monitor_name columns to intern_log table
        DB::connection('logify')->statement('
            ALTER TABLE intern_log 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS logify_sync_batch_id VARCHAR(255) NULL
        ');

        // Add monitor_name columns to going_home table
        DB::connection('logify')->statement('
            ALTER TABLE going_home 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS logify_sync_batch_id VARCHAR(255) NULL
        ');

        echo 'Missing columns added to Logify tables successfully!' . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('logify')->statement('
            ALTER TABLE academics 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name,
            DROP COLUMN IF EXISTS logify_sync_batch_id
        ');

        DB::connection('logify')->statement('
            ALTER TABLE going_outs 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name,
            DROP COLUMN IF EXISTS logify_sync_batch_id
        ');

        DB::connection('logify')->statement('
            ALTER TABLE intern_log 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name,
            DROP COLUMN IF EXISTS logify_sync_batch_id
        ');

        DB::connection('logify')->statement('
            ALTER TABLE going_home 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name,
            DROP COLUMN IF EXISTS logify_sync_batch_id
        ');

        echo 'Columns removed from Logify tables successfully!' . PHP_EOL;
    }
};
