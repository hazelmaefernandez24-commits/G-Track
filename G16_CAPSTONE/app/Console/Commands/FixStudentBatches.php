<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentDetail;
use App\Models\PNUser;

class FixStudentBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:fix-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix student batch assignments by parsing from student_code or student_id';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to fix ALL student batches...');
        $this->info('This will ensure batch 2025 students are ONLY in 2025 and batch 2026 students are ONLY in 2026');

        // Get all student details
        $studentDetails = StudentDetail::all();
        $fixed = 0;
        $verified = 0;
        $couldNotFix = 0;

        foreach ($studentDetails as $detail) {
            $user = PNUser::where('user_id', $detail->user_id)->first();
            $studentName = $user ? trim($user->user_fname . ' ' . $user->user_lname) : 'Unknown';

            $correctBatch = null;

            // Try to parse from student_id FIRST (most reliable)
            if (!empty($detail->student_id)) {
                if (preg_match('/^(20\\d{2})/', $detail->student_id, $matches)) {
                    $correctBatch = (int)$matches[1];
                    $this->info("Parsed batch {$correctBatch} from student_id: {$detail->student_id} for {$studentName}");
                }
            }

            // If no student_id, try student_code
            if (!$correctBatch && !empty($detail->student_code)) {
                if (preg_match('/^(20\\d{2})/', $detail->student_code, $matches)) {
                    $correctBatch = (int)$matches[1];
                    $this->info("Parsed batch {$correctBatch} from student_code: {$detail->student_code} for {$studentName}");
                }
            }

            // Only process if we found a valid batch (2025 or 2026)
            if ($correctBatch && ($correctBatch == 2025 || $correctBatch == 2026)) {
                // Check if current batch is correct
                if ($detail->batch == $correctBatch) {
                    $this->info("✓ {$studentName} already correct: Batch {$correctBatch}");
                    $verified++;
                } else {
                    // Fix incorrect batch
                    $oldBatch = $detail->batch;
                    $detail->batch = $correctBatch;
                    $detail->save();
                    $this->info("✅ FIXED: {$studentName} from Batch {$oldBatch} -> Batch {$correctBatch}");
                    $fixed++;
                }
            } else {
                $this->warn("⚠️ Could not determine batch for {$studentName} (student_id: {$detail->student_id}, student_code: {$detail->student_code})");
                $couldNotFix++;
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("✓ Already correct: {$verified}");
        $this->info("✅ Fixed: {$fixed}");
        $this->warn("⚠️ Could not fix: {$couldNotFix}");

        if ($fixed > 0) {
            $this->info("\n🎉 {$fixed} student(s) have been corrected!");
            $this->info("⚡ Now run Auto-Shuffle to update the assignments with correct batch data.");
        } else {
            $this->info("\n✅ All students already have correct batch assignments!");
        }

        return Command::SUCCESS;
    }
}
