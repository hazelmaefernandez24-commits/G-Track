<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GradeSubmission;
use Illuminate\Support\Facades\DB;

class CleanupGradeSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:grade-submissions {--force : Run the command without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all grade submissions except the latest one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the latest submission
        $latestSubmission = GradeSubmission::latest('created_at')->first();
        
        if (!$latestSubmission) {
            $this->info('No grade submissions found.');
            return 0;
        }

        // Get count of submissions to be deleted
        $countToDelete = GradeSubmission::where('id', '!=', $latestSubmission->id)->count();
        
        if ($countToDelete === 0) {
            $this->info('No submissions to delete. Only the latest submission exists.');
            return 0;
        }

        $this->info("Latest submission (will be kept):");
        $this->line("- ID: {$latestSubmission->id}");
        $this->line("- Created: {$latestSubmission->created_at}");
        $this->line("- School: " . ($latestSubmission->school->name ?? 'N/A'));
        $this->line("- Class: " . ($latestSubmission->classModel->class_name ?? 'N/A'));
        $this->line("- Status: {$latestSubmission->status}");
        
        $this->warn("\nThis will delete {$countToDelete} submissions.");

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete all submissions except the latest one?', false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Start transaction
            DB::beginTransaction();

            // First delete related records
            DB::table('grade_submission_subject')
                ->whereNotIn('grade_submission_id', [$latestSubmission->id])
                ->delete();

            // Then delete the submissions
            $deleted = GradeSubmission::where('id', '!=', $latestSubmission->id)->delete();

            DB::commit();

            $this->info("\nSuccessfully deleted {$deleted} submissions. Kept the latest submission (ID: {$latestSubmission->id}).");
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
