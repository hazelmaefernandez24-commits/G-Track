<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\LogifyLateRecord;
use App\Models\LogifyAbsentRecord;
use App\Services\LogifyDataImportService;
use Carbon\Carbon;

class LogifyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current status of Logify integration';

    protected $importService;

    public function __construct(LogifyDataImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Logify Integration Status');
        $this->line('');

        // Show integration method
        $useDbImport = env('LOGIFY_USE_DATABASE_IMPORT', false);
        $apiEnabled = env('LOGIFY_API_ENABLED', false);

        $this->info('🔧 Integration Method:');
        if ($useDbImport) {
            $this->info('   ✅ DATABASE INTEGRATION (Active)');
            $this->line('   📝 Using direct database connection to Logify');
        }
        if ($apiEnabled) {
            $this->warn('   ⚠️  API INTEGRATION (Enabled but not recommended)');
        }
        if (!$useDbImport && !$apiEnabled) {
            $this->error('   ❌ No integration method enabled');
        }
        $this->line('');

        // Test database connection
        $this->info('🔗 Database Connection:');
        if ($this->importService->testConnection()) {
            $this->info('   ✅ Connected to Logify database');
        } else {
            $this->error('   ❌ Failed to connect to Logify database');
            return Command::FAILURE;
        }
        $this->line('');

        // Show violation types
        $this->info('🏷️  Logify Violation Types:');
        $logifyTypes = ViolationType::whereIn('violation_name', [
            'Late',
            'Absent', 
            'Academic Login Late',
            'Academic Logout Late',
            'Going-out Login Late',
            'Academic Absent'
        ])->get(['id', 'violation_name', 'default_penalty']);

        if ($logifyTypes->isNotEmpty()) {
            foreach ($logifyTypes as $type) {
                $this->line("   • {$type->violation_name} (ID: {$type->id}, Penalty: {$type->default_penalty})");
            }
        } else {
            $this->warn('   ⚠️  No Logify violation types found');
        }
        $this->line('');

        // Show current month statistics
        $currentMonth = now()->format('m');
        $currentYear = now()->format('Y');
        
        $this->info("📈 Current Month Statistics ({$currentMonth}/{$currentYear}):");
        
        // Logify violations count
        $logifyViolations = Violation::whereIn('violation_type_id', $logifyTypes->pluck('id'))
            ->whereNotNull('logify_sync_batch_id')
            ->whereYear('violation_date', $currentYear)
            ->whereMonth('violation_date', $currentMonth)
            ->count();
        
        $this->line("   • Logify Violations Created: {$logifyViolations}");
        
        // Late records
        $lateRecords = LogifyLateRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->count();
        $this->line("   • Late Records Stored: {$lateRecords}");
        
        // Absent records
        $absentRecords = LogifyAbsentRecord::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->count();
        $this->line("   • Absent Records Stored: {$absentRecords}");
        
        $this->line('');

        // Show recent activity
        $this->info('🕒 Recent Activity (Last 24 hours):');
        $recentViolations = Violation::whereIn('violation_type_id', $logifyTypes->pluck('id'))
            ->whereNotNull('logify_sync_batch_id')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        
        $this->line("   • Violations Created: {$recentViolations}");
        
        // Last sync info
        $lastSync = Violation::whereNotNull('logify_sync_batch_id')
            ->latest('created_at')
            ->first();
        
        if ($lastSync) {
            $this->line("   • Last Sync: " . $lastSync->created_at->diffForHumans());
        } else {
            $this->line("   • Last Sync: Never");
        }
        
        $this->line('');

        // Show scheduler status
        $this->info('⏰ Scheduler Status:');
        $this->line('   • Logify import scheduled to run every minute');
        $this->line('   • Check logs: storage/logs/logify-sync.log');
        
        // Check if there are any recent log entries
        $logFile = storage_path('logs/logify-sync.log');
        if (file_exists($logFile)) {
            $lastModified = Carbon::createFromTimestamp(filemtime($logFile));
            $this->line("   • Last log update: " . $lastModified->diffForHumans());
        }

        $this->line('');
        $this->info('✅ Status check completed!');
        
        return Command::SUCCESS;
    }
}
