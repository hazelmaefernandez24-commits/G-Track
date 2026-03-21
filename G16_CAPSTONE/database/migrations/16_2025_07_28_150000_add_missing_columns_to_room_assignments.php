<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_assignments', function (Blueprint $table) {
            // Add student_id column if it doesn't exist
            if (!Schema::hasColumn('room_assignments', 'student_id')) {
                $table->string('student_id', 50)->nullable()->after('room_number');
            }
            
            // Add batch_year column if it doesn't exist
            if (!Schema::hasColumn('room_assignments', 'batch_year')) {
                $table->string('batch_year', 10)->nullable()->after('student_gender');
            }
            
            // Add indexes if they don't exist
            try {
                $table->index('student_id');
                $table->index(['room_number', 'student_gender']);
                $table->index('batch_year');
            } catch (\Exception $e) {
                // Indexes might already exist, ignore errors
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('room_assignments', 'student_id')) {
                $table->dropColumn('student_id');
            }
            
            if (Schema::hasColumn('room_assignments', 'batch_year')) {
                $table->dropColumn('batch_year');
            }
        });
    }
};
