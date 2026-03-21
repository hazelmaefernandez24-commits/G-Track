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
        Schema::table('generated_schedules', function (Blueprint $table) {
            // Add task_status column if it doesn't exist
            if (!Schema::hasColumn('generated_schedules', 'task_status')) {
                $table->string('task_status')->default('pending')->after('rotation_frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('generated_schedules', 'task_status')) {
                $table->dropColumn('task_status');
            }
        });
    }
};
