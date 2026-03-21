<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionProof;

class ClearDummyGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:dummy-grades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear dummy grades and proofs for University of San Jose Recoletos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing dummy grades for University of San Jose Recoletos...');

        try {
            DB::beginTransaction();

            // Find the grade submission for University of San Jose Recoletos
            $gradeSubmission = GradeSubmission::where('school_id', '123')
                ->where('semester', '1st')
                ->where('term', 'prelim')
                ->where('academic_year', '2024-2025')
                ->first();

            if ($gradeSubmission) {
                $this->info("Found grade submission ID: {$gradeSubmission->id}");

                // Clear grade submission subject records
                $deletedGrades = DB::table('grade_submission_subject')
                    ->where('grade_submission_id', $gradeSubmission->id)
                    ->delete();

                $this->info("Deleted {$deletedGrades} grade records");

                // Clear proof records
                $deletedProofs = GradeSubmissionProof::where('grade_submission_id', $gradeSubmission->id)
                    ->delete();

                $this->info("Deleted {$deletedProofs} proof records");

                // Optionally delete the grade submission itself
                // $gradeSubmission->delete();
                // $this->info("Deleted grade submission");
            } else {
                $this->warn('No grade submission found for University of San Jose Recoletos');
            }

            DB::commit();
            $this->info('Dummy grades cleared successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error clearing dummy grades: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
