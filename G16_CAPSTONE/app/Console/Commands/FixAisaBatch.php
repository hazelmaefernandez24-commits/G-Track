<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentDetail;
use App\Models\PNUser;

class FixAisaBatch extends Command
{
    protected $signature = 'students:fix-aisa';
    protected $description = 'Fix Aisa Delos Santos batch to 2025';

    public function handle()
    {
        $this->info('Looking for Aisa Delos Santos...');
        
        // Find Aisa Delos Santos
        $user = PNUser::where('user_fname', 'LIKE', '%Aisa%')
                      ->where('user_lname', 'LIKE', '%Delos Santos%')
                      ->first();
        
        if (!$user) {
            // Try alternative spelling
            $user = PNUser::where('user_fname', 'LIKE', '%Aisa%')
                          ->where('user_lname', 'LIKE', '%Santos%')
                          ->first();
        }
        
        if (!$user) {
            $this->error('Aisa Delos Santos not found in users table');
            return Command::FAILURE;
        }
        
        $this->info("Found user: {$user->user_fname} {$user->user_lname} (ID: {$user->user_id})");
        
        // Get student details
        $detail = StudentDetail::where('user_id', $user->user_id)->first();
        
        if (!$detail) {
            $this->error('Student details not found');
            return Command::FAILURE;
        }
        
        $this->info("Current batch: {$detail->batch}");
        $this->info("Student code: {$detail->student_code}");
        $this->info("Student ID: {$detail->student_id}");
        
        // Parse batch from student_code
        $parsedBatch = null;
        if (!empty($detail->student_code)) {
            if (preg_match('/^(20\d{2})/', $detail->student_code, $matches)) {
                $parsedBatch = (int)$matches[1];
                $this->info("Batch from student_code: {$parsedBatch}");
            }
        }
        
        // Update to 2025
        $detail->batch = 2025;
        $detail->save();
        
        $this->info("✅ Updated Aisa Delos Santos batch to 2025");
        
        return Command::SUCCESS;
    }
}
