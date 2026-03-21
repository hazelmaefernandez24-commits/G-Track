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
        // Make student_id nullable to support custom members. The `student_id`
        // column references `pnph_users.user_id` which is a string primary key,
        // so we must keep it as a string type when altering.
        if (Schema::hasTable('assignments_members') && Schema::hasColumn('assignments_members', 'student_id')) {
            Schema::table('assignments_members', function (Blueprint $table) {
                $table->string('student_id')->nullable()->change();

                // Also make student_code nullable if it exists
                if (Schema::hasColumn('assignments_members', 'student_code')) {
                    $table->string('student_code')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assignments_members') && Schema::hasColumn('assignments_members', 'student_id')) {
            Schema::table('assignments_members', function (Blueprint $table) {
                // Revert back to NOT NULL (only if safe to do so). Keep as string
                // to match pnph_users.user_id.
                $table->string('student_id')->nullable(false)->change();

                if (Schema::hasColumn('assignments_members', 'student_code')) {
                    $table->string('student_code')->nullable(false)->change();
                }
            });
        }
    }
};
