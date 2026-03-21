<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Violation;
use App\Models\ViolationAppeal;
use App\Models\PenaltyConfiguration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Recalculate penalties for all students, excluding violations resolved by approved appeals
        
        $updatedCount = 0;
        
        // Get all students who have violations
        $studentsWithViolations = DB::table('violations')
            ->select('student_id')
            ->distinct()
            ->get();
            
        foreach ($studentsWithViolations as $studentRecord) {
            $studentId = $studentRecord->student_id;
            
            // Get all violations for this student that should count for penalty calculation
            $countableViolations = Violation::where('student_id', $studentId)
                ->where('action_taken', true)
                ->where(function($query) {
                    // Exclude violations that were resolved due to approved appeals
                    $query->where('status', '!=', 'resolved')
                          ->orWhere(function($subQuery) {
                              // If status is resolved, only exclude if it was due to an approved appeal
                              $subQuery->where('status', 'resolved')
                                       ->whereDoesntHave('appeals', function($appealQuery) {
                                           $appealQuery->where('status', 'approved');
                                       });
                          });
                })
                ->get();
                
            if ($countableViolations->isEmpty()) {
                continue;
            }
            
            // Get penalty rankings from database configuration
            $penaltyRanks = PenaltyConfiguration::getActive()
                ->pluck('sort_order', 'penalty_code')
                ->toArray();
            
            // Find the highest penalty among countable violations
            $highestRank = 0;
            $highestPenalty = null;
            
            foreach ($countableViolations as $violation) {
                $rank = $penaltyRanks[$violation->penalty] ?? 0;
                if ($rank > $highestRank) {
                    $highestRank = $rank;
                    $highestPenalty = $violation->penalty;
                }
            }
            
            // Update all violations for this student to have the correct highest penalty
            // but only update violations that should count for penalty calculation
            if ($highestPenalty) {
                $updated = Violation::where('student_id', $studentId)
                    ->where('action_taken', true)
                    ->where(function($query) {
                        // Only update violations that should count for penalty calculation
                        $query->where('status', '!=', 'resolved')
                              ->orWhere(function($subQuery) {
                                  // If status is resolved, only update if it was NOT due to an approved appeal
                                  $subQuery->where('status', 'resolved')
                                           ->whereDoesntHave('appeals', function($appealQuery) {
                                               $appealQuery->where('status', 'approved');
                                           });
                              });
                    })
                    ->where('penalty', '!=', $highestPenalty)
                    ->update(['penalty' => $highestPenalty]);
                    
                $updatedCount += $updated;
            }
        }
        
        // Log the migration
        \Log::info("Recalculated penalties excluding appeal-resolved violations", [
            'updated_count' => $updatedCount,
            'migration' => '2025_07_25_000002_recalculate_penalties_excluding_appeal_resolved'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as it corrects penalty calculations
        // The previous state may have been incorrect
        \Log::info("Penalty recalculation migration rollback - no action taken", [
            'migration' => '2025_07_25_000002_recalculate_penalties_excluding_appeal_resolved'
        ]);
    }
};
