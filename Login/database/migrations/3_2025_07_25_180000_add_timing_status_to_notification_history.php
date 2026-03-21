<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notification_history', function (Blueprint $table) {
            // Add timing_status field to store Early, On Time, Late, nontime, or Manual Entry
            // Using string instead of enum for SQLite compatibility
            $table->string('timing_status', 50)->nullable()->after('is_late');

            // Add index for better performance when querying by timing status
            $table->index(['timing_status', 'created_at']);
        });

        // Migrate existing data: set timing_status based on is_late field
        DB::statement("UPDATE notification_history SET timing_status = CASE WHEN is_late = 1 THEN 'Late' ELSE 'On Time' END WHERE timing_status IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_history', function (Blueprint $table) {
            $table->dropIndex(['timing_status', 'created_at']);
            $table->dropColumn('timing_status');
        });
    }
};
