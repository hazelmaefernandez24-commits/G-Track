<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;

class CheckKitchenOperation extends Command
{
    protected $signature = 'check:kitchen-operation';
    protected $description = 'Check Kitchen operation assignment details';

    public function handle()
    {
        $this->info('=== KITCHEN OPERATION ASSIGNMENT ===');
        $this->newLine();
        
        $assignment = Assignment::find(334);
        
        if (!$assignment) {
            $this->error("Assignment ID 334 not found!");
            return Command::FAILURE;
        }
        
        $this->info("Assignment ID: {$assignment->id}");
        $this->info("Category: {$assignment->category->name}");
        $this->info("Status: {$assignment->status}");
        $this->info("Created: {$assignment->created_at}");
        $this->info("Updated: {$assignment->updated_at}");
        $this->info("Total Members: {$assignment->assignmentMembers->count()}");
        $this->newLine();
        
        $batch2025 = [];
        $batch2026 = [];
        
        foreach ($assignment->assignmentMembers as $member) {
            $name = 'Unknown';
            $batch = null;
            
            if ($member->student && $member->student->studentDetail) {
                $name = trim($member->student->user_fname . ' ' . $member->student->user_lname);
                $batch = $member->student->studentDetail->batch;
            } elseif ($member->student_name) {
                $name = $member->student_name;
            }
            
            $coordFlag = $member->is_coordinator ? ' ⭐' : '';
            
            if ($batch == 2025) {
                $batch2025[] = $name . $coordFlag;
            } elseif ($batch == 2026) {
                $batch2026[] = $name . $coordFlag;
            }
        }
        
        $this->info("📊 Batch 2025 (" . count($batch2025) . " students):");
        foreach ($batch2025 as $student) {
            $this->info("  - {$student}");
        }
        
        $this->newLine();
        
        $this->info("📊 Batch 2026 (" . count($batch2026) . " students):");
        foreach ($batch2026 as $student) {
            $this->info("  - {$student}");
        }
        
        return Command::SUCCESS;
    }
}
