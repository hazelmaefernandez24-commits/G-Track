<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InvalidStudentCatcher
{
    /**
     * Catch and store invalid students from G16_CAPSTONE
     */
    public function catchInvalidStudents()
    {
        try {
            Log::info('Starting to catch invalid students from G16_CAPSTONE');
            
            // Get invalid task submissions from G16_CAPSTONE
            $invalidSubmissions = $this->getInvalidSubmissionsFromG16();
            
            if ($invalidSubmissions->isEmpty()) {
                Log::info('No invalid submissions found');
                return [
                    'success' => true,
                    'message' => 'No invalid students found',
                    'count' => 0
                ];
            }

            $storedCount = 0;
            $errors = [];

            foreach ($invalidSubmissions as $submission) {
                try {
                    $stored = $this->storeInvalidStudent($submission);
                    if ($stored) {
                        $storedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to store submission ID {$submission->id}: " . $e->getMessage();
                    Log::error("Error storing invalid student: " . $e->getMessage());
                }
            }

            Log::info("Caught and stored {$storedCount} invalid students");

            return [
                'success' => true,
                'message' => "Successfully caught {$storedCount} invalid students",
                'count' => $storedCount,
                'total_found' => $invalidSubmissions->count(),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            Log::error('Error in catchInvalidStudents: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get invalid submissions from G16_CAPSTONE database
     */
    private function getInvalidSubmissionsFromG16()
    {
        try {
            // Connect to G16_CAPSTONE and get invalid submissions with student info
            $invalidSubmissions = DB::connection('g16_capstone')
                ->table('task_submissions as ts')
                ->join('pnph_users as u', 'ts.user_id', '=', 'u.user_id')
                ->leftJoin('student_details as sd', 'ts.user_id', '=', 'sd.user_id')
                ->where('ts.status', 'invalid')
                ->whereNotExists(function ($query) {
                    // Don't catch submissions already stored
                    $query->select(DB::raw(1))
                          ->from('invalid_students')
                          ->whereRaw('invalid_students.g16_submission_id = ts.id');
                })
                ->select(
                    'ts.id as submission_id',
                    'ts.user_id',
                    'ts.task_category',
                    'ts.description',
                    'ts.validated_at',
                    'ts.validated_by',
                    'ts.admin_notes',
                    'u.user_fname',
                    'u.user_lname',
                    'u.user_email',
                    'u.gender',
                    'sd.student_id',
                    'sd.batch'
                )
                ->orderBy('ts.validated_at', 'desc')
                ->get();

            return $invalidSubmissions;

        } catch (\Exception $e) {
            Log::error("Error connecting to G16_CAPSTONE: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store invalid student data in PN-ScholarSync
     */
    private function storeInvalidStudent($submission)
    {
        try {
            // Check if already stored
            $exists = DB::table('invalid_students')
                ->where('g16_submission_id', $submission->submission_id)
                ->exists();

            if ($exists) {
                // Ensure a linked invalid_violations row exists as well
                $ivExists = DB::table('invalid_violations')
                    ->where('task_submission_id', $submission->submission_id)
                    ->exists();
                if (!$ivExists) {
                    $this->createInvalidViolationRow($submission);
                }
                return false; // Already stored in invalid_students
            }

            // Store the invalid student record
            DB::table('invalid_students')->insert([
                'g16_submission_id' => $submission->submission_id,
                'g16_user_id' => $submission->user_id,
                'student_name' => trim($submission->user_fname . ' ' . $submission->user_lname),
                'student_id_code' => $submission->student_id,
                'student_email' => $submission->user_email,
                'gender' => $submission->gender,
                'batch' => $submission->batch,
                'task_category' => $submission->task_category,
                'description' => $submission->description,
                'validated_by' => $submission->validated_by,
                'validated_at' => $submission->validated_at,
                'admin_notes' => $submission->admin_notes,
                'caught_at' => now(),
                'status' => 'caught',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("Stored invalid student: {$submission->user_fname} {$submission->user_lname} (ID: {$submission->student_id})");
            
            // Also create a corresponding row in invalid_violations table
            $this->createInvalidViolationRow($submission);

            return true;

        } catch (\Exception $e) {
            Log::error("Error storing invalid student: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create an invalid_violations row linked to a G16 invalid task submission
     */
    private function createInvalidViolationRow($submission): void
    {
        try {
            $category = $submission->task_category ?? 'task';
            // Count how many violations the student already has in the main violations table
            $violationCount = 0;
            try {
                $violationCount = DB::table('violations')
                    ->where('student_id', $submission->student_id)
                    ->count();
            } catch (\Exception $e) {
                // If the table/query fails, keep count as 0
                $violationCount = 0;
            }
            DB::table('invalid_violations')->insert([
                'task_submission_id' => $submission->submission_id,
                'student_id' => $submission->student_id,
                'gender' => $submission->gender,
                'violation_date' => $submission->validated_at
                    ? Carbon::parse($submission->validated_at)->toDateString()
                    : Carbon::now()->toDateString(),
                'violation_type_id' => null,
                'severity' => 'Low',
                // Display the count of total violations the student has
                'offense' => (string) $violationCount,
                'penalty' => 'VW',
                // Consequence display should be 'pending' while the violation status is pending
                'consequence' => 'pending',
                'incident_place' => $category,
                'incident_datetime' => $submission->validated_at ? Carbon::parse($submission->validated_at) : Carbon::now(),
                // Show only this fixed text per requirement
                'incident_details' => 'Validated by admin.',
                // Prepared by should be null while pending; educator's name will be filled when assigning consequence
                'prepared_by' => null,
                // Mark as pending so consequence remains null until educator sets it
                'status' => 'pending',
                'action_taken' => true,
                // Keep consequence workflow pending while status is pending
                'consequence_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Created invalid_violations row from G16 invalid submission', [
                'task_submission_id' => $submission->submission_id,
                'student_id' => $submission->student_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed creating invalid_violations row: ' . $e->getMessage());
        }
    }

    /**
     * Get all caught invalid students
     */
    public function getCaughtInvalidStudents()
    {
        try {
            return DB::table('invalid_students')
                ->orderBy('caught_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error getting caught invalid students: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get count of caught invalid students
     */
    public function getCaughtCount()
    {
        try {
            return DB::table('invalid_students')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get invalid students by date
     */
    public function getInvalidStudentsByDate($date)
    {
        try {
            return DB::table('invalid_students')
                ->whereDate('validated_at', $date)
                ->orderBy('validated_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error getting invalid students by date: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Mark invalid student as processed
     */
    public function markAsProcessed($id)
    {
        try {
            return DB::table('invalid_students')
                ->where('id', $id)
                ->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'updated_at' => now()
                ]);
        } catch (\Exception $e) {
            Log::error("Error marking invalid student as processed: " . $e->getMessage());
            return false;
        }
    }
}
