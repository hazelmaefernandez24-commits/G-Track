<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskDefinition;
use App\Models\TaskAssignment;
use App\Models\TaskSchedule;
use App\Models\Category;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\PNUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskManagementController extends Controller
{
    /**
     * Get tasks for a specific category
     */
    public function getTasksForCategory($categoryId)
    {
        try {
            $tasks = TaskDefinition::where('category_id', $categoryId)
                ->where('is_active', true)
                ->orderBy('task_name')
                ->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update task definition
     */
    public function saveTaskDefinition(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'task_name' => 'required|string|max:255',
                'task_description' => 'nullable|string',
                'estimated_duration' => 'nullable|integer|min:1',
                'difficulty_level' => 'required|in:easy,medium,hard'
            ]);

            $task = TaskDefinition::updateOrCreate(
                [
                    'id' => $request->task_id ?? null
                ],
                [
                    'category_id' => $request->category_id,
                    'task_name' => $request->task_name,
                    'task_description' => $request->task_description,
                    'estimated_duration' => $request->estimated_duration,
                    'difficulty_level' => $request->difficulty_level,
                    'is_active' => true
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Task saved successfully',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete task definition
     */
    public function deleteTaskDefinition($taskId)
    {
        try {
            $task = TaskDefinition::findOrFail($taskId);
            
            // Check if task has active assignments
            $hasActiveAssignments = TaskAssignment::where('task_definition_id', $taskId)
                ->where('status', '!=', 'completed')
                ->exists();

            if ($hasActiveAssignments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete task with active assignments'
                ], 400);
            }

            $task->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign tasks to students for a specific assignment
     */
    public function assignTasks(Request $request)
    {
        try {
            $request->validate([
                'assignment_id' => 'required|exists:assignments,id',
                'assigned_date' => 'required|date',
                'task_assignments' => 'required|array',
                'task_assignments.*.student_id' => 'required|string',
                'task_assignments.*.task_definition_id' => 'required|exists:task_definitions,id',
                'task_assignments.*.start_time' => 'nullable|date_format:H:i',
                'task_assignments.*.end_time' => 'nullable|date_format:H:i',
                'task_assignments.*.notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Delete existing assignments for this date and assignment
            TaskAssignment::where('assignment_id', $request->assignment_id)
                ->whereDate('assigned_date', $request->assigned_date)
                ->delete();

            // Create new task assignments
            foreach ($request->task_assignments as $taskAssignment) {
                TaskAssignment::create([
                    'assignment_id' => $request->assignment_id,
                    'student_id' => $taskAssignment['student_id'],
                    'task_definition_id' => $taskAssignment['task_definition_id'],
                    'assigned_date' => $request->assigned_date,
                    'start_time' => $taskAssignment['start_time'] ?? null,
                    'end_time' => $taskAssignment['end_time'] ?? null,
                    'status' => 'assigned',
                    'notes' => $taskAssignment['notes'] ?? null,
                    'assigned_by' => auth()->user()->user_id ?? 'system'
                ]);
            }

            // Create or update task schedule
            $dayOfWeek = strtolower(Carbon::parse($request->assigned_date)->format('l'));
            
            TaskSchedule::updateOrCreate(
                [
                    'assignment_id' => $request->assignment_id,
                    'schedule_date' => $request->assigned_date
                ],
                [
                    'day_of_week' => $dayOfWeek,
                    'task_assignments' => $request->task_assignments,
                    'status' => 'active',
                    'created_by' => auth()->user()->user_id ?? 'system',
                    'finalized_at' => now()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tasks assigned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task assignments for a specific date and assignment
     */
    public function getTaskAssignments($assignmentId, $date)
    {
        try {
            $assignments = TaskAssignment::with(['student', 'taskDefinition'])
                ->where('assignment_id', $assignmentId)
                ->whereDate('assigned_date', $date)
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'success' => true,
                'assignments' => $assignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's task assignments
     */
    public function getStudentTasks($studentId, $date = null)
    {
        try {
            $query = TaskAssignment::with(['taskDefinition.category', 'assignment'])
                ->where('student_id', $studentId);

            if ($date) {
                $query->whereDate('assigned_date', $date);
            } else {
                // Get current week's assignments
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('assigned_date', [$startOfWeek, $endOfWeek]);
            }

            $assignments = $query->orderBy('assigned_date')
                ->orderBy('start_time')
                ->get();

            // Group by category and date
            $groupedAssignments = $assignments->groupBy(function ($assignment) {
                return $assignment->taskDefinition->category->name;
            })->map(function ($categoryAssignments) {
                return $categoryAssignments->groupBy('assigned_date');
            });

            return response()->json([
                'success' => true,
                'assignments' => $groupedAssignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all students' task assignments for display
     */
    public function getAllTaskAssignments($date = null)
    {
        try {
            $targetDate = $date ? Carbon::parse($date) : Carbon::now();

            $assignments = TaskAssignment::with([
                'student', 
                'taskDefinition.category', 
                'assignment'
            ])
            ->whereDate('assigned_date', $targetDate)
            ->orderBy('start_time')
            ->get();

            // Group by category
            $groupedAssignments = $assignments->groupBy(function ($assignment) {
                return $assignment->taskDefinition->category->name;
            });

            return response()->json([
                'success' => true,
                'date' => $targetDate->format('Y-m-d'),
                'assignments' => $groupedAssignments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching all task assignments: ' . $e->getMessage()
            ], 500);
        }
    }
}
