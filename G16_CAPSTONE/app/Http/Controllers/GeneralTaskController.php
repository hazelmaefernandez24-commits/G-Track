<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use App\Models\Category;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\TaskChecklist;
use App\Models\CategoryChecklist;
use App\Models\StudentDetail;
use App\Models\GeneratedSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GeneralTaskController extends Controller
{
    public function index() {
        AssignmentMember::cleanExpiredComments();
        
        // AUTOMATIC DUPLICATE CLEANUP: Remove duplicate members from ALL current assignments
        $this->removeDuplicatesFromAllAssignments();

        // Fetch ALL CATEGORIES (both main areas and sub-areas) with their assignments and checklists
        // Main areas are for organization, sub-areas are the actual task cards
        // CRITICAL: Load student.studentDetail to get batch information!
        $categories = Category::with(['assignments' => function($query) {
            $query->where('status', 'current');
        }, 'assignments.assignmentMembers.student.studentDetail', 'subCategories.assignments' => function($query) {
            $query->where('status', 'current');
        }, 'subCategories.assignments.assignmentMembers.student.studentDetail', 'parentCategory'])
        ->get();

        // Get students from Login database properly
        $users = PNUser::where('user_role', 'student')->get();
        $details = StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');

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

        $activeBatches = StudentDetail::select('batch')
            ->distinct()
            ->orderBy('batch')
            ->get()
            ->map(function($r) {
                return (object)[
                    'year' => $r->batch,
                    'display_name' => (string)$r->batch,
                ];
            });

        // Get assignments - only for SUB-AREAS (categories with parent_id)
        // Main areas don't have assignments, only sub-areas do
        $assignments = Assignment::with(['category', 'assignmentMembers.student'])
            ->whereHas('category', function($query) {
                $query->whereNotNull('parent_id'); // Only sub-areas
            })
            ->whereIn('status', ['current', 'active'])
            ->orderBy('start_date', 'desc')
            ->get();
            
        // If no current/active assignments, get the latest assignments for sub-areas only
        if ($assignments->isEmpty()) {
            $assignments = Assignment::with(['category', 'assignmentMembers.student'])
                ->whereHas('category', function($query) {
                    $query->whereNotNull('parent_id'); // Only sub-areas
                })
                ->orderBy('id', 'desc')
                ->get();
        }
        
        // FORCE LOAD student relationships if not already loaded
        foreach ($assignments as $assignment) {
            foreach ($assignment->assignmentMembers as $member) {
                if (!$member->relationLoaded('student') && $member->student_id) {
                    $member->load('student');
                }
            }
        }
        
        \Log::info('Debug - Assignments query result: ' . $assignments->count() . ' assignments found');

        $assignmentHistory = Assignment::with(['category', 'assignmentMembers.student'])
            ->orderBy('status', 'asc') // current first, then previous
            ->orderBy('id', 'desc') // newest first within same status
            ->get();

        // Create assignmentDetails array for the blade template
        $assignmentDetails = [];
        
        // Debug: Log what assignments we have
        \Log::info('Debug - Total assignments found: ' . $assignments->count());
        
        // Debug: Check if we have any assignment members at all
        $totalMembers = \DB::table('assignments_members')->count();
        \Log::info('Debug - Total assignment members in database: ' . $totalMembers);
        
        // Debug: Check if we have students in PNUser
        $totalStudents = \DB::table('pnph_users')->where('user_role', 'student')->count();
        \Log::info('Debug - Total students in pnph_users: ' . $totalStudents);
        
        foreach ($assignments as $assignment) {
            $categoryName = $assignment->category->category_name;
            \Log::info('Debug - Processing category: ' . $categoryName);
            \Log::info('Debug - Assignment members count: ' . $assignment->assignmentMembers->count());
            
            // Use the actual category name directly (no more hardcoded mapping)
            $mappedCategoryName = $categoryName;
            
            \Log::info('Debug - Using dynamic category name: ' . $mappedCategoryName);
            
            // Group members by batch and separate coordinators - include ALL batches
            $members2025 = [];
            $members2026 = [];
            $members2027 = [];
            $coordinators2025 = [];
            $coordinators2026 = [];
            $coordinators2027 = [];
            
            foreach ($assignment->assignmentMembers as $member) {
                \Log::info('Debug - Processing assignment member: ' . ($member->id ?? 'no-id'));
                
                // Try to get student data - first from relationship, then direct lookup
                $student = $member->student;
                if (!$student && $member->student_id) {
                    $student = PNUser::find($member->student_id);
                }
                
                if ($student) {
                    $studentDetail = StudentDetail::where('user_id', $student->user_id)->first();
                    $batch = $studentDetail ? $studentDetail->batch : null;
                    
                    // If batch is not set, try to parse from student_code
                    if (!$batch && $studentDetail && !empty($studentDetail->student_code)) {
                        if (preg_match('/^(20\d{2})/', $studentDetail->student_code, $matches)) {
                            $batch = (int)$matches[1];
                            \Log::info('Debug - Parsed batch from student_code: ' . $studentDetail->student_code . ' -> ' . $batch);
                        }
                    }
                    
                    // If still no batch, try parsing from student_id
                    if (!$batch && $studentDetail && !empty($studentDetail->student_id)) {
                        if (preg_match('/^(20\d{2})/', $studentDetail->student_id, $matches)) {
                            $batch = (int)$matches[1];
                            \Log::info('Debug - Parsed batch from student_id: ' . $studentDetail->student_id . ' -> ' . $batch);
                        }
                    }
                    
                    $studentName = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                    $isCoordinator = $member->is_coordinator ?? false;
                    \Log::info('Debug - Student: ' . $studentName . ' (Batch: ' . $batch . ', Coordinator: ' . ($isCoordinator ? 'Yes' : 'No') . ')');
                    
                    $memberData = [
                        'id' => $student->user_id,
                        'name' => $studentName,
                        'gender' => $student->gender ?? 'Unknown',
                        'is_coordinator' => $isCoordinator
                    ];
                    
                    // STRICT BATCH SEPARATION: Only assign to correct batch, skip if unknown
                    if ($batch == 2025) {
                        $members2025[] = $memberData;
                        if ($isCoordinator) {
                            $coordinators2025[] = $studentName;
                        }
                        \Log::info('Debug - C2025 Student: ' . $studentName . ($isCoordinator ? ' [COORDINATOR]' : ''));
                    } elseif ($batch == 2026) {
                        $members2026[] = $memberData;
                        if ($isCoordinator) {
                            $coordinators2026[] = $studentName;
                        }
                    } else {
                        // DO NOT add to any batch if batch cannot be determined
                        // This prevents students from appearing in wrong batches
                        \Log::warning('Debug - Student ' . $studentName . ' has unknown batch (' . $batch . ') and will not be displayed. Student code: ' . ($studentDetail->student_code ?? 'N/A') . ', Student ID: ' . ($studentDetail->student_id ?? 'N/A'));
                    }
                } else {
                    \Log::info('Debug - Assignment member has no student relationship');
                }
            }
            
            $assignmentDetails[$mappedCategoryName] = [
                'id' => $assignment->category->id,
                'category' => $mappedCategoryName,
                'description' => $assignment->category->description ?? 'No description provided.',
                'parent_id' => $assignment->category->parent_id,
                'is_main_area' => is_null($assignment->category->parent_id),
                'members_2025' => $members2025,
                'members_2026' => $members2026,
                'members_2027' => $members2027,
                'coordinators_2025' => $coordinators2025,
                'coordinators_2026' => $coordinators2026,
                'coordinators_2027' => $coordinators2027,
                'total_members' => count($members2025) + count($members2026) + count($members2027),
                'total_coordinators' => count($coordinators2025) + count($coordinators2026) + count($coordinators2027)
            ];
            
            \Log::info('Debug - Final assignment details for ' . $mappedCategoryName . ': 2025=' . count($members2025) . ', 2026=' . count($members2026) . ', 2027=' . count($members2027));
        }
        
        \Log::info('Debug - Total assignmentDetails created: ' . count($assignmentDetails));

        // Create dynamicStructure for admin view compatibility
        $dynamicStructure = collect();
        
        // Group categories by parent (main areas)
        $mainAreas = $categories->whereNull('parent_id');
        $subAreas = $categories->whereNotNull('parent_id');
        
        foreach ($mainAreas as $mainArea) {
            $subCategoriesForMain = $subAreas->where('parent_id', $mainArea->id);
            $dynamicStructure[$mainArea->name] = $subCategoriesForMain;
        }
        
        // If there are sub-areas without parents, group them under "Other"
        $orphanSubAreas = $subAreas->whereNotIn('parent_id', $mainAreas->pluck('id'));
        if ($orphanSubAreas->isNotEmpty()) {
            $dynamicStructure['Other'] = $orphanSubAreas;
        }

        // Pass all data to the dashboard view - check user role to determine which view to return
        $user = auth()->user();
        if ($user && in_array($user->user_role, ['student', 'coordinator'])) {
            // Student view - use same exact view as admin for identical UI
            return view('generalTask', compact('categories', 'students', 'assignments', 'assignmentHistory', 'activeBatches', 'assignmentDetails', 'dynamicStructure'));
        } else {
            // Admin view - use dynamicStructure format
            return view('generalTask', compact('categories', 'students', 'assignments', 'assignmentHistory', 'activeBatches', 'assignmentDetails', 'dynamicStructure'));
        }
    }

    public function taskChecklist()
    {
        // Get current week start (Monday)
        $currentWeekStart = Carbon::now()->startOfWeek();

        // Initialize default tasks if they don't exist for this week
        $this->initializeDefaultTasks($currentWeekStart);

        // Get all tasks for current week
        $tasks = TaskChecklist::where('week_start_date', $currentWeekStart)->get();

        return view('task-checklist', compact('tasks', 'currentWeekStart'));
    }

    public function updateTaskStatus(Request $request)
    {
        $task = TaskChecklist::findOrFail($request->task_id);

        if ($request->week == 1) {
            $status = $task->week1_status ?? array_fill(0, 7, null);
            $status[$request->day] = $request->status;
            $task->week1_status = $status;
        } else {
            $status = $task->week2_status ?? array_fill(0, 7, null);
            $status[$request->day] = $request->status;
            $task->week2_status = $status;
        }

        $task->save();

        return response()->json(['success' => true]);
    }

    public function updateTaskRemarks(Request $request)
    {
        $task = TaskChecklist::findOrFail($request->task_id);

        if ($request->week == 1) {
            $task->week1_remarks = $request->remarks;
        } else {
            $task->week2_remarks = $request->remarks;
        }

        $task->save();

        return response()->json(['success' => true]);
    }

    public function updateWeekDates(Request $request)
    {
        $weekStart = Carbon::parse($request->week_start_date)->startOfWeek();

        // Update all tasks for this week
        TaskChecklist::where('week_start_date', $weekStart)
            ->update(['week_start_date' => $weekStart]);

        return response()->json(['success' => true]);
    }

    private function initializeDefaultTasks($weekStart)
    {
        // Check if tasks already exist for this week
        $existingTasks = TaskChecklist::where('week_start_date', $weekStart)->count();

        if ($existingTasks > 0) {
            return; // Tasks already exist
        }

        $defaultTasks = [
            // Kitchen Tasks
            ['category' => 'KITCHEN', 'description' => 'Assigned members wake up on time and completed their tasks as scheduled.'],
            ['category' => 'KITCHEN', 'description' => 'The students assigned to cook the rice completed the task properly.'],
            ['category' => 'KITCHEN', 'description' => 'The students assigned to cook the viand completed the task properly.'],
            ['category' => 'KITCHEN', 'description' => 'The students assigned to assist the cook carried out their duties diligently.'],
            ['category' => 'KITCHEN', 'description' => 'Ingredients were prepared ahead of time.'],
            ['category' => 'KITCHEN', 'description' => 'The kitchen was properly cleaned after cooking.'],
            ['category' => 'KITCHEN', 'description' => 'The food was transferred from the kitchen to the center.'],
            ['category' => 'KITCHEN', 'description' => 'Proper inventory of stocks was maintained and deliveries were handled appropriately.'],
            ['category' => 'KITCHEN', 'description' => 'Water and food supplies were regularly monitored and stored in the proper place.'],
            ['category' => 'KITCHEN', 'description' => 'Receipts, kitchen phones, and keys were safely stored.'],
            ['category' => 'KITCHEN', 'description' => 'Kitchen utensils were properly stored.'],
            ['category' => 'KITCHEN', 'description' => 'The stove was turned off after cooking.'],
            ['category' => 'KITCHEN', 'description' => 'Properly disposed of the garbage.'],

            // General Cleaning Tasks
            ['category' => 'GENERAL CLEANING', 'description' => 'Properly washed the burner.'],
            ['category' => 'GENERAL CLEANING', 'description' => 'Wiped and arranged the chiller.'],
            ['category' => 'GENERAL CLEANING', 'description' => 'Cleaned the canal after cooking.'],
            ['category' => 'GENERAL CLEANING', 'description' => 'Arranged the freezer.'],
        ];

        foreach ($defaultTasks as $task) {
            TaskChecklist::create([
                'task_category' => $task['category'],
                'task_description' => $task['description'],
                'week_start_date' => $weekStart,
                'week1_status' => array_fill(0, 7, null),
                'week2_status' => array_fill(0, 7, null),
            ]);
        }
    }

    /**
     * Add a new task area (main area or sub area)
     * Main areas are containers, sub areas are actual task cards
     */
    public function addTaskArea(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
            'area_type' => 'required|in:main,sub',
            'parent_id' => 'nullable|exists:categories,id',
            'color_code' => 'nullable|string',
            'checklist_items' => 'nullable|array'
        ]);

        try {
            $isMainArea = $request->area_type === 'main';
            $isSubArea = $request->area_type === 'sub';

            // Normalize and validate color for sub areas
            $colorCode = null;
            if ($isSubArea) {
                $incomingColor = $request->input('color_code');
                if (is_string($incomingColor) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', trim($incomingColor))) {
                    $colorCode = trim($incomingColor);
                } else {
                    $colorCode = '#45B7D1';
                }
            }

            // Validate sub area has parent
            if ($isSubArea && !$request->parent_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sub areas must have a parent main area selected.'
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $isSubArea ? $request->parent_id : null,
                'color_code' => $colorCode,
            ]);

            // Save checklist items for sub areas only
            if ($isSubArea && $request->has('checklist_items') && !empty($request->checklist_items)) {
                CategoryChecklist::create([
                    'category_id' => $category->id,
                    'checklist_items' => array_filter($request->checklist_items) // Remove empty items
                ]);
            }

            $message = $isMainArea 
                ? "Main area '{$request->name}' created successfully! You can now add sub-areas under it."
                : "Sub area '{$request->name}' created successfully! It will appear as a task card for auto-shuffle.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'category' => $category,
                'area_type' => $request->area_type,
                'is_main_area' => $isMainArea,
                'is_sub_area' => $isSubArea,
                'has_checklist' => $isSubArea && $request->has('checklist_items') && !empty($request->checklist_items)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating task area: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task area. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an existing task area
     */
    public function updateTaskArea(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string'
        ]);

        try {
            $category = Category::findOrFail($id);
            
            // Check if new name conflicts with existing categories (excluding current)
            if ($request->filled('name')) {
                $existingCategory = Category::where('name', $request->name)
                                         ->where('id', '!=', $id)
                                         ->first();
                if ($existingCategory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A task area with this name already exists.'
                    ], 422);
                }
            }

            // Prepare update payload: always update name; update description/color when provided
            $updateData = [];
            if ($request->filled('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }

            if ($request->has('color_code')) {
                $incoming = trim((string)$request->input('color_code'));
                if (is_string($incoming) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $incoming)) {
                    $updateData['color_code'] = $incoming;
                }
            }

            $category->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Task area updated successfully!',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'parent_id' => $category->parent_id,
                    'is_main_area' => is_null($category->parent_id),
                    'color_code' => $category->color_code,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating task area: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task area. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a task area permanently (including assignments and members)
     */
    public function deleteTaskArea($id)
    {
        try {
            $category = Category::findOrFail($id);
            $categoryName = $category->name;
            $isMainArea = is_null($category->parent_id);
            
            \Log::info("Attempting to delete task area: {$categoryName} (ID: {$id})");

            // For main areas, check if they have sub-areas
            if ($isMainArea) {
                $subAreas = $category->subCategories()->get();
                if ($subAreas->count() > 0) {
                    $subAreaNames = $subAreas->pluck('name')->join(', ');
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete main area '{$categoryName}' because it has sub-areas: {$subAreaNames}. Please delete sub-areas first."
                    ], 422);
                }
            }

            // Start database transaction for safe deletion
            \DB::beginTransaction();

            try {
                // Step 1: Delete all assignment members for this category
                $assignments = $category->assignments()->get();
                $deletedMembers = 0;
                $deletedAssignments = 0;

                foreach ($assignments as $assignment) {
                    // Delete assignment members
                    $memberCount = $assignment->assignmentMembers()->count();
                    $assignment->assignmentMembers()->delete();
                    $deletedMembers += $memberCount;
                    
                    // Delete the assignment itself
                    $assignment->delete();
                    $deletedAssignments++;
                }

                // Step 2: Delete the category itself
                $category->delete();

                // Commit the transaction
                \DB::commit();

                \Log::info("Successfully deleted task area '{$categoryName}': {$deletedAssignments} assignments, {$deletedMembers} members");

                return response()->json([
                    'success' => true,
                    'message' => "Task area '{$categoryName}' permanently deleted successfully!",
                    'details' => [
                        'deleted_assignments' => $deletedAssignments,
                        'deleted_members' => $deletedMembers,
                        'category_name' => $categoryName,
                        'was_main_area' => $isMainArea
                    ]
                ]);

            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollback();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task area not found. It may have already been deleted.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting task area: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task area: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all main areas for dropdown selection
     */
    public function getMainAreas()
    {
        try {
            $mainAreas = Category::whereNull('parent_id')
                               ->orderBy('name')
                               ->get(['id', 'name', 'description']);

            return response()->json([
                'success' => true,
                'main_areas' => $mainAreas
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching main areas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch main areas.'
            ], 500);
        }
    }
    
    /**
     * Remove duplicate members from all current assignments
     * Keeps only the first occurrence of each unique student (by name, case-insensitive)
     */
    private function removeDuplicatesFromAllAssignments()
    {
        try {
            $currentAssignments = Assignment::where('status', 'current')->get();
            $totalRemoved = 0;
            
            foreach ($currentAssignments as $assignment) {
                $members = AssignmentMember::where('assignment_id', $assignment->id)
                    ->orderBy('id', 'asc') // Keep oldest entry
                    ->get();
                
                $seenNames = [];
                
                foreach ($members as $member) {
                    $normalizedName = strtolower(trim($member->student_name ?? ''));
                    
                    if (empty($normalizedName)) {
                        continue;
                    }
                    
                    if (in_array($normalizedName, $seenNames)) {
                        // This is a duplicate - delete it
                        \Log::info("🗑️ Removing duplicate: {$member->student_name} (ID: {$member->id}) from assignment {$assignment->id}");
                        $member->delete();
                        $totalRemoved++;
                    } else {
                        // First occurrence - keep it
                        $seenNames[] = $normalizedName;
                    }
                }
            }
            
            if ($totalRemoved > 0) {
                \Log::info("✅ DUPLICATE CLEANUP: Removed {$totalRemoved} duplicate member(s) from all assignments");
            }
            
            return $totalRemoved;
        } catch (\Exception $e) {
            \Log::error("Error removing duplicates: " . $e->getMessage());
            return 0;
        }
    }
}