<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Violation;

class UpdateConsequenceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consequences:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update consequence status for existing violations based on action_taken field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating consequence status for existing violations...');

        $violations = Violation::whereNull('consequence_status')->get();
        $updated = 0;

        foreach ($violations as $violation) {
            if (!$violation->action_taken) {
                // No action taken - consequence is resolved
                $violation->consequence_status = 'resolved';
            } else {
                // Action taken - check if there's a duration
                if ($violation->consequence_duration_value && $violation->consequence_duration_unit) {
                    $violation->consequence_status = 'active';
                    $violation->consequence_start_date = $violation->created_at;
                    $violation->consequence_end_date = $violation->calculateConsequenceEndDate();
                } else {
                    $violation->consequence_status = 'active';
                }
            }

            $violation->save();
            $updated++;
        }

        $this->info("Updated consequence status for {$updated} violations.");

        // Also resolve any expired consequences
        $this->info('Checking for expired consequences...');
        $expiredCount = Violation::where('consequence_status', 'active')
            ->whereNotNull('consequence_end_date')
            ->where('consequence_end_date', '<=', now())
            ->update([
                'consequence_status' => 'resolved'
            ]);

        $this->info("Resolved {$expiredCount} expired consequences.");

        return 0;
    }
}
