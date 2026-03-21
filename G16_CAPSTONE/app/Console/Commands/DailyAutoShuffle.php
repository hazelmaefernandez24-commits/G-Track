<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DailyAutoShuffle extends Command
{
    protected $signature = 'assignments:daily-shuffle';
    protected $description = 'Run auto-shuffle daily based on assignment duration settings';

    public function handle()
    {
        $this->info('Checking if daily auto-shuffle should run...');
        
        // Get the assignment duration setting
        $durationDays = SystemSetting::get('assignment_duration_days', 7);
        
        $this->info("Current assignment duration: {$durationDays} days");
        
        // If duration is 0, auto-shuffle is manual only (anytime)
        if ($durationDays == 0) {
            $this->info('⏸️  Duration is set to 0 (anytime) - auto-shuffle is manual only');
            return Command::SUCCESS;
        }
        
        // If duration is 1, run daily auto-shuffle
        if ($durationDays == 1) {
            $this->info('🔄 Duration is 1 day - running daily auto-shuffle...');
            return $this->runAutoShuffle();
        }
        
        // For other durations, check if any current assignment has reached its end date
        $currentAssignments = Assignment::where('status', 'current')->get();
        
        if ($currentAssignments->isEmpty()) {
            $this->info('No current assignments found - running auto-shuffle...');
            return $this->runAutoShuffle();
        }
        
        $now = Carbon::now('Asia/Manila');
        $shouldShuffle = false;
        
        foreach ($currentAssignments as $assignment) {
            $endDate = Carbon::parse($assignment->end_date, 'Asia/Manila')->endOfDay();
            
            if ($now->gte($endDate)) {
                $this->info("Assignment #{$assignment->id} has reached end date ({$assignment->end_date})");
                $shouldShuffle = true;
                break;
            }
        }
        
        if ($shouldShuffle) {
            $this->info('🔄 At least one assignment has reached end date - running auto-shuffle...');
            return $this->runAutoShuffle();
        } else {
            $this->info('⏸️  No assignments have reached their end date yet - skipping shuffle');
            return Command::SUCCESS;
        }
    }
    
    private function runAutoShuffle()
    {
        try {
            // Call the auto-shuffle endpoint
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post(url('/assignments/auto-shuffle'), [
                'automated' => true
            ]);
            
            if ($response->successful()) {
                $this->info('✅ Auto-shuffle completed successfully!');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Auto-shuffle failed: ' . $response->body());
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error running auto-shuffle: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
