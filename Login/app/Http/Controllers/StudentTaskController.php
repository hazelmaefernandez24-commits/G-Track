<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentTaskController extends Controller
{
    /**
     * Get tasks assigned to the currently logged-in student
     */
    public function getMyTasks(Request $request)
    {
        try {
            $studentId = Auth::id();
            $day = $request->input('day');
            $date = $request->input('date');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $query = DB::table('student_task_assignments')
                ->where('student_id', $studentId);

            // Filter by day if provided
            if ($day) {
                $query->where('day', strtolower($day));
            }

            // Filter by date if provided
            if ($date) {
                $query->where('date', $date);
            }

            $tasks = $query->orderBy('date', 'desc')
                ->orderBy('time_slot')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'message' => 'Tasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a day as complete and save assignments for students
     */
    public function markDayComplete(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $categoryName = $request->input('category_name');
            $day = $request->input('day');
            $date = $request->input('date');

            if (!$categoryId || !$day || !$date) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Missing required fields: category_id, day, or date'
                ], 400);
            }

            // Get current assignment for this category from G16_CAPSTONE database
            $currentAssignment = DB::connection('mysql_g16')->table('assignments')
                ->where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();

            if (!$currentAssignment) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No current assignment found for this category'
                ], 404);
            }

            // Get all assignment members for this category from G16_CAPSTONE database
            $assignmentMembers = DB::connection('mysql_g16')->table('assignment_members')
                ->where('assignment_id', $currentAssignment->id)
                ->get();

            $assignmentsCreated = 0;

            // Create student task assignments for the specified day and date
            foreach ($assignmentMembers as $member) {
                // Create or update student task assignment in Login database
                DB::table('student_task_assignments')->updateOrInsert(
                    [
                        'student_id' => $member->student_id,
                        'category_id' => $categoryId,
                        'day' => strtolower($day),
                        'date' => $date
                    ],
                    [
                        'student_name' => $member->student_name,
                        'category_name' => $categoryName,
                        'task_type' => $member->task_type,
                        'time_slot' => $member->time_slot,
                        'task_area' => $member->task_area ?? 'General',
                        'task_description' => $member->task_description ?? 'Assigned task',
                        'status' => 'assigned',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                $assignmentsCreated++;
            }

            // Mark the day as completed in Login database
            DB::table('day_completions')->updateOrInsert(
                [
                    'category_id' => $categoryId,
                    'day' => strtolower($day),
                    'date' => $date
                ],
                [
                    'category_name' => $categoryName,
                    'completed_by' => Auth::id(),
                    'completed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Day {$day} marked as completed for {$categoryName}",
                'data' => [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'day' => $day,
                    'date' => $date,
                    'assignments_created' => $assignmentsCreated
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark day as complete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assigned students for a specific category
     */
    public function getAssignedStudents($categoryId)
    {
        try {
            // Get current assignment for this category from G16_CAPSTONE database
            $currentAssignment = DB::connection('mysql_g16')->table('assignments')
                ->where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();

            if (!$currentAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current assignment found for this category',
                    'data' => []
                ]);
            }

            // Get all assignment members for this category
            $assignmentMembers = DB::connection('mysql_g16')->table('assignment_members')
                ->where('assignment_id', $currentAssignment->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $assignmentMembers,
                'message' => 'Assigned students retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assigned students: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
