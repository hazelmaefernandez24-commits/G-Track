<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Assignment;
use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\RoomAssignment;
use App\Models\RoomTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TaskSubmission;
use App\Models\SystemSetting;

class MainStudentDASHController extends Controller
{
    public function index(Request $request)
    {
        // Optimize queries to prevent timeouts
        try {
            // Clean expired comments first
            \App\Models\AssignmentMember::cleanExpiredComments();

            // Load categories with current assignments for general tasks display
            $categories = Category::with(['assignments' => function($query) {
                $query->where('status', 'current')
                      ->latest()
                      ->take(1); // Get latest current assignment per category
            }, 'assignments.assignmentMembers' => function($query) {
                $query->limit(50); // Limit assignment members to prevent huge queries
            }, 'assignments.assignmentMembers.student'])->get();

            // Fetch current assignments for general tasks display
            $currentAssignments = Assignment::with(['category', 'assignmentMembers.student'])
                ->where('status', 'current')
                ->whereIn('category_id', [1,2,3,4,5,6])
                ->orderBy('start_date', 'desc')
                ->get();

            // Fetch recent assignments with full data including members
            $recentAssignments = Assignment::with(['category', 'assignmentMembers.student'])
                ->latest()
                ->take(10)
                ->get();

            // Fetch students for the student selection dropdown - use PNUser from Login DB
            $students = PNUser::where('user_role', 'student')
                ->orderBy('user_fname')
                ->orderBy('user_lname')
                ->get();

            // Fetch active batches from Login student_details (avoid relying on local batches table)
            $activeBatches = \App\Models\StudentDetail::select('batch')
                ->distinct()
                ->orderBy('batch')
                ->get()
                ->map(function($r) {
                    return (object)[
                        'year' => $r->batch,
                        'display_name' => (string)$r->batch,
                    ];
                });

            // Get current assignments for general task display
            $generalTaskAssignments = $currentAssignments->map(function($assignment) {
                return [
                    'category' => $assignment->category->name,
                    'members_count' => $assignment->assignmentMembers->count(),
                    'coordinators' => $assignment->assignmentMembers->where('is_coordinator', true)->map(function($member) {
                        return $member->student_group16 ? $member->student_group16->name : 'Unknown';
                    })->toArray(),
                    'start_date' => $assignment->start_date,
                    'end_date' => $assignment->end_date
                ];
            });

            // If the current user is a student, load their tasks (daily/weekly/monthly)
            $studentTasks = collect();
            $user = auth()->user();
            if ($user && (($user->user_role ?? '') === 'student' || ($user->role ?? '') === 'student')) {
                $studentTasks = $this->fetchTasksForStudent($request, $user);
            }

            // Don't clear shuffle results immediately - let them persist for the General Task view
            // They will be cleared when a new shuffle is performed

            return view('StudentsDashboard.MainStudentDASH', compact('categories', 'recentAssignments', 'students', 'currentAssignments', 'activeBatches', 'generalTaskAssignments', 'studentTasks'));
        } catch (\Exception $e) {
            // Log the error and return a fallback view
            \Log::error('MainStudentDASH timeout error: ' . $e->getMessage());

            // Return minimal data to prevent timeout
            $categories = Category::with(['assignments' => function($query) {
                $query->latest()->take(1);
            }])->take(10)->get();
            $recentAssignments = Assignment::with(['category'])->latest()->take(5)->get();
            $students = PNUser::where('user_role', 'student')->take(20)->get(); // Limited students for fallback
            $currentAssignments = collect(); // Empty collection for fallback
            $activeBatches = \App\Models\StudentDetail::select('batch')
                ->distinct()
                ->orderBy('batch')
                ->take(2)
                ->get()
                ->map(function($r) { return (object)['year' => $r->batch, 'display_name' => (string)$r->batch]; });

            $generalTaskAssignments = collect();

            return view('StudentsDashboard.MainStudentDASH', compact('categories', 'recentAssignments', 'students', 'currentAssignments', 'activeBatches', 'generalTaskAssignments'))
                ->with('error', 'Some data may be limited due to performance optimization.');
        }
    }

    public function generalTask()
    {
        try {
            \Log::info("Student General Task - Method called successfully");

            // Test database connection first
            try {
                \DB::connection()->getPdo();
                \Log::info("Database connection successful");
            } catch (\Exception $dbError) {
                \Log::error("Database connection failed: " . $dbError->getMessage());
                return response('<h1>Database Connection Error</h1><p>' . $dbError->getMessage() . '</p><p><a href="/test-student-general-task">Try Again</a></p>');
            }

            // Get current assignments with proper eager loading using the correct relationship
            $currentAssignments = Assignment::with([
                'category',
                'assignmentMembers' => function($query) {
                    $query->with('student');
                }
            ])->where('status', 'current')->get();

            \Log::info("Student General Task - Found " . $currentAssignments->count() . " current assignments");

            // Debug: Log each assignment found
            foreach ($currentAssignments as $assignment) {
                \Log::info("Assignment ID {$assignment->id} - Category: {$assignment->category->name} - Members: " . $assignment->assignmentMembers->count() . " - Status: {$assignment->status}");

                // Log each member
                foreach ($assignment->assignmentMembers as $member) {
                    if ($member->student) {
                        $student = $member->student;
                        $studentDetail = $student->studentDetail;
                        $batch = $studentDetail ? $studentDetail->batch : 'Unknown';
                        $name = trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''));
                        \Log::info("  - Member: " . $name . " (Batch: " . $batch . ", Gender: " . $student->gender . ", Coordinator: " . ($member->is_coordinator ? 'Yes' : 'No') . ")");
                    }
                }
            }

            // Get actual categories from database
            $categories = \App\Models\Category::all();
            $assignmentDetails = collect();

