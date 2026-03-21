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
        if (Schema::hasColumn('assignments_members', 'student_name')) {
            return;
        }

        $afterColumn = Schema::hasColumn('assignments_members', 'student_code')
            ? 'student_code'
            : 'student_id';

        Schema::table('assignments_members', function (Blueprint $table) use ($afterColumn) {
            $table->string('student_name', 255)->nullable()->after($afterColumn);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('assignments_members', 'student_name')) {
            return;
        }

        Schema::table('assignments_members', function (Blueprint $table) {
            $table->dropColumn('student_name');
        });
    }
};
