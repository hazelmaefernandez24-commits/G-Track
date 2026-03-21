<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearInventoryTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:clear-test-data {--force : Force clear without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all test inventory data to start fresh';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('force') || $this->confirm('This will delete ALL inventory data. Are you sure?')) {
            $this->info('Clearing inventory test data...');

            try {
                // Clear in correct order due to foreign key constraints
                \DB::table('inventory_check_items')->delete();
                \DB::table('inventory_checks')->delete();
                \DB::table('inventory')->delete();

                // Clear related notifications
                \DB::table('notifications')->where('type', 'inventory_update')->delete();
                \DB::table('notifications')->where('type', 'inventory_check')->delete();

                $this->info('All inventory test data cleared successfully!');
                $this->info('The system is now ready for fresh kitchen reports.');
            } catch (\Exception $e) {
                $this->error('Error clearing data: ' . $e->getMessage());
            }
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
