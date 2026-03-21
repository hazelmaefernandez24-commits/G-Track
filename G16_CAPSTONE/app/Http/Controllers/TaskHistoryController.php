<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\RoomTask;
use App\Models\TaskHistory;
use Carbon\Carbon;

class TaskHistoryController extends Controller
{
    /**
     * Marks a day as completed and updates task statuses.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markDayComplete(Request $request)
    {
        // Prefer Laravel auth guard; for AJAX return JSON 401 so frontend can handle it.
        if (!auth()->check()) {
            if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        try {
            // allow week/month/year to be optional so frontend can send minimal payload
            $validated = $request->validate([
                'day' => 'required|string',
                // room may be numeric; normalize below
                'room' => 'required',
                'week' => 'nullable|string',
                'month' => 'nullable|string',
                'year' => 'nullable|string',
                'dayKey' => 'nullable|string',
                'tasks' => 'required|array'
            ]);

            $day = $validated['day'];
            $room = isset($validated['room']) ? (string)$validated['room'] : '';
            // If frontend omitted week/month/year, default to current values so DB non-null columns are satisfied
            $week = $validated['week'] ?? Carbon::now()->weekOfYear;
            $month = $validated['month'] ?? Carbon::now()->month;
            $year = $validated['year'] ?? Carbon::now()->year;
            $taskStatuses = $validated['tasks'];

            // If the frontend provided a dayKey (YYYY-MM-DD), use it for created_at/updated_at so history groups by that date
            $dayKey = $validated['dayKey'] ?? null;
            if ($dayKey) {
                try {
                    $tz = config('app.timezone') ?: 'UTC';
                    $dateForCreated = Carbon::createFromFormat('Y-m-d', $dayKey, $tz)->startOfDay();
                } catch (\Exception $e) {
                    $dateForCreated = Carbon::now();
                }
            } else {
                $dateForCreated = Carbon::now();
            }

            // Update RoomTask statuses and assigned names where possible; continue on error per-item
            foreach ($taskStatuses as $status) {
                if (!isset($status['id'])) continue;
                try {
                    $task = RoomTask::find($status['id']);
                    if ($task) {
                        // Persist provided assigned name if present (this comes from generated schedule or client-side rendering)
                        if (isset($status['assigned_name']) && $status['assigned_name'] !== '') {
                            $task->name = $status['assigned_name'];
                        }
                        $task->status = $status['status'] ?? $task->status;
                        if ($week !== null && Schema::hasColumn('room_tasks', 'week')) $task->week = $week;
                        if ($month !== null && Schema::hasColumn('room_tasks', 'month')) $task->month = $month;
                        if ($year !== null && Schema::hasColumn('room_tasks', 'year')) $task->year = $year;
                        $task->save();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to update RoomTask id=' . ($status['id'] ?? 'n/a') . ': ' . $e->getMessage());
                }
            }

            // Create detailed task history entries for each task so the Task History page shows the checklist
            foreach ($taskStatuses as $status) {
                if (!isset($status['id'])) continue;
                try {
                    $task = RoomTask::find($status['id']);
                    if ($task) {
                        // Prefer assigned_name from payload (schedule) to ensure history records the persisted assignee
                        $assignedTo = $status['assigned_name'] ?? $task->name ?? null;

                        $entry = [
                            'room_number' => $room,
                            'task_id' => $task->id ?? null,
                            'assigned_to' => $assignedTo,
                            'task_area' => $task->area ?? null,
                            'task_description' => $task->desc ?? ($status['desc'] ?? null),
                            'status' => $status['status'] ?? $task->status ?? null,
                            'day' => $day,
                            'week' => $week,
                            'month' => $month,
                            'year' => $year,
                            'created_at' => $dateForCreated,
                            'updated_at' => $dateForCreated,
                            'filter_type' => 'daily',
                            'completed' => true
                        ];

                        // Use helper to respect schema columns and insert safely
                        $this->createTaskHistoryEntry($entry);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to create TaskHistory entry for RoomTask id=' . ($status['id'] ?? 'n/a') . ': ' . $e->getMessage());
                }
            }

            // Prepare data for task_histories insert/update but only include existing columns
            $table = 'task_histories';
            $now = $dateForCreated ?? Carbon::now();
            $candidate = [
                'room_number' => $room,
                'day' => $day,
                'week' => $week,
                'month' => $month,
                'year' => $year,
                'completed' => true,
                'status' => 'completed',
                'created_at' => $now,
                'updated_at' => $now
            ];

            $insertable = [];
            foreach ($candidate as $col => $val) {
                if (Schema::hasColumn($table, $col) && $val !== null) {
                    $insertable[$col] = $val;
                }
            }

            if (!empty($insertable)) {
                // Build where clause for updateOrInsert using available identifying columns
                $where = [];
                foreach (['room_number','day','week','month','year'] as $k) {
                    if (isset($insertable[$k])) $where[$k] = $insertable[$k];
                }
                if (!empty($where)) {
                    DB::table($table)->updateOrInsert($where, $insertable);
                } else {
                    DB::table($table)->insert($insertable);
                }
            } else {
                \Log::warning('No matching columns found on task_histories to insert task history.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Day marked as completed successfully',
                'tasks' => $taskStatuses
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Mark day complete validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Mark day complete error: ' . $e->getMessage() . ' -- payload: ' . json_encode($request->all()));
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while marking day as complete',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Displays the completed task history records with filtering options.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function showTaskHistory(Request $request)
    {
        // Require auth for history view; use normal redirect to login page when not authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        $filterType = $request->input('filter_type', 'daily');
        // Start a query and apply filters defensively depending on available columns
        $query = TaskHistory::query();
        if (Schema::hasColumn('task_histories', 'completed')) {
            $query->where('completed', true);
        } elseif (Schema::hasColumn('task_histories', 'status')) {
            $query->where('status', 'completed');
        }

        // Respect optional room and date range filters from request
        $room = $request->input('room', null);
        $startDate = $request->input('start_date', null);
        $endDate = $request->input('end_date', null);

        if ($room) {
            $query->where('room_number', $room);
        }

        // If start/end provided, use them; otherwise apply filterType defaults
        if ($startDate || $endDate) {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('1970-01-01')->startOfDay();
            $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

            // If the table has a 'day' column prefer filtering by it (it's YYYY-MM-DD),
            // otherwise fall back to created_at range.
            if (Schema::hasColumn('task_histories', 'day')) {
                $query->whereBetween('day', [$start->toDateString(), $end->toDateString()]);
            } else {
                $query->whereBetween('created_at', [$start, $end]);
            }
        } else {
            // No explicit date range provided; choose defaults based on filter type.
            if ($filterType === 'daily') {
                if (Schema::hasColumn('task_histories', 'day')) {
                    $query->where('day', Carbon::now()->toDateString());
                } else {
                    $query->whereDate('created_at', Carbon::now()->toDateString());
                }
            } elseif ($filterType === 'weekly') {
                if (Schema::hasColumn('task_histories', 'day')) {
                    $query->whereBetween('day', [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()]);
                } else {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                }
            } elseif ($filterType === 'monthly') {
                if (Schema::hasColumn('task_histories', 'day')) {
                    $query->whereMonth('day', Carbon::now()->month)->whereYear('day', Carbon::now()->year);
                } else {
                    $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
                }
            }
        }

        $completedHistories = $query->orderBy('updated_at', 'desc')->get();

        // Determine the date range we used for the query so we can render a table for every day
        if ($startDate || $endDate) {
            $rangeStart = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('1970-01-01')->startOfDay();
            $rangeEnd = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();
        } else {
            if ($filterType === 'daily') {
                $rangeStart = Carbon::now()->startOfDay();
                $rangeEnd = Carbon::now()->endOfDay();
            } elseif ($filterType === 'weekly') {
                $rangeStart = Carbon::now()->startOfWeek();
                $rangeEnd = Carbon::now()->endOfWeek();
            } else { // monthly
                $rangeStart = Carbon::now()->startOfMonth();
                $rangeEnd = Carbon::now()->endOfMonth();
            }
        }

        // Group histories by date string. Prefer the explicit 'day' column when it contains a parsable
        // date (frontend may provide a YYYY-MM-DD day key). Fall back to created_at (normalized
        // to app timezone) when 'day' is missing or not a parsable date.
        $tz = config('app.timezone') ?: 'UTC';
        $byDate = $completedHistories->groupBy(function($h) use ($tz) {
            // If the table has a 'day' column and it looks like a date, prefer it.
            try {
                if (Schema::hasColumn('task_histories', 'day') && !empty($h->day)) {
                    // Try to parse the 'day' value. If it's already in Y-m-d or a parsable format,
                    // Carbon::parse will succeed and we can use its date string.
                    try {
                        $parsed = Carbon::parse($h->day);
                        return $parsed->toDateString();
                    } catch (\Exception $e) {
                        // not a parsable date, continue to fallback
                    }
                }

                if ($h->created_at) {
                    return $h->created_at->setTimezone($tz)->toDateString();
                }
            } catch (\Exception $e) {
                // In case of unexpected data, fall back to today's date
            }
            return Carbon::now()->setTimezone($tz)->toDateString();
        });

        // Build grouped array that includes every date in the range (empty arrays when no records exist)
        $grouped = [];
        $cursor = $rangeStart->copy();
        while ($cursor->lte($rangeEnd)) {
            $key = $cursor->toDateString();
            $grouped[$key] = isset($byDate[$key]) ? $byDate[$key]->all() : [];
            $cursor->addDay();
        }

        // Provide additional view variables the blade expects
        $studentNames = [];
        if ($room) {
            // get assigned students for the selected room if model exists
            if (Schema::hasTable('room_assignments')) {
                $studentNames = \App\Models\RoomAssignment::where('room_number', $room)->orderBy('assignment_order')->pluck('student_name')->toArray();
            }
        }

        $dayMap = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        // Provide list of rooms for the room <select>
        $rooms = [];
        if (Schema::hasTable('rooms')) {
            $rooms = \App\Models\Room::orderBy('room_number')->get();
        }

        // Minimal placeholders for matrix/allAreas/feedbacksByDay used in blade
        $allAreas = [];
        $matrix = [];
        $feedbacksByDay = [];

        $startDateStr = $startDate ? Carbon::parse($startDate)->format('F j, Y') : null;
        $endDateStr = $endDate ? Carbon::parse($endDate)->format('F j, Y') : null;

        return view('task-history', [
            'taskHistories' => $grouped,
            'filterType' => $filterType,
            'studentNames' => $studentNames,
            'dayMap' => $dayMap,
            'rooms' => $rooms,
            'room' => $room,
            'allAreas' => $allAreas,
            'matrix' => $matrix,
            'feedbacksByDay' => $feedbacksByDay,
            'startDateStr' => $startDateStr,
            'endDateStr' => $endDateStr
        ]);
    }
    
    /**
     * Creates a detailed task history entry.
     *
     * @param array $taskData
     * @return void
     */
    public function createTaskHistoryEntry($taskData)
    {
        // Compute a normalized YYYY-MM-DD day value: prefer provided day (if parsable), else use created_at if present,
        // otherwise today's date. This ensures grouping by day works reliably.
        $computedDay = null;
        if (!empty($taskData['day'])) {
            try {
                $computedDay = Carbon::parse($taskData['day'])->toDateString();
            } catch (\Exception $e) {
                $computedDay = null;
            }
        }
        if (empty($computedDay) && !empty($taskData['created_at'])) {
            try {
                $computedDay = ($taskData['created_at'] instanceof Carbon) ? $taskData['created_at']->toDateString() : Carbon::parse($taskData['created_at'])->toDateString();
            } catch (\Exception $e) {
                $computedDay = null;
            }
        }
        if (empty($computedDay)) {
            $computedDay = Carbon::now()->toDateString();
        }

        $payload = [
            'room_number' => $taskData['room_number'] ?? null,
            'assigned_to' => $taskData['assigned_to'] ?? null,
            'task_area' => $taskData['task_area'] ?? null,
            'task_description' => $taskData['task_description'] ?? null,
            'status' => $taskData['status'] ?? 'completed',
            'filter_type' => $taskData['filter_type'] ?? 'daily',
            'day' => $computedDay,
            'week' => $taskData['week'] ?? date('W'),
            'month' => $taskData['month'] ?? date('n'),
            'year' => $taskData['year'] ?? date('Y'),
            'completed' => true
        ];

        $final = [];
        foreach ($payload as $k => $v) {
            if (Schema::hasColumn('task_histories', $k)) {
                $final[$k] = $v;
            }
        }

        // Preserve created_at/updated_at if provided in taskData and the columns exist
        if (!empty($taskData)) {
            if (isset($taskData['created_at']) && Schema::hasColumn('task_histories', 'created_at')) {
                try {
                    $final['created_at'] = $taskData['created_at'] instanceof \Carbon\Carbon ? $taskData['created_at'] : Carbon::parse($taskData['created_at']);
                } catch (\Exception $e) {
                    $final['created_at'] = Carbon::now();
                }
            }
            if (isset($taskData['updated_at']) && Schema::hasColumn('task_histories', 'updated_at')) {
                try {
                    $final['updated_at'] = $taskData['updated_at'] instanceof \Carbon\Carbon ? $taskData['updated_at'] : Carbon::parse($taskData['updated_at']);
                } catch (\Exception $e) {
                    $final['updated_at'] = $final['created_at'] ?? Carbon::now();
                }
            }
        }

        if (!empty($final)) {
            // Ensure timestamps are strings for direct DB insert
            if (isset($final['created_at']) && $final['created_at'] instanceof \Carbon\Carbon) {
                $final['created_at'] = $final['created_at']->toDateTimeString();
            }
            if (isset($final['updated_at']) && $final['updated_at'] instanceof \Carbon\Carbon) {
                $final['updated_at'] = $final['updated_at']->toDateTimeString();
            }

            DB::table('task_histories')->insert($final);
        } else {
            \Log::warning('createTaskHistoryEntry: no columns available on task_histories to insert data');
        }
    }
}