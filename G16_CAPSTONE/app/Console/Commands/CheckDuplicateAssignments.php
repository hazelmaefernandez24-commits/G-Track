<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\Category;

class CheckDuplicateAssignments extends Command
{
    protected $signature = 'check:duplicate-assignments';
    protected $description = 'Check for duplicate current assignments per category';

    public function handle()
    {
        $this->info('=== CHECKING FOR DUPLICATE ASSIGNMENTS ===');
        $this->newLine();
        
        $categories = Category::all();
        $duplicatesFound = false;
        
        foreach ($categories as $category) {
            $currentAssignments = Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->with('assignmentMembers')
                ->get();
            
            if ($currentAssignments->count() > 1) {
                $duplicatesFound = true;
                $this->error("❌ DUPLICATE: {$category->name} has {$currentAssignments->count()} current assignments!");
                
                foreach ($currentAssignments as $assignment) {
                    $this->info("  Assignment ID: {$assignment->id}");
                    $this->info("    Created: {$assignment->created_at}");
                    $this->info("    Updated: {$assignment->updated_at}");
                    $this->info("    Members: {$assignment->assignmentMembers->count()}");
                    $this->newLine();
                }
            } elseif ($currentAssignments->count() == 1) {
                $assignment = $currentAssignments->first();
                $this->info("✅ {$category->name}: 1 current assignment (ID: {$assignment->id}, Members: {$assignment->assignmentMembers->count()})");
            } else {
                $this->warn("⚠️ {$category->name}: No current assignment");
            }
        }
        
        $this->newLine();
        
        if ($duplicatesFound) {
            $this->error("🚨 DUPLICATES FOUND! This will cause issues with the UI.");
            $this->info("💡 Solution: Delete older duplicate assignments, keep only the most recent one.");
        } else {
            $this->info("✅ No duplicates found!");
        }
        
        return $duplicatesFound ? Command::FAILURE : Command::SUCCESS;
    }
}
