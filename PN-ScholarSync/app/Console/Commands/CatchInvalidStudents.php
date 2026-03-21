<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvalidStudentCatcher;

class CatchInvalidStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catch:invalid-students 
                            {--show : Show what would be caught without actually catching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Catch and store invalid students from G16_CAPSTONE task submissions';

    protected $catcher;

    public function __construct(InvalidStudentCatcher $catcher)
    {
        parent::__construct();
        $this->catcher = $catcher;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎣 Starting Invalid Student Catcher...');
        $this->newLine();

        try {
            if ($this->option('show')) {
                $this->showInvalidStudents();
            } else {
                $this->catchInvalidStudents();
            }

        } catch (\Exception $e) {
            $this->error('❌ Catcher failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function showInvalidStudents()
    {
        $this->info('👀 Showing invalid students (no catching)...');
        
        // Get already caught students
        $caughtStudents = $this->catcher->getCaughtInvalidStudents();
        
        if ($caughtStudents->count() > 0) {
            $this->info("📋 Already caught {$caughtStudents->count()} invalid students:");
            $this->newLine();

            $tableData = $caughtStudents->take(10)->map(function($student) {
                return [
                    $student->student_name,
                    $student->student_id_code ?? 'N/A',
                    ucfirst($student->task_category),
                    $student->status,
                    \Carbon\Carbon::parse($student->caught_at)->format('M d H:i')
                ];
            })->toArray();

            $this->table(
                ['Student Name', 'Student ID', 'Task Category', 'Status', 'Caught At'],
                $tableData
            );

            if ($caughtStudents->count() > 10) {
                $this->info("... and " . ($caughtStudents->count() - 10) . " more students");
            }
        } else {
            $this->info('📭 No invalid students caught yet.');
        }

        $this->newLine();
        $this->info('🚀 Run without --show to catch new invalid students from G16_CAPSTONE.');
    }

    private function catchInvalidStudents()
    {
        $this->info('🎣 Catching invalid students from G16_CAPSTONE...');
        
        $result = $this->catcher->catchInvalidStudents();

        if (!empty($result['success'])) {
            $this->info("✅ Catch completed successfully!");
            $this->info("📊 Results:");
            $totalFound = $result['total_found'] ?? null;
            $count = $result['count'] ?? 0;
            if ($totalFound !== null) {
                $this->info("   • Total found: {$totalFound} invalid submissions");
            }
            $this->info("   • New caught: {$count} students");

            $errors = $result['errors'] ?? [];
            if (!empty($errors)) {
                $this->warn("⚠️  Errors encountered: " . count($errors));
                foreach ($errors as $error) {
                    $this->warn("   • {$error}");
                }
            }

            if ($count > 0) {
                $this->info("🎯 New invalid students are now stored in PN-ScholarSync.");
                $this->info("💻 View them at: /educator/invalid-students");
            } else {
                $this->info("ℹ️  No new invalid students found to catch.");
            }

        } else {
            $error = $result['error'] ?? 'Unknown error';
            $this->error("❌ Catch failed: {$error}");
        }
    }
}
