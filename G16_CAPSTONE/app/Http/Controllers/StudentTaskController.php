<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignmentMember;
use App\Models\Assignment;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class StudentTaskController extends Controller
{
    public function myTasks(Request $request)
    {
        try {
            // Check user permissions
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator', 'student'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: You do not have permission to access this feature.');
            }
            
            $studentId = auth()->id();
            $studentName = auth()->user()->name ?? 'Student';
            
            $selectedDate = $request->input('date', date('Y-m-d'));

            // Get current assignments for this student on the selected date
            $assignments = AssignmentMember::where('student_id', $studentId)
                ->whereHas('assignment', function($query) use ($selectedDate) {
                    $query->whereDate('start_date', '<=', $selectedDate)
                          ->whereDate('end_date', '>=', $selectedDate);
                })
                ->with(['assignment.category'])
                ->get();
            
            return view('StudentsDashboard.my-tasks', compact('assignments', 'studentName'));
            
        } catch (\Exception $e) {
            return view('StudentsDashboard.my-tasks', [
                'assignments' => collect(),
                'studentName' => 'Student',
                'error' => 'Unable to load tasks at this time.'
            ]);
        }
    }

    public function getMyTasksAPI(Request $request)
    {
        try {
            $studentId = auth()->id();
            $day = $request->input('day');
            $date = $request->input('date');

            // Get current assignments for this student
            $assignments = AssignmentMember::where('student_id', $studentId)
                ->with(['assignment.category'])
                ->get();

            // Format the data for the JavaScript
            $tasks = $assignments->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'category' => $assignment->assignment->category->name ?? 'General Task',
                    'task_type' => $assignment->task_type ?? 'Assigned Task',
                    'task_area' => $assignment->task_area ?? 'General',
                    'task_description' => $assignment->task_description ?? 'Complete assigned task',
                    'time_slot' => $assignment->time_slot ?? 'All Day',
                    'status' => 'assigned',
                    'created_at' => $assignment->created_at ? $assignment->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'day' => $day,
                'date' => $date,
                'message' => 'Tasks loaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'tasks' => [],
                'message' => 'Failed to load tasks: ' . $e->getMessage()
            ], 500);
        }
    }
}
