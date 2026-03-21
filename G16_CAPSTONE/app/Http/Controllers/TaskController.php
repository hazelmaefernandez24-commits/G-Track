<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomTask;
use App\Models\RoomAssignment;
use App\Models\Category;
use App\Models\Assignment;
use App\Models\TaskTemplate;
use App\Models\Room;
use App\Models\GeneratedSchedule;
use App\Http\Controllers\RoomTaskController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Services\StudentValidationService;

class TaskController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Redirect students to their own dashboard
        if ($user->user_role === 'student') {
            return redirect()->route('mainstudentdash')->with('error', 'Students can only access the student dashboard.');
        }


        // Only allow educator/inspector to access admin dashboard
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        // Check if this is a student view request (for admin viewing student interface)
        $isStudentView = request()->has('student_view') && request('student_view') == '1';

        // Get dynamic room student data for the dashboard (active rooms only)
        $roomStudents = $this->getDynamicRoomStudents();

        // Fetch only active rooms so non-student rooms stay hidden on the dashboard
        $allRooms = Room::where('status', 'active')
            ->with(['assignments' => function ($query) {
                $query->orderBy('assignment_order');
            }])
            ->orderBy('room_number')
            ->get();
        $floorsAndRooms = [];
        $roomMeta = [];

        foreach ($allRooms as $room) {
            // If a floor column exists and is set, use it. Otherwise infer from numeric room_number (e.g. 202 -> 2).
            $floor = null;
            if (isset($room->floor) && $room->floor !== null && $room->floor !== '') {
                $floor = (int)$room->floor;
            } else {
                // Attempt to infer from room_number
                $rn = preg_replace('/[^0-9]/', '', $room->room_number);
                $roomNum = $rn === '' ? 0 : (int)$rn;
                $floor = $roomNum > 0 ? (int)floor($roomNum / 100) : 1;
            }
            if (!isset($floorsAndRooms[$floor])) {
                $floorsAndRooms[$floor] = [];
            }
            // Build assignment payload for front-end rendering
            $roomAssignments = $room->assignments->map(function ($assignment) {
                return [
                    'student_name' => $assignment->student_name,
                    'student_gender' => $assignment->student_gender,
                    'student_id' => $assignment->student_id,
                ];
            })->values()->toArray();

            // Keep room_number as string to preserve formatting, but attach metadata
            $floorsAndRooms[$floor][] = [
                'room_number' => $room->room_number,
                'capacity' => $room->capacity ?? $this->getRoomCapacitySetting(),
                'status' => $room->status,
                'description' => $room->description,
                'occupant_type' => $room->occupant_type ?? 'both',
                'assignments' => $roomAssignments,
                'assignments_count' => count($roomAssignments),
            ];

            $roomMeta[(string) $room->room_number] = [
                'occupant_type' => $room->occupant_type ?? 'both',
                'capacity' => $room->capacity ?? $this->getRoomCapacitySetting(),
            ];
        }

        $totalRoomsCount = 0;
        foreach ($floorsAndRooms as $roomsOnFloor) {
            $totalRoomsCount += count($roomsOnFloor);
        }

        // Get total number of students from room assignments (both manual and auto-assigned)
        $totalStudents = \App\Models\RoomAssignment::count();

        $generalTaskOverview = $this->prepareGeneralTaskOverview();
        $taskTemplateOverview = $this->prepareTaskTemplateOverview($totalRoomsCount);
        $occupancySummary = $this->prepareOccupancySummary($allRooms);

        return view('dashboard', [
            'user' => $user,
            'isStudentView' => $isStudentView,
            'roomStudents' => $roomStudents,
            'floorsAndRooms' => $floorsAndRooms,
            'roomMeta' => $roomMeta,
            'totalStudents' => $totalStudents,
            'generalTaskOverview' => $generalTaskOverview,
            'taskTemplateOverview' => $taskTemplateOverview,
            'occupancySummary' => $occupancySummary,
        ]);
    }

    private function prepareGeneralTaskOverview(): array
    {
        $baseStatusCounts = [
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
        ];

        try {
            $statusCounts = $baseStatusCounts;

            $generatedCounts = GeneratedSchedule::query()
                ->select('task_status', DB::raw('COUNT(*) as total'))
                ->groupBy('task_status')
                ->pluck('total', 'task_status');

            foreach ($generatedCounts as $status => $count) {
                $normalized = $this->normalizeGeneratedStatus($status);
                if (isset($statusCounts[$normalized])) {
                    $statusCounts[$normalized] += $count;
                }
            }

            $totalAssignments = array_sum($statusCounts);

            // Also get category snapshots from assignments for the category insights section
            $subCategories = Category::whereNotNull('parent_id')
                ->with(['assignments' => function ($query) {
                    $query->with('assignmentMembers')->orderByDesc('start_date');
                }])
                ->orderBy('name')
                ->get();

            $uniqueStudents = collect();
            $coordinatorCount = 0;
            $assignedStudentsCount = 0;
            $overdueCount = 0;
            $categorySnapshots = [];
            $today = now()->toDateString();

            foreach ($subCategories as $category) {
                $membersCount = 0;
                $categoryCoordinators = 0;
                $latestAssignment = null;

                foreach ($category->assignments as $assignment) {
                    if ($assignment->end_date && $assignment->status !== 'completed' && $assignment->end_date < $today) {
                        $overdueCount++;
                    }
                    $isCurrent = $this->isGeneralTaskActiveStatus($assignment->status);
                    if ($isCurrent) {
                        $membersCount += $assignment->assignmentMembers->count();
                    }

                    foreach ($assignment->assignmentMembers as $member) {
                        $identifier = $member->student_id ?: ('member-' . $member->id);
                        if ($identifier) {
                            $uniqueStudents->push($identifier);
                        }
                        if ($isCurrent) {
                            $assignedStudentsCount++;
                            if ($member->is_coordinator) {
                                $coordinatorCount++;
                                $categoryCoordinators++;
                            }
                        }
                    }

                    if (!$latestAssignment || $assignment->status === 'current') {
                        $latestAssignment = $assignment;
                    }
                }

                $statusKey = $this->normalizeAssignmentStatus(optional($latestAssignment)->status);

                $categorySnapshots[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'status' => optional($latestAssignment)->status ?? 'pending',
                    'status_key' => $statusKey,
                    'members' => $membersCount,
                    'coordinators' => $categoryCoordinators,
                    'start_date' => optional($latestAssignment)->start_date,
                    'end_date' => optional($latestAssignment)->end_date,
                ];
            }

            return [
                'counts' => [
                    'total_categories' => $subCategories->count(),
                    'total_assignments' => $totalAssignments,
                    'total_students' => $assignedStudentsCount,
                    'total_coordinators' => $coordinatorCount,
                    'overdue' => $overdueCount,
                    'statuses' => $statusCounts,
                ],
                'categories' => $categorySnapshots,
            ];
        } catch (\Throwable $e) {
            \Log::warning('Failed to prepare general task overview: ' . $e->getMessage());
            return [
                'counts' => [
                    'total_categories' => 0,
                    'total_assignments' => 0,
                    'total_students' => 0,
                    'total_coordinators' => 0,
                    'overdue' => 0,
                    'statuses' => $baseStatusCounts,
                ],
                'categories' => [],
            ];
        }
    }

    private function normalizeAssignmentStatus(?string $status): string
    {
        $normalized = strtolower(trim($status ?? ''));

        return match ($normalized) {
            'current', 'in_progress', 'active' => 'in_progress',
            'completed', 'done' => 'completed',
            'review', 'for_review' => 'for_review',
            'on_hold', 'hold', 'on hold' => 'on_hold',
            default => 'pending',
        };
    }

    private function isGeneralTaskActiveStatus(?string $status): bool
    {
        if (!$status) {
            return false;
        }

        return in_array(strtolower($status), ['current', 'active', 'in_progress', 'in progress'], true);
    }

    private function prepareTaskTemplateOverview(int $totalRooms = 0): array
    {
        $summary = [
            'total_templates' => 0,
            'total_rooms' => 0,
            'templates' => [],
        ];

        try {
            $templates = TaskTemplate::where('is_active', true)
                ->orderBy('area')
                ->get();

            $templateRowFilter = function ($query) {
                $query->whereNotNull('task_template_id')
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNull('week')
                            ->whereNull('month')
                            ->whereNull('year')
                            ->where('name', '');
                    });
            };

            $usageRows = DB::table('roomtask')
                ->selectRaw('COALESCE(task_template_id, 0) as template_id, area, `desc` as description, COUNT(DISTINCT room_number) as rooms_count, COUNT(*) as total_tasks')
                ->where(function ($query) use ($templateRowFilter) {
                    $templateRowFilter($query);
                })
                ->groupByRaw('COALESCE(task_template_id, 0), area, `desc`')
                ->get();

            $roomsTracked = DB::table('roomtask')
                ->where(function ($query) use ($templateRowFilter) {
                    $templateRowFilter($query);
                })
                ->distinct('room_number')
                ->count('room_number');
            $summary['total_rooms'] = $roomsTracked;

            $usageById = [];
            $usageByAreaDesc = [];

            foreach ($usageRows as $row) {
                $roomsCount = (int)($row->rooms_count ?? 0);
                $tasksCount = (int)($row->total_tasks ?? 0);
                if (!empty($row->template_id)) {
                    $usageById[$row->template_id] = [
                        'rooms' => $roomsCount,
                        'tasks' => $tasksCount,
                    ];
                    continue;
                }

                $areaKey = $this->buildTemplateAreaKey($row->area ?? '', $row->description ?? '');
                $existing = $usageByAreaDesc[$areaKey] ?? ['rooms' => 0, 'tasks' => 0];
                $existing['rooms'] += $roomsCount;
                $existing['tasks'] += $tasksCount;
                $usageByAreaDesc[$areaKey] = $existing;
            }

            foreach ($templates as $template) {
                $fallbackKey = $this->buildTemplateAreaKey($template->area, $template->description);
                $usage = $usageById[$template->id] ?? $usageByAreaDesc[$fallbackKey] ?? ['rooms' => 0, 'tasks' => 0];
                $roomsApplied = $usage['rooms'];
                $tasksCount = $usage['tasks'];
                $coverageBase = $summary['total_rooms'] > 0 ? $summary['total_rooms'] : $totalRooms;
                $coverage = $coverageBase > 0 ? min(100, round(($roomsApplied / $coverageBase) * 100)) : 0;

                if ($roomsApplied === 0) {
                    continue;
                }

                $summary['templates'][] = [
                    'id' => $template->id,
                    'name' => $template->area ?? 'Untitled Task',
                    'description' => $template->description ?? null,
                    'rooms_applied' => $roomsApplied,
                    'tasks_count' => $tasksCount,
                    'coverage_percent' => $coverage,
                ];
            }

            $summary['total_templates'] = count($summary['templates']);
        } catch (\Throwable $e) {
            \Log::warning('Failed to prepare task template overview: ' . $e->getMessage());
        }

        return $summary;
    }

    private function buildTemplateAreaKey(?string $area, ?string $description): string
    {
        return strtolower(trim($area ?? '')) . '|' . strtolower(trim($description ?? ''));
    }

    private function prepareOccupancySummary($rooms): array
    {
        $summary = [
            'total_rooms' => 0,
            'total_students' => 0,
            'ratings' => [],
        ];

        try {
            $roomsCollection = $rooms instanceof \Illuminate\Support\Collection ? $rooms : collect($rooms);
            if ($roomsCollection->isEmpty()) {
                return $summary;
            }

            $summary['total_rooms'] = $roomsCollection->count();
            $summary['total_students'] = RoomAssignment::count();

            $occupancyByRoom = RoomAssignment::select('room_number', DB::raw('COUNT(*) as occupants'))
                ->groupBy('room_number')
                ->pluck('occupants', 'room_number');

            $fullCapacity = 0;
            $partialOccupancy = 0;
            $availableRooms = 0;

            foreach ($roomsCollection as $room) {
                $capacity = max(1, (int)($room->capacity ?? 6));
                $roomNumber = $room->room_number;
                $occupants = (int)($occupancyByRoom[$roomNumber] ?? 0);

                if ($occupants === 0) {
                    $availableRooms++;
                } elseif ($occupants >= $capacity) {
                    $fullCapacity++;
                } else {
                    $partialOccupancy++;
                }
            }

            $summary['ratings'] = [
                [
                    'title' => 'Full Capacity',
                    'subtitle' => 'No available slots',
                    'count' => $fullCapacity,
                    'description' => 'rooms at maximum capacity',
                    'detail' => 'Cannot accept more students',
                    'status_label' => 'Capacity Status',
                    'status_value' => '6/6 students',
                    'accent' => '#dc2626',
                    'percent' => $summary['total_rooms'] > 0 ? round(($fullCapacity / $summary['total_rooms']) * 100) : 0,
                ],
                [
                    'title' => 'Partially Occupied',
                    'subtitle' => 'Has available slots',
                    'count' => $partialOccupancy,
                    'description' => 'rooms with remaining slots',
                    'detail' => 'Can accept additional students',
                    'status_label' => 'Occupancy Range',
                    'status_value' => '1 to 5 students',
                    'accent' => '#f59e0b',
                    'percent' => $summary['total_rooms'] > 0 ? round(($partialOccupancy / $summary['total_rooms']) * 100) : 0,
                ],
                [
                    'title' => 'Fully Available',
                    'subtitle' => 'All slots available',
                    'count' => $availableRooms,
                    'description' => 'rooms completely unoccupied',
                    'detail' => 'Ready for new assignments',
                    'status_label' => 'Capacity Status',
                    'status_value' => '0/6 students',
                    'accent' => '#0ea5e9',
                    'percent' => $summary['total_rooms'] > 0 ? round(($availableRooms / $summary['total_rooms']) * 100) : 0,
                ],
            ];

            return $summary;
        } catch (\Throwable $e) {
            \Log::warning('Failed to build occupancy summary: ' . $e->getMessage());
            return $summary;
        }
    }

    /**
     * Temporary debug endpoint to inspect Login DB rotation_schedules and connection info
     * NOTE: remove this before production.
     */
    public function debugLoginRotation()
    {
        try {
            $connName = null;
            $dbName = null;
            try {
                $rsModel = new \App\Models\RotationSchedule();
                $connName = $rsModel->getConnectionName();
                $dbName = DB::connection($connName)->getDatabaseName();
            } catch (\Throwable $e) {
                $connName = 'unknown';
                $dbName = 'unknown';
            }

            $rows = [];
            try {
                $rows = DB::connection($connName)->table('rotation_schedules')->orderByDesc('created_at')->limit(20)->get();
            } catch (\Throwable $e) {
                $rows = ['error' => $e->getMessage()];
            }

            return response()->json([
                'connection_name' => $connName,
                'database' => $dbName,
                'rows_preview' => $rows
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return the latest persisted rotation schedule for a given room as JSON.
     * Used by the client after applying a generated schedule to refresh the persisted preview.
     */
    public function getLatestRotationSchedule($room)
    {
        try {
            $rs = \App\Models\RotationSchedule::where('room', $room)
                ->orderBy('created_at', 'desc')
                ->first();
            if (!$rs) return response()->json(['success' => false, 'message' => 'No schedule found'], 404);
            return response()->json(['success' => true, 'schedule' => $rs->toArray()]);
        } catch (\Throwable $e) {
            \Log::error('getLatestRotationSchedule error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Return canonical base templates (roomtask rows with week/month/year NULL) for a room and optional day.
     * If templates exist only in the centralized 'login' DB, mirror them into the local DB so generation uses a single source.
     */
    public function getBaseTemplates(Request $request)
    {
        $room = $request->query('room');
        $day = $request->query('day');
        try {
            $query = RoomTask::whereNull('week')->whereNull('month')->whereNull('year');
            if ($room) $query->where('room_number', $room);
            if ($day) $query->where('day', $day);
            $rows = $query->orderBy('id')->get(['id', 'room_number', 'day', 'area', 'desc', 'name'])->toArray();

            // If no local base templates, try to read from login connection and mirror into local DB
            if (empty($rows)) {
                try {
                    $loginQuery = DB::connection('login')->table('roomtask')->whereNull('week')->whereNull('month')->whereNull('year');
                    if ($room) $loginQuery->where('room_number', $room);
                    if ($day) $loginQuery->where('day', $day);
                    $loginRows = $loginQuery->orderBy('id')->get()->toArray();
                    $loginRows = array_map(function($r){ return (array)$r; }, $loginRows);
                } catch (\Throwable $e) {
                    $loginRows = [];
                }

                if (!empty($loginRows)) {
                    foreach ($loginRows as $lr) {
                        // Avoid duplicates by area+room+day
                        $exists = RoomTask::whereNull('week')->whereNull('month')->whereNull('year')
                            ->where('area', $lr['area'] ?? '')
                            ->when(isset($lr['room_number']), function($q) use ($lr) { return $q->where('room_number', $lr['room_number']); })
                            ->when(isset($lr['day']), function($q) use ($lr) { return $q->where('day', $lr['day']); })
                            ->exists();
                        if (!$exists) {
                            try {
                                RoomTask::create([
                                    'name' => $lr['name'] ?? '',
                                    'room_number' => $lr['room_number'] ?? ($room ?? null),
                                    'area' => $lr['area'] ?? '',
                                    'desc' => $lr['desc'] ?? ($lr['description'] ?? null),
                                    'day' => $lr['day'] ?? ($day ?? null),
                                    'status' => $lr['status'] ?? 'not yet'
                                ]);
                            } catch (\Throwable $e) {
                                // continue on individual insert failure
                                \Log::warning('Failed mirroring login template to local DB: ' . $e->getMessage());
                            }
                        }
                    }
                    // re-query local rows after mirroring
                    $rows = $query->orderBy('id')->get(['id', 'room_number', 'day', 'area', 'desc', 'name'])->toArray();
                }
            }

            // As a last resort, fall back to TaskTemplate definitions
            if (empty($rows)) {
                [$base, $fixed] = $this->getTaskTemplates();
                $rows = [];
                $i = 0;
                foreach ($base as $tpl) {
                    $i++;
                    $rows[] = [
                        'id' => 't' . $i,
                        'room_number' => $room ?? null,
                        'day' => $day ?? null,
                        'area' => $tpl['area'] ?? '',
                        'desc' => $tpl['desc'] ?? '',
                        'name' => ''
                    ];
                }
            }

            return response()->json(['success' => true, 'tasks' => $rows]);
        } catch (\Throwable $e) {
            \Log::error('getBaseTemplates error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch task templates (base tasks and fixed tasks) strictly from DB
     * Returns array: [baseTasks, fixedTasks]
     */
    private function getTaskTemplates(): array
    {
        try {
            $base = \App\Models\TaskTemplate::where('is_active', true)
                ->where('is_fixed', false)
                ->orderBy('id')
                ->get(['area as area', 'description as desc'])
                ->toArray();

            $fixed = \App\Models\TaskTemplate::where('is_active', true)
                ->where('is_fixed', true)
                ->orderBy('id')
                ->get(['area as area', 'description as desc'])
                ->toArray();

            \Log::info('Loaded task templates', ['base_count' => count($base), 'fixed_count' => count($fixed)]);
            return [$base, $fixed];
        } catch (\Throwable $e) {
            \Log::error('Failed to load task templates: ' . $e->getMessage());
            return [[], []];
        }
    }


    // Helper to get the next day in the week (Monday to Sunday)
    private function getNextDay($currentDay, $daysOfWeek)
    {
        $idx = array_search($currentDay, $daysOfWeek);
        if ($idx === false) return $daysOfWeek[0];
        return $daysOfWeek[($idx + 1) % count($daysOfWeek)];
    }

    // Helper to get the previous day in the week (Monday to Sunday)
    private function getPrevDay($currentDay, $daysOfWeek)
    {
        $idx = array_search($currentDay, $daysOfWeek);
        if ($idx === false) return $daysOfWeek[0];
        return $daysOfWeek[($idx - 1 + count($daysOfWeek)) % count($daysOfWeek)];
    }

    public function roomtask($roomNumber = null)
    {
        // Ensure room tasks are synchronized with current room assignments
        $this->ensureRoomTasksAreCurrentSimple();

        // Check if this is a student view (read-only mode)
        $isStudentView = request()->has('student_view') && request('student_view') == '1';

        // Dynamically include rooms/floors from the database (no hardcoding)
        $allRooms = \App\Models\Room::orderBy('room_number')->get();
        $floors = [];
        foreach ($allRooms as $room) {
            $floor = null;
            if (isset($room->floor) && $room->floor !== null && $room->floor !== '') {
                $floor = (int)$room->floor;
            } else {
                $rn = preg_replace('/[^0-9]/', '', $room->room_number);
                $roomNum = $rn === '' ? 0 : (int)$rn;
                $floor = $roomNum > 0 ? (int)floor($roomNum / 100) : 1;
            }
            if (!isset($floors[$floor])) {
                $floors[$floor] = [];
            }
            $floors[$floor][] = $room->room_number;
        }
        $floorNumbers = array_keys($floors);

        // --- Fetch room students dynamically from database ---
        // This ensures the names come from the seeded database data instead of hardcoded values.
        $roomStudents = $this->getDynamicRoomStudents();

        // Load task templates from DB (fallback to defaults if empty)
        [$baseTasks, $fixedTasks] = $this->getTaskTemplates();

        // Use sequential days: Sunday to Saturday
        $daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $currentDayIndex = date('w'); // 0 (Sunday) to 6 (Saturday)
        $currentDay = $daysOfWeek[$currentDayIndex];

        if ($roomNumber && isset($roomStudents[$roomNumber])) {
            $students = array_unique($roomStudents[$roomNumber]);
            foreach ($daysOfWeek as $day) {
                $existingTasks = RoomTask::where('room_number', $roomNumber)
                    ->where('day', $day)
                    ->exists();

                if (!$existingTasks) {
                    $shuffledStudents = $students;
                    sort($shuffledStudents); // Use deterministic sorting instead of random shuffle

                    $dayTasks = $baseTasks;
                    // Keep tasks in original order for consistency

                    // Assign each area to a student for this day (no repeats for the day)
                    $assignments = [];
                    $studentCount = count($shuffledStudents);
                    $areaCount = count($dayTasks);
                    $usedAreas = [];

                    foreach ($shuffledStudents as $studentIdx => $student) {
                        foreach ($dayTasks as $areaIdx => $areaTask) {
                            if (!in_array($areaTask['area'], $usedAreas)) {
                                $assignments[] = [
                                    'student' => $student,
                                    'area' => $areaTask['area'],
                                    'desc' => $areaTask['desc']
                                ];
                                $usedAreas[] = $areaTask['area'];
                                break;
                            }
                        }
                    }

                    foreach ($assignments as $assignment) {
                        RoomTask::create([
                            'name' => $assignment['student'],
                            'room_number' => $roomNumber,
                            'area' => $assignment['area'],
                            'desc' => $assignment['desc'],
                            'day' => $day,
                            'status' => 'unchecked'
                        ]);
                    }

                    // Add fixed tasks for everyone
                    foreach ($fixedTasks as $task) {
                        RoomTask::create([
                            'name' => 'Everyone',
                            'room_number' => $roomNumber,
                            'area' => $task['area'],
                            'desc' => $task['desc'],
                            'day' => $day,
                            'status' => 'unchecked'
                        ]);
                    }
                }
            }
        }

        $tasksByDay = [];
        foreach ($daysOfWeek as $day) {
            // Check if the day is completed for the selected room and current week/month/year
            $isCompleted = false;
            if ($roomNumber) {
                $isCompleted = \DB::table('task_histories')
                    ->where('room_number', $roomNumber)
                    ->where('day', $day)
                    ->where('completed', true)
                    ->exists();
            }

            // Load tasks from local/default connection
            $localTasks = RoomTask::where('day', $day)
                ->when($roomNumber, function ($query) use ($roomNumber) {
                    return $query->where('room_number', $roomNumber);
                })->get()->toArray();

            // Attempt to also load tasks persisted in the centralized 'login' DB
            $loginTasks = [];
            try {
                $loginQuery = \DB::connection('login')->table('roomtask')
                    ->where('day', $day);
                if ($roomNumber) $loginQuery->where('room_number', $roomNumber);
                $loginRows = $loginQuery->get();
                $loginTasks = array_map(function($r) { return (array) $r; }, $loginRows->toArray());
            } catch (\Throwable $e) {
                // If login connection/table isn't available, silently continue with local tasks
                \Log::debug('Login DB roomtask read failed: ' . $e->getMessage());
                $loginTasks = [];
            }

            // Merge local and login tasks; prefer local Eloquent objects where present
            // Create a combined array of associative arrays
            $combined = [];
            foreach ($localTasks as $lt) { $combined[] = is_array($lt) ? $lt : (array)$lt; }
            foreach ($loginTasks as $jt) {
                // Avoid duplicating rows that are identical to local ones (match by id or area+desc+room)
                $duplicate = false;
                foreach ($combined as $c) {
                    if ((isset($c['id']) && isset($jt['id']) && $c['id'] == $jt['id']) ||
                        (isset($c['area']) && isset($jt['area']) && isset($c['room_number']) && isset($jt['room_number']) && $c['area'] == $jt['area'] && $c['room_number'] == $jt['room_number'] && (isset($c['desc']) ? $c['desc'] : null) == (isset($jt['desc']) ? $jt['desc'] : null))) {
                        $duplicate = true; break;
                    }
                }
                if (!$duplicate) $combined[] = $jt;
            }

            // Group by room_number and normalize display_status
            $tasks = collect($combined)
                ->groupBy('room_number')
                ->map(function($tasks) use ($isCompleted) {
                    return collect($tasks)->map(function($task) use ($isCompleted) {
                        // Normalize object/array access
                        $t = is_object($task) ? (array)$task : (array)$task;
                        if ($isCompleted && (!empty($t['status']))) {
                            $t['display_status'] = $t['status'];
                        } else {
                            $t['display_status'] = null;
                        }
                        return $t;
                    })->toArray();
                })->toArray();

            $tasksByDay[$day] = [
                'isCompleted' => $isCompleted,
                'tasks' => $tasks
            ];
        }

        $nextDay = $this->getNextDay($currentDay, $daysOfWeek);
        $prevDay = $this->getPrevDay($currentDay, $daysOfWeek);

        // Get week, month, year from request or default to current
        $week = request('week') ?? '';
        $month = request('month') ?? date('n');
        $year = request('year') ?? date('Y');

        // Retrieve feedbacks for the selected room, day, week, month, year
        $feedbacks = [];
        if ($roomNumber) {
            $feedbacks = \App\Models\FeedbackRoom::where('room_number', $roomNumber)
                ->where('day', $currentDay)
                ->when($week, function($q) use ($week) { return $q->where('week', $week); })
                ->when($month, function($q) use ($month) { return $q->where('month', $month); })
                ->when($year, function($q) use ($year) { return $q->where('year', $year); })
                ->orderByDesc('id')
                ->get();
        }

        // Load latest persisted rotation schedule for this room (if available)
        $persistedSchedule = null;
        try {
            if ($roomNumber) {
                $rs = \App\Models\RotationSchedule::where('room', $roomNumber)
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($rs) {
                    $persistedSchedule = $rs->toArray();

                    // Overlay persisted assignments onto tasksByDay so the generated
                    // rotation is visible for every date in the saved range
                    if (!empty($persistedSchedule['schedule_map']) && is_array($persistedSchedule['schedule_map'])) {
                        try {
                            foreach ($persistedSchedule['schedule_map'] as $iso => $assigns) {
                                try {
                                    $d = new \Carbon\Carbon($iso);
                                    $dayName = $d->format('l');

                                    // only overlay for the selected room
                                    if (!isset($tasksByDay[$dayName]['tasks'])) continue;

                                    // normalize assigns: suport both map area=>name and list of entries
                                    $normalized = [];
                                    if (array_values($assigns) !== $assigns && is_array($assigns)) {
                                        // associative area => student
                                        foreach ($assigns as $area => $student) {
                                            $normalized[] = ['task_area' => $area, 'student_name' => $student];
                                        }
                                    } else {
                                        // list of ['task_area'=>..., 'student_name'=>...]
                                        foreach ($assigns as $a) {
                                            if (is_array($a) && (isset($a['task_area']) || isset($a['area']))) {
                                                $normalized[] = [
                                                    'task_area' => $a['task_area'] ?? ($a['area'] ?? null),
                                                    'student_name' => $a['student_name'] ?? ($a['student'] ?? null) ?? null
                                                ];
                                            }
                                        }
                                    }

                                    // Apply names to tasks for this room/day
                                    if (isset($tasksByDay[$dayName]['tasks'][$roomNumber])) {
                                        foreach ($tasksByDay[$dayName]['tasks'][$roomNumber] as $ti => $taskRow) {
                                            $areaVal = isset($taskRow['area']) ? $taskRow['area'] : (isset($taskRow['task_area']) ? $taskRow['task_area'] : null);
                                            if (!$areaVal) continue;
                                            foreach ($normalized as $as) {
                                                if (!isset($as['task_area'])) continue;
                                                if (strcasecmp(trim($as['task_area']), trim($areaVal)) === 0) {
                                                    // overlay assigned student name if present
                                                    if (!empty($as['student_name'])) {
                                                        $tasksByDay[$dayName]['tasks'][$roomNumber][$ti]['name'] = $as['student_name'];
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } catch (\Throwable $inner) {
                                    continue;
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Failed to overlay persisted schedule onto tasksByDay: ' . $e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->debug('RotationSchedule not available when loading roomtask view: ' . $e->getMessage());
            $persistedSchedule = null;
        }



        return view('roomtask', [
            'tasksByDay' => $tasksByDay,
            'daysOfWeek' => $daysOfWeek,
            'floors' => $floorNumbers,
            'rooms' => $floors,
            'currentDay' => $currentDay,
            'selectedRoom' => $roomNumber,
            'nextDay' => $nextDay,
            'prevDay' => $prevDay,
            'feedbacks' => $feedbacks,
            'selectedWeek' => $week,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'isStudentView' => $isStudentView,
            'roomStudents' => $roomStudents,
            'persistedSchedule' => $persistedSchedule,
        ]);
    }


    public function updateTaskStatus(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'taskId' => 'required|integer|exists:roomtask,id',
                'status' => 'required|string',
                'week' => 'nullable|string',
                'month' => 'nullable|string',
                'year' => 'nullable|string',
            ]);

            // Find the task by ID
            $task = RoomTask::find($validated['taskId']);
            if ($task) {
                // Update the task's status
                $task->status = $validated['status'];
                $task->week = $validated['week'];
                $task->month = $validated['month'];
                $task->year = $validated['year'];
                $task->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Task status updated successfully',
                    'task' => $task
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found',
                ], 404);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Task status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markDayComplete(Request $request)
    {
        try {
            $validated = $request->validate([
                'day' => 'required|string',
                'room' => 'required|string',
                'week' => 'required|string',
                'month' => 'required|string',
                'year' => 'required|string',
                'dayKey' => 'required|string',
                'tasks' => 'required|array'
            ]);

            $day = $validated['day'];
            $room = $validated['room'];
            $week = $validated['week'];
            $month = $validated['month'];
            $year = $validated['year'];
            $taskStatuses = $validated['tasks'];
            $dayKey = $validated['dayKey'] ?? null;

            // Parse dayKey into a Carbon instance and normalized date string for the 'day' column
            try {
                $tz = config('app.timezone') ?: 'UTC';
                $dateForCreated = $dayKey ? \Carbon\Carbon::createFromFormat('Y-m-d', $dayKey, $tz)->startOfDay() : now();
            } catch (\Exception $e) {
                $dateForCreated = now();
            }
            $dayNormalized = $dateForCreated->toDateString();

            \Log::info('Mark day complete request', [
                'room' => $room,
                'day' => $day,
                'week' => $week,
                'month' => $month,
                'year' => $year,
                'tasks_count' => count($taskStatuses)
            ]);

            // Update each task with its corresponding status
            foreach ($taskStatuses as $status) {
                if (!is_array($status) || !isset($status['id'], $status['status'])) {
                    \Log::warning('Invalid task status format', ['status' => $status]);
                    continue;
                }

                // Find the task by ID
                $task = \App\Models\RoomTask::find($status['id']);
                if ($task) {
                    $task->status = $status['status'];
                    $task->week = $week;
                    $task->month = $month;
                    $task->year = $year;
                    $task->save();

                    \Log::info('Updated task', [
                        'task_id' => $task->id,
                        'status' => $task->status
                    ]);

                    // Insert or update task_histories for this task_id
                    \DB::table('task_histories')->updateOrInsert(
                        [
                            'room_number' => $room,
                            'task_id' => $task->id,
                            'day' => $dayNormalized,
                            'week' => $week,
                            'month' => $month,
                            'year' => $year
                        ],
                        [
                            'completed' => true,
                            'status' => $task->status,
                            'assigned_to' => $task->name,
                            'task_area' => $task->area,
                            'task_description' => $task->desc,
                            'filter_type' => 'daily',
                            'created_at' => $dateForCreated->toDateTimeString(),
                            'updated_at' => $dateForCreated->toDateTimeString()
                        ]
                    );
                } else {
                    \Log::warning('Task not found', ['task_id' => $status['id']]);
                }
            }

            // Mark the day as completed in the database (summary row, task_id = null)
            \DB::table('task_histories')->updateOrInsert(
                [
                    'room_number' => $room,
                    'task_id' => null,
                    'day' => $dayNormalized,
                    'week' => $week,
                    'month' => $month,
                    'year' => $year
                ],
                [
                    'completed' => true,
                    'status' => 'completed',
                    'filter_type' => 'daily',
                    'created_at' => $dateForCreated->toDateTimeString(),
                    'updated_at' => $dateForCreated->toDateTimeString()
                ]
            );
            
            // Create weekly summary entry
            \DB::table('task_histories')->updateOrInsert(
                [
                    'room_number' => $room,
                    'task_id' => null,
                    'week' => $week,
                    'month' => $month,
                    'year' => $year,
                    'filter_type' => 'weekly'
                ],
                [
                    'completed' => true,
                    'status' => 'completed',
                    'created_at' => $dateForCreated->toDateTimeString(),
                    'updated_at' => $dateForCreated->toDateTimeString()
                ]
            );
            
            // Create monthly summary entry
            \DB::table('task_histories')->updateOrInsert(
                [
                    'room_number' => $room,
                    'task_id' => null,
                    'month' => $month,
                    'year' => $year,
                    'filter_type' => 'monthly'
                ],
                [
                    'completed' => true,
                    'status' => 'completed',
                    'created_at' => $dateForCreated->toDateTimeString(),
                    'updated_at' => $dateForCreated->toDateTimeString()
                ]
            );

            \Log::info('Day marked as completed successfully');

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
            \Log::error('Mark day complete error: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while marking day as complete',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function saveTask(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'area' => 'required|string',
                'desc' => 'required|string',
                'day' => 'required|string',
                'room' => 'required|string',
                'mode' => 'required|string|in:add,edit',
                'taskIndex' => 'nullable|integer',
                'taskId' => 'nullable|integer'
            ]);

            if ($validated['mode'] === 'edit') {
                // Use taskId if provided, else fallback to index (legacy)
                $task = null;
                if (!empty($validated['taskId'])) {
                    $task = RoomTask::where('id', $validated['taskId'])
                        ->where('room_number', $validated['room'])
                        ->where('day', $validated['day'])
                        ->first();
                }
                if (!$task && isset($validated['taskIndex'])) {
                    $task = RoomTask::where('room_number', $validated['room'])
                        ->where('day', $validated['day'])
                        ->skip($validated['taskIndex'])
                        ->first();
                }
                if ($task) {
                    $task->update([
                        'name' => $validated['name'],
                        'area' => $validated['area'],
                        'desc' => $validated['desc']
                    ]);
                } else {
                    throw new \Exception('Task not found');
                }
            } else {
                // --- NEW LOGIC: Add student to all days and reshuffle assignments ---
                $daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                $room = $validated['room'];
                $newStudent = $validated['name'];
                $area = $validated['area'];
                $desc = $validated['desc'];

                // 1. Add the new student/area/desc to all days if not already present
                foreach ($daysOfWeek as $day) {
                    $exists = RoomTask::where('room_number', $room)
                        ->where('day', $day)
                        ->where('name', $newStudent)
                        ->where('area', $area)
                        ->where('desc', $desc)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->exists();
                    if (!$exists) {
                        $newTask = new RoomTask();
                        $newTask->name = $newStudent;
                        $newTask->area = $area;
                        $newTask->desc = $desc;
                        $newTask->day = $day;
                        $newTask->room_number = $room;
                        $newTask->status = 'not yet';
                        $newTask->save();
                    }
                }

                // 2. For each week, assign each student to each area exactly once (round-robin)
                //    This ensures no student gets the same area twice in a week

                // Load base and fixed tasks from DB templates
                [$baseTasks, $fixedTasks] = $this->getTaskTemplates();

                // Get all student names (excluding "Everyone") for this room
                $allStudentNames = [];
                foreach ($daysOfWeek as $day) {

                    $studentTasks = RoomTask::where('room_number', $room)
                        ->where('day', $day)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->whereRaw('LOWER(name) != ?', ['everyone'])
                        ->pluck('name')
                        ->toArray();
                    $allStudentNames = array_merge($allStudentNames, $studentTasks);
                }
                $students = array_values(array_unique($allStudentNames));
                $studentCount = count($students);

                // Get all unique areas (order: baseTasks first, then any custom areas)
                $areaDescMap = [];
                foreach ($baseTasks as $bt) {
                    $areaDescMap[$bt['area']] = $bt['desc'];
                }
                // Add any custom areas from existing tasks
                foreach ($daysOfWeek as $day) {
                    $studentTasks = RoomTask::where('room_number', $room)
                        ->where('day', $day)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->whereRaw('LOWER(name) != ?', ['everyone'])
                        ->get();
                    foreach ($studentTasks as $task) {
                        if (!isset($areaDescMap[$task->area])) {
                            $areaDescMap[$task->area] = $task->desc;
                        }
                    }
                }
                $orderedAreas = array_keys($areaDescMap);
                $areaCount = count($orderedAreas);

                // Round-robin assignment: for each day, rotate students so that each gets each area once per week
                // Remove all existing student tasks for all days (excluding "Everyone")
                foreach ($daysOfWeek as $day) {
                    RoomTask::where('room_number', $room)
                        ->where('day', $day)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->whereRaw('LOWER(name) != ?', ['everyone'])
                        ->delete();
                }

                // For each day, assign areas to students using round-robin
                for ($d = 0; $d < count($daysOfWeek); $d++) {
                    $day = $daysOfWeek[$d];
                    // Rotate students for this day
                    $rotatedStudents = $students;
                    // For each day, rotate by $d positions
                    if ($studentCount > 1) {
                        $rotatedStudents = array_merge(
                            array_slice($students, $d % $studentCount),
                            array_slice($students, 0, $d % $studentCount)
                        );
                    }
                    // Assign areas to students
                    for ($i = 0; $i < $studentCount; $i++) {
                        $areaIdx = $i % $areaCount;
                        $areaKey = $orderedAreas[$areaIdx];
                        $descVal = $areaDescMap[$areaKey];
                        RoomTask::create([
                            'name' => $rotatedStudents[$i],
                            'room_number' => $room,
                            'area' => $areaKey,
                            'desc' => $descVal,
                            'day' => $day,
                            'status' => 'not yet'
                        ]);
                    }
                }
                // "Everyone" tasks remain untouched
            }

            return response()->json([
                'success' => true,
                'message' => 'Task saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function getTaskStatuses(Request $request)
    {
        try {
            $validated = $request->validate([
                'day' => 'required|string',
                // room may be numeric in JSON (e.g. 302) so accept required and normalize below
                'room' => 'required',
                'week' => 'required|string',
                'month' => 'required|string',
                'year' => 'required|string'
            ]);

            $day = $validated['day'];
            // normalize room to string to satisfy downstream queries and avoid validation type issues
            $room = isset($validated['room']) ? (string)$validated['room'] : '';
            $week = $validated['week'];
            $month = $validated['month'];
            $year = $validated['year'];

            // Always get the base (template) tasks for this room/day (week/month/year null)
            $baseTasks = RoomTask::where('day', $day)
                ->where('room_number', $room)
                ->whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->orderBy('id')
                ->get(['id', 'name', 'area', 'desc']);

            // Get any tasks for this room/day/week/month/year (with status)
            $statusTasks = RoomTask::where('day', $day)
                ->where('room_number', $room)
                ->where('week', $week)
                ->where('month', $month)
                ->where('year', $year)
                ->get(['id', 'name', 'area', 'desc', 'status']);

            // Build a map for quick lookup by (name, area, desc)
            $statusMap = [];
            foreach ($statusTasks as $st) {
                $key = $st->name . '|' . $st->area . '|' . $st->desc;
                $statusMap[$key] = $st;
            }

            // Merge: for each base task, use status from statusTasks if exists, else status = 'not yet'
            $mergedTasks = [];
            foreach ($baseTasks as $bt) {
                $key = $bt->name . '|' . $bt->area . '|' . $bt->desc;
                if (isset($statusMap[$key])) {
                    $mergedTasks[] = [
                        'id' => $statusMap[$key]->id,
                        'name' => $bt->name,
                        'area' => $bt->area,
                        'desc' => $bt->desc,
                        'status' => $statusMap[$key]->status ?? 'not yet'
                    ];
                } else {
                    $mergedTasks[] = [
                        'id' => $bt->id,
                        'name' => $bt->name,
                        'area' => $bt->area,
                        'desc' => $bt->desc,
                        'status' => 'not yet'
                    ];
                }
            }

            // Check for a persisted rotation schedule in the Login DB and overlay assigned students
            try {
                $requestedDate = null;
                // Attempt to compute a concrete ISO date from week/month/year/day if possible
                if (is_numeric($week) && is_numeric($month) && is_numeric($year)) {
                    // find first day of week in the month/year and then map day name
                    $requestedDate = null; // fallback — better to rely on client sending date in future
                }

                $rotation = \App\Models\RotationSchedule::where('room', $room)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($rotation && $rotation->schedule_map && is_array($rotation->schedule_map)) {
                    // Determine which ISO date from schedule_map corresponds to the requested day
                    $useIso = null;

                    // 1) If client provided explicit week/month/year, try to compute exact date using helper
                    if (is_numeric($week) && is_numeric($month) && is_numeric($year)) {
                        try {
                            $computed = $this->getDateForWeekDay(intval($year), intval($month), intval($week), $day);
                            if ($computed && isset($rotation->schedule_map[$computed])) {
                                $useIso = $computed;
                            }
                        } catch (\Throwable $e) {
                            // ignore and fall back
                        }
                    }

                    // 2) If client passed a dayKey (explicit ISO date), prefer that
                    if (!$useIso && request()->has('dayKey')) {
                        $dk = request()->input('dayKey');
                        try { $dkd = (new \Carbon\Carbon($dk))->toDateString(); if (isset($rotation->schedule_map[$dkd])) $useIso = $dkd; } catch (\Throwable $e) { }
                    }

                    // 3) Fallback: pick the schedule_map entry whose day name matches and is within start/end range
                    if (!$useIso) {
                        $candidates = [];
                        foreach ($rotation->schedule_map as $iso => $assigns) {
                            try {
                                $d = new \Carbon\Carbon($iso);
                                $dayMatches = ($d->format('l') === $day);
                                $withinRange = true;
                                if (isset($rotation->start_date) && $rotation->start_date) {
                                    $withinRange = $withinRange && ($d->gte(new \Carbon\Carbon($rotation->start_date)));
                                }
                                if (isset($rotation->end_date) && $rotation->end_date) {
                                    $withinRange = $withinRange && ($d->lte(new \Carbon\Carbon($rotation->end_date)));
                                }
                                if ($dayMatches && $withinRange) $candidates[] = $iso;
                            } catch (\Throwable $e) { }
                        }
                        // prefer the candidate closest to the requested week/month/year if provided, otherwise most recent
                        if (!empty($candidates)) {
                            if (is_numeric($week) && is_numeric($month) && is_numeric($year)) {
                                try {
                                    $target = \Carbon\Carbon::createFromFormat('Y-n-j', $this->getDateForWeekDay(intval($year), intval($month), intval($week), $day));
                                } catch (\Throwable $e) { $target = null; }
                                if ($target) {
                                    usort($candidates, function($a,$b) use ($target) {
                                        $da = new \Carbon\Carbon($a); $db = new \Carbon\Carbon($b);
                                        return abs($da->diffInDays($target)) <=> abs($db->diffInDays($target));
                                    });
                                    $useIso = $candidates[0];
                                } else {
                                    $useIso = end($candidates);
                                }
                            } else {
                                $useIso = end($candidates);
                            }
                        }
                    }

                    if ($useIso) {
                        $assigns = $rotation->schedule_map[$useIso] ?? [];
                        // Normalize assigns (support associative and array-of-objects)
                        $normalized = [];
                        if (is_array($assigns)) {
                            if (array_values($assigns) !== $assigns) {
                                foreach ($assigns as $area => $student) {
                                    $normalized[] = ['task_area' => $area, 'student_name' => $student];
                                }
                            } else {
                                foreach ($assigns as $a) {
                                    if (is_array($a)) {
                                        $normalized[] = [
                                            'task_area' => $a['task_area'] ?? ($a['area'] ?? null),
                                            'student_name' => $a['student_name'] ?? ($a['student'] ?? null) ?? null
                                        ];
                                    }
                                }
                            }
                        }

                        // Assign by matching area
                        foreach ($mergedTasks as &$mt) {
                            foreach ($normalized as $as) {
                                if (isset($as['task_area']) && isset($mt['area']) && strcasecmp(trim($as['task_area']), trim($mt['area'])) === 0) {
                                    // overlay the assigned student name into 'name'
                                    if (!empty($as['student_name'])) $mt['name'] = $as['student_name'];
                                    break;
                                }
                            }
                        }
                        unset($mt);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Rotation overlay failed: ' . $e->getMessage());
            }

            // If no base tasks (should not happen), fallback to any tasks for this day/room
            if (empty($mergedTasks)) {
                $fallbackTasks = RoomTask::where('day', $day)
                    ->where('room_number', $room)
                    ->get(['id', 'name', 'area', 'desc', 'status']);
                foreach ($fallbackTasks as $ft) {
                    $mergedTasks[] = [
                        'id' => $ft->id,
                        'name' => $ft->name,
                        'area' => $ft->area,
                        'desc' => $ft->desc,
                        'status' => $ft->status ?? 'not yet'
                    ];
                }
            }

            // Check if the day is marked as completed (guard column existence)
            $isCompleted = false;
            if (\Schema::hasTable('task_histories') && \Schema::hasColumn('task_histories', 'completed')) {
                $isCompleted = \DB::table('task_histories')
                    ->where('room_number', $room)
                    ->where('day', $day)
                    ->where('week', $week)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('completed', true)
                    ->exists();
            }

            return response()->json([
                'success' => true,
                'tasks' => $mergedTasks,
                'completed' => $isCompleted
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return structured validation errors instead of a 500
            \Log::warning('Get task statuses validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Get task statuses error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting task statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Checks if a week is fully completed for a room and, if so, records it in task_history.
     * This can be called from a cron job or after marking a day's completion.
     */
    public function checkAndRecordWeekCompletion($room, $week, $month, $year)
    {
        $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        $allDaysCompleted = true;

        foreach ($daysOfWeek as $day) {
            $tasks = \App\Models\RoomTask::where('room_number', $room)
                ->where('day', $day)
                ->where('week', $week)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            $statuses = $tasks->pluck('status')->filter(function($status) {
                return !is_null($status) && $status !== '';
            });

            $uniqueTaskIds = $tasks->pluck('id')->unique();
            $isDayCompleted = ($tasks->count() > 0)
                && ($statuses->count() === $tasks->count())
                && ($uniqueTaskIds->count() === $tasks->count());

            if (!$isDayCompleted) {
                $allDaysCompleted = false;
            }
        }

        // If all 7 days are completed, record in task_histories (if not already)
        if ($allDaysCompleted) {
            foreach ($daysOfWeek as $day) {
                \DB::table('task_histories')->updateOrInsert(
                    [
                        'room_number' => $room,
                        'day' => $day,
                        'week' => $week,
                        'month' => $month,
                        'year' => $year
                    ],
                    [
                        'completed' => true,
                        'status' => 'completed',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    public function showTaskHistory(Request $request)
    {
        // Provide defaults and request filters
        $room = $request->input('room', null);
        $week = $request->input('week', null);
        $month = $request->input('month', null);
        $year = $request->input('year', null);
        $filterType = $request->input('filter_type', 'daily');

        $dayMap = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Query task histories and eager load related room task when available
        $historiesQuery = \App\Models\TaskHistory::with('roomTask');

        if ($room) {
            $historiesQuery->where('room_number', $room);
        }

        // Normalize filter inputs or default to current
        $week = $week ?: now()->weekOfYear;
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        // Apply filters based on requested filter type
        if ($filterType === 'daily') {
            // Daily: match room + week/month/year and show entries where filter_type = daily OR specific task entries
            $historiesQuery->where('week', $week)->where('month', $month)->where('year', $year)->where(function($q){
                $q->where('filter_type', 'daily')->orWhereNull('filter_type');
            });
        } elseif ($filterType === 'weekly') {
            // Weekly: match week/month/year and prefer weekly summaries but include daily entries for that week
            $historiesQuery->where('week', $week)->where('month', $month)->where('year', $year);
        } elseif ($filterType === 'monthly') {
            // Monthly: match month/year
            $historiesQuery->where('month', $month)->where('year', $year);
        }

        $taskHistories = $historiesQuery->orderBy('created_at', 'desc')->get();

        // Provide list of rooms for the select (from rooms table)
        $rooms = \App\Models\Room::orderBy('room_number')->get(['room_number']);

        // Provide all known areas from TaskTemplate (fallbacks)
        $allAreas = array_keys(\App\Models\TaskTemplate::where('is_active', true)->pluck('description','area')->toArray());

        // Build studentNames and matrix as previously used by the blade if needed
        $studentNames = [];
        $matrix = [];
        // (matrix building omitted for history listing - kept empty unless the blade has data)

        return view('task-history', [
            'taskHistories' => $taskHistories,
            'rooms' => $rooms,
            'room' => $room,
            'week' => $week,
            'month' => $month,
            'year' => $year,
            'filterType' => $filterType,
            'dayMap' => $dayMap,
            'studentNames' => $studentNames,
            'matrix' => $matrix,
            'allAreas' => $allAreas
        ]);
    }

    private function getDateForWeekDay($year, $month, $week, $dayName)
    {
        // Find the first day of the month
        $firstOfMonth = \Carbon\Carbon::create($year, $month, 1);
        // Find the first Monday before or on the 1st
        $firstMonday = $firstOfMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        // Calculate the start of the requested week
        $weekStart = $firstMonday->copy()->addWeeks($week - 1);
        // Map day name to offset
        $dayOffsets = [
            "Monday" => 0, "Tuesday" => 1, "Wednesday" => 2, "Thursday" => 3,
            "Friday" => 4, "Saturday" => 5, "Sunday" => 6
        ];
        if (!isset($dayOffsets[$dayName])) return null;
        $date = $weekStart->copy()->addDays($dayOffsets[$dayName]);
        return $date->format('Y-m-d');
    }

    public function apiCheckWeekCompletion(Request $request)
    {
        $room = $request->input('room');
        $week = $request->input('week');
        $month = $request->input('month');
        $year = $request->input('year');
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $allDaysCompleted = true;
        foreach ($daysOfWeek as $day) {
            $completed = \DB::table('task_histories')
                ->where('room_number', $room)
                ->where('day', $day)
                ->where('week', $week)
                ->where('month', $month)
                ->where('year', $year)
                ->where('completed', true)
                ->exists();
            if (!$completed) {
                $allDaysCompleted = false;
                break;
            }
        }
        return response()->json(['completed' => $allDaysCompleted]);
    }

    // --- API: Add student to room assignment ---
    public function apiAddStudent(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:10',
            'name' => 'required|string|max:100'
        ]);
        $room = $validated['room'];
        $name = trim($validated['name']);

        try {
            // Validate student name format first
            $nameValidation = StudentValidationService::validateStudentNameFormat($name);
            if (!$nameValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $nameValidation['message'],
                    'error_type' => 'validation'
                ], 422);
            }

            // Validate that student exists in Login database
            $studentValidation = StudentValidationService::validateStudentExists($name);
            if (!$studentValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $studentValidation['message'],
                    'suggestions' => $studentValidation['suggestions'] ?? [],
                    'error_type' => 'not_found'
                ], 404);
            }

            $student = $studentValidation['student'];

            // Remove student from any previous room assignments first
            $previousAssignments = \App\Models\RoomAssignment::where('student_id', $student->user_id)->get();
            $previousRooms = [];

            foreach ($previousAssignments as $prevAssignment) {
                $previousRooms[] = $prevAssignment->room_number;
                \Log::info("Removing student {$student->user_fname} {$student->user_lname} from previous room {$prevAssignment->room_number}");
                $prevAssignment->delete();
            }

            // Validate room assignment constraints
            $roomValidation = StudentValidationService::validateRoomAssignment(
                $room,
                $student->user_id,
                $student->gender
            );

            if (!$roomValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $roomValidation['message'],
                    'error_type' => 'room_constraint'
                ], 422);
            }

            $roomModel = $roomValidation['room'];
            $currentOccupancy = $roomValidation['current_occupancy'];

            // Get student batch from StudentDetail if available
            $batchYear = StudentValidationService::getStudentBatch($student->user_id);

            // Create room assignment
            $assignment = \App\Models\RoomAssignment::create([
                'room_number' => $room,
                'student_id' => $student->user_id,
                'student_name' => $student->user_fname . ' ' . $student->user_lname,
                'student_gender' => $student->gender,
                'batch_year' => $batchYear,
                'assignment_order' => $currentOccupancy,
                'room_capacity' => $roomModel->capacity,
                'assigned_at' => now()
            ]);

            // Clear any caching that might interfere with immediate updates
            \Cache::forget('room_assignments_' . $room);
            \Cache::forget('dynamic_room_students');

            // Get updated room assignments for immediate dashboard update
            $updatedRoomData = $this->getRoomAssignmentsForDashboard();

            $message = "Student '{$student->user_fname} {$student->user_lname}' added to room {$room} successfully.";
            if (!empty($previousRooms)) {
                $previousRoomsList = implode(', ', $previousRooms);
                $message = "Student '{$student->user_fname} {$student->user_lname}' moved successfully from room(s) {$previousRoomsList} to room {$room}.";
            }

            \Log::info('Student added successfully', [
                'student_id' => $student->user_id,
                'student_name' => $student->user_fname . ' ' . $student->user_lname,
                'room' => $room,
                'previous_rooms' => $previousRooms
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'assignment' => $assignment,
                'updated_room_data' => $updatedRoomData,
                'room_students' => $updatedRoomData[$room] ?? [],
                'previous_rooms' => $previousRooms,
                'student_info' => [
                    'id' => $student->user_id,
                    'name' => $student->user_fname . ' ' . $student->user_lname,
                    'gender' => $student->gender,
                    'batch' => $batchYear
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding student', [
                'room' => $room,
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while adding the student. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }



    // --- API: Edit student assignment in room ---
    public function apiEditStudent(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:10',
            'old_name' => 'required|string|max:100',
            'new_name' => 'required|string|max:100'
        ]);
        $room = $validated['room'];
        $old = trim($validated['old_name']);
        $new = trim($validated['new_name']);

        try {
            // Validate new student name format
            $nameValidation = StudentValidationService::validateStudentNameFormat($new);
            if (!$nameValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $nameValidation['message'],
                    'error_type' => 'validation'
                ], 422);
            }

            // Check if names are the same
            if ($old === $new) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a different student name or cancel the edit.',
                    'error_type' => 'validation'
                ], 422);
            }

            // Find the room
            $roomModel = \App\Models\Room::where('room_number', $room)->first();
            if (!$roomModel) {
                return response()->json([
                    'success' => false,
                    'message' => "Room {$room} not found.",
                    'error_type' => 'not_found'
                ], 404);
            }

            // Find the existing assignment - try multiple approaches
            $existingAssignment = \App\Models\RoomAssignment::where('room_number', $room)
                ->where('student_name', $old)
                ->first();

            // If not found by exact name, try fuzzy matching
            if (!$existingAssignment) {
                $existingAssignment = \App\Models\RoomAssignment::where('room_number', $room)
                    ->where('student_name', 'LIKE', "%{$old}%")
                    ->first();
            }

            if (!$existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => "Student '{$old}' not found in room {$room}. The student may have already been removed.",
                    'error_type' => 'not_found'
                ], 404);
            }

            // Validate that new student exists in Login database
            $studentValidation = StudentValidationService::validateStudentExists($new);
            if (!$studentValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $studentValidation['message'],
                    'suggestions' => $studentValidation['suggestions'] ?? [],
                    'error_type' => 'not_found'
                ], 404);
            }

            $newStudent = $studentValidation['student'];

            // Remove new student from any other room assignments first
            $previousAssignments = \App\Models\RoomAssignment::where('student_id', $newStudent->user_id)
                ->where('id', '!=', $existingAssignment->id)
                ->get();

            $previousRooms = [];
            foreach ($previousAssignments as $prevAssignment) {
                $previousRooms[] = $prevAssignment->room_number;
                \Log::info("Removing student {$newStudent->user_fname} {$newStudent->user_lname} from previous room {$prevAssignment->room_number}");
                $prevAssignment->delete();
            }

            // Check if new student is already assigned to this specific room
            $alreadyAssigned = \App\Models\RoomAssignment::where('room_number', $room)
                ->where('student_id', $newStudent->user_id)
                ->where('id', '!=', $existingAssignment->id)
                ->exists();

            if ($alreadyAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => "Student '{$new}' is already assigned to room {$room}.",
                    'error_type' => 'duplicate'
                ], 422);
            }

            // Check gender compatibility with other students in the room
            $otherAssignments = \App\Models\RoomAssignment::where('room_number', $room)
                ->where('id', '!=', $existingAssignment->id)
                ->first();

            if ($otherAssignments && $otherAssignments->student_gender !== $newStudent->gender) {
                $genderText = $otherAssignments->student_gender === 'M' ? 'male' : 'female';
                $newGenderText = $newStudent->gender === 'M' ? 'male' : 'female';
                $availableRooms = StudentValidationService::getAvailableRoomsForGender($newStudent->gender);
                $suggestion = !empty($availableRooms)
                    ? " Available {$newGenderText} rooms: " . implode(', ', array_slice($availableRooms, 0, 3))
                    : " No available {$newGenderText} rooms found.";

                return response()->json([
                    'success' => false,
                    'message' => "Gender mismatch. Room {$room} is assigned to {$genderText} students only.{$suggestion}",
                    'error_type' => 'gender_mismatch'
                ], 422);
            }

            // Get new student batch from StudentDetail if available
            $batchYear = StudentValidationService::getStudentBatch($newStudent->user_id);

            // Store old student info for logging
            $oldStudentName = $existingAssignment->student_name;

            // Update the assignment
            $existingAssignment->update([
                'student_id' => $newStudent->user_id,
                'student_name' => $newStudent->user_fname . ' ' . $newStudent->user_lname,
                'student_gender' => $newStudent->gender,
                'batch_year' => $batchYear,
                'assigned_at' => now()
            ]);

            // Clear any caching that might interfere with immediate updates
            \Cache::forget('room_assignments_' . $room);
            \Cache::forget('dynamic_room_students');

            // Get updated room assignments for immediate dashboard update
            $updatedRoomData = $this->getRoomAssignmentsForDashboard();

            $message = "Student assignment updated successfully. '{$oldStudentName}' replaced with '{$newStudent->user_fname} {$newStudent->user_lname}' in room {$room}.";
            if (!empty($previousRooms)) {
                $previousRoomsList = implode(', ', $previousRooms);
                $message .= " Student was also removed from room(s): {$previousRoomsList}.";
            }

            \Log::info('Student assignment edited successfully', [
                'room' => $room,
                'old_student' => $oldStudentName,
                'new_student' => $newStudent->user_fname . ' ' . $newStudent->user_lname,
                'new_student_id' => $newStudent->user_id,
                'previous_rooms' => $previousRooms
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'assignment' => $existingAssignment->fresh(),
                'updated_room_data' => $updatedRoomData,
                'room_students' => $updatedRoomData[$room] ?? [],
                'previous_rooms' => $previousRooms,
                'student_info' => [
                    'id' => $newStudent->user_id,
                    'name' => $newStudent->user_fname . ' ' . $newStudent->user_lname,
                    'gender' => $newStudent->gender,
                    'batch' => $batchYear
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error editing student assignment', [
                'room' => $room,
                'old_name' => $old,
                'new_name' => $new,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the student assignment. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    // --- API: Delete student from room assignment ---
    public function apiDeleteStudent(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string|max:10',
            'name' => 'required|string|max:100'
        ]);
        $room = $validated['room'];
        $name = trim($validated['name']);

        try {
            // Find the room
            $roomModel = \App\Models\Room::where('room_number', $room)->first();
            if (!$roomModel) {
                return response()->json([
                    'success' => false,
                    'message' => "Room {$room} not found.",
                    'error_type' => 'not_found'
                ], 404);
            }

            // Find and delete the assignment - try multiple approaches
            $assignment = \App\Models\RoomAssignment::where('room_number', $room)
                ->where('student_name', $name)
                ->first();

            // If not found by exact name, try fuzzy matching
            if (!$assignment) {
                $assignment = \App\Models\RoomAssignment::where('room_number', $room)
                    ->where('student_name', 'LIKE', "%{$name}%")
                    ->first();
            }

            if (!$assignment) {
                // Get current students in room for better error message
                $currentStudents = \App\Models\RoomAssignment::where('room_number', $room)
                    ->pluck('student_name')
                    ->toArray();

                $suggestion = !empty($currentStudents)
                    ? " Current students in room {$room}: " . implode(', ', $currentStudents)
                    : " Room {$room} has no students assigned.";

                return response()->json([
                    'success' => false,
                    'message' => "Student '{$name}' not found in room {$room}.{$suggestion}",
                    'error_type' => 'not_found',
                    'current_students' => $currentStudents
                ], 404);
            }

            // Store assignment info for response and logging
            $studentName = $assignment->student_name;
            $studentId = $assignment->student_id;

            // Delete the assignment
            $assignment->delete();

            // Reorder remaining assignments
            $remainingAssignments = \App\Models\RoomAssignment::where('room_number', $room)
                ->orderBy('assignment_order')
                ->get();

            foreach ($remainingAssignments as $index => $remainingAssignment) {
                $remainingAssignment->update(['assignment_order' => $index]);
            }

            // Clear any caching that might interfere with immediate updates
            \Cache::forget('room_assignments_' . $room);
            \Cache::forget('dynamic_room_students');

            // Get updated room assignments for immediate dashboard update
            $updatedRoomData = $this->getRoomAssignmentsForDashboard();

            \Log::info('Student removed successfully', [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'room' => $room,
                'remaining_students' => count($updatedRoomData[$room] ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Student '{$studentName}' removed successfully from room {$room}.",
                'updated_room_data' => $updatedRoomData,
                'room_students' => $updatedRoomData[$room] ?? [],
                'removed_student' => [
                    'id' => $studentId,
                    'name' => $studentName
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting student', [
                'room' => $room,
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while removing the student. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    /**
     * Fetch student names dynamically from the database with persistent assignments
     * Returns an array of room assignments with student names from the seeded database
     * Assignments persist across page reloads, sessions, and only change when capacity is modified
     */
    public function getDynamicRoomStudents()
    {
        try {
            // COMPREHENSIVE FIX: Absolute session priority with complete validation
            $sessionCapacity = session('room_capacity');
            $reassignmentInProgress = session('reassignment_in_progress');

            \Log::info('getDynamicRoomStudents called', [
                'session_capacity' => $sessionCapacity,
                'reassignment_in_progress' => $reassignmentInProgress
            ]);

            // RULE 1 (Revised): Only honor session capacity during an active reassignment
            if ($reassignmentInProgress && $sessionCapacity && is_numeric($sessionCapacity) && $sessionCapacity > 0) {
                \Log::info('Session capacity found - using as absolute priority', [
                    'session_capacity' => $sessionCapacity
                ]);

                // Check if we have assignments that match the session capacity EXACTLY
                $matchingAssignments = RoomAssignment::where('room_capacity', $sessionCapacity)
                    ->orderBy('room_number')
                    ->orderBy('assignment_order')
                    ->get();

                if ($matchingAssignments->count() > 0) {
                    // Verify each room has exactly the session capacity number of students
                    $roomCounts = $matchingAssignments->groupBy('room_number')
                        ->map(function ($assignments) {
                            return $assignments->count();
                        });

                    // Check if ALL rooms have the correct capacity
                    $allRoomsCorrect = $roomCounts->every(function ($count) use ($sessionCapacity) {
                        return $count == $sessionCapacity;
                    });

                    // Also check if we have enough rooms with students
                    $roomsWithStudents = $roomCounts->filter(function ($count) {
                        return $count > 0;
                    })->count();

                    if ($allRoomsCorrect && $roomsWithStudents >= 5) { // At least 5 rooms should have students
                        \Log::info('Perfect match found with session capacity', [
                            'capacity' => $sessionCapacity,
                            'total_assignments' => $matchingAssignments->count(),
                            'rooms_with_students' => $roomsWithStudents
                        ]);

                        $result = $matchingAssignments->groupBy('room_number')
                            ->map(function ($assignments) {
                                return $assignments->pluck('student_name')->toArray();
                            })
                            ->toArray();

                        return $this->filterActiveRoomAssignments($result);
                    }
                }

                RoomAssignment::truncate();

                $roomStudents = $this->generateNewRoomAssignments($sessionCapacity);
                $this->saveRoomAssignments($roomStudents, $sessionCapacity);

                \Log::info('Generated new assignments with session capacity', [
                    'capacity' => $sessionCapacity,
                    'rooms_generated' => count($roomStudents)
                ]);

                return $this->filterActiveRoomAssignments($roomStudents);
            }

            // RULE 2: Prefer database assignments for persistence when not reassigning
            $anyExistingAssignments = RoomAssignment::count();

            if ($anyExistingAssignments > 0) {
                // Get the capacity from existing assignments (most recent/consistent)
                $existingCapacity = RoomAssignment::select('room_capacity')
                    ->groupBy('room_capacity')
                    ->orderByRaw('COUNT(*) DESC')
                    ->first();

                if ($existingCapacity) {
                    $currentCapacity = $existingCapacity->room_capacity;

                    // DO NOT override session - only set if session is empty
                    if (!session('room_capacity')) {
                        session(['room_capacity' => $currentCapacity]);
                    }

                    // Get all assignments with this capacity
                    $existingAssignments = RoomAssignment::where('room_capacity', $currentCapacity)
                        ->orderBy('room_number')
                        ->orderBy('assignment_order')
                        ->get();

                    \Log::info('Using existing room assignments from database (fallback)', [
                        'capacity' => $currentCapacity,
                        'assignment_count' => $existingAssignments->count()
                    ]);

                    $result = $existingAssignments->groupBy('room_number')
                        ->map(function ($assignments) {
                            return $assignments->pluck('student_name')->toArray();
                        })
                        ->toArray();

                    return $this->filterActiveRoomAssignments($result);
                }
            }

            // RULE 3: No session, no database - generate new with default
            $currentCapacity = $this->getRoomCapacitySetting();
            \Log::info('Generating new room assignments (no existing data)', ['capacity' => $currentCapacity]);

            $roomStudents = $this->generateNewRoomAssignments($currentCapacity);

            // Save the new assignments to database
            $this->saveRoomAssignments($roomStudents, $currentCapacity);

            return $this->filterActiveRoomAssignments($roomStudents);

        } catch (\Exception $e) {
            \Log::error('Error fetching dynamic room students: ' . $e->getMessage());

            // Return empty array as fallback
            return $this->filterActiveRoomAssignments([]);
        }
    }

    /**
     * Ensure only active (student) rooms remain in the assignment list.
     */
    private function filterActiveRoomAssignments(array $roomStudents): array
    {
        try {
            static $activeRoomLookup = null;

            if ($activeRoomLookup === null) {
                $activeRoomNumbers = Room::where('status', 'active')
                    ->pluck('room_number')
                    ->map(function ($number) {
                        return (string) $number;
                    })
                    ->toArray();

                $activeRoomLookup = array_fill_keys($activeRoomNumbers, true);
            }

            if (empty($activeRoomLookup)) {
                return [];
            }

            return array_filter(
                $roomStudents,
                function ($students, $roomNumber) use ($activeRoomLookup) {
                    return isset($activeRoomLookup[(string) $roomNumber]);
                },
                ARRAY_FILTER_USE_BOTH
            );
        } catch (\Throwable $e) {
            \Log::warning('filterActiveRoomAssignments failed: ' . $e->getMessage());
            return $roomStudents;
        }
    }

    /**
     * Update room tasks when room assignments change
     * This ensures room task checklist reflects current student assignments
     */
    private function updateRoomTasksAfterAssignmentChange()
    {
        try {
            \Log::info('Updating room tasks after assignment change...');

            // Clear existing base room tasks (not completed ones with week/month/year)
            RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->delete();

            \Log::info('Cleared existing base room tasks');

            // Get current room assignments directly from database to avoid circular calls
            $roomStudents = RoomAssignment::orderBy('room_number')
                ->orderBy('assignment_order')
                ->get()
                ->groupBy('room_number')
                ->map(function ($assignments) {
                    return $assignments->pluck('student_name')->toArray();
                })
                ->toArray();

            // Get all rooms that have students assigned
            $roomsWithStudents = array_filter($roomStudents, function($students) {
                return !empty($students);
            });

            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // Define the task areas and descriptions
            // Load tasks from DB templates
            [$baseTasks, $fixedTasks] = $this->getTaskTemplates();
            $taskAreas = [];
            foreach ($baseTasks as $tpl) {
                $taskAreas[$tpl['area']] = $tpl['desc'];
            }

            // Use optimized batch task creation instead of individual creates
            $this->generateOptimizedRoomTasksBatch($roomsWithStudents, $taskAreas, $fixedTasks, $daysOfWeek);

            \Log::info('Room tasks updated successfully after assignment change');
            return true;

        } catch (\Exception $e) {
            \Log::error('Error updating room tasks after assignment change: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate room tasks efficiently using batch operations for assignment changes
     */
    private function generateOptimizedRoomTasksBatch($roomsWithStudents, $taskAreas, $fixedTasks, $daysOfWeek)
    {
        $allTasks = [];
        $timestamp = now();

        foreach ($roomsWithStudents as $roomNumber => $students) {
            \Log::info("Creating tasks for room {$roomNumber} with students: " . implode(', ', $students));

            foreach ($daysOfWeek as $day) {
                // Create individual student tasks with rotating assignments
                $studentCount = count($students);
                $areaKeys = array_keys($taskAreas);
                $areaCount = count($areaKeys);

                // Guard: if there are no template task areas, avoid modulo by zero
                if ($areaCount === 0) {
                    \Log::warning("No task areas/templates found; creating only fixed tasks for room {$roomNumber} on {$day}");

                    // Add fixed tasks for everyone for this day
                    foreach ($fixedTasks as $task) {
                        $allTasks[] = [
                            'name' => 'Everyone',
                            'room_number' => $roomNumber,
                            'area' => $task['area'],
                            'desc' => $task['desc'],
                            'day' => $day,
                            'status' => 'not yet',
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp
                        ];
                    }

                    // Skip per-student area assignment for this day
                    continue;
                }

                // Get day index for rotation
                $dayIndex = array_search($day, $daysOfWeek);

                // Rotate students for this day
                $rotatedStudents = $students;
                if ($studentCount > 1) {
                    $rotatedStudents = array_merge(
                        array_slice($students, $dayIndex % $studentCount),
                        array_slice($students, 0, $dayIndex % $studentCount)
                    );
                }

                // Assign areas to students
                for ($i = 0; $i < $studentCount; $i++) {
                    $areaIndex = $i % $areaCount;
                    $areaKey = $areaKeys[$areaIndex];
                    $areaDesc = $taskAreas[$areaKey];

                    $allTasks[] = [
                        'name' => $rotatedStudents[$i],
                        'room_number' => $roomNumber,
                        'area' => $areaKey,
                        'desc' => $areaDesc,
                        'day' => $day,
                        'status' => 'not yet',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }

                // Add fixed tasks for everyone
                foreach ($fixedTasks as $task) {
                    $allTasks[] = [
                        'name' => 'Everyone',
                        'room_number' => $roomNumber,
                        'area' => $task['area'],
                        'desc' => $task['desc'],
                        'day' => $day,
                        'status' => 'not yet',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }
        }

        // Batch insert all tasks at once
        if (!empty($allTasks)) {
            // Insert in chunks to avoid memory issues with large datasets
            $chunks = array_chunk($allTasks, 500);
            foreach ($chunks as $chunk) {
                RoomTask::insert($chunk);
            }
            \Log::info('Batch inserted room tasks', ['total_tasks' => count($allTasks)]);
        }
    }

    /**
     * Ensure room tasks are current with room assignments
     * Checks if room tasks need to be updated based on current assignments
     */
    private function ensureRoomTasksAreCurrent()
    {
        try {
            // Get current room assignments directly from database
            $currentRoomStudents = RoomAssignment::orderBy('room_number')
                ->orderBy('assignment_order')
                ->get()
                ->groupBy('room_number')
                ->map(function ($assignments) {
                    return $assignments->pluck('student_name')->toArray();
                })
                ->toArray();

            // Check if we have any room tasks
            $existingTasksCount = RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->count();

            if ($existingTasksCount === 0) {
                // No base tasks exist, create them
                \Log::info('No base room tasks found, creating them');
                $this->updateRoomTasksAfterAssignmentChange();
                return;
            }

            // Check if the current room tasks match the current assignments
            $needsUpdate = false;

            foreach ($currentRoomStudents as $roomNumber => $students) {
                if (empty($students)) continue;

                // Check if tasks exist for this room with current students
                $existingStudentTasks = RoomTask::where('room_number', $roomNumber)
                    ->where('day', 'Monday') // Check Monday as sample
                    ->where('name', '!=', 'Everyone')
                    ->whereNull('week')
                    ->whereNull('month')
                    ->whereNull('year')
                    ->pluck('name')
                    ->toArray();

                // Compare current students with existing task students
                $currentStudents = array_values($students);
                sort($currentStudents);
                sort($existingStudentTasks);

                if ($currentStudents !== $existingStudentTasks) {
                    \Log::info("Room {$roomNumber} tasks need update", [
                        'current_students' => $currentStudents,
                        'existing_tasks' => $existingStudentTasks
                    ]);
                    $needsUpdate = true;
                    break;
                }
            }

            if ($needsUpdate) {
                \Log::info('Room tasks are outdated, updating them');
                $this->updateRoomTasksAfterAssignmentChange();
            } else {
                \Log::info('Room tasks are current with assignments');
            }

        } catch (\Exception $e) {
            \Log::error('Error ensuring room tasks are current: ' . $e->getMessage());
        }
    }

    /**
     * Simple method to ensure room tasks exist for current assignments
     */
    private function ensureRoomTasksAreCurrentSimple()
    {
        try {
            // Ensure we have task templates available
            [$baseTasks, $fixedTasks] = $this->getTaskTemplates();

            if (empty($baseTasks)) {
                \Log::warning('No task templates found in DB. Seed task_templates before generating room tasks.');
                return;
            }

            // Check if we have any base room tasks
            $existingTasksCount = RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->count();

            if ($existingTasksCount === 0) {
                \Log::info('No base room tasks found, generating from DB templates');
                $this->syncRoomTasksWithAssignments();
                return;
            }

            // Verify that base tasks reflect current template areas
            $templateAreas = array_map(function($t){ return $t['area']; }, $baseTasks);
            sort($templateAreas);

            $existingAreas = RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->select('area')
                ->distinct()
                ->pluck('area')
                ->toArray();
            sort($existingAreas);

            if ($templateAreas !== $existingAreas) {
                \Log::info('Base room tasks areas differ from templates; regenerating', [
                    'templateAreas' => $templateAreas,
                    'existingAreas' => $existingAreas,
                ]);
                $this->syncRoomTasksWithAssignments();
            }

        } catch (\Exception $e) {
            \Log::error('Error in simple room task ensure: ' . $e->getMessage());
        }
    }

    /**
     * Generate new room assignments with gender separation
     */
    private function generateNewRoomAssignments($studentsPerRoom)
    {
        try {
            \Log::info('Starting generateNewRoomAssignments', ['studentsPerRoom' => $studentsPerRoom]);

            // Fetch all students from the database with gender information
            $students = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->get(['user_fname', 'user_lname', 'gender'])
                ->map(function ($student) {
                    return [
                        'name' => trim($student->user_fname . ' ' . $student->user_lname),
                        'gender' => $student->gender
                    ];
                })
                ->toArray();

            \Log::info('Fetched students', ['count' => count($students)]);

        // If no students found, provide some sample data for testing
        if (empty($students)) {
            \Log::warning('No students found in database, using sample data');
            $students = [
                ['name' => 'Sample Male 1', 'gender' => 'M'],
                ['name' => 'Sample Male 2', 'gender' => 'M'],
                ['name' => 'Sample Male 3', 'gender' => 'M'],
                ['name' => 'Sample Female 1', 'gender' => 'F'],
                ['name' => 'Sample Female 2', 'gender' => 'F'],
                ['name' => 'Sample Female 3', 'gender' => 'F'],
            ];
        }

        // Separate students by gender
        $maleStudents = array_filter($students, function($student) {
            return $student['gender'] === 'M';
        });
        $femaleStudents = array_filter($students, function($student) {
            return $student['gender'] === 'F';
        });

        // Sort each gender group deterministically for consistent assignments
        sort($maleStudents);
        sort($femaleStudents);

        // Get available room numbers from DB (prefer active rooms, allow any floor >=2)
        $rooms = \App\Models\Room::where('status', 'active')
            ->orderBy('room_number')
            ->pluck('room_number')
            ->toArray();

        // If no rooms exist in DB, fail fast so the caller can handle creating rooms first
        if (empty($rooms)) {
            throw new \Exception('No active rooms available for assignment. Please create rooms before generating assignments.');
        }

        // Distribute students across rooms with gender separation
        $roomStudents = [];
        $roomIndex = 0;

        // Assign male students first
        $maleIndex = 0;
        while ($maleIndex < count($maleStudents) && $roomIndex < count($rooms)) {
            $room = $rooms[$roomIndex];
            $roomStudents[$room] = [];

            // Fill room with male students (up to capacity)
            for ($i = 0; $i < $studentsPerRoom && $maleIndex < count($maleStudents); $i++) {
                $roomStudents[$room][] = [
                    'name' => $maleStudents[$maleIndex]['name'],
                    'gender' => $maleStudents[$maleIndex]['gender']
                ];
                $maleIndex++;
            }
            $roomIndex++;
        }

        // Assign female students to remaining rooms
        $femaleIndex = 0;
        while ($femaleIndex < count($femaleStudents) && $roomIndex < count($rooms)) {
            $room = $rooms[$roomIndex];
            $roomStudents[$room] = [];

            // Fill room with female students (up to capacity)
            for ($i = 0; $i < $studentsPerRoom && $femaleIndex < count($femaleStudents); $i++) {
                $roomStudents[$room][] = [
                    'name' => $femaleStudents[$femaleIndex]['name'],
                    'gender' => $femaleStudents[$femaleIndex]['gender']
                ];
                $femaleIndex++;
            }
            $roomIndex++;
        }

        // Initialize empty arrays for rooms without students
        foreach ($rooms as $room) {
            if (!isset($roomStudents[$room])) {
                $roomStudents[$room] = [];
            }
        }

        \Log::info('New room assignments generated', [
            'total_students' => count($students),
            'male_students' => count($maleStudents),
            'female_students' => count($femaleStudents),
            'rooms_with_students' => count(array_filter($roomStudents, function($room) { return !empty($room); })),
            'students_per_room' => $studentsPerRoom
        ]);

        // Convert to simple name arrays for return
        $result = [];
        foreach ($roomStudents as $room => $students) {
            $result[$room] = array_map(function($student) {
                return $student['name'];
            }, $students);
        }

            return $result;

        } catch (\Exception $e) {
            \Log::error('Error in generateNewRoomAssignments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Save room assignments to database
     */
    private function saveRoomAssignments($roomStudents, $capacity)
    {
        try {
            // Clear existing assignments for this capacity
            RoomAssignment::where('room_capacity', $capacity)->delete();

            // Prepare assignments for bulk insert
            $assignments = [];
            $timestamp = now();

            foreach ($roomStudents as $roomNumber => $students) {
                foreach ($students as $order => $studentName) {
                    // Get student details from database
                    $student = PNUser::where('user_role', 'student')
                        ->where('status', 'active')
                        ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
                        ->first();

                    // Get batch year from student details if available
                    $batchYear = null;
                    if ($student) {
                        $studentDetail = \DB::table('student_details')
                            ->where('user_id', $student->user_id)
                            ->first();
                        $batchYear = $studentDetail ? $studentDetail->batch : null;
                    }

                    $assignments[] = [
                        'room_number' => $roomNumber,
                        'student_id' => $student ? $student->user_id : null,
                        'student_name' => $studentName,
                        'student_gender' => $student ? $student->gender : 'M', // Default fallback
                        'batch_year' => $batchYear,
                        'assignment_order' => $order,
                        'room_capacity' => $capacity,
                        'assigned_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
            }

            if (!empty($assignments)) {
                RoomAssignment::insert($assignments);
                \Log::info('Room assignments saved to database', [
                    'capacity' => $capacity,
                    'assignments_count' => count($assignments)
                ]);

                // Update room tasks to reflect new assignments
                $this->updateRoomTasksAfterAssignmentChange();
            }

        } catch (\Exception $e) {
            \Log::error('Error saving room assignments: ' . $e->getMessage());
        }
    }

    /**
     * Get the room capacity setting (default 6 students per room)
     * This can be customized through admin interface
     * Checks database first, then session, then default
     */
    private function getRoomCapacitySetting()
    {
        // ABSOLUTE PRIORITY: Session capacity always wins
        $sessionCapacity = session('room_capacity');
        if ($sessionCapacity && is_numeric($sessionCapacity) && $sessionCapacity > 0) {
            \Log::info('getRoomCapacitySetting - using session capacity (absolute priority)', [
                'session_capacity' => $sessionCapacity
            ]);
            return (int) $sessionCapacity;
        }

        // FALLBACK: Check database only if no session exists
        $existingCapacity = RoomAssignment::select('room_capacity')
            ->distinct()
            ->first();

        if ($existingCapacity && $existingCapacity->room_capacity > 0) {
            \Log::info('getRoomCapacitySetting - using database capacity (fallback)', [
                'database_capacity' => $existingCapacity->room_capacity
            ]);

            // DO NOT override session if it exists - only set if session is empty
            if (!session('room_capacity')) {
                session(['room_capacity' => $existingCapacity->room_capacity]);
            }
            return (int) $existingCapacity->room_capacity;
        }

        // LAST RESORT: Default capacity
        \Log::info('getRoomCapacitySetting - using default capacity (last resort)', ['default' => 6]);

        // Only set session if it's completely empty
        if (!session('room_capacity')) {
            session(['room_capacity' => 6]);
        }
        return 6;
    }

    /**
     * Set room capacity for student assignments
     */
    public function setRoomCapacity(Request $request)
    {
        try {
            $validated = $request->validate([
                'capacity' => 'required|integer|min:1|max:20'
            ]);

            $capacity = $validated['capacity'];

            // Store in session for immediate use
            session(['room_capacity' => $capacity]);

            // Optionally store in database for persistence
            // You can create a settings table for this

            \Log::info('Room capacity updated', [
                'new_capacity' => $capacity,
                'user' => auth()->user()->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Room capacity set to {$capacity} students per room",
                'capacity' => $capacity
            ]);

        } catch (\Exception $e) {
            \Log::error('Error setting room capacity: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update room capacity'
            ], 500);
        }
    }

    /**
     * Get current room capacity setting
     */
    public function getRoomCapacity()
    {
        $capacity = $this->getRoomCapacitySetting();

        return response()->json([
            'success' => true,
            'capacity' => $capacity
        ]);
    }

    /**
     * Set capacity for an individual room and reassign students accordingly
     */
    public function setIndividualRoomCapacity(Request $request)
    {
        try {
            // Add basic logging to debug
            \Log::info('setIndividualRoomCapacity called', [
                'request_data' => $request->all(),
                'user' => auth()->user()->user_id ?? 'unknown'
            ]);

            $validated = $request->validate([
                'room_number' => 'required|string',
                'capacity' => 'required|integer|min:1|max:20',
                'male_capacity' => 'nullable|integer|min:0|max:20',
                'female_capacity' => 'nullable|integer|min:0|max:20',
                'male_capacity_2025' => 'nullable|integer|min:0|max:20',
                'female_capacity_2025' => 'nullable|integer|min:0|max:20',
                'male_capacity_2026' => 'nullable|integer|min:0|max:20',
                'female_capacity_2026' => 'nullable|integer|min:0|max:20',
                'assigned_batch' => 'nullable|string|in:2025,2026,',
                'auto_update_other_rooms' => 'nullable|boolean'
            ]);

            $roomNumber = $validated['room_number'];
            $newCapacity = $validated['capacity'];

            \Log::info('Setting individual room capacity', [
                'room_number' => $roomNumber,
                'new_capacity' => $newCapacity,
                'user' => auth()->user()->user_id ?? 'unknown'
            ]);

            // Get current room assignments
            $currentRoomStudents = $this->getDynamicRoomStudents();
            $currentStudents = $currentRoomStudents[$roomNumber] ?? [];
            $currentOccupancy = count($currentStudents);

            \Log::info('Current room status', [
                'room_number' => $roomNumber,
                'current_occupancy' => $currentOccupancy,
                'current_students' => $currentStudents
            ]);

            // Track changes for comprehensive response
            $changesSummary = [];
            $affectedRooms = [];
            $studentsReassigned = 0;

            // Update room in database with all capacity settings
            $this->updateRoomCapacityInDatabase($roomNumber, $validated);
            $changesSummary[] = "Capacity set to {$newCapacity}";

            // Handle gender-based capacity settings
            if (isset($validated['male_capacity']) || isset($validated['female_capacity'])) {
                $maleCapacity = $validated['male_capacity'] ?? 'auto';
                $femaleCapacity = $validated['female_capacity'] ?? 'auto';
                $changesSummary[] = "Gender capacity: Male({$maleCapacity}), Female({$femaleCapacity})";
            }

            // Handle batch-specific settings
            if (isset($validated['assigned_batch']) && $validated['assigned_batch'] !== '') {
                $changesSummary[] = "Assigned to batch {$validated['assigned_batch']}";
            }

            // Handle capacity changes and dynamic student reassignment
            $updatedRoomStudents = $currentRoomStudents;
            $message = "Room {$roomNumber} capacity updated to {$newCapacity}.";

            // Check if auto-assignment is enabled
            $autoAssignEnabled = $request->has('enable_auto_assignment') && $request->boolean('enable_auto_assignment');
            $crossRoomSyncEnabled = $request->has('enable_cross_room_sync') && $request->boolean('enable_cross_room_sync');

            \Log::info('Individual room capacity - Auto-assignment settings', [
                'auto_assign_enabled' => $autoAssignEnabled,
                'cross_room_sync_enabled' => $crossRoomSyncEnabled,
                'request_params' => $request->only(['enable_auto_assignment', 'enable_cross_room_sync'])
            ]);

            if ($autoAssignEnabled) {
                // AUTO-ASSIGNMENT MODE: Fill room to exact capacity
                \Log::info("Auto-assignment enabled for room {$roomNumber}", [
                    'target_capacity' => $newCapacity,
                    'current_occupancy' => $currentOccupancy
                ]);

                if ($newCapacity > $currentOccupancy) {
                    // Need to add students
                    $studentsNeeded = $newCapacity - $currentOccupancy;
                    $newStudents = $this->autoAssignStudentsToRoom($roomNumber, $studentsNeeded, $validated);

                    if (!empty($newStudents)) {
                        $updatedRoomStudents[$roomNumber] = array_merge($currentStudents, $newStudents);
                        $message .= " {$studentsNeeded} student(s) auto-assigned: " . implode(', ', $newStudents);
                        $studentsReassigned += count($newStudents);
                    } else {
                        // Fallback: if cross-room sync is enabled, borrow compatible students from other rooms
                        if ($crossRoomSyncEnabled) {
                            $borrowed = $this->borrowStudentsFromOtherRooms($updatedRoomStudents, $roomNumber, $studentsNeeded);
                            if ($borrowed > 0) {
                                $studentsReassigned += $borrowed;
                                $message .= " {$borrowed} student(s) moved from other rooms.";
                            } else {
                                $message .= " No available students for auto-assignment.";
                            }
                        } else {
                            $message .= " No available students for auto-assignment.";
                            $updatedRoomStudents[$roomNumber] = $currentStudents;
                        }
                    }
                } elseif ($newCapacity < $currentOccupancy) {
                    // Need to remove excess students
                    $studentsToRemove = $currentOccupancy - $newCapacity;
                    $studentsToKeep = array_slice($currentStudents, 0, $newCapacity);
                    $studentsToRelocate = array_slice($currentStudents, $newCapacity);

                    $updatedRoomStudents[$roomNumber] = $studentsToKeep;

                    // Try to relocate excess students to other rooms
                    foreach ($studentsToRelocate as $studentName) {
                        $relocated = $this->relocateStudentToAvailableRoom($studentName, $updatedRoomStudents, 6);
                        if ($relocated) {
                            $studentsReassigned++;
                        }
                    }

                    $message .= " {$studentsToRemove} student(s) relocated to other rooms.";
                } else {
                    // Capacity matches - keep current students
                    $updatedRoomStudents[$roomNumber] = $currentStudents;
                    $message .= " Room filled to capacity.";
                }

                $affectedRooms[$roomNumber] = [
                    'students' => $updatedRoomStudents[$roomNumber],
                    'capacity' => $newCapacity,
                    'occupancy' => count($updatedRoomStudents[$roomNumber])
                ];
            } else {
                // MANUAL MODE: Just update capacity, keep existing students
                if ($newCapacity >= $currentOccupancy) {
                    $message .= " Current students remain assigned.";
                    $affectedRooms[$roomNumber] = [
                        'students' => $currentStudents,
                        'capacity' => $newCapacity,
                        'occupancy' => $currentOccupancy
                    ];
                } else {
                // Need to relocate some students
                $studentsToRelocate = $currentOccupancy - $newCapacity;

                \Log::info('Need to relocate students', [
                    'students_to_relocate' => $studentsToRelocate,
                    'current_occupancy' => $currentOccupancy,
                    'new_capacity' => $newCapacity
                ]);

                // Get students to keep (first N students) and students to relocate
                $studentsToKeep = array_slice($currentStudents, 0, $newCapacity);
                $studentsToMove = array_slice($currentStudents, $newCapacity);

                // Update the target room with students to keep
                $updatedRoomStudents[$roomNumber] = $studentsToKeep;

                // Find available rooms for relocated students
                $relocatedCount = 0;
                \Log::info('Starting student relocation', [
                    'students_to_move' => $studentsToMove,
                    'students_to_keep' => $studentsToKeep
                ]);

                foreach ($studentsToMove as $studentName) {
                    $placed = false;

                    // Try to place in existing rooms with available space
                    foreach ($updatedRoomStudents as $roomNum => $roomStudents) {
                        if ($roomNum === $roomNumber) continue; // Skip the target room

                        $roomCapacity = 6; // Default capacity for other rooms

                        if (count($roomStudents) < $roomCapacity) {
                            // Check if room is empty or has compatible gender
                            if (empty($roomStudents)) {
                                // Empty room - can place student
                                $updatedRoomStudents[$roomNum][] = $studentName;
                                $placed = true;
                                $relocatedCount++;
                                $studentsReassigned++;

                                // Track affected room
                                $affectedRooms[$roomNum] = [
                                    'students' => $updatedRoomStudents[$roomNum],
                                    'capacity' => 6, // Default capacity
                                    'occupancy' => count($updatedRoomStudents[$roomNum])
                                ];

                                break;
                            } else {
                                // Check gender compatibility
                                $roomGender = $this->getRoomGender($roomStudents);
                                $studentGender = $this->getStudentGender($studentName);

                                if ($roomGender === $studentGender) {
                                    $updatedRoomStudents[$roomNum][] = $studentName;
                                    $placed = true;
                                    $relocatedCount++;
                                    $studentsReassigned++;

                                    // Track affected room
                                    $affectedRooms[$roomNum] = [
                                        'students' => $updatedRoomStudents[$roomNum],
                                        'capacity' => 6, // Default capacity
                                        'occupancy' => count($updatedRoomStudents[$roomNum])
                                    ];

                                    \Log::info("Student relocated to existing room", [
                                        'student' => $studentName,
                                        'from_room' => $roomNumber,
                                        'to_room' => $roomNum,
                                        'gender' => $studentGender
                                    ]);
                                    break;
                                }
                            }
                        }
                    }

                    // If not placed, create a new room assignment
                    if (!$placed) {
                        $newRoomNumber = $this->findAvailableRoomNumber($updatedRoomStudents);
                        $updatedRoomStudents[$newRoomNumber] = [$studentName];
                        $relocatedCount++;

                        \Log::info("Created new room assignment", [
                            'new_room' => $newRoomNumber,
                            'student' => $studentName
                        ]);
                    }
                }

                $message .= " {$relocatedCount} student(s) relocated to other rooms.";
                }
            }

            // Handle cross-room capacity updates if enabled
            if ($crossRoomSyncEnabled) {
                $this->updateOtherRoomCapacities($roomNumber, $newCapacity, $updatedRoomStudents, $affectedRooms);
                $changesSummary[] = "Other rooms updated automatically";
            }

            // Save the updated assignments to database and rebuild base tasks to include any new assignees
            $this->saveRoomAssignments($updatedRoomStudents, $this->getRoomCapacitySetting());
            // Ensure room tasks align with new occupants immediately
            $this->updateRoomTasksAfterAssignmentChange();

            // Update the room capacity and batch-specific settings in the Room model - this makes it persistent
            try {
                $roomData = [
                    'capacity' => $newCapacity,
                    'name' => "Room {$roomNumber}",
                    'status' => 'active'
                ];

                // Add general gender capacity fields if provided
                if (array_key_exists('male_capacity', $validated) && $validated['male_capacity'] !== null) {
                    $roomData['male_capacity'] = $validated['male_capacity'];
                }
                if (array_key_exists('female_capacity', $validated) && $validated['female_capacity'] !== null) {
                    $roomData['female_capacity'] = $validated['female_capacity'];
                }

                // Add batch-specific capacity fields if provided
                if (array_key_exists('male_capacity_2025', $validated) && $validated['male_capacity_2025'] !== null) {
                    $roomData['male_capacity_2025'] = $validated['male_capacity_2025'];
                }
                if (array_key_exists('female_capacity_2025', $validated) && $validated['female_capacity_2025'] !== null) {
                    $roomData['female_capacity_2025'] = $validated['female_capacity_2025'];
                }
                if (array_key_exists('male_capacity_2026', $validated) && $validated['male_capacity_2026'] !== null) {
                    $roomData['male_capacity_2026'] = $validated['male_capacity_2026'];
                }
                if (array_key_exists('female_capacity_2026', $validated) && $validated['female_capacity_2026'] !== null) {
                    $roomData['female_capacity_2026'] = $validated['female_capacity_2026'];
                }
                if (array_key_exists('assigned_batch', $validated) && $validated['assigned_batch'] !== null) {
                    $roomData['assigned_batch'] = $validated['assigned_batch'];
                }

                $room = \App\Models\Room::updateOrCreate(
                    ['room_number' => $roomNumber],
                    $roomData
                );

                \Log::info("Room settings persisted to database", [
                    'room_number' => $roomNumber,
                    'room_data' => $roomData,
                    'saved_room' => $room->toArray(),
                    'connection' => $room->getConnectionName()
                ]);

                // Verify the data was actually saved by retrieving it again
                $verifyRoom = \App\Models\Room::where('room_number', $roomNumber)->first();
                \Log::info("Verification - Room retrieved after save", [
                    'room_number' => $roomNumber,
                    'retrieved_room' => $verifyRoom ? $verifyRoom->toArray() : 'NOT_FOUND'
                ]);
            } catch (\Exception $e) {
                \Log::warning("Failed to update room settings in database: " . $e->getMessage());
            }

            \Log::info('Individual room capacity update completed', [
                'room_number' => $roomNumber,
                'new_capacity' => $newCapacity,
                'message' => $message
            ]);

            // Build data structures expected by the dashboard JS for immediate refresh
            // Persist assignments again to guarantee read-your-write before building response
            $this->saveRoomAssignments($updatedRoomStudents, $this->getRoomCapacitySetting());
            $updatedRoomData = $this->getRoomAssignmentsForDashboard(); // room_number => [names]
            $affectedRoomsList = [];
            foreach ($affectedRooms as $roomNum => $info) {
                $affectedRoomsList[] = [
                    'room_number' => $roomNum,
                    'new_capacity' => $info['capacity'] ?? $newCapacity,
                    'current_occupancy' => isset($info['students']) ? count($info['students']) : ($info['occupancy'] ?? 0)
                ];
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'room_assignments' => $updatedRoomStudents,
                'room_number' => $roomNumber,
                'new_capacity' => $newCapacity,
                'changes_summary' => implode("\n", $changesSummary),
                'affected_rooms' => $affectedRooms,
                'affected_rooms_list' => $affectedRoomsList, // shape used by updateAffectedRoomsWithAnimation()
                'updated_room_data' => $updatedRoomData, // shape used by updateDashboardWithRoomData()
                'students_reassigned' => $studentsReassigned,
                'auto_assign_enabled' => $autoAssignEnabled,
                'cross_room_sync_enabled' => $crossRoomSyncEnabled,
                'global_capacity_updated' => false, // Set to true if global capacity was changed
                'room_settings' => [
                    'capacity' => $newCapacity,
                    'male_capacity' => $validated['male_capacity'] ?? null,
                    'female_capacity' => $validated['female_capacity'] ?? null,
                    'male_capacity_2025' => $validated['male_capacity_2025'] ?? null,
                    'female_capacity_2025' => $validated['female_capacity_2025'] ?? null,
                    'male_capacity_2026' => $validated['male_capacity_2026'] ?? null,
                    'female_capacity_2026' => $validated['female_capacity_2026'] ?? null,
                    'assigned_batch' => $validated['assigned_batch'] ?? null
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error setting individual room capacity: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update room capacity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Borrow students from other rooms to fill the target room when no unassigned students are available.
     * Respects gender consistency in both donor and target rooms.
     *
     * @param array $roomMap map of room_number => [student names]
     * @param string $targetRoom
     * @param int $needed
     * @return int number of students moved
     */
    private function borrowStudentsFromOtherRooms(array &$roomMap, string $targetRoom, int $needed): int
    {
        if ($needed <= 0) return 0;

        // Determine target gender (if room already has students)
        $targetGender = null;
        $targetStudents = $roomMap[$targetRoom] ?? [];
        if (!empty($targetStudents)) {
            $targetGender = $this->getRoomGender($targetStudents);
        }

        // Build room capacities map
        $roomCapacities = \App\Models\Room::pluck('capacity', 'room_number')->toArray();

        // Sort donor rooms by descending occupancy
        $donorRooms = collect($roomMap)
            ->keys()
            ->filter(fn($room) => $room !== $targetRoom)
            ->sortByDesc(function ($room) use ($roomMap) {
                return count($roomMap[$room] ?? []);
            })
            ->values()
            ->all();

        $moved = 0;

        foreach ($donorRooms as $donorRoom) {
            if ($moved >= $needed) break;

            $donorStudents = $roomMap[$donorRoom] ?? [];
            if (empty($donorStudents)) continue;

            // Determine donor gender (room stays single-gender)
            $donorGender = $this->getRoomGender($donorStudents);

            // Gender must be compatible with target room
            if ($targetGender !== null && $donorGender !== $targetGender) continue;

            // Do not underflow donor below 1 student
            $donorCapacity = $roomCapacities[$donorRoom] ?? $this->getRoomCapacitySetting();
            if (count($donorStudents) <= 1) continue;

            // Pick the last student for movement (simple heuristic)
            $studentName = array_pop($donorStudents);

            // Update maps
            $roomMap[$donorRoom] = $donorStudents;
            $roomMap[$targetRoom] = array_merge($roomMap[$targetRoom] ?? [], [$studentName]);

            $moved++;
        }

        return $moved;
    }

    /**
     * Get the gender of a student by name
     */
    private function getStudentGender($studentName)
    {
        $student = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
            ->first();

        return $student ? $student->gender : null;
    }

    /**
     * Get room details including persistent capacity and batch-specific settings
     */
    public function getRoomDetails($roomNumber)
    {
        try {
            // Clear any caching to ensure fresh data
            \Cache::forget('room_assignments_' . $roomNumber);
            \Cache::forget('dynamic_room_students');

            // Get room from database
            $room = \App\Models\Room::where('room_number', $roomNumber)->first();

            // Get current room assignments directly from database for fresh data
            $assignments = \App\Models\RoomAssignment::where('room_number', $roomNumber)
                ->orderBy('assignment_order')
                ->get();

            $currentOccupancy = $assignments->count();

            // Get student details with batch information
            $studentsWithDetails = [];
            foreach ($assignments as $assignment) {
                $student = PNUser::where('user_id', $assignment->student_id)->first();

                if ($student) {
                    $studentDetail = \DB::table('student_details')
                        ->where('user_id', $student->user_id)
                        ->first();

                    $studentsWithDetails[] = [
                        'name' => $assignment->student_name,
                        'gender' => $assignment->student_gender,
                        'batch' => $studentDetail ? $studentDetail->batch : null,
                        'user_id' => $assignment->student_id
                    ];
                } else {
                    // Fallback if student not found in PNUser table
                    $studentsWithDetails[] = [
                        'name' => $assignment->student_name,
                        'gender' => $assignment->student_gender,
                        'batch' => null,
                        'user_id' => $assignment->student_id
                    ];
                }
            }

            $roomDetails = [
                'room_number' => $roomNumber,
                'capacity' => $room ? $room->capacity : 6,
                'male_capacity' => $room ? $room->male_capacity : null,
                'female_capacity' => $room ? $room->female_capacity : null,
                'male_capacity_2025' => $room ? $room->male_capacity_2025 : null,
                'female_capacity_2025' => $room ? $room->female_capacity_2025 : null,
                'male_capacity_2026' => $room ? $room->male_capacity_2026 : null,
                'female_capacity_2026' => $room ? $room->female_capacity_2026 : null,
                'assigned_batch' => $room ? $room->assigned_batch : null,
                'status' => $room ? $room->status : 'active',
                'occupant_type' => $room ? ($room->occupant_type ?? 'both') : 'both',
                'current_occupancy' => $currentOccupancy,
                'available_slots' => ($room ? $room->capacity : 6) - $currentOccupancy,
                'students' => $studentsWithDetails
            ];

            return response()->json([
                'success' => true,
                'room' => $roomDetails
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting room details: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get room details: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Validate that students exist in the Login database
     */
    private function validateStudentsFromLoginDatabase($students)
    {
        if (empty($students)) {
            return ['valid' => true, 'invalid_students' => []];
        }

        $invalidStudents = [];

        foreach ($students as $studentName) {
            // Check if student exists in PNUser table (Login database)
            $exists = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
                ->exists();

            if (!$exists) {
                $invalidStudents[] = $studentName;
            }
        }

        return [
            'valid' => empty($invalidStudents),
            'invalid_students' => $invalidStudents
        ];
    }

    /**
     * Update room capacity and settings in the Room model database
     */
    private function updateRoomCapacityInDatabase($roomNumber, $settings)
    {
        try {
            // Prepare room data with all capacity settings
            $roomData = [
                'capacity' => $settings['capacity'],
                'name' => "Room {$roomNumber}",
                'status' => 'active'
            ];

            // Add gender capacity settings if provided
            if (isset($settings['male_capacity'])) {
                $roomData['male_capacity'] = $settings['male_capacity'];
            }
            if (isset($settings['female_capacity'])) {
                $roomData['female_capacity'] = $settings['female_capacity'];
            }

            // Add batch-specific capacity settings if provided
            if (isset($settings['male_capacity_2025'])) {
                $roomData['male_capacity_2025'] = $settings['male_capacity_2025'];
            }
            if (isset($settings['female_capacity_2025'])) {
                $roomData['female_capacity_2025'] = $settings['female_capacity_2025'];
            }
            if (isset($settings['male_capacity_2026'])) {
                $roomData['male_capacity_2026'] = $settings['male_capacity_2026'];
            }
            if (isset($settings['female_capacity_2026'])) {
                $roomData['female_capacity_2026'] = $settings['female_capacity_2026'];
            }

            // Add assigned batch if provided
            if (isset($settings['assigned_batch']) && $settings['assigned_batch'] !== '') {
                $roomData['assigned_batch'] = $settings['assigned_batch'];
            }

            // Update or create room record with comprehensive settings
            $room = \App\Models\Room::updateOrCreate(
                ['room_number' => $roomNumber],
                $roomData
            );

            \Log::info("Updated room settings in database", [
                'room_number' => $roomNumber,
                'room_data' => $roomData,
                'room_id' => $room->id
            ]);

            return $room;
        } catch (\Exception $e) {
            \Log::warning("Failed to update room settings in database: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reassign students when individual room capacity is reduced
     */
    private function reassignStudentsWithIndividualCapacity($currentRoomStudents, $targetRoom, $studentsToKeep, $studentsToMove, $newCapacity)
    {
        $updatedRoomStudents = $currentRoomStudents;

        // Update the target room with students to keep
        $updatedRoomStudents[$targetRoom] = $studentsToKeep;

        // Get gender of students to move for proper placement
        $studentsToMoveWithGender = [];
        foreach ($studentsToMove as $studentName) {
            $student = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$studentName])
                ->first();

            if ($student) {
                $studentsToMoveWithGender[] = [
                    'name' => $studentName,
                    'gender' => $student->gender
                ];
            }
        }

        // Find available rooms for relocated students
        foreach ($studentsToMoveWithGender as $studentData) {
            $placed = false;

            // Try to place in existing rooms with same gender and available space
            foreach ($updatedRoomStudents as $roomNum => $roomStudents) {
                if ($roomNum === $targetRoom) continue; // Skip the target room

                $roomCapacity = ($roomNum === $targetRoom) ? $newCapacity : $this->getRoomCapacitySetting();

                if (count($roomStudents) < $roomCapacity) {
                    // Check if room is empty or has same gender
                    if (empty($roomStudents)) {
                        // Empty room - can place student
                        $updatedRoomStudents[$roomNum][] = $studentData['name'];
                        $placed = true;
                        break;
                    } else {
                        // Check gender compatibility
                        $roomGender = $this->getRoomGender($roomStudents);
                        if ($roomGender === $studentData['gender']) {
                            $updatedRoomStudents[$roomNum][] = $studentData['name'];
                            $placed = true;
                            break;
                        }
                    }
                }
            }

            // If not placed, create a new room assignment (this should rarely happen)
            if (!$placed) {
                \Log::warning("Could not place student in existing rooms", [
                    'student' => $studentData['name'],
                    'gender' => $studentData['gender']
                ]);

                // Find a new room number
                $newRoomNumber = $this->findAvailableRoomNumber($updatedRoomStudents);
                $updatedRoomStudents[$newRoomNumber] = [$studentData['name']];
            }
        }

        return $updatedRoomStudents;
    }

    /**
     * Get the gender of students in a room
     */
    private function getRoomGender($roomStudents)
    {
        if (empty($roomStudents)) {
            return null;
        }

        $firstStudent = $roomStudents[0];
        $student = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->whereRaw("CONCAT(user_fname, ' ', user_lname) = ?", [$firstStudent])
            ->first();

        return $student ? $student->gender : null;
    }

    /**
     * Find an available room number for new assignments
     */
    private function findAvailableRoomNumber($existingRooms)
    {
        // Query existing room numbers from DB and pick a room number on any floor >= 2 that is not present
        $dbRooms = \App\Models\Room::orderBy('room_number')->pluck('room_number')->toArray();
        // Build a set for quick lookup
        $taken = array_flip(array_merge(array_keys($existingRooms), $dbRooms));

        // Start from floor 2 upwards; allow growth beyond floor 8
        for ($floor = 2; $floor <= 50; $floor++) {
            for ($room = 1; $room <= 50; $room++) {
                $roomNumber = $floor . str_pad($room, 2, '0', STR_PAD_LEFT);
                if (!isset($taken[$roomNumber])) {
                    return $roomNumber;
                }
            }
        }

        // Last fallback
        return '201';
    }

    /**
     * Get list of valid students from Login database
     */
    public function getValidStudentsList(Request $request)
    {
        try {
            $roomNumber = $request->get('room_number');
            $occupantType = $request->get('occupant_type'); // 'male'|'female'|'both'
            $unassignedOnly = $request->boolean('unassigned_only', false);
            $searchTerm = trim($request->get('search', ''));
            $limit = min((int)$request->get('limit', 20), 50); // Max 50 results
            $includeAssigned = $request->boolean('include_assigned', false);

            if (!empty($searchTerm)) {
                // Validate search term format
                $nameValidation = StudentValidationService::validateStudentNameFormat($searchTerm);
                if (!$nameValidation['valid'] && strlen($searchTerm) > 1) {
                    return response()->json([
                        'success' => false,
                        'message' => $nameValidation['message'],
                        'students' => [],
                        'total_count' => 0
                    ], 422);
                }

                // Search students by name
                $students = StudentValidationService::searchStudents($searchTerm, $limit);

                // Filter out already assigned students if requested for a specific room
                if (!$includeAssigned && $roomNumber) {
                    $assignedStudentIds = \App\Models\RoomAssignment::where('room_number', $roomNumber)
                        ->pluck('student_id')
                        ->toArray();

                    $students = array_filter($students, function($student) use ($assignedStudentIds) {
                        return !in_array($student['id'], $assignedStudentIds);
                    });
                    $students = array_values($students); // Re-index array
                }
            } elseif ($roomNumber) {
                // Get available students for specific room (excluding already assigned)
                $students = StudentValidationService::getAvailableStudentsForRoom($roomNumber);
                $students = array_slice($students, 0, $limit); // Apply limit
            } else {
                // Get all valid students
                $students = StudentValidationService::getAllValidStudents();
                $students = array_slice($students, 0, $limit); // Apply limit
            }

            // Apply occupant_type filtering if requested
            if (!empty($occupantType) && in_array($occupantType, ['male', 'female', 'both'])) {
                if ($occupantType !== 'both') {
                    $genderMap = ['male' => 'M', 'female' => 'F'];
                    $gender = $genderMap[$occupantType] ?? null;
                    if ($gender) {
                        $students = array_filter($students, function($s) use ($gender) {
                            return isset($s['gender']) && $s['gender'] === $gender;
                        });
                        $students = array_values($students);
                    }
                }
            }

            // If caller requested only unassigned students, exclude any student who already has an assignment
            if ($unassignedOnly) {
                $allAssignedIds = \App\Models\RoomAssignment::pluck('student_id')->toArray();
                $students = array_filter($students, function($s) use ($allAssignedIds) {
                    return !in_array($s['id'], $allAssignedIds);
                });
                $students = array_values($students);
            }

            // Add additional info for each student
            $enrichedStudents = array_map(function($student) {
                return [
                    'id' => $student['id'],
                    'name' => $student['name'],
                    'gender' => $student['gender'],
                    'batch' => $student['batch'],
                    'gender_text' => $student['gender'] === 'M' ? 'Male' : 'Female',
                    'display_name' => $student['name'] . ' (' . ($student['gender'] === 'M' ? 'M' : 'F') . ', ' . $student['batch'] . ')'
                ];
            }, $students);

            return response()->json([
                'success' => true,
                'students' => $enrichedStudents,
                'total_count' => count($enrichedStudents),
                'search_term' => $searchTerm,
                'room_number' => $roomNumber
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting valid students list', [
                'search_term' => $searchTerm ?? null,
                'room_number' => $roomNumber ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students list. Please try again.',
                'students' => [],
                'total_count' => 0
            ], 500);
        }
    }

    /**
     * Reassign students with new capacity - Optimized for speed
     */
    public function reassignStudents(Request $request)
    {
        try {
            // Increase execution time limit for this operation
            set_time_limit(120); // 2 minutes

            $validated = $request->validate([
                'capacity' => 'required|integer|min:1|max:20'
            ]);

            $startTime = microtime(true);
            \Log::info('Starting fast student reassignment', [
                'new_capacity' => $validated['capacity'],
                'timestamp' => now()
            ]);

            // FORCE set new capacity with absolute priority
            // Only clear room-related session data, preserve user authentication
            session()->forget(['room_capacity', 'reassignment_in_progress']);
            session(['room_capacity' => $validated['capacity']]);
            session(['reassignment_in_progress' => true]);

            \Log::info('Capacity set with absolute priority', [
                'new_capacity' => $validated['capacity'],
                'session_capacity' => session('room_capacity')
            ]);

            // Check if there are any active rooms before mutating database
            $availableRooms = \App\Models\Room::where('status', 'active')
                ->orderBy('room_number')
                ->pluck('room_number')
                ->toArray();

            if (empty($availableRooms)) {
                // No active rooms - do NOT truncate existing assignments or tasks.
                // Clean up temporary session flag and return success because capacity was applied.
                session()->forget('reassignment_in_progress');

                \Log::warning('Capacity applied but no active rooms available for assignment', [
                    'capacity' => $validated['capacity']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Capacity applied but no active rooms available for assignment. Please create or activate rooms before performing reassignment.',
                    'capacity' => $validated['capacity'],
                    'roomStudents' => (object)[]
                ]);
            }

            // Clear existing assignments in one operation
            RoomAssignment::truncate();

            // Clear existing base room tasks in one operation
            RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->delete();

            // Generate new assignments efficiently
            $roomStudents = $this->generateOptimizedRoomAssignments($validated['capacity']);

            // Quick validation check
            $validationResult = $this->validateGenderSeparation($roomStudents);

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gender separation validation failed: ' . $validationResult['message']
                ], 400);
            }

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

            \Log::info('Fast student reassignment completed', [
                'execution_time_ms' => $executionTime,
                'capacity' => $validated['capacity'],
                'total_rooms_assigned' => count(array_filter($roomStudents, function($students) { return !empty($students); }))
            ]);

            // Clean up temporary session flag after successful reassignment
            session()->forget('reassignment_in_progress');

            // Return response with dashboard sync data
            return response()->json([
                'success' => true,
                'message' => 'Students successfully reassigned!',
                'roomStudents' => $roomStudents,
                'capacity' => $validated['capacity'],
                'validation' => $validationResult,
                'execution_time_ms' => $executionTime,
                'dashboard_sync' => true // Flag to trigger dashboard refresh
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reassigning students: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'capacity' => $validated['capacity'] ?? 'unknown'
            ]);

            // Clean up temporary session flag on error
            session()->forget('reassignment_in_progress');

            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign students. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate optimized room assignments for fast reassignment
     */
    private function generateOptimizedRoomAssignments($studentsPerRoom)
    {
        try {
            // Get available room numbers from the database
            $availableRooms = \App\Models\Room::where('status', 'active')
                ->orderBy('room_number')
                ->pluck('room_number')
                ->toArray();

            if (empty($availableRooms)) {
                throw new \Exception('No active rooms available for assignment');
            }

            // Fetch all active students with gender in one optimized query
            $students = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->select('user_id', 'user_fname', 'user_lname', 'gender')
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->user_id,
                        'name' => trim($student->user_fname . ' ' . $student->user_lname),
                        'gender' => $student->gender
                    ];
                })
                ->toArray();

            // Separate by gender for proper room assignment
            $maleStudents = array_filter($students, function($student) {
                return $student['gender'] === 'M';
            });
            $femaleStudents = array_filter($students, function($student) {
                return $student['gender'] === 'F';
            });

            // Sort deterministically by name for consistent assignment order
            usort($maleStudents, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            usort($femaleStudents, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $roomStudents = [];
            $assignments = [];
            $timestamp = now();
            $batchYear = date('Y');
            $roomIndex = 0;

            // Assign male students to available rooms
            $maleChunks = array_chunk($maleStudents, $studentsPerRoom);

            foreach ($maleChunks as $chunk) {
                if ($roomIndex >= count($availableRooms)) {
                    \Log::warning('Not enough rooms available for all male students');
                    break;
                }

                $roomNumber = $availableRooms[$roomIndex];
                $roomStudents[$roomNumber] = array_column($chunk, 'name');

                // Prepare batch insert data
                foreach ($chunk as $index => $student) {
                    $assignments[] = [
                        'room_number' => $roomNumber,
                        'student_id' => $student['id'],
                        'student_name' => $student['name'],
                        'student_gender' => $student['gender'],
                        'batch_year' => $batchYear,
                        'assignment_order' => $index + 1,
                        'room_capacity' => $studentsPerRoom,
                        'assigned_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
                $roomIndex++;
            }

            // Assign female students to remaining available rooms
            $femaleChunks = array_chunk($femaleStudents, $studentsPerRoom);

            foreach ($femaleChunks as $chunk) {
                if ($roomIndex >= count($availableRooms)) {
                    \Log::warning('Not enough rooms available for all female students');
                    break;
                }

                $roomNumber = $availableRooms[$roomIndex];
                $roomStudents[$roomNumber] = array_column($chunk, 'name');

                // Prepare batch insert data
                foreach ($chunk as $index => $student) {
                    $assignments[] = [
                        'room_number' => $roomNumber,
                        'student_id' => $student['id'],
                        'student_name' => $student['name'],
                        'student_gender' => $student['gender'],
                        'batch_year' => $batchYear,
                        'assignment_order' => $index + 1,
                        'room_capacity' => $studentsPerRoom,
                        'assigned_at' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp
                    ];
                }
                $roomIndex++;
            }

            // Insert all room assignments at once (already in transaction)
            if (!empty($assignments)) {
                RoomAssignment::insert($assignments);
                \Log::info('Room assignments saved to database', [
                    'capacity' => $studentsPerRoom,
                    'assignments_count' => count($assignments)
                ]);
            }

            // Generate room tasks efficiently
            $this->generateOptimizedRoomTasks($roomStudents);

            return $roomStudents;

        } catch (\Exception $e) {
            \Log::error('Error in generateOptimizedRoomAssignments: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate room tasks efficiently using batch operations
     */
    private function generateOptimizedRoomTasks($roomStudents)
    {
        // Load tasks from DB templates
        [$baseTasks, $fixedTasks] = $this->getTaskTemplates();
        $taskAreas = [];
        foreach ($baseTasks as $tpl) {
            $taskAreas[$tpl['area']] = $tpl['desc'];
        }

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $areaKeys = array_keys($taskAreas);
        $areaCount = count($areaKeys);
        $allTasks = [];

        foreach ($roomStudents as $roomNumber => $students) {
            if (empty($students)) continue;

            $studentCount = count($students);

            foreach ($daysOfWeek as $dayIndex => $day) {
                // Rotate students for each day
                $rotatedStudents = $students;
                if ($studentCount > 1) {
                    $rotatedStudents = array_merge(
                        array_slice($students, $dayIndex % $studentCount),
                        array_slice($students, 0, $dayIndex % $studentCount)
                    );
                }
                // Guard: if there are no template task areas, only add fixed tasks to avoid modulo by zero
                if ($areaCount === 0) {
                    \Log::warning("No task areas/templates found; creating only fixed tasks for room {$roomNumber} on {$day}");
                    foreach ($fixedTasks as $task) {
                        $allTasks[] = [
                            'name' => 'Everyone',
                            'room_number' => $roomNumber,
                            'area' => $task['area'],
                            'desc' => $task['desc'],
                            'day' => $day,
                            'status' => 'not yet',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    continue;
                }

                // Assign areas to students
                for ($i = 0; $i < $studentCount; $i++) {
                    $areaIndex = $i % $areaCount;
                    $areaKey = $areaKeys[$areaIndex];
                    $areaDesc = $taskAreas[$areaKey];

                    $allTasks[] = [
                        'name' => $rotatedStudents[$i],
                        'room_number' => $roomNumber,
                        'area' => $areaKey,
                        'desc' => $areaDesc,
                        'day' => $day,
                        'status' => 'not yet',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Add fixed tasks for everyone
                foreach ($fixedTasks as $task) {
                    $allTasks[] = [
                        'name' => 'Everyone',
                        'room_number' => $roomNumber,
                        'area' => $task['area'],
                        'desc' => $task['desc'],
                        'day' => $day,
                        'status' => 'not yet',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }

        // Batch insert all room tasks at once
        if (!empty($allTasks)) {
            // Insert in chunks to avoid memory issues with large datasets
            $chunks = array_chunk($allTasks, 500);
            foreach ($chunks as $chunk) {
                RoomTask::insert($chunk);
            }
            \Log::info('Batch inserted room tasks', ['total_tasks' => count($allTasks)]);
        }
    }

    /**
     * Validate that room assignments maintain gender separation
     */
    private function validateGenderSeparation($roomStudents)
    {
        try {
            $violations = [];
            $totalRooms = 0;
            $maleRooms = 0;
            $femaleRooms = 0;
            $mixedRooms = [];

            foreach ($roomStudents as $roomNumber => $students) {
                if (empty($students)) {
                    continue;
                }

                $totalRooms++;

                // Get gender information from RoomAssignment table (more efficient)
                $studentGenders = RoomAssignment::where('room_number', $roomNumber)
                    ->whereIn('student_name', $students)
                    ->pluck('student_gender')
                    ->unique()
                    ->toArray();

                // If no data in RoomAssignment table, fall back to PNUser table
                if (empty($studentGenders)) {
                    $studentGenders = PNUser::where('user_role', 'student')
                        ->where('status', 'active')
                        ->get(['user_fname', 'user_lname', 'gender'])
                        ->filter(function($user) use ($students) {
                            $fullName = trim($user->user_fname . ' ' . $user->user_lname);
                            return in_array($fullName, $students);
                        })
                        ->pluck('gender')
                        ->unique()
                        ->toArray();
                }

                // Check for mixed genders in the same room
                if (count($studentGenders) > 1) {
                    $mixedRooms[] = $roomNumber;
                    $violations[] = "Room {$roomNumber} contains both male and female students";
                } elseif (count($studentGenders) === 1) {
                    if ($studentGenders[0] === 'M') {
                        $maleRooms++;
                    } elseif ($studentGenders[0] === 'F') {
                        $femaleRooms++;
                    }
                }
            }

            $isValid = empty($violations);

            return [
                'valid' => $isValid,
                'message' => $isValid ? 'All rooms maintain proper gender separation' : implode('; ', $violations),
                'statistics' => [
                    'total_rooms' => $totalRooms,
                    'male_rooms' => $maleRooms,
                    'female_rooms' => $femaleRooms,
                    'mixed_rooms' => count($mixedRooms),
                    'violations' => $violations
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Error validating gender separation: ' . $e->getMessage());

            return [
                'valid' => false,
                'message' => 'Validation error: ' . $e->getMessage(),
                'statistics' => []
            ];
        }
    }

    /**
     * Get detailed room assignment statistics
     */
    public function getRoomStatistics()
    {
        try {
            // Increase execution time limit for this operation
            set_time_limit(60); // 1 minute

            \Log::info('getRoomStatistics called');

            $roomStudents = $this->getDynamicRoomStudents();
            \Log::info('Room students retrieved', ['count' => count($roomStudents)]);

            $validation = $this->validateGenderSeparation($roomStudents);
            \Log::info('Gender validation completed', ['valid' => $validation['valid']]);

            // Get total student counts by gender
            $studentCounts = PNUser::where('user_role', 'student')
                ->where('status', 'active')
                ->selectRaw('gender, COUNT(*) as count')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();

            \Log::info('Student counts retrieved', ['counts' => $studentCounts]);

            // PRIORITY FIX: Always use session capacity if available for statistics
            $sessionCapacity = session('room_capacity');
            if ($sessionCapacity && is_numeric($sessionCapacity) && $sessionCapacity > 0) {
                $capacity = (int) $sessionCapacity;
                \Log::info('Room capacity retrieved from session (priority)', ['capacity' => $capacity]);
            } else {
                $capacity = $this->getRoomCapacitySetting();
                \Log::info('Room capacity retrieved from fallback method', ['capacity' => $capacity]);
            }

            return response()->json([
                'success' => true,
                'room_students' => $roomStudents,
                'validation' => $validation,
                'student_counts' => $studentCounts,
                'capacity' => $capacity
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting room statistics: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get room statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize room tasks with current room assignments
     * This ensures that the task checklist always reflects the current student assignments
     */
    public function syncRoomTasksWithAssignments()
    {
        try {
            \Log::info('Starting room task synchronization...');

            // Get current room assignments
            $roomStudents = $this->getDynamicRoomStudents();

            // Get all rooms that have students assigned
            $roomsWithStudents = array_filter($roomStudents, function($students) {
                return !empty($students);
            });

            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // Load tasks from DB templates
            [$baseTasks, $fixedTasks] = $this->getTaskTemplates();
            $taskAreas = [];
            foreach ($baseTasks as $tpl) {
                $taskAreas[$tpl['area']] = $tpl['desc'];
            }

            foreach ($roomsWithStudents as $roomNumber => $students) {
                \Log::info("Syncing tasks for room {$roomNumber} with students: " . implode(', ', $students));

                // Remove existing tasks for this room (only base tasks, not completed ones)
                RoomTask::where('room_number', $roomNumber)
                    ->whereNull('week')
                    ->whereNull('month')
                    ->whereNull('year')
                    ->delete();

                foreach ($daysOfWeek as $day) {
                    // Create individual student tasks with rotating assignments
                    $studentCount = count($students);
                    $areaKeys = array_keys($taskAreas);
                    $areaCount = count($areaKeys);

                    // Get day index for rotation
                    $dayIndex = array_search($day, $daysOfWeek);

                    // Rotate students for this day
                    $rotatedStudents = $students;
                    if ($studentCount > 1) {
                        $rotatedStudents = array_merge(
                            array_slice($students, $dayIndex % $studentCount),
                            array_slice($students, 0, $dayIndex % $studentCount)
                        );
                    }


                    // Guard: if there are no template task areas, only create fixed tasks to avoid division by zero
                    if ($areaCount === 0) {
                        \Log::warning("No task areas/templates found; creating only fixed tasks for room {$roomNumber} on {$day}");
                        foreach ($fixedTasks as $task) {
                            RoomTask::create([
                                'name' => 'Everyone',
                                'room_number' => $roomNumber,
                                'area' => $task['area'],
                                'desc' => $task['desc'],
                                'day' => $day,
                                'status' => 'not yet'
                            ]);
                        }
                        continue;
                    }

                    // Assign areas to students
                    for ($i = 0; $i < $studentCount; $i++) {
                        $areaIndex = $i % $areaCount;
                        $areaKey = $areaKeys[$areaIndex];
                        $areaDesc = $taskAreas[$areaKey];

                        RoomTask::create([
                            'name' => $rotatedStudents[$i],
                            'room_number' => $roomNumber,
                            'area' => $areaKey,
                            'desc' => $areaDesc,
                            'day' => $day,
                            'status' => 'not yet'
                        ]);
                    }

                    // Add fixed tasks for everyone
                    foreach ($fixedTasks as $task) {
                        RoomTask::create([
                            'name' => 'Everyone',
                            'room_number' => $roomNumber,
                            'area' => $task['area'],
                            'desc' => $task['desc'],
                            'day' => $day,
                            'status' => 'not yet'
                        ]);
                    }
                }
            }

            \Log::info('Room task synchronization completed successfully');
            return true;

        } catch (\Exception $e) {
            \Log::error('Error syncing room tasks: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * API endpoint to manually trigger room task synchronization
     */
    public function syncRoomTasks(Request $request)
    {
        try {
            // Run the template-driven synchronization which uses DB templates
            \Log::info('Manual room task sync triggered (template-driven)');

            $ok = $this->syncRoomTasksWithAssignments();

            if ($ok === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room task synchronization failed'
                ], 500);
            }

            // Count base (template) tasks that were created
            $taskCount = RoomTask::whereNull('week')
                ->whereNull('month')
                ->whereNull('year')
                ->count();

            return response()->json([
                'success' => true,
                'message' => "Room tasks synchronized successfully. Total base tasks: {$taskCount}.",
                'task_count' => $taskCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in manual room task sync: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize room tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add room to dashboard floor data
     */
    public function addRoomToDashboard(Request $request)
    {
        try {
            $roomNumber = $request->input('room_number');
            $floor = intval(substr($roomNumber, 0, 1));

            // Validate room number: must be 3 digits and floor must be 2 or above
            if (!preg_match('/^\d{3}$/', $roomNumber) || $floor < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid room number format'
                ], 422);
            }

            // Check if room already exists in database
            $existingRoom = Room::where('room_number', $roomNumber)->first();
            if ($existingRoom) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room already exists'
                ], 422);
            }

            // The room will be created by the RoomManagementController
            // This endpoint just confirms the dashboard can accept the new room
            \Log::info("Dashboard ready to accept new room: {$roomNumber} on floor {$floor}");

            return response()->json([
                'success' => true,
                'message' => 'Dashboard ready for new room',
                'floor' => $floor,
                'room_number' => $roomNumber
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding room to dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add room to dashboard'
            ], 500);
        }
    }

    /**
     * Delete room from dashboard floor data
     */
    public function deleteRoomFromDashboard($roomNumber)
    {
        try {
            // Delete related data and the room record in a single transaction so deletion persists
            DB::transaction(function() use ($roomNumber) {
                // Remove any persistent room assignments
                RoomAssignment::where('room_number', $roomNumber)->delete();

                // Remove any RoomTask rows associated with this room (base templates and date-scoped)
                RoomTask::where('room_number', $roomNumber)->delete();

                // Remove rotation schedules for this room if the model/table exists
                if (class_exists('\\App\\Models\\RotationSchedule')) {
                    try {
                        \App\Models\RotationSchedule::where('room', $roomNumber)->delete();
                    } catch (\Throwable $inner) {
                        // ignore if login DB not available
                        \Log::warning('Failed to delete rotation schedules for room ' . $roomNumber . ': ' . $inner->getMessage());
                    }
                }

                // Finally remove the Room row so it no longer appears in dashboard queries
                \App\Models\Room::where('room_number', $roomNumber)->delete();

                // Clear caches that affect dashboard rendering
                \Cache::forget('room_assignments_' . $roomNumber);
                \Cache::forget('dynamic_room_students');
            });

            \Log::info("Deleted room and related data: {$roomNumber}");

            return response()->json([
                'success' => true,
                'message' => 'Room removed from dashboard',
                'room_number' => $roomNumber
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting room from dashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room from dashboard'
            ], 500);
        }
    }

    /**
     * Get room assignments directly from database for dashboard
     */
    public function getRoomAssignmentsForDashboard()
    {
        $assignments = RoomAssignment::orderBy('room_number')
            ->orderBy('assignment_order')
            ->get()
            ->groupBy('room_number')
            ->map(function ($roomAssignments) {
                return $roomAssignments->pluck('student_name')->toArray();
            })
            ->toArray();

        return $assignments;
    }

    /**
     * Get current dashboard room data
     */
    public function getDashboardRoomData()
    {
        try {
            // Use direct database query for immediate updates
            $roomStudents = $this->getRoomAssignmentsForDashboard();

            // Load only active rooms from the rooms table. Dashboard should only show
            // rooms that are configured as active (student rooms).
            $roomsActive = \App\Models\Room::where('status', 'active')->orderBy('room_number')->get();
            $activeRoomNumbers = $roomsActive->pluck('room_number')->toArray();

            // Filter assignments to only include active rooms so inactive/non-student
            // rooms are not shown on the dashboard even if assignments exist for them.
            $roomStudents = array_filter($roomStudents, function($students, $roomNumber) use ($activeRoomNumbers) {
                return in_array($roomNumber, $activeRoomNumbers);
            }, ARRAY_FILTER_USE_BOTH);

            // Convert to floor structure using both assignments and active rooms from DB
            $floorData = [];

            // Start with assignments (filtered to active rooms) so occupied rooms are reflected
            foreach ($roomStudents as $roomNumber => $students) {
                // Infer floor from room number (fallback - handle multi-digit floors too)
                $floor = 0;
                $numeric = preg_replace('/\D/', '', $roomNumber);
                if ($numeric !== '') {
                    $floor = (int) floor(intval($numeric) / 100);
                    if ($floor === 0) {
                        // fallback to first digit
                        $floor = intval(substr($roomNumber, 0, 1));
                    }
                }

                if (!isset($floorData[$floor])) {
                    $floorData[$floor] = [
                        'rooms' => [],
                        'students' => []
                    ];
                }

                if (!in_array($roomNumber, $floorData[$floor]['rooms'])) {
                    $floorData[$floor]['rooms'][] = $roomNumber;
                }

                $floorData[$floor]['students'][$roomNumber] = $students;
            }

            // Ensure every active room from the rooms table appears in floorData (even if empty)
            $roomCapacities = [];
            // reuse previously loaded active rooms to avoid including inactive/non-student rooms
            $rooms = $roomsActive;
            foreach ($rooms as $room) {
                $roomNumber = $room->room_number;

                // Determine floor using explicit column when available
                if (isset($room->floor) && $room->floor !== null) {
                    $floor = (int) $room->floor;
                } else {
                    $numeric = preg_replace('/\D/', '', $roomNumber);
                    $floor = $numeric !== '' ? (int) floor(intval($numeric) / 100) : intval(substr($roomNumber, 0, 1));
                }

                if (!isset($floorData[$floor])) {
                    $floorData[$floor] = [
                        'rooms' => [],
                        'students' => []
                    ];
                }

                if (!in_array($roomNumber, $floorData[$floor]['rooms'])) {
                    $floorData[$floor]['rooms'][] = $roomNumber;
                }

                if (!isset($floorData[$floor]['students'][$roomNumber])) {
                    $floorData[$floor]['students'][$roomNumber] = [];
                }

                $roomCapacities[$roomNumber] = $room->capacity;
            }

            // Sort rooms within each floor
            foreach ($floorData as $floor => $data) {
                sort($floorData[$floor]['rooms']);
            }

            // Get current global room capacity with session priority
            $sessionCapacity = session('room_capacity');
            if ($sessionCapacity && is_numeric($sessionCapacity) && $sessionCapacity > 0) {
                $currentCapacity = (int) $sessionCapacity;
                \Log::info('Dashboard API using session capacity', ['capacity' => $currentCapacity]);
            } else {
                $currentCapacity = $this->getRoomCapacitySetting();
                \Log::info('Dashboard API using fallback capacity', ['capacity' => $currentCapacity]);
            }

            // For rooms without specific capacity, use global capacity
            foreach ($roomStudents as $roomNumber => $students) {
                if (!isset($roomCapacities[$roomNumber])) {
                    $roomCapacities[$roomNumber] = $currentCapacity;
                }
            }

            return response()->json([
                'success' => true,
                'floor_data' => $floorData,
                'room_assignments' => $roomStudents,
                'room_capacities' => $roomCapacities,
                // total_rooms should reflect configured (active) rooms shown on the dashboard
                'total_rooms' => count($roomCapacities),
                'room_capacity' => $currentCapacity
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting dashboard room data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard room data'
            ], 500);
        }
    }

    /**
     * Auto-assign students to a room based on capacity - Enhanced for dynamic assignment
     */
    private function autoAssignStudentsToRoom($roomNumber, $studentsNeeded, $settings)
    {
        try {
            \Log::info("Auto-assigning students to room {$roomNumber}", [
                'students_needed' => $studentsNeeded,
                'settings' => $settings
            ]);

            // Get current room assignments to avoid duplicates
            $currentAssignments = $this->getDynamicRoomStudents();
            $assignedStudents = [];
            foreach ($currentAssignments as $room => $students) {
                $assignedStudents = array_merge($assignedStudents, $students);
            }

            // Determine gender requirement for the room
            $roomGender = null;
            if (isset($currentAssignments[$roomNumber]) && !empty($currentAssignments[$roomNumber])) {
                $roomGender = $this->getRoomGender($currentAssignments[$roomNumber]);
                \Log::info("Room has existing gender requirement", [
                    'room' => $roomNumber,
                    'gender' => $roomGender
                ]);
            }

            // Determine batch requirement
            $batchFilter = null;
            if (isset($settings['assigned_batch']) && $settings['assigned_batch'] !== '') {
                $batchFilter = $settings['assigned_batch'];
            }

            // Build query for available students from Login database
            $query = PNUser::where('user_role', 'student')
                ->where('status', 'active');

            // Exclude already assigned students
            if (!empty($assignedStudents)) {
                $query->whereNotIn(DB::raw("CONCAT(user_fname, ' ', user_lname)"), $assignedStudents);
            }

            // Apply gender filter if room has existing students
            if ($roomGender) {
                $query->where('gender', $roomGender);
                \Log::info("Filtering by gender", ['gender' => $roomGender]);
            }

            // Apply batch filter if specified
            if ($batchFilter) {
                $query->whereHas('studentDetail', function($q) use ($batchFilter) {
                    $q->where('batch', $batchFilter);
                });
                \Log::info("Filtering by batch", ['batch' => $batchFilter]);
            }

            // Get available students
            $availableStudents = $query->limit($studentsNeeded * 2)->get(); // Get more than needed for better selection

            \Log::info("Found available students", [
                'total_found' => $availableStudents->count(),
                'needed' => $studentsNeeded
            ]);

            $newStudents = [];
            $assignedCount = 0;

            foreach ($availableStudents as $student) {
                if ($assignedCount >= $studentsNeeded) break;

                $studentName = trim($student->user_fname . ' ' . $student->user_lname);

                // Double-check this student isn't already assigned
                if (!in_array($studentName, $assignedStudents)) {
                    $newStudents[] = $studentName;
                    $assignedStudents[] = $studentName; // Add to prevent duplicates in this session
                    $assignedCount++;

                    \Log::info("Auto-assigned student to room", [
                        'student' => $studentName,
                        'room' => $roomNumber,
                        'gender' => $student->gender,
                        'batch' => optional($student->studentDetail)->batch ?? 'unknown'
                    ]);
                }
            }

            \Log::info("Auto-assignment completed", [
                'room' => $roomNumber,
                'requested' => $studentsNeeded,
                'assigned' => count($newStudents),
                'students' => $newStudents
            ]);

            return $newStudents;

        } catch (\Exception $e) {
            \Log::error("Error in auto-assignment: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Update other room capacities when auto-update is enabled
     */
    private function updateOtherRoomCapacities($targetRoom, $newCapacity, &$roomStudents, &$affectedRooms)
    {
        try {
            \Log::info("Updating other room capacities", [
                'target_room' => $targetRoom,
                'new_capacity' => $newCapacity
            ]);

            // Get all existing rooms
            $allRooms = array_keys($roomStudents);

            foreach ($allRooms as $roomNumber) {
                if ($roomNumber === $targetRoom) continue; // Skip the target room

                // Update capacity in database
                $this->updateRoomCapacityInDatabase($roomNumber, [
                    'capacity' => $newCapacity,
                    'name' => "Room {$roomNumber}",
                    'status' => 'active'
                ]);

                // Check if room needs student adjustments
                $currentStudents = $roomStudents[$roomNumber] ?? [];
                $currentCount = count($currentStudents);

                if ($currentCount > $newCapacity) {
                    // Need to relocate excess students
                    $studentsToKeep = array_slice($currentStudents, 0, $newCapacity);
                    $studentsToMove = array_slice($currentStudents, $newCapacity);

                    $roomStudents[$roomNumber] = $studentsToKeep;

                    // Try to relocate excess students to other rooms
                    foreach ($studentsToMove as $studentName) {
                        $this->relocateStudentToAvailableRoom($studentName, $roomStudents, $newCapacity);
                    }
                } elseif ($currentCount < $newCapacity) {
                    // Room has space - could auto-assign more students if needed
                    // This is handled separately if auto-assignment is enabled globally
                }

                // Track affected room
                $affectedRooms[$roomNumber] = [
                    'students' => $roomStudents[$roomNumber] ?? [],
                    'capacity' => $newCapacity,
                    'occupancy' => count($roomStudents[$roomNumber] ?? [])
                ];

                \Log::info("Updated room capacity", [
                    'room' => $roomNumber,
                    'new_capacity' => $newCapacity,
                    'current_occupancy' => count($roomStudents[$roomNumber] ?? [])
                ]);
            }

        } catch (\Exception $e) {
            \Log::error("Error updating other room capacities: " . $e->getMessage());
        }
    }

    /**
     * Relocate a student to an available room
     */
    private function relocateStudentToAvailableRoom($studentName, &$roomStudents, $maxCapacity)
    {
        $studentGender = $this->getStudentGender($studentName);

        foreach ($roomStudents as $roomNumber => $students) {
            if (count($students) < $maxCapacity) {
                // Check gender compatibility
                if (empty($students)) {
                    // Empty room - can place student
                    $roomStudents[$roomNumber][] = $studentName;
                    return true;
                } else {
                    $roomGender = $this->getRoomGender($students);
                    if ($roomGender === $studentGender) {
                        $roomStudents[$roomNumber][] = $studentName;
                        return true;
                    }
                }
            }
        }

        // If no existing room found, create new room
        $newRoomNumber = $this->findAvailableRoomNumber($roomStudents);
        $roomStudents[$newRoomNumber] = [$studentName];
        return true;
    }

    /**
     * Manually schedule assignments for a specific day or date range.
     * - mode: manual or auto
     * - manual: expects assignments: [{task_id, student_name}] for the day
     * - auto: copies base template tasks into the specified date range
     */
    public function scheduleTasks(Request $request)
    {
        $validated = $request->validate([
            'room' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'mode' => 'required|in:manual,auto',
            'frequency' => 'nullable|in:daily,weekly,monthly',
            'assignments' => 'nullable|array',
        ]);

        $room = $validated['room'];
        $start = new \Carbon\Carbon($validated['start_date']);
        $end = isset($validated['end_date']) ? new \Carbon\Carbon($validated['end_date']) : (clone $start);

        // Prevent applying a new rotation schedule if an active schedule exists for this room
        try {
            $today = now()->toDateString();
            $activeExists = \App\Models\RotationSchedule::where('room', $room)
                ->where(function($q) use ($today) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
                })->exists();
            if ($activeExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'An active rotation schedule exists for this room. You can only apply a new schedule after the current schedule end date.'
                ], 409);
            }
        } catch (\Exception $e) {
            // If the RotationSchedule table/connection is not available, allow the operation to proceed
            logger()->debug('Could not check active rotation schedule before applying new one: ' . $e->getMessage());
        }

        try {
            if ($validated['mode'] === 'manual') {
                $dayName = $start->format('l');
                $week = $start->format('W');
                $month = $start->format('n');
                $year = $start->format('Y');

                foreach ($validated['assignments'] ?? [] as $a) {
                    if (!isset($a['task_id']) || !isset($a['student_name'])) continue;
                    $task = RoomTask::where('id', $a['task_id'])
                        ->where('room_number', $room)
                        ->where('day', $dayName)
                        ->first();
                    if ($task) {
                        $task->name = $a['student_name'];
                        $task->week = $week;
                        $task->month = $month;
                        $task->year = $year;
                        $task->save();
                    }
                }

                // Persist the manual schedule_map if provided
                $schedule_map = $request->input('schedule_map') ?? null;
                $persisted = $this->persistRotationScheduleRecord($room, $start, $end, 'manual', $validated['frequency'] ?? null, $schedule_map);
                if (!$persisted) {
                    return response()->json(['success' => false, 'message' => 'Failed to persist manual schedule. Check server logs.'], 500);
                }
                // Return saved rotation id for client confirmation
                try {
                    $saved = \App\Models\RotationSchedule::where('room', $room)->orderBy('created_at', 'desc')->first();
                    $rotationId = $saved ? $saved->id : null;
                } catch (\Throwable $e) {
                    $rotationId = null;
                }

                return response()->json(['success' => true, 'message' => 'Manual schedule saved', 'rotation_id' => $rotationId]);
            } else {
                // Auto mode: copy base template tasks for the date range
                $frequency = $validated['frequency'] ?? 'daily';
                while ($start->lte($end)) {
                    $dayName = $start->format('l');
                    $week = $start->format('W');
                    $month = $start->format('n');
                    $year = $start->format('Y');

                    // Get base template tasks for this room and day
                    $baseForDay = RoomTask::where('room_number', $room)
                        ->where('day', $dayName)
                        ->whereNull('week')
                        ->whereNull('month')
                        ->whereNull('year')
                        ->get();

                    foreach ($baseForDay as $tpl) {
                        // Check if task already exists for this specific date
                        $exists = RoomTask::where('room_number', $room)
                            ->where('day', $dayName)
                            ->where('week', $week)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->where('name', $tpl->name)
                            ->where('area', $tpl->area)
                            ->exists();

                        if (!$exists) {
                            RoomTask::create([
                                'name' => $tpl->name,
                                'room_number' => $room,
                                'area' => $tpl->area,
                                'desc' => $tpl->desc,
                                'day' => $dayName,
                                'status' => 'not yet',
                                'week' => $week,
                                'month' => $month,
                                'year' => $year,
                            ]);
                        }
                    }

                    // advance the start date based on selected frequency
                    if ($frequency === 'weekly') {
                        $start->addDays(7);
                    } elseif ($frequency === 'monthly') {
                        $start->addMonth();
                    } else {
                        $start->addDay();
                    }
                }

                // Attempt to persist generated schedule_map for the auto run
                // Build a minimal schedule_map if request didn't supply one
                $generated_map = $request->input('schedule_map') ?? null;
                if (!$generated_map) {
                    // Build a map from baseForDay copy for the range
                    try {
                        $map = [];
                        $cursor = $request->input('start_date') ? new \Carbon\Carbon($request->input('start_date')) : null;
                        $endCursor = $request->input('end_date') ? new \Carbon\Carbon($request->input('end_date')) : null;
                        if ($cursor && $endCursor) {
                            while ($cursor->lte($endCursor)) {
                                $iso = $cursor->toDateString();
                                $dayName = $cursor->format('l');
                                $baseForDay = \App\Models\RoomTask::where('room_number', $room)
                                    ->where('day', $dayName)
                                    ->whereNull('week')
                                    ->whereNull('month')
                                    ->whereNull('year')
                                    ->orderBy('id')
                                    ->get();
                                $map[$iso] = $baseForDay->map(function($t){ return ['task_id' => $t->id, 'task_area' => $t->area, 'student_name' => $t->name]; })->toArray();
                                if ($frequency === 'weekly') $cursor->addDays(7);
                                elseif ($frequency === 'monthly') $cursor->addMonth();
                                else $cursor->addDay();
                            }
                        }
                    } catch (\Throwable $e) {
                        $map = null;
                    }
                    $generated_map = $map ?? null;
                    }

                    // Normalize generated_map when it's a JSON string
                    if (is_string($generated_map)) {
                        $decodedGen = json_decode($generated_map, true);
                        if (json_last_error() === JSON_ERROR_NONE) $generated_map = $decodedGen;
                    }

                    // If we have a generated schedule_map, apply the assignments into RoomTask rows
                    // in the Login DB connection so assigned student names persist for the generated dates.
                    if ($generated_map && is_array($generated_map)) {
                        try {
                            $conn = DB::connection('login');
                            foreach ($generated_map as $iso => $assigns) {
                                try {
                                    $d = new \Carbon\Carbon($iso);
                                    $dayName = $d->format('l');
                                    $week = $d->format('W');
                                    $month = $d->format('n');
                                    $year = $d->format('Y');

                                    if (!is_array($assigns)) continue;

                                    foreach ($assigns as $a) {
                                        if (!isset($a['task_area'])) continue;
                                        $area = $a['task_area'];
                                        $studentName = $a['student_name'] ?? null;
                                        if (!$studentName) continue;

                                        // Try to update an existing task for that specific date in login DB
                                        $task = $conn->table('roomtask')
                                            ->where('room_number', $room)
                                            ->where('day', $dayName)
                                            ->where('week', $week)
                                            ->where('month', $month)
                                            ->where('year', $year)
                                            ->where('area', $area)
                                            ->first();

                                        if ($task) {
                                            $conn->table('roomtask')
                                                ->where('id', $task->id)
                                                ->update(['name' => $studentName, 'updated_at' => now()]);
                                        } else {
                                            // If no date-scoped task exists in login DB, try to find a base template there
                                            $tpl = $conn->table('roomtask')
                                                ->whereNull('week')
                                                ->whereNull('month')
                                                ->whereNull('year')
                                                ->where('room_number', $room)
                                                ->where('area', $area)
                                                ->orderBy('id')
                                                ->first();

                                            $desc = $tpl ? $tpl->desc : ($a['desc'] ?? null);

                                            $conn->table('roomtask')->insert([
                                                'name' => $studentName,
                                                'room_number' => $room,
                                                'area' => $area,
                                                'desc' => $desc,
                                                'day' => $dayName,
                                                'status' => 'not yet',
                                                'week' => $week,
                                                'month' => $month,
                                                'year' => $year,
                                                'created_at' => now(),
                                                'updated_at' => now()
                                            ]);
                                        }
                                    }
                                } catch (\Throwable $inner) {
                                    \Log::warning('Failed applying generated_map date assignments to login DB: ' . $inner->getMessage(), ['iso' => $iso, 'room' => $room]);
                                    continue;
                                }
                            }
                        } catch (\Throwable $e) {
                            \Log::error('Error applying generated schedule_map to RoomTask rows (login DB): ' . $e->getMessage());
                        }
                    }

                    $persisted = $this->persistRotationScheduleRecord($room, $start, $end, 'auto', $frequency, $generated_map);
                if (!$persisted) {
                    return response()->json(['success' => false, 'message' => 'Failed to persist auto-generated schedule. Check server logs.'], 500);
                }
                try {
                    $saved = \App\Models\RotationSchedule::where('room', $room)->orderBy('created_at', 'desc')->first();
                    $rotationId = $saved ? $saved->id : null;
                } catch (\Throwable $e) {
                    $rotationId = null;
                }

                return response()->json(['success' => true, 'message' => 'Auto schedule generated for range', 'rotation_id' => $rotationId]);
            }
        } catch (\Throwable $e) {
            \Log::error('scheduleTasks error: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to schedule tasks'], 500);
        }
    }

    // Persist a rotation schedule record in the Login DB for admin-generated schedules
    protected function persistRotationScheduleRecord($room, $start, $end, $mode, $frequency, $schedule_map = null)
    {
        // Normalize frequency to integer (migration expects integer)
        $freqVal = null;
        if (is_string($frequency)) {
            $f = strtolower($frequency);
            if ($f === 'daily') $freqVal = 1;
            elseif ($f === 'weekly') $freqVal = 7;
            elseif ($f === 'monthly') $freqVal = 30;
            else $freqVal = null;
        } elseif (is_numeric($frequency)) {
            $freqVal = intval($frequency);
        }

        // Normalize schedule_map
        if (is_string($schedule_map)) {
            // try decode
            $decoded = json_decode($schedule_map, true);
            if (json_last_error() === JSON_ERROR_NONE) $schedule_map = $decoded;
        }

        $payload = [
            'room' => $room,
            'start_date' => $start ? $start->toDateString() : null,
            'end_date' => $end ? $end->toDateString() : null,
            'mode' => $mode,
            'frequency' => $freqVal,
            'schedule_map' => $schedule_map,
            'created_by' => auth()->user() ? auth()->user()->id : null,
            'updated_at' => now(),
        ];

        // Log payload for debugging
        \Log::debug('persistRotationScheduleRecord payload', ['payload' => $payload]);

        // Try Eloquent save first
        try {
            $saved = \App\Models\RotationSchedule::updateOrCreate(
                ['room' => $room],
                $payload
            );
            \Log::debug('persistRotationScheduleRecord eloquent saved', ['id' => $saved ? $saved->id : null, 'connection' => (isset($saved) ? $saved->getConnectionName() : 'unknown')]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('persistRotationScheduleRecord eloquent error: ' . $e->getMessage());
            // Fallback: attempt a raw insert/update using the configured login connection
            try {
                $conn = DB::connection('login');
                // Use insert or update by room
                $exists = $conn->table('rotation_schedules')->where('room', $room)->exists();
                $now = now()->toDateTimeString();
                $row = [
                    'room' => $room,
                    'start_date' => $payload['start_date'],
                    'end_date' => $payload['end_date'],
                    'mode' => $payload['mode'],
                    'frequency' => $payload['frequency'],
                    'schedule_map' => is_array($payload['schedule_map']) ? json_encode($payload['schedule_map']) : $payload['schedule_map'],
                    'created_by' => $payload['created_by'],
                    'updated_at' => $now,
                ];
                if ($exists) {
                    $conn->table('rotation_schedules')->where('room', $room)->update($row);
                } else {
                    $row['created_at'] = $now;
                    $conn->table('rotation_schedules')->insert($row);
                }
                \Log::debug('persistRotationScheduleRecord fallback raw DB succeeded for room', ['room' => $room]);
                return true;
            } catch (\Throwable $e2) {
                \Log::error('persistRotationScheduleRecord fallback DB error: ' . $e2->getMessage());
                return false;
            }
        }
    }

    private function normalizeGeneratedStatus(?string $status): string
    {
        $normalized = strtolower(trim($status ?? ''));

        return match ($normalized) {
            'completed', 'complete', 'done' => 'completed',
            'in_progress', 'in progress', 'active', 'current' => 'in_progress',
            default => 'pending',
        };
    }
}






 