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
            $table->enum('schedule_type', ['academic', 'going_out'])
                  ->default('academic')
                  ->after('day_of_week')
                  ->comment('Type of schedule: academic for irregular academic schedules, going_out for individual going-out schedules');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('schedule_type');
        });
    }
};
