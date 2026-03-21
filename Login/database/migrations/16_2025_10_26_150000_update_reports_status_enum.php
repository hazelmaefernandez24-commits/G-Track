<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to include 'pending' for existing databases where migration already ran
        DB::statement("ALTER TABLE `reports` MODIFY `status` ENUM('pending','active','resolved') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original set (active, resolved)
        DB::statement("ALTER TABLE `reports` MODIFY `status` ENUM('active','resolved') NOT NULL DEFAULT 'active'");
    }
};
