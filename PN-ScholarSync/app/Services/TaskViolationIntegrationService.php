<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use App\Models\User;
use Carbon\Carbon;

class TaskViolationIntegrationService
{
    /**
     * Sync invalid task submissions from G16_CAPSTONE to PN-ScholarSync violations
     */
    public function syncInvalidTaskSubmissions()
    {
        try {
            Log::info('Starting sync of invalid task submissions to violations');
            
            // Connect to G16_CAPSTONE database
            $g16Database = config('database.connections.g16_capstone.database', 'g16_capstone');
            
            // Get invalid task submissions from G16_CAPSTONE
            $invalidSubmissions = DB::connection('g16_capstone')
                ->table('task_submissions')
                ->where('status', 'invalid')
                ->whereNotExists(function ($query) {
                    // Don't sync submissions that have already been converted to violations
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.task_submission_id = task_submissions.id');
                })
                ->get();

            Log::info('Found ' . $invalidSubmissions->count() . ' invalid task submissions to sync');

            $syncedCount = 0;
            $errors = [];

            foreach ($invalidSubmissions as $submission) {
                try {
                    $violationCreated = $this->createViolationFromTaskSubmission($submission);
                    if ($violationCreated) {
                        $syncedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync submission ID {$submission->id}: " . $e->getMessage();
                    Log::error("Error syncing task submission {$submission->id}: " . $e->getMessage());
                }
            }

            Log::info("Sync completed: {$syncedCount} violations created, " . count($errors) . " errors");

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'total_found' => $invalidSubmissions->count(),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('Error in syncInvalidTaskSubmissions: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a violation record from an invalid task submission
     */
    private function createViolationFromTaskSubmission($submission)
    {
        try {
            // Get student information from G16_CAPSTONE
            $studentInfo = $this->getStudentInfoFromG16($submission->user_id);
            
            if (!$studentInfo) {
                Log::warning("Could not find student info for user_id: {$submission->user_id}");
                return false;
            }

            // Find or create the corresponding user in PN-ScholarSync
            $localUser = $this->findOrCreateLocalUser($studentInfo);
            
            if (!$localUser) {
                Log::warning("Could not create/find local user for: {$studentInfo->user_fname} {$studentInfo->user_lname}");
                return false;
            }

            // Get or create violation type for task non-compliance
            $violationType = $this->getTaskNonComplianceViolationType($submission->task_category);
            
            if (!$violationType) {
                Log::warning("Could not create violation type for task category: {$submission->task_category}");
                return false;
            }

            // Create the violation record
            $violation = Violation::create([
                'student_id' => $studentInfo->student_id ?? $localUser->id,
                'gender' => $studentInfo->gender ?? 'unknown',
                'violation_date' => Carbon::parse($submission->validated_at ?? $submission->created_at)->format('Y-m-d'),
                'violation_type_id' => $violationType->id,
                'severity' => 'Low', // Task non-compliance is typically low severity
                'offense' => 'Task non-compliance',
                'penalty' => 'VW', // Verbal Warning for task issues
                'consequence' => $this->generateConsequenceText($submission),
                'status' => 'active',
                'incident_details' => $submission->description,
                'prepared_by' => $this->getValidatorName($submission),
                'action_taken' => true,
                'task_submission_id' => $submission->id, // Link back to original submission
                'created_at' => Carbon::parse($submission->validated_at ?? $submission->created_at),
                'updated_at' => now()
            ]);

            Log::info("Created violation ID {$violation->id} for student {$studentInfo->user_fname} {$studentInfo->user_lname} (Task: {$submission->task_category})");

            return $violation;

        } catch (\Exception $e) {
            Log::error("Error creating violation from task submission: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student information from G16_CAPSTONE database
     */
    private function getStudentInfoFromG16($userId)
    {
        try {
            // Get user info from pnph_users table in G16_CAPSTONE
            $userInfo = DB::connection('g16_capstone')
                ->table('pnph_users')
                ->where('user_id', $userId)
                ->first();

            if (!$userInfo) {
                return null;
            }

            // Get student details if available
            $studentDetail = DB::connection('g16_capstone')
                ->table('student_details')
                ->where('user_id', $userId)
                ->first();

            // Combine user and student detail info
            $studentInfo = (object) [
                'user_id' => $userInfo->user_id,
                'user_fname' => $userInfo->user_fname,
                'user_lname' => $userInfo->user_lname,
                'user_email' => $userInfo->user_email,
                'gender' => $userInfo->gender,
                'student_id' => $studentDetail->student_id ?? null,
                'batch' => $studentDetail->batch ?? null
            ];

            return $studentInfo;

        } catch (\Exception $e) {
            Log::error("Error getting student info from G16: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find or create local user in PN-ScholarSync
     */
    private function findOrCreateLocalUser($studentInfo)
    {
        try {
            // Try to find existing user by email or name
            $localUser = User::where('email', $studentInfo->user_email)
                ->orWhere(function($query) use ($studentInfo) {
                    $query->where('name', trim($studentInfo->user_fname . ' ' . $studentInfo->user_lname));
                })
                ->first();

            if (!$localUser) {
                // Create new user
                $localUser = User::create([
                    'name' => trim($studentInfo->user_fname . ' ' . $studentInfo->user_lname),
                    'email' => $studentInfo->user_email ?? 'student_' . $studentInfo->user_id . '@pn.edu.ph',
                    'password' => bcrypt('temporary_password'), // They'll need to reset
                    'user_role' => 'student',
                    'g16_user_id' => $studentInfo->user_id, // Link to G16 system
                    'student_id' => $studentInfo->student_id,
                    'batch' => $studentInfo->batch
                ]);

                Log::info("Created new local user: {$localUser->name} (ID: {$localUser->id})");
            }

            return $localUser;

        } catch (\Exception $e) {
            Log::error("Error finding/creating local user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get or create violation type for task non-compliance
     */
    private function getTaskNonComplianceViolationType($taskCategory)
    {
        try {
            // Find or create "Center Tasking" offense category
            $offenseCategory = OffenseCategory::firstOrCreate(
                ['category_name' => 'Center Tasking'],
                ['description' => 'Violations related to center task assignments and responsibilities']
            );

            // Create specific violation type for the task category
            $violationName = "Non-compliance with {$taskCategory} task assignment";
            
            $violationType = ViolationType::firstOrCreate(
                [
                    'violation_name' => $violationName,
                    'offense_category_id' => $offenseCategory->id
                ],
                [
                    'description' => "Student failed to properly complete assigned {$taskCategory} tasks",
                    'default_penalty' => 'VW',
                    'severity_id' => $this->getLowSeverityId()
                ]
            );

            return $violationType;

        } catch (\Exception $e) {
            Log::error("Error getting/creating violation type: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Low severity ID
     */
    private function getLowSeverityId()
    {
        try {
            $severity = \App\Models\Severity::firstOrCreate(
                ['severity_name' => 'Low'],
                ['description' => 'Low severity violations']
            );
            return $severity->id;
        } catch (\Exception $e) {
            return 1; // Default fallback
        }
    }

    /**
     * Generate consequence text based on task submission
     */
    private function generateConsequenceText($submission)
    {
        $taskCategory = ucfirst($submission->task_category);
        $baseConsequence = "Student must complete additional {$taskCategory} tasks and demonstrate proper task completion procedures.";
        
        if ($submission->description) {
            $baseConsequence .= " Issue details: " . $submission->description;
        }

        return $baseConsequence;
    }

    /**
     * Get validator name from submission
     */
    private function getValidatorName($submission)
    {
        try {
            if ($submission->validated_by) {
                $validator = DB::connection('g16_capstone')
                    ->table('pnph_users')
                    ->where('user_id', $submission->validated_by)
                    ->first();
                
                if ($validator) {
                    return trim($validator->user_fname . ' ' . $validator->user_lname);
                }
            }

            return 'System Administrator';
        } catch (\Exception $e) {
            return 'System Administrator';
        }
    }

    /**
     * Get all invalid submissions for display
     */
    public function getInvalidSubmissionsWithStudentNames()
    {
        try {
            $invalidSubmissions = DB::connection('g16_capstone')
                ->table('task_submissions as ts')
                ->join('pnph_users as u', 'ts.user_id', '=', 'u.user_id')
                ->leftJoin('student_details as sd', 'ts.user_id', '=', 'sd.user_id')
                ->where('ts.status', 'invalid')
                ->select(
                    'ts.*',
                    'u.user_fname',
                    'u.user_lname',
                    'u.gender',
                    'sd.student_id',
                    'sd.batch'
                )
                ->orderBy('ts.validated_at', 'desc')
                ->get()
                ->map(function($submission) {
                    return [
                        'id' => $submission->id,
                        'student_name' => trim($submission->user_fname . ' ' . $submission->user_lname),
                        'student_id' => $submission->student_id,
                        'task_category' => $submission->task_category,
                        'description' => $submission->description,
                        'validated_at' => $submission->validated_at,
                        'batch' => $submission->batch,
                        'gender' => $submission->gender
                    ];
                });

            return $invalidSubmissions;

        } catch (\Exception $e) {
            Log::error("Error getting invalid submissions: " . $e->getMessage());
            return collect();
        }
    }
}
