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
        Schema::table('assignments_members', function (Blueprint $table) {
            // Add columns for task-specific assignments
            $table->string('task_type')->nullable()->after('is_coordinator'); // cook-breakfast, prep-lunch, etc.
            $table->string('time_slot')->nullable()->after('task_type'); // monday, tuesday, etc.
            $table->string('student_name')->nullable()->after('student_id'); // Store student name for quick access
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments_members', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'time_slot', 'student_name']);
        });
    }
};
