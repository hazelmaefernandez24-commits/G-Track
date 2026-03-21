<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LogifyApiService;
use App\Services\LogifyDataImportService;

class LogifyIntegrationInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show information about Logify integration configuration and available commands';

    protected $apiService;
    protected $importService;

    public function __construct(LogifyApiService $apiService, LogifyDataImportService $importService)
    {
        parent::__construct();
        $this->apiService = $apiService;
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Logify Integration Configuration');
        $this->line('');

        // Show current configuration
        $apiEnabled = $this->apiService->isApiEnabled();
        $useDbImport = env('LOGIFY_USE_DATABASE_IMPORT', false);
        $testMode = env('LOGIFY_TEST_MODE', false);

        $this->info('📋 Current Settings:');
        $this->line('   • API Integration: ' . ($apiEnabled ? '✅ Enabled' : '❌ Disabled'));
        $this->line('   • Database Import: ' . ($useDbImport ? '✅ Enabled' : '❌ Disabled'));
        $this->line('   • Test Mode: ' . ($testMode ? '✅ Enabled' : '❌ Disabled'));
        $this->line('');

        // Show recommended configuration
        $this->info('🎯 Recommended Configuration:');
        $this->line('   • API Integration: ❌ Disabled (API endpoints not available)');
        $this->line('   • Database Import: ✅ Enabled (Direct database connection)');
        $this->line('   • Test Mode: ❌ Disabled (Use real data)');
        $this->line('');

        // Test connections
        $this->info('🔗 Connection Tests:');
        
        // Test API connection
        if ($apiEnabled) {
            if ($this->apiService->testConnection()) {
                $this->line('   • API Connection: ✅ Working');
            } else {
                $this->line('   • API Connection: ❌ Failed');
            }
        } else {
            $this->line('   • API Connection: ⚠️  Disabled');
        }

        // Test database connection
        if ($this->importService->testConnection()) {
            $this->line('   • Database Connection: ✅ Working');
        } else {
            $this->line('   • Database Connection: ❌ Failed');
        }
        $this->line('');

        // Show available commands
        $this->info('📝 Available Commands:');
        $this->line('');

        if ($useDbImport) {
            $this->info('✅ DATABASE INTEGRATION (Recommended):');
            $this->line('   php artisan logify:import --test --detailed    # Test database connection');
            $this->line('   php artisan logify:import --force --detailed   # Force import data');
            $this->line('   php artisan logify:status                      # Show integration status');
            $this->line('   php artisan logify:cleanup-violations          # Clean up duplicates');
            $this->line('');
        }

        if ($apiEnabled) {
            $this->info('⚠️  API INTEGRATION (Not recommended - endpoints missing):');
            $this->line('   php artisan logify:sync --test --detailed      # Test API connection');
            $this->line('   php artisan logify:sync --force --detailed     # Force API sync');
            $this->line('');
        }

        $this->info('🔧 SETUP & MANAGEMENT:');
        $this->line('   php artisan logify:setup-violation-types       # Setup violation types');
        $this->line('   php artisan schedule:run                       # Run scheduler manually');
        $this->line('   php artisan schedule:list                      # Show scheduled tasks');
        $this->line('');

        // Show current status
        if ($useDbImport && $this->importService->testConnection()) {
            $this->info('🎉 SYSTEM STATUS: Ready for Database Integration!');
            $this->line('');
            $this->info('🚀 Quick Start:');
            $this->line('1. Run: php artisan logify:import --test --detailed');
            $this->line('2. Run: php artisan logify:import --force --detailed');
            $this->line('3. Start scheduler: start-logify-scheduler.bat');
            $this->line('4. Monitor: php artisan logify:status');
        } else {
            $this->warn('⚠️  SYSTEM STATUS: Configuration needed');
            $this->line('');
            $this->info('🔧 Setup Steps:');
            $this->line('1. Configure database connection in .env');
            $this->line('2. Run: php artisan logify:setup-violation-types');
            $this->line('3. Test: php artisan logify:import --test');
        }

        return Command::SUCCESS;
    }
}
