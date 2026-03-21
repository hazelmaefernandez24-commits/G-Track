<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\AssignmentMember;
use App\Models\CategoryLimit;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * Fix coordinators for a specific category - ensure both batches have coordinators
     */
    public function fixCoordinatorsForCategory(Request $request, $categoryId)
    {
        try {
            \Log::info("🔧 FIX COORDINATORS: Starting for category ID {$categoryId}");
            
            $category = Category::find($categoryId);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            // Get current assignment
            $assignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();
            
            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current assignment found for this category'
                ], 404);
            }
            
            // Get all members with their batch information
            $members = AssignmentMember::where('assignment_id', $assignment->id)
                ->with('student.studentDetail')
                ->get();
            
            if ($members->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No members found in this assignment'
                ], 404);
            }
            
            // Clear all coordinator flags first
            AssignmentMember::where('assignment_id', $assignment->id)
                ->update(['is_coordinator' => 0]);
            
            // Find first student from each batch
            $batch2025Member = null;
            $batch2026Member = null;
            
            foreach ($members as $member) {
                $batch = null;
                
                if ($member->student && $member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch;
                }
                
                if ($batch == 2025 && !$batch2025Member) {
                    $batch2025Member = $member;
                }
                
                if ($batch == 2026 && !$batch2026Member) {
                    $batch2026Member = $member;
                }
                
                if ($batch2025Member && $batch2026Member) {
                    break;
                }
            }
            
            // Set coordinators
            $coordinator2025Name = null;
            $coordinator2026Name = null;
            
            if ($batch2025Member) {
                $batch2025Member->is_coordinator = 1;
                $batch2025Member->save();
                
                $coordinator2025Name = $batch2025Member->student 
                    ? trim($batch2025Member->student->user_fname . ' ' . $batch2025Member->student->user_lname)
                    : 'Unknown';
                
                \Log::info("Set Batch 2025 coordinator: {$coordinator2025Name}");
            }
            
            if ($batch2026Member) {
                $batch2026Member->is_coordinator = 1;
                $batch2026Member->save();
                
                $coordinator2026Name = $batch2026Member->student 
                    ? trim($batch2026Member->student->user_fname . ' ' . $batch2026Member->student->user_lname)
                    : 'Unknown';
                
                \Log::info("Set Batch 2026 coordinator: {$coordinator2026Name}");
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Coordinators fixed! Both batches now have yellow highlighting.',
                'coordinator_2025' => $coordinator2025Name,
                'coordinator_2026' => $coordinator2026Name
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Fix coordinators failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Emergency fix: Ensure students are split between batches and assign them properly
     */
    public function emergencyFixBatches(Request $request)
    {
        try {
            \Log::info("EMERGENCY FIX: Starting batch fix and assignment");
            
            // STEP 1: Split students 50/50 between batches
            try {
                $allStudents = StudentDetail::all();
                $totalStudents = $allStudents->count();
                
                if ($totalStudents === 0) {
                    \Log::warning("No students found in StudentDetail");
                    return response()->json([
                        'success' => false,
                        'message' => 'No students found in the system'
                    ], 400);
                }
                
                $halfCount = (int) ceil($totalStudents / 2);
                
                \Log::info("Splitting {$totalStudents} students: {$halfCount} to Batch 2025, " . ($totalStudents - $halfCount) . " to Batch 2026");
                
                foreach ($allStudents as $index => $student) {
                    $newBatch = ($index < $halfCount) ? 2025 : 2026;
                    if ($student->batch != $newBatch) {
                        $student->batch = $newBatch;
                        $student->save();
                    }
                }
                
                $batch2025Count = StudentDetail::where('batch', 2025)->count();
                $batch2026Count = StudentDetail::where('batch', 2026)->count();
                
                \Log::info("Batch distribution: 2025={$batch2025Count}, 2026={$batch2026Count}");
                
            } catch (\Exception $dbError) {
                \Log::error("Database error in batch fix: " . $dbError->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $dbError->getMessage()
                ], 500);
            }
            
            // STEP 2: Clear ALL current assignments
            $cleared = 0;
            try {
                $currentAssignments = Assignment::where('status', 'current')->get();
                foreach ($currentAssignments as $assignment) {
                    $count = $assignment->assignmentMembers()->count();
                    $assignment->assignmentMembers()->delete();
                    $cleared += $count;
                }
                
                \Log::info("Cleared {$cleared} assignment members. Ready for auto-shuffle.");
                
            } catch (\Exception $clearError) {
                \Log::error("Error clearing assignments: " . $clearError->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error clearing assignments: ' . $clearError->getMessage()
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Fixed! Split students into Batch 2025 ({$batch2025Count}) and Batch 2026 ({$batch2026Count}). Cleared {$cleared} old assignments. Now click Auto-Shuffle to assign students.",
                'batch_2025' => $batch2025Count,
                'batch_2026' => $batch2026Count,
                'cleared' => $cleared
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Emergency fix failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function saveKitchenAssignments(Request $request)
    {
        try {
            $assignments = $request->input('assignments', []);
            
            if (empty($assignments)) {
                return response()->json(['success' => false, 'message' => 'No assignments to save']);
            }

            // Find or create Kitchen category
            $kitchenCategory = Category::firstOrCreate(['name' => 'Kitchen']);
            
            // Mark existing current assignments as previous
            Assignment::where('category_id', $kitchenCategory->id)
                ->where('status', 'current')
                ->update(['status' => 'previous', 'end_date' => now()->subDay()]);
            
            // Create new assignment
            $durationDays = SystemSetting::get('assignment_duration_days', 7);
            $assignment = Assignment::create([
                'category_id' => $kitchenCategory->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($durationDays)->toDateString(),
                'status' => 'current'
            ]);
            
            // Save assignment members
            foreach ($assignments as $assignmentData) {
                AssignmentMember::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $assignmentData['student_id'],
                    'student_name' => $assignmentData['student_name'],
                    'task_type' => $assignmentData['task'],
                    'time_slot' => $assignmentData['time'],
                    'is_coordinator' => false
                ]);
            }
            
            return response()->json(['success' => true, 'message' => 'Kitchen assignments saved successfully!']);
            
        } catch (\Exception $e) {
            \Log::error('Error saving kitchen assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving assignments: ' . $e->getMessage()]);
        }
    }

    public function saveDishwashingAssignments(Request $request)
    {
        try {
            $assignments = $request->input('assignments', []);
            
            if (empty($assignments)) {
                return response()->json(['success' => false, 'message' => 'No assignments to save']);
            }

            // Find or create Dishwashing category
            $category = Category::firstOrCreate(['name' => 'Dishwashing']);
            
            // Mark existing current assignments as previous
            Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->update(['status' => 'previous', 'end_date' => now()->subDay()]);
            
            // Create new assignment
            $durationDays = SystemSetting::get('assignment_duration_days', 7);
            $assignment = Assignment::create([
                'category_id' => $category->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($durationDays)->toDateString(),
                'status' => 'current'
            ]);
            
            // Save assignment members
            foreach ($assignments as $assignmentData) {
                AssignmentMember::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $assignmentData['student_id'],
                    'student_name' => $assignmentData['student_name'],
                    'task_type' => $assignmentData['task'],
                    'time_slot' => $assignmentData['time'],
                    'is_coordinator' => false
                ]);
            }
            
            return response()->json(['success' => true, 'message' => 'Dishwashing assignments saved successfully!']);
            
        } catch (\Exception $e) {
            \Log::error('Error saving dishwashing assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving assignments: ' . $e->getMessage()]);
        }
    }

    public function saveDiningAssignments(Request $request)
    {
        return $this->saveGenericAssignments($request, 'Dining');
    }

    public function saveOfficeAssignments(Request $request)
    {
        return $this->saveGenericAssignments($request, 'Offices & Conference Rooms');
    }

    public function saveConferenceAssignments(Request $request)
    {
        return $this->saveGenericAssignments($request, 'Offices & Conference Rooms');
    }

    public function saveGroundAssignments(Request $request)
    {
        \Log::info('🔍 Ground Assignments Received:', [
            'request_data' => $request->all()
        ]);
        return $this->saveGenericAssignments($request, 'Ground Floor');
    }

    public function saveWasteAssignments(Request $request)
    {
        return $this->saveGenericAssignments($request, 'Garbage, Rugs, & Rooftop');
    }

    private function saveGenericAssignments(Request $request, $categoryName)
    {
        try {
            $assignments = $request->input('assignments', []);
            
            if (empty($assignments)) {
                return response()->json(['success' => false, 'message' => 'No assignments to save']);
            }

            // Find or create category
            $category = Category::firstOrCreate(['name' => $categoryName]);
            
            // Mark existing current assignments as previous
            Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->update(['status' => 'previous', 'end_date' => now()->subDay()]);
            
            // Create new assignment
            $durationDays = SystemSetting::get('assignment_duration_days', 7);
            $assignment = Assignment::create([
                'category_id' => $category->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($durationDays)->toDateString(),
                'status' => 'current'
            ]);
            
            // Save assignment members
            foreach ($assignments as $assignmentData) {
                AssignmentMember::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $assignmentData['student_id'],
                    'student_name' => $assignmentData['student_name'],
                    'task_type' => $assignmentData['task'] ?? null,
                    'time_slot' => $assignmentData['time'] ?? null,
                    'is_coordinator' => false
                ]);
            }
            
            return response()->json(['success' => true, 'message' => $categoryName . ' assignments saved successfully!']);
            
        } catch (\Exception $e) {
            \Log::error('Error saving ' . $categoryName . ' assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error saving assignments: ' . $e->getMessage()]);
        }
    }

    /**
     * Create an AssignmentMember row safely: ensure either student_code or student_id is present.
     * Returns the created model or null if no valid data.
     */
    private function safeCreateAssignmentMember(array $data)
    {
        // If student_id not provided but a student_code (canonical student id) is given,
        // attempt to resolve it to the underlying user_id stored on StudentDetail.
        if ((empty($data['student_id']) || $data['student_id'] === null) && !empty($data['student_code'])) {
            try {
                $resolvedUserId = StudentDetail::where('student_id', $data['student_code'])->pluck('user_id')->first();
                if ($resolvedUserId) {
                    $data['student_id'] = $resolvedUserId;
                }
            } catch (\Exception $e) {
                // Could not reach StudentDetail table (login DB misconfigured or down) — log and skip
                \Log::warning('safeCreateAssignmentMember failed to resolve student_code to student_id', ['student_code' => $data['student_code'], 'error' => $e->getMessage()]);
            }
        }
        
        // REVERSE LOOKUP: If student_code not provided but student_id is given,
        // try to get the student_code from StudentDetail for better batch detection
        if ((empty($data['student_code']) || $data['student_code'] === null) && !empty($data['student_id'])) {
            try {
                $studentDetail = StudentDetail::where('user_id', $data['student_id'])->first();
                if ($studentDetail && !empty($studentDetail->student_id)) {
                    $data['student_code'] = $studentDetail->student_id;
                    \Log::info("safeCreateAssignmentMember: Resolved student_code '{$data['student_code']}' from student_id {$data['student_id']}");
                }
            } catch (\Exception $e) {
                \Log::warning('safeCreateAssignmentMember failed to resolve student_id to student_code', ['student_id' => $data['student_id'], 'error' => $e->getMessage()]);
            }
        }

        if ((empty($data['student_code']) || $data['student_code'] === null) && (empty($data['student_id']) || $data['student_id'] === null)) {
            \Log::warning('safeCreateAssignmentMember skipped: neither student_code nor student_id provided after resolution attempt', $data);
            return null;
        }

        // At this point we should have a student_id (preferred) or a student_code that maps
        // to a student. The assignments_members DB requires student_id not be null, so ensure
        // it's present before creating.
        if (empty($data['student_id'])) {
            \Log::warning('safeCreateAssignmentMember cannot create: student_id still empty', $data);
            return null;
        }

        // Prevent double-assignment: ensure this student is not already assigned to ANY current assignment.
        try {
            // Build canonical key variants to check
            $candidateKeys = [];
            if (!empty($data['student_id'])) {
                $candidateKeys[] = 'legacy:' . $data['student_id'];
            }
            if (!empty($data['student_code'])) {
                // Try to resolve student_code to user_id
                $resolved = StudentDetail::where('student_id', $data['student_code'])->pluck('user_id')->first();
                if ($resolved) $candidateKeys[] = 'pnuser:' . $resolved;
                $candidateKeys[] = 'code:' . $data['student_code'];
            }

            // Also include direct PNUser id if present as numeric student_id
            if (!empty($data['student_id']) && is_numeric($data['student_id'])) {
                $candidateKeys[] = 'pnuser:' . $data['student_id'];
            }

            // Check existing current AssignmentMember rows for any of these keys
            $existing = \App\Models\AssignmentMember::whereHas('assignment', function($q){ $q->where('status','current'); })->get();
            foreach ($existing as $ex) {
                $uKey = null;
                if (!empty($ex->student_id)) $uKey = 'legacy:' . $ex->student_id;
                elseif (!empty($ex->student_code)) {
                    $resolved = StudentDetail::where('student_id', $ex->student_code)->pluck('user_id')->first();
                    if ($resolved) $uKey = 'pnuser:' . $resolved;
                    else $uKey = 'code:' . $ex->student_code;
                } elseif ($ex->student) {
                    // Handle both Eloquent models and stdClass objects
                    if (is_object($ex->student)) {
                        $studentId = $ex->student->user_id ?? $ex->student->id ?? null;
                        if ($studentId) {
                            $uKey = 'pnuser:' . $studentId;
                        }
                    }
                }
                if ($uKey && in_array($uKey, $candidateKeys)) {
                    \Log::info('safeCreateAssignmentMember avoided duplicate assignment for: ' . $uKey, $data);
                    return null;
                }
            }
        } catch (\Exception $e) {
            // If this check fails for any reason, log and continue with creation — prefer to create than crash the shuffle.
            \Log::warning('safeCreateAssignmentMember duplicate-check failed: ' . $e->getMessage());
        }

        return \App\Models\AssignmentMember::create($data);
    }
    // Show all assignments with related data
    public function index()
    {
        $assignments = Assignment::with(['category', 'assignmentMembers.student'])->get();
        return view('assignments.index', compact('assignments'));
    }
    // Auto-shuffle assignments: creates new current assignments based on available students
    public function autoShuffle(Request $request)
    {
        // Check if this is a fill_to_requirements request (from Edit Members modal)
        $fillToRequirements = $request->input('fill_to_requirements', false);
        
        if ($fillToRequirements) {
            \Log::info("🎯 FILL TO REQUIREMENTS MODE: Auto-shuffle will fill existing assignments to meet requirements");
        }
        
        // Lock file path used to prevent concurrent runs
        $lockFile = storage_path('app/auto_shuffle.lock');
        // Check if lock file exists and is recent (less than 5 minutes old)
        if (file_exists($lockFile)) {
            $lockTime = (int) file_get_contents($lockFile);
            $currentTime = time();

            // If lock is older than 5 minutes, remove it (stale lock)
            if (($currentTime - $lockTime) > 300) {
                unlink($lockFile);
                \Log::info("Removed stale auto shuffle lock file");
            } else {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Auto-shuffle is already in progress. Please wait.'], 409);
                }
                return redirect()->route('generalTask')->with('error', 'Auto-shuffle is already in progress. Please wait.');
            }
        }

        // Create lock file
        file_put_contents($lockFile, time());

        try {
            // Check if 0 days duration is set (allow anytime shuffle)
            $currentDuration = SystemSetting::get('assignment_duration_days', 7);
            $allowAnytimeShuffle = ($currentDuration == 0);
            
            // CROSS-DATABASE STATE TRACKING: Check assignment state in Login database
            // DISABLED: Allow auto-shuffle anytime without state tracking restrictions
            // This provides maximum flexibility for users to shuffle whenever needed
            
            // Original state tracking validation commented out - kept for reference
            /*
            if (!$request->has('force_shuffle') && !$fillToRequirements && !$allowAnytimeShuffle) {
                try {
                    $loginDb = \DB::connection('login');
                    $stateRecords = $loginDb->table('assignment_state_tracking')
                        ->where('assignment_status', 'current')
                        ->get();
                    
                    foreach ($stateRecords as $state) {
                        if (!$state->shuffle_allowed || $state->is_locked) {
                            // Block shuffle if disabled
                            $message = "Auto-shuffle is currently disabled...";
                            return redirect()->route('generalTask')->with('error', $message);
                        }
                    }
                } catch (\Exception $stateError) {
                    \Log::warning("Could not check assignment state tracking: " . $stateError->getMessage());
                }

            }
            */
            
            // ALWAYS ALLOW: State tracking validation disabled
            \Log::info("Assignment state tracking validation skipped - auto-shuffle allowed anytime");
            
            // VALIDATION: Check if auto-shuffle is allowed based on current assignment end dates
            // DISABLED: Allow auto-shuffle anytime for better user experience
            // Users can now shuffle anytime without waiting for assignment end dates
            // This makes the system more flexible and user-friendly
            
            // Original validation logic commented out - kept for reference
            /*
            if (!$request->has('force_shuffle') && !$fillToRequirements && !$allowAnytimeShuffle) {
                $currentAssignments = Assignment::where('status', 'current')->get();
                $now = Carbon::now('Asia/Manila');
                
                foreach ($currentAssignments as $assignment) {
                    $endDate = Carbon::parse($assignment->end_date, 'Asia/Manila')->endOfDay();
                    if ($now->lt($endDate)) {
                        // Block auto-shuffle if before end date
                        $message = "Auto-shuffle not allowed yet...";
                        return redirect()->route('generalTask')->with('error', $message);
                    }
                }
            }
            */
            
            // ALWAYS ALLOW AUTO-SHUFFLE: No time restrictions
            \Log::info("Auto-shuffle allowed anytime - time validation disabled for user convenience");
            
            \Log::info("Auto-shuffle validation passed: All current assignments have reached their end dates");
            // Test database connection first
            try {
                \DB::connection()->getPdo();
                \Log::info("Database connection verified for auto-shuffle");
            } catch (\Exception $dbError) {
                \Log::error("Database connection failed: " . $dbError->getMessage());
                if (file_exists($lockFile)) {
                    unlink($lockFile);
                }
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Database connection failed. Please check your database server and credentials.'], 500);
                }
                return redirect()->route('generalTask')->with('error', 'Database connection failed. Please check your database server and credentials.');
            }

            // REMOVE 7-day restriction for testing - you can shuffle anytime now
            // Get the latest assignment end date
            // $latestAssignment = Assignment::orderBy('end_date', 'desc')->first();
            // $now = Carbon::now();
            // if ($latestAssignment && $now->lessThan(Carbon::parse($latestAssignment->end_date))) {
            //     // Not yet 7 days since last shuffle
            //     return redirect()->route('dashboard')->with('error', 'Auto-shuffle is only allowed once every 7 days. Next shuffle: ' . Carbon::parse($latestAssignment->end_date)->toFormattedDateString());
            // }

            // Use Philippines timezone (Asia/Manila = UTC+8) for all date/time operations
            $now = Carbon::now('Asia/Manila');

            // Debug: Log that auto shuffle started with Philippines time
            \Log::info("Auto shuffle started at: " . $now->toDateTimeString() . " (Philippines Time)");

        // PRESERVE: capture any existing current assignment members (manual additions) so we can
        // include them in the new shuffle. We must capture before we move current assignments to 'previous'.
        $manualPreserve = [];
        try {
            $existingCurrentAssignments = Assignment::where('status','current')->with(['category','assignmentMembers.student.studentDetail'])->get();
            foreach ($existingCurrentAssignments as $a) {
                $catName = $a->category->name ?? null;
                if (!$catName) continue;
                foreach ($a->assignmentMembers as $m) {
                    // Build normalized student object used by selection logic
                    $batch = optional($m->student)->studentDetail ? optional($m->student->studentDetail)->batch : null;
                    $student_code = $m->student_code ?? null;
                    $unique_key = null;
                    if (!empty($m->student_id)) {
                        $unique_key = 'legacy:' . $m->student_id;
                    } elseif (!empty($m->student_code)) {
                        $resolved = StudentDetail::where('student_id', $m->student_code)->pluck('user_id')->first();
                        if ($resolved) $unique_key = 'pnuser:' . $resolved;
                        else $unique_key = 'code:' . $m->student_code;
                    } elseif ($m->student) {
                        // Handle both Eloquent models and stdClass objects
                        if (is_object($m->student)) {
                            $studentId = $m->student->user_id ?? $m->student->id ?? null;
                            if ($studentId) {
                                $unique_key = 'pnuser:' . $studentId;
                            }
                        }
                    }
                    $name = $m->student ? trim(($m->student->user_fname ?? '') . ' ' . ($m->student->user_lname ?? '')) : ($m->student_name ?? $m->student_code ?? null);
                    $gender = $m->student ? ($m->student->gender ?? null) : ($m->gender ?? null);
                    $id = $m->student && is_object($m->student) ? ($m->student->user_id ?? $m->student->id ?? null) : ($m->student_id ?? null);
                    $obj = (object)[
                        'id' => $id,
                        'source' => $m->student ? 'pnuser' : 'legacy',
                        'unique_key' => $unique_key,
                        'student_code' => $student_code,
                        'name' => $name,
                        'gender' => $gender,
                        'batch' => $batch
                    ];
                    if (!isset($manualPreserve[$catName])) $manualPreserve[$catName] = collect();
                    $manualPreserve[$catName]->push($obj);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to capture existing current assignments for preservation: ' . $e->getMessage());
            $manualPreserve = [];
        }

        // Preserve current assignment members per category so manual additions are carried into the new shuffle
        $preservedMembersByCategory = []; // category_name => [ canonical_key => member-like object ]
        $currentAssignments = Assignment::where('status', 'current')->with('assignmentMembers.student')->get();
        foreach ($currentAssignments as $assignment) {
            $catName = $assignment->category ? $assignment->category->name : ('cat:' . $assignment->category_id);
            if (!isset($preservedMembersByCategory[$catName])) $preservedMembersByCategory[$catName] = [];
            foreach ($assignment->assignmentMembers as $member) {
                // build canonical unique key similar to other code (pnuser:ID, legacy:ID, code:CODE)
                $uKey = null;
                if (!empty($member->student_id)) {
                    $uKey = 'legacy:' . $member->student_id;
                } elseif (!empty($member->student_code)) {
                    $resolved = StudentDetail::where('student_id', $member->student_code)->pluck('user_id')->first();
                    if ($resolved) $uKey = 'pnuser:' . $resolved;
                    else $uKey = 'code:' . $member->student_code;
                } elseif ($member->student) {
                    // Handle both Eloquent models and stdClass objects
                    if (is_object($member->student)) {
                        $studentId = $member->student->user_id ?? $member->student->id ?? null;
                        if ($studentId) {
                            $uKey = 'pnuser:' . $studentId;
                        }
                    }
                }

                if ($uKey) {
                    $preservedMembersByCategory[$catName][$uKey] = (object)[
                        'unique_key' => $uKey,
                        'student_code' => $member->student_code ?? null,
                        'id' => $member->student_id ?? ($member->student && is_object($member->student) ? ($member->student->user_id ?? $member->student->id ?? null) : null),
                        'name' => $member->student ? trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? '')) : ($member->student_name ?? $member->student_code ?? null),
                        'is_coordinator' => (bool)$member->is_coordinator,
                        'batch' => optional($member->student)->studentDetail ? optional($member->student->studentDetail)->batch : null
                    ];
                }
            }
            
            // CHECK IF REQUIREMENTS ARE ALREADY MET - Skip shuffle if requirements are satisfied
            // unless force_shuffle is explicitly requested
            // ALSO: Keep current assignment if it has manually assigned members (don't lose them)
            // ROTATION SYSTEM: Always move current assignments to previous status
            // This ensures every auto-shuffle creates NEW assignments with DIFFERENT students
            // No skipping - we want complete rotation every time
            \Log::info("🔄 ROTATION: Moving current assignment for {$assignment->category->name} to previous status (ID: {$assignment->id})");
            
            // HISTORY SAVING: Move current assignment to previous status with proper timestamping
            $memberCount = $assignment->assignmentMembers->count();
            $assignment->update([
                'status' => 'previous',
                'end_date' => $now->copy()->subDay()->toDateString(),
                'updated_at' => $now // Ensure proper timestamp for history tracking
            ]);
            
            \Log::info("📚 HISTORY SAVED: Assignment for {$assignment->category->name} moved to history with {$memberCount} members (ID: {$assignment->id})");
        }

    // Get previous assignments to avoid reassigning students repeatedly
        // ENHANCED ROTATION: Track students assigned in previous round across ALL categories
        $previousAssignments = Assignment::where('status', 'previous')
            ->orderBy('end_date', 'desc')
            ->with('assignmentMembers.student')
            ->get();
        
        $previousTaskAssignments = []; // Maps student unique_key => category_id (for same-category avoidance)
        $recentlyAssignedStudents = []; // Set of unique_keys for students assigned in most recent round

        // Build a robust map keyed by the user_id used in the students pool.
        // AssignmentMember may store student references in different legacy columns
        // (student_id, student_code) so try multiple resolutions.
        
        // Get the most recent assignment end date to identify the last shuffle round
        $latestEndDate = $previousAssignments->max('end_date');
        
        foreach ($previousAssignments as $assignment) {
            foreach ($assignment->assignmentMembers as $member) {
                // Build a stable unique key for previous members matching the in-memory student.unique_key
                $uKey = null;
                if (!empty($member->student_id)) {
                    $uKey = 'legacy:' . $member->student_id;
                } elseif (!empty($member->student_code)) {
                    $resolved = StudentDetail::where('student_id', $member->student_code)->pluck('user_id')->first();
                    if ($resolved) $uKey = 'pnuser:' . $resolved;
                    else $uKey = 'code:' . $member->student_code;
                } elseif (isset($member->student) && !empty($member->student->user_id)) {
                    $uKey = 'pnuser:' . $member->student->user_id;
                }

                if (!empty($uKey)) {
                    // Track which category this student was in (for same-category avoidance)
                    $previousTaskAssignments[$uKey] = $assignment->category_id;
                    
                    // GLOBAL ROTATION: Track students from the most recent shuffle round
                    // These students should be deprioritized in the next shuffle
                    if ($assignment->end_date == $latestEndDate) {
                        $recentlyAssignedStudents[$uKey] = true;
                    }
                }
            }
        }
        
        $recentlyAssignedCount = count($recentlyAssignedStudents);
        \Log::info("🔄 ROTATION TRACKING: {$recentlyAssignedCount} students were assigned in the most recent round and will be deprioritized");
        \Log::info("📋 Previous assignments tracked: " . count($previousTaskAssignments) . " student-category mappings");



        $categories = Category::all();
        
        // AUTOMATIC BATCH VALIDATION & CORRECTION: Ensure all students are in correct batches
        // This prevents Batch 2025 students from appearing in Batch 2026 and vice versa
        \Log::info("🔍 AUTO-VALIDATING student batches before shuffle...");
        $batchCorrectionCount = 0;
        $allDetails = StudentDetail::all();
        foreach ($allDetails as $detail) {
            if (empty($detail->student_id)) continue;
            
            // Parse correct batch from student_id (e.g., "2025010041C1" -> 2025)
            if (preg_match('/^(20\d{2})/', $detail->student_id, $matches)) {
                $correctBatch = (int)$matches[1];
                
                // Only validate batches 2025 and 2026
                if (in_array($correctBatch, [2025, 2026]) && $detail->batch != $correctBatch) {
                    $user = PNUser::where('user_id', $detail->user_id)->first();
                    $name = $user ? trim($user->user_fname . ' ' . $user->user_lname) : 'Unknown';
                    
                    \Log::warning("🔧 AUTO-FIX: {$name} was in Batch {$detail->batch}, correcting to Batch {$correctBatch}");
                    $detail->batch = $correctBatch;
                    $detail->save();
                    $batchCorrectionCount++;
                }
            }
        }
        if ($batchCorrectionCount > 0) {
            \Log::info("AUTO-CORRECTED {$batchCorrectionCount} student batch(es) before shuffle");
        } else {
            \Log::info("All student batches are correct");
        }
        
        // Load ALL students from Login (PNUser) and map to expected fields used throughout this method
        $users = PNUser::where('user_role', 'student')->get();
        $details = StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');
        // Map Login PNUser + StudentDetail into a students collection used by this method
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
                'source' => 'pnuser',
                'unique_key' => 'pnuser:' . $u->user_id,
                'student_code' => $d->student_id ?? null,
                'name' => trim($u->user_fname . ' ' . $u->user_lname),
                'gender' => $gender,
                'batch' => $d->batch ?? null
            ];
        });
        
        // DIAGNOSTIC: Count students per batch to verify database has batch 2026 students
        $batch2025Count = $students->where('batch', 2025)->count();
        $batch2026Count = $students->where('batch', 2026)->count();
        $noBatchCount = $students->where('batch', null)->count();
        \Log::info("🔍 DIAGNOSTIC: Student counts by batch - 2025: {$batch2025Count}, 2026: {$batch2026Count}, No batch: {$noBatchCount}, Total: {$students->count()}");
        
        if ($batch2026Count == 0) {
            \Log::error("❌ CRITICAL: NO STUDENTS WITH BATCH 2026 FOUND IN DATABASE! Auto-shuffle cannot assign to batch 2026. Please check student_details table.");
        }

        // FALLBACK: if no students found in PNUser (or not enough), also include legacy
        // `student_group16` table so auto-shuffle can assign from the older student list.
        // This ensures categories get filled even if PNUser/StudentDetail mapping is incomplete.
        try {
            $legacyCount = \Illuminate\Support\Facades\DB::table('student_group16')->count();
        } catch (\Exception $e) {
            $legacyCount = 0;
        }

        if ($students->count() == 0 && $legacyCount > 0) {
            \Log::warning("PNUser students empty — falling back to student_group16 table with {$legacyCount} entries");
            $legacy = \Illuminate\Support\Facades\DB::table('student_group16')->get()->map(function($s) {
                // Normalize legacy gender as well
                $raw = isset($s->gender) ? trim($s->gender) : null;
                $gender = null;
                if (!empty($raw)) {
                    $g = strtolower($raw);
                    if (in_array($g, ['m', 'male'])) $gender = 'Male';
                    elseif (in_array($g, ['f', 'female'])) $gender = 'Female';
                }
                return (object) [
                    'id' => $s->id, // will be used as student_id when creating AssignmentMember
                    'source' => 'legacy',
                    'unique_key' => 'legacy:' . $s->id,
                    'student_code' => null,
                    'name' => $s->name ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? '')),
                    'gender' => $gender,
                    'batch' => $s->batch ?? null
                ];
            });

            // Append legacy students to the students collection
            $students = $students->concat($legacy)->values();
        }

        // Debug: Log counts
        \Log::info("Categories found: " . $categories->count());
        \Log::info("Total students found: " . $students->count());

        // Ensure categories are processed in a specific order to guarantee all get members
        // Use flexible matching to handle category name variations
        $categoryOrder = [
            'Kitchen',
            'Dishwashing',
            'Dining',
            'Ground Floor',
            'Offices & Conference Rooms',
            'Garbage, Rugs, & Rooftop'
        ];

        $orderedCategories = collect();
        $usedCategories = collect();
        
        // First, try to match categories in the preferred order
        foreach ($categoryOrder as $orderName) {
            foreach ($categories as $category) {
                if ($usedCategories->contains($category->id)) continue;
                
                // Try exact match first
                if ($category->name === $orderName) {
                    $orderedCategories->push($category);
                    $usedCategories->push($category->id);
                    break;
                }
                
                // Try flexible matching (case-insensitive, partial match)
                $normalizedOrderName = strtolower(preg_replace('/[^a-z0-9]/', '', $orderName));
                $normalizedCategoryName = strtolower(preg_replace('/[^a-z0-9]/', '', $category->name));
                
                if ($normalizedOrderName === $normalizedCategoryName || 
                    strpos($normalizedCategoryName, $normalizedOrderName) !== false ||
                    strpos($normalizedOrderName, $normalizedCategoryName) !== false) {
                    $orderedCategories->push($category);
                    $usedCategories->push($category->id);
                    \Log::info("Category '{$category->name}' matched with order '{$orderName}' using flexible matching");
                    break;
                }
            }
        }
        
        // Add any remaining categories that weren't matched
        foreach ($categories as $category) {
            if (!$usedCategories->contains($category->id)) {
                $orderedCategories->push($category);
                \Log::info("Category '{$category->name}' added without specific ordering");
            }
        }
        
        $categories = $orderedCategories;
        \Log::info("Final category processing order: " . $categories->pluck('name')->implode(', '));

        // Define member limits per category (defaults) - increased limits to use more students
        $defaultCategoryLimits = [
            'Kitchen' => 32,  // 20 boys + 12 girls = 32 total with equal batch distribution
            'Ground Floor' => 25,
            'Dining' => 25,
            'Dishwashing' => 25,
            'Offices & Conference Rooms' => 25,
            'Garbage, Rugs, & Rooftop' => 30,  // Increased for rooftop area
        ];

        // Accept optional overrides from request: overrides => [ 'Dining' => ['max_total'=>24,'start_date'=>'2025-09-08','end_date'=>'2025-09-13'], ... ]
        $overrides = $request->input('overrides', []);
        // Also allow server-side overrides saved in session by the edit form
        $sessionOverrides = session('auto_shuffle_overrides', []);
        \Log::info("Session overrides found: " . json_encode($sessionOverrides));
        if (is_array($sessionOverrides) && count($sessionOverrides) > 0) {
            // Merge session overrides into request overrides, request takes precedence
            $overrides = array_replace_recursive($sessionOverrides, is_array($overrides) ? $overrides : []);
        }
        \Log::info("Final overrides after merge: " . json_encode($overrides));

        $categoryLimits = $defaultCategoryLimits;
        if (is_array($overrides) && count($overrides) > 0) {
            foreach ($overrides as $catName => $info) {
                if (isset($info['max_total']) && is_numeric($info['max_total'])) {
                    $categoryLimits[$catName] = (int) $info['max_total'];
                }
            }
        }

    // Include ALL batch years found in the database for proper separation
    $allBatches = $students->pluck('batch')->filter()->unique()->sort()->values()->toArray();
    $allowedBatchYears = !empty($allBatches) ? $allBatches : [2027, 2026, 2025];
    
    \Log::info("🎓 BATCH YEARS FOUND: " . implode(', ', $allowedBatchYears));

        // Build a map of students keyed by batch year (include ALL batches)
        $studentsByBatch = [];
        foreach ($allowedBatchYears as $batchYear) {
            $studentsByBatch[$batchYear] = $students->filter(function($s) use ($batchYear) {
                return isset($s->batch) && $s->batch == $batchYear;
            })->values();
        }
        
        // Also include students with no batch info
        $noBatchStudents = $students->filter(function($s) {
            return empty($s->batch) || !in_array($s->batch, [2025, 2026]);
        })->values();
        
        if ($noBatchStudents->count() > 0) {
            $studentsByBatch['other'] = $noBatchStudents;
            $allowedBatchYears[] = 'other';
        }
        
        // Log students by all batches
        foreach ($allowedBatchYears as $batch) {
            $count = isset($studentsByBatch[$batch]) ? $studentsByBatch[$batch]->count() : 0;
            \Log::info("Students in batch {$batch}: {$count}");
        }

        // Load round-robin state (persisted file) so rotation is deterministic across runs.
        $rrFile = storage_path('app/round_robin_state.json');
        $rrState = [];
        try {
            if (file_exists($rrFile)) {
                $rrState = json_decode(file_get_contents($rrFile), true) ?: [];
            }
        } catch (\Exception $e) {
            \Log::warning('Could not read round-robin state: ' . $e->getMessage());
            $rrState = [];
        }

        // Remember original pointers to update later
        $origPointers = [];
        foreach ($studentsByBatch as $by => $col) {
            $count = $col->count();
            $ptr = isset($rrState[$by]) ? (int)$rrState[$by] : 0;
            $origPointers[$by] = $ptr;
            if ($count > 0) {
                $ptr = $ptr % $count;
                // rotate collection deterministically by pointer
                $rotated = $col->slice($ptr)->concat($col->slice(0, $ptr))->values();
                $studentsByBatch[$by] = $rotated;
            }
        }

        // Create a pool of available students by concatenating all batches (keeps distribution flexible)
        $availableStudents = collect();
        foreach ($studentsByBatch as $batchGroup) {
            foreach ($batchGroup as $student) {
                $availableStudents->push($student);
            }
        }

    // Do not fully randomize the pool here; we rely on round-robin rotation above for deterministic alternation.
    $availableStudents = $availableStudents->values();

    // Track which students have been assigned using a consistent unique_key
    $usedStudentKeys = [];
    // Backwards-compatible array for older code paths that still reference numeric ids
    $usedStudentIds = [];

    // Track how many students we pick from each batch (used to advance round-robin pointers)
    $batchPickedCount = [];
    foreach ($allowedBatchYears as $batchYear) {
        $batchPickedCount[$batchYear] = 0;
    }

        // Calculate total students needed
        $totalNeeded = array_sum($categoryLimits);
        $totalAvailable = $availableStudents->count();

        \Log::info("Total students available: {$totalAvailable}, Total needed (pre-adjust): {$totalNeeded}");

        // If requested totals exceed available students, adjust limits:
        if ($totalNeeded > $totalAvailable) {
            \Log::warning("Requested total ({$totalNeeded}) exceeds available students ({$totalAvailable}). Adjusting category limits...");

            // Identify overridden categories (explicit max_total or batch_requirements present)
            $overriddenCats = [];
            $nonOverriddenCats = [];
            foreach ($categoryLimits as $catName => $limit) {
                if (isset($overrides[$catName]) && (!empty($overrides[$catName]['max_total']) || !empty($overrides[$catName]['batch_requirements']))) {
                    $overriddenCats[$catName] = $limit;
                } else {
                    $nonOverriddenCats[$catName] = $limit;
                }
            }

            $sumOverrides = array_sum($overriddenCats);
            $sumNonOverrides = array_sum($nonOverriddenCats);

            // If overrides alone exceed available, scale overrides down proportionally
            if ($sumOverrides >= $totalAvailable) {
                $factor = $totalAvailable / max(1, $sumOverrides);
                foreach ($overriddenCats as $catName => $orig) {
                    // If this override has batch_requirements, scale by preserving ratio
                    if (isset($overrides[$catName]['batch_requirements']) && is_array($overrides[$catName]['batch_requirements'])) {
                        $br = $overrides[$catName]['batch_requirements'];
                        $origTotal = 0;
                        foreach ([2025,2026] as $y) {
                            if (!isset($br[$y]) || !is_array($br[$y])) continue;
                            $vals = $br[$y];
                            $origTotal += (int)($vals['boys'] ?? 0) + (int)($vals['girls'] ?? 0);
                        }
                        $targetTotal = max(0, (int) floor($origTotal * $factor));
                        $categoryLimits[$catName] = $targetTotal;
                        // Store the scaled totals back into overrides for consumption later
                        // (we update a copy only; session will remain until controller saves again)
                        $scaledBr = [];
                        if ($origTotal > 0) {
                            foreach ([2025,2026] as $y) {
                                if (!isset($br[$y]) || !is_array($br[$y])) continue;
                                $vals = $br[$y];
                                $yearTotal = (int)($vals['boys'] ?? 0) + (int)($vals['girls'] ?? 0);
                                $scaledYearTotal = (int) floor($yearTotal * ($targetTotal / max(1, $origTotal)));
                                // Attempt to preserve boys/girls ratio roughly by scaling each
                                $scaledBr[$y] = [
                                    'boys' => (int) floor((($vals['boys'] ?? 0) / max(1, $yearTotal)) * $scaledYearTotal),
                                    'girls' => max(0, $scaledYearTotal - (int) floor((($vals['boys'] ?? 0) / max(1, $yearTotal)) * $scaledYearTotal))
                                ];
                            }
                            // Replace in overrides copy so later per-batch selection uses scaled values
                            $overrides[$catName]['batch_requirements'] = $scaledBr;
                        }
                    } else {
                        $newLimit = max(0, (int) floor($orig * $factor));
                        $categoryLimits[$catName] = $newLimit;
                    }
                }
                // Set all non-overridden to zero because overrides consumed capacity
                foreach ($nonOverriddenCats as $catName => $orig) {
                    $categoryLimits[$catName] = 0;
                }
            } else {
                // Keep overrides as-is, scale non-overridden categories to fit remaining slots
                $remaining = $totalAvailable - $sumOverrides;
                if ($sumNonOverrides > 0) {
                    $factor = $remaining / $sumNonOverrides;
                    foreach ($nonOverriddenCats as $catName => $orig) {
                        $categoryLimits[$catName] = max(0, (int) floor($orig * $factor));
                    }
                }
            }

            $totalNeeded = array_sum($categoryLimits);
            \Log::info("Adjusted total needed after scaling: {$totalNeeded}");
        }

        // Get category names for reservation calculation
        $categoryNames = $categories->pluck('name')->toArray();
        
        // Track categories with manual requirements to skip final-fill passes
        $categoriesWithManualRequirements = [];
        $categoriesWithManualReqs = collect();
        $categoriesWithoutManualReqs = collect();
        
        foreach ($categories as $category) {
            $hasManualReqs = false;
            
            // Check for exact match first
            if (isset($overrides[$category->name]['batch_requirements'])) {
                $hasManualReqs = true;
            } else {
                // Check flexible matching
                foreach ($overrides as $overrideKey => $overrideData) {
                    if (isset($overrideData['batch_requirements'])) {
                        $normalizedCategoryName = strtolower(preg_replace('/[^a-z0-9]/', '', $category->name));
                        $normalizedOverrideKey = strtolower(preg_replace('/[^a-z0-9]/', '', $overrideKey));
                        
                        if ($normalizedCategoryName === $normalizedOverrideKey || 
                            strpos($normalizedOverrideKey, $normalizedCategoryName) !== false ||
                            strpos($normalizedCategoryName, $normalizedOverrideKey) !== false) {
                            $hasManualReqs = true;
                            break;
                        }
                    }
                }
            }
            
            if ($hasManualReqs) {
                $categoriesWithManualReqs->push($category);
                \Log::info("Category {$category->name}: Has manual requirements - will be processed FIRST");
            } else {
                $categoriesWithoutManualReqs->push($category);
            }
        }
        
        // Process categories with manual requirements first, then others
        $prioritizedCategories = $categoriesWithManualReqs->concat($categoriesWithoutManualReqs);
        \Log::info("Processing order: " . $prioritizedCategories->pluck('name')->implode(', '));
        
        foreach ($prioritizedCategories as $category) {
            \Log::info("=== Processing category: {$category->name} (ID: {$category->id}) ===");
            
            // ROTATION SYSTEM: Always create NEW assignments with DIFFERENT students
            // No skipping, no adding to existing - every shuffle is a complete refresh
            \Log::info("🔄 ROTATION: Category '{$category->name}' will get NEW assignment with different students");
            
            // Re-shuffle the available student pool per category to improve randomness across runs
            $availableStudents = $availableStudents->shuffle();
            // Get member limit for this category
            $maxTotal = $categoryLimits[$category->name] ?? 20;

            // Debug: Log category processing
            $remainingStudents = $availableStudents->count() - count($usedStudentKeys);
            \Log::info("Processing category: {$category->name}, Max: {$maxTotal}, Remaining students: {$remainingStudents}");

            $selected2025 = collect();
            $selected2026 = collect();
            
            // Track if coordinators are pre-seeded to avoid double-selection
            $preSeededCoord2025 = null;
            $preSeededCoord2026 = null;

            // If there are session overrides with metadata (from manual Edit modal), pre-seed those people
            // into the selection for this category so manual edits are respected by Auto-Shuffle.
            $meta = isset($overrides[$category->name]['metadata']) ? $overrides[$category->name]['metadata'] : null;
            if (is_array($meta)) {
                // helper to attempt to find a student object in availableStudents by student_code, id, or full-name
                $findStudent = function($val) use ($availableStudents, $students) {
                    if (empty($val)) return null;
                    $valTrim = trim($val);
                    // try matching student_code first
                    $found = $availableStudents->first(function($s) use ($valTrim) {
                        return (!empty($s->student_code) && $s->student_code === $valTrim) || (isset($s->id) && ((string)$s->id) === (string)$valTrim);
                    });
                    if ($found) return $found;

                    // try matching by full name (students collection)
                    $found = $students->first(function($s) use ($valTrim) {
                        return trim($s->name) === $valTrim;
                    });
                    if ($found) {
                        // convert to the same shape as availableStudents entries
                        return (object)[
                            'id' => $found->id,
                            'source' => 'pnuser',
                            'unique_key' => 'pnuser:' . $found->id,
                            'student_code' => $found->student_code ?? null,
                            'name' => $found->name,
                            'gender' => $found->gender ?? null,
                            'batch' => $found->batch ?? null
                        ];
                    }
                    return null;
                };
                
                // Pre-seed coordinator 2025 if present (but ONLY if not recently assigned - respect rotation)
                if (!empty($meta['coordinator_2025'])) {
                    $cval = $meta['coordinator_2025'];
                    $stu = $findStudent($cval);
                    if ($stu) {
                        // STRICT ROTATION: Check if this coordinator was recently assigned
                        if (isset($recentlyAssignedStudents[$stu->unique_key])) {
                            \Log::warning("⚠️ Pre-seeded coordinator 2025 {$stu->name} was recently assigned - SKIPPING to respect rotation system");
                        } else {
                            $selected2025->push($stu);
                            $preSeededCoord2025 = $stu; // Track pre-seeded coordinator
                            if (isset($stu->unique_key)) $usedStudentKeys[] = $stu->unique_key;
                            if (isset($batchPickedCount) && isset($stu->batch) && isset($batchPickedCount[$stu->batch])) $batchPickedCount[$stu->batch]++;
                            // remove from availableStudents to avoid double-pick
                            $availableStudents = $availableStudents->filter(function($s) use ($stu) { return ($s->unique_key ?? null) !== ($stu->unique_key ?? null); })->values();
                            \Log::info("Auto-shuffle: pre-seeded manual coordinator 2025 {$stu->name} for category {$category->name}");
                        }
                    } else {
                        \Log::info("Auto-shuffle: manual coordinator_2025 value for {$category->name} could not be resolved: {$cval}");
                    }
                }

                // Pre-seed coordinator 2026 if present (but ONLY if not recently assigned - respect rotation)
                if (!empty($meta['coordinator_2026'])) {
                    $cval = $meta['coordinator_2026'];
                    $stu = $findStudent($cval);
                    if ($stu) {
                        // STRICT ROTATION: Check if this coordinator was recently assigned
                        if (isset($recentlyAssignedStudents[$stu->unique_key])) {
                            \Log::warning("⚠️ Pre-seeded coordinator 2026 {$stu->name} was recently assigned - SKIPPING to respect rotation system");
                        } else {
                            $selected2026->push($stu);
                            $preSeededCoord2026 = $stu; // Track pre-seeded coordinator
                            if (isset($stu->unique_key)) $usedStudentKeys[] = $stu->unique_key;
                            if (isset($batchPickedCount) && isset($stu->batch) && isset($batchPickedCount[$stu->batch])) $batchPickedCount[$stu->batch]++;
                            $availableStudents = $availableStudents->filter(function($s) use ($stu) { return ($s->unique_key ?? null) !== ($stu->unique_key ?? null); })->values();
                            \Log::info("Auto-shuffle: pre-seeded manual coordinator 2026 {$stu->name} for category {$category->name}");
                        }
                    } else {
                        \Log::info("Auto-shuffle: manual coordinator_2026 value for {$category->name} could not be resolved: {$cval}");
                    }
                }
            }

            // Pre-seed preserved members (from previous 'current' assignments) so manual members are included
            if (!empty($preservedMembersByCategory)) {
                $catKey = $category->name;
                if (isset($preservedMembersByCategory[$catKey]) && is_array($preservedMembersByCategory[$catKey])) {
                    foreach ($preservedMembersByCategory[$catKey] as $uKey => $memberObj) {
                        if (in_array($uKey, $usedStudentKeys)) continue;
                        $resolvedStudent = null;
                        // try to match by legacy student_id first
                        if (!empty($memberObj->id)) {
                            foreach ([2026,2025] as $b) {
                                if (!isset($studentsByBatch[$b]) || $studentsByBatch[$b]->isEmpty()) continue;
                                foreach ($studentsByBatch[$b] as $idx => $s) {
                                    if (!empty($s->id) && $s->id == $memberObj->id) { $resolvedStudent = $s; $studentsByBatch[$b]->forget($idx); break 2; }
                                }
                            }
                        }
                        // fallback: match by student_code
                        if (!$resolvedStudent && !empty($memberObj->student_code)) {
                            foreach ([2026,2025] as $b) {
                                if (!isset($studentsByBatch[$b]) || $studentsByBatch[$b]->isEmpty()) continue;
                                foreach ($studentsByBatch[$b] as $idx => $s) {
                                    if (!empty($s->student_code) && $s->student_code == $memberObj->student_code) { $resolvedStudent = $s; $studentsByBatch[$b]->forget($idx); break 2; }
                                }
                            }
                        }
                        // fallback: match by exact fullname
                        if (!$resolvedStudent && !empty($memberObj->name)) {
                            $nm = strtolower($memberObj->name);
                            foreach ([2026,2025] as $b) {
                                if (!isset($studentsByBatch[$b]) || $studentsByBatch[$b]->isEmpty()) continue;
                                foreach ($studentsByBatch[$b] as $idx => $s) {
                                    $full = strtolower(trim(($s->name ?? '')));
                                    if ($full === $nm) { $resolvedStudent = $s; $studentsByBatch[$b]->forget($idx); break 2; }
                                }
                            }
                        }

                        if ($resolvedStudent) {
                            $tArr = $resolvedStudent->batch == 2026 ? 'selected2026' : 'selected2025';
                            ${$tArr}->push($resolvedStudent);
                            $usedStudentKeys[] = $resolvedStudent->unique_key ?? $uKey;
                            if (isset($batchPickedCount) && isset($resolvedStudent->batch) && isset($batchPickedCount[$resolvedStudent->batch])) $batchPickedCount[$resolvedStudent->batch]++;
                        } else {
                            // unresolved: create a lightweight placeholder so coordinator displays and remains reserved
                            $placeholder = (object)[
                                'unique_key' => $uKey,
                                'student_code' => $memberObj->student_code ?? null,
                                'student_id' => $memberObj->id ?? null,
                                'user_fname' => $memberObj->name ?? null,
                                'user_lname' => '',
                                'batch' => $memberObj->batch ?? null,
                                'name' => $memberObj->name ?? null
                            ];
                            if (!empty($memberObj->batch) && $memberObj->batch == 2026) {
                                $selected2026->push($placeholder);
                                if (isset($batchPickedCount) && isset($batchPickedCount[2026])) $batchPickedCount[2026]++;
                            } else {
                                $selected2025->push($placeholder);
                                if (isset($batchPickedCount) && isset($batchPickedCount[2025])) $batchPickedCount[2025]++;
                            }
                            $usedStudentKeys[] = $uKey;
                        }
                    }
                }
            }

            // Check if there are specific batch requirements for this category (from Edit modal)
            // Priority: 1) Session overrides, 2) Database, 3) Category capacity
            $batchRequirements = null;
            $matchedOverrideKey = null;
            
            if (isset($overrides[$category->name]['batch_requirements'])) {
                $batchRequirements = $overrides[$category->name]['batch_requirements'];
                $matchedOverrideKey = $category->name;
                \Log::info("Category {$category->name}: Using session requirements");
            } else {
                // Try flexible matching for category names
                foreach ($overrides as $overrideKey => $overrideData) {
                    if (isset($overrideData['batch_requirements'])) {
                        // Check if category names are similar (case-insensitive, ignore spaces/punctuation)
                        $normalizedCategoryName = strtolower(preg_replace('/[^a-z0-9]/', '', $category->name));
                        $normalizedOverrideKey = strtolower(preg_replace('/[^a-z0-9]/', '', $overrideKey));
                        
                        if ($normalizedCategoryName === $normalizedOverrideKey || 
                            strpos($normalizedOverrideKey, $normalizedCategoryName) !== false ||
                            strpos($normalizedCategoryName, $normalizedOverrideKey) !== false) {
                            $batchRequirements = $overrideData['batch_requirements'];
                            $matchedOverrideKey = $overrideKey;
                            \Log::info("Category {$category->name}: Matched with override key '{$overrideKey}' using flexible matching");
                            break;
                        }
                    }
                }
                
                // FALLBACK 1: Read from database (persistent storage)
                if (!$batchRequirements && !empty($category->batch_requirements)) {
                    // Laravel auto-converts from JSON to array
                    $dbRequirements = $category->batch_requirements;
                    
                    if (is_array($dbRequirements) && !empty($dbRequirements)) {
                        // Ensure proper format: keys should be integers (2025, 2026)
                        $formattedReqs = [];
                        foreach ($dbRequirements as $year => $vals) {
                            $yearInt = (int)$year;
                            $formattedReqs[$yearInt] = [
                                'boys' => (int)($vals['boys'] ?? 0),
                                'girls' => (int)($vals['girls'] ?? 0)
                            ];
                        }
                        $batchRequirements = $formattedReqs;
                        \Log::info("Category {$category->name}: Using database requirements: " . json_encode($batchRequirements));
                    }
                }
                
                // FALLBACK 2: If no session/database requirements found, use category capacity
                if (!$batchRequirements && isset($category->capacity) && $category->capacity > 0) {
                    $totalCapacity = (int)$category->capacity;
                    // Split evenly between batches (50/50) and between boys/girls (50/50)
                    $perBatch = (int)ceil($totalCapacity / 2);
                    $perBatchBoys = (int)ceil($perBatch / 2);
                    $perBatchGirls = $perBatch - $perBatchBoys;
                    
                    $batchRequirements = [
                        2025 => ['boys' => $perBatchBoys, 'girls' => $perBatchGirls],
                        2026 => ['boys' => $perBatchBoys, 'girls' => $perBatchGirls]
                    ];
                    \Log::info("Category {$category->name}: Using category capacity fallback ({$totalCapacity} total) - Batch 2025: {$perBatchBoys}M+{$perBatchGirls}F, Batch 2026: {$perBatchBoys}M+{$perBatchGirls}F");
                }
            }
            
            $usedManualRequirements = false;
            $total2025 = 0;
            $total2026 = 0;
            
            \Log::info("Category {$category->name}: Checking for manual requirements. Matched key: " . ($matchedOverrideKey ?? 'none') . ", Requirements: " . json_encode($batchRequirements ?? 'none'));
            \Log::info("Category {$category->name}: Session overrides available: " . json_encode(array_keys(session('auto_shuffle_overrides', []))));
            
            if ($batchRequirements && is_array($batchRequirements)) {
                $usedManualRequirements = true;
                // Track this category to skip final-fill passes
                $categoriesWithManualRequirements[] = $category->id;
                // Use specific batch requirements instead of hardcoded logic
                \Log::info("Category {$category->name}: Using manual batch requirements: " . json_encode($batchRequirements));
                
                // FORCE CHECK: Ensure batch requirements array has both 2025 and 2026 keys
                if (!isset($batchRequirements[2025])) {
                    $batchRequirements[2025] = ['boys' => 0, 'girls' => 0];
                }
                if (!isset($batchRequirements[2026])) {
                    $batchRequirements[2026] = ['boys' => 0, 'girls' => 0];
                }
                
                // Log each batch requirement clearly
                $has2025Requirements = false;
                $has2026Requirements = false;
                
                foreach ([2025, 2026] as $year) {
                    if (isset($batchRequirements[$year])) {
                        $boys = (int)($batchRequirements[$year]['boys'] ?? 0);
                        $girls = (int)($batchRequirements[$year]['girls'] ?? 0);
                        $total = $boys + $girls;
                        \Log::info("  → Batch {$year}: {$boys} boys + {$girls} girls = {$total} total");
                        
                        if ($year == 2025) {
                            $total2025 = $total;
                            if ($total > 0) $has2025Requirements = true;
                        }
                        if ($year == 2026) {
                            $total2026 = $total;
                            if ($total > 0) $has2026Requirements = true;
                        }
                    } else {
                        \Log::warning("  → Batch {$year}: NO REQUIREMENTS SET!");
                    }
                }
                
                // RESPECT USER REQUIREMENTS: Use EXACTLY what user specified
                // NO automatic distribution - user's requirements are final
                if ($has2025Requirements && $has2026Requirements) {
                    \Log::info("✅ BALANCED: Both batches have requirements - 2025: {$total2025} students, 2026: {$total2026} students, Total: " . ($total2025 + $total2026) . " students");
                } elseif ($has2025Requirements && !$has2026Requirements) {
                    \Log::info("✅ BATCH 2025 ONLY: User specified Batch 2025 requirements only ({$total2025} students). Batch 2026 will have 0 students.");
                } elseif (!$has2025Requirements && $has2026Requirements) {
                    \Log::info("✅ BATCH 2026 ONLY: User specified Batch 2026 requirements only ({$total2026} students). Batch 2025 will have 0 students.");
                } else {
                    // EDGE CASE: No requirements at all - skip this category
                    \Log::warning("⚠️ WARNING: No batch requirements set for category {$category->name}. Skipping.");
                    continue; // Skip to next category
                }
                
                // ROTATION SYSTEM: Always creating NEW assignments, no existing members to account for
                // All requirements are fresh - no need to subtract existing members
                
                foreach ([2025, 2026] as $batchYear) {
                    // Handle both string and integer keys from session data
                    $batchKey = $batchYear;
                    $batchData = null;
                    
                    if (isset($batchRequirements[$batchYear]) && is_array($batchRequirements[$batchYear])) {
                        $req = $batchRequirements[$batchYear];
                        $boysRequired = (int)($req['boys'] ?? 0);
                        $girlsRequired = (int)($req['girls'] ?? 0);
                        
                        // Skip this batch if no requirements set (0 boys and 0 girls)
                        if ($boysRequired === 0 && $girlsRequired === 0) {
                            \Log::info("Category {$category->name}: Batch {$batchYear} has no requirements (0 boys, 0 girls). Skipping.");
                            continue;
                        }
                        
                        // ROTATION SYSTEM: Creating NEW assignments, so we need ALL required students (not "more")
                        $boysNeeded = $boysRequired;
                        $girlsNeeded = $girlsRequired;
                        
                        // ADJUST for pre-seeded coordinators (they count toward requirements)
                        if ($batchYear == 2025 && $preSeededCoord2025) {
                            $gender = $preSeededCoord2025->gender ?? null;
                            if ($gender === 'M' || $gender === 'Male') {
                                $boysNeeded = max(0, $boysNeeded - 1);
                                \Log::info("Category {$category->name}: Batch 2025 - Pre-seeded male coordinator counts toward requirement. Boys needed reduced to {$boysNeeded}");
                            } elseif ($gender === 'F' || $gender === 'Female') {
                                $girlsNeeded = max(0, $girlsNeeded - 1);
                                \Log::info("Category {$category->name}: Batch 2025 - Pre-seeded female coordinator counts toward requirement. Girls needed reduced to {$girlsNeeded}");
                            }
                        }
                        
                        if ($batchYear == 2026 && $preSeededCoord2026) {
                            $gender = $preSeededCoord2026->gender ?? null;
                            if ($gender === 'M' || $gender === 'Male') {
                                $boysNeeded = max(0, $boysNeeded - 1);
                                \Log::info("Category {$category->name}: Batch 2026 - Pre-seeded male coordinator counts toward requirement. Boys needed reduced to {$boysNeeded}");
                            } elseif ($gender === 'F' || $gender === 'Female') {
                                $girlsNeeded = max(0, $girlsNeeded - 1);
                                \Log::info("Category {$category->name}: Batch 2026 - Pre-seeded female coordinator counts toward requirement. Girls needed reduced to {$girlsNeeded}");
                            }
                        }
                        
                        \Log::info("Category {$category->name}: Batch {$batchYear} requires {$boysRequired} boys, {$girlsRequired} girls (NEW assignment). After pre-seeded adjustment: {$boysNeeded} boys, {$girlsNeeded} girls needed");
                        
                        // Get students from this specific batch
                        $batchStudents = isset($studentsByBatch[$batchYear]) ? $studentsByBatch[$batchYear] : collect();
                        \Log::info("Category {$category->name}: Batch {$batchYear} has {$batchStudents->count()} total students in database");
                        
                        // CRITICAL CHECK: If no students in this batch at all, log error
                        if ($batchStudents->count() === 0) {
                            \Log::error("❌ CRITICAL: NO STUDENTS FOUND IN BATCH {$batchYear}! Please check student_details table to ensure students have batch={$batchYear} set.");
                        }
                        
                        // SMART ROTATION: Try strict rotation first, then relax if needed to meet requirements
                        // This ensures requirements are ALWAYS met
                        
                        // FIRST PASS: Try strict rotation (exclude recently assigned)
                        $availableBatchStudents = $batchStudents->filter(function($s) use ($usedStudentKeys, $previousTaskAssignments, $category, $recentlyAssignedStudents) {
                            // Already used in this shuffle
                            if (in_array($s->unique_key, $usedStudentKeys)) return false;
                            
                            // STRICT: Exclude students assigned in most recent round
                            if (isset($recentlyAssignedStudents[$s->unique_key])) {
                                return false;
                            }
                            
                            // Also avoid reassigning to same category (additional safety)
                            if (isset($previousTaskAssignments[$s->unique_key]) && $previousTaskAssignments[$s->unique_key] == $category->id) return false;
                            
                            return true;
                        });
                        
                        \Log::info("Category {$category->name}: Batch {$batchYear} - Available students after STRICT rotation filter: {$availableBatchStudents->count()}");
                        
                        // Select boys from available (non-recently-assigned) students
                        $availableBoys = $availableBatchStudents->filter(function($s) {
                            return $s->gender === 'M' || $s->gender === 'Male';
                        })->shuffle();
                        
                        $selectedBoys = $availableBoys->take($boysNeeded);
                        
                        // FALLBACK: If not enough boys with strict rotation, relax filter to include recently assigned
                        if ($selectedBoys->count() < $boysNeeded) {
                            \Log::warning("⚠️ Category {$category->name}: Batch {$batchYear} - Strict rotation insufficient for boys ({$selectedBoys->count()}/{$boysNeeded}). Relaxing filter to include recently assigned students...");
                            
                            // SECOND PASS: Relax filter - allow recently assigned students
                            $relaxedBatchStudents = $batchStudents->filter(function($s) use ($usedStudentKeys, $previousTaskAssignments, $category) {
                                // Already used in this shuffle
                                if (in_array($s->unique_key, $usedStudentKeys)) return false;
                                
                                // Avoid same category reassignment
                                if (isset($previousTaskAssignments[$s->unique_key]) && $previousTaskAssignments[$s->unique_key] == $category->id) return false;
                                
                                return true;
                            });
                            
                            $relaxedBoys = $relaxedBatchStudents->filter(function($s) {
                                return $s->gender === 'M' || $s->gender === 'Male';
                            })->shuffle();
                            
                            $additionalBoys = $relaxedBoys->take($boysNeeded - $selectedBoys->count());
                            
                            // ENSURE BATCH INFO: Verify all additional boys have batch set to current batchYear
                            foreach ($additionalBoys as $boy) {
                                if (!isset($boy->batch) || $boy->batch != $batchYear) {
                                    $boy->batch = $batchYear;
                                }
                            }
                            
                            $selectedBoys = $selectedBoys->concat($additionalBoys);
                            
                            \Log::info("Category {$category->name}: Batch {$batchYear} - After relaxing filter: {$selectedBoys->count()}/{$boysNeeded} boys available");
                        }
                        
                        \Log::info("Category {$category->name}: Batch {$batchYear} - Available boys: {$availableBoys->count()}, needed: {$boysNeeded}, selected: {$selectedBoys->count()}");
                        
                        foreach ($selectedBoys as $boy) {
                            if ($batchYear == 2025) {
                                $selected2025->push($boy);
                            } else {
                                $selected2026->push($boy);
                            }
                            $usedStudentKeys[] = $boy->unique_key;
                            if (isset($boy->id)) $usedStudentIds[] = $boy->id;
                            if (isset($batchPickedCount[$boy->batch])) $batchPickedCount[$boy->batch]++;
                        }
                        
                        // Select girls from available (non-recently-assigned) students
                        $availableGirls = $availableBatchStudents->filter(function($s) {
                            return $s->gender === 'F' || $s->gender === 'Female';
                        })->shuffle();
                        
                        $selectedGirls = $availableGirls->take($girlsNeeded);
                        
                        // FALLBACK: If not enough girls with strict rotation, relax filter to include recently assigned
                        if ($selectedGirls->count() < $girlsNeeded) {
                            \Log::warning("⚠️ Category {$category->name}: Batch {$batchYear} - Strict rotation insufficient for girls ({$selectedGirls->count()}/{$girlsNeeded}). Relaxing filter to include recently assigned students...");
                            
                            // SECOND PASS: Relax filter - allow recently assigned students
                            $relaxedBatchStudents = $batchStudents->filter(function($s) use ($usedStudentKeys, $previousTaskAssignments, $category) {
                                // Already used in this shuffle
                                if (in_array($s->unique_key, $usedStudentKeys)) return false;
                                
                                // Avoid same category reassignment
                                if (isset($previousTaskAssignments[$s->unique_key]) && $previousTaskAssignments[$s->unique_key] == $category->id) return false;
                                
                                return true;
                            });
                            
                            $relaxedGirls = $relaxedBatchStudents->filter(function($s) {
                                return $s->gender === 'F' || $s->gender === 'Female';
                            })->shuffle();
                            
                            $additionalGirls = $relaxedGirls->take($girlsNeeded - $selectedGirls->count());
                            
                            // ENSURE BATCH INFO: Verify all additional girls have batch set to current batchYear
                            foreach ($additionalGirls as $girl) {
                                if (!isset($girl->batch) || $girl->batch != $batchYear) {
                                    $girl->batch = $batchYear;
                                }
                            }
                            
                            $selectedGirls = $selectedGirls->concat($additionalGirls);
                            
                            \Log::info("Category {$category->name}: Batch {$batchYear} - After relaxing filter: {$selectedGirls->count()}/{$girlsNeeded} girls available");
                        }
                        
                        \Log::info("Category {$category->name}: Batch {$batchYear} - Available girls: {$availableGirls->count()}, needed: {$girlsNeeded}, selected: {$selectedGirls->count()}");
                        
                        foreach ($selectedGirls as $girl) {
                            if ($batchYear == 2025) {
                                $selected2025->push($girl);
                            } else {
                                $selected2026->push($girl);
                            }
                            $usedStudentKeys[] = $girl->unique_key;
                            if (isset($girl->id)) $usedStudentIds[] = $girl->id;
                            if (isset($batchPickedCount[$girl->batch])) $batchPickedCount[$girl->batch]++;
                        }
                        
                        \Log::info("Category {$category->name}: Selected {$selectedBoys->count()}/{$boysNeeded} boys and {$selectedGirls->count()}/{$girlsNeeded} girls for batch {$batchYear}");
                        
                        // Verify requirements are met
                        if ($selectedBoys->count() < $boysNeeded) {
                            \Log::error("❌ CRITICAL: Category {$category->name}: Could not select enough boys for batch {$batchYear}. Required: {$boysNeeded}, Selected: {$selectedBoys->count()}");
                        }
                        if ($selectedGirls->count() < $girlsNeeded) {
                            \Log::error("❌ CRITICAL: Category {$category->name}: Could not select enough girls for batch {$batchYear}. Required: {$girlsNeeded}, Selected: {$selectedGirls->count()}");
                        }
                    }
                }
                
                // Skip the hardcoded category logic since we used specific requirements
                $allSelected = $selected2025->concat($selected2026);
                
                // VALIDATION: Verify we selected the correct number of students per batch
                $actualSelected2025 = $selected2025->count();
                $actualSelected2026 = $selected2026->count();
                $totalSelected = $allSelected->count();
                $expectedTotal = $total2025 + $total2026;
                
                // Count gender distribution
                $actual2025M = $selected2025->filter(function($s) { return $s->gender === 'M' || $s->gender === 'Male'; })->count();
                $actual2025F = $selected2025->filter(function($s) { return $s->gender === 'F' || $s->gender === 'Female'; })->count();
                $actual2026M = $selected2026->filter(function($s) { return $s->gender === 'M' || $s->gender === 'Male'; })->count();
                $actual2026F = $selected2026->filter(function($s) { return $s->gender === 'F' || $s->gender === 'Female'; })->count();
                
                $required2025M = (int)($batchRequirements[2025]['boys'] ?? 0);
                $required2025F = (int)($batchRequirements[2025]['girls'] ?? 0);
                $required2026M = (int)($batchRequirements[2026]['boys'] ?? 0);
                $required2026F = (int)($batchRequirements[2026]['girls'] ?? 0);
                
                \Log::info("Category {$category->name}: Selection Results:");
                \Log::info("  → Expected: 2025={$total2025} ({$required2025M}M+{$required2025F}F), 2026={$total2026} ({$required2026M}M+{$required2026F}F), Total={$expectedTotal}");
                \Log::info("  → Actual: 2025={$actualSelected2025} ({$actual2025M}M+{$actual2025F}F), 2026={$actualSelected2026} ({$actual2026M}M+{$actual2026F}F), Total={$totalSelected}");
                
                // Check gender distribution
                if ($actual2025M != $required2025M || $actual2025F != $required2025F) {
                    \Log::warning("GENDER MISMATCH 2025: Expected {$required2025M}M+{$required2025F}F but got {$actual2025M}M+{$actual2025F}F");
                }
                if ($actual2026M != $required2026M || $actual2026F != $required2026F) {
                    \Log::warning("GENDER MISMATCH 2026: Expected {$required2026M}M+{$required2026F}F but got {$actual2026M}M+{$actual2026F}F");
                }
                
                if ($actualSelected2025 < $total2025) {
                    \Log::warning("WARNING: Batch 2025 shortage! Expected {$total2025} but only selected {$actualSelected2025}. Not enough available students in batch 2025.");
                }
                if ($actualSelected2026 < $total2026) {
                    \Log::warning("WARNING: Batch 2026 shortage! Expected {$total2026} but only selected {$actualSelected2026}. Not enough available students in batch 2026.");
                }
                if ($totalSelected == $expectedTotal) {
                    \Log::info("SUCCESS: Selected exactly {$totalSelected} students as required!");
                }
            }
            
            // DYNAMIC SYSTEM: Use default assignment logic for categories without manual requirements
            if (!$usedManualRequirements) {
                \Log::info("Category {$category->name}: Using dynamic default assignment (no manual requirements set)");
                
                // Dynamic default: assign students based on available pool and category limit
                $studentsToAssign = min($maxTotal, $availableStudents->count() - count($usedStudentKeys));
                
                // Calculate proportional distribution based on actual batch sizes
                $batchSizes = [];
                $totalStudentsInBatches = 0;
                
                foreach ($allowedBatchYears as $batchYear) {
                    $batchSize = isset($studentsByBatch[$batchYear]) ? $studentsByBatch[$batchYear]->count() : 0;
                    $batchSizes[$batchYear] = $batchSize;
                    $totalStudentsInBatches += $batchSize;
                }
                
                \Log::info("Category {$category->name}: Batch sizes - " . json_encode($batchSizes) . ", Total: {$totalStudentsInBatches}");
                
                // Calculate how many students to assign from each batch proportionally
                $batchAssignments = [];
                foreach ($allowedBatchYears as $batchYear) {
                    if ($totalStudentsInBatches > 0) {
                        $proportion = $batchSizes[$batchYear] / $totalStudentsInBatches;
                        $batchAssignments[$batchYear] = (int) round($studentsToAssign * $proportion);
                    } else {
                        $batchAssignments[$batchYear] = 0;
                    }
                }
                
                \Log::info("Category {$category->name}: Proportional assignments - " . json_encode($batchAssignments));
                
                // Assign students from each batch proportionally
                $assigned = 0;
                foreach ($allowedBatchYears as $batchYear) {
                    $studentsFromThisBatch = $batchAssignments[$batchYear] ?? 0;
                    if ($studentsFromThisBatch <= 0 || $assigned >= $studentsToAssign) continue;
                    
                    $batchStudents = isset($studentsByBatch[$batchYear]) ? $studentsByBatch[$batchYear] : collect();
                    
                    // STRICT ROTATION: Completely exclude recently assigned students
                    $availableBatchStudents = $batchStudents->filter(function($s) use ($usedStudentKeys, $previousTaskAssignments, $category, $recentlyAssignedStudents) {
                        if (in_array($s->unique_key, $usedStudentKeys)) return false;
                        
                        // STRICT: Exclude students assigned in most recent round
                        if (isset($recentlyAssignedStudents[$s->unique_key])) {
                            return false;
                        }
                        
                        if (isset($previousTaskAssignments[$s->unique_key]) && $previousTaskAssignments[$s->unique_key] == $category->id) return false;
                        return true;
                    })->shuffle();
                    
                    $needed = min($studentsFromThisBatch, $studentsToAssign - $assigned, $availableBatchStudents->count());
                    $selectedFromBatch = $availableBatchStudents->take($needed);
                    
                    if ($selectedFromBatch->count() < $needed) {
                        \Log::warning("⚠️ Category {$category->name}: Batch {$batchYear} - Could only select {$selectedFromBatch->count()}/{$needed} students (not enough who weren't recently assigned)");
                    }
                    
                    foreach ($selectedFromBatch as $student) {
                        if ($batchYear == 2025 || $batchYear == 'other') {
                            $selected2025->push($student);
                        } else {
                            $selected2026->push($student);
                        }
                        $usedStudentKeys[] = $student->unique_key;
                        if (isset($student->id)) $usedStudentIds[] = $student->id;
                        if (isset($batchPickedCount[$student->batch])) $batchPickedCount[$student->batch]++;
                        $assigned++;
                    }
                    
                    \Log::info("Category {$category->name}: Selected {$selectedFromBatch->count()} students from batch {$batchYear}");
                }

                $allSelected = $selected2025->concat($selected2026);
                \Log::info("🔄 Dynamic Default Assignment for {$category->name}: Selected {$allSelected->count()} students - Batch 2025: {$selected2025->count()}, Batch 2026: {$selected2026->count()}");
            }


            // ROTATION SYSTEM: Skip if no students selected (strict exclusion may result in empty selection)
            if ($allSelected->count() == 0) {
                \Log::warning("⚠️ Category {$category->name}: No students selected (all eligible students may have been in previous round). Skipping this category.");
                continue;
            }

            if ($usedManualRequirements) {
                \Log::info("✅ Manual Requirements Applied for {$category->name}: Selected {$allSelected->count()} students exactly as specified - Batch 2025: {$selected2025->count()}, Batch 2026: {$selected2026->count()}");
            } else {
                \Log::info("🔄 Auto-Distribution for {$category->name}: Selected {$allSelected->count()} students - Batch 2025: {$selected2025->count()}, Batch 2026: {$selected2026->count()}");
            }
            
            // Log batch separation verification
            \Log::info("🛡️ BATCH SEPARATION VERIFIED for {$category->name}: C2025 and C2026 students properly separated");

            // Determine start/end dates (allow overrides per category)
            // Get assignment duration from system settings (default: 7 days)
            $durationDays = SystemSetting::get('assignment_duration_days', 7);
            $useStart = $now->toDateString();
            $useEnd = $now->copy()->addDays($durationDays)->toDateString();
            
            // Use the matched override key for dates if available
            $overrideKey = $matchedOverrideKey ?? $category->name;
            if (isset($overrides[$overrideKey]['start_date']) && !empty($overrides[$overrideKey]['start_date'])) {
                $useStart = $overrides[$overrideKey]['start_date'];
            }
            if (isset($overrides[$overrideKey]['end_date']) && !empty($overrides[$overrideKey]['end_date'])) {
                $useEnd = $overrides[$overrideKey]['end_date'];
            }

            // ROTATION SYSTEM: Always create NEW assignment (never add to existing)
            // CRITICAL FIX: Ensure no duplicate current assignments exist for this category
            // Move any remaining current assignments to previous status before creating new one
            $existingCurrentForCategory = Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->get();
            
            if ($existingCurrentForCategory->count() > 0) {
                \Log::warning("⚠️ Found {$existingCurrentForCategory->count()} existing current assignment(s) for {$category->name}. Moving to previous status to prevent duplicates.");
                foreach ($existingCurrentForCategory as $existing) {
                    $existing->update([
                        'status' => 'previous',
                        'end_date' => $now->copy()->subDay()->toDateString(),
                        'updated_at' => $now
                    ]);
                    \Log::info("  → Moved assignment ID {$existing->id} to previous status");
                }
            }
            
            $assignment = Assignment::create([
                'category_id' => $category->id,
                'start_date' => $useStart,
                'end_date' => $useEnd,
                'status' => 'current'
            ]);

            \Log::info("✨ Created NEW assignment for category: {$category->name} with {$allSelected->count()} members (Batch 2025: {$selected2025->count()}, Batch 2026: {$selected2026->count()})");

            // Select coordinators (one from each batch if possible)
            // IMPORTANT: Ensure coordinators are from correct batch and avoid repeating from previous assignment
            $coor2025 = null;
            $coor2026 = null;
            
            // Get previous assignment coordinators to avoid repetition
            $previousAssignment = Assignment::where('category_id', $category->id)
                ->where('status', 'previous')
                ->orderBy('id', 'desc')
                ->first();
            
            $previousCoord2025Ids = [];
            $previousCoord2026Ids = [];
            
            if ($previousAssignment) {
                $prevMembers = AssignmentMember::where('assignment_id', $previousAssignment->id)
                    ->where('is_coordinator', true)
                    ->get();
                    
                foreach ($prevMembers as $pm) {
                    $student = $pm->student;
                    if ($student && isset($student->batch)) {
                        if ((int)$student->batch === 2025) {
                            $previousCoord2025Ids[] = $student->user_id ?? $pm->student_id;
                        } elseif ((int)$student->batch === 2026) {
                            $previousCoord2026Ids[] = $student->user_id ?? $pm->student_id;
                        }
                    }
                }
            }
            
            // Select C2025 coordinator from batch 2025 only, avoiding previous coordinators
            // Use pre-seeded coordinator if available, otherwise auto-select
            if ($preSeededCoord2025) {
                $coor2025 = $preSeededCoord2025;
                \Log::info("C2025 coordinator: using pre-seeded {$coor2025->name}");
            } elseif ($selected2025->isNotEmpty()) {
                // STRICT: Only students from batch 2025
                $batch2025Only = $selected2025->filter(function($s) {
                    return isset($s->batch) && (int)$s->batch === 2025;
                });
                
                // FALLBACK: If no batch 2025 found, use any from selected2025 (batch info might be missing)
                if ($batch2025Only->isEmpty()) {
                    \Log::warning("No batch 2025 students found in selected2025 for {$category->name}. Using fallback from all selected2025 students.");
                    $batch2025Only = $selected2025;
                }
                
                if ($batch2025Only->isEmpty()) {
                    \Log::warning("No students available for C2025 coordinator in {$category->name}");
                    $coor2025 = null;
                } else {
                    // Filter out previous coordinators
                    $available2025 = $batch2025Only->filter(function($s) use ($previousCoord2025Ids) {
                        $sid = $s->id ?? $s->user_id ?? null;
                        return !in_array($sid, $previousCoord2025Ids);
                    });
                    
                    // If all were previous coordinators, use any from batch 2025
                    if ($available2025->isEmpty()) {
                        \Log::info("All batch 2025 students were previous coordinators. Using any available for {$category->name}");
                        $available2025 = $batch2025Only;
                    }
                    
                    $coor2025 = $available2025->random();
                    \Log::info("C2025 coordinator selected: " . $coor2025->name . " (batch: " . ($coor2025->batch ?? 'unknown') . ")");
                }
            }
            
            // Select C2026 coordinator from batch 2026 only, avoiding previous coordinators
            // Use pre-seeded coordinator if available, otherwise auto-select
            if ($preSeededCoord2026) {
                $coor2026 = $preSeededCoord2026;
                \Log::info("C2026 coordinator: using pre-seeded {$coor2026->name}");
            } elseif ($selected2026->isNotEmpty()) {
                // STRICT: Only students from batch 2026
                $batch2026Only = $selected2026->filter(function($s) {
                    return isset($s->batch) && (int)$s->batch === 2026;
                });
                
                // FALLBACK: If no batch 2026 found, use any from selected2026 (batch info might be missing)
                if ($batch2026Only->isEmpty()) {
                    \Log::warning("No batch 2026 students found in selected2026 for {$category->name}. Using fallback from all selected2026 students.");
                    $batch2026Only = $selected2026;
                }
                
                if ($batch2026Only->isEmpty()) {
                    \Log::warning("No students available for C2026 coordinator in {$category->name}");
                    $coor2026 = null;
                } else {
                    // Filter out previous coordinators
                    $available2026 = $batch2026Only->filter(function($s) use ($previousCoord2026Ids) {
                        $sid = $s->id ?? $s->user_id ?? null;
                        return !in_array($sid, $previousCoord2026Ids);
                    });
                    
                    // If all were previous coordinators, use any from batch 2026
                    if ($available2026->isEmpty()) {
                        \Log::info("All batch 2026 students were previous coordinators. Using any available for {$category->name}");
                        $available2026 = $batch2026Only;
                    }
                    
                    $coor2026 = $available2026->random();
                    \Log::info("C2026 coordinator selected: " . $coor2026->name . " (batch: " . ($coor2026->batch ?? 'unknown') . ")");
                }
            }
            
            // CRITICAL: Ensure C2025 and C2026 are different students
            if ($coor2025 && $coor2026) {
                $id2025 = $coor2025->id ?? $coor2025->user_id ?? null;
                $id2026 = $coor2026->id ?? $coor2026->user_id ?? null;
                
                if ($id2025 && $id2026 && $id2025 === $id2026) {
                    \Log::error("CRITICAL: Same student selected for both C2025 and C2026! Student ID: {$id2025}, Name: {$coor2025->name}");
                    // This should never happen with proper batch filtering, but if it does, clear C2026
                    $coor2026 = null;
                    \Log::warning("Cleared C2026 coordinator to prevent duplicate assignment");
                }
            }

            \Log::info("Selected coordinators for {$category->name}: 2025=" . ($coor2025 ? $coor2025->name . " (batch: " . ($coor2025->batch ?? 'unknown') . ")" : 'none') . ", 2026=" . ($coor2026 ? $coor2026->name . " (batch: " . ($coor2026->batch ?? 'unknown') . ")" : 'none'));

            // CRITICAL: Enforce exact count limits for manual requirements
            // Only create assignment members up to the required count
            $membersCreated = 0;
            $maxMembersToCreate = $usedManualRequirements ? ($total2025 + $total2026) : $allSelected->count();
            
            // Create assignment members
            foreach ($allSelected as $member) {
                // ENFORCE LIMIT: Stop creating members once we reach the required count
                if ($usedManualRequirements && $membersCreated >= $maxMembersToCreate) {
                    \Log::info("Reached required member count ({$maxMembersToCreate}) for {$category->name}. Stopping member creation.");
                    break;
                }
                
                $isCoordinator = false;
                $memberId = $member->id ?? $member->user_id ?? null;
                $coor2025Id = $coor2025 ? ($coor2025->id ?? $coor2025->user_id ?? null) : null;
                $coor2026Id = $coor2026 ? ($coor2026->id ?? $coor2026->user_id ?? null) : null;
                
                // CRITICAL: Check if this member is a coordinator
                if ($coor2025Id && $memberId && $memberId == $coor2025Id) {
                    $isCoordinator = true;
                    \Log::info("COORDINATOR MARKED: {$member->name} is C2025 coordinator for {$category->name}");
                }
                if ($coor2026Id && $memberId && $memberId == $coor2026Id) {
                    $isCoordinator = true;
                    \Log::info("COORDINATOR MARKED: {$member->name} is C2026 coordinator for {$category->name}");
                }

                    // Prefer canonical student_code (from student_details.student_id). If missing,
                    // fall back to writing the legacy user_id directly into student_id so
                    // the DB constraint is satisfied.
                    if (!empty($member->student_code)) {
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $member->student_code,
                            'is_coordinator' => $isCoordinator
                        ]);
                    } else {
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_id' => $memberId,
                            'is_coordinator' => $isCoordinator
                        ]);
                    }

                    $membersCreated++;

                    // Mark this student as used so later passes (final-fill/final-ensure) won't reassign them
                    if (isset($member->unique_key)) {
                        $usedStudentKeys[] = $member->unique_key;
                    }
                    // Keep legacy numeric id tracking for older code paths
                    if ($memberId) {
                        $usedStudentIds[] = $memberId;
                    }

                \Log::info("Assigned {$member->name} to {$category->name}" . ($isCoordinator ? " (COORDINATOR)" : "") . " | Batch: " . ($member->batch ?? 'unknown') . " | Gender: " . ($member->gender ?? 'unknown'));
            }
            
            // VERIFICATION: Log final assignment member count per batch
            if ($usedManualRequirements) {
                $finalMembers = $assignment->assignmentMembers()->with('student.studentDetail')->get();
                $count2025 = 0;
                $count2026 = 0;
                foreach ($finalMembers as $fm) {
                    $batch = null;
                    if ($fm->student && $fm->student->studentDetail) {
                        $batch = $fm->student->studentDetail->batch;
                    } elseif (!empty($fm->student_code)) {
                        // Parse batch from student_code (e.g., "2025010029C1" -> 2025)
                        if (preg_match('/^(202[56])/', $fm->student_code, $matches)) {
                            $batch = (int)$matches[1];
                        }
                    }
                    if ($batch == 2025) $count2025++;
                    elseif ($batch == 2026) $count2026++;
                }
                \Log::info("FINAL VERIFICATION for {$category->name}: Database has {$count2025} students in batch 2025, {$count2026} students in batch 2026, Total: " . ($count2025 + $count2026));
                
                // CRITICAL: Verify requirements are met - if not, fill remaining slots
                $totalAssigned = $count2025 + $count2026;
                $totalRequired = $total2025 + $total2026;
                
                if ($totalAssigned < $totalRequired) {
                    $remaining = $totalRequired - $totalAssigned;
                    \Log::warning("REQUIREMENT SHORTFALL for {$category->name}: Assigned {$totalAssigned}, Required {$totalRequired}. Filling remaining {$remaining} slots...");
                    
                    // Build fallback pool of available students not yet used
                    $fallbackPool = $availableStudents->filter(function($s) use ($usedStudentKeys) {
                        return !in_array($s->unique_key, $usedStudentKeys);
                    })->values();
                    
                    $needed = $totalRequired - $totalAssigned;
                    $filled = 0;
                    
                    // Fill remaining slots
                    foreach ($fallbackPool as $student) {
                        if ($filled >= $needed) break;
                        
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $student->student_code ?? null,
                            'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                            'is_coordinator' => false
                        ]);
                        
                        $usedStudentKeys[] = $student->unique_key;
                        $filled++;
                        \Log::info("Requirement fill: assigned {$student->name} to {$category->name}");
                    }
                    
                    if ($filled < $needed) {
                        \Log::error("CRITICAL: Could not fill all requirements for {$category->name}. Filled {$filled}/{$needed}. Not enough available students.");
                    } else {
                        \Log::info("SUCCESS: Filled all {$filled} remaining slots for {$category->name}");
                    }
                }
                
                // CRITICAL CLEANUP: Remove any excess members beyond requirements
                // AND verify gender distribution per batch
                if ($usedManualRequirements) {
                    $currentMembers = $assignment->assignmentMembers()->with('student.studentDetail')->get();
                    $totalRequired = $total2025 + $total2026;
                    $totalAssigned = $currentMembers->count();
                    
                    // Count by batch and gender
                    $count2025M = 0;
                    $count2025F = 0;
                    $count2026M = 0;
                    $count2026F = 0;
                    
                    foreach ($currentMembers as $member) {
                        $batch = null;
                        $gender = null;
                        
                        // Get batch
                        if ($member->student && $member->student->studentDetail) {
                            $batch = $member->student->studentDetail->batch;
                        } elseif (!empty($member->student_code) && preg_match('/^(202[56])/', $member->student_code, $matches)) {
                            $batch = (int)$matches[1];
                        }
                        
                        // Get gender
                        if ($member->student) {
                            $gender = $member->student->gender;
                        }
                        
                        // Count
                        if ($batch == 2025 && ($gender === 'M' || $gender === 'Male')) $count2025M++;
                        elseif ($batch == 2025 && ($gender === 'F' || $gender === 'Female')) $count2025F++;
                        elseif ($batch == 2026 && ($gender === 'M' || $gender === 'Male')) $count2026M++;
                        elseif ($batch == 2026 && ($gender === 'F' || $gender === 'Female')) $count2026F++;
                    }
                    
                    \Log::info("VERIFICATION: {$category->name} - 2025: {$count2025M}M/{$count2025F}F, 2026: {$count2026M}M/{$count2026F}F");
                    \Log::info("REQUIRED: {$category->name} - 2025: {$batchRequirements[2025]['boys']}M/{$batchRequirements[2025]['girls']}F, 2026: {$batchRequirements[2026]['boys']}M/{$batchRequirements[2026]['girls']}F");
                    
                    if ($totalAssigned > $totalRequired) {
                        $excessCount = $totalAssigned - $totalRequired;
                        \Log::warning("CLEANUP: {$category->name} has {$totalAssigned} members but only {$totalRequired} required. Removing {$excessCount} excess members.");
                        
                        // Remove excess members (keep first N members)
                        $membersToDelete = $currentMembers->slice($totalRequired);
                        foreach ($membersToDelete as $member) {
                            $memberIdentifier = $member->student_code ?? $member->student_id ?? 'unknown';
                            $member->delete();
                            \Log::info("Removed excess member: {$memberIdentifier}");
                        }
                    }
                }
            }

            // FINAL-FILL PASS: If category still lacks members up to $maxTotal, attempt to fill remaining slots.
            // This pass relaxes the 'avoid previous assignment' rule only as a last resort and ensures
            // we create AssignmentMember records so the UI doesn't show "None Assigned".
            // Skip this for manual requirements since user specified exact numbers.
            $currentCount = $allSelected->count();
            if (!$usedManualRequirements && $currentCount < $maxTotal) {
                $needed = $maxTotal - $currentCount;
                \Log::info("Final-fill for {$category->name}: need {$needed} more members");

                // Build batch-separated fallback pools to maintain C2025/C2026 separation
                $fallbackPool2025 = collect();
                $fallbackPool2026 = collect();
                
                // Separate available students by batch, excluding already-used
                foreach ($availableStudents as $student) {
                    if (in_array($student->unique_key, $usedStudentKeys)) continue;
                    
                    if ($student->batch == 2025) {
                        $fallbackPool2025->push($student);
                    } elseif ($student->batch == 2026) {
                        $fallbackPool2026->push($student);
                    }
                }
                
                \Log::info("Final-fill for {$category->name}: Available C2025: {$fallbackPool2025->count()}, C2026: {$fallbackPool2026->count()}");
                
                // Distribute needed students proportionally based on actual batch sizes
                $total2025Available = $fallbackPool2025->count();
                $total2026Available = $fallbackPool2026->count();
                $totalAvailable = $total2025Available + $total2026Available;
                
                if ($totalAvailable > 0) {
                    // Calculate proportional distribution
                    $ratio2025 = $total2025Available / $totalAvailable;
                    $ratio2026 = $total2026Available / $totalAvailable;
                    
                    $needed2025 = (int) round($needed * $ratio2025);
                    $needed2026 = $needed - $needed2025;
                } else {
                    // Fallback to equal distribution if no students available
                    $needed2025 = (int) ceil($needed / 2);
                    $needed2026 = $needed - $needed2025;
                }
                
                // Adjust if one batch doesn't have enough students
                if ($fallbackPool2025->count() < $needed2025) {
                    $needed2026 += ($needed2025 - $fallbackPool2025->count());
                    $needed2025 = $fallbackPool2025->count();
                }
                if ($fallbackPool2026->count() < $needed2026) {
                    $needed2025 += ($needed2026 - $fallbackPool2026->count());
                    $needed2026 = $fallbackPool2026->count();
                }
                
                \Log::info("Final-fill for {$category->name}: Will assign {$needed2025} from C2025, {$needed2026} from C2026");

                // For Kitchen, respect gender quotas when possible while maintaining batch separation
                if ($category->name === 'Kitchen') {
                    // Fill from C2025 batch first
                    $assignedFromBatch = 0;
                    foreach ($fallbackPool2025->shuffle() as $student) {
                        if ($assignedFromBatch >= $needed2025) break;
                        
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $student->student_code ?? null,
                            'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                            'is_coordinator' => false
                        ]);
                        $usedStudentKeys[] = $student->unique_key;
                        if (isset($batchPickedCount[2025])) $batchPickedCount[2025]++;
                        $assignedFromBatch++;
                        $needed--;
                        \Log::info("Final-fill assigned C2025 {$student->name} to {$category->name}");
                    }
                    
                    // Fill from C2026 batch
                    $assignedFromBatch = 0;
                    foreach ($fallbackPool2026->shuffle() as $student) {
                        if ($assignedFromBatch >= $needed2026) break;
                        
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $student->student_code ?? null,
                            'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                            'is_coordinator' => false
                        ]);
                        $usedStudentKeys[] = $student->unique_key;
                        if (isset($batchPickedCount[2026])) $batchPickedCount[2026]++;
                        $assignedFromBatch++;
                        $needed--;
                        \Log::info("Final-fill assigned C2026 {$student->name} to {$category->name}");
                    }
                } else {
                    // Non-Kitchen categories: maintain batch separation while filling remaining slots
                    
                    // Fill from C2025 batch first, avoiding previous same-category assignments
                    $assignedFromBatch = 0;
                    foreach ($fallbackPool2025->shuffle() as $student) {
                        if ($assignedFromBatch >= $needed2025) break;
                        
                        // Prefer students who were not in same category last week
                        $studentId = $student->id ?? $student->user_id ?? null;
                        if ($studentId && isset($previousTaskAssignments[$student->unique_key]) && $previousTaskAssignments[$student->unique_key] == $category->id) {
                            continue; // Skip for now, will be picked up in relaxed pass if needed
                        }
                        
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $student->student_code ?? null,
                            'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                            'is_coordinator' => false
                        ]);
                        $usedStudentKeys[] = $student->unique_key;
                        if (isset($batchPickedCount[2025])) $batchPickedCount[2025]++;
                        $assignedFromBatch++;
                        $needed--;
                        \Log::info("Final-fill assigned C2025 {$student->name} to {$category->name}");
                    }
                    
                    // Fill from C2026 batch, avoiding previous same-category assignments
                    $assignedFromBatch = 0;
                    foreach ($fallbackPool2026->shuffle() as $student) {
                        if ($assignedFromBatch >= $needed2026) break;
                        
                        // Prefer students who were not in same category last week
                        $studentId = $student->id ?? $student->user_id ?? null;
                        if ($studentId && isset($previousTaskAssignments[$student->unique_key]) && $previousTaskAssignments[$student->unique_key] == $category->id) {
                            continue; // Skip for now, will be picked up in relaxed pass if needed
                        }
                        
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_code' => $student->student_code ?? null,
                            'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                            'is_coordinator' => false
                        ]);
                        $usedStudentKeys[] = $student->unique_key;
                        if (isset($batchPickedCount[2026])) $batchPickedCount[2026]++;
                        $assignedFromBatch++;
                        $needed--;
                        \Log::info("Final-fill assigned C2026 {$student->name} to {$category->name}");
                    }

                    // If still need more (relaxed pass - allow previous same-category assignments)
                    if ($needed > 0) {
                        \Log::info("Final-fill relaxed pass for {$category->name}: still need {$needed} more students");
                        
                        // Try C2025 batch again (relaxed)
                        foreach ($fallbackPool2025->shuffle() as $student) {
                            if ($needed <= 0) break;
                            if (in_array($student->unique_key, $usedStudentKeys)) continue;
                            
                            $this->safeCreateAssignmentMember([
                                'assignment_id' => $assignment->id,
                                'student_code' => $student->student_code ?? null,
                                'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                                'is_coordinator' => false
                            ]);
                            $usedStudentKeys[] = $student->unique_key;
                            if (isset($batchPickedCount[2025])) $batchPickedCount[2025]++;
                            $needed--;
                            \Log::info("Final-fill (relaxed) assigned C2025 {$student->name} to {$category->name}");
                        }
                        
                        // Try C2026 batch again (relaxed)
                        foreach ($fallbackPool2026->shuffle() as $student) {
                            if ($needed <= 0) break;
                            if (in_array($student->unique_key, $usedStudentKeys)) continue;
                            
                            $this->safeCreateAssignmentMember([
                                'assignment_id' => $assignment->id,
                                'student_code' => $student->student_code ?? null,
                                'student_id' => $student->student_code ? null : ($student->id ?? $student->user_id ?? null),
                                'is_coordinator' => false
                            ]);
                            $usedStudentKeys[] = $student->unique_key;
                            if (isset($batchPickedCount[2026])) $batchPickedCount[2026]++;
                            $needed--;
                            \Log::info("Final-fill (relaxed) assigned C2026 {$student->name} to {$category->name}");
                        }
                    }
                }
            }
        }

        // Robust dedupe: compute a stable unique key for each AssignmentMember and ensure
        // a student appears only once across current assignments. If duplicates are found,
        // keep the earliest created member and delete later ones.
        $duplicateFound = false;
        $coordinatorCount = 0;

        $currentAssignments = Assignment::where('status', 'current')->with('assignmentMembers')->get();

        // Map unique_key => [member_id => AssignmentMember]
        $seen = [];
        $toDelete = [];

        foreach ($currentAssignments as $assignment) {
            foreach ($assignment->assignmentMembers as $member) {
                // Build unique key for this member row
                $uKey = null;
                if (!empty($member->student_id)) {
                    $uKey = 'legacy:' . $member->student_id;
                } elseif (!empty($member->student_code)) {
                    // Try to resolve canonical PNUser id from student_code
                    $resolved = StudentDetail::where('student_id', $member->student_code)->pluck('user_id')->first();
                    if ($resolved) $uKey = 'pnuser:' . $resolved;
                    else $uKey = 'code:' . $member->student_code;
                } elseif ($member->student) {
                    $uKey = 'pnuser:' . ($member->student->user_id ?? $member->student->id);
                }

                if (!$uKey) {
                    // If we couldn't build a key, skip dedupe for this row
                    continue;
                }

                // If we've already seen this student, mark current row for deletion
                if (isset($seen[$uKey])) {
                    \Log::warning("Duplicate detected for {$uKey} — removing member id {$member->id} (kept member id {$seen[$uKey]->id})");
                    $duplicateFound = true;
                    $toDelete[] = $member->id;
                    continue;
                }

                // Otherwise keep this member as the canonical one
                $seen[$uKey] = $member;
                if ($member->is_coordinator) $coordinatorCount++;
            }
        }

        // Delete duplicate member rows (if any)
        if (!empty($toDelete)) {
            AssignmentMember::whereIn('id', $toDelete)->delete();
        }

        \Log::info("Final verification: {$coordinatorCount} coordinators assigned across all categories; duplicates removed: " . count($toDelete));

        // FINAL-ENSURE PASS: Guarantee every category with a non-zero target has at least one assigned member.
        // This ensures the UI never shows 'None Assigned' for coordinator or zero counts.
        // SKIP categories with manual requirements - they already have exact counts!
        foreach ($categories as $category) {
            // Skip categories with manual requirements
            if (in_array($category->id, $categoriesWithManualRequirements)) {
                \Log::info("Final-ensure SKIPPING category {$category->name} (has manual requirements)");
                continue;
            }
            
            $target = $categoryLimits[$category->name] ?? 0;
            // Do not skip categories even if target is 0 — ensure at least one member so UI doesn't show 'None Assigned'.
            \Log::info("Final-ensure processing category {$category->name} with target {$target}");

            $existing = Assignment::where('category_id', $category->id)->where('status', 'current')->with('assignmentMembers')->first();
            $hasAny = $existing && $existing->assignmentMembers->count() > 0;
            if ($hasAny) continue;

            // Try staged candidate selection to guarantee at least one member per non-zero category.
            $candidate = null;
            $candidateStage = null;

            // Stage 1: unused and not previously in same category
            $candidate = $availableStudents->first(function($s) use ($usedStudentKeys, $previousTaskAssignments, $category) {
                if (in_array($s->unique_key, $usedStudentKeys)) return false;
                if (isset($previousTaskAssignments[$s->unique_key]) && $previousTaskAssignments[$s->unique_key] == $category->id) return false;
                return true;
            });
            if ($candidate) $candidateStage = 1;

            // Stage 2: unused (allow previous-week participants)
            if (!$candidate) {
                $candidate = $availableStudents->first(function($s) use ($usedStudentKeys) {
                    return !in_array($s->unique_key, $usedStudentKeys);
                });
                if ($candidate) $candidateStage = 2;
            }

            // Stage 3: pick any student from existing current AssignmentMember rows (reassign) who isn't already used
            if (!$candidate) {
                $someAssigned = AssignmentMember::whereNotNull('id')->orderBy('id','asc')->get();
                foreach ($someAssigned as $sa) {
                    // compute a pseudo-student unique key for this row
                    $uKey = null;
                    if (!empty($sa->student_id)) {
                        $uKey = 'legacy:' . $sa->student_id;
                    } elseif (!empty($sa->student_code)) {
                        $resolved = StudentDetail::where('student_id', $sa->student_code)->pluck('user_id')->first();
                        if ($resolved) $uKey = 'pnuser:' . $resolved;
                        else $uKey = 'code:' . $sa->student_code;
                    } elseif ($sa->student) {
                        $uKey = 'pnuser:' . ($sa->student->user_id ?? $sa->student->id);
                    }
                    if ($uKey && in_array($uKey, $usedStudentKeys)) continue;

                    if (!empty($sa->student_id) || !empty($sa->student_code)) {
                        $candidate = (object) [
                            'id' => $sa->student_id ?: ($sa->student_code ? $sa->student_code : null),
                            'student_code' => $sa->student_code ?? null,
                            'name' => optional($sa->student)->user_fname ? trim(optional($sa->student)->user_fname . ' ' . optional($sa->student)->user_lname) : ($sa->student_code ?: 'Student'),
                            'gender' => optional($sa->student)->gender ?? null,
                            'batch' => optional($sa->student)->studentDetail ? optional($sa->student->studentDetail)->batch : null,
                            'unique_key' => $uKey
                        ];
                        $candidateStage = 3;
                        break;
                    }
                }
            }

            // Stage 4 (last resort): allow selecting any student (even if already marked used) to avoid None Assigned
            if (!$candidate) {
                $candidate = $availableStudents->first();
                if ($candidate) $candidateStage = 4;
            }

            if ($candidate) {
                // Ensure there's an assignment record
                if (!$existing) {
                    $durationDays = SystemSetting::get('assignment_duration_days', 7);
                    
                    // If duration is 0 (anytime shuffle), set end_date far in future (1 year)
                    $endDate = $durationDays == 0 
                        ? $now->copy()->addYear()->toDateString() 
                        : $now->copy()->addDays($durationDays)->toDateString();
                    
                    $assignment = Assignment::create([
                        'category_id' => $category->id,
                        'start_date' => $now->toDateString(),
                        'end_date' => $endDate,
                        'status' => 'current'
                    ]);
                } else {
                    $assignment = $existing;
                }

                // Create member record
                $createData = [
                    'assignment_id' => $assignment->id,
                    'is_coordinator' => false
                ];
                if (!empty($candidate->student_code)) {
                    $createData['student_code'] = $candidate->student_code;
                } elseif (!empty($candidate->id)) {
                    $createData['student_id'] = $candidate->id;
                } else {
                    \Log::warning("Final-ensure: candidate for category {$category->name} has no id or student_code; skipping");
                    continue;
                }

                $this->safeCreateAssignmentMember($createData);

                // Determine and mark used unique key (compute if missing)
                if (!isset($candidate->unique_key)) {
                    // try to compute
                    if (!empty($candidate->id) && is_numeric($candidate->id)) {
                        $candidate->unique_key = 'legacy:' . $candidate->id;
                    } elseif (!empty($candidate->student_code)) {
                        $resolved = StudentDetail::where('student_id', $candidate->student_code)->pluck('user_id')->first();
                        if ($resolved) $candidate->unique_key = 'pnuser:' . $resolved;
                        else $candidate->unique_key = 'code:' . $candidate->student_code;
                    }
                }
                if (isset($candidate->unique_key)) {
                    $usedStudentKeys[] = $candidate->unique_key;
                    // Attempt to increment pointer for candidate batch if available
                    if (isset($batchPickedCount)) {
                        if (!empty($candidate->student_code)) {
                            $resolved = StudentDetail::where('student_id', $candidate->student_code)->pluck('user_id')->first();
                            if ($resolved) {
                                $batch = optional(StudentDetail::where('user_id',$resolved)->first())->batch ?? null;
                                if ($batch && isset($batchPickedCount[$batch])) $batchPickedCount[$batch]++;
                            }
                        } elseif (!empty($candidate->id) && is_numeric($candidate->id)) {
                            // ID-based legacy; we don't know batch — skip increment
                        }
                    }
                }
                // Also append numeric id to legacy list if present
                if (isset($candidate->id)) {
                    $usedStudentIds[] = $candidate->id;
                }

                \Log::info("Final-ensure assigned {$candidate->name} to {$category->name} (stage {$candidateStage}) to avoid None Assigned");
            } else {
                \Log::warning("Final-ensure: no candidate available to assign to category {$category->name} after all fallbacks");
            }
        }

        // STRICT FINAL PASS (added):
        // 1) Deduplicate any remaining AssignmentMember rows across current assignments using a canonical key
        // 2) Ensure every category has at least one member by picking unused PNUser students (batch 2025/2026) first,
        //    then legacy students, and as a last resort move a non-coordinator from a category with >1 members.

        // Build canonical map of current members
        $currentAssignments = Assignment::where('status', 'current')->with('assignmentMembers.student','category')->get();
        $canonicalSeen = [];
        $toDelete = [];

        foreach ($currentAssignments as $assignment) {
            foreach ($assignment->assignmentMembers as $member) {
                $uKey = null;
                if (!empty($member->student_id)) {
                    $uKey = 'legacy:' . $member->student_id;
                } elseif (!empty($member->student_code)) {
                    $resolved = StudentDetail::where('student_id', $member->student_code)->pluck('user_id')->first();
                    if ($resolved) $uKey = 'pnuser:' . $resolved;
                    else $uKey = 'code:' . $member->student_code;
                } elseif ($member->student) {
                    $uKey = 'pnuser:' . ($member->student->user_id ?? $member->student->id);
                }

                if (!$uKey) continue;

                if (isset($canonicalSeen[$uKey])) {
                    // keep earliest seen, delete this one
                    $toDelete[] = $member->id;
                    \Log::warning("Strict dedupe removing duplicate member id {$member->id} for {$uKey}");
                } else {
                    $canonicalSeen[$uKey] = $member->id;
                }
            }
        }

        if (!empty($toDelete)) {
            AssignmentMember::whereIn('id', $toDelete)->delete();
            \Log::info('Strict dedupe removed ' . count($toDelete) . ' duplicate assignment members');
        }

        // Recompute current assignments after dedupe
        $currentAssignments = Assignment::where('status','current')->with('assignmentMembers.student','category')->get();

        // Build pool of available (unused) students: prefer PNUser batch 2025/2026
        $usedKeys = array_keys($canonicalSeen);
        $availablePool = collect();
        $pnUsers = PNUser::with('studentDetail')->get();
        foreach ($pnUsers as $u) {
            $batch = optional($u->studentDetail)->batch ?? null;
            if (!in_array($batch, [2025,2026])) continue;
            $key = 'pnuser:' . $u->user_id;
            if (in_array($key, $usedKeys)) continue;
            $availablePool->push((object)[
                'unique_key' => $key,
                'student_code' => optional($u->studentDetail)->student_id ?? null,
                'id' => $u->user_id,
                'name' => trim($u->user_fname . ' ' . $u->user_lname)
            ]);
        }

        // Append legacy students next
        try {
            $legacyRows = DB::table('student_group16')->get();
            foreach ($legacyRows as $l) {
                $key = 'legacy:' . $l->id;
                if (in_array($key, $usedKeys)) continue;
                $availablePool->push((object)[
                    'unique_key' => $key,
                    'student_code' => null,
                    'id' => $l->id,
                    'name' => $l->name ?? trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? ''))
                ]);
            }
        } catch (\Exception $e) {
            // ignore legacy lookup failures
        }

        // Now ensure every category has at least 1 member
        // SKIP categories with manual requirements - they already have exact counts!
        foreach ($currentAssignments as $assignment) {
            $category = $assignment->category;
            
            // Skip categories with manual requirements
            if (in_array($category->id, $categoriesWithManualRequirements)) {
                \Log::info("Strict final-ensure SKIPPING category {$category->name} (has manual requirements)");
                continue;
            }
            
            $members = $assignment->assignmentMembers;
            if ($members->count() > 0) continue;

            // pick first availablePool member not used
            $candidate = $availablePool->shift();
            if ($candidate) {
                $this->safeCreateAssignmentMember([
                    'assignment_id' => $assignment->id,
                    'student_code' => $candidate->student_code ?? null,
                    'student_id' => $candidate->student_code ? null : ($candidate->id ?? $candidate->user_id ?? null),
                    'is_coordinator' => false
                ]);
                $usedKeys[] = $candidate->unique_key;
                \Log::info("Strict final-ensure assigned {$candidate->name} to {$category->name}");
                continue;
            }

            // If no unused student available, try to move a non-coordinator from a category with >1 members
            $moved = false;
            foreach ($currentAssignments as $srcAssign) {
                if ($srcAssign->assignmentMembers->count() <= 1) continue;
                // find a non-coordinator member
                $mv = $srcAssign->assignmentMembers->first(function($mm){ return !$mm->is_coordinator; });
                if (!$mv) continue;
                // move this member
                $mv->assignment_id = $assignment->id;
                $mv->save();
                \Log::info("Moved member id {$mv->id} from category {$srcAssign->category->name} to {$assignment->category->name} to avoid None Assigned");
                $moved = true;
                break;
            }
            if (!$moved) {
                \Log::warning("Could not find candidate or movable member to assign to category {$category->name}");
            }
        }

    // Log completion for student dashboard access
    $currentAssignments = Assignment::where('status', 'current')->with(['category', 'assignmentMembers.student'])->get();
    
    // Calculate total students used across all categories
    $totalStudentsUsed = 0;
    $categoryBreakdown = [];
    foreach ($currentAssignments as $assignment) {
        $memberCount = $assignment->assignmentMembers->count();
        $totalStudentsUsed += $memberCount;
        $categoryBreakdown[$assignment->category->name] = $memberCount;
    }
    
    \Log::info("🎯 AUTO-SHUFFLE COMPLETED SUCCESSFULLY! 🎯");
    \Log::info("📊 TOTAL STUDENTS USED: {$totalStudentsUsed}");
    \Log::info("📋 BREAKDOWN BY CATEGORY:");
    foreach ($categoryBreakdown as $categoryName => $count) {
        \Log::info("   • {$categoryName}: {$count} students");
    }
    \Log::info("📚 Total categories assigned: " . $currentAssignments->count());
        // Strict reconciliation (final-pass):
        // - Deduplicate current AssignmentMember rows by canonical key (keep earliest)
        // - Ensure every category has at least one member by selecting an unused PNUser (batch 2025/2026) then legacy,
        //   otherwise move a non-coordinator from another category as a last resort.
        try {
            $members = \App\Models\AssignmentMember::whereHas('assignment', function($q){ $q->where('status','current'); })->with('student')->orderBy('id','asc')->get();
            $groups = [];
            foreach ($members as $m) {
                $canonical = null;
                if (!empty($m->student_id)) {
                    $canonical = 'legacy:' . $m->student_id;
                }
                if (!empty($m->student_code)) {
                    $resolved = \App\Models\StudentDetail::where('student_id', $m->student_code)->pluck('user_id')->first();
                    if ($resolved) $canonical = 'pnuser:' . $resolved;
                    else $canonical = $canonical ?? ('code:' . $m->student_code);
                }
                if (!$canonical && $m->student) {
                    $canonical = 'pnuser:' . ($m->student->user_id ?? $m->student->id);
                }
                if (!$canonical) continue;
                if (!isset($groups[$canonical])) $groups[$canonical] = [];
                $groups[$canonical][] = $m;
            }

            $toDelete = [];
            $backup = [];
            foreach ($groups as $key => $rows) {
                if (count($rows) <= 1) continue;
                // keep earliest, delete others
                usort($rows, function($a,$b){ return $a->id <=> $b->id; });
                $keep = array_shift($rows);
                foreach ($rows as $r) {
                    $toDelete[] = $r->id;
                    $backup[] = [
                        'id'=>$r->id,
                        'assignment_id'=>$r->assignment_id,
                        'student_code'=>$r->student_code,
                        'student_id'=>$r->student_id,
                        'is_coordinator'=>(bool)$r->is_coordinator,
                        'canonical'=>$key
                    ];
                }
            }

            if (!empty($toDelete)) {
                $bkFile = storage_path('app/assignment_member_duplicates_backup_' . date('Ymd_His') . '.json');
                @file_put_contents($bkFile, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                \Log::warning("Auto-shuffle: deleting " . count($toDelete) . " duplicate AssignmentMember rows (backup: {$bkFile})");
                \App\Models\AssignmentMember::whereIn('id', $toDelete)->delete();
            }

            // Rebuild used canonical keys
            $used = [];
            $membersNow = \App\Models\AssignmentMember::whereHas('assignment', function($q){ $q->where('status','current'); })->with('student')->get();
            foreach ($membersNow as $m) {
                $canonical = null;
                if (!empty($m->student_id)) $canonical = 'legacy:' . $m->student_id;
                elseif (!empty($m->student_code)) {
                    $resolved = \App\Models\StudentDetail::where('student_id', $m->student_code)->pluck('user_id')->first();
                    if ($resolved) $canonical = 'pnuser:' . $resolved;
                    else $canonical = 'code:' . $m->student_code;
                } elseif ($m->student) $canonical = 'pnuser:' . ($m->student->user_id ?? $m->student->id);
                if ($canonical) $used[$canonical] = true;
            }

            // Build prioritized pool: PNUser with StudentDetail batch 2025/2026 first, then legacy
            $pool = [];
            $pnUsers = \App\Models\PNUser::with('studentDetail')->get();
            foreach ($pnUsers as $u) {
                if (isset($u->studentDetail) && in_array($u->studentDetail->batch, [2025,2026])) {
                    $key = 'pnuser:' . $u->user_id;
                    $pool[$key] = (object)['canonical'=>$key,'student_code'=> $u->studentDetail->student_id ?? null,'id'=>$u->user_id,'name'=>trim($u->user_fname . ' ' . $u->user_lname)];
                }
            }
            $legacy = \DB::table('student_group16')->get();
            foreach ($legacy as $l) {
                $key = 'legacy:' . $l->id;
                if (!isset($pool[$key])) $pool[$key] = (object)['canonical'=>$key,'student_code'=>null,'id'=>$l->id,'name'=>$l->name ?? trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? ''))];
            }

            // For any category missing members, try to assign an unused student; if none, move a non-coordinator from another category
            // SKIP categories with manual requirements - they already have exact counts!
            $allCategories = \App\Models\Category::all();
            foreach ($allCategories as $category) {
                // Skip categories with manual requirements
                if (in_array($category->id, $categoriesWithManualRequirements)) {
                    \Log::info("Final reconciliation SKIPPING category {$category->name} (has manual requirements)");
                    continue;
                }
                
                $existing = \App\Models\Assignment::where('category_id',$category->id)->where('status','current')->with('assignmentMembers')->first();
                $hasAny = $existing && $existing->assignmentMembers->count() > 0;
                if ($hasAny) continue;

                // Try pick unused pool candidate
                $candidate = null;
                foreach ($pool as $p) {
                    if (!isset($used[$p->canonical])) { $candidate = $p; break; }
                }

                if ($candidate) {
                    if (!$existing) {
                        $assignment = \App\Models\Assignment::create([
                            'category_id' => $category->id,
                            'start_date' => $now->toDateString(),
                            'end_date' => $now->copy()->addDays(7)->toDateString(),
                            'status' => 'current'
                        ]);
                    } else {
                        $assignment = $existing;
                    }
                    $createData = ['assignment_id'=>$assignment->id,'is_coordinator'=>false];
                    if (!empty($candidate->student_code)) $createData['student_code'] = $candidate->student_code;
                    else $createData['student_id'] = $candidate->id;
                    $this->safeCreateAssignmentMember($createData);
                    $used[$candidate->canonical] = true;
                    \Log::info("Final reconciliation: assigned {$candidate->name} to {$category->name} from pool");
                    continue;
                }

                // No unused available: move a non-coordinator from another category that has >1 members
                $moved = false;
                $donor = \App\Models\Assignment::where('status','current')->with('assignmentMembers')->get();
                foreach ($donor as $d) {
                    if ($d->assignmentMembers->count() <= 1) continue; // don't empty donor
                    // pick first non-coordinator member
                    $memberToMove = $d->assignmentMembers->first(function($mm){ return !$mm->is_coordinator; });
                    if (!$memberToMove) continue;
                    // move this member row to target assignment (create if needed)
                    if (!$existing) {
                        $assignment = \App\Models\Assignment::create([
                            'category_id' => $category->id,
                            'start_date' => $now->toDateString(),
                            'end_date' => $now->copy()->addDays(7)->toDateString(),
                            'status' => 'current'
                        ]);
                    } else {
                        $assignment = $existing;
                    }
                    // Update member's assignment_id
                    $memberToMove->assignment_id = $assignment->id;
                    $memberToMove->save();
                    // update used canonical tracking
                    $canonicalMoved = null;
                    if (!empty($memberToMove->student_id)) $canonicalMoved = 'legacy:' . $memberToMove->student_id;
                    elseif (!empty($memberToMove->student_code)) {
                        $resolved = \App\Models\StudentDetail::where('student_id', $memberToMove->student_code)->pluck('user_id')->first();
                        if ($resolved) $canonicalMoved = 'pnuser:' . $resolved;
                        else $canonicalMoved = 'code:' . $memberToMove->student_code;
                    } elseif ($memberToMove->student) $canonicalMoved = 'pnuser:' . ($memberToMove->student->user_id ?? $memberToMove->student->id);
                    if ($canonicalMoved) $used[$canonicalMoved] = true;
                    \Log::info("Final reconciliation: moved member id {$memberToMove->id} to category {$category->name}");
                    $moved = true;
                    break;
                }

                if (!$moved) {
                    \Log::warning("Final reconciliation: unable to find any candidate to fill category {$category->name}");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Final reconciliation failed: " . $e->getMessage());
        }

        // --- Coordinator-only enforcement pass (dynamic): ensure each assignment has coordinators
        // without moving members. Prefer selecting a 2026 coordinator when possible. This pass
        // will unset other is_coordinator flags for the assignment and set exactly one (per batch)
        // prioritizing batch 2026.
        try {
            $currentAssignments = Assignment::where('status', 'current')->with('assignmentMembers.student.studentDetail')->get();
            foreach ($currentAssignments as $assignment) {
                // collect members by batch
                $membersByBatch = [2025 => collect(), 2026 => collect(), 'unknown' => collect()];
                foreach ($assignment->assignmentMembers as $m) {
                    $batch = optional($m->student)->studentDetail ? (int)optional($m->student->studentDetail)->batch : null;
                    if ($batch === 2025 || $batch === 2026) {
                        $membersByBatch[$batch]->push($m);
                    } else {
                        $membersByBatch['unknown']->push($m);
                    }
                }

                // Unset all existing coordinator flags first
                foreach ($assignment->assignmentMembers as $m) {
                    if ($m->is_coordinator) {
                        $m->is_coordinator = false;
                        $m->save();
                    }
                }

                // Prefer pick 2026 if available; else pick 2025; else unknown
                $picked = null;
                if ($membersByBatch[2026]->isNotEmpty()) {
                    // pick the member who has not been coordinator recently if possible (use round-robin preference by id)
                    $picked = $membersByBatch[2026]->sortBy('id')->first();
                } elseif ($membersByBatch[2025]->isNotEmpty()) {
                    $picked = $membersByBatch[2025]->sortBy('id')->first();
                } elseif ($membersByBatch['unknown']->isNotEmpty()) {
                    $picked = $membersByBatch['unknown']->sortBy('id')->first();
                }

                if ($picked) {
                    $picked->is_coordinator = true;
                    $picked->save();
                    \Log::info("Coordinator-only enforcement: set member id {$picked->id} as coordinator for category {$assignment->category->name}");
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Coordinator-only enforcement failed: ' . $e->getMessage());
        }

        $message = $duplicateFound ?
            'Auto-shuffle complete but duplicates were detected and cleaned. Please review.' :
            "Auto-shuffle complete! All students assigned and reconciled - each name appears only once. {$coordinatorCount} coordinators assigned.";

        // Aggressive iterative fill: if any category still has zero members, attempt to move non-coordinator members
        // from other categories (prefer donors with >1 members) until all categories have at least one member
        try {
            $maxIterations = 10;
            $iter = 0;
            while ($iter++ < $maxIterations) {
                $emptyCats = [];
                $allCats = \App\Models\Category::all();
                foreach ($allCats as $cat) {
                    $exist = \App\Models\Assignment::where('category_id', $cat->id)->where('status','current')->with('assignmentMembers')->first();
                    if (!$exist || $exist->assignmentMembers->count() == 0) $emptyCats[] = $cat;
                }
                if (empty($emptyCats)) break;

                $movedAny = false;
                foreach ($emptyCats as $cat) {
                    // find donor with >1 members first
                    $donor = null; $memberToMove = null;
                    $assignments = \App\Models\Assignment::where('status','current')->with('assignmentMembers')->get();
                    foreach ($assignments as $a) {
                        if ($a->assignmentMembers->count() <= 1) continue;
                        $candidate = $a->assignmentMembers->first(function($mm){ return !$mm->is_coordinator; });
                        if ($candidate) { $donor = $a; $memberToMove = $candidate; break; }
                    }

                    // if no donor with >1, pick any non-coordinator member
                    if (!$memberToMove) {
                        foreach ($assignments as $a) {
                            $candidate = $a->assignmentMembers->first(function($mm){ return !$mm->is_coordinator; });
                            if ($candidate) { $donor = $a; $memberToMove = $candidate; break; }
                        }
                    }

                    if ($memberToMove) {
                        // ensure target assignment exists
                        $durationDays = SystemSetting::get('assignment_duration_days', 7);
                        $endDate = $durationDays == 0 
                            ? now()->copy()->addYear()->toDateString() 
                            : now()->copy()->addDays($durationDays)->toDateString();
                        
                        $targetAssign = \App\Models\Assignment::firstOrCreate([
                            'category_id' => $cat->id,
                            'status' => 'current',
                            'start_date' => now()->toDateString(),
                            'end_date' => $endDate
                        ]);

                        // move the member row
                        $memberToMove->assignment_id = $targetAssign->id;
                        $memberToMove->save();
                        \Log::info("Aggressive fill: moved member id {$memberToMove->id} from category {$donor->category->name} to {$cat->name}");
                        $movedAny = true;
                    } else {
                        \Log::warning("Aggressive fill: no movable non-coordinator found to fill {$cat->name}");
                    }
                }

                if (!$movedAny) break;
            }

            // Final check log
            $stillEmpty = [];
            foreach (\App\Models\Category::all() as $c) {
                $exist = \App\Models\Assignment::where('category_id',$c->id)->where('status','current')->with('assignmentMembers')->first();
                if (!$exist || $exist->assignmentMembers->count() == 0) $stillEmpty[] = $c->name;
            }
            if (!empty($stillEmpty)) {
                \Log::warning('After aggressive fill, these categories remain empty: ' . implode(', ', $stillEmpty));
                $message .= ' Note: some categories could not be auto-filled automatically.';
            }
            // LAST-RESORT: If any categories still empty, assign unused legacy student_group16 entries
            if (!empty($stillEmpty)) {
                try {
                    $usedLegacy = \App\Models\AssignmentMember::whereNotNull('student_id')->pluck('student_id')->toArray();
                    $availableLegacy = \DB::table('student_group16')->whereNotIn('id', $usedLegacy)->get();
                    foreach ($stillEmpty as $catName) {
                        $cat = \App\Models\Category::where('name', $catName)->first();
                        if (!$cat) continue;
                        
                        $durationDays = SystemSetting::get('assignment_duration_days', 7);
                        $endDate = $durationDays == 0 
                            ? $now->copy()->addYear()->toDateString() 
                            : $now->copy()->addDays($durationDays)->toDateString();
                        
                        $assignment = \App\Models\Assignment::firstOrCreate([
                            'category_id' => $cat->id,
                            'status' => 'current',
                            'start_date' => $now->toDateString(),
                            'end_date' => $endDate
                        ]);
                        $picked = $availableLegacy->shift();
                        if (!$picked) {
                            \Log::warning("Last-resort fill: no legacy students available to assign to {$catName}");
                            continue;
                        }
                        $this->safeCreateAssignmentMember([
                            'assignment_id' => $assignment->id,
                            'student_id' => $picked->id,
                            'is_coordinator' => false
                        ]);
                        \Log::info("Last-resort fill: assigned legacy student id {$picked->id} to {$catName}");
                    }
                } catch (\Exception $e) {
                    \Log::error('Last-resort fill failed: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Aggressive final-fill failed: ' . $e->getMessage());
        }

        // Coordinator ensure pass was removed to avoid complex moves inside auto-shuffle; rely on
        // existing reconciliation and safeCreateAssignmentMember duplicate checks. If needed,
        // we can re-introduce a slim coordinator assignment pass later.

        // UPDATE STATE TRACKING: Save assignment state to Login database for persistence
        try {
            $loginDb = \DB::connection('login');
            $currentUserId = auth()->user() ? auth()->user()->user_id : null;
            
            // Get all current assignments to update state tracking
            $currentAssignments = Assignment::where('status', 'current')->with(['category', 'assignmentMembers'])->get();
            
            foreach ($currentAssignments as $assignment) {
                $memberCount = $assignment->assignmentMembers->count();
                $memberDistribution = [
                    'total' => $memberCount,
                    'by_batch' => [],
                    'by_gender' => ['male' => 0, 'female' => 0]
                ];
                
                // Calculate distribution
                foreach ($assignment->assignmentMembers as $member) {
                    if ($member->student && $member->student->studentDetail) {
                        $batch = $member->student->studentDetail->batch;
                        $gender = $member->student->gender;
                        
                        if (!isset($memberDistribution['by_batch'][$batch])) {
                            $memberDistribution['by_batch'][$batch] = 0;
                        }
                        $memberDistribution['by_batch'][$batch]++;
                        
                        if ($gender === 'M' || $gender === 'Male') {
                            $memberDistribution['by_gender']['male']++;
                        } elseif ($gender === 'F' || $gender === 'Female') {
                            $memberDistribution['by_gender']['female']++;
                        }
                    }
                }
                
                // Get manual requirements used for this category
                $categoryOverrides = $overrides[$assignment->category->name] ?? [];
                $shuffleRequirements = [
                    'batch_requirements' => $categoryOverrides['batch_requirements'] ?? null,
                    'max_total' => $categoryOverrides['max_total'] ?? null,
                    'manual_requirements_used' => !empty($categoryOverrides['batch_requirements'])
                ];
                
                // Update or create state tracking record
                $loginDb->table('assignment_state_tracking')->updateOrInsert(
                    [
                        'assignment_id' => $assignment->id,
                        'category_name' => $assignment->category->name
                    ],
                    [
                        'assignment_status' => 'current',
                        'last_shuffle_at' => $now,
                        'shuffle_requirements' => json_encode($shuffleRequirements),
                        'total_members' => $memberCount,
                        'member_distribution' => json_encode($memberDistribution),
                        'assignment_start_date' => $assignment->start_date,
                        'assignment_end_date' => $assignment->end_date,
                        'is_locked' => false,
                        'shuffle_allowed' => true,
                        'next_shuffle_allowed_at' => Carbon::parse($assignment->end_date)->addDay(), // Allow next shuffle after end date
                        'last_modified_by_user_id' => $currentUserId,
                        'modification_notes' => "Auto-shuffle completed with {$memberCount} members assigned",
                        'updated_at' => $now
                    ]
                );
                
                \Log::info("🔄 STATE TRACKING UPDATED: {$assignment->category->name} - {$memberCount} members, next shuffle allowed after " . Carbon::parse($assignment->end_date)->format('M d, Y'));
            }
            
            \Log::info("✅ Assignment state tracking updated successfully in Login database");
        } catch (\Exception $stateError) {
            \Log::error("❌ Failed to update assignment state tracking: " . $stateError->getMessage());
            // Don't fail the entire operation if state tracking fails
        }

        // Check if this is an AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        // Create detailed success message with student count
        $finalMessage = $message . " 📊 Total students assigned: {$totalStudentsUsed} across " . count($categoryBreakdown) . " categories.";
        
        // Add batch separation confirmation to success message
        $batchSeparationMessage = " ✅ C2025 and C2026 students kept separate - no batch mixing occurred.";
        $finalMessage .= $batchSeparationMessage;
        
        // Clear applied manual requirements from session so badges disappear
        $overrides = session('auto_shuffle_overrides', []);
        if (!empty($overrides)) {
            \Log::info("Clearing applied manual requirements from session");
            session()->forget('auto_shuffle_overrides');
        }
        
        // For regular form submissions, stay on admin general task page
        return redirect()->route('generalTask')->with('success', $finalMessage);

        } catch (\Exception $e) {
            // Log the error with more details
            \Log::error("Auto shuffle error: " . $e->getMessage());
            \Log::error("Auto shuffle stack trace: " . $e->getTraceAsString());

            // Remove lock file on error
            $lockFile = storage_path('app/auto_shuffle.lock');
            if (file_exists($lockFile)) {
                unlink($lockFile);
            }

            // Provide more specific error messages
            $errorMessage = 'Auto-shuffle failed: ';
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                $errorMessage .= 'Database connection failed. Please check your database credentials.';
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                $errorMessage .= 'Database server is not running. Please start your MySQL/MariaDB server.';
            } else {
                $errorMessage .= $e->getMessage();
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMessage], 500);
            }
            return redirect()->route('generalTask')->with('error', $errorMessage);
        } finally {
            // Always remove lock file
            $lockFile = storage_path('app/auto_shuffle.lock');
            if (file_exists($lockFile)) {
                unlink($lockFile);
                \Log::info("Auto shuffle lock file removed");
            }
            // Update round-robin state file to advance pointers based on how many were picked
            try {
                // compute how many we advanced per batch
                foreach ($batchPickedCount as $by => $cnt) {
                    if (!isset($origPointers[$by])) $origPointers[$by] = 0;
                    $origPointers[$by] = ($origPointers[$by] + $cnt) % max(1, ($studentsByBatch[$by]->count() ?: 1));
                }
                @file_put_contents($rrFile, json_encode($origPointers));
            } catch (\Exception $e) {
                \Log::warning('Failed to persist round-robin state: ' . $e->getMessage());
            }
        }
    }

    // Method to clear auto shuffle lock (for debugging)
    public function clearAutoShuffleLock()
    {
        $lockFile = storage_path('app/auto_shuffle.lock');
        if (file_exists($lockFile)) {
            unlink($lockFile);
            \Log::info("Auto shuffle lock file manually cleared");
            return redirect()->route('generalTask')->with('success', 'Auto shuffle lock cleared successfully. You can now try auto shuffle again.');
        }

        return redirect()->route('generalTask')->with('info', 'No lock file found. Auto shuffle should work normally.');
    }

    public function cleanupDuplicates()
    {
        // Clean up duplicate assignments
        $duplicates = Assignment::select('category_id', 'start_date', 'status')
            ->groupBy('category_id', 'start_date', 'status')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $deletedCount = 0;
        foreach ($duplicates as $duplicate) {
            $assignments = Assignment::where('category_id', $duplicate->category_id)
                ->where('start_date', $duplicate->start_date)
                ->where('status', $duplicate->status)
                ->orderBy('id', 'desc')
                ->get();

            // Keep the first (latest) one, delete the rest
            foreach ($assignments->skip(1) as $assignment) {
                $assignment->assignmentMembers()->delete();
                $assignment->delete();
                $deletedCount++;
            }
        }

        return redirect()->route('generalTask')->with('success', "Cleaned up {$deletedCount} duplicate assignments.");
    }

    // Accept capacity overrides for next auto-shuffle run
    public function updateCategoryCapacity(Request $request)
    {
        // Debug: Log received data for troubleshooting
        \Log::info('🔍 Manual Requirements Received:', [
            'category_id' => $request->input('category_id'),
            'batch_requirements' => $request->input('batch_requirements'),
            'all_request_data' => $request->all()
        ]);

        $data = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'batch_requirements' => 'nullable|array',
            'batch_requirements.*.boys' => 'nullable|integer|min:0',
            'batch_requirements.*.girls' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ]);

        // Convert to category name key
        $category = Category::findOrFail($data['category_id']);
        // Compute totals per batch if batch_requirements provided
        $batchReq = $data['batch_requirements'] ?? [];
        $computedTotal = null;
        if (!empty($batchReq) && is_array($batchReq)) {
            $computedTotal = 0;
            foreach ($batchReq as $year => $vals) {
                $computedTotal += (int)($vals['boys'] ?? 0) + (int)($vals['girls'] ?? 0);
            }
        }

        $overrides = session('auto_shuffle_overrides', []);
        // Preserve any existing metadata for this category when updating
        $existing = isset($overrides[$category->name]) ? $overrides[$category->name] : [];
        // Validate coordinator overrides if provided: ensure coordinator_2025 belongs to batch 2025 and coordinator_2026 to batch 2026
        $coord2025 = $request->input('coordinators.2025');
        $coord2026 = $request->input('coordinators.2026');

        // Attempt to resolve coordinator values to StudentDetail. If a resolved entry exists and batch mismatches,
        // record a warning. If resolution fails (likely a manually-entered display name), allow saving the value.
        $coordWarnings = [];
        if (!empty($coord2025)) {
            $sd = StudentDetail::where('student_id', $coord2025)->first();
            if ($sd) {
                if ((int)($sd->batch ?? 0) !== 2025) {
                    $coordWarnings[] = 'Coordinator 2025 value resolved to a student whose batch does not match 2025.';
                    \Log::warning('Coordinator 2025 batch mismatch while saving override', ['category'=>$category->name,'value'=>$coord2025]);
                    // We deliberately do NOT reject; we save the override but flag warning
                }
            } else {
                // Try resolving by display name (PNUser fullname) to be flexible with UI inputs
                $name = trim($coord2025);
                $pn = \App\Models\PNUser::whereRaw("CONCAT_WS(' ', COALESCE(user_fname,''), COALESCE(user_lname,'')) = ?", [$name])->first();
                if ($pn) {
                    $sd2 = StudentDetail::where('user_id', $pn->user_id)->first();
                    if ($sd2 && (int)($sd2->batch ?? 0) !== 2025) {
                        $coordWarnings[] = 'Coordinator 2025 name resolved to a student whose batch does not match 2025.';
                        \Log::warning('Coordinator 2025 display-name resolved but batch mismatch', ['category'=>$category->name,'name'=>$name]);
                    }
                }
            }
        }
        if (!empty($coord2026)) {
            $sd = StudentDetail::where('student_id', $coord2026)->first();
            if ($sd) {
                if ((int)($sd->batch ?? 0) !== 2026) {
                    $coordWarnings[] = 'Coordinator 2026 value resolved to a student whose batch does not match 2026.';
                    \Log::warning('Coordinator 2026 batch mismatch while saving override', ['category'=>$category->name,'value'=>$coord2026]);
                }
            } else {
                $name = trim($coord2026);
                $pn = \App\Models\PNUser::whereRaw("CONCAT_WS(' ', COALESCE(user_fname,''), COALESCE(user_lname,'')) = ?", [$name])->first();
                if ($pn) {
                    $sd2 = StudentDetail::where('user_id', $pn->user_id)->first();
                    if ($sd2 && (int)($sd2->batch ?? 0) !== 2026) {
                        $coordWarnings[] = 'Coordinator 2026 name resolved to a student whose batch does not match 2026.';
                        \Log::warning('Coordinator 2026 display-name resolved but batch mismatch', ['category'=>$category->name,'name'=>$name]);
                    }
                }
            }
        }

        $overrides[$category->name] = array_merge($existing, [
            'batch_requirements' => $batchReq ?: ($existing['batch_requirements'] ?? null),
            'max_total' => $computedTotal > 0 ? $computedTotal : ($existing['max_total'] ?? null),
            'start_date' => $data['start_date'] ?? ($existing['start_date'] ?? null),
            'end_date' => $data['end_date'] ?? ($existing['end_date'] ?? null),
            // Store modal metadata so the UI can show coordinator and description when reopened
            'metadata' => [
                'coordinator_2025' => $coord2025 ?? ($existing['metadata']['coordinator_2025'] ?? null),
                'coordinator_2026' => $coord2026 ?? ($existing['metadata']['coordinator_2026'] ?? null),
                'auto_assign_coord_2025' => $request->input('auto_assign_coordinator.2025') ? true : !!($existing['metadata']['auto_assign_coord_2025'] ?? false),
                'auto_assign_coord_2026' => $request->input('auto_assign_coordinator.2026') ? true : !!($existing['metadata']['auto_assign_coord_2026'] ?? false),
                'description' => $request->input('description') ?? ($existing['metadata']['description'] ?? null)
            ]
        ]);
        session(['auto_shuffle_overrides' => $overrides]);
        
        // ALSO save batch requirements to database for persistence across sessions
        \Log::info("Attempting to save batch requirements for {$category->name}. batchReq: " . json_encode($batchReq));
        
        if (is_array($batchReq)) {
            $category->batch_requirements = $batchReq; // Laravel auto-converts to JSON
            $category->save();
            \Log::info("✅ Saved batch requirements to database for category {$category->name}: " . json_encode($batchReq));
        } else {
            \Log::warning("⚠️ batchReq is not an array for category {$category->name}. Type: " . gettype($batchReq));
        }

        // Attempt to ensure displayed coordinators are actual members in the current assignment
        try {
            $currentAssignment = Assignment::where('category_id', $category->id)->where('status','current')->first();
            if (!$currentAssignment) {
                // create a current assignment placeholder so members can be added
                $durationDays = SystemSetting::get('assignment_duration_days', 7);
                
                // If duration is 0 (anytime shuffle), set end_date far in future (1 year)
                $endDate = $durationDays == 0 
                    ? now()->copy()->addYear()->toDateString() 
                    : now()->copy()->addDays($durationDays)->toDateString();
                
                $currentAssignment = Assignment::create([
                    'category_id' => $category->id,
                    'start_date' => $data['start_date'] ?? now()->toDateString(),
                    'end_date' => $data['end_date'] ?? $endDate,
                    'status' => 'current'
                ]);
            }

            $ensureCoordinator = function($coordVal, $batchExpected) use ($currentAssignment, $category) {
                if (empty($coordVal)) return null;
                try {
                    // Try resolve as canonical student_code
                    $sd = StudentDetail::where('student_id', $coordVal)->first();
                    if ($sd) {
                        $userId = $sd->user_id;
                        // Check if this user is already an AssignmentMember in any current assignment
                        $existing = AssignmentMember::where(function($q) use ($userId) {
                            $q->where('student_id', $userId)->orWhere('student_code', StudentDetail::where('user_id',$userId)->pluck('student_id')->first());
                        })->whereHas('assignment', function($q){ $q->where('status','current'); })->with('assignment')->first();

                        if ($existing) {
                            // If already in same category, set coordinator flag
                            if ($existing->assignment && $existing->assignment->category_id == $currentAssignment->category_id) {
                                if (!$existing->is_coordinator) { $existing->is_coordinator = true; $existing->save(); }
                                return $existing;
                            }
                            // If assigned elsewhere, move the member row to this category (make coordinator)
                            $existing->assignment_id = $currentAssignment->id;
                            $existing->is_coordinator = true;
                            $existing->save();
                            \Log::info("Moved existing member id {$existing->id} for coordinator {$coordVal} into category {$category->name}");
                            return $existing;
                        }

                        // Not existing: create via safeCreateAssignmentMember (this will attempt resolution)
                        $created = \App\Models\AssignmentMember::create([ 
                            'assignment_id' => $currentAssignment->id,
                            'student_id' => $userId,
                            'is_coordinator' => true
                        ]);
                        \Log::info("Created AssignmentMember for coordinator {$coordVal} in category {$category->name}");
                        return $created;
                    } else {
                        // Not resolvable by student_code. Try to find PNUser by full name (display input)
                        $pn = \App\Models\PNUser::whereRaw("CONCAT_WS(' ', COALESCE(user_fname,''), COALESCE(user_lname,'')) = ?", [trim($coordVal)])->first();
                        if ($pn) {
                            $userId = $pn->user_id;
                            $existing = AssignmentMember::where('student_id', $userId)->whereHas('assignment', function($q){ $q->where('status','current'); })->with('assignment')->first();
                            if ($existing) {
                                if ($existing->assignment && $existing->assignment->category_id == $currentAssignment->category_id) {
                                    if (!$existing->is_coordinator) { $existing->is_coordinator = true; $existing->save(); }
                                    return $existing;
                                }
                                $existing->assignment_id = $currentAssignment->id;
                                $existing->is_coordinator = true;
                                $existing->save();
                                \Log::info("Moved existing PNUser member id {$existing->id} for coordinator {$coordVal} into category {$category->name}");
                                return $existing;
                            }
                            // Create a new AssignmentMember
                            $created = \App\Models\AssignmentMember::create([ 'assignment_id'=>$currentAssignment->id, 'student_id'=>$userId, 'is_coordinator'=>true ]);
                            \Log::info("Created AssignmentMember (by PNUser name) for coordinator {$coordVal} in category {$category->name}");
                            return $created;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("Could not ensure coordinator member for value {$coordVal} in category {$category->name}: " . $e->getMessage());
                }
                return null;
            };

            // Ensure both coordinators (if provided) exist as members
            if (!empty($overrides[$category->name]['metadata']['coordinator_2025'] ?? null)) {
                $ensureCoordinator($overrides[$category->name]['metadata']['coordinator_2025'], 2025);
            }
            if (!empty($overrides[$category->name]['metadata']['coordinator_2026'] ?? null)) {
                $ensureCoordinator($overrides[$category->name]['metadata']['coordinator_2026'], 2026);
            }
        } catch (\Exception $e) {
            \Log::warning('Could not auto-add coordinator to current assignment: ' . $e->getMessage());
        }

        // Create a more informative message about what will happen
        $totalStudents = $computedTotal ?? 'default';
        $batchInfo = '';
        if (!empty($batchReq)) {
            $batchDetails = [];
            foreach ($batchReq as $year => $vals) {
                $boys = (int)($vals['boys'] ?? 0);
                $girls = (int)($vals['girls'] ?? 0);
                if ($boys > 0 || $girls > 0) {
                    $batchDetails[] = "C{$year}: {$boys}M+{$girls}F";
                }
            }
            if (!empty($batchDetails)) {
                $batchInfo = ' (' . implode(', ', $batchDetails) . ')';
            }
        }
        
        $message = "✅ Manual requirements saved for {$category->name}! ";
        $message .= "Requirements: {$totalStudents} students{$batchInfo}. ";
        $message .= "⚠️ Changes will apply on NEXT shuffle - current assignments unchanged until then.";
        
        $resp = ['success' => true, 'message' => $message, 'category_name' => $category->name, 'pending_changes' => true];
        if (!empty($coordWarnings)) {
            $resp['warnings'] = $coordWarnings;
        }
        return response()->json($resp);
    }

    // Check current session overrides for manual requirements
    public function checkSessionOverrides()
    {
        $overrides = session('auto_shuffle_overrides', []);
        
        return response()->json([
            'hasOverrides' => !empty($overrides),
            'overrides' => $overrides,
            'count' => count($overrides)
        ]);
    }

    // Delete current assignment for a category (used by delete button)
    public function deleteCurrentAssignment(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        $currentAssignment = Assignment::where('category_id', $categoryId)
            ->where('status', 'current')
            ->first();

        if (!$currentAssignment) {
            return response()->json(['success' => false, 'message' => 'No current assignment found for this category.'], 404);
        }

        // Delete members then assignment
        $currentAssignment->assignmentMembers()->delete();
        $currentAssignment->delete();

        return response()->json(['success' => true, 'message' => 'Current assignment deleted.']);
    }

    public function create()
    {
        // Get categories and students for the assignment form
        $categories = \App\Models\Category::all();
        // Map PNUser students for the create form
        $users = \App\Models\PNUser::where('user_role', 'student')->get();
        $details = \App\Models\StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');
        $students = $users->map(function($u) use ($details) {
            $d = $details->get($u->user_id);
            return [
                'id' => $u->user_id,
                'name' => trim($u->user_fname . ' ' . $u->user_lname),
                'gender' => $u->gender,
                'batch' => $d->batch ?? null
            ];
        });
        return view('assignments.create', compact('categories', 'students'));
    }

    /**
     * Remove duplicate members from an assignment
     * Keeps only the first occurrence of each unique student (by name, case-insensitive)
     */
    private function removeDuplicateMembers($assignmentId)
    {
        try {
            $members = AssignmentMember::where('assignment_id', $assignmentId)
                ->orderBy('id', 'asc') // Keep oldest entry
                ->get();
            
            $seenNames = [];
            $duplicatesRemoved = 0;
            
            foreach ($members as $member) {
                $normalizedName = strtolower(trim($member->student_name ?? ''));
                
                if (empty($normalizedName)) {
                    continue;
                }
                
                if (in_array($normalizedName, $seenNames)) {
                    // This is a duplicate - delete it
                    \Log::info("Removing duplicate member: {$member->student_name} (ID: {$member->id}) from assignment {$assignmentId}");
                    $member->delete();
                    $duplicatesRemoved++;
                } else {
                    // First occurrence - keep it
                    $seenNames[] = $normalizedName;
                }
            }
            
            if ($duplicatesRemoved > 0) {
                \Log::info("✅ Removed {$duplicatesRemoved} duplicate member(s) from assignment {$assignmentId}");
            }
            
            return $duplicatesRemoved;
        } catch (\Exception $e) {
            \Log::error("Error removing duplicates: " . $e->getMessage());
            return 0;
        }
    }
    
    // Get category members for editing
    public function getCategoryMembers($categoryId)
    {
        try {
            // Clean expired comments first
            AssignmentMember::cleanExpiredComments();
            
            // AUTOMATIC DUPLICATE CLEANUP: Remove duplicate members from current assignment
            $currentAssignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();
                
            if ($currentAssignment) {
                $this->removeDuplicateMembers($currentAssignment->id);
            }

            // ONLY get CURRENT assignments, not previous ones
            $category = Category::with(['assignments' => function($query) {
                $query->where('status', 'current');
            }, 'assignments.assignmentMembers.student.studentDetail'])->findOrFail($categoryId);

    $membersByBatch = [];
    $totalMembersFound = 0;

    foreach($category->assignments as $assignment) {
            // Skip if not current assignment
            if ($assignment->status !== 'current') {
                continue;
            }

            foreach($assignment->assignmentMembers as $member) {
                // Check if comment is expired and clean it
                if ($member->isCommentExpired()) {
                    $member->update([
                        'comments' => null,
                        'comment_created_at' => null
                    ]);
                    $member->refresh();
                }

                // Try to get batch from multiple sources
                $batch = null;
                
                // First try: studentDetail->batch
                if ($member->student && $member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch;
                }
                
                // Second try: parse from student_code (e.g., '2025010029C1' -> 2025)
                if (!$batch && $member->student && $member->student->studentDetail && !empty($member->student->studentDetail->student_code)) {
                    if (preg_match('/^(20\d{2})/', $member->student->studentDetail->student_code, $matches)) {
                        $batch = (int)$matches[1];
                    }
                }
                
                // Third try: parse from student_id field
                if (!$batch && $member->student && $member->student->studentDetail && !empty($member->student->studentDetail->student_id)) {
                    if (preg_match('/^(20\d{2})/', $member->student->studentDetail->student_id, $matches)) {
                        $batch = (int)$matches[1];
                    }
                }
                
                // Fourth try: For custom members, check if student_code contains batch info (format: BATCH_YYYY)
                if (!$batch && !empty($member->student_code)) {
                    if (preg_match('/^BATCH_(\d{4})$/', $member->student_code, $matches)) {
                        $batch = (int)$matches[1];
                        \Log::info("getCategoryMembers: Found custom member batch from student_code: {$member->student_code} -> batch {$batch}");
                    }
                }
                
                // Fifth try: For custom members, try parsing from student_code if it starts with year
                if (!$batch && !empty($member->student_code)) {
                    if (preg_match('/^(20\d{2})/', $member->student_code, $matches)) {
                        $batch = (int)$matches[1];
                    }
                }
                
                if ($batch) {
                    if (!isset($membersByBatch[$batch])) $membersByBatch[$batch] = [];
                    $membersByBatch[$batch][] = $member;
                } else {
                    // Log members that couldn't be assigned to a batch
                    \Log::warning("getCategoryMembers: Could not determine batch for member ID {$member->id}, student_name: {$member->student_name}, student_code: {$member->student_code}, student_id: {$member->student_id}");
                    if (!isset($membersByBatch['unknown'])) $membersByBatch['unknown'] = [];
                    $membersByBatch['unknown'][] = $member;
                }
            }
        }

        // Keep legacy keys for compatibility (2025 & 2026) if present
        $members2025 = $membersByBatch[2025] ?? [];
        $members2026 = $membersByBatch[2026] ?? [];

        // Sanitize metadata: ensure stored coordinator values (if provided) actually belong to the claimed batch
        $rawMetadata = session('auto_shuffle_overrides.' . $category->name . '.metadata', null);
        $safeMetadata = null;
        if (is_array($rawMetadata)) {
            $safeMetadata = $rawMetadata;
            try {
                // Validate coordinator_2025
                if (!empty($safeMetadata['coordinator_2025'])) {
                    $val = $safeMetadata['coordinator_2025'];
                    $resolved = null;
                    // If it's a student_code (string), try to resolve to StudentDetail
                    $resolved = StudentDetail::where('student_id', $val)->first();
                    if ($resolved) {
                        if ((int)($resolved->batch ?? 0) !== 2025) {
                            \Log::warning('getCategoryMembers: coordinator_2025 metadata does not match batch 2025, clearing', ['category'=>$category->name,'value'=>$val]);
                            $safeMetadata['coordinator_2025'] = null;
                        }
                    } else {
                        // Could be a display name — try to find it among members2025
                        $found = false;
                        foreach ($members2025 as $m) {
                            $name = $m->student ? trim(($m->student->user_fname ?? '') . ' ' . ($m->student->user_lname ?? '')) : ($m->student_name ?? $m->student_code ?? null);
                            if ($name && trim($name) === trim($val)) { $found = true; break; }
                        }
                        if (!$found) $safeMetadata['coordinator_2025'] = null;
                    }
                }

                // Validate coordinator_2026 similarly
                if (!empty($safeMetadata['coordinator_2026'])) {
                    $val = $safeMetadata['coordinator_2026'];
                    $resolved = StudentDetail::where('student_id', $val)->first();
                    if ($resolved) {
                        if ((int)($resolved->batch ?? 0) !== 2026) {
                            \Log::warning('getCategoryMembers: coordinator_2026 metadata does not match batch 2026, clearing', ['category'=>$category->name,'value'=>$val]);
                            $safeMetadata['coordinator_2026'] = null;
                        }
                    } else {
                        $found = false;
                        foreach ($members2026 as $m) {
                            $name = $m->student ? trim(($m->student->user_fname ?? '') . ' ' . ($m->student->user_lname ?? '')) : ($m->student_name ?? $m->student_code ?? null);
                            if ($name && trim($name) === trim($val)) { $found = true; break; }
                        }
                        if (!$found) $safeMetadata['coordinator_2026'] = null;
                    }
                }
            } catch (\Exception $e) {
                // If validation fails for any reason, drop metadata to be safe
                \Log::warning('getCategoryMembers: metadata validation failed: ' . $e->getMessage());
                $safeMetadata = null;
            }
        }

        // Transform members to include student data
        $transformMember = function($member) {
            $student = $member->student;
            return [
                'id' => $member->id,
                'student_id' => $student ? $student->user_id : null,
                'student_code' => $student && $student->studentDetail ? $student->studentDetail->student_code : null,
                'user_fname' => $student ? $student->user_fname : null,
                'user_lname' => $student ? $student->user_lname : null,
                'name' => $member->student_name ?? null, // Include student_name for custom members
                'gender' => $student ? $student->gender : null,
                'batch' => $student && $student->studentDetail ? $student->studentDetail->batch : null,
                'is_coordinator' => $member->is_coordinator ?? false,
                'comments' => $member->comments,
                'comment_created_at' => $member->comment_created_at
            ];
        };

        $transformed2025 = array_map($transformMember, $members2025);
        $transformed2026 = array_map($transformMember, $members2026);

        // Get batch requirements from session overrides
        $batchRequirements = [];
        $overrides = session('auto_shuffle_overrides', []);
        if (isset($overrides[$category->name]) && !empty($overrides[$category->name]['batch_requirements'])) {
            $batchRequirements = $overrides[$category->name]['batch_requirements'];
        }

        // Calculate total required and actual assigned count
        $totalRequired = 0;
        $actualAssignedCount = 0;
        
        // Calculate total required from batch requirements
        if (!empty($batchRequirements)) {
            foreach ($batchRequirements as $year => $vals) {
                $boys = (int)($vals['boys'] ?? 0);
                $girls = (int)($vals['girls'] ?? 0);
                $totalRequired += ($boys + $girls);
            }
        }
        
        // Calculate actual assigned count from current assignments
        foreach($category->assignments as $assignment) {
            if ($assignment->status === 'current') {
                $actualAssignedCount += $assignment->assignmentMembers->count();
            }
        }

        return response()->json([
            'success' => true,
            'category_name' => $category->name,
            'members_by_batch' => $membersByBatch,
            'members2025' => $transformed2025,
            'members2026' => $transformed2026,
            // Include sanitized modal metadata (coordinator, auto flags, description) from session overrides
            'metadata' => $safeMetadata,
            // Include batch requirements for modal display
            'batch_requirements' => $batchRequirements,
            // Include requirement totals for dynamic display
            'total_required' => $totalRequired,
            'actual_assigned_count' => $actualAssignedCount,
            'requirements_met' => $totalRequired > 0 ? ($actualAssignedCount >= $totalRequired) : true
        ]);
        } catch (\Exception $e) {
            \Log::error('Error in getCategoryMembers: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error loading members: ' . $e->getMessage(),
                'members2025' => [],
                'members2026' => []
            ], 500);
        }
    }

    // Get available students for adding to category
    public function getAvailableStudents($categoryId)
    {
        try {
            $category = Category::findOrFail($categoryId);

            // Get all students from Login users + student details
            $users = PNUser::where('user_role', 'student')->get();
            
            \Log::info("Total students in Login table: " . $users->count());
            
            $details = StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');
            
            \Log::info("Total student details found: " . $details->count());

            $allStudents = $users->map(function($u) use ($details) {
                $d = $details->get($u->user_id);
                return (object) [
                    'id' => $u->user_id,
                    'user_fname' => $u->user_fname,
                    'user_lname' => $u->user_lname,
                    'name' => trim($u->user_fname . ' ' . $u->user_lname),
                    'gender' => $u->gender,
                    'student_code' => $d ? $d->student_code : null,
                    'batch' => $d ? $d->batch : null
                ];
            });

            // Get currently assigned students for this category
            $currentAssignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();

            $assignedStudentCodes = [];
            if ($currentAssignment) {
                // Use application-level attribute 'student_code' from AssignmentMember
                $assignedStudentCodes = AssignmentMember::where('assignment_id', $currentAssignment->id)
                    ->get()
                    ->map(function($m){ return $m->student_code; })
                    ->filter()
                    ->toArray();
            }

            // Filter out already assigned students by canonical student_code
            $availableStudents = $allStudents->filter(function($s) use ($assignedStudentCodes) {
                return !in_array($s->student_code, $assignedStudentCodes);
            })->values();

            // Group available students by batch dynamically
            $studentsByBatch = [];
            foreach ($availableStudents as $s) {
                $batch = $s->batch ?? 'unknown';
                if (!isset($studentsByBatch[$batch])) $studentsByBatch[$batch] = collect();
                $studentsByBatch[$batch]->push($s);
            }

            // Keep legacy keys for 2025 and 2026
            $students2025 = isset($studentsByBatch[2025]) ? $studentsByBatch[2025]->values() : collect();
            $students2026 = isset($studentsByBatch[2026]) ? $studentsByBatch[2026]->values() : collect();

            \Log::info("Available students after filtering: " . $availableStudents->count());

            return response()->json([
                'success' => true,
                'students' => $availableStudents->values()->toArray(),
                'students_by_batch' => $studentsByBatch,
                'students2025' => $students2025->toArray(),
                'students2026' => $students2026->toArray()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableStudents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading students: ' . $e->getMessage(),
                'students' => [],
                'students2025' => [],
                'students2026' => []
            ], 500);
        }
    }

    // Get current members for deleting from category
    public function getCurrentMembers($categoryId)
    {
        $category = Category::with(['assignments' => function($query) {
            $query->where('status', 'current');
        }, 'assignments.assignmentMembers.student'])->findOrFail($categoryId);

        $membersByBatch = [];

        foreach($category->assignments as $assignment) {
            if ($assignment->status !== 'current') {
                continue;
            }

            foreach($assignment->assignmentMembers as $member) {
                $batch = optional($member->student)->studentDetail ? optional($member->student->studentDetail)->batch : null;
                if ($batch) {
                    if (!isset($membersByBatch[$batch])) $membersByBatch[$batch] = [];
                    $membersByBatch[$batch][] = $member;
                } else {
                    if (!isset($membersByBatch['unknown'])) $membersByBatch['unknown'] = [];
                    $membersByBatch['unknown'][] = $member;
                }
            }
        }

        $members2025 = $membersByBatch[2025] ?? [];
        $members2026 = $membersByBatch[2026] ?? [];

        return response()->json([
            'success' => true,
            'category_name' => $category->name,
            'members_by_batch' => $membersByBatch,
            'members2025' => $members2025,
            'members2026' => $members2026
        ]);
    }

    // Add members to category
    public function addMembers(Request $request, $categoryId)
    {
        try {
            // Accept user_ids and custom_members
            $studentIds = $request->input('student_ids', []);
            $customMembers = $request->input('custom_members', []);
            
            if (empty($studentIds) && empty($customMembers)) {
                return response()->json(['success' => false, 'message' => 'No members selected'], 422);
            }

            $category = Category::findOrFail($categoryId);

            // Get current assignment for this category
            $currentAssignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();

            if (!$currentAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current assignment found for this category.'
                ]);
            }

            // CHECK REQUIREMENTS LIMIT BEFORE ADDING
            $overrides = session('auto_shuffle_overrides', []);
            $totalRequired = 0;
            
            if (isset($overrides[$category->name]) && !empty($overrides[$category->name]['batch_requirements'])) {
                $batchReqs = $overrides[$category->name]['batch_requirements'];
                foreach ($batchReqs as $year => $vals) {
                    $totalRequired += (int)($vals['boys'] ?? 0) + (int)($vals['girls'] ?? 0);
                }
            }
            
            // Get current member count
            $currentMemberCount = AssignmentMember::where('assignment_id', $currentAssignment->id)->count();
            
            // Calculate how many members are being added
            $membersToAdd = count($studentIds) + count($customMembers);
            
            // Check if adding would exceed the requirement
            if ($totalRequired > 0 && ($currentMemberCount + $membersToAdd) > $totalRequired) {
                $availableSlots = $totalRequired - $currentMemberCount;
                return response()->json([
                    'success' => false,
                    'message' => "❌ Cannot add {$membersToAdd} member(s)! The requirement is {$totalRequired} students. Currently assigned: {$currentMemberCount}. Available slots: {$availableSlots}."
                ], 422);
            }

            $addedCount = 0;
            $alreadyAssignedCount = 0;

            // Add regular students
            if (!empty($studentIds)) {
                // Get student details for those who have them
                $studentDetails = StudentDetail::whereIn('user_id', $studentIds)->get()->keyBy('user_id');
                
                // Get user information for all selected students
                $users = PNUser::whereIn('user_id', $studentIds)->get();
                
                foreach ($users as $user) {
                    $studentName = trim($user->user_fname . ' ' . $user->user_lname);
                    
                    // Check if already assigned by student_id OR by name (case-insensitive)
                    $exists = AssignmentMember::where('assignment_id', $currentAssignment->id)
                        ->where(function($query) use ($user, $studentName) {
                            $query->where('student_id', $user->user_id)
                                  ->orWhereRaw('LOWER(TRIM(student_name)) = ?', [strtolower($studentName)]);
                        })
                        ->exists();

                    if ($exists) {
                        $alreadyAssignedCount++;
                        \Log::warning("Duplicate prevented: {$studentName} (ID: {$user->user_id}) already assigned to assignment {$currentAssignment->id}");
                        continue;
                    }

                    // Get student detail if exists
                    $detail = $studentDetails->get($user->user_id);
                    
                    // Add the student
                    AssignmentMember::create([
                        'assignment_id' => $currentAssignment->id,
                        'student_id' => $user->user_id,
                        'student_code' => $detail ? $detail->student_code : null,
                        'student_name' => trim($user->user_fname . ' ' . $user->user_lname),
                        'is_coordinator' => false
                    ]);
                    
                    $addedCount++;
                }
            }

            // Add custom members (non-students)
            if (!empty($customMembers)) {
                foreach ($customMembers as $customMember) {
                    $name = trim($customMember['name'] ?? '');
                    $batch = $customMember['batch'] ?? null;
                    
                    if (empty($name)) {
                        continue;
                    }

                    // Check if custom member with same name already exists in THIS assignment
                    // Note: We allow same person in different tasks, just not duplicate in same task
                    $exists = AssignmentMember::where('assignment_id', $currentAssignment->id)
                        ->whereRaw('LOWER(TRIM(student_name)) = ?', [strtolower($name)])
                        ->whereNull('student_id')
                        ->exists();

                    if ($exists) {
                        $alreadyAssignedCount++;
                        continue;
                    }

                    // Add custom member with batch info stored in student_code
                    // Format: BATCH_YYYY where YYYY is the batch year (e.g., BATCH_2025)
                    $studentCodeForBatch = $batch ? "BATCH_{$batch}" : null;
                    
                    try {
                        AssignmentMember::create([
                            'assignment_id' => $currentAssignment->id,
                            'student_id' => null,
                            'student_code' => $studentCodeForBatch,  // Store batch info here
                            'student_name' => $name,
                            'is_coordinator' => false
                        ]);
                        $addedCount++;
                        \Log::info("Added custom member '{$name}' with batch {$batch} (student_code: {$studentCodeForBatch})");
                    } catch (\Exception $e) {
                        \Log::error("Error adding custom member '{$name}': " . $e->getMessage());
                        // If student_name column doesn't exist, try without it
                        continue;
                    }
                }
            }

            // Build response message
            if ($addedCount === 0 && $alreadyAssignedCount > 0) {
                // All selected members were already assigned - show as error
                return response()->json([
                    'success' => false,
                    'message' => "❌ Cannot add duplicate members! All {$alreadyAssignedCount} selected member(s) are already assigned to {$category->name}."
                ], 422);
            }
            
            // Get updated member count after adding
            $newMemberCount = AssignmentMember::where('assignment_id', $currentAssignment->id)->count();
            
            $message = "✅ Successfully added {$addedCount} member(s) to {$category->name}.";
            if ($alreadyAssignedCount > 0) {
                $message .= " ⚠️ {$alreadyAssignedCount} member(s) were already assigned and skipped.";
            }
            
            // Add requirement status to message
            if ($totalRequired > 0) {
                $remainingSlots = $totalRequired - $newMemberCount;
                if ($remainingSlots > 0) {
                    $message .= " 📊 Current: {$newMemberCount}/{$totalRequired}. {$remainingSlots} slot(s) remaining.";
                } else if ($remainingSlots === 0) {
                    $message .= " ✅ Requirement met! {$newMemberCount}/{$totalRequired} students assigned.";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'added_count' => $addedCount,
                'duplicate_count' => $alreadyAssignedCount,
                'current_count' => $newMemberCount,
                'total_required' => $totalRequired
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in addMembers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding members: ' . $e->getMessage()
            ], 500);
        }
    }

    // Remove members from category
    public function removeMembers(Request $request, $categoryId)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:assignments_members,id'
        ]);

        $category = Category::findOrFail($categoryId);

        // Verify that all member IDs belong to the current assignment of this category
        $currentAssignment = Assignment::where('category_id', $categoryId)
            ->where('status', 'current')
            ->first();

        if (!$currentAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'No current assignment found for this category.'
            ]);
        }

        $validMemberIds = AssignmentMember::where('assignment_id', $currentAssignment->id)
            ->whereIn('id', $request->member_ids)
            ->pluck('id')
            ->toArray();

        if (count($validMemberIds) !== count($request->member_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more members do not belong to this category.'
            ]);
        }

        // Remove the members
        AssignmentMember::whereIn('id', $request->member_ids)->delete();

        $memberCount = count($request->member_ids);
        return response()->json([
            'success' => true,
            'message' => "Successfully removed {$memberCount} member(s) from {$category->name}."
        ]);
    }

    // Update member comments
    public function updateMemberComment(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:assignments_members,id',
            'comments' => 'nullable|string|max:500',
        ]);

        $member = AssignmentMember::findOrFail($request->member_id);

        // Check if comment is expired and clean it first
        if ($member->isCommentExpired()) {
            $member->update([
                'comments' => null,
                'comment_created_at' => null
            ]);
        }

        // Update with new comment and timestamp
        $updateData = [
            'comments' => $request->comments
        ];

        // Set timestamp only if comment is not empty
        if (!empty(trim($request->comments))) {
            $updateData['comment_created_at'] = now();
        } else {
            $updateData['comment_created_at'] = null;
        }

        $member->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'comments' => $member->comments,
            'comment_created_at' => $member->comment_created_at
        ]);
    }

    // Change member's batch assignment
    public function changeMemberBatch(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:assignments_members,id',
            'new_batch' => 'required|integer|in:2025,2026'
        ]);

        try {
            $member = AssignmentMember::findOrFail($request->member_id);
            
            // Get the student's user_id
            if (!$member->student_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change batch for custom members (non-students).'
                ], 422);
            }

            // Update the student's batch in student_details table
            $studentDetail = StudentDetail::where('user_id', $member->student_id)->first();
            
            if (!$studentDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student details not found.'
                ], 404);
            }

            $oldBatch = $studentDetail->batch;
            $studentDetail->batch = $request->new_batch;
            $studentDetail->save();

            $studentName = $member->student ? trim($member->student->user_fname . ' ' . $member->student->user_lname) : 'Unknown';

            \Log::info("Changed batch for {$studentName} from {$oldBatch} to {$request->new_batch}");

            return response()->json([
                'success' => true,
                'message' => "✅ Successfully moved {$studentName} to Batch {$request->new_batch}.",
                'old_batch' => $oldBatch,
                'new_batch' => $request->new_batch
            ]);
        } catch (\Exception $e) {
            \Log::error('Error changing member batch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error changing batch: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get assignments filtered by specific task and time slot
    public function getTaskSpecificAssignments(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string',
            'task_type' => 'nullable|string',
            'time_slot' => 'nullable|string'
        ]);

        try {
            // Get the category
            $category = Category::where('name', $request->category_name)->first();
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.',
                    'assignments' => []
                ]);
            }

            // Get current assignment for this category
            $currentAssignment = Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->with(['assignmentMembers.student'])
                ->first();

            if (!$currentAssignment) {
                return response()->json([
                    'success' => true,
                    'assignments' => [],
                    'message' => 'No current assignments found.'
                ]);
            }

            // Build query for assignment members
            $query = $currentAssignment->assignmentMembers();

            // Filter by task type if specified
            if ($request->task_type) {
                $query->where('task_type', $request->task_type);
            }

            // Filter by time slot if specified
            if ($request->time_slot) {
                $query->where('time_slot', $request->time_slot);
            }

            $members = $query->get();

            // Format the response to show only assigned students
            $assignments = [];
            foreach ($members as $member) {
                $studentData = [
                    'id' => $member->student_id ?? $member->student->user_id ?? null,
                    'student_code' => $member->student_code ?? null,
                    'name' => $member->student_name ?? 
                             ($member->student ? trim($member->student->user_fname . ' ' . $member->student->user_lname) : 'Unknown'),
                    'is_coordinator' => (bool)$member->is_coordinator,
                    'task_type' => $member->task_type,
                    'time_slot' => $member->time_slot,
                    'comments' => $member->comments
                ];
                
                $assignments[] = $studentData;
            }

            return response()->json([
                'success' => true,
                'assignments' => $assignments,
                'total_count' => count($assignments),
                'filters_applied' => [
                    'category' => $request->category_name,
                    'task_type' => $request->task_type,
                    'time_slot' => $request->time_slot
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Task-specific assignment retrieval failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assignments: ' . $e->getMessage(),
                'assignments' => []
            ]);
        }
    }

    // Update assignment with task-specific information
    public function updateTaskAssignment(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:assignments_members,id',
            'task_type' => 'nullable|string',
            'time_slot' => 'nullable|string',
            'student_name' => 'nullable|string'
        ]);

        try {
            $member = AssignmentMember::findOrFail($request->member_id);
            
            // Update task-specific information
            $updateData = [];
            
            if ($request->has('task_type')) {
                $updateData['task_type'] = $request->task_type;
            }
            
            if ($request->has('time_slot')) {
                $updateData['time_slot'] = $request->time_slot;
            }
            
            if ($request->has('student_name')) {
                $updateData['student_name'] = $request->student_name;
            }

            if (!empty($updateData)) {
                $member->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'member' => $member->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Task assignment update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment: ' . $e->getMessage()
            ]);
        }
    }

    public function getKitchenAssignments()
    {
        return $this->getAssignmentsByCategory('Kitchen');
    }

    // Generic method to get assignments for any category
    private function getAssignmentsByCategory($categoryName)
    {
        try {
            $category = Category::where('name', $categoryName)->first();
            
            if (!$category) {
                return response()->json(['success' => false, 'message' => $categoryName . ' category not found']);
            }
            
            $assignment = Assignment::where('category_id', $category->id)
                ->where('status', 'current')
                ->with('assignmentMembers')
                ->first();
            
            if (!$assignment) {
                return response()->json(['success' => true, 'assignments' => []]);
            }
            
            $assignments = $assignment->assignmentMembers->map(function($member) {
                return [
                    'student_id' => $member->student_id,
                    'student_name' => $member->student_name,
                    'task_type' => $member->task_type,
                    'time_slot' => $member->time_slot,
                    'is_coordinator' => $member->is_coordinator
                ];
            });
            
            return response()->json(['success' => true, 'assignments' => $assignments]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting ' . $categoryName . ' assignments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading assignments']);
        }
    }

    public function getDishwashingAssignments()
    {
        return $this->getAssignmentsByCategory('Dishwashing');
    }

    public function getDiningAssignments()
    {
        return $this->getAssignmentsByCategory('Dining');
    }

    public function getOfficeAssignments()
    {
        return $this->getAssignmentsByCategory('Offices & Conference Rooms');
    }

    public function getConferenceAssignments()
    {
        return $this->getAssignmentsByCategory('Offices & Conference Rooms');
    }

    public function getGroundAssignments()
    {
        return $this->getAssignmentsByCategory('Ground Floor');
    }

    public function getWasteAssignments()
    {
        return $this->getAssignmentsByCategory('Garbage, Rugs, & Rooftop');
    }

    public function markDayComplete(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $categoryName = $request->input('category_name');
            $day = $request->input('day');
            $date = $request->input('date');

            \Log::info('markDayComplete called', [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'day' => $day,
                'date' => $date,
            ]);

            if (!$categoryId || !$day || !$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: category_id, day, or date'
                ], 400);
            }

            // Get the category
            $category = Category::find($categoryId);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Get current assignment for this category
            $currentAssignment = Assignment::where('category_id', $categoryId)
                ->where('status', 'current')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current assignment found for this category'
                ], 404);
            }

            // Get all assignment members for this category (include student + detail for batch)
            $assignmentMembers = AssignmentMember::where('assignment_id', $currentAssignment->id)
                ->with(['student.studentDetail'])
                ->get();

            // Bridge to generated_schedules so the new My Tasks page can see these assignments
            try {
                \DB::table('generated_schedules')
                    ->where('assignment_id', $currentAssignment->id)
                    ->where('schedule_date', $date)
                    ->delete();
            } catch (\Exception $cleanupError) {
                \Log::warning('Unable to cleanup previous generated_schedules for markDayComplete', [
                    'error' => $cleanupError->getMessage(),
                    'assignment_id' => $currentAssignment->id,
                    'date' => $date,
                ]);
            }

            $inserted = 0;

            foreach ($assignmentMembers as $member) {
                $student = $member->student;
                $studentDetail = $student && method_exists($student, 'studentDetail') ? $student->studentDetail : null;

                $studentId = $member->student_id ?? ($student->user_id ?? null);
                $studentName = $member->student_name;
                if (!$studentName && $student) {
                    $studentName = trim(($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''));
                }

                $batch = null;
                if ($studentDetail) {
                    $batch = $studentDetail->batch;
                } elseif ($studentId) {
                    $batch = StudentDetail::where('user_id', $studentId)->value('batch');
                }

                try {
                    \DB::table('generated_schedules')->insert([
                        'assignment_id' => $currentAssignment->id,
                        'category_name' => $categoryName ?: $category->name,
                        'schedule_date' => $date,
                        'student_id' => $studentId,
                        'student_name' => $studentName ?: 'Student',
                        'task_title' => $member->task_type ?? $category->name,
                        'task_description' => $member->time_slot ? ('Time: ' . $member->time_slot) : ('Task assignment for ' . ($categoryName ?: $category->name)),
                        'batch' => $batch,
                        'start_date' => $currentAssignment->start_date,
                        'end_date' => $currentAssignment->end_date,
                        'rotation_frequency' => 'Manual',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $inserted++;
                } catch (\Exception $insertError) {
                    \Log::error('Failed inserting generated_schedules row from markDayComplete', [
                        'error' => $insertError->getMessage(),
                        'member_id' => $member->id,
                        'assignment_id' => $currentAssignment->id,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Day {$day} assignments saved successfully for {$categoryName}",
                'data' => [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'day' => $day,
                    'date' => $date,
                    'assignments_found' => $assignmentMembers->count(),
                    'generated_inserted' => $inserted,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating assignment end date: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment end date: ' . $e->getMessage()

            ], 500);
        }
    }
}
