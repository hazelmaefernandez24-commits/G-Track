<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PNUser;

class CheckSofiaBatch extends Command
{
    protected $signature = 'check:sofia-batch';
    protected $description = 'Check Sofia Nicole Moreno batch';

    public function handle()
    {
        $this->info('Checking Sofia Nicole Moreno batch...');
        
        $student = PNUser::where('user_fname', 'LIKE', '%Sofia%')
            ->where('user_lname', 'LIKE', '%Moreno%')
            ->with('studentDetail')
            ->first();
        
        if (!$student) {
            $this->error('Sofia Nicole Moreno not found');
            return Command::FAILURE;
        }
        
        $this->info("Found: {$student->user_fname} {$student->user_lname}");
        
        if ($student->studentDetail) {
            $this->info("Batch: {$student->studentDetail->batch}");
            $this->info("Student ID: {$student->studentDetail->student_id}");
            
            if ($student->studentDetail->batch == 2025) {
                $this->info("✅ Correct! Sofia is in Batch 2025");
            } else {
                $this->error("❌ Wrong! Sofia is in Batch {$student->studentDetail->batch} but should be in 2025");
            }
        } else {
            $this->error('No student details found');
        }
        
        return Command::SUCCESS;
    }
}
