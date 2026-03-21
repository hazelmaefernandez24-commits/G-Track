<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class TestAutoShuffle extends Command
{
    protected $signature = 'test:auto-shuffle';
    protected $description = 'Test auto-shuffle requirements reading';

    public function handle()
    {
        $this->info('=== AUTO-SHUFFLE REQUIREMENTS TEST ===');
        $this->newLine();
        
        $category = Category::where('name', 'LIKE', '%Kitchen operation%')->first();
        
        if (!$category) {
            $this->error('Kitchen operation not found!');
            return Command::FAILURE;
        }
        
        $this->info("Category: {$category->name}");
        $this->info("Category ID: {$category->id}");
        $this->info("Category Capacity: " . ($category->capacity ?? 'NULL'));
        $this->newLine();
        
        // Simulate what auto-shuffle does
        $overrides = session('auto_shuffle_overrides', []);
        
        $this->info('📋 Session Overrides:');
        if (empty($overrides)) {
            $this->warn('  ⚠️ No session overrides found!');
        } else {
            foreach ($overrides as $catName => $data) {
                $this->info("  Category: {$catName}");
                if (isset($data['batch_requirements'])) {
                    foreach ($data['batch_requirements'] as $batch => $reqs) {
                        $boys = $reqs['boys'] ?? 0;
                        $girls = $reqs['girls'] ?? 0;
                        $this->info("    Batch {$batch}: {$boys} boys + {$girls} girls");
                    }
                }
            }
        }
        
        $this->newLine();
        
        // Check what auto-shuffle would use
        $batchRequirements = null;
        
        if (isset($overrides[$category->name]['batch_requirements'])) {
            $batchRequirements = $overrides[$category->name]['batch_requirements'];
            $this->info('✅ Would use: Session requirements');
        } else {
            // Try flexible matching
            foreach ($overrides as $overrideKey => $overrideData) {
                if (isset($overrideData['batch_requirements'])) {
                    $normalizedCategoryName = strtolower(preg_replace('/[^a-z0-9]/', '', $category->name));
                    $normalizedOverrideKey = strtolower(preg_replace('/[^a-z0-9]/', '', $overrideKey));
                    
                    if ($normalizedCategoryName === $normalizedOverrideKey || 
                        strpos($normalizedOverrideKey, $normalizedCategoryName) !== false ||
                        strpos($normalizedCategoryName, $normalizedOverrideKey) !== false) {
                        $batchRequirements = $overrideData['batch_requirements'];
                        $this->info("✅ Would use: Flexible match with '{$overrideKey}'");
                        break;
                    }
                }
            }
            
            // Fallback to capacity
            if (!$batchRequirements && isset($category->capacity) && $category->capacity > 0) {
                $totalCapacity = (int)$category->capacity;
                $perBatch = (int)ceil($totalCapacity / 2);
                $perBatchBoys = (int)ceil($perBatch / 2);
                $perBatchGirls = $perBatch - $perBatchBoys;
                
                $batchRequirements = [
                    2025 => ['boys' => $perBatchBoys, 'girls' => $perBatchGirls],
                    2026 => ['boys' => $perBatchBoys, 'girls' => $perBatchGirls]
                ];
                $this->info("✅ Would use: Category capacity fallback ({$totalCapacity} total)");
            }
        }
        
        if ($batchRequirements) {
            $this->newLine();
            $this->info('📊 Requirements that would be used:');
            $total = 0;
            foreach ($batchRequirements as $batch => $reqs) {
                $boys = $reqs['boys'] ?? 0;
                $girls = $reqs['girls'] ?? 0;
                $batchTotal = $boys + $girls;
                $total += $batchTotal;
                $this->info("  Batch {$batch}: {$boys} boys + {$girls} girls = {$batchTotal} total");
            }
            $this->info("  TOTAL: {$total} students");
        } else {
            $this->warn('⚠️ No requirements found! Would use dynamic assignment.');
        }
        
        return Command::SUCCESS;
    }
}