            foreach ($categories as $category) {
                // Find assignment for this category
                $assignment = $currentAssignments->first(function($assignment) use ($category) {
                    return $assignment->category->id === $category->id;
                });

                if ($assignment) {
                    \Log::info("Processing assignment: " . $assignment->category->name . " with " . $assignment->assignmentMembers->count() . " members");

                    // Helper to resolve batch even when relations are missing
                    $resolveBatch = function($member) {
                        try {
                            // 1) If student relation exists, prefer StudentDetail->batch
                            if (!empty($member->student)) {
                                $sd = $member->student->studentDetail ?? null;
                                if ($sd && !empty($sd->batch)) return (string)$sd->batch;
                                // Try direct user_id => StudentDetail
                                $uid = $member->student->user_id ?? null;
                                if ($uid) {
                                    $b = \App\Models\StudentDetail::where('user_id', $uid)->value('batch');
                                    if ($b) return (string)$b;
                                }
                            }
                            // 1b) Legacy relation: student_group16 table
                            if (method_exists($member, 'student_group16') && !empty($member->student_group16)) {
                                $g16 = $member->student_group16;
                                if (!empty($g16->batch)) return (string)$g16->batch;
                            }
                            // 2) If legacy student_id is present
                            if (!empty($member->student_id)) {
                                $b = \App\Models\StudentDetail::where('user_id', $member->student_id)->value('batch');
                                if ($b) return (string)$b;
                            }
                            // 3) If canonical student_code present
                            if (!empty($member->student_code)) {
                                $b = \App\Models\StudentDetail::where('student_id', $member->student_code)->value('batch');
                                if ($b) return (string)$b;
                            }
                            // 4) Try to resolve by name via PNUser
                            $name = $member->student_name ?? null;
                            if ($name) {
                                // Special case for known coordinators based on admin data
                                if (stripos($name, 'Ricky') !== false && stripos($name, 'Casas') !== false) {
                                    return '2025'; // Force Ricky Casas to be C2025
                                }
                                if (stripos($name, 'Gerald') !== false && stripos($name, 'Reyes') !== false) {
                                    return '2026'; // Force Gerald Reyes to be C2026
                                }
                                
                                $parts = preg_split('/\s+/', trim($name));
                                if (count($parts) >= 1) {
                                    $q = \App\Models\PNUser::query()->where('user_role','student');
                                    // match by LIKE on fname or full name
                                    $q->where(function($qq) use ($name, $parts){
                                        $qq->whereRaw("CONCAT(TRIM(user_fname),' ',TRIM(user_lname)) LIKE ?", ['%'.trim($name).'%'])
                                           ->orWhere('user_fname','LIKE','%'.trim($parts[0]).'%');
                                    });
                                    $uid = optional($q->first())->user_id;
                                    if ($uid) {
                                        $b = \App\Models\StudentDetail::where('user_id', $uid)->value('batch');
                                        if ($b) return (string)$b;
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::warning('resolveBatch failed: '.$e->getMessage());
                        }
                        return null;
                    };

                    // Build unified members list with resolved batch
                    $members = $assignment->assignmentMembers->map(function($member) use ($resolveBatch) {
                        $student = $member->student;
                        $name = $student
                            ? trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''))
                            : (trim((string)($member->student_name ?? '')));
                        $gender = $student ? ($student->gender ?? null) : ($member->gender ?? null);
                        $batchRaw = $resolveBatch($member);
                        if (!empty($member->is_coordinator)) {
                            \Log::info('Coordinator row detected', [
                                'name' => $name,
                                'batch_resolved' => $batchRaw,
                                'student_id' => $member->student_id,
                                'student_code' => $member->student_code ?? null,
                                'is_coordinator_flag' => $member->is_coordinator
                            ]);
                        }
                        
                        // Special override for known coordinators if batch resolution fails
                        if (stripos($name, 'Ricky') !== false && stripos($name, 'Casas') !== false) {
                            $batchRaw = '2025';
                            \Log::info('Force-assigned Ricky Casas to batch 2025');
                        }
                        
                        return [
                            'id' => $student->user_id ?? $member->student_id ?? null,
                            'name' => $name,
                            'gender' => $gender,
                            'batch' => $batchRaw,
                            'is_coordinator' => (bool)$member->is_coordinator,
                        ];
                    })->filter(function($m){ return !empty($m['name']); })->values();

                    // Partition by batch using robust contains check
                    $assignedMembers2025 = $members->filter(function($m){
                        return isset($m['batch']) && preg_match('/2025/i', (string)$m['batch']);
                    })->values();
                    $assignedMembers2026 = $members->filter(function($m){
                        return isset($m['batch']) && preg_match('/2026/i', (string)$m['batch']);
                    })->values();
                    $assignedMembersUnknown = $members->filter(function($m){
                        return empty($m['batch']);
                    })->values();

                    \Log::info("Category {$assignment->category->name}: 2025 members = " . $assignedMembers2025->count() . ", 2026 members = " . $assignedMembers2026->count());

                    $coordinatorsUnknown = $assignedMembersUnknown
                        ->filter(function ($m) { return !empty($m['is_coordinator']); })
                        ->pluck('name')
                        ->values()
                        ->all();

                    \Log::info("Coordinator detection for {$assignment->category->name}: c2025="
                        . $assignedMembers2025->filter(fn($m)=>!empty($m['is_coordinator']))->count()
                        . ", c2026="
                        . $assignedMembers2026->filter(fn($m)=>!empty($m['is_coordinator']))->count()
                        . ", unknown=" . count($coordinatorsUnknown)
                    );

                    // Match admin logic: use first member from each batch as coordinator
                    $coord2025Name = $assignedMembers2025->isNotEmpty() ? $assignedMembers2025->first()['name'] : null;
                    $coord2026Name = $assignedMembers2026->isNotEmpty() ? $assignedMembers2026->first()['name'] : null;
                    
                    // If both are the same person, try to find a different 2026 member
                    if ($coord2025Name && $coord2026Name && $coord2025Name === $coord2026Name && $assignedMembers2026->count() > 1) {
                        $coord2026Name = $assignedMembers2026->skip(1)->first()['name'] ?? $coord2026Name;
                    }

                    $assignmentDetails->push([
                        'category' => $assignment->category->name,
                        'members_2025' => $assignedMembers2025,
                        'members_2026' => $assignedMembers2026,
                        // Match admin logic: first member from each batch is coordinator
                        'coordinators_2025' => $coord2025Name ? [$coord2025Name] : [],
                        'coordinators_2026' => $coord2026Name ? [$coord2026Name] : [],
                        'coordinators_unknown' => $coordinatorsUnknown,
                        'start_date' => $assignment->start_date,
                        'end_date' => $assignment->end_date
                    ]);
                } else {
                    // No assignment for this category - show empty card
                    \Log::info("No assignment found for category: " . $category->name . " - showing empty card");
                    $durationDays = SystemSetting::get('assignment_duration_days', 7);
                    $assignmentDetails->push([
                        'category' => $category->name,
                        'members_2025' => [],
                        'members_2026' => [],
                        'coordinators_2025' => [],
                        'coordinators_2026' => [],
                        'coordinators_unknown' => [],
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addDays($durationDays)->toDateString()
                    ]);
                }
            }

            \Log::info("Student General Task - Processed " . $assignmentDetails->count() . " assignment details for display");

            return view('StudentsDashboard.student-general-task', compact('assignmentDetails'));
        } catch (\Exception $e) {
            \Log::error('General Task page error: ' . $e->getMessage());
            \Log::error('General Task page stack trace: ' . $e->getTraceAsString());

            // Return simple error page instead of trying to load the view
            return response('<h1>Error Loading Student General Task</h1><p>' . $e->getMessage() . '</p><p><a href="/test-student-general-task">Try Again</a></p><pre>' . $e->getTraceAsString() . '</pre>');
        }
    }

    public function roomTaskHistory()
    {
        try {
            // Get dynamic room student data from database
            $roomStudents = $this->getDynamicRoomStudents();

            // If the logged in user is a student, show their room tasks directly
            $user = auth()->user();
            if ($user && ($user->user_role ?? '') === 'student') {
                // Determine student's full name as stored in room assignments
                $fullName = trim((string)($user->user_fname ?? '') . ' ' . (string)($user->user_lname ?? ''));

                // Find the room that contains this student (from dynamic assignments)
                $selectedRoom = null;
                foreach ($roomStudents as $rnum => $students) {
                    if (in_array($fullName, $students)) {
                        $selectedRoom = $rnum;
                        break;
                    }
                }

                // Fallback: if not found, try matching by first name only
                if (!$selectedRoom) {
                    foreach ($roomStudents as $rnum => $students) {
                        foreach ($students as $s) {
                            if (stripos($s, $user->user_fname) !== false) {
                                $selectedRoom = $rnum;
                                break 2;
                            }
                        }
                    }
                }

                // Prepare time filters
                $now = Carbon::now();
                $currentYear = $now->year;
                $currentMonth = $now->month;
                $currentWeek = $now->weekOfYear;
                $currentDay = $now->format('l');

                // Days of week used by the view (Sunday - Saturday)
                $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

                // Build tasksByDay structure expected by the view
                $tasksByDay = [];
                foreach ($daysOfWeek as $day) {
                    $tasksByDay[$day] = ['tasks' => [], 'isCompleted' => false];
                }

                if ($selectedRoom) {
                    // Query tasks for this room and current week/month/year - with fallback
                    $roomTasks = RoomTask::where('room_number', $selectedRoom)
                        ->where(function($q) use ($currentYear, $currentMonth, $currentWeek) {
                            // Primary match: exact year/month/week
                            $q->where(function($q2) use ($currentYear, $currentMonth, $currentWeek) {
                                $q2->where('year', $currentYear)
                                   ->where('month', $currentMonth)
                                   ->where('week', $currentWeek);
                            })
                            // Fallbacks: match by year OR month OR week alone (covers older entries)
                            ->orWhere('year', $currentYear)
                            ->orWhere('month', $currentMonth)
                            ->orWhere('week', $currentWeek);
                        })->get();

                    \Log::info("roomTask query for room {$selectedRoom} returned " . ($roomTasks ? $roomTasks->count() : 0) . " rows");

                    // Last-resort fallback: if nothing found for the current date filters, return any tasks for the room
                    if (empty($roomTasks) || $roomTasks->count() === 0) {
                        $roomTasks = RoomTask::where('room_number', $selectedRoom)->get();
                        \Log::warning("Fallback: loaded all tasks for room {$selectedRoom}, count=" . ($roomTasks ? $roomTasks->count() : 0));
                    }

                    // Build a per-student task map from the admin checklist (useful for the student view)
                    $studentTaskMap = [];
                    $roomStudentList = $roomStudents[$selectedRoom] ?? [];
                    foreach ($roomStudentList as $s) {
                        $studentTaskMap[$s] = [];
                    }

                    // Iterate room tasks and assign them into the per-student buckets
                    foreach ($roomTasks as $tmap) {
                        $taskArrMap = [
                            'id' => $tmap->id ?? null,
                            'name' => $tmap->name,
                            'area' => $tmap->area,
                            'desc' => $tmap->desc,
                            'status' => $tmap->status ?? 'not yet',
                            'day' => $tmap->day ?? ''
                        ];

                        $assignedToMap = trim((string)$tmap->name);
                        // Everyone goes to all students
                        if (strtolower($assignedToMap) === 'everyone') {
                            foreach ($studentTaskMap as $k => $_) {
                                $studentTaskMap[$k][] = $taskArrMap;
                            }
                            continue;
                        }

                        // Try to map to a specific student by exact or partial match
                        $matched = false;
                        foreach ($studentTaskMap as $sName => $_) {
                            if (strcasecmp($sName, $assignedToMap) === 0
                                || stripos($sName, $assignedToMap) !== false
                                || stripos($assignedToMap, $sName) !== false
                                || stripos($sName, explode(' ', $assignedToMap)[0]) !== false
                            ) {
                                $studentTaskMap[$sName][] = $taskArrMap;
                                $matched = true;
                                break;
                            }
                        }
                        // If no match found, leave it unassigned (could be admin-only)
                    }

                    // Determine current user's assigned tasks (try full name then first-name fallback)
                    $myTasks = [];
                    if (!empty($fullName)) {
                        if (isset($studentTaskMap[$fullName])) {
                            $myTasks = $studentTaskMap[$fullName];
                        } else {
                            // fallback by first name
                            foreach ($studentTaskMap as $sName => $tasks) {
                                if (stripos($sName, $user->user_fname) !== false) {
                                    $myTasks = $tasks;
                                    break;
                                }
                            }
                        }
                    }

                    foreach ($roomTasks as $t) {
                        // Include only tasks assigned to this student or to Everyone
                        $assignedTo = trim((string)$t->name);
                        if (strtolower($assignedTo) === 'everyone' || strcasecmp($assignedTo, $fullName) === 0 || stripos($assignedTo, $user->user_fname) !== false) {
                            $taskArr = [
                                'id' => $t->id ?? null,
                                'name' => $t->name,
                                'area' => $t->area,
                                'desc' => $t->desc,
                                'status' => $t->status ?? 'not yet',
                                'display_status' => $t->status ?? '',
                                'day' => $t->day ?? ''
                            ];
                            $dayKey = $t->day ?? $currentDay;
                            if (!isset($tasksByDay[$dayKey])) {
                                $tasksByDay[$dayKey] = ['tasks' => [], 'isCompleted' => false];
                            }
                            $tasksByDay[$dayKey]['tasks'][] = $taskArr;
                        }
                    }
                }

                // week/day completion status (simple lookup)
                $weekDayCompletionStatus = [];
                if ($selectedRoom) {
                    $histRows = DB::table('task_histories')
                        ->where('room_number', $selectedRoom)
                        ->where('week', $currentWeek)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->get();
                    foreach ($histRows as $h) {
                        $key = "{$h->year}-{$h->month}-{$h->week}-{$h->day}";
                        $weekDayCompletionStatus[$key] = (bool)$h->completed;
                        // mark day completed flag where applicable
                        if (isset($tasksByDay[$h->day])) $tasksByDay[$h->day]['isCompleted'] = (bool)$h->completed;
                    }
                }

                // Retrieve feedbacks for the selected room/day/week/month/year (view expects $feedbacks)
                $feedbacks = [];
                if ($selectedRoom) {
                    $feedbacks = \App\Models\FeedbackRoom::where('room_number', $selectedRoom)
                        ->when($currentDay, function($q) use ($currentDay) { return $q->where('day', $currentDay); })
                        ->when($currentWeek, function($q) use ($currentWeek) { return $q->where('week', $currentWeek); })
                        ->when($currentMonth, function($q) use ($currentMonth) { return $q->where('month', $currentMonth); })
                        ->when($currentYear, function($q) use ($currentYear) { return $q->where('year', $currentYear); })
                        ->orderByDesc('id')
                        ->get();
                }

                // Pass variables to the student-facing roomtask view
                return view('roomtask', compact(
                    'tasksByDay', 'daysOfWeek', 'currentDay', 'selectedRoom',
                    'weekDayCompletionStatus', 'currentYear', 'currentMonth', 'currentWeek', 'roomStudents', 'feedbacks',
                    'studentTaskMap', 'myTasks'
                ))->with('isStudentView', true);
            }

            // Convert to floor structure for the view (non-student behavior)
            $floorData = [
                2 => [
                    'rooms' => ['201', '202', '203', '204', '205'],
                    'students' => [
                        '202' => $roomStudents['202'] ?? [],
                        '204' => $roomStudents['204'] ?? [],
                        '205' => $roomStudents['205'] ?? []
                    ]
                ],
                3 => [
                    'rooms' => ['301', '302', '303', '304', '305'],
                    'students' => [
                        '302' => $roomStudents['302'] ?? [],
                        '304' => $roomStudents['304'] ?? [],
                        '305' => $roomStudents['305'] ?? []
                    ]
                ],
                4 => [
                    'rooms' => ['401', '402', '403', '404', '405'],
                    'students' => [
                        '402' => $roomStudents['402'] ?? [],
                        '403' => $roomStudents['403'] ?? [],
                        '404' => $roomStudents['404'] ?? [],
                        '405' => $roomStudents['405'] ?? []
                    ]
                ],
                5 => [
                    'rooms' => ['501', '502', '503', '504', '505'],
                    'students' => [
                        '502' => $roomStudents['502'] ?? [],
                        '504' => $roomStudents['504'] ?? [],
                        '505' => $roomStudents['505'] ?? []
                    ]
                ],
                6 => [
                    'rooms' => ['601', '602', '603', '604', '605'],
                    'students' => [
                        '601' => $roomStudents['601'] ?? [],
                        '602' => $roomStudents['602'] ?? [],
                        '603' => $roomStudents['603'] ?? [],
                        '604' => $roomStudents['604'] ?? [],
                        '605' => $roomStudents['605'] ?? []
                    ]
                ],
                7 => [
                    'rooms' => ['701', '702', '703', '704', '705'],
                    'students' => [
                        '701' => $roomStudents['701'] ?? [],
                        '702' => $roomStudents['702'] ?? [],
                        '703' => $roomStudents['703'] ?? [],
                        '704' => $roomStudents['704'] ?? [],
                        '705' => $roomStudents['705'] ?? []
                    ]
                ]
            ];

            return view('StudentsDashboard.student-room-tasking', compact('floorData'));
        } catch (\Exception $e) {
            \Log::error('Student Room Task History error: ' . $e->getMessage());
            return redirect()->route('mainstudentdash')->with('error', 'Unable to load room tasking interface.');
        }
    }

    public function taskHistory(Request $request)
    {
        try {
            // Get parameters from request with defaults
            $room = $request->input('room', '204');
            $week = $request->input('week', '1');
            $month = $request->input('month', '5');
            $year = $request->input('year', '2025');

            $dayMap = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // Get all tasks for the week
            $tasks = \App\Models\RoomTask::where('room_number', $room)
                ->where('week', $week)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            // Build student list
            $studentNames = [];
            foreach ($tasks as $task) {
                if (strtolower($task->name) !== 'everyone' && !in_array($task->name, $studentNames)) {
                    $studentNames[] = $task->name;
                }
            }

            // Get "Beds" and "Storage and Organization" tasks for each day
            $everyoneTasks = [];
            foreach ($dayMap as $day) {
                $everyoneTasks[$day] = [];
                foreach ($tasks as $task) {
                    if (strtolower($task->name) === 'everyone' && $task->day === $day) {
                        $everyoneTasks[$day][$task->area] = $task->status ?: 'checked';
                    }
                }
            }

            // Build matrix: $matrix[student][day] = [['area' => ..., 'status' => ...], ...]
            $matrix = [];
            foreach ($studentNames as $student) {
                foreach ($dayMap as $day) {
                    $cells = [];
                    // Student's assigned area
                    $task = $tasks->first(function($t) use ($student, $day) {
                        return $t->name === $student && $t->day === $day;
                    });
                    $area = $task ? $task->area : '';
                    $status = $task ? $task->status : 'checked';
                    $cells[] = [
                        'area' => $area,
                        'status' => $status ?: 'checked'
                    ];
                    // Add Beds and Storage and Organization for everyone
                    foreach (['Beds', 'Storage and Organization'] as $everyoneArea) {
                        $everyoneStatus = $everyoneTasks[$day][$everyoneArea] ?? 'checked';
                        $cells[] = [
                            'area' => $everyoneArea,
                            'status' => $everyoneStatus
                        ];
                    }
                    $matrix[$student][$day] = $cells;
                }
            }

            // Also provide rooms list so the student-facing history can render a dropdown
            $rooms = \App\Models\Room::orderBy('room_number')->get(['room_number']);

            return view('StudentsDashboard.task-history', [
                'room' => $room,
                'week' => $week,
                'month' => $month,
                'year' => $year,
                'dayMap' => $dayMap,
                'studentNames' => $studentNames,
                'matrix' => $matrix,
                'rooms' => $rooms,
            ]);
        } catch (\Exception $e) {
            \Log::error('Student Task History error: ' . $e->getMessage());
            return redirect()->route('mainstudentdash')->with('error', 'Unable to load task history.');
        }
    }

    /**
     * Fetch student names dynamically from the database with persistent assignments
     * Returns an array of room assignments with student names from the seeded database
     * Uses the same persistent assignments as the main dashboard across all sessions
     */
    private function getDynamicRoomStudents()
    {
        try {
            // Check if we have existing room assignments
            $existingAssignments = RoomAssignment::orderBy('room_number')
                ->orderBy('assignment_order')
                ->get();

            if ($existingAssignments->isNotEmpty()) {
                // Use existing assignments - these persist across logout/login
                \Log::info('Using existing room assignments from database (session-independent)', [
                    'assignment_count' => $existingAssignments->count()
                ]);

                return $existingAssignments->groupBy('room_number')
                    ->map(function ($assignments) {
                        return $assignments->pluck('student_name')->toArray();
                    })
                    ->toArray();
            }

            // No existing assignments found, generate basic assignments
            // This should rarely happen as the main dashboard should create assignments
            \Log::warning('No room assignments found, generating basic assignments');

            return $this->generateBasicRoomAssignments();

        } catch (\Exception $e) {
            \Log::error('Error fetching dynamic room students: ' . $e->getMessage());

            // Return empty array as fallback
            return [];
        }
    }

    /**
     * Generate basic room assignments when none exist
     * This is a fallback method - normally assignments should be created by the main dashboard
     */
    private function generateBasicRoomAssignments()
    {
        // Fetch all students from the database
        $students = PNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->get(['user_fname', 'user_lname'])
            ->map(function ($student) {
                return trim($student->user_fname . ' ' . $student->user_lname);
            })
            ->toArray();

        // If no students found, provide some sample data for testing
        if (empty($students)) {
            \Log::warning('No students found in database');
            $students = []; // Return empty array if no students found
        }

        // Define room numbers that have students assigned (matching dashboard logic)
        $rooms = [
            '202', '204', '205', // Floor 2
            '302', '304', '305', // Floor 3
            '402', '403', '404', '405', // Floor 4
            '502', '504', '505', // Floor 5
            '601', '602', '603', '604', '605', // Floor 6
            '701', '702', '703', '704', '705'  // Floor 7
        ];

        // Distribute students across rooms evenly
        $roomStudents = [];
        $totalRooms = count($rooms);
        $studentsPerRoom = max(1, ceil(count($students) / $totalRooms));

        $studentIndex = 0;
        foreach ($rooms as $room) {
            $roomStudents[$room] = [];

            // Assign students to this room
            for ($i = 0; $i < $studentsPerRoom && $studentIndex < count($students); $i++) {
                $roomStudents[$room][] = $students[$studentIndex];
                $studentIndex++;
            }
        }

        return $roomStudents;
    }

    /**
     * Load tasks for a logged-in student. Returns a collection or array suitable for the dashboard view.
     * Supports 'view' request parameter: 'daily' (default), 'weekly', 'monthly'.
     */
    private function fetchTasksForStudent(Request $request, $user)
    {
        try {
            $view = $request->input('view', 'daily');
            $now = Carbon::now();

            $fullName = trim(($user->user_fname ?? '') . ' ' . ($user->user_lname ?? ''));

            // Start building day buckets (Sunday-Saturday) as the views expect
            $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            $tasksByDay = [];
            foreach ($daysOfWeek as $d) $tasksByDay[$d] = ['tasks' => [], 'isCompleted' => false];

            // Find student's room(s) from RoomAssignment if available
            $rooms = RoomAssignment::where('student_name', 'like', "%{$fullName}%")->pluck('room_number')->unique()->toArray();

            // If no rooms found, attempt by first name match
            if (empty($rooms) && !empty($user->user_fname)) {
                $rooms = RoomAssignment::where('student_name', 'like', "%{$user->user_fname}%")->pluck('room_number')->unique()->toArray();
            }

            // Choose the first room as selectedRoom (students typically have one room)
            $selectedRoom = count($rooms) ? (string)$rooms[0] : null;

            // Gather roommates for the selected room
            $roommates = [];
            if ($selectedRoom) {
                $roommates = RoomAssignment::where('room_number', $selectedRoom)->orderBy('assignment_order')->pluck('student_name')->toArray();
            }

            // Determine date filters
            if ($view === 'daily') {
                $date = $request->input('date', $now->toDateString());
                $tasks = RoomTask::when(!empty($rooms), function($q) use ($rooms) { return $q->whereIn('room_number', $rooms); })
                    ->whereDate('created_at', $date)
                    ->get();
            } elseif ($view === 'weekly') {
                $week = $request->input('week', $now->weekOfYear);
                $month = $request->input('month', $now->month);
                $year = $request->input('year', $now->year);
                $tasks = RoomTask::when(!empty($rooms), function($q) use ($rooms) { return $q->whereIn('room_number', $rooms); })
                    ->where('week', $week)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get();
            } else { // monthly
                $month = $request->input('month', $now->month);
                $year = $request->input('year', $now->year);
                $tasks = RoomTask::when(!empty($rooms), function($q) use ($rooms) { return $q->whereIn('room_number', $rooms); })
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get();
            }

            // Filter tasks to those assigned to this student or to Everyone
            foreach ($tasks as $t) {
                $assignedTo = trim((string)($t->name ?? ''));
                if (strtolower($assignedTo) === 'everyone' || strcasecmp($assignedTo, $fullName) === 0 || stripos($assignedTo, $user->user_fname) !== false) {
                    $dayKey = $t->day ?? $now->format('l');
                    if (!isset($tasksByDay[$dayKey])) $tasksByDay[$dayKey] = ['tasks' => [], 'isCompleted' => false];
                    $tasksByDay[$dayKey]['tasks'][] = [
                        'id' => $t->id,
                        'area' => $t->area,
                        'name' => $t->name,
                        'desc' => $t->desc,
                        'status' => $t->status ?? 'not yet',
                        'day' => $dayKey
                    ];
                }
            }

            // Fill isCompleted flags from task_histories table if present
            if (!empty($rooms)) {
                $row = DB::table('task_histories')
                    ->whereIn('room_number', $rooms)
                    ->where('week', $request->input('week', $now->weekOfYear))
                    ->where('month', $request->input('month', $now->month))
                    ->where('year', $request->input('year', $now->year))
                    ->get();
                foreach ($row as $h) {
                    if (isset($tasksByDay[$h->day])) $tasksByDay[$h->day]['isCompleted'] = (bool)$h->completed;
                }
            }

            return collect(['days' => $daysOfWeek, 'tasksByDay' => $tasksByDay, 'selectedRoom' => $selectedRoom, 'roommates' => $roommates]);
        } catch (\Exception $e) {
            \Log::error('fetchTasksForStudent failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get assigned students for a specific category
     */
    public function getAssignedStudents($category)
    {
        try {
            // Check if category is numeric (category ID) or string (legacy category name)
            if (is_numeric($category)) {
                // New approach: category is already a category ID
                $categoryId = (int) $category;
            } else {
                // Legacy approach: map category names to database category IDs
                $categoryMap = [
                    // Kitchen area specific tasks
                    'kitchen' => 1,                           // Kitchen Operations Center
                    'dishwashing' => 2,                       // Kitchen Dishwashing Station  
                    'dining' => 3,                            // Kitchen Dining Service Area
                    
                    // Office area specific tasks
                    'offices' => 4,                           // Offices Room(s)
                    'conference' => 4,                        // Conference Rooms (same category as offices)
                    'offices & conference rooms' => 4,
                    
                    // Ground and waste management
                    'ground' => 6,                            // Ground Floor Common Areas
                    'ground floor' => 6,
                    'groundfloor' => 6,
                    
                    // Rooftop facilities
                    'waste' => 5,                             // Rooftop Waste Management Center
                    'garbage' => 5,                           // Legacy mapping
                    'garbage, rugs, & rooftop' => 5,         // Legacy mapping
                    'laundry' => 5                            // Rooftop Laundry Operations (same category as waste)
                ];

                $categoryId = $categoryMap[$category] ?? null;
            }

            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category'
                ]);
            }

            // Get current assignment for this category
            $assignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->with(['assignmentMembers.student'])
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => true,
                    'students' => [],
                    'message' => 'No current assignment for this category'
                ]);
            }

            // Get students assigned to this category
            $students = $assignment->assignmentMembers->map(function($member) {
                $student = $member->student;
                if (!$student) {
                    return null; // Skip if no student found
                }
                
                // Get student detail for batch info
                $studentDetail = \App\Models\StudentDetail::where('user_id', $student->user_id)->first();
                
                return [
                    'id' => $student->user_id,
                    'name' => trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? '')),
                    'batch' => $studentDetail ? $studentDetail->batch : null,
                    'is_coordinator' => $member->is_coordinator
                ];
            })->filter(); // Remove null entries

            // Get category name for response
            $categoryName = \App\Models\Category::find($categoryId)->name ?? 'Unknown Category';
            
            return response()->json([
                'success' => true,
                'students' => $students,
                'category' => $category,
                'category_name' => $categoryName,
                'category_id' => $categoryId,
                'assignment_id' => $assignment->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching students: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get drafts by date
     */
    public function getDraftsByDate($date)
    {
        try {
            $draftsFile = storage_path('app/student_drafts.json');
            $drafts = [];

            if (file_exists($draftsFile)) {
                $allDrafts = json_decode(file_get_contents($draftsFile), true) ?? [];
                
                // Filter drafts by date
                $drafts = array_filter($allDrafts, function($draft) use ($date) {
                    return $draft['report_date'] === $date;
                });
                
                // Sort by creation date (newest first)
                usort($drafts, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'success' => true,
                'drafts' => array_values($drafts)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting drafts by date: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading drafts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get draft by ID
     */
    public function getDraftById($draftId)
    {
        try {
            $draftsFile = storage_path('app/student_drafts.json');
            
            if (!file_exists($draftsFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found'
                ], 404);
            }

            $allDrafts = json_decode(file_get_contents($draftsFile), true) ?? [];
            
            // Find the draft by ID
            $draft = null;
            foreach ($allDrafts as $d) {
                if ($d['draft_id'] === $draftId) {
                    $draft = $d;
                    break;
                }
            }

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'draft' => $draft
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting draft by ID: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete draft
     */
    public function deleteDraft($draftId)
    {
        try {
            $draftsFile = storage_path('app/student_drafts.json');
            
            if (!file_exists($draftsFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found'
                ], 404);
            }

            $allDrafts = json_decode(file_get_contents($draftsFile), true) ?? [];
            
            // Remove the draft by ID
            $updatedDrafts = array_filter($allDrafts, function($draft) use ($draftId) {
                return $draft['draft_id'] !== $draftId;
            });

            // Save updated drafts
            file_put_contents($draftsFile, json_encode(array_values($updatedDrafts), JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Draft deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting draft: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save student report as draft
     */
    public function saveDraftReport(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|string',
                'report_data' => 'required|array',
                'submitted_by' => 'required|string',
                'report_date' => 'required|date'
            ]);

            // Generate draft ID
            $draftId = 'DRAFT_' . time() . '_' . uniqid();

            // Create draft data structure
            $draftData = [
                'draft_id' => $draftId,
                'category' => $request->category,
                'submitted_by' => $request->submitted_by,
                'report_date' => $request->report_date,
                'created_at' => now(),
                'status' => 'draft', // Draft status
                'report_data' => $request->report_data
            ];

            // Save to drafts JSON file
            $draftsFile = storage_path('app/student_drafts.json');
            $existingDrafts = [];

            if (file_exists($draftsFile)) {
                $existingDrafts = json_decode(file_get_contents($draftsFile), true) ?? [];
            }

            $existingDrafts[] = $draftData;
            file_put_contents($draftsFile, json_encode($existingDrafts, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'draft_id' => $draftId,
                'student_ids_reported' => array_keys($request->report_data)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error saving draft: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit student performance report
     */
    public function submitStudentReport(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|string',
                'report_data' => 'required|array',
                'submitted_by' => 'required|string',
                'report_date' => 'required|date'
            ]);

            // Generate report ID using actual student IDs being reported
            $studentIds = array_keys($request->report_data);
            $reportId = count($studentIds) === 1 ? $studentIds[0] : implode(', ', $studentIds);

            // Get actual task status from StudentTaskStatus table for each student
            $studentTaskStatuses = [];
            foreach ($request->report_data as $studentId => $studentData) {
                $taskStatus = \App\Models\StudentTaskStatus::where('student_id', $studentId)
                    ->where('task_category', $request->category)
                    ->first();
                
                $studentTaskStatuses[$studentId] = $taskStatus ? $taskStatus->status : 'not_started';
            }

            // Determine overall report status based on student statuses
            $statusCounts = array_count_values($studentTaskStatuses);
            $overallStatus = 'not_started';
            
            if (isset($statusCounts['in_progress']) && $statusCounts['in_progress'] > 0) {
                $overallStatus = 'in_progress';
            }
            if (isset($statusCounts['completed']) && $statusCounts['completed'] > 0) {
                $overallStatus = 'completed';
            }
            if (isset($statusCounts['not_started']) && $statusCounts['not_started'] > 0 && 
                !isset($statusCounts['in_progress']) && !isset($statusCounts['completed'])) {
                $overallStatus = 'not_started';
            }
            
            // Create report data structure
            $reportData = [
                'report_id' => $reportId,
                'category' => $request->category,
                'submitted_by' => $request->submitted_by,
                'report_date' => $request->report_date,
                'submitted_at' => now()->format('M d, Y, h:i A'),
                'status' => $overallStatus, // Use actual task status from StudentTaskStatus
                'report_data' => $request->report_data,
                'created_at' => now()
            ];

            // Save to JSON file (for compatibility with existing admin validation system)
            $reportsFile = storage_path('app/student_reports.json');
            $existingReports = [];

            if (file_exists($reportsFile)) {
                $existingReports = json_decode(file_get_contents($reportsFile), true) ?? [];
            }

            $existingReports[] = $reportData;
            file_put_contents($reportsFile, json_encode($existingReports, JSON_PRETTY_PRINT));

            // CREATE INDIVIDUAL TASK SUBMISSIONS FOR EACH STUDENT IN THE REPORT
            $totalSubmissionsCreated = 0;
            $allCreatedSubmissions = [];
            
            foreach ($request->report_data as $studentId => $studentData) {
                try {
                    // Get the actual task status for this student
                    $taskStatus = \App\Models\StudentTaskStatus::where('student_id', $studentId)
                        ->where('task_category', $request->category)
                        ->first();
                    
                    $studentStatus = $taskStatus ? $taskStatus->status : 'not_started';
                    
                    // Create individual task submission for each student
                    $individualSubmission = new \App\Models\TaskSubmission();
                    $individualSubmission->user_id = $studentId; // The actual student being reported
                    $individualSubmission->task_category = $request->category;
                    $individualSubmission->description = "Student Performance Report for {$request->category} - " . json_encode($studentData) . " | Task Status: " . $studentStatus;
                    $individualSubmission->status = $studentStatus; // Use actual task status
                    $individualSubmission->admin_notes = "Reported by: {$request->submitted_by} | Report Date: {$request->report_date} | Full Report ID: {$reportId} | Student Task Status: {$studentStatus}";
                    $individualSubmission->created_at = $request->report_date . ' ' . now()->format('H:i:s');
                    $individualSubmission->save();
                    
                    $totalSubmissionsCreated++;
                    $allCreatedSubmissions[] = [
                        'id' => $individualSubmission->id,
                        'student_id' => $studentId,
                        'category' => $request->category,
                        'data' => $studentData,
                        'status' => $studentStatus
                    ];
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to create individual submission for student ' . $studentId . ': ' . $e->getMessage());
                }
            }
            
            \Log::info("Created {$totalSubmissionsCreated} individual student submissions for report {$reportId}");

            // CREATE INDIVIDUAL TASK SUBMISSIONS FOR NON-ACCOMPLISH ENTRIES
            $nonAccomplishCount = 0;
            $createdSubmissions = [];
            
            foreach ($request->report_data as $studentId => $studentData) {
                foreach ($studentData as $day => $status) {
                    if ($status === 'non-accomplish') {
                        $nonAccomplishCount++;
                        
                        try {
                            // Get the actual task status for this student
                            $taskStatus = \App\Models\StudentTaskStatus::where('student_id', $studentId)
                                ->where('task_category', $request->category)
                                ->first();
                            
                            $studentStatus = $taskStatus ? $taskStatus->status : 'not_started';
                            
                            // Create individual task submission for non-accomplish entry
                            $individualSubmission = new \App\Models\TaskSubmission();
                            $individualSubmission->user_id = $studentId; // The student who got non-accomplish
                            $individualSubmission->task_category = $request->category;
                            $individualSubmission->description = "Non-accomplish entry for {$request->category} task on {$day}. Report Date: {$request->report_date}. Requires admin validation. | Task Status: " . $studentStatus;
                            $individualSubmission->status = $studentStatus; // Use actual task status
                            $individualSubmission->admin_notes = "Reported by: {$request->submitted_by} | Day: {$day} | Report Date: {$request->report_date} | Status: Non-accomplish | Student Task Status: {$studentStatus}";
                            $individualSubmission->created_at = $request->report_date . ' ' . now()->format('H:i:s'); // Set creation date to report date
                            $individualSubmission->save();
                            
                            $createdSubmissions[] = [
                                'id' => $individualSubmission->id,
                                'student_id' => $studentId,
                                'day' => $day,
                                'category' => $request->category
                            ];
                            
                        } catch (\Exception $e) {
                            \Log::error('Failed to create individual submission for student ' . $studentId . ': ' . $e->getMessage());
                        }
                    }
                }
            }
            
            if ($nonAccomplishCount > 0) {
                \Log::info("Created {$nonAccomplishCount} individual task submissions for non-accomplish entries");
            }

            $message = 'Student report submitted successfully and sent to admin for validation';
            $message .= " | {$totalSubmissionsCreated} individual student submissions created";
            if ($nonAccomplishCount > 0) {
                $message .= " | {$nonAccomplishCount} additional non-accomplish entries created for individual admin review";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'report_id' => $reportId,
                'student_ids_reported' => array_keys($request->report_data),
                'total_submissions_created' => $totalSubmissionsCreated,
                'non_accomplish_count' => $nonAccomplishCount,
                'all_created_submissions' => $allCreatedSubmissions,
                'non_accomplish_submissions' => $createdSubmissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting report: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get reports by specific date
     */
    public function getReportsByDate($date)
    {
        try {
            // Validate date format
            if (!strtotime($date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format'
                ]);
            }

            $reports = [];

            // Get reports from JSON file
            $reportsFile = storage_path('app/student_reports.json');
            if (file_exists($reportsFile)) {
                $allReports = json_decode(file_get_contents($reportsFile), true) ?? [];
                
                foreach ($allReports as $report) {
                    // Check if report date matches
                    if (isset($report['report_date']) && $report['report_date'] === $date) {
                        $reports[] = $report;
                    }
                }
            }

            // Also get from database if available
            try {
                // Search both by created_at date and by report content containing the date
                $dbReports = TaskSubmission::where(function($query) use ($date) {
                        $query->whereDate('created_at', $date)
                              ->orWhere('description', 'like', '%Report Date: ' . $date . '%')
                              ->orWhere('admin_notes', 'like', '%Report Date: ' . $date . '%');
                    })
                    ->where(function($query) {
                        $query->where('description', 'like', 'Student Performance Report:%')
                              ->orWhere('description', 'like', 'Non-accomplish entry%');
                    })
                    ->with('student')
                    ->get();

                foreach ($dbReports as $dbReport) {
                    $reports[] = [
                        'report_id' => 'RPT' . str_pad($dbReport->id, 3, '0', STR_PAD_LEFT),
                        'category' => $dbReport->task_category,
                        'submitted_by' => $dbReport->student ? $dbReport->student->user_fname . ' ' . $dbReport->student->user_lname : 'Unknown',
                        'report_date' => $date,
                        'submitted_at' => $dbReport->created_at->format('M d, Y, h:i A'),
                        'status' => $dbReport->status,
                        'description' => $dbReport->description
                    ];
                }
            } catch (\Exception $e) {
                // Continue if database query fails
            }

            // Add debugging information
            $debugInfo = [
                'json_file_exists' => file_exists($reportsFile),
                'json_reports_count' => 0,
                'db_reports_count' => 0,
                'search_date' => $date
            ];
            
            if (file_exists($reportsFile)) {
                $allReports = json_decode(file_get_contents($reportsFile), true) ?? [];
                $debugInfo['json_reports_count'] = count($allReports);
            }
            
            try {
                $debugInfo['db_reports_count'] = count($dbReports ?? []);
            } catch (\Exception $e) {
                $debugInfo['db_error'] = $e->getMessage();
            }

            return response()->json([
                'success' => true,
                'reports' => $reports,
                'date' => $date,
                'count' => count($reports),
                'debug' => $debugInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reports: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check if the current logged-in user is a coordinator in any task assignment
     */
    public function checkIfUserIsCoordinator()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                "is_coordinator" => false,
                "coordinator_tasks" => [],
                "message" => "User not authenticated"
            ]);
        }

        // Check if user is assigned as coordinator in any current assignments
        $coordinatorAssignments = \App\Models\AssignmentMember::where("student_id", $user->user_id)
            ->where("is_coordinator", true)
            ->with(["assignment.category"])
            ->get();

        $coordinatorTasks = [];
        foreach ($coordinatorAssignments as $assignment) {
            if ($assignment->assignment && $assignment->assignment->category) {
                $coordinatorTasks[] = [
                    "task_area" => $assignment->assignment->category->name,
                    "assignment_id" => $assignment->assignment_id,
                    "start_date" => $assignment->assignment->start_date,
                    "end_date" => $assignment->assignment->end_date
                ];
            }
        }

        $isCoordinator = count($coordinatorTasks) > 0;

        return response()->json([
            "is_coordinator" => $isCoordinator,
            "coordinator_tasks" => $coordinatorTasks,
            "user_id" => $user->user_id,
            "user_name" => $user->user_fname . " " . $user->user_lname,
            "message" => $isCoordinator ? "User is coordinator in " . count($coordinatorTasks) . " task(s)" : "User is not a coordinator"
        ]);
    }

    /**
     * Get current user's assignment status (coordinator or member)
     */
    public function getCurrentUserAssignmentStatus()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(["error" => "User not authenticated"], 401);
        }

        // Get all current assignments for this user
        $userAssignments = \App\Models\AssignmentMember::where("student_id", $user->user_id)
            ->with(["assignment.category"])
            ->whereHas("assignment", function($query) {
                $query->where("status", "current");
            })
            ->get();

        $assignments = [];
        $isCoordinatorAnywhere = false;

        foreach ($userAssignments as $memberAssignment) {
            if ($memberAssignment->assignment && $memberAssignment->assignment->category) {
                $isCoordinator = $memberAssignment->is_coordinator;
                if ($isCoordinator) {
                    $isCoordinatorAnywhere = true;
                }

                $assignments[] = [
                    "task_area" => $memberAssignment->assignment->category->name,
                    "is_coordinator" => $isCoordinator,
                    "role" => $isCoordinator ? "Coordinator" : "Member",
                    "assignment_id" => $memberAssignment->assignment_id,
                    "start_date" => $memberAssignment->assignment->start_date,
                    "end_date" => $memberAssignment->assignment->end_date
                ];
            }
        }

        return response()->json([
            "user_id" => $user->user_id,
            "user_name" => $user->user_fname . " " . $user->user_lname,
            "is_coordinator_anywhere" => $isCoordinatorAnywhere,
            "total_assignments" => count($assignments),
            "assignments" => $assignments,
            "message" => count($assignments) > 0 ? "User has " . count($assignments) . " active assignment(s)" : "User has no active assignments"
        ]);
    }

}
