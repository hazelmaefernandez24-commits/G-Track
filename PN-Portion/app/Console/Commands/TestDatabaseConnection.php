<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test database connection';

    public function handle()
    {
        try {
            DB::connection()->getPdo();
            $this->info('Successfully connected to the database: ' . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('Could not connect to the database. Error: ' . $e->getMessage());
        }
    }
} 