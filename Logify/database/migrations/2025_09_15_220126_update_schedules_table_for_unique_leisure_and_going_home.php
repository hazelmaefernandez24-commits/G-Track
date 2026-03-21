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
        Schema::table('schedules', function (Blueprint $table) {
            // Add valid_until column for unique leisure schedules
            $table->date('valid_until')->nullable()->after('end_date');
        });

        // Update the schedule_type enum to include new types
        DB::statement("ALTER TABLE schedules MODIFY COLUMN schedule_type ENUM('academic', 'going_out', 'going_home', 'unique_leisure') NOT NULL DEFAULT 'academic'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Remove valid_until column
            $table->dropColumn('valid_until');
        });

        // Revert the schedule_type enum to original values
        DB::statement("ALTER TABLE schedules MODIFY COLUMN schedule_type ENUM('academic', 'going_out') NOT NULL DEFAULT 'academic'");
    }
};
