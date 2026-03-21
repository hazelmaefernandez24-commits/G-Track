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
        Schema::table('academics', function (Blueprint $table) {
            // Add fields to store monitor names who set considerations
            if (!Schema::hasColumn('academics', 'time_out_monitor_name')) {
                $table->string('time_out_monitor_name')->nullable()->after('time_out_reason');
            }
            if (!Schema::hasColumn('academics', 'time_in_monitor_name')) {
                $table->string('time_in_monitor_name')->nullable()->after('time_in_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academics', function (Blueprint $table) {
            if (Schema::hasColumn('academics', 'time_out_monitor_name')) {
                $table->dropColumn('time_out_monitor_name');
            }
            if (Schema::hasColumn('academics', 'time_in_monitor_name')) {
                $table->dropColumn('time_in_monitor_name');
            }
        });
    }
};
