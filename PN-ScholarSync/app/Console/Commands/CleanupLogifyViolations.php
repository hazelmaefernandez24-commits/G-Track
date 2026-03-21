<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Support\Facades\Log;

class CleanupLogifyViolations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:cleanup-violations 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate Logify violations and reset for fresh import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting Logify violations cleanup...');

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Get all Logify-related violation types
        $logifyViolationTypes = ViolationType::whereIn('violation_name', [
            'Late',
            'Absent', 
            'Academic Login Late',
            'Academic Logout Late',
            'Going-out Login Late',
            'Academic Absent'
        ])->pluck('id')->toArray();

        if (empty($logifyViolationTypes)) {
            $this->warn('⚠️  No Logify violation types found.');
            return Command::SUCCESS;
        }

        // Find violations with logify_sync_batch_id
        $logifyViolations = Violation::whereIn('violation_type_id', $logifyViolationTypes)
            ->whereNotNull('logify_sync_batch_id')
            ->get();

        $this->info("📊 Found {$logifyViolations->count()} Logify-synced violations");

        if ($logifyViolations->isEmpty()) {
            $this->info('✅ No Logify violations to clean up.');
            return Command::SUCCESS;
        }

        // Group by student and violation type to show duplicates
        $grouped = $logifyViolations->groupBy(function ($violation) {
            return $violation->student_id . '_' . $violation->violation_type_id;
        });

        $duplicateGroups = $grouped->filter(function ($group) {
            return $group->count() > 1;
        });

        if ($duplicateGroups->isNotEmpty()) {
            $this->warn("⚠️  Found duplicate violations:");
            foreach ($duplicateGroups as $key => $group) {
                $firstViolation = $group->first();
                $this->line("   • Student {$firstViolation->student_id} - {$firstViolation->violationType->violation_name}: {$group->count()} violations");
            }
        }

        if ($dryRun) {
            $this->info('🔍 DRY RUN - No violations will be deleted');
            $this->info("Would delete {$logifyViolations->count()} Logify violations");
            return Command::SUCCESS;
        }

        if (!$force) {
            if (!$this->confirm("Are you sure you want to delete {$logifyViolations->count()} Logify violations?")) {
                $this->info('❌ Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        // Delete the violations
        $deletedCount = 0;
        foreach ($logifyViolations as $violation) {
            try {
                $violation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to delete violation ID {$violation->id}: " . $e->getMessage());
            }
        }

        $this->info("✅ Successfully deleted {$deletedCount} Logify violations");
        
        Log::info('Logify violations cleanup completed', [
            'deleted_count' => $deletedCount,
            'total_found' => $logifyViolations->count()
        ]);

        return Command::SUCCESS;
    }
}
