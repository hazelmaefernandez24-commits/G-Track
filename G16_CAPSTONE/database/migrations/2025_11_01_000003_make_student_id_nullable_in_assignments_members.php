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
        // Make student_id nullable to support custom members
        Schema::table('assignments_members', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            
            // Also make student_code nullable if it exists
            if (Schema::hasColumn('assignments_members', 'student_code')) {
                $table->string('student_code')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments_members', function (Blueprint $table) {
            // Revert back to NOT NULL (only if safe to do so)
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            
            if (Schema::hasColumn('assignments_members', 'student_code')) {
                $table->string('student_code')->nullable(false)->change();
            }
        });
    }
};
