<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Assignment;

class CheckKitchenRequirements extends Command
{
    protected $signature = 'check:kitchen-requirements';
    protected $description = 'Check Kitchen operation requirements and actual assignments';

    public function handle()
    {
        $this->info('=== KITCHEN OPERATION REQUIREMENTS ===');
        $this->newLine();
        
        $category = Category::where('name', 'LIKE', '%Kitchen operation%')->first();
        
        if (!$category) {
            $this->error('Kitchen operation category not found!');
            return Command::FAILURE;
        }
        
        $this->info("Category: {$category->name}");
        $this->info("Capacity: {$category->capacity}");
        $this->newLine();
        
        // Check session overrides
        $overrides = session('auto_shuffle_overrides', []);
        if (isset($overrides[$category->name])) {
            $this->info('📋 Session Requirements:');
            $data = $overrides[$category->name];
            
            if (isset($data['batch_requirements'])) {
                foreach ($data['batch_requirements'] as $batch => $reqs) {
                    $boys = $reqs['boys'] ?? 0;
                    $girls = $reqs['girls'] ?? 0;
                    $total = $boys + $girls;
                    $this->info("  Batch {$batch}: {$boys} boys + {$girls} girls = {$total} total");
                }
            }
            
            if (isset($data['max_total'])) {
                $this->info("  Max Total: {$data['max_total']}");
            }
        } else {
            $this->warn('⚠️ No session requirements found for Kitchen operation');
        }
        
        $this->newLine();
        
        // Check current assignment
        $assignment = Assignment::where('category_id', $category->id)
            ->where('status', 'current')
            ->with('assignmentMembers.student.studentDetail')
            ->first();
        
        if ($assignment) {
            $this->info('👥 Current Assignment:');
            $this->info("  Assignment ID: {$assignment->id}");
            $this->info("  Total Members: {$assignment->assignmentMembers->count()}");
            
            $batch2025 = 0;
            $batch2026 = 0;
            $boys2025 = 0;
            $girls2025 = 0;
            $boys2026 = 0;
            $girls2026 = 0;
            
            foreach ($assignment->assignmentMembers as $member) {
                if ($member->student && $member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch;
                    $gender = $member->student->gender;
                    
                    if ($batch == 2025) {
                        $batch2025++;
                        if ($gender === 'M' || $gender === 'Male') {
                            $boys2025++;
                        } else {
                            $girls2025++;
                        }
                    } elseif ($batch == 2026) {
                        $batch2026++;
                        if ($gender === 'M' || $gender === 'Male') {
                            $boys2026++;
                        } else {
                            $girls2026++;
                        }
                    }
                }
            }
            
            $this->info("  Batch 2025: {$batch2025} students ({$boys2025} boys, {$girls2025} girls)");
            $this->info("  Batch 2026: {$batch2026} students ({$boys2026} boys, {$girls2026} girls)");
        } else {
            $this->warn('⚠️ No current assignment found');
        }
        
        return Command::SUCCESS;
    }
}
