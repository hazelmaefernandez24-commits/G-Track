<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckRequirements extends Command
{
    protected $signature = 'check:requirements';
    protected $description = 'Check session requirements for Kitchen operation';

    public function handle()
    {
        $this->info('=== CHECKING REQUIREMENTS ===');
        $this->newLine();
        
        $overrides = session('auto_shuffle_overrides', []);
        
        if (empty($overrides)) {
            $this->warn('⚠️ No session overrides found!');
            return Command::FAILURE;
        }
        
        $this->info('Session Overrides:');
        foreach ($overrides as $categoryName => $data) {
            $this->info("Category: {$categoryName}");
            
            if (isset($data['batch_requirements'])) {
                $this->info('  Batch Requirements:');
                foreach ($data['batch_requirements'] as $batch => $reqs) {
                    $boys = $reqs['boys'] ?? 0;
                    $girls = $reqs['girls'] ?? 0;
                    $total = $boys + $girls;
                    $this->info("    Batch {$batch}: {$boys} boys + {$girls} girls = {$total} total");
                }
            }
            
            if (isset($data['max_total'])) {
                $this->info("  Max Total: {$data['max_total']}");
            }
            
            $this->newLine();
        }
        
        return Command::SUCCESS;
    }
}
