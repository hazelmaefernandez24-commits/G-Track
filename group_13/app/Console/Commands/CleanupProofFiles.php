<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CleanupProofFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proofs:cleanup {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all proof files from storage that are not referenced in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force') && ! $this->confirm('This will delete all proof files not referenced in the database. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Get all proof files from storage
        $proofsInStorage = Storage::disk('public')->allFiles('proofs');
        
        // Get all proof file paths from the database
        $proofsInDb = DB::table('grade_submission_proofs')
            ->pluck('file_path')
            ->toArray();

        // Find files in storage that don't exist in the database
        $orphanedFiles = array_filter($proofsInStorage, function($file) use ($proofsInDb) {
            return !in_array($file, $proofsInDb);
        });

        if (empty($orphanedFiles)) {
            $this->info('No orphaned proof files found.');
            return 0;
        }

        $this->info('Found ' . count($orphanedFiles) . ' orphaned proof files:');
        
        $this->table(
            ['File Path', 'Size'],
            array_map(function($file) {
                return [
                    $file,
                    Storage::disk('public')->size($file) . ' bytes'
                ];
            }, $orphanedFiles)
        );

        if ($this->confirm('Do you want to delete these files?', true)) {
            $deleted = 0;
            foreach ($orphanedFiles as $file) {
                if (Storage::disk('public')->delete($file)) {
                    $deleted++;
                    // Try to remove parent directory if empty
                    $dir = dirname($file);
                    if (count(Storage::disk('public')->files($dir)) === 0) {
                        Storage::disk('public')->deleteDirectory($dir);
                    }
                }
            }
            $this->info("Successfully deleted $deleted files.");
        } else {
            $this->info('Operation cancelled. No files were deleted.');
        }

        return 0;
    }
}
