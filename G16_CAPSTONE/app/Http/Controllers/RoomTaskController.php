<?php

namespace App\Http\Controllers;

use App\Models\TaskHistory;
use App\Models\RoomTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomTaskController extends Controller
{
    public function markDayComplete(Request $request)
    {
        if (!session()->has('userid')) {
            return redirect()->route('auth.login');
        }
        
        try {
            $validated = $request->validate([
                'day' => 'required|string',
                // room may be numeric; normalize below
                'room' => 'required',
                'week' => 'required|string',
                'month' => 'required|string',
                'year' => 'required|string',
                'tasks' => 'required|array',
                'dayKey' => 'nullable|string'
            ]);

            $day = $validated['day'];
            $room = isset($validated['room']) ? (string)$validated['room'] : '';
            $week = $validated['week'];
            $month = $validated['month'];
            $year = $validated['year'];
            $taskStatuses = $validated['tasks'];
            $dayKey = $validated['dayKey'] ?? null;
            try {
                $tz = config('app.timezone') ?: 'UTC';
                $dateForCreated = $dayKey ? \Carbon\Carbon::createFromFormat('Y-m-d', $dayKey, $tz)->startOfDay() : now();
            } catch (\Exception $e) {
                $dateForCreated = now();
            }
            $dayNormalized = $dateForCreated->toDateString();

            // Update each task with its corresponding status
            foreach ($taskStatuses as $status) {
                if (!isset($status['id'])) continue;
                $task = RoomTask::find($status['id']);
                if ($task) {
                    $task->status = $status['isChecked'] ? 'checked' : ($status['isWrong'] ? 'wrong' : 'not yet');
                    $task->week = $week;
                    $task->month = $month;
                    $task->year = $year;
                    $task->save();
                }
            }

            // Mark the day as completed in the database with the specific status
            \DB::table('task_histories')->updateOrInsert(
                [
                    'room_number' => $room,
                    'day' => $dayNormalized,
                    'week' => $week,
                    'month' => $month,
                    'year' => $year
                ],
                [
                    'completed' => true,
                    'status' => 'completed',
                    'updated_at' => $dateForCreated->toDateTimeString()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Day marked as completed successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTaskHistory(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required',
            'week' => 'nullable|string',
            'month' => 'nullable|string',
            'year' => 'nullable|string'
        ]);
    
        $room = isset($validated['room']) ? (string)$validated['room'] : '';
        $query = DB::table('task_histories')
            ->where('room_number', $room);
    
        if (!empty($validated['week'])) {
            $query->where('week', $validated['week']);
        }
        if (!empty($validated['month'])) {
            $query->where('month', $validated['month']);
        }
        if (!empty($validated['year'])) {
            $query->where('year', $validated['year']);
        }
    
        $history = $query->get();
    
        return response()->json(['success' => true, 'history' => $history]);
    }

    public function renderRoomTaskView(Request $request)
    {
        $selectedRoom = $request->input('room', 'default_room');
        $currentYear = request('year', date('Y'));
        $currentMonth = request('month', date('n'));
        $currentWeek = request('week', date('W'));

        // Fetch task statuses from the database
        $taskStatuses = \DB::table('task_statuses')
            ->where('room_number', $selectedRoom)
            ->get();

        // Organize task statuses by day and task ID
        foreach ($taskStatuses as $status) {
            $dayKey = "{$status->year}-{$status->month}-{$status->week}-{$status->day}";
            if (isset($tasksByDay[$status->day][$selectedRoom])) {
                foreach ($tasksByDay[$status->day][$selectedRoom] as &$task) {
                    if ($task['id'] == $status->task_id) {
                        if (!isset($task['statuses'])) {
                            $task['statuses'] = [];
                        }
                        $task['statuses'][$dayKey] = $status->status;
                    }
                }
            }
        }

    // Fetch day completion status
        $completedDays = \DB::table('task_histories')
            ->where('room_number', $selectedRoom)
            ->where('completed', true)
            ->get();

        $weekDayCompletionStatus = [];
        foreach ($completedDays as $day) {
            $dayKey = "{$day->year}-{$day->month}-{$day->week}-{$day->day}";
            $weekDayCompletionStatus[$dayKey] = true;
        }

        // Pass all variables to the view
        // Ensure feedbacks is defined (blade expects it) to avoid ViewException when not available
        $feedbacks = [];

        return view('roomtask', compact(
            'tasksByDay', 
            'daysOfWeek', 
            'currentDay', 
            'selectedRoom',
            'weekDayCompletionStatus',
            'currentYear',
            'currentMonth',
            'currentWeek',
            'feedbacks'
        ));
    }

    public function deleteAllApplied()
    {
        try {
            // Truncate the entire roomtask table on default connection (clears all rows)
            try {
                // disable foreign key checks for MySQL to allow truncate when FKs exist
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::table('roomtask')->truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $e) {
                \Log::warning('Default DB: failed to truncate roomtask table: ' . $e->getMessage());
            }

            // Also attempt on the 'login' connection if available
            try {
                DB::connection('login')->statement('SET FOREIGN_KEY_CHECKS=0');
                DB::connection('login')->table('roomtask')->truncate();
                DB::connection('login')->statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable $e) {
                \Log::warning('Login DB: failed to truncate roomtask table: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'All roomtask rows have been removed (truncated) on available connections'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete/truncate roomtask: ' . $e->getMessage()
            ], 500);
        }
    }
} 