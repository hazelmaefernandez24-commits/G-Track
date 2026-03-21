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
            // Add student_code column after student_id
            // This will store student codes for registered students
            // OR batch information for custom members (format: BATCH_2025, BATCH_2026)
            $table->string('student_code', 50)->nullable()->after('student_id');
            
            // Add index for faster lookups
            $table->index('student_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments_members', function (Blueprint $table) {
            $table->dropIndex(['student_code']);
            $table->dropColumn('student_code');
        });
    }
};
