<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearGradeSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-grade-submissions {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all grade submission data including proofs and subject relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('WARNING: This will delete ALL grade submission data. Are you sure you want to continue?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Clearing grade submission data...');
        
        // Disable foreign key checks to avoid constraint errors
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear the tables in the correct order to respect foreign key constraints
        \DB::table('grade_submission_proofs')->truncate();
        $this->info('✓ Cleared grade submission proofs');
        
        \DB::table('grade_submission_subject')->truncate();
        $this->info('✓ Cleared grade submission subject relationships');
        
        \DB::table('grade_submissions')->truncate();
        $this->info('✓ Cleared grade submissions');
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->info('\nAll grade submission data has been cleared successfully!');
        
        return 0;
    }
}
