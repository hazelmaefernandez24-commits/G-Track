<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Violation;
use Carbon\Carbon;

class ResolveExpiredConsequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consequences:resolve-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically resolve consequences that have expired based on their duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired consequences...');

        // Get all violations with active consequences that have expired
        $expiredViolations = Violation::expiredConsequences()->get();

        if ($expiredViolations->isEmpty()) {
            $this->info('No expired consequences found.');
            return 0;
        }

        $resolvedCount = 0;

        foreach ($expiredViolations as $violation) {
            try {
                $violation->resolveConsequence();
                $resolvedCount++;

                $this->line("Resolved consequence for violation ID {$violation->id} - Student: {$violation->student_id}");
            } catch (\Exception $e) {
                $this->error("Failed to resolve consequence for violation ID {$violation->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully resolved {$resolvedCount} expired consequences.");

        return 0;
    }
}
