<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DummyGradesSeeder;

class SeedDummyGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:dummy-grades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed dummy grades and proofs for University of San Jose Recoletos students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to seed dummy grades for University of San Jose Recoletos...');

        $seeder = new DummyGradesSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->info('Dummy grades seeding completed!');

        return 0;
    }
}
