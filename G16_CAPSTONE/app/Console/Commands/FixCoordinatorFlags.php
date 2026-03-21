<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\AssignmentMember;

class FixCoordinatorFlags extends Command
{
    protected $signature = 'fix:coordinator-flags';
    protected $description = 'Fix coordinator flags for Kitchen operation based on metadata';

    public function handle()
    {
        $this->info('Fixing coordinator flags for Kitchen operation...');
        
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
        
        // Get metadata
        $metadata = $assignment->metadata ? json_decode($assignment->metadata, true) : [];
        $coordinator2025 = $metadata['coordinator_2025'] ?? null;
        $coordinator2026 = $metadata['coordinator_2026'] ?? null;
        
        $this->info("Metadata coordinator_2025: " . ($coordinator2025 ?? 'Not set'));
        $this->info("Metadata coordinator_2026: " . ($coordinator2026 ?? 'Not set'));
        
        // Get all members
        $members = $assignment->assignmentMembers()->with('student.studentDetail')->get();
        
        $fixed = 0;
        
        foreach ($members as $member) {
            $name = 'Unknown';
            $batch = null;
            $studentId = null;
            
            if ($member->student) {
                $name = trim($member->student->user_fname . ' ' . $member->student->user_lname);
                if ($member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch;
                    $studentId = $member->student->studentDetail->student_id;
                }
            } else {
                $name = $member->student_name ?? $member->student_code ?? 'Unknown';
            }
            
            $shouldBeCoordinator = false;
            
            // Check if this member should be coordinator for their batch
            if ($batch == 2025 && $coordinator2025) {
                // Check if name matches or student_id matches
                if (trim($name) === trim($coordinator2025) || $studentId === $coordinator2025) {
                    $shouldBeCoordinator = true;
                }
            } elseif ($batch == 2026 && $coordinator2026) {
                // Check if name matches or student_id matches
                if (trim($name) === trim($coordinator2026) || $studentId === $coordinator2026) {
                    $shouldBeCoordinator = true;
                }
            }
            
            // Update if needed
            if ($shouldBeCoordinator && !$member->is_coordinator) {
                $member->is_coordinator = true;
                $member->save();
                $this->info("✅ Set {$name} (Batch {$batch}) as coordinator");
                $fixed++;
            } elseif (!$shouldBeCoordinator && $member->is_coordinator) {
                $member->is_coordinator = false;
                $member->save();
                $this->info("✅ Removed coordinator flag from {$name} (Batch {$batch})");
                $fixed++;
            }
        }
        
        if ($fixed > 0) {
            $this->info("\n🎉 Fixed {$fixed} coordinator flag(s)!");
        } else {
            $this->info("\n✅ All coordinator flags are already correct!");
        }
        
        return Command::SUCCESS;
    }
}
