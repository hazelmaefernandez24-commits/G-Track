<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionProof;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetGradeSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:reset {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all grade submissions and related data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Count existing submissions
        $submissionCount = GradeSubmission::count();
        $proofCount = GradeSubmissionProof::count();
        $gradeCount = DB::table('grade_submission_subject')->count();
        
        if ($submissionCount === 0) {
            $this->info('No grade submissions found to delete.');
            return 0;
        }

        $this->warn('WARNING: This will permanently delete:');
        $this->line("- {$submissionCount} grade submissions");
        $this->line("- {$proofCount} grade submission proofs");
        $this->line("- {$gradeCount} grade records");

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete ALL grade submissions and related data?', false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            // Start transaction
            DB::beginTransaction();

            try {
                // First, get all proof file paths before deleting
                $this->info('Collecting proof files...');
                $proofs = DB::table('grade_submission_proofs')->get();
                
                $this->info('Deleting grade submission proofs...');
                DB::table('grade_submission_proofs')->delete();
                
                $this->info('Deleting grade submission subjects...');
                DB::table('grade_submission_subject')->delete();
                
                $this->info('Deleting grade submissions...');
                DB::table('grade_submissions')->delete();
                
                // Commit transaction
                DB::commit();
                
                // Delete the proof files
                $this->info('\nDeleting proof files...');
                $deletedFiles = 0;
                $deletedFolders = [];
                
                foreach ($proofs as $proof) {
                    if (Storage::disk('public')->exists($proof->file_path)) {
                        Storage::disk('public')->delete($proof->file_path);
                        $deletedFiles++;
                        
                        // Track folders for cleanup
                        $folderPath = dirname($proof->file_path);
                        if (!in_array($folderPath, $deletedFolders)) {
                            $deletedFolders[] = $folderPath;
                        }
                    }
                }
                
                // Clean up empty folders
                foreach ($deletedFolders as $folder) {
                    if (Storage::disk('public')->exists($folder)) {
                        if (count(Storage::disk('public')->files($folder)) === 0) {
                            Storage::disk('public')->deleteDirectory($folder);
                        }
                    }
                }
                
                $this->info("\nSuccessfully deleted all grade submissions and related data.");
                $this->info("- Deleted {$deletedFiles} proof files");
                $this->info("You can now create new grade submissions.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            $this->error("No data was deleted due to the error.");
            return 1;
        }
    }
}
