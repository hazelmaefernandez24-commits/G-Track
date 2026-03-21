<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds monitor_name columns to Logify database tables to fix consideration save errors
     */
    public function up(): void
    {
        // Add monitor name columns to going_outs table
        DB::connection('logify')->statement('
            ALTER TABLE going_outs 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL
        ');

        // Add monitor name columns to academics table
        DB::connection('logify')->statement('
            ALTER TABLE academics 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL
        ');

        // Add monitor name columns to intern_log table
        DB::connection('logify')->statement('
            ALTER TABLE intern_log 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL
        ');

        // Add monitor name columns to going_home table
        DB::connection('logify')->statement('
            ALTER TABLE going_home 
            ADD COLUMN IF NOT EXISTS time_in_monitor_name VARCHAR(255) NULL,
            ADD COLUMN IF NOT EXISTS time_out_monitor_name VARCHAR(255) NULL
        ');

        echo 'Monitor name columns added to Logify tables successfully!' . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('logify')->statement('
            ALTER TABLE going_outs 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name
        ');

        DB::connection('logify')->statement('
            ALTER TABLE academics 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name
        ');

        DB::connection('logify')->statement('
            ALTER TABLE intern_log 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name
        ');

        DB::connection('logify')->statement('
            ALTER TABLE going_home 
            DROP COLUMN IF EXISTS time_in_monitor_name,
            DROP COLUMN IF EXISTS time_out_monitor_name
        ');

        echo 'Monitor name columns removed from Logify tables successfully!' . PHP_EOL;
    }
};
