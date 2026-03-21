<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\Category;

class CheckCoordinators extends Command
{
    protected $signature = 'check:coordinators';
    protected $description = 'Check coordinator flags for Kitchen operation';

    public function handle()
    {
        $this->info('Checking coordinators for Kitchen operation...');
        
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
        $this->info("\nMembers:");
        
        $members = $assignment->assignmentMembers()->with('student.studentDetail')->get();
        
        foreach ($members as $member) {
            $name = 'Unknown';
            $batch = 'Unknown';
            
            if ($member->student) {
                $name = trim($member->student->user_fname . ' ' . $member->student->user_lname);
                if ($member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch ?? 'Unknown';
                }
            } else {
                $name = $member->student_name ?? $member->student_code ?? 'Unknown';
            }
            
            $coordinatorFlag = $member->is_coordinator ? 'YES ⭐' : 'NO';
            $this->info("- {$name} (Batch {$batch}) - Coordinator: {$coordinatorFlag}");
        }
        
        return Command::SUCCESS;
    }
}
