<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing schedules with appropriate schedule_type

        // 1. Set schedule_type to 'academic' for schedules with student_id (irregular academic schedules)
        DB::table('schedules')
            ->whereNotNull('student_id')
            ->where('schedule_type', 'academic') // Only update records that have default academic type
            ->update(['schedule_type' => 'academic']);

        // 2. Set schedule_type to 'academic' for schedules with batch and pn_group (batch academic schedules)
        DB::table('schedules')
            ->whereNotNull('batch')
            ->whereNotNull('pn_group')
            ->whereNull('student_id') // Exclude individual schedules
            ->where('schedule_type', 'academic') // Only update records that have default academic type
            ->update(['schedule_type' => 'academic']);

        // 3. Set schedule_type to 'going_out' for schedules with gender (going-out schedules)
        DB::table('schedules')
            ->whereNotNull('gender')
            ->whereNull('student_id') // Exclude individual schedules
            ->whereNull('batch') // Exclude batch schedules
            ->whereNull('pn_group') // Exclude batch schedules
            ->where('schedule_type', 'academic') // Only update records that have default academic type
            ->update(['schedule_type' => 'going_out']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset schedule_type to default for all records
        DB::table('schedules')->update(['schedule_type' => 'academic']);
    }
};
