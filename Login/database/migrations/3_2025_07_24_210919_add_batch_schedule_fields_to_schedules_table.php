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
        Schema::table('schedules', function (Blueprint $table) {
            // Add fields for batch going-out schedules
            $table->string('schedule_name')->nullable()->after('schedule_type')->comment('Name/description for batch schedules (e.g., "Going Home - July 2025")');
            $table->date('start_date')->nullable()->after('schedule_name')->comment('Start date for batch schedules');
            $table->date('end_date')->nullable()->after('start_date')->comment('End date for batch schedules');
            $table->boolean('is_batch_schedule')->default(false)->after('end_date')->comment('Flag to identify batch schedules');

            // Add index for better performance when querying batch schedules
            $table->index(['batch', 'schedule_type', 'is_batch_schedule'], 'schedules_batch_type_flag_index');
            $table->index(['start_date', 'end_date'], 'schedules_date_range_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('schedules_batch_type_flag_index');
            $table->dropIndex('schedules_date_range_index');

            // Drop columns
            $table->dropColumn([
                'schedule_name',
                'start_date',
                'end_date',
                'is_batch_schedule'
            ]);
        });
    }
};
