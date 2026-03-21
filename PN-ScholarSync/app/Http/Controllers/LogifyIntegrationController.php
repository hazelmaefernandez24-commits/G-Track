<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LogifyApiService;
use App\Models\LogifyLateRecord;
use App\Models\LogifyAbsentRecord;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\StudentDetails;

class LogifyIntegrationController extends Controller
{
    protected $logifyService;

    public function __construct(LogifyApiService $logifyService)
    {
        $this->logifyService = $logifyService;
    }

    /**
     * Main sync method - fetches data from Logify and creates violations
     */
    public function syncLogifyData()
    {
        try {
            Log::info('LogifyIntegration: Starting sync process');

            $syncBatchId = uniqid('sync_', true);
            $violationsCreated = 0;
            $errors = [];

            // Get current month/year for sync
            $currentMonth = now()->format('m');
            $currentYear = now()->format('Y');

            // Sync late students
            $lateData = $this->logifyService->getLateStudents($currentMonth, $currentYear);
            if ($lateData) {
                $result = $this->processLateStudents($lateData, $syncBatchId);
                $violationsCreated += $result['violations_created'];
                $errors = array_merge($errors, $result['errors']);
            } else {
                $errors[] = 'Failed to fetch late students data from Logify';
            }

            // Sync absent students
            $absentData = $this->logifyService->getAbsentStudents($currentMonth, $currentYear);
            if ($absentData) {
                $result = $this->processAbsentStudents($absentData, $syncBatchId);
                $violationsCreated += $result['violations_created'];
                $errors = array_merge($errors, $result['errors']);
            } else {
                $errors[] = 'Failed to fetch absent students data from Logify';
            }

            // Update last sync timestamp
            $this->logifyService->updateLastSyncTimestamp();

            Log::info('LogifyIntegration: Sync completed', [
                'violations_created' => $violationsCreated,
                'errors_count' => count($errors),
                'sync_batch_id' => $syncBatchId
            ]);

            return [
                'success' => true,
                'data' => [
                    'late_students_processed' => count($lateData['late_students'] ?? []),
                    'absent_students_processed' => count($absentData['absent_students'] ?? []),
                    'violations_created' => $violationsCreated,
                    'errors' => $errors,
                    'sync_batch_id' => $syncBatchId
                ]
            ];

        } catch (\Exception $e) {
            Log::error('LogifyIntegration: Sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process late students data and create violations
     */
    public function processLateStudents($lateData, $syncBatchId)
    {
        $violationsCreated = 0;
        $errors = [];

        // Get the specific violation types
        $academicLoginLateType = ViolationType::where('violation_name', 'Academic Login Late')->first();
        $academicLogoutLateType = ViolationType::where('violation_name', 'Academic Logout Late')->first();
        $goingOutLoginLateType = ViolationType::where('violation_name', 'Going-out Login Late')->first();

        if (!$academicLoginLateType || !$academicLogoutLateType || !$goingOutLoginLateType) {
            $errors[] = 'One or more late violation types not found in database';
            return ['violations_created' => 0, 'errors' => $errors];
        }

        foreach ($lateData['late_students'] as $lateStudent) {
            try {
                // Find the student in PN-ScholarSync
                $studentData = StudentDetails::where('student_id', $lateStudent['student_id'])->first();
                
                if (!$studentData) {
                    $errors[] = "Student not found: {$lateStudent['student_id']}";
                    continue;
                }

                // Store late record
                $lateRecord = LogifyLateRecord::updateOrCreate(
                    [
                        'student_id' => $lateStudent['student_id'],
                        'month' => now()->format('m'),
                        'year' => now()->format('Y')
                    ],
                    [
                        'first_name' => $lateStudent['first_name'],
                        'last_name' => $lateStudent['last_name'],
                        'batch' => $lateStudent['batch'],
                        'group' => $lateStudent['group'] ?? null,
                        'total_late_count' => $lateStudent['total_late_count'],
                        'sync_batch_id' => $syncBatchId,
                        'last_synced_at' => now()
                    ]
                );

                // Create violations for each type of late incident

                // Academic Login Late violations
                if (isset($lateStudent['login_late_count']) && $lateStudent['login_late_count'] > 0) {
                    $violationsCreated += $this->createSpecificViolations(
                        $studentData,
                        $academicLoginLateType,
                        $lateStudent['login_late_count'],
                        'Academic Login Late',
                        $syncBatchId
                    );
                }

                // Academic Logout Late violations
                if (isset($lateStudent['logout_late_count']) && $lateStudent['logout_late_count'] > 0) {
                    $violationsCreated += $this->createSpecificViolations(
                        $studentData,
                        $academicLogoutLateType,
                        $lateStudent['logout_late_count'],
                        'Academic Logout Late',
                        $syncBatchId
                    );
                }

                // Going-out Login Late violations
                if (isset($lateStudent['going_out_late_count']) && $lateStudent['going_out_late_count'] > 0) {
                    $violationsCreated += $this->createSpecificViolations(
                        $studentData,
                        $goingOutLoginLateType,
                        $lateStudent['going_out_late_count'],
                        'Going-out Login Late',
                        $syncBatchId
                    );
                }

                Log::info('LogifyIntegration: Processed late student', [
                    'student_id' => $lateStudent['student_id'],
                    'login_late_count' => $lateStudent['login_late_count'] ?? 0,
                    'logout_late_count' => $lateStudent['logout_late_count'] ?? 0,
                    'going_out_late_count' => $lateStudent['going_out_late_count'] ?? 0,
                    'total_late_count' => $lateStudent['total_late_count']
                ]);

            } catch (\Exception $e) {
                $errors[] = "Error processing late student {$lateStudent['student_id']}: {$e->getMessage()}";
                Log::error('LogifyIntegration: Error processing late student', [
                    'student_id' => $lateStudent['student_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['violations_created' => $violationsCreated, 'errors' => $errors];
    }

    /**
     * Process absent students data and create violations
     */
    public function processAbsentStudents($absentData, $syncBatchId)
    {
        $violationsCreated = 0;
        $errors = [];

        // Get the "Academic Absent" violation type
        $academicAbsentType = ViolationType::where('violation_name', 'Academic Absent')->first();
        if (!$academicAbsentType) {
            $errors[] = 'Academic Absent violation type not found in database';
            return ['violations_created' => 0, 'errors' => $errors];
        }

        foreach ($absentData['absent_students'] as $absentStudent) {
            try {
                // Find the student in PN-ScholarSync
                $studentData = StudentDetails::where('student_id', $absentStudent['student_id'])->first();
                
                if (!$studentData) {
                    $errors[] = "Student not found: {$absentStudent['student_id']}";
                    continue;
                }

                // Store absent record
                $absentRecord = LogifyAbsentRecord::updateOrCreate(
                    [
                        'student_id' => $absentStudent['student_id'],
                        'month' => now()->format('m'),
                        'year' => now()->format('Y')
                    ],
                    [
                        'first_name' => $absentStudent['first_name'],
                        'last_name' => $absentStudent['last_name'],
                        'batch' => $absentStudent['batch'],
                        'group' => $absentStudent['group'] ?? null,
                        'academic_absent_count' => $absentStudent['academic_absent_count'],
                        'sync_batch_id' => $syncBatchId,
                        'last_synced_at' => now()
                    ]
                );

                // Create Academic Absent violations
                $violationsCreated += $this->createSpecificViolations(
                    $studentData,
                    $academicAbsentType,
                    $absentStudent['academic_absent_count'],
                    'Academic Absent',
                    $syncBatchId
                );

                Log::info('LogifyIntegration: Processed absent student', [
                    'student_id' => $absentStudent['student_id'],
                    'academic_absent_count' => $absentStudent['academic_absent_count']
                ]);

            } catch (\Exception $e) {
                $errors[] = "Error processing absent student {$absentStudent['student_id']}: {$e->getMessage()}";
                Log::error('LogifyIntegration: Error processing absent student', [
                    'student_id' => $absentStudent['student_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return ['violations_created' => $violationsCreated, 'errors' => $errors];
    }

    /**
     * Get sync status and statistics
     */
    public function getSyncStatus()
    {
        try {
            $lateRecordsCount = LogifyLateRecord::count();
            $absentRecordsCount = LogifyAbsentRecord::count();
            $recentLateRecords = LogifyLateRecord::where('last_synced_at', '>=', now()->subDays(7))->count();
            $recentAbsentRecords = LogifyAbsentRecord::where('last_synced_at', '>=', now()->subDays(7))->count();
            $lastSync = LogifyLateRecord::latest('last_synced_at')->first()?->last_synced_at;

            $data = [
                'last_sync' => $lastSync ? $lastSync->format('Y-m-d H:i:s') : 'Never',
                'connection_status' => $this->logifyService->testConnection(),
                'late_records_count' => $lateRecordsCount,
                'absent_records_count' => $absentRecordsCount,
                'recent_late_records' => $recentLateRecords,
                'recent_absent_records' => $recentAbsentRecords
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('LogifyIntegration: Failed to get sync status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create specific violations for a student and violation type
     */
    protected function createSpecificViolations($studentData, $violationType, $count, $description, $syncBatchId)
    {
        $violationsCreated = 0;

        // Get current month and year for better duplicate detection
        $currentMonth = now()->format('m');
        $currentYear = now()->format('Y');

        // Check existing violations for this student, violation type, and current month/year
        $existingViolations = Violation::where('student_id', $studentData->student_id)
            ->where('violation_type_id', $violationType->id)
            ->whereNotNull('logify_sync_batch_id')
            ->whereYear('violation_date', $currentYear)
            ->whereMonth('violation_date', $currentMonth)
            ->count();

        $newIncidents = max(0, $count - $existingViolations);

        // Only create violations if there are new incidents
        if ($newIncidents > 0) {
            // Create violations for new incidents
            for ($i = 0; $i < $newIncidents; $i++) {
                $violation = new Violation([
                    'student_id' => $studentData->student_id,
                    'violation_type_id' => $violationType->id,
                    'violation_date' => now()->toDateString(),
                    'incident_details' => "{$description} incident from Logify import - Month: {$currentMonth}/{$currentYear}, Total Count: {$count}",
                    'severity' => $violationType->severity,
                    'status' => 'active',
                    'consequence' => 'To be assigned by educator',
                    'penalty' => $violationType->default_penalty,
                    'logify_sync_batch_id' => $syncBatchId,
                ]);

                $violation->save();
                $violationsCreated++;
            }
        }

        Log::info("LogifyIntegration: Processed {$description} violations", [
            'student_id' => $studentData->student_id,
            'violation_type' => $violationType->violation_name,
            'total_count' => $count,
            'existing_violations' => $existingViolations,
            'new_violations_created' => $violationsCreated,
            'month_year' => "{$currentMonth}/{$currentYear}"
        ]);

        return $violationsCreated;
    }
}
