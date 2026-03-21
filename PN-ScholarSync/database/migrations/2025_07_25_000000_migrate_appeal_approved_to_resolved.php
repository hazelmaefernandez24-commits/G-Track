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
        // Migrate existing violations with 'appeal_approved' status to 'resolved'
        // This is needed because we changed the behavior so that approved appeals
        // automatically set violations to 'resolved' instead of 'appeal_approved'
        
        DB::table('violations')
            ->where('status', 'appeal_approved')
            ->update(['status' => 'resolved']);
            
        // Log the migration
        $migratedCount = DB::table('violations')
            ->where('status', 'resolved')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('violation_appeals')
                      ->whereColumn('violation_appeals.violation_id', 'violations.id')
                      ->where('violation_appeals.status', 'approved');
            })
            ->count();
            
        \Log::info("Migrated appeal_approved violations to resolved status", [
            'migrated_count' => $migratedCount,
            'migration' => '2025_07_25_000000_migrate_appeal_approved_to_resolved'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert resolved violations back to appeal_approved if they have approved appeals
        DB::table('violations')
            ->where('status', 'resolved')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('violation_appeals')
                      ->whereColumn('violation_appeals.violation_id', 'violations.id')
                      ->where('violation_appeals.status', 'approved');
            })
            ->update(['status' => 'appeal_approved']);
    }
};
