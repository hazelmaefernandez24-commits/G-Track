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
        // Add 'pending' back to the consequence_status enum
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_status ENUM('pending', 'active', 'resolved') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'pending' from the enum (revert to previous state)
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_status ENUM('active', 'resolved') DEFAULT 'resolved'");
    }
};
