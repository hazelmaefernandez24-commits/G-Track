<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\AssignmentMember;

class DiagnoseRotation extends Command
{
    protected $signature = 'diagnose:rotation';
    protected $description = 'Diagnose rotation system and assignment counts';

    public function handle()
    {
        $this->info('=== ROTATION SYSTEM DIAGNOSIS ===');
        $this->newLine();
        
        // Check assignment statuses
        $currentCount = Assignment::where('status', 'current')->count();
        $previousCount = Assignment::where('status', 'previous')->count();
        
        $this->info("📊 Assignment Status:");
        $this->info("  Current: {$currentCount}");
        $this->info("  Previous: {$previousCount}");
        $this->newLine();
        
        // Check latest previous assignment
        $latestPrevious = Assignment::where('status', 'previous')
            ->orderBy('end_date', 'desc')
            ->with('category', 'assignmentMembers.student.studentDetail')
            ->first();
        
        if ($latestPrevious) {
            $this->info("📅 Latest Previous Assignment:");
            $this->info("  Category: {$latestPrevious->category->name}");
            $this->info("  End Date: {$latestPrevious->end_date}");
            $this->info("  Members: {$latestPrevious->assignmentMembers->count()}");
            $this->newLine();
            
            $this->info("  Members List:");
            foreach ($latestPrevious->assignmentMembers as $member) {
                $name = 'Unknown';
                $batch = 'Unknown';
                
                if ($member->student && $member->student->studentDetail) {
                    $name = trim($member->student->user_fname . ' ' . $member->student->user_lname);
                    $batch = $member->student->studentDetail->batch ?? 'Unknown';
                } elseif ($member->student_name) {
                    $name = $member->student_name;
                }
                
                $this->info("    - {$name} (Batch {$batch})");
            }
        } else {
            $this->warn("⚠️ No previous assignments found!");
        }
        
        $this->newLine();
        
        // Check current Kitchen operation assignment
        $kitchenCategory = \App\Models\Category::where('name', 'LIKE', '%Kitchen%')->first();
        if ($kitchenCategory) {
            $currentKitchen = Assignment::where('category_id', $kitchenCategory->id)
                ->where('status', 'current')
                ->with('assignmentMembers.student.studentDetail')
                ->first();
            
            if ($currentKitchen) {
                $this->info("🍳 Current Kitchen Operation:");
                $this->info("  Members: {$currentKitchen->assignmentMembers->count()}");
                $this->newLine();
                
                $this->info("  Members List:");
                foreach ($currentKitchen->assignmentMembers as $member) {
                    $name = 'Unknown';
                    $batch = 'Unknown';
                    
                    if ($member->student && $member->student->studentDetail) {
                        $name = trim($member->student->user_fname . ' ' . $member->student->user_lname);
                        $batch = $member->student->studentDetail->batch ?? 'Unknown';
                    } elseif ($member->student_name) {
                        $name = $member->student_name;
                    }
                    
                    $coordFlag = $member->is_coordinator ? ' ⭐' : '';
                    $this->info("    - {$name} (Batch {$batch}){$coordFlag}");
                }
            } else {
                $this->warn("⚠️ No current Kitchen operation assignment found!");
            }
        }
        
        return Command::SUCCESS;
    }
}
