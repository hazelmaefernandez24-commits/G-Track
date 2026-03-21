<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KitchenMenuPoll;
use Carbon\Carbon;

class UpdateExpiredPolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'polls:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update polls that have passed their deadline to expired status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired polls...');

        // Get all active polls that have passed their deadline
        $expiredPolls = KitchenMenuPoll::whereIn('status', ['active', 'sent'])
            ->where(function ($query) {
                $query->where('poll_date', '<', now()->format('Y-m-d'))
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('poll_date', '=', now()->format('Y-m-d'))
                            ->whereRaw('TIME(deadline) < TIME(?)', [now()->format('H:i:s')]);
                    });
            })
            ->get();

        $count = $expiredPolls->count();

        if ($count === 0) {
            $this->info('No expired polls found.');
            return 0;
        }

        // Update expired polls
        foreach ($expiredPolls as $poll) {
            $poll->update(['status' => 'expired']);
            $this->line("Poll #{$poll->id} ({$poll->meal_name}) marked as expired");
        }

        $this->info("Successfully updated {$count} expired poll(s).");
        return 0;
    }
}
