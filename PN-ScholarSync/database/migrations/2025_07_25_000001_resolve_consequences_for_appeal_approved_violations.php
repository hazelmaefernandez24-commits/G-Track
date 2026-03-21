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
        // Resolve consequences for violations that were resolved due to approved appeals
        // This ensures that when appeals are approved, both violation and consequence are resolved
        
        $updatedCount = DB::table('violations')
            ->where('status', 'resolved')
            ->where('consequence_status', 'active')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('violation_appeals')
                      ->whereColumn('violation_appeals.violation_id', 'violations.id')
                      ->where('violation_appeals.status', 'approved');
            })
            ->update(['consequence_status' => 'resolved']);
            
        // Log the migration
        \Log::info("Resolved consequences for appeal-approved violations", [
            'updated_count' => $updatedCount,
            'migration' => '2025_07_25_000001_resolve_consequences_for_appeal_approved_violations'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert consequences back to active for violations that were resolved by approved appeals
        // This is a conservative rollback - only affects violations with approved appeals
        DB::table('violations')
            ->where('status', 'resolved')
            ->where('consequence_status', 'resolved')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('violation_appeals')
                      ->whereColumn('violation_appeals.violation_id', 'violations.id')
                      ->where('violation_appeals.status', 'approved');
            })
            ->update(['consequence_status' => 'active']);
    }
};
