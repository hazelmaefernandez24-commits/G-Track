<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\AssignmentMember;

class SetBatchCoordinators extends Command
{
    protected $signature = 'set:batch-coordinators';
    protected $description = 'Set coordinators for both Batch 2025 and Batch 2026 in Kitchen operation';

    public function handle()
    {
        $this->info('Setting coordinators for both batches in Kitchen operation...');
        
        // Find Kitchen operation category
        $category = Category::where('name', 'LIKE', '%Kitchen operation%')->first();
        
        if (!$category) {
            $this->error('Kitchen operation category not found');
            return Command::FAILURE;
        }
        
        // Get current assignment
        $assignment = Assignment::where('category_id', $category->id)
            ->where('status', 'current')
            ->first();
        
        if (!$assignment) {
            $this->error('No current assignment found');
            return Command::FAILURE;
        }
        
        $this->info("Assignment ID: {$assignment->id}");
        
        // Get all members grouped by batch
        $members = $assignment->assignmentMembers()->with('student.studentDetail')->get();
        
        $batch2025Members = [];
        $batch2026Members = [];
        
        foreach ($members as $member) {
            $batch = null;
            if ($member->student && $member->student->studentDetail) {
                $batch = $member->student->studentDetail->batch;
            }
            
            if ($batch == 2025) {
                $batch2025Members[] = $member;
            } elseif ($batch == 2026) {
                $batch2026Members[] = $member;
            }
        }
        
        $this->info("Batch 2025 members: " . count($batch2025Members));
        $this->info("Batch 2026 members: " . count($batch2026Members));
        
        // Set first member of each batch as coordinator
        $fixed = 0;
        
        // Batch 2025 - Set first member as coordinator
        if (count($batch2025Members) > 0) {
            $coordinator2025 = $batch2025Members[0];
            $name = $coordinator2025->student 
                ? trim($coordinator2025->student->user_fname . ' ' . $coordinator2025->student->user_lname)
                : 'Unknown';
            
            if (!$coordinator2025->is_coordinator) {
                $coordinator2025->is_coordinator = true;
                $coordinator2025->save();
                $this->info("✅ Set {$name} as Batch 2025 coordinator");
                $fixed++;
            } else {
                $this->info("✓ {$name} is already Batch 2025 coordinator");
            }
        }
        
        // Batch 2026 - Set first member as coordinator
        if (count($batch2026Members) > 0) {
            $coordinator2026 = $batch2026Members[0];
            $name = $coordinator2026->student 
                ? trim($coordinator2026->student->user_fname . ' ' . $coordinator2026->student->user_lname)
                : 'Unknown';
            
            if (!$coordinator2026->is_coordinator) {
                $coordinator2026->is_coordinator = true;
                $coordinator2026->save();
                $this->info("✅ Set {$name} as Batch 2026 coordinator");
                $fixed++;
            } else {
                $this->info("✓ {$name} is already Batch 2026 coordinator");
            }
        }
        
        if ($fixed > 0) {
            $this->info("\n🎉 Set {$fixed} coordinator(s)!");
            $this->info("✅ Both batches now have coordinators assigned!");
        } else {
            $this->info("\n✅ Both batches already have coordinators!");
        }
        
        return Command::SUCCESS;
    }
}
