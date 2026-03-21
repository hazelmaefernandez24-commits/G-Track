<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\TaskHistoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GeneralTaskController;
use App\Http\Controllers\GeneralTaskInspectionController;
use App\Http\Controllers\Student16Controller;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudentTaskController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\MainStudentDASHController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\RoomManagementController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', [AuthController::class, 'dashboard']);
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/login', function () {
    return redirect()->to(env('MAIN_SYSTEM_URL'). '/');
})->name('login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');
// Fallback: handle accidental GET /logout links
Route::get('/logout', function (Request $request) {
    try {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    } catch (\Throwable $e) {
        // ignore
    }
    return redirect()->route('dashboard');
});

// Admin routes (educator/inspector only)
Route::middleware(['auth'])->group(function () {
    // Admin Tasking Report (separate from Damage Reports)
    Route::get('/admin-tasking-report', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Only admins can access Tasking Report.');
        }
        return view('admin.submitted-task-validation');
    })->name('admin.tasking.report');

    // Admin Submitted Task Validation (admin-only page)
    Route::get('/admin-submitted-task-validation', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Only admins can access Task Validation.');
        }
        return view('admin.submitted-task-validation');
    })->name('admin.submitted.task.validation');

    // Task Assignments Overview Route
    Route::get('/task-assignments-overview', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Only admins can access Task Assignments Overview.');
        }
        return view('admin.task-assignments-overview');
    })->name('admin.task.assignments.overview');

    // API route for current assignments (for student side to get latest shuffle results)
    Route::get('/api/current-assignments', function() {
        try {
            // Get current assignments with members
            $assignments = \App\Models\Assignment::with(['category', 'assignmentMembers.student'])
                ->whereIn('status', ['current', 'active'])
                ->orderBy('start_date', 'desc')
                ->get();
                
            // If no current/active assignments, get latest
            if ($assignments->isEmpty()) {
                $assignments = \App\Models\Assignment::with(['category', 'assignmentMembers.student'])
                    ->orderBy('id', 'desc')
                    ->get();
            }
            
            $assignmentDetails = [];
            
            foreach ($assignments as $assignment) {
                $categoryName = $assignment->category->category_name;
                
                // Map category names
                $mappedCategoryName = $categoryName;
                $catLower = strtolower($categoryName);
                
                if (str_contains($catLower, 'kitchen') && !str_contains($catLower, 'dishwashing') && !str_contains($catLower, 'dining')) {
                    $mappedCategoryName = 'Kitchen Operations Center';
                } elseif (str_contains($catLower, 'dishwashing')) {
                    $mappedCategoryName = 'Kitchen Dishwashing Station';
                } elseif (str_contains($catLower, 'dining')) {
                    $mappedCategoryName = 'Kitchen Dining Area';
                } elseif (str_contains($catLower, 'office')) {
                    $mappedCategoryName = 'Dorm Office Area';
                } elseif (str_contains($catLower, 'conference')) {
                    $mappedCategoryName = 'Conference Rooms';
                } elseif (str_contains($catLower, 'ground')) {
                    $mappedCategoryName = 'Ground Level Operations';
                } elseif (str_contains($catLower, 'waste') || str_contains($catLower, 'rooftop')) {
                    $mappedCategoryName = 'Rooftop Waste Management';
                }
                
                $members2025 = [];
                $members2026 = [];
                
                // Separate coordinators and regular members
                $coordinators2025 = [];
                $coordinators2026 = [];
                
                foreach ($assignment->assignmentMembers as $member) {
                    if ($member->student) {
                        $studentDetail = \App\Models\StudentDetail::where('user_id', $member->student->user_id)->first();
                        $batch = $studentDetail ? $studentDetail->batch : null;
                        
                        $studentName = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                        $isCoordinator = $member->is_coordinator ?? false;
                        
                        if ($batch == 2025) {
                            $members2025[] = $studentName;
                            if ($isCoordinator) {
                                $coordinators2025[] = $studentName;
                            }
                        } elseif ($batch == 2026) {
                            $members2026[] = $studentName;
                            if ($isCoordinator) {
                                $coordinators2026[] = $studentName;
                            }
                        } else {
                            $members2026[] = $studentName; // Default to 2026
                            if ($isCoordinator) {
                                $coordinators2026[] = $studentName;
                            }
                        }
                    }
                }
                
                $assignmentDetails[$mappedCategoryName] = [
                    'category' => $mappedCategoryName,
                    'members_2025' => $members2025,
                    'members_2026' => $members2026,
                    'coordinators_2025' => $coordinators2025,
                    'coordinators_2026' => $coordinators2026,
                    'total_members' => count($members2025) + count($members2026),
                    'total_coordinators' => count($coordinators2025) + count($coordinators2026)
                ];
            }
            
            return response()->json($assignmentDetails);
            
        } catch (\Exception $e) {
            \Log::error('Error in current-assignments API: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch assignments'], 500);
        }
    });

    // Proxy attendance endpoint that reads Login DB tables if the central attendance API is not available
    Route::get('/api/attendance/proxy/{studentId}', [\App\Http\Controllers\AttendanceProxyController::class, 'studentAttendance'])->name('api.attendance.proxy');

    // API route for student's assigned tasks
    Route::get('/api/student/my-tasks', function() {
        try {
            $user = auth()->user();
            $day = request('day', 'wednesday');
            $date = request('date', date('Y-m-d'));
            
            // Helper function to get team members for an assignment
            $getTeamMembers = function($assignmentId) {
                try {
                    $members = \App\Models\AssignmentMember::where('assignment_id', $assignmentId)
                        ->with('student')
                        ->get();
                    
                    $teamNames = [];
                    foreach ($members as $member) {
                        if ($member->student) {
                            $name = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                            if ($name) {
                                $teamNames[] = $name;
                            }
                        }
                    }
                    
                    return count($teamNames) > 1 ? implode(', ', $teamNames) : 'Individual task';
                } catch (\Exception $e) {
                    return 'Team info unavailable';
                }
            };
            
            // Get student's assignments from assignment_members table - ONLY for this specific student
            $assignments = \App\Models\AssignmentMember::where('student_id', $user->user_id)
                ->with(['assignment.category', 'student'])
                ->get();
                
            // Debug: Log what we found for this student
            \Log::info('Student Tasks Debug', [
                'student_id' => $user->user_id,
                'student_name' => ($user->user_fname ?? '') . ' ' . ($user->user_lname ?? ''),
                'assignments_found' => $assignments->count(),
                'assignment_details' => $assignments->map(function($a) {
                    return [
                        'assignment_id' => $a->assignment_id,
                        'category' => $a->assignment->category->name ?? 'No category',
                        'task_type' => $a->task_type,
                        'task_area' => $a->task_area
                    ];
                })
            ]);
            
            $tasks = [];
            
            foreach ($assignments as $assignmentMember) {
                $assignment = $assignmentMember->assignment;
                $category = $assignment->category ?? null;
                
                // Only show current assignments that are specifically for this student
                if ($category && $assignment->status === 'current') {
                    $tasks[] = [
                        'id' => $assignment->id,
                        'category_name' => $category->name,
                        'area_name' => $assignmentMember->task_area ?? ($category->parent ? $category->parent->name : 'General Area'),
                        'task_description' => $assignmentMember->task_description ?? ('Complete assigned duties for ' . $category->name),
                        'location' => $assignmentMember->task_area ?? $category->name,
                        'start_time' => '08:00',
                        'end_time' => '17:00',
                        'status' => 'assigned',
                        'team_members' => $assignmentMember->task_type ?? 'Individual task',
                        'notes' => $assignmentMember->notes ?? null,
                        'assigned_date' => $assignment->created_at ? $assignment->created_at->format('Y-m-d') : $date,
                        'day_of_week' => $day,
                        'task_type' => $assignmentMember->task_type,
                        'time_slot' => $assignmentMember->time_slot
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'student_name' => ($user->user_fname ?? '') . ' ' . ($user->user_lname ?? ''),
                'day' => $day,
                'date' => $date,
                'total_tasks' => count($tasks)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching student tasks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch your tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    });

    // API route for task submissions
    Route::get('/api/task-submissions', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $submissions = collect();

        // If no task submissions exist, create sample data for testing
        $existingSubmissions = \App\Models\TaskSubmission::count();
        if ($existingSubmissions == 0) {
            // Create sample task submissions for testing
            $sampleStudents = \App\Models\PNUser::where('user_role', 'student')->take(3)->get();
            $sampleCategories = \App\Models\Category::take(3)->get();
            
            if ($sampleStudents->count() > 0 && $sampleCategories->count() > 0) {
                foreach ($sampleStudents as $index => $student) {
                    $category = $sampleCategories->get($index % $sampleCategories->count());
                    
                    \App\Models\TaskSubmission::create([
                        'user_id' => $student->user_id,
                        'task_category' => $category->id,
                        'description' => 'Sample task submission for testing',
                        'status' => 'pending',
                        'admin_notes' => 'Reported by: Jasper Drake Ybanez'
                    ]);
                }
            }
        }

        // Get existing task submissions from database
        try {
            $dbSubmissions = \App\Models\TaskSubmission::with('student')
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Debug: Log the count and sample data
            \Log::info('Found ' . $dbSubmissions->count() . ' task submissions');
            if ($dbSubmissions->count() > 0) {
                $firstSubmission = $dbSubmissions->first();
                \Log::info('Sample submission - ID: ' . $firstSubmission->id . ', task_category: ' . $firstSubmission->task_category);
            }
                
            $dbSubmissions = $dbSubmissions->map(function($submission) {
                    // Get both student ID and name
                    $studentId = 'Unknown ID';
                    $studentName = 'Unknown Student';
                    if ($submission->student) {
                        // Get student detail to find student_id code
                        $studentDetail = \App\Models\StudentDetail::where('user_id', $submission->student->user_id)->first();
                        $studentId = $studentDetail ? $studentDetail->student_id : 'Unknown ID';
                        
                        // Get actual student name
                        $studentName = trim(($submission->student->user_fname ?? '') . ' ' . ($submission->student->user_lname ?? ''));
                        if (empty(trim($studentName))) {
                            $studentName = 'Unknown Student';
                        }
                    }
                    
                    // Extract coordinator name from admin_notes if available, or use a dynamic student name
                    $coordinatorName = 'Unknown Coordinator';
                    if ($submission->admin_notes && preg_match('/Reported by: ([^|]+)/', $submission->admin_notes, $matches)) {
                        $coordinatorName = trim($matches[1]);
                    } else {
                        // Use a random student name from the database as coordinator
                        $randomStudent = \App\Models\PNUser::where('user_role', 'student')->inRandomOrder()->first();
                        if ($randomStudent) {
                            $coordinatorName = trim(($randomStudent->user_fname ?? '') . ' ' . ($randomStudent->user_lname ?? ''));
                        }
                    }
                    
                    // Get category name from task_category ID
                    $categoryName = 'Unknown Category';
                    if ($submission->task_category) {
                        // Try to find category by ID first
                        $category = \App\Models\Category::find($submission->task_category);
                        if ($category) {
                            $categoryName = $category->name ?? 'Unknown Category';
                        } else {
                            // If not found by ID, try to match by name (in case task_category is a string)
                            $categoryByName = \App\Models\Category::where('name', 'like', '%' . $submission->task_category . '%')->first();
                            if ($categoryByName) {
                                $categoryName = $categoryByName->name;
                            } else {
                                // If still not found, use a default based on common categories
                                $categoryName = match(strtolower($submission->task_category)) {
                                    '33', 'dorm', 'office' => 'Dorm Office Area',
                                    'conference', 'meeting' => 'Conference Rooms',
                                    'ground', 'operations' => 'Ground Level Operations',
                                    'waste', 'rooftop' => 'Rooftop Waste Management',
                                    default => 'General Task Area'
                                };
                            }
                        }
                    } else {
                        $categoryName = 'General Task Area';
                    }
                    
                    // Use actual submission date but with current time for display
                    $submissionDate = $submission->created_at->setTimezone('Asia/Manila');
                    $currentTime = now()->setTimezone('Asia/Manila');
                    
                    // Use today's date with current time for display, but keep original date for filtering
                    $displayDate = $currentTime->format('Y-m-d') . ' ' . $currentTime->format('H:i:s');
                    
                    // Parse student's task status from description (e.g., "Status: In Progress")
                    $studentStatusDisplay = 'Pending';
                    $studentStatusKey = 'pending';
                    $reportedDateForJoin = null;
                    if (!empty($submission->description)) {
                        if (preg_match('/Status:\s*([^\n]+)/i', $submission->description, $m)) {
                            $studentStatusDisplay = trim($m[1]);
                            $key = strtolower(str_replace(' ', '_', trim($studentStatusDisplay)));
                            // normalize known values
                            if (in_array($key, ['pending','in_progress','completed'])) {
                                $studentStatusKey = $key;
                            } elseif ($key === 'inprogress') {
                                $studentStatusKey = 'in_progress';
                            } elseif ($key === 'done') {
                                $studentStatusKey = 'completed';
                            } else {
                                $studentStatusKey = 'pending';
                            }
                            // Ensure friendly display text
                            $studentStatusDisplay = $studentStatusKey === 'in_progress' ? 'In Progress' : ucfirst($studentStatusKey);
                        }
                        // Extract reported date if present to join with generated_schedules
                        if (preg_match('/Date:\s*(\d{4}-\d{2}-\d{2})/i', $submission->description, $dm)) {
                            $reportedDateForJoin = trim($dm[1]);
                        }
                    }

                    // If we have a user_id and/or date, read the authoritative status from generated_schedules
                    try {
                        // Default date fallback: submission created_at (YYYY-MM-DD)
                        if (!$reportedDateForJoin && isset($submission->created_at)) {
                            try { $reportedDateForJoin = \Carbon\Carbon::parse($submission->created_at)->toDateString(); } catch (\Throwable $e) {}
                        }

                        $gs = null;
                        if ($reportedDateForJoin) {
                            // Primary: exact date match + student match
                            $gs = \DB::table('generated_schedules')
                                ->whereDate('schedule_date', $reportedDateForJoin)
                                ->where(function($q) use ($submission, $studentName) {
                                    $q->where('student_id', $submission->user_id);
                                    if (!empty($studentName)) {
                                        $q->orWhere('student_name', 'like', $studentName);
                                    }
                                })
                                ->orderByDesc('id')
                                ->first();

                            // Fallback A: ±1 day window if exact date not found
                            if (!$gs) {
                                $start = \Carbon\Carbon::parse($reportedDateForJoin)->subDay()->toDateString();
                                $end   = \Carbon\Carbon::parse($reportedDateForJoin)->addDay()->toDateString();
                                $gs = \DB::table('generated_schedules')
                                    ->whereBetween('schedule_date', [$start, $end])
                                    ->where(function($q) use ($submission, $studentName) {
                                        $q->where('student_id', $submission->user_id);
                                        if (!empty($studentName)) {
                                            $q->orWhere('student_name', 'like', $studentName);
                                        }
                                    })
                                    ->orderByDesc('schedule_date')
                                    ->orderByDesc('id')
                                    ->first();
                            }
                        }

                        // Fallback B: latest schedule for this student regardless of date
                        if (!$gs) {
                            $gs = \DB::table('generated_schedules')
                                ->where(function($q) use ($submission, $studentName) {
                                    $q->where('student_id', $submission->user_id);
                                    if (!empty($studentName)) {
                                        $q->orWhere('student_name', 'like', $studentName);
                                    }
                                })
                                ->orderByDesc('schedule_date')
                                ->orderByDesc('id')
                                ->first();
                        }

                        if ($gs && !empty($gs->task_status)) {
                            $key = strtolower(str_replace(' ', '_', trim($gs->task_status)));
                            if (in_array($key, ['pending','in_progress','completed'])) {
                                $studentStatusKey = $key;
                            } elseif ($key === 'inprogress') {
                                $studentStatusKey = 'in_progress';
                            } elseif ($key === 'done') {
                                $studentStatusKey = 'completed';
                            }
                            $studentStatusDisplay = $studentStatusKey === 'in_progress' ? 'In Progress' : ucfirst($studentStatusKey);
                        }
                    } catch (\Throwable $e) {
                        // non-fatal; keep parsed status
                    }

                    return [
                        'id' => $studentId, // Student ID code
                        'student_name' => $studentName, // Actual student name
                        'original_id' => $submission->id, // Keep original ID for validation
                        'task' => $categoryName, // Use actual category name instead of ID
                        'submittedBy' => $coordinatorName,
                        'dateSubmitted' => $displayDate, // Use current date with current time
                        'originalDate' => $submissionDate->format('Y-m-d H:i:s'), // Keep original for reference
                        'status' => $submission->status,
                        'student_status' => $studentStatusKey,
                        'student_status_display' => $studentStatusDisplay,
                        'description' => $submission->description,
                        'admin_notes' => $submission->admin_notes,
                        'type' => strpos($submission->description, 'Non-accomplish') !== false ? 'non-accomplish' : 'regular'
                    ];
                });
            $submissions = $submissions->merge($dbSubmissions);
        } catch (\Exception $e) {
        }

        return response()->json($submissions->sortByDesc(function($item) {
            return strtotime($item['dateSubmitted']);
        })->values());
    });

        // Report Routes (Damage / Maintenance Reports) - use distinct URI to avoid collision with student reports
        Route::prefix('damage-reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('damage_reports.index');
        Route::get('/create', [ReportController::class, 'create'])->name('damage_reports.create');
        Route::post('/', [ReportController::class, 'store'])->name('damage_reports.store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('damage_reports.show');
        Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('damage_reports.edit');
        Route::put('/{report}', [ReportController::class, 'update'])->name('damage_reports.update');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('damage_reports.destroy');
            Route::get('/{report}/download', [ReportController::class, 'download'])->name('damage_reports.download');
            Route::get('/export/excel', [ReportController::class, 'export'])->name('damage_reports.export');
    });

    // NOTE: Damage Reports are handled by ReportController routes (named damage_reports.*).
    // The explicit inline closures were removed to avoid duplicate/conflicting routes.


    // API route for validating task submissions
    Route::post('/api/task-submissions/{id}/validate', function($id, \Illuminate\Http\Request $request) {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $status = $request->input('status'); // 'valid' or 'invalid'
        
        \Log::info("Validation attempt - ID: {$id}, Status: {$status}, User: {$user->user_id}");

        try {
            $submission = \App\Models\TaskSubmission::findOrFail($id);
            \Log::info("Found submission in database: " . $submission->id);
            
            $submission->update([
                'status' => $status,
                'validated_by' => $user->user_id,
                'validated_at' => now(),
                'admin_notes' => $request->input('notes', '') . " | Original admin_notes: " . $submission->admin_notes
            ]);
            
            \Log::info("Successfully updated submission {$id} to status: {$status}");
            return response()->json(['success' => true, 'message' => 'Task validation updated successfully', 'updated_id' => $id]);
        } catch (\Exception $e) {
            \Log::error("Database validation failed for ID {$id}: " . $e->getMessage());
            // If not found in database, check JSON file
        }

        $reportsFile = storage_path('app/student_reports.json');
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];

            foreach ($reports as &$report) {
                // Handle both numeric ID and RPT format
                $reportIdToCheck = $report['report_id'];
                $idToSearch = $id;
                
                // Convert both to same format for comparison
                if (is_numeric($id)) {
                    // If ID is numeric, convert to RPT format
                    $idToSearch = 'RPT' . str_pad($id, 3, '0', STR_PAD_LEFT);
                }
                
                if ($reportIdToCheck === $idToSearch) {
                    $report['status'] = ucfirst($status);
                    $report['validated_by'] = $user->user_fname . ' ' . $user->user_lname;
                    $report['validated_at'] = now()->format('M d, Y, h:i A');

                    file_put_contents($reportsFile, json_encode($reports, JSON_PRETTY_PRINT));
                    return response()->json(['success' => true, 'message' => 'Student report validation updated successfully']);
                }
            }
        }

        return response()->json(['success' => false, 'message' => 'Report not found'], 404);
    });
    // Restrict admin dashboard to educator/inspector only
    Route::get('/AdminDashboard', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('mainstudentdash')->with('error', 'Access denied. Students can only access the student dashboard.');
        }
        return app(TaskController::class)->dashboard();
    })->name('dashboard');

    Route::get('/Admindashboard', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('mainstudentdash')->with('error', 'Access denied. Students can only access the student dashboard.');
        }
        return app(TaskController::class)->dashboard();
    })->name('Admindashboard');
    // Route moved to shared section for both admin and student access
    Route::get('/generalTask', [GeneralTaskController::class, 'index'])->name('generalTask');
    Route::get('/generalTask/inspection', [GeneralTaskInspectionController::class, 'index'])->name('generalTask.inspection');
    Route::get('/generalTask/inspection/status-details', [GeneralTaskInspectionController::class, 'statusDetails'])->name('generalTask.inspection.status-details');
    Route::post('/generalTask/inspection/{generatedSchedule}/status', [GeneralTaskInspectionController::class, 'updateStatus'])->name('generalTask.inspection.update');

    // Submit feedback route
    Route::post('/submit-feedback', [FeedbackController::class, 'submitFeedback'])->name('submit.feedback');
    Route::get('/feedbacks', [FeedbackController::class, 'getRoomFeedbacks']);
    Route::post('/feedback/edit', [FeedbackController::class, 'editFeedback'])->name('feedback.edit');
    Route::post('/feedback/delete', [FeedbackController::class, 'deleteFeedback'])->name('feedback.delete');

    // Task routes
    Route::post('/update-task-status', [TaskController::class, 'updateTaskStatus']);
    Route::post('/mark-day-complete', [TaskHistoryController::class, 'markDayComplete']);
    Route::post('/save-task', [TaskController::class, 'saveTask'])->name('save.task');
    Route::post('/schedule-tasks', [TaskController::class, 'scheduleTasks'])->name('tasks.schedule');
    // API: Get canonical base templates for a room/day (used by client-side generated schedule preview)
    Route::get('/tasks/base-templates', [TaskController::class, 'getBaseTemplates'])->name('tasks.baseTemplates');
    // Persist rotation schedules created from student dashboard
    Route::post('/tasks/schedule', function (\Illuminate\Http\Request $request) {
        $data = $request->all();
        $room = $data['room'] ?? null;
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'room is required'], 422);
        }

        $start_date = $data['start_date'] ?? null;
        $end_date = $data['end_date'] ?? null;
        $mode = $data['mode'] ?? null;
        $frequency = isset($data['frequency']) ? intval($data['frequency']) : null;
        $schedule_map = $data['schedule_map'] ?? null;

        // Persist rotation_schedules (prefer Eloquent, fallback to login connection raw query)
        try {
            $rs = \App\Models\RotationSchedule::updateOrCreate(
                ['room' => $room, 'start_date' => $start_date, 'end_date' => $end_date],
                [
                    'schedule_map' => is_string($schedule_map) ? $schedule_map : (is_array($schedule_map) ? json_encode($schedule_map) : $schedule_map),
                    'mode' => $mode,
                    'frequency' => $frequency,
                    'created_by' => auth()->id() ?? null,
                ]
            );
        } catch (\Throwable $e) {
            try {
                \DB::connection('login')->table('rotation_schedules')->updateOrInsert(
                    ['room' => $room, 'start_date' => $start_date, 'end_date' => $end_date],
                    [
                        'schedule_map' => is_string($schedule_map) ? $schedule_map : (is_array($schedule_map) ? json_encode($schedule_map) : $schedule_map),
                        'mode' => $mode,
                        'frequency' => $frequency,
                        'created_by' => auth()->id() ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            } catch (\Throwable $ee) {
                \Log::error('Failed to persist rotation schedule in login DB: ' . $ee->getMessage());
            }
        }

        // Decode schedule_map if it's a JSON string
        $map = $schedule_map;
        if (is_string($map)) {
            $decoded = json_decode($map, true);
            if (json_last_error() === JSON_ERROR_NONE) $map = $decoded;
        }

        // Apply schedule_map into date-scoped roomtask rows on login DB connection
        if ($map && is_array($map)) {
            try {
                $conn = \DB::connection('login');
                foreach ($map as $iso => $assigns) {
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
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } catch (\Throwable $inner) {
                        \Log::warning('Failed applying schedule_map into login roomtask: ' . $inner->getMessage(), ['iso' => $iso, 'room' => $room]);
                        continue;
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Error applying schedule_map to roomtask (login DB): ' . $e->getMessage());
            }
        }

        // After applying schedule_map, return authoritative persisted roomtask rows
        // for the affected dates so client can refresh DOM from server state.
        $appliedTasks = [];
        try {
            if (is_array($map) && count($map) && $room) {
                $conn = \DB::connection('login');
                foreach (array_keys($map) as $isoDate) {
                    try {
                        $d = new \Carbon\Carbon($isoDate);
                        $dayName = $d->format('l');
                        $week = $d->format('W');
                        $month = $d->format('n');
                        $year = $d->format('Y');

                        $rows = $conn->table('roomtask')
                            ->where('room_number', $room)
                            ->where('day', $dayName)
                            ->where('week', $week)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->orderBy('area')
                            ->get();

                        foreach ($rows as $r) {
                            $appliedTasks[] = [
                                'id' => $r->id ?? null,
                                'room_number' => $r->room_number ?? null,
                                'area' => $r->area ?? null,
                                'name' => $r->name ?? null,
                                'desc' => $r->desc ?? null,
                                'date_iso' => $isoDate,
                                'day' => $dayName,
                                'week' => $week,
                                'month' => $month,
                                'year' => $year,
                            ];
                        }
                    } catch (\Throwable $inner) {
                        // skip this date on failure
                        continue;
                    }
                }
            }
        } catch (\Throwable $e) {
            // do not break apply response on fetch errors; log and continue
            \Log::warning('Failed to collect applied roomtask rows: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'schedule' => $rs ?? null, 'applied_tasks' => $appliedTasks]);
    });
    Route::get('/get-task-photos', [TaskController::class, 'getTaskPhotos']);
    Route::get('/task-history', [TaskHistoryController::class, 'showTaskHistory'])->name('admin.task.history');
    // Route moved to shared section for both admin and student access
   // Route::post('/mark-day-completed', [App\Http\Controllers\TaskController::class, 'markDayAsCompleted'])->name('mark.day.completed');
    Route::post('/api/check-week-completion', [\App\Http\Controllers\TaskController::class, 'apiCheckWeekCompletion']);

    // Report Routes - Only accessible by coordinators
    Route::prefix('reports')->middleware(['auth'])->group(function () {
        Route::get('/', function() {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->index();
        })->name('reports.index');
        
        Route::get('/create', function() {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->index();
        })->name('reports.create');
        
        Route::post('/', function(\Illuminate\Http\Request $request) {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Access denied: Only coordinators can access this feature.'], 403);
                }
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->submitReport($request);
        })->name('reports.store');
        
        Route::get('/{report}', function($report) {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->index();
        })->name('reports.show');
        
        Route::get('/{report}/edit', function($report) {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->index();
        })->name('reports.edit');
        
        Route::put('/{report}', function(\Illuminate\Http\Request $request, $report) {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Access denied: Only coordinators can access this feature.'], 403);
                }
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->submitReport($request);
        })->name('reports.update');
        
        Route::delete('/{report}', function(\Illuminate\Http\Request $request, $report) {
            $user = auth()->user();
            $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
            if (!in_array($user->user_role, $coordinatorRoles)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Access denied: Only coordinators can access this feature.'], 403);
                }
                return redirect()->route('mainstudentdash')->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
            }
            return app(\App\Http\Controllers\StudentReportController::class)->deleteReport($report);
        })->name('reports.destroy');
        // Add more as needed (download, export, etc.)
    });

    // API routes for dashboard student management
    Route::post('/api/room/add-student', [TaskController::class, 'apiAddStudent']);
    Route::post('/api/room/edit-student', [TaskController::class, 'apiEditStudent']);
    Route::post('/api/room/delete-student', [TaskController::class, 'apiDeleteStudent']);

    // API routes for room capacity management
    Route::post('/api/room/set-capacity', [TaskController::class, 'setRoomCapacity']);
    Route::get('/api/room/get-capacity', [TaskController::class, 'getRoomCapacity']);
    Route::post('/api/room/reassign-students', [TaskController::class, 'reassignStudents']);
    Route::get('/api/room/statistics', [TaskController::class, 'getRoomStatistics']);

    // API route for individual room capacity management
    Route::post('/api/room/set-individual-capacity', [TaskController::class, 'setIndividualRoomCapacity']);

    // API route to get room details including persistent capacity
    Route::get('/api/room/details/{roomNumber}', [TaskController::class, 'getRoomDetails']);

    // API routes for student reports
    Route::get('/api/get-assigned-students/{category}', [MainStudentDASHController::class, 'getAssignedStudents']);
    Route::post('/api/submit-student-report', [MainStudentDASHController::class, 'submitStudentReport']);
    Route::post('/api/save-draft-report', [MainStudentDASHController::class, 'saveDraftReport']);
    Route::get('/api/get-reports-by-date/{date}', [MainStudentDASHController::class, 'getReportsByDate']);
    Route::get('/api/get-drafts-by-date/{date}', [MainStudentDASHController::class, 'getDraftsByDate']);
    Route::get('/api/get-draft-by-id/{draftId}', [MainStudentDASHController::class, 'getDraftById']);
    Route::delete('/api/delete-draft/{draftId}', [MainStudentDASHController::class, 'deleteDraft']);
    // Coordinator Detection API Routes
    Route::get('/api/check-coordinator-status', [MainStudentDASHController::class, 'checkIfUserIsCoordinator']);
    Route::get('/api/current-user-assignment-status', [MainStudentDASHController::class, 'getCurrentUserAssignmentStatus']);

    // Admin API: Get students by task status
    Route::get('/api/admin-students-by-status/{status}', function($status) {
        $user = auth()->user();
        if (!$user || !in_array($user->user_role, ['educator', 'inspector'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Map status parameter to display status values
        $statusMap = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed'
        ];

        if (!isset($statusMap[$status])) {
            return response()->json(['error' => 'Invalid status'], 422);
        }

        $targetDisplayStatus = $statusMap[$status];

        // Get all task submissions with database status in ['pending', 'valid', 'invalid']
        $submissions = \App\Models\TaskSubmission::whereIn('status', ['pending', 'valid', 'invalid'])
            ->orderByDesc('created_at')
            ->get();

        // Filter by display status extracted from description
        $filteredSubmissions = $submissions->filter(function($submission) use ($targetDisplayStatus) {
            preg_match('/Status: (.+?)\n/', $submission->description, $statusMatch);
            $displayStatus = $statusMatch[1] ?? 'Pending';
            return $displayStatus === $targetDisplayStatus;
        });

        $scheduleCache = [];
        $getGeneratedScheduleEntry = function($studentId, $studentName, $targetDate = null) use (&$scheduleCache) {
            $normalizedName = $studentName ? strtolower(trim($studentName)) : null;
            $baseKey = $studentId ? 'id_' . $studentId : ($normalizedName ? 'name_' . $normalizedName : null);

            if (!$baseKey) {
                return null;
            }

            $dateKey = $targetDate ? 'date_' . $targetDate : 'latest';
            $cacheKey = $baseKey . '__' . $dateKey;

            if (array_key_exists($cacheKey, $scheduleCache)) {
                return $scheduleCache[$cacheKey];
            }

            $buildQuery = function($withDateFilter = true) use ($studentId, $normalizedName, $targetDate) {
                $query = \App\Models\GeneratedSchedule::query();
                if ($studentId) {
                    $query->where('student_id', $studentId);
                } elseif ($normalizedName) {
                    $query->whereRaw('LOWER(student_name) = ?', [$normalizedName]);
                }

                if ($withDateFilter && $targetDate) {
                    $query->whereDate('schedule_date', $targetDate);
                }

                return $query->orderByDesc('schedule_date');
            };

            $result = $buildQuery(true)->first();

            if (!$result && $targetDate) {
                $result = $buildQuery(false)->first();
            }

            $scheduleCache[$cacheKey] = $result;
            return $result;
        };

        $students = $filteredSubmissions->map(function($submission) use ($getGeneratedScheduleEntry) {
            // Get student details first by user_id
            $student = null;
            $studentName = 'Unknown';
            $assignedTaskName = 'N/A';
            $reportDate = optional($submission->created_at)->toDateString();
            $assignedDate = $reportDate;
            $assignmentRecord = null;
            $displayStatus = 'Pending';
            $descriptionText = $submission->description ?? '';
            $parsedTaskDescription = null;

            if ($submission->user_id) {
                $student = \App\Models\PNUser::where('user_id', $submission->user_id)->first();
            }
            
            // If found by user_id, use that name
            if ($student) {
                $studentName = trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''));
                if (empty($studentName)) {
                    $studentName = $student->user_id ?? 'Unknown';
                }
            } else {
                // Try to extract from description as fallback
                preg_match('/Student: (.+?)\n/', $submission->description, $studentMatch);
                $studentName = trim($studentMatch[1] ?? 'Unknown');
                
                // If still "Unknown", try to find by name in description
                if ($studentName === 'Unknown') {
                    $student = \App\Models\PNUser::where(\DB::raw("CONCAT(user_fname, ' ', user_lname)"), $studentName)->first();
                }
            }

            // Get category names - try multiple approaches
            $mainAreaName = 'N/A';
            $subAreaName = 'N/A';
            
            try {
                // First, try to get the student's current assignment to find the category
                if ($student && $student->user_id) {
                    $assignmentRecord = \App\Models\AssignmentMember::where('student_id', $student->user_id)
                        ->with('assignment.category')
                        ->orderByDesc('created_at')
                        ->first();
                    
                    if ($assignmentRecord && $assignmentRecord->assignment && $assignmentRecord->assignment->category) {
                        $category = $assignmentRecord->assignment->category;
                        $subAreaName = $category->name;
                        $assignedTaskName = $category->name ?? 'General Task';
                        $assignedDate = $assignmentRecord->assignment->start_date ?? null;
                        
                        // Get parent category (main area)
                        if ($category->parent_id) {
                            $parentCategory = \App\Models\Category::find($category->parent_id);
                            if ($parentCategory) {
                                $mainAreaName = $parentCategory->name;
                            }
                        } else {
                            // If no parent, this is the main area
                            $mainAreaName = $category->name;
                        }
                    } else {
                        // Log if assignment not found
                        \Log::info('No assignment found for student', [
                            'student_id' => $student->user_id,
                            'student_name' => $studentName,
                            'submission_id' => $submission->id
                        ]);
                    }
                }
                
                // If assignment lookup failed, try to extract category from description
                if ($mainAreaName === 'N/A' && $subAreaName === 'N/A') {
                    preg_match('/Student Performance Report for (.+?) -/', $submission->description, $categoryMatch);
                    $categoryFromDesc = trim($categoryMatch[1] ?? '');
                    
                    if ($categoryFromDesc && $categoryFromDesc !== '') {
                        // Try to find category by name (exact match)
                        $category = \App\Models\Category::where('name', $categoryFromDesc)->first();
                        if ($category) {
                            $subAreaName = $category->name;
                            if ($assignedTaskName === 'N/A') {
                                $assignedTaskName = $category->name;
                            }
                            if ($category->parent_id) {
                                $parentCategory = \App\Models\Category::find($category->parent_id);
                                if ($parentCategory) {
                                    $mainAreaName = $parentCategory->name;
                                }
                            } else {
                                $mainAreaName = $category->name;
                            }
                        } else {
                            // Try partial match
                            $category = \App\Models\Category::where('name', 'like', '%' . $categoryFromDesc . '%')->first();
                            if ($category) {
                                $subAreaName = $category->name;
                                if ($assignedTaskName === 'N/A') {
                                    $assignedTaskName = $category->name;
                                }
                                if ($category->parent_id) {
                                    $parentCategory = \App\Models\Category::find($category->parent_id);
                                    if ($parentCategory) {
                                        $mainAreaName = $parentCategory->name;
                                    }
                                } else {
                                    $mainAreaName = $category->name;
                                }
                            }
                        }
                    }
                }
                
                // Final fallback: try to get category by ID
                if ($mainAreaName === 'N/A' && $subAreaName === 'N/A' && $submission->task_category) {
                    $category = \App\Models\Category::find($submission->task_category);
                    if ($category) {
                        $subAreaName = $category->name;
                        if ($category->parent_id) {
                            $parentCategory = \App\Models\Category::find($category->parent_id);
                            if ($parentCategory) {
                                $mainAreaName = $parentCategory->name;
                            }
                        } else {
                            $mainAreaName = $category->name;
                        }
                    }
                }
            } catch (\Exception $e) {
                // If all lookups fail, keep as N/A
            }

            // Extract display status and metadata from submission description (defaults to Pending)
            preg_match('/Status: (.+?)\n/', $descriptionText, $statusMatch);
            if (!empty($statusMatch[1])) {
                $displayStatus = trim($statusMatch[1]);
            }

            // Extract task title
            preg_match('/Task Status Update:\s*(.*?)\s*-\s*Status:/', $descriptionText, $taskTitleMatch);
            if (!empty($taskTitleMatch[1])) {
                $assignedTaskName = trim($taskTitleMatch[1]);
            }

            // Extract schedule date from description if available
            preg_match('/Date:\s*([^\n]+)/', $descriptionText, $dateMatch);
            if (!empty($dateMatch[1])) {
                try {
                    $parsedDate = \Carbon\Carbon::parse(trim($dateMatch[1]));
                    $reportDate = $parsedDate->toDateString();
                    $assignedDate = $reportDate;
                } catch (\Exception $e) {
                    // ignore parse failure
                }
            }

            // Extract task description/details
            preg_match('/Description:\s*(.+)$/s', $descriptionText, $taskDescMatch);
            if (!empty($taskDescMatch[1])) {
                $parsedTaskDescription = trim($taskDescMatch[1]);
            }

            $generatedTaskTitle = $assignedTaskName;
            $generatedTaskDescription = $parsedTaskDescription;
            $generatedTaskDate = $reportDate ?? $submission->created_at;

            try {
                $scheduleEntry = $getGeneratedScheduleEntry($student->user_id ?? null, $studentName, $reportDate);
                if ($scheduleEntry) {
                    $generatedTaskTitle = $scheduleEntry->task_title ?: $generatedTaskTitle;
                    $generatedTaskDescription = $scheduleEntry->task_description ?: $generatedTaskDescription;
                    $generatedTaskDate = optional($scheduleEntry->schedule_date)->toDateString() ?? $generatedTaskDate;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch generated schedule entry', [
                    'student' => $studentName,
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'id' => $submission->id,
                'name' => $studentName,
                'student_code' => $student ? ($student->user_id ?? 'N/A') : 'N/A',
                'main_area' => $mainAreaName,
                'sub_area' => $subAreaName,
                'task_category' => $submission->task_category,
                'assignment_id' => $submission->id,
                'status' => strtolower(str_replace(' ', '_', $displayStatus)),
                'notes' => $submission->admin_notes,
                'started_at' => $submission->created_at,
                'completed_at' => $submission->updated_at,
                'assigned_task' => $assignedTaskName,
                'assigned_date' => $assignedDate,
                'generated_task_title' => $generatedTaskTitle,
                'generated_task_description' => $generatedTaskDescription,
                'generated_task_date' => $generatedTaskDate,
            ];
        })->values();

        \Log::info('Admin students by status API', [
            'status' => $status,
            'total_students' => $students->count(),
            'students' => $students->map(function($s) {
                return [
                    'name' => $s['name'],
                    'main_area' => $s['main_area'],
                    'sub_area' => $s['sub_area']
                ];
            })->toArray()
        ]);

        return response()->json([
            'success' => true,
            'status' => $status,
            'students' => $students,
            'total' => $students->count()
        ]);
    })->name('api.admin.students.by.status');

    // API route to get valid students from Login database
    Route::get('/api/students/valid-list', [TaskController::class, 'getValidStudentsList']);

    // API route for manual room task synchronization
    Route::post('/api/room/sync-tasks', [TaskController::class, 'syncRoomTasks']);

    // API routes for dashboard room management integration
    Route::post('/api/dashboard/add-room', [TaskController::class, 'addRoomToDashboard']);
    Route::delete('/api/dashboard/delete-room/{roomNumber}', [TaskController::class, 'deleteRoomFromDashboard']);
    Route::get('/api/dashboard/room-data', [TaskController::class, 'getDashboardRoomData']);

    // Room Management Routes
    Route::get('/room-management', [RoomManagementController::class, 'index'])->name('room.management');
    Route::post('/room-management/rooms', [RoomManagementController::class, 'store'])->name('room.management.store');
    Route::get('/room-management/rooms/{id}', [RoomManagementController::class, 'show'])->name('room.management.show');
    Route::put('/room-management/rooms/{id}', [RoomManagementController::class, 'update'])->name('room.management.update');
    Route::delete('/room-management/rooms/{id}', [RoomManagementController::class, 'destroy'])->name('room.management.destroy');

    // Room Assignment Routes
    Route::post('/room-management/assign-student', [RoomManagementController::class, 'assignStudent'])->name('room.management.assign');
    Route::delete('/room-management/remove-student', [RoomManagementController::class, 'removeStudent'])->name('room.management.remove');

    // Room Task Management Routes
    Route::post('/room-management/tasks', [RoomManagementController::class, 'createTask'])->name('room.management.task.create');
    Route::put('/room-management/tasks/{id}', [RoomManagementController::class, 'updateTask'])->name('room.management.task.update');
    Route::delete('/room-management/tasks/{id}', [RoomManagementController::class, 'deleteTask'])->name('room.management.task.delete');
    Route::get('/room-management/rooms/{roomNumber}/tasks', [RoomManagementController::class, 'getRoomTasks'])->name('room.management.tasks');

    // Room Statistics Route
    Route::get('/room-management/statistics', [RoomManagementController::class, 'getStatistics'])->name('room.management.statistics');

    // Room Data Sync Route
    Route::post('/room-management/sync-dashboard', [RoomManagementController::class, 'syncWithDashboard'])->name('room.management.sync');

    // Test route for debugging room assignments
    Route::get('/test-room-sync', function() {
        $taskController = new \App\Http\Controllers\TaskController();
        $roomStudents = $taskController->getDynamicRoomStudents();

        $roomAssignments = \App\Models\RoomAssignment::with('room')->get();
        $rooms = \App\Models\Room::with('assignments')->get();

        return response()->json([
            'dashboard_room_students' => $roomStudents,
            'room_assignments_count' => $roomAssignments->count(),
            'rooms_count' => $rooms->count(),
            'rooms_with_assignments' => $rooms->filter(function($room) {
                return $room->assignments->count() > 0;
            })->count(),
            'sample_room_assignments' => $roomAssignments->take(5)->map(function($assignment) {
                return [
                    'room_number' => $assignment->room_number,
                    'student_name' => $assignment->student_name,
                    'student_gender' => $assignment->student_gender
                ];
            })
        ]);
    });

    // Test route for reassignment
    Route::post('/test-reassign', function(\Illuminate\Http\Request $request) {
        try {
            $taskController = new \App\Http\Controllers\TaskController();
            return $taskController->reassignStudents($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

    // Test route for debugging statistics API
    Route::get('/test-statistics', function() {
        try {
            $taskController = new \App\Http\Controllers\TaskController();
            $result = $taskController->getRoomStatistics();
            return $result;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Enhanced Room Management Routes
    Route::post('/room-management/add-room-sync', [RoomManagementController::class, 'addRoomWithSync'])->name('room.management.add.sync');
    Route::delete('/room-management/delete-room-sync/{id}', [RoomManagementController::class, 'deleteRoomWithSync'])->name('room.management.delete.sync');
    Route::get('/room-management/dashboard-data', [RoomManagementController::class, 'getRoomsForDashboard'])->name('room.management.dashboard.data');

    // General Task Dashboard
    Route::get('/GenTaskDashboard', [GeneralTaskController::class, 'index'])->name('genTaskDashboard');
    Route::get('/task-checklist', [GeneralTaskController::class, 'taskChecklist'])->name('task.checklist');
    Route::post('/task-checklist/update-status', [GeneralTaskController::class, 'updateTaskStatus'])->name('task.updateStatus');
    Route::post('/task-checklist/update-remarks', [GeneralTaskController::class, 'updateTaskRemarks'])->name('task.updateRemarks');
    Route::post('/task-checklist/update-dates', [GeneralTaskController::class, 'updateWeekDates'])->name('task.updateDates');

    // Placeholder route: some views expect the named route 'dashboard.loadTaskStatuses'.
    // This returns an empty JSON payload to avoid Blade render exceptions while the
    // real implementation is reviewed/added.
    Route::get('/dashboard/load-task-statuses', function() {
        return response()->json(['success' => true, 'data' => []]);
    })->name('dashboard.loadTaskStatuses');

    // Students
    Route::get('/students', [Student16Controller::class, 'index'])->name('students.index');
    Route::get('/students/create', [Student16Controller::class, 'create'])->name('students.create');
    Route::post('/students', [Student16Controller::class, 'store'])->name('students.store');
    Route::get('/student/{id}', [Student16Controller::class, 'show'])->name('student.show');
    Route::delete('/students/remove/{name}', [Student16Controller::class, 'removeStudent'])->name('students.remove');

    // Student Management (for View Members modal)
    Route::put('/students/{id}/update-name', [Student16Controller::class, 'updateName'])->name('students.updateName');
    Route::delete('/students/{id}', [Student16Controller::class, 'destroy'])->name('students.destroy');
    Route::post('/students/quick-add', [Student16Controller::class, 'quickAdd'])->name('students.quickAdd');
    Route::post('/students/quick-add-to-category', [Student16Controller::class, 'quickAddToCategory'])->name('students.quickAddToCategory');
    Route::get('/students/all-for-deletion', [Student16Controller::class, 'getAllForDeletion'])->name('students.getAllForDeletion');
    Route::post('/students/delete-multiple', [Student16Controller::class, 'deleteMultiple'])->name('students.deleteMultiple');

    // Batch Management
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
    Route::get('/api/batches/active', [BatchController::class, 'getActiveBatches'])->name('batches.active');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Task Areas Management (using GeneralTaskController for consistency)
    Route::post('/task-areas', [GeneralTaskController::class, 'addTaskArea'])->name('task-areas.store');
    Route::put('/task-areas/{id}', [GeneralTaskController::class, 'updateTaskArea'])->name('task-areas.update');
    Route::delete('/task-areas/{id}', [GeneralTaskController::class, 'deleteTaskArea'])->name('task-areas.destroy');
    Route::get('/api/main-areas', [GeneralTaskController::class, 'getMainAreas'])->name('api.main-areas');

    // System Settings
    Route::get('/api/settings/assignment-duration', [App\Http\Controllers\SystemSettingsController::class, 'getAssignmentDuration'])->name('settings.assignment-duration.get');
    Route::post('/api/settings/assignment-duration', [App\Http\Controllers\SystemSettingsController::class, 'updateAssignmentDuration'])->name('settings.assignment-duration.update');

    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/assignments/auto-shuffle', [AssignmentController::class, 'autoShuffle'])->name('assignments.autoShuffle');
    Route::post('/assignments/emergency-fix-batches', [AssignmentController::class, 'emergencyFixBatches'])->name('assignments.emergencyFixBatches');
    Route::post('/assignments/category/{categoryId}/fix-coordinators', [AssignmentController::class, 'fixCoordinatorsForCategory'])->name('assignments.fixCoordinators');
    // Handle GET requests to auto-shuffle (redirect with error message)
    Route::get('/assignments/auto-shuffle', function() {
        return redirect()->route('generalTask')->with('error', 'Auto-shuffle must be triggered using the Auto-Shuffle button, not by accessing the URL directly.');
    });
    Route::post('/assignments/update-capacity', [AssignmentController::class, 'updateCategoryCapacity'])->name('assignments.updateCapacity');
    Route::post('/assignments/update-end-date', [AssignmentController::class, 'updateAssignmentEndDate'])->name('assignments.updateEndDate');
    Route::get('/assignments/check-session-overrides', [AssignmentController::class, 'checkSessionOverrides'])->name('assignments.checkSessionOverrides');
    Route::delete('/assignments/category/{categoryId}/current', [AssignmentController::class, 'deleteCurrentAssignment'])->name('assignments.deleteCurrent');
    Route::post('/assignments/clear-shuffle-lock', [AssignmentController::class, 'clearAutoShuffleLock'])->name('assignments.clearShuffleLock');

    // Debug route to test database connection
    Route::get('/test-db', function() {
        try {
            DB::connection()->getPdo();
            $studentCount = \App\Models\PNUser::where('user_role', 'student')->count();
            $categoryCount = \App\Models\Category::count();
            return "Database connection successful! Students: {$studentCount}, Categories: {$categoryCount}";
        } catch (\Exception $e) {
            return "Database connection failed: " . $e->getMessage();
        }
    });

    // Test auto-shuffle prerequisites
    Route::get('/test-shuffle-prereq', function() {
        try {
            $students = \App\Models\PNUser::where('user_role', 'student')->count();
            $categories = \App\Models\Category::count();
            $assignments = \App\Models\Assignment::count();
            return response()->json([
                'status' => 'success',
                'students' => $students,
                'categories' => $categories,
                'assignments' => $assignments,
                'db_connection' => config('database.default')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'db_connection' => config('database.default')
            ]);
        }
    });
    Route::post('/assignments/cleanup-duplicates', [AssignmentController::class, 'cleanupDuplicates'])->name('assignments.cleanupDuplicates');
    Route::get('/assignments/category/{categoryId}/members', [AssignmentController::class, 'getCategoryMembers'])->name('assignments.getCategoryMembers');
    Route::post('/assignments/update-member-comment', [AssignmentController::class, 'updateMemberComment'])->name('assignments.updateMemberComment');
    Route::post('/assignments/change-member-batch', [AssignmentController::class, 'changeMemberBatch'])->name('assignments.changeMemberBatch');
    Route::get('/assignments/category/{categoryId}/available-students', [AssignmentController::class, 'getAvailableStudents'])->name('assignments.getAvailableStudents');
    Route::get('/assignments/category/{categoryId}/current-members', [AssignmentController::class, 'getCurrentMembers'])->name('assignments.getCurrentMembers');
    Route::post('/assignments/category/{categoryId}/add-members', [AssignmentController::class, 'addMembers'])->name('assignments.addMembers');
        Route::post('/assignments/category/{categoryId}/remove-members', [AssignmentController::class, 'removeMembers'])->name('assignments.removeMembers');

        // Admin access to Student Dashboard views
        // Route::get('/StudentDashboard', [MainStudentDASHController::class, 'index'])->name('admin.student.dashboard');
        Route::get('/StudentGenTaskDashboard', [MainStudentDASHController::class, 'generalTask'])->name('admin.student.general.task');
});

// Test route for debugging (no auth required) - Use same controller as admin
Route::get('/test-student-general-task', [GeneralTaskController::class, 'index'])->name('test.student.general.task');

// Direct student general task route (no auth required for testing) - Use same controller as admin
Route::get('/student-general-task-direct', [GeneralTaskController::class, 'index'])->name('student.general.task.direct');


// Check current assignments blocking auto-shuffle
Route::get('/check-assignments', function() {
    $currentAssignments = \App\Models\Assignment::where('status', 'current')->with('category')->get();
    $now = \Carbon\Carbon::now();
    $results = [];
    
    foreach ($currentAssignments as $assignment) {
        $endDate = \Carbon\Carbon::parse($assignment->end_date);
        $daysRemaining = $now->diffInDays($endDate);
        $results[] = [
            'category' => $assignment->category->name,
            'end_date' => $assignment->end_date,
            'days_remaining' => $daysRemaining,
            'can_shuffle' => $now->gte($endDate)
        ];
    }
    
    return response()->json([
        'current_date' => $now->toDateString(),
        'assignments' => $results,
        'can_auto_shuffle' => count($results) == 0 || collect($results)->every(fn($a) => $a['can_shuffle'])
    ]);
});

// Force auto-shuffle for testing (bypasses date validation)
Route::post('/force-auto-shuffle', function() {
    try {
        return app(\App\Http\Controllers\AssignmentController::class)->autoShuffle(request()->merge(['force_shuffle' => true]));
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::post('/student/damage-report/submit', function(\Illuminate\Http\Request $request) {
    try {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'item_damaged' => 'required|string|max:255',
            'description' => 'required|string',
            'date_discovered' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'reporter_contact' => 'nullable|string|max:255'
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('damage-reports', 'public');
        }

        // Create damage report
        $damageReport = \App\Models\DamageReport::create([
            'location' => $validated['location'],
            'priority' => $validated['priority'],
            'item_damaged' => $validated['item_damaged'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'reporter_contact' => $validated['reporter_contact'] ?? null,
            'reported_by' => auth()->id(),
            'reporter_name' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname,
            'status' => 'pending',
            'reported_at' => $validated['date_discovered']
        ]);

        return redirect()->route('student.damage.report')
            ->with('success', 'Damage report submitted successfully! Maintenance team will be notified.');
            
    } catch (\Exception $e) {
        \Log::error('Damage report submission failed: ' . $e->getMessage());
        return redirect()->route('student.damage.report')
            ->with('error', 'Failed to submit damage report. Please try again.');
    }
})->name('student.damage.report.submit');

Route::get('/test-damage-form', function() {
    try {
        return view('StudentsDashboard.damage-report', ['recentReports' => []]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'View error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});


// Test with simple view
Route::get('/test-simple-damage', function() {
    return view('StudentsDashboard.damage-report-simple');
});

// Test login database connection for damage reports
Route::get('/test-login-db-damage', function() {
    try {
        // Test login database connection
        $loginDb = \DB::connection('login');
        $loginDb->getPdo();
        
        // Test if damage_reports table exists
        $tableExists = \Schema::connection('login')->hasTable('damage_reports');
        
        // Count existing reports
        $reportCount = \App\Models\DamageReport::on('login')->count();
        
        return response()->json([
            'status' => 'SUCCESS',
            'login_db_connection' => 'Connected',
            'damage_reports_table_exists' => $tableExists ? 'YES' : 'NO',
            'existing_reports_count' => $reportCount,
            'message' => 'Login database connection working for damage reports!'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Debug route to test General Task access
Route::get('/debug-general-task', function() {
    try {
        return app(\App\Http\Controllers\GeneralTaskController::class)->index();
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Admin route to fix student batches
Route::get('/admin/fix-student-batches', function() {
    // Only allow admin access
    if (!auth()->check() || !in_array(auth()->user()->user_role, ['educator', 'inspector'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    try {
        \Artisan::call('students:fix-batches');
        $output = \Artisan::output();
        return response("<pre>{$output}</pre>");
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('admin.fix.batches');

// Simple debug route to check assignments
Route::get('/debug-assignments', function() {
    $assignments = \App\Models\Assignment::with(['category', 'assignmentMembers.student'])->get();
    $output = "<h1>Debug: Assignments</h1>";
    $output .= "<p>Total assignments: " . $assignments->count() . "</p>";

    if ($assignments->isEmpty()) {
        $output .= "<p><strong>No assignments found!</strong></p>";
        $output .= "<p><a href='/generalTask'>Go to Admin Dashboard to do Auto-Shuffle</a></p>";
    } else {
        foreach ($assignments as $assignment) {
            $output .= "<h3>Category: {$assignment->category->name}</h3>";
            $output .= "<p>Status: " . ($assignment->status ?? 'NO STATUS') . "</p>";
            $output .= "<p>Members: {$assignment->assignmentMembers->count()}</p>";

            if ($assignment->assignmentMembers->count() > 0) {
                $output .= "<ul>";
                foreach ($assignment->assignmentMembers as $member) {
                    if ($member->student) {
                        $coord = $member->is_coordinator ? " (COORDINATOR)" : "";
                        $u = $member->student;
                        $name = trim(($u->user_fname ?? '') . ' ' . ($u->user_lname ?? '')) ?: ($u->name ?? 'Unknown');
                        $batch = optional($u->studentDetail)->batch ?? ($u->batch ?? 'Unknown');
                        $output .= "<li>{$name} - Batch {$batch}{$coord}</li>";
                    }
                }
                $output .= "</ul>";
            }
            $output .= "<hr>";
        }
    }

    $output .= "<h2>Fix Status</h2>";
    $output .= "<p><a href='/fix-assignment-status' style='background: green; color: white; padding: 10px; text-decoration: none;'>Click here to fix assignment status</a></p>";

    return $output;
});

// Debug route to inspect rotation_schedules on the Login DB (temporary)
Route::get('/debug/rotation-schedules', [TaskController::class, 'debugLoginRotation']);

    // API: get latest persisted rotation schedule for a room (admin-only)
    Route::get('/api/rotation-schedule/{room}', [TaskController::class, 'getLatestRotationSchedule']);

// Route to fix assignment status
Route::get('/fix-assignment-status', function() {
    // Get the latest assignment for each category
    $categories = \App\Models\Category::all();
    $fixed = 0;

    foreach ($categories as $category) {
        // Get the latest assignment for this category
        $latestAssignment = \App\Models\Assignment::where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestAssignment) {
            // Mark all assignments for this category as 'previous'
            \App\Models\Assignment::where('category_id', $category->id)
                ->update(['status' => 'previous']);

            // Mark the latest assignment as 'current'
            $latestAssignment->update(['status' => 'current']);
            $fixed++;
        }
    }

    return "<h1>Status Fixed!</h1><p>Fixed {$fixed} assignments to 'current' status.</p><p><a href='/debug-assignments'>Check Debug Again</a></p><p><a href='/test-student-general-task'>Test Student View</a></p>";
});


// Test route to verify role-based access control
Route::get('/test-role-access', function() {
    if (!Auth::check()) {
        return 'Not logged in';
    }

    $user = Auth::user();
    $role = $user->user_role;

    $output = "<h1>Role-Based Access Test</h1>";
    $output .= "<p><strong>Current User:</strong> {$user->user_fname} {$user->user_lname}</p>";
    $output .= "<p><strong>Role:</strong> {$role}</p>";
    $output .= "<hr>";

    if ($role === 'student') {
        $output .= "<p>✅ You are a student. You should only access:</p>";
        $output .= "<ul>";
        $output .= "<li><a href='/StudentDashboard'>Student Dashboard</a> ✅</li>";
        $output .= "<li><a href='/student-room-task-history'>Student Room Task History</a> ✅</li>";
        $output .= "</ul>";
        $output .= "<p>❌ You should NOT be able to access:</p>";
        $output .= "<ul>";
        $output .= "<li><a href='/AdminDashboard'>Admin Dashboard</a> ❌ (should redirect)</li>";
        $output .= "</ul>";
    } elseif (in_array($role, ['educator', 'inspector'])) {
        $output .= "<p>✅ You are an admin ({$role}). You should only access:</p>";
        $output .= "<ul>";
        $output .= "<li><a href='/AdminDashboard'>Admin Dashboard</a> ✅</li>";
        $output .= "</ul>";
        $output .= "<p>❌ You should NOT be able to access:</p>";
        $output .= "<ul>";
        $output .= "<li><a href='/StudentDashboard'>Student Dashboard</a> ❌ (should redirect)</li>";
        $output .= "</ul>";
    }

    return $output;
})->middleware('auth')->name('test.role.access');

// Student routes (student only)
Route::middleware(['auth'])->group(function () {
    // Restrict student dashboard to students only
    Route::get('/StudentDashboard', function(Request $request) {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        // Use the controller so the view receives student-specific tasks and data
        return app(\App\Http\Controllers\MainStudentDASHController::class)->index($request);
    })->name('mainstudentdash');

    Route::get('/StudentDashboard.student-dashboard', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        return app(MainStudentDASHController::class)->index(request());
    })->name('StudentSDashboard.student.dashboard');

    Route::get('/StudentGenTaskDashboard', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        try {
            // Student-specific controller logic that fetches ALL categories
            \App\Models\AssignmentMember::cleanExpiredComments();

            // Fetch ALL CATEGORIES (both main and sub-categories) for students
            $categories = \App\Models\Category::with(['assignments' => function($query) {
                $query->where('status', 'current');
            }, 'assignments.assignmentMembers.student', 'subCategories.assignments' => function($query) {
                $query->where('status', 'current');
            }, 'subCategories.assignments.assignmentMembers.student'])
            ->get(); // Get all categories for students

            // Get students from Login database properly
            $users = \App\Models\PNUser::where('user_role', 'student')->get();
            $details = \App\Models\StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');

            $students = $users->map(function($u) use ($details) {
                $d = $details->get($u->user_id);
                // Normalize gender values to 'Male' or 'Female'
                $rawGender = isset($u->gender) ? trim($u->gender) : null;
                $gender = null;
                if (!empty($rawGender)) {
                    $g = strtolower($rawGender);
                    if (in_array($g, ['m', 'male'])) $gender = 'Male';
                    elseif (in_array($g, ['f', 'female'])) $gender = 'Female';
                }
                return (object) [
                    'id' => $u->user_id,
                    'name' => trim(($u->user_fname ?? '') . ' ' . ($u->user_lname ?? '')),
                    'student_code' => $d->student_id ?? null,
                    'gender' => $gender,
                    'batch' => $d->batch ?? null
                ];
            });

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

            $assignments = \App\Models\Assignment::with(['category', 'assignmentMembers.student'])
                ->where('status', 'current')
                ->orderBy('start_date', 'desc')
                ->get();

            $assignmentHistory = \App\Models\Assignment::with(['category', 'assignmentMembers.student'])
                ->orderBy('status', 'asc') // current first, then previous
                ->orderBy('id', 'desc') // newest first within same status
                ->get();

            // Use the same GeneralTaskController as admin for consistency
            return app(\App\Http\Controllers\GeneralTaskController::class)->index();
        } catch (\Exception $e) {
            \Log::error('Student General Task Route Error: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    })->name('student.general.task');
    Route::get('/StudentsDashboard.dining-form', function() {
        return view('StudentsDashboard.dining-form');
    })->name('dining.form');
    Route::get('/StudentsDashboard.groundfloor-form', function() {
        return view('StudentsDashboard.groundfloor-form');
    })->name('groundfloor.form');
    Route::get('/StudentsDashboard.offices-form', function() {
        return view('StudentsDashboard.offices-form');
    })->name('offices.form');
    Route::get('/StudentsDashboard.garbage-form', function() {
        return view('StudentsDashboard.garbage-form');
    })->name('garbage.form');
    Route::get('/StudentsDashboard.student-room-task-history', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        return app(MainStudentDASHController::class)->roomTaskHistory();
    })->name('StudentsDashboard.student.room.task.history');

    Route::get('/student-room-tasking', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        return app(MainStudentDASHController::class)->roomTaskHistory();
    })->name('student.room.tasking');

    Route::get('/student-room-task-history', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['student'])) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
        }
        return app(MainStudentDASHController::class)->taskHistory(request());
    })->name('task.history');

    Route::get('/StudentsDashboard.task-history', [MainStudentDASHController::class, 'taskHistory'])->name('StudentsDashboard.task.history');
    Route::get('/StudentsDashboard.reports', function() {
        $user = auth()->user();
        if (in_array($user->user_role, ['educator', 'inspector'])) {
            // Redirect admin to the admin reports page (Damage Reports)
            return redirect()->route('reports.index');
        }
        
        // Redirect students to dashboard since tasking reports are removed
        return redirect()->route('mainstudentdash')->with('info', 'Tasking reports have been moved to admin dashboard.');
    })->name('StudentsDashboard.reports');

    // Student My Tasks - View assigned tasks (STUDENT ONLY)
    Route::get('/student/my-tasks', [StudentTaskController::class, 'myTasks'])->name('student.my-tasks');
});

// Shared routes for both admin and student
Route::middleware(['auth'])->group(function () {
    Route::get('/homepage', function () {
        // Always show the landing page (homepage) after login, regardless of role
        return view('homepage');
    })->name('homepage');

    // Task status route - needed for both admin and student views
    Route::post('/get-task-statuses', [TaskController::class, 'getTaskStatuses']);

    // Temporary test page for dynamic scheduler (admin only)
    Route::get('/task-scheduler', function() {
        $user = auth()->user();
        if (!in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('mainstudentdash');
        }
        return view('task-scheduler');
    })->name('task.scheduler');

    // Room task route - needed for both admin and student views
    Route::get('/roomtask/{room?}', [TaskController::class, 'roomtask'])->name('roomtask');

    // Manage Room Tasks - dedicated page for creating and assigning reusable room tasks
    Route::get('/manage_roomtask', function() {
        // Access control: require auth (this route is inside the auth middleware group)
        // Attempt to gather known room numbers from common sources so the dropdown is populated.
        $rooms = [];
        try {
            // 1) Prefer a dedicated rooms table with room_number column
            if (\Schema::hasTable('rooms') && \Schema::hasColumn('rooms', 'room_number')) {
                $rooms = array_values(array_filter(array_unique(\DB::table('rooms')->pluck('room_number')->toArray())));
            }
        } catch (\Throwable $e) {
            // ignore and continue
            $rooms = [];
        }

        try {
            // 2) If still empty, try a 'room' table or common alternative columns
            if (empty($rooms)) {
                if (\Schema::hasTable('room') && \Schema::hasColumn('room', 'room_number')) {
                    $rooms = array_values(array_filter(array_unique(\DB::table('room')->pluck('room_number')->toArray())));
                } elseif (\Schema::hasTable('rooms') && \Schema::hasColumn('rooms', 'number')) {
                    $rooms = array_values(array_filter(array_unique(\DB::table('rooms')->pluck('number')->toArray())));
                }
            }
        } catch (\Throwable $e) {
            // ignore and continue
        }

        try {
            // 3) Always include any room numbers already present in the persistent roomtask table
            if (\Schema::hasTable('roomtask')) {
                $fromTasks = \DB::table('roomtask')->whereNotNull('room_number')->pluck('room_number')->toArray();
                $rooms = array_values(array_unique(array_merge($rooms, $fromTasks)));
            }
        } catch (\Throwable $e) {
            // If default connection doesn't have the table, try the login connection
            try {
                if (\DB::connection('login')->getSchemaBuilder()->hasTable('roomtask')) {
                    $fromTasks = \DB::connection('login')->table('roomtask')->whereNotNull('room_number')->pluck('room_number')->toArray();
                    $rooms = array_values(array_unique(array_merge($rooms, $fromTasks)));
                }
            } catch (\Throwable $ee) {
                // give up
            }
        }

        // Normalize to strings and sort
        $rooms = array_values(array_map('strval', $rooms));
        sort($rooms, SORT_NATURAL | SORT_FLAG_CASE);

        return view('manage_roomtask', ['rooms' => $rooms]);
    })->name('manage_roomtask');

    // Persist Manage Room Tasks into existing `roomtask` table (controller below)
    Route::post('/manage-roomtask/apply', [App\Http\Controllers\ManageRoomTaskController::class, 'apply'])->name('manage-roomtask.apply');
    // Mark manage-roomtask templates inactive so they cannot be applied again
    Route::post('/manage-roomtask/delete-template', [App\Http\Controllers\ManageRoomTaskController::class, 'deleteTemplate'])->name('manage-roomtask.delete-template');
    Route::post('/manage-roomtask/delete-all-applied', [App\Http\Controllers\RoomTaskController::class, 'deleteAllApplied'])->name('manage-roomtask.delete-all-applied');

    // Route for Tasking Hub card - redirects based on user role
    Route::get('/tasking-hub', function () {
        $user = auth()->user();
        if (in_array($user->user_role, ['educator', 'inspector'])) {
            return redirect()->route('dashboard'); // AdminDashboard
        } elseif (in_array($user->user_role, ['student', 'coordinator'])) {
            return redirect()->route('mainstudentdash'); // StudentDashboard (coordinators use student dashboard)
        }
        // Fallback to login if role is not recognized
        return redirect()->route('auth.login');
    })->name('tasking.hub');

    // Kitchen Assignment Routes
    Route::post('/save-kitchen-assignments', [AssignmentController::class, 'saveKitchenAssignments'])->name('save.kitchen.assignments');
    Route::get('/get-kitchen-assignments', [AssignmentController::class, 'getKitchenAssignments'])->name('get.kitchen.assignments');
    
    // Get Assignment Routes for all categories
    Route::get('/get-dishwashing-assignments', [AssignmentController::class, 'getDishwashingAssignments'])->name('get.dishwashing.assignments');
    Route::get('/get-dining-assignments', [AssignmentController::class, 'getDiningAssignments'])->name('get.dining.assignments');
    Route::get('/get-office-assignments', [AssignmentController::class, 'getOfficeAssignments'])->name('get.office.assignments');
    Route::get('/get-conference-assignments', [AssignmentController::class, 'getConferenceAssignments'])->name('get.conference.assignments');
    Route::get('/get-ground-assignments', [AssignmentController::class, 'getGroundAssignments'])->name('get.ground.assignments');
    Route::get('/get-waste-assignments', [AssignmentController::class, 'getWasteAssignments'])->name('get.waste.assignments');
    
    // All Assignment Save Routes
    Route::post('/save-dishwashing-assignments', [AssignmentController::class, 'saveDishwashingAssignments'])->name('save.dishwashing.assignments');
    Route::post('/save-dining-assignments', [AssignmentController::class, 'saveDiningAssignments'])->name('save.dining.assignments');
    Route::post('/save-office-assignments', [AssignmentController::class, 'saveOfficeAssignments'])->name('save.office.assignments');
    Route::post('/save-conference-assignments', [AssignmentController::class, 'saveConferenceAssignments'])->name('save.conference.assignments');
    Route::post('/save-ground-assignments', [AssignmentController::class, 'saveGroundAssignments'])->name('save.ground.assignments');
    Route::post('/save-waste-assignments', [AssignmentController::class, 'saveWasteAssignments'])->name('save.waste.assignments');
    
    // Task-Specific Assignment Routes - ensures only assigned students are shown
    Route::get('/get-task-assignments', [AssignmentController::class, 'getTaskSpecificAssignments'])->name('get.task.assignments');
    Route::post('/update-task-assignment', [AssignmentController::class, 'updateTaskAssignment'])->name('update.task.assignment');
});

// Mark Day as Complete - Save assignments (moved outside API group for web access)
Route::post('/api/mark-day-complete', [AssignmentController::class, 'markDayComplete'])->name('mark.day.complete');

// Simple direct route for My Tasks (no middleware conflicts)
Route::get('/my-tasks-simple', function() {
    return view('StudentsDashboard.my-tasks');
})->name('my.tasks.simple');


// Test route to check current room assignments
Route::get('/test-current-assignments', function() {
    $assignments = \App\Models\RoomAssignment::orderBy('room_number')
        ->orderBy('assignment_order')
        ->get()
        ->groupBy('room_number')
        ->map(function($roomAssignments) {
            return [
                'count' => $roomAssignments->count(),
                'capacity' => $roomAssignments->first()->room_capacity ?? 'unknown',
                'students' => $roomAssignments->pluck('student_name')->toArray()
            ];
        });

    return response()->json([
        'total_assignments' => \App\Models\RoomAssignment::count(),
        'rooms' => $assignments,
        'sample_room_201' => $assignments['201'] ?? 'No assignments'
    ]);
});

Route::middleware(['auth'])->group(function () {
    // Attendance routes removed — attendance is handled via AttendanceProxyController and middleware
    
    // Category hierarchy routes
    Route::get('/api/categories/hierarchy', [\App\Http\Controllers\CategoryController::class, 'getHierarchy']);
    Route::get('/api/categories/main-areas', [\App\Http\Controllers\CategoryController::class, 'getMainAreas']);
    Route::get('/api/categories/sub-areas-with-checklists', [\App\Http\Controllers\CategoryController::class, 'getSubAreasWithChecklists']);
    
    // Student Task Status routes
    Route::prefix('api/student/task-status')->group(function () {
        Route::post('/update', [\App\Http\Controllers\StudentTaskStatusController::class, 'updateStatus']);
        Route::get('/get', [\App\Http\Controllers\StudentTaskStatusController::class, 'getStatus']);
        Route::get('/all', [\App\Http\Controllers\StudentTaskStatusController::class, 'getAllStatuses']);
    });
    
    // Weekly Schedule Save API
    Route::post('/api/save-weekly-schedule', function(Request $request) {
        try {
            $data = $request->validate([
                'category' => 'required|string',
                'assignments' => 'required|array',
                'week_start' => 'required|date',
                'created_by' => 'required|string'
            ]);
            
            // Save to database (you can create a WeeklySchedule model later)
            $scheduleData = [
                'id' => uniqid(),
                'category' => $data['category'],
                'assignments' => $data['assignments'],
                'week_start' => $data['week_start'],
                'created_by' => $data['created_by'],
                'created_at' => now()->toISOString(),
                'status' => 'active'
            ];
            
            // Save to storage file
            $filePath = storage_path('app/weekly_schedules.json');
            $existingSchedules = [];
            
            if (file_exists($filePath)) {
                $existingSchedules = json_decode(file_get_contents($filePath), true) ?? [];
            }
            
            $existingSchedules = array_filter($existingSchedules, function($schedule) use ($data) {
                return !($schedule['category'] === $data['category'] && $schedule['week_start'] === $data['week_start']);
            });
            
            // Add new schedule
            $existingSchedules[] = $scheduleData;
            
            // Save back to file
            file_put_contents($filePath, json_encode($existingSchedules, JSON_PRETTY_PRINT));
            
            return response()->json([
                'success' => true,
                'message' => 'Weekly schedule saved successfully',
                'schedule_id' => $scheduleData['id']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving schedule: ' . $e->getMessage()
            ], 500);
        }
    });

    // Task Management Routes
    Route::prefix('task-management')->group(function () {
        // Get tasks for a category
        Route::get('/category/{categoryId}/tasks', [App\Http\Controllers\TaskManagementController::class, 'getTasksForCategory']);
        
        // Save task definition
        Route::post('/tasks', [App\Http\Controllers\TaskManagementController::class, 'saveTaskDefinition']);
        
        // Delete task definition
        Route::delete('/tasks/{taskId}', [App\Http\Controllers\TaskManagementController::class, 'deleteTaskDefinition']);
        
        // Assign tasks to students
        Route::post('/assign-tasks', [App\Http\Controllers\TaskManagementController::class, 'assignTasks']);
        
        // Get task assignments
        Route::get('/assignments/{assignmentId}/{date}', [App\Http\Controllers\TaskManagementController::class, 'getTaskAssignments']);
        
        // Get student's task assignments
        Route::get('/student/{studentId}/tasks/{date?}', [App\Http\Controllers\TaskManagementController::class, 'getStudentTasks']);
        
        // Get all task assignments for display
        Route::get('/all-assignments/{date?}', [App\Http\Controllers\TaskManagementController::class, 'getAllTaskAssignments']);
    });
    
    // Manage Tasks page (separate page for testing)
    Route::get('/manage-tasks', function() {
        return view('manage-tasks');
    })->name('manage.tasks');
    
    // Generated Schedule Routes (Admin saves, Student views)
    Route::post('/api/save-generated-schedule', function(Request $request) {
        try {
            $entries = $request->input('entries');
            $assignmentId = $request->input('assignment_id');
            $categoryName = $request->input('category_name');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $rotationFrequency = $request->input('rotation_frequency', 'Daily');

            \Log::info('=== SAVE SCHEDULE REQUEST ===', [
                'assignment_id' => $assignmentId,
                'category_name' => $categoryName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'entries_count' => $entries ? count($entries) : 0,
                'entries' => $entries,
            ]);

            if (!$entries || count($entries) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No entries provided',
                    'received_entries' => $entries,
                ], 400);
            }

            // Save each schedule entry using DB facade
            $savedEntries = 0;
            foreach ($entries as $entry) {
                try {
                    $scheduleId = \DB::table('generated_schedules')->insertGetId([
                        'assignment_id' => $assignmentId,
                        'category_name' => $categoryName,
                        'schedule_date' => $entry['schedule_date'] ?? null,
                        'student_id' => $entry['student_id'] ?? null,
                        'student_name' => $entry['student_name'] ?? 'Unknown',
                        'task_title' => $entry['task_title'] ?? 'Task',
                        'task_description' => $entry['task_description'] ?? 'Task assigned from generated schedule',
                        'batch' => $entry['batch'] ?? null,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'rotation_frequency' => $rotationFrequency,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $savedEntries++;
                    \Log::info('Saved schedule entry', ['id' => $scheduleId, 'student' => $entry['student_name']]);
                } catch (\Exception $entryError) {
                    \Log::error('Error saving individual schedule entry: ' . $entryError->getMessage(), [
                        'entry' => $entry,
                        'error' => $entryError->getMessage(),
                    ]);
                }
            }

            \Log::info('Schedule saved successfully', ['saved_entries' => $savedEntries]);

            return response()->json([
                'success' => true,
                'message' => "Schedule saved successfully for students to view ({$savedEntries} entries saved)",
                'saved_entries' => $savedEntries,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving generated schedule: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving schedule: ' . $e->getMessage(),
            ], 500);
        }
    })->name('api.save.generated.schedule');
    
    // Get student's generated schedules (prioritize batch, then name, then fallback)
    Route::get('/api/student-generated-schedules', function(Request $request) {
        try {
            // Test if the model exists
            if (!class_exists('App\Models\GeneratedSchedule')) {
                return response()->json([
                    'success' => false,
                    'schedules' => [],
                    'message' => 'GeneratedSchedule model not found',
                    'debug' => 'Model class does not exist'
                ]);
            }

            $student = auth()->user();
            $studentId = $student->user_id ?? null;
            $studentName = trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''));
            $studentDetail = \App\Models\StudentDetail::where('user_id', $studentId)->first();
            $studentBatch = $studentDetail->batch ?? null;

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'schedules' => [],
                    'message' => 'Unable to identify student',
                ]);
            }

            // Check if table exists and get schedules for this specific student
            try {
                // Check if table exists first
                $tables = \DB::select('SHOW TABLES LIKE "generated_schedules"');
                if (empty($tables)) {
                    return response()->json([
                        'success' => false,
                        'schedules' => [],
                        'message' => 'generated_schedules table does not exist',
                        'debug' => 'Table not found in database'
                    ]);
                }

                // Base query: schedules that belong only to this student (by id or name+batch)
                $baseQuery = \DB::table('generated_schedules')
                    ->where(function($q) use ($studentId, $studentName, $studentBatch) {
                        // Primary: match by student_id
                        $q->where('student_id', $studentId);

                        // Fallback: match by student_name (and optionally batch)
                        $q->orWhere(function($q2) use ($studentName, $studentBatch) {
                            $q2->where('student_name', $studentName);

                            if ($studentBatch) {
                                $q2->where(function($q3) use ($studentBatch) {
                                    $q3->whereNull('batch')
                                        ->orWhere('batch', $studentBatch);
                                });
                            }
                        });
                    });

                // Prefer schedules coming from the generated-schedule pipeline
                $generatedSchedules = (clone $baseQuery)
                    ->where(function($q) {
                        $q->whereNull('rotation_frequency')
                          ->orWhere('rotation_frequency', '!=', 'Manual');
                    })
                    ->orderBy('schedule_date', 'asc')
                    ->get();

                if ($generatedSchedules->count() > 0) {
                    // Use only generated schedules if they exist
                    $schedules = $generatedSchedules;
                    $scheduleSource = 'generated';
                } else {
                    // Fallback: include manual bridge entries from markDayComplete
                    $schedules = $baseQuery
                        ->orderBy('schedule_date', 'asc')
                        ->get();
                    $scheduleSource = 'manual_or_legacy';
                }
            } catch (\Exception $dbError) {
                return response()->json([
                    'success' => false,
                    'schedules' => [],
                    'message' => 'Database query failed: ' . $dbError->getMessage(),
                    'debug' => $dbError->getMessage()
                ]);
            }
            
            \Log::info('Student schedules - returning schedules', [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'batch' => $studentBatch,
                'schedules_found' => $schedules->count(),
                'source' => $scheduleSource ?? 'unknown',
            ]);

            // Get all schedules for debugging
            $allSchedules = \DB::table('generated_schedules')->orderBy('schedule_date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'schedules' => $schedules,
                'student_name' => $studentName,
                'student_id' => $studentId,
                'batch' => $studentBatch,
                'message' => 'Schedules retrieved successfully',
                'debug' => [
                    'schedules_count' => $schedules->count(),
                    'total_in_db' => $allSchedules->count(),
                    'all_schedules' => $allSchedules->take(5), // Show first 5 for debugging
                    'student_batch' => $studentBatch,
                    'student_name_search' => $studentName,
                    'source' => $scheduleSource ?? 'unknown',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error retrieving student schedules: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'schedules' => [],
                'message' => 'Error retrieving schedules: ' . $e->getMessage(),
            ], 500);
        }
    })->name('api.student.generated.schedules');
    
    // Update task status for student's schedule
    Route::put('/api/update-task-status/{scheduleId}', function(Request $request, $scheduleId) {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $taskStatus = $request->input('task_status', 'pending');
            
            // Validate status
            $validStatuses = ['pending', 'in_progress', 'completed'];
            if (!in_array($taskStatus, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid task status'
                ], 400);
            }

            // Get the schedule record to extract details
            $schedule = \DB::table('generated_schedules')
                ->where('id', $scheduleId)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            // Update the generated schedule record
            \DB::table('generated_schedules')
                ->where('id', $scheduleId)
                ->update([
                    'task_status' => $taskStatus,
                    'updated_at' => now()
                ]);

            // Create a task submission record for admin to see
            $studentName = $schedule->student_name ?? $user->name ?? 'Unknown Student';
            $taskTitle = $schedule->task_title ?? 'Task';
            $taskDescription = $schedule->task_description ?? 'Task from generated schedule';
            
            // Map status to display format
            $statusDisplay = match($taskStatus) {
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                default => 'Pending'
            };

            // Create submission record
            try {
                // PNUser model uses 'user_id' as primary key, not 'id'
                $userId = $user->user_id ?? $user->id ?? null;
                
                if (!$userId) {
                    throw new \Exception('User ID not found. User object: ' . json_encode($user->toArray()));
                }

                \Log::info('Creating task submission with user_id:', [
                    'user_id' => $userId,
                    'user_object' => [
                        'id' => $user->id ?? null,
                        'user_id' => $user->user_id ?? null,
                        'name' => $user->name ?? null,
                        'user_fname' => $user->user_fname ?? null,
                        'user_lname' => $user->user_lname ?? null
                    ]
                ]);

                $submission = \App\Models\TaskSubmission::create([
                    'user_id' => $userId,
                    'task_category' => $schedule->batch ?? 'General',
                    'description' => "Task Status Update: {$taskTitle} - Status: {$statusDisplay}\n\nStudent: {$studentName}\nDate: {$schedule->schedule_date}\nDescription: {$taskDescription}",
                    'status' => 'pending',
                    'admin_notes' => "Task status submitted by {$studentName} on " . now()->format('M d, Y h:i A') . " - Status: {$statusDisplay}"
                ]);

                \Log::info('Task submission created successfully', [
                    'submission_id' => $submission->id,
                    'user_id' => $userId,
                    'student_name' => $studentName,
                    'status' => $statusDisplay
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Task status updated and submitted successfully',
                    'task_status' => $taskStatus,
                    'submission_id' => $submission->id
                ]);
            } catch (\Exception $submissionError) {
                \Log::error('Error creating task submission: ' . $submissionError->getMessage(), [
                    'user_id' => $user->id,
                    'schedule_id' => $scheduleId,
                    'trace' => $submissionError->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating task submission: ' . $submissionError->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating task status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating task status: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.update.task.status');
    
    // API endpoint to fetch task assignments overview
    Route::get('/api/task-assignments-overview', function() {
        try {
            $user = auth()->user();
            if (!in_array($user->user_role, ['educator', 'inspector'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Get all task submissions with status updates (all statuses, not just pending/valid)
            $submissions = \App\Models\TaskSubmission::whereIn('status', ['pending', 'valid', 'invalid'])
                ->orderByDesc('created_at')
                ->get();

            \Log::info('Fetching task submissions for overview', [
                'total_submissions' => $submissions->count(),
                'statuses' => $submissions->pluck('status')->unique()->toArray()
            ]);

            $assignments = [];
            foreach ($submissions as $submission) {
                try {
                    // Extract student info from description
                    preg_match('/Student: (.+?)\n/', $submission->description, $studentMatch);
                    $studentName = $studentMatch[1] ?? 'Unknown';

                    // Extract status from description
                    preg_match('/Status: (.+?)\n/', $submission->description, $statusMatch);
                    $status = $statusMatch[1] ?? 'Pending';

                    // Extract date from description
                    preg_match('/Date: (.+?)\n/', $submission->description, $dateMatch);
                    $date = $dateMatch[1] ?? date('Y-m-d');

                    // Get student details - try multiple lookup methods
                    $student = null;
                    
                    // First try by user_id from submission (most reliable)
                    if ($submission->user_id) {
                        $student = \App\Models\PNUser::where('user_id', $submission->user_id)->first();
                    }
                    
                    // If not found, try by full name (first + last)
                    if (!$student) {
                        $student = \App\Models\PNUser::where(\DB::raw("CONCAT(user_fname, ' ', user_lname)"), $studentName)->first();
                    }

                    $studentCode = $student ? ($student->user_id ?? 'N/A') : 'N/A';

                    // Map category to main area
                    $mainAreaMap = [
                        'Kitchen' => 'Kitchen',
                        'Dishwashing' => 'Kitchen',
                        'Dining' => 'Dining',
                        'Offices' => 'Offices',
                        'Garbage' => 'Grounds',
                        'Maintenance' => 'Maintenance',
                    ];
                    $mainArea = $mainAreaMap[$submission->task_category] ?? $submission->task_category;

                    $assignments[] = [
                        'id' => $submission->id,
                        'student' => $studentName,
                        'studentCode' => $studentCode,
                        'mainArea' => $mainArea,
                        'subArea' => $submission->task_category,
                        'tasks' => '0/0',
                        'progress' => $status === 'Completed' ? 100 : ($status === 'In Progress' ? 50 : 0),
                        'status' => $status,
                        'statusType' => strtolower(str_replace(' ', '-', $status)),
                        'submittedAt' => $submission->created_at->format('M d, Y h:i A'),
                        'adminNotes' => $submission->admin_notes
                    ];
                } catch (\Exception $itemError) {
                    \Log::warning('Error processing submission item: ' . $itemError->getMessage(), [
                        'submission_id' => $submission->id,
                        'error' => $itemError->getMessage()
                    ]);
                    // Skip this submission and continue with next
                    continue;
                }
            }

            \Log::info('Task assignments overview response', [
                'total_assignments' => count($assignments),
                'assignments' => $assignments
            ]);

            return response()->json([
                'success' => true,
                'assignments' => $assignments,
                'total' => count($assignments)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching task assignments overview: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching assignments: ' . $e->getMessage()
            ], 500);
        }
    })->name('api.task.assignments.overview');
    
    // Debug route to check database data
    Route::get('/debug-data', function() {
        $categories = \App\Models\Category::all();
        $submissions = \App\Models\TaskSubmission::all();
        
        $output = "<h2>Categories in Database:</h2>";
        foreach($categories as $cat) {
            $output .= "ID: {$cat->id} - Name: {$cat->name}<br>";
        }
        
        $output .= "<h2>Task Submissions:</h2>";
        foreach($submissions as $sub) {
            $output .= "ID: {$sub->id} - task_category: {$sub->task_category} - description: {$sub->description}<br>";
        }
        
        return $output;
    });

    // Debug route to check saved schedules
    Route::get('/debug-schedules', function() {
        try {
            // Check if table exists
            $tables = \DB::select('SHOW TABLES LIKE "generated_schedules"');
            if (empty($tables)) {
                return "<h2>generated_schedules table does not exist</h2><p>Creating table now...</p>" . 
                       createGeneratedSchedulesTable();
            }
            
            $schedules = \DB::table('generated_schedules')->orderBy('schedule_date', 'desc')->get();
            
            $output = "<h2>All Saved Schedules ({$schedules->count()})</h2>";
            $output .= "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            $output .= "<tr><th>ID</th><th>Student Name</th><th>Batch</th><th>Date</th><th>Task</th><th>Category</th></tr>";
            
            foreach ($schedules as $schedule) {
                $output .= "<tr>";
                $output .= "<td>{$schedule->id}</td>";
                $output .= "<td>{$schedule->student_name}</td>";
                $output .= "<td>{$schedule->batch}</td>";
                $output .= "<td>{$schedule->schedule_date}</td>";
                $output .= "<td>{$schedule->task_title}</td>";
                $output .= "<td>{$schedule->category_name}</td>";
                $output .= "</tr>";
            }
            
            $output .= "</table>";
            return $output;
            
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    // Helper function to create the table
    function createGeneratedSchedulesTable() {
        try {
            \DB::statement("
                CREATE TABLE IF NOT EXISTS generated_schedules (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    assignment_id BIGINT UNSIGNED NULL,
                    category_name VARCHAR(255) NULL,
                    schedule_date DATE NULL,
                    student_id BIGINT UNSIGNED NULL,
                    student_name VARCHAR(255) NULL,
                    task_title VARCHAR(255) NULL,
                    task_description TEXT NULL,
                    batch VARCHAR(50) NULL,
                    start_date DATE NULL,
                    end_date DATE NULL,
                    rotation_frequency VARCHAR(50) NULL,
                    task_status VARCHAR(50) NULL DEFAULT 'pending',
                    schedule_data JSON NULL,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            return "<p style='color: green;'>Table created successfully!</p>";
        } catch (\Exception $e) {
            return "<p style='color: red;'>Error creating table: " . $e->getMessage() . "</p>";
        }
    }

    // ... (rest of the code remains the same)
    // Test route to verify everything works
    Route::get('/test-schedule-api', function() {
        return response()->json([
            'message' => 'Schedule API is working',
            'endpoints' => [
                'POST /api/save-generated-schedule' => 'Save schedule entries',
                'GET /api/student-generated-schedules' => 'Get student schedules',
                'GET /debug-schedules' => 'View all schedules in database',
            ],
            'test_save_url' => '/api/save-generated-schedule',
            'test_get_url' => '/api/student-generated-schedules',
        ]);
    });

    // Simple test to insert a schedule entry directly
    Route::get('/test-insert-schedule', function() {
        try {
            $result = \DB::table('generated_schedules')->insertGetId([
                'student_name' => 'Test Student',
                'batch' => '2025',
                'schedule_date' => now()->toDateString(),
                'task_title' => 'Test Task',
                'task_description' => 'This is a test entry',
                'category_name' => 'Test Category',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'rotation_frequency' => 'Daily',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Test entry inserted successfully',
                'id' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    });
});
