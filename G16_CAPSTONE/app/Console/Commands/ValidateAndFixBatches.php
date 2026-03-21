<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentDetail;
use App\Models\PNUser;

class ValidateAndFixBatches extends Command
{
    protected $signature = 'validate:batches {--fix : Automatically fix incorrect batches}';
    protected $description = 'Validate all student batches and optionally fix them';

    public function handle()
    {
        $autoFix = $this->option('fix');
        
        $this->info('🔍 Validating ALL student batches...');
        $this->info('This ensures Batch 2025 students stay in 2025 and Batch 2026 students stay in 2026');
        $this->newLine();
        
        $studentDetails = StudentDetail::all();
        $errors = [];
        $fixed = 0;
        $correct = 0;
        
        foreach ($studentDetails as $detail) {
            $user = PNUser::where('user_id', $detail->user_id)->first();
            $studentName = $user ? trim($user->user_fname . ' ' . $user->user_lname) : 'Unknown';
            
            // Parse correct batch from student_id
            $correctBatch = null;
            if (!empty($detail->student_id)) {
                if (preg_match('/^(20\d{2})/', $detail->student_id, $matches)) {
                    $correctBatch = (int)$matches[1];
                }
            }
            
            // Only validate batches 2025 and 2026
            if (!$correctBatch || !in_array($correctBatch, [2025, 2026])) {
                continue;
            }
            
            $currentBatch = $detail->batch;
            
            // Check if batch is correct
            if ($currentBatch != $correctBatch) {
                $error = [
                    'name' => $studentName,
                    'student_id' => $detail->student_id,
                    'current_batch' => $currentBatch,
                    'correct_batch' => $correctBatch
                ];
                $errors[] = $error;
                
                $this->error("❌ {$studentName} - Student ID: {$detail->student_id}");
                $this->error("   Current batch: {$currentBatch} | Should be: {$correctBatch}");
                
                if ($autoFix) {
                    $detail->batch = $correctBatch;
                    $detail->save();
                    $this->info("   ✅ FIXED to Batch {$correctBatch}");
                    $fixed++;
                }
            } else {
                $correct++;
            }
        }
        
        $this->newLine();
        $this->info('=== VALIDATION SUMMARY ===');
        $this->info("✅ Correct: {$correct} students");
        
        if (count($errors) > 0) {
            $this->error("❌ Errors found: " . count($errors) . " students");
            
            if ($autoFix) {
                $this->info("✅ Fixed: {$fixed} students");
                $this->newLine();
                $this->info('🎉 All batches have been corrected!');
                $this->info('⚡ Run Auto-Shuffle to apply changes to assignments');
            } else {
                $this->newLine();
                $this->warn('💡 Run with --fix flag to automatically correct these batches:');
                $this->warn('   php artisan validate:batches --fix');
            }
        } else {
            $this->info('🎉 All student batches are correct!');
        }
        
        return count($errors) > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
