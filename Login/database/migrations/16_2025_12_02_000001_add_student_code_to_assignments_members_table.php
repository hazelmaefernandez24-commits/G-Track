<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignments_members') && !Schema::hasColumn('assignments_members', 'student_code')) {
            Schema::table('assignments_members', function (Blueprint $table) {
                $table->string('student_code', 50)->nullable()->after('student_id');
                $table->index('student_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments_members') && Schema::hasColumn('assignments_members', 'student_code')) {
            Schema::table('assignments_members', function (Blueprint $table) {
                $table->dropIndex(['student_code']);
                $table->dropColumn('student_code');
            });
        }
    }
};
