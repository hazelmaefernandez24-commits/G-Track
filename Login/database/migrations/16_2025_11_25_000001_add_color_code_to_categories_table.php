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
        // Run on the Login connection so both schemas stay in sync
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'color_code')) {
                $table->string('color_code')->nullable()->default('#45B7D1')->after('description');
            }
        });

        Schema::table('assignments_members', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments_members', 'student_code')) {
                $table->string('student_code', 32)->nullable()->after('assignment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'color_code')) {
                $table->dropColumn('color_code');
            }
        });

        Schema::table('assignments_members', function (Blueprint $table) {
            if (Schema::hasColumn('assignments_members', 'student_code')) {
                $table->dropColumn('student_code');
            }
        });
    }
};
