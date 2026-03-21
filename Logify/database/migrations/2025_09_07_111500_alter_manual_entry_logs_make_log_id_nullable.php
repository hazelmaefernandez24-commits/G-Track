<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Make log_id nullable in case it was created NOT NULL in the database
        DB::statement('ALTER TABLE manual_entry_logs MODIFY COLUMN log_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Revert to NOT NULL without default (may fail if NULLs exist) - so set default 0 during revert
        DB::statement('ALTER TABLE manual_entry_logs MODIFY COLUMN log_id BIGINT UNSIGNED NOT NULL DEFAULT 0');
    }
};