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
        Schema::table('grade_submission_subject', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_submission_subject', 'student_status')) {
                $table->enum('student_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_submission_subject', function (Blueprint $table) {
            $table->dropColumn('student_status');
        });
    }
};
