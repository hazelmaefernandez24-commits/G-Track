<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\Category;
use Illuminate\Support\Facades\Http;

class FixKitchenAssignments extends Command
{
    protected $signature = 'assignments:fix-kitchen';
    protected $description = 'Fix Kitchen operation assignments to have correct number of students';

    public function handle()
    {
        $this->info('Fixing Kitchen operation assignments...');
        
        // Find Kitchen operation category
        $category = Category::where('name', 'LIKE', '%Kitchen operation%')->first();
        
        if (!$category) {
            $this->error('Kitchen operation category not found');
            return Command::FAILURE;
        }
        
        $this->info("Found category: {$category->name} (ID: {$category->id})");
        
        // Get current assignment
        $assignment = Assignment::where('category_id', $category->id)
            ->where('status', 'current')
            ->first();
        
        if (!$assignment) {
            $this->error('No current assignment found for Kitchen operation');
            return Command::FAILURE;
        }
        
        $currentCount = $assignment->assignmentMembers()->count();
        $this->info("Current members assigned: {$currentCount}");
        $this->info("Required: 9 students (5 male, 4 female)");
        
        if ($currentCount >= 9) {
            $this->info('✅ Assignment already has enough members!');
            return Command::SUCCESS;
        }
        
        $this->warn("⚠️ Only {$currentCount} members assigned. Need to run auto-shuffle to fix.");
        $this->info('Running auto-shuffle...');
        
        try {
            // Trigger auto-shuffle via HTTP request
            $response = Http::post(url('/assignments/auto-shuffle'));
            
            if ($response->successful()) {
                $this->info('✅ Auto-shuffle completed successfully!');
                $this->info('Please check the Kitchen operation assignments now.');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Auto-shuffle failed: ' . $response->body());
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
