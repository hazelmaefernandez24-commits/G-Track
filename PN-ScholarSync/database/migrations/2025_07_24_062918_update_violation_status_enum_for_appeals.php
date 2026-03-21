<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include appeal-related statuses
        DB::statement("ALTER TABLE violations MODIFY COLUMN status ENUM('pending', 'active', 'resolved', 'appealed', 'appeal_approved', 'appeal_denied') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE violations MODIFY COLUMN status ENUM('pending', 'active', 'resolved', 'appealed') DEFAULT 'active'");
    }
};
