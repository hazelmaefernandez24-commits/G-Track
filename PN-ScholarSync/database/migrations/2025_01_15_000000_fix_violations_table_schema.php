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
        // Ensure the violations table has all required columns
        Schema::table('violations', function (Blueprint $table) {
            // Add action_taken column if it doesn't exist
            if (!Schema::hasColumn('violations', 'action_taken')) {
                $table->boolean('action_taken')->default(true)->after('consequence')->comment('Whether action has been taken for this violation');
            }
            
            // Add consequence_status column if it doesn't exist
            if (!Schema::hasColumn('violations', 'consequence_status')) {
                $table->enum('consequence_status', ['active', 'resolved'])->default('resolved')->after('consequence')->comment('Status of the consequence');
            }
            
            // Add other missing columns if they don't exist
            if (!Schema::hasColumn('violations', 'consequence_duration_value')) {
                $table->integer('consequence_duration_value')->nullable()->after('consequence_status');
            }
            
            if (!Schema::hasColumn('violations', 'consequence_duration_unit')) {
                $table->enum('consequence_duration_unit', ['hours', 'days', 'weeks', 'months'])->nullable()->after('consequence_duration_value');
            }
            
            if (!Schema::hasColumn('violations', 'consequence_start_date')) {
                $table->datetime('consequence_start_date')->nullable()->after('consequence_duration_unit');
            }
            
            if (!Schema::hasColumn('violations', 'consequence_end_date')) {
                $table->datetime('consequence_end_date')->nullable()->after('consequence_start_date');
            }
        });
        
        // Update existing records to have action_taken = true if it's null
        DB::table('violations')
            ->whereNull('action_taken')
            ->update(['action_taken' => true]);
            
        // Update existing records to have consequence_status = 'resolved' if it's null
        DB::table('violations')
            ->whereNull('consequence_status')
            ->update(['consequence_status' => 'resolved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns in down migration to avoid data loss
        // This migration is meant to fix schema issues, not remove them
    }
};
