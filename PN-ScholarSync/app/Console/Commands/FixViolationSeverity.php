<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViolationType;
use App\Models\Severity;

class FixViolationSeverity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'violations:fix-severity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing severity assignments for violation types';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking and fixing violation type severity assignments...');

        // Ensure severities exist
        $this->ensureSeveritiesExist();

        // Get all severities
        $severities = Severity::all()->keyBy('severity_name');
        
        $this->info('Available severities:');
        foreach ($severities as $severity) {
            $this->line("- {$severity->severity_name} (ID: {$severity->id})");
        }
        $this->newLine();

        // Get all violation types
        $violationTypes = ViolationType::with('severityRelation')->get();
        
        $this->info("Found {$violationTypes->count()} violation types to check.");
        $this->newLine();

        $fixedCount = 0;
        $unknownCount = 0;
        $progressBar = $this->output->createProgressBar($violationTypes->count());
        $progressBar->start();

        foreach ($violationTypes as $type) {
            if (!$type->severity_id) {
                // Try to determine severity based on default_penalty
                $assignedSeverity = null;
                switch ($type->default_penalty) {
                    case 'VW':
                        $assignedSeverity = $severities->get('Low');
                        break;
                    case 'WW':
                        $assignedSeverity = $severities->get('Medium');
                        break;
                    case 'Pro':
                        $assignedSeverity = $severities->get('High');
                        break;
                    case 'T':
                        $assignedSeverity = $severities->get('Very High');
                        break;
                }

                if ($assignedSeverity) {
                    $type->severity_id = $assignedSeverity->id;
                    $type->save();
                    $fixedCount++;
                } else {
                    $unknownCount++;
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Summary:');
        $this->line("- Fixed: {$fixedCount} violation types");
        $this->line("- Unknown penalty codes: {$unknownCount} violation types");
        $this->line("- Total checked: {$violationTypes->count()} violation types");

        if ($fixedCount > 0) {
            $this->info('✅ Severity assignments have been fixed!');
        } else {
            $this->info('✅ All violation types already have proper severity assignments.');
        }

        return Command::SUCCESS;
    }

    /**
     * Ensure that severity records exist in the database
     */
    private function ensureSeveritiesExist()
    {
        $defaultSeverities = [
            'Low',
            'Medium', 
            'High',
            'Very High'
        ];

        foreach ($defaultSeverities as $severityName) {
            Severity::firstOrCreate(['severity_name' => $severityName]);
        }
    }
}

