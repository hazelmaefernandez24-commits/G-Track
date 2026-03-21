<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use App\Models\Severity;
use Illuminate\Support\Facades\Log;

class SetupLogifyViolationTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logify:setup-violation-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the required violation types for Logify integration (Late and Absent)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Setting up Logify violation types...');

        try {
            // Get or create offense category for schedule
            $scheduleCategory = OffenseCategory::firstOrCreate(
                ['category_name' => 'Schedule'],
                ['description' => 'Schedule-related violations from Logify integration']
            );

            // Get default severity (assuming Low severity exists)
            $defaultSeverity = Severity::where('severity_name', 'Low')->first();
            if (!$defaultSeverity) {
                $defaultSeverity = Severity::first(); // Use any available severity
            }

            if (!$defaultSeverity) {
                $this->error('❌ No severity levels found in database. Please create severity levels first.');
                return Command::FAILURE;
            }

            // Create specific violation types for each late type
            $academicLoginLateType = ViolationType::firstOrCreate(
                ['violation_name' => 'Academic Login Late'],
                [
                    'offense_category_id' => $scheduleCategory->id,
                    'description' => 'Student late login to academic session - imported from Logify system',
                    'default_penalty' => 'VW', // Verbal Warning
                    'severity_id' => $defaultSeverity->id
                ]
            );

            $academicLogoutLateType = ViolationType::firstOrCreate(
                ['violation_name' => 'Academic Logout Late'],
                [
                    'offense_category_id' => $scheduleCategory->id,
                    'description' => 'Student late logout from academic session - imported from Logify system',
                    'default_penalty' => 'VW', // Verbal Warning
                    'severity_id' => $defaultSeverity->id
                ]
            );

            $goingOutLoginLateType = ViolationType::firstOrCreate(
                ['violation_name' => 'Going-out Login Late'],
                [
                    'offense_category_id' => $scheduleCategory->id,
                    'description' => 'Student late login from going out - imported from Logify system',
                    'default_penalty' => 'VW', // Verbal Warning
                    'severity_id' => $defaultSeverity->id
                ]
            );

            $academicAbsentType = ViolationType::firstOrCreate(
                ['violation_name' => 'Academic Absent'],
                [
                    'offense_category_id' => $scheduleCategory->id,
                    'description' => 'Student absent from academic session - imported from Logify system',
                    'default_penalty' => 'VW', // Verbal Warning
                    'severity_id' => $defaultSeverity->id
                ]
            );

            $this->info('✅ Violation types setup completed!');
            
            $this->table(
                ['Violation Type', 'ID', 'Category', 'Default Penalty', 'Status'],
                [
                    [
                        'Academic Login Late',
                        $academicLoginLateType->id,
                        $scheduleCategory->category_name,
                        $academicLoginLateType->default_penalty,
                        $academicLoginLateType->wasRecentlyCreated ? 'Created' : 'Already exists'
                    ],
                    [
                        'Academic Logout Late',
                        $academicLogoutLateType->id,
                        $scheduleCategory->category_name,
                        $academicLogoutLateType->default_penalty,
                        $academicLogoutLateType->wasRecentlyCreated ? 'Created' : 'Already exists'
                    ],
                    [
                        'Going-out Login Late',
                        $goingOutLoginLateType->id,
                        $scheduleCategory->category_name,
                        $goingOutLoginLateType->default_penalty,
                        $goingOutLoginLateType->wasRecentlyCreated ? 'Created' : 'Already exists'
                    ],
                    [
                        'Academic Absent',
                        $academicAbsentType->id,
                        $scheduleCategory->category_name,
                        $academicAbsentType->default_penalty,
                        $academicAbsentType->wasRecentlyCreated ? 'Created' : 'Already exists'
                    ]
                ]
            );

            Log::info('Logify violation types setup completed', [
                'academic_login_late_id' => $academicLoginLateType->id,
                'academic_logout_late_id' => $academicLogoutLateType->id,
                'going_out_login_late_id' => $goingOutLoginLateType->id,
                'academic_absent_id' => $academicAbsentType->id,
                'category_id' => $scheduleCategory->id
            ]);

            $this->info('🎯 You can now run the Logify import command to create violations!');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('💥 Failed to setup violation types: ' . $e->getMessage());
            Log::error('Failed to setup Logify violation types', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
