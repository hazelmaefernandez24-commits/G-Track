<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DynamicTaskCategory;
use App\Models\DynamicTaskAssignment;
use App\Models\DynamicTaskMember;
use App\Models\LoginPNUser;
use App\Models\LoginStudentDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DynamicTaskController extends Controller
{
    /**
     * Apply middleware to all methods
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin'])->except(['index']);
        $this->middleware('throttle:60,1')->only(['storeCategory', 'storeAssignment', 'updateCategory', 'updateAssignment']);
    }

    /**
     * Display the dynamic task dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access denied. Admin privileges required.');
        }

        // Get all active categories with current assignments
        $categories = DynamicTaskCategory::active()
            ->ordered()
            ->with(['currentAssignments.members.student.studentDetail'])
            ->get();

        // Get all students for assignment
        $students = LoginPNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->with('studentDetail')
            ->orderBy('user_fname')
            ->orderBy('user_lname')
            ->get();

        // Get active batches
        $activeBatches = LoginStudentDetail::getActiveBatches();

        return view('generalTask', compact('categories', 'students', 'activeBatches'));
    }

    /**
     * Store a new task category
     */
    public function storeCategory(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:dynamic_task_categories,name|regex:/^[a-zA-Z0-9\s\-_&]+$/',
            'description' => 'nullable|string|max:1000',
            'color_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'max_students' => 'nullable|integer|min:1|max:100',
            'max_boys' => 'nullable|integer|min:0|max:50',
            'max_girls' => 'nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Sanitize input data
            $sanitizedData = [
                'name' => strip_tags(trim($request->name)),
                'description' => $request->description ? strip_tags(trim($request->description)) : null,
                'color_code' => $request->color_code ?? '#007bff',
                'max_students' => $request->max_students ? (int)$request->max_students : null,
                'max_boys' => $request->max_boys ? (int)$request->max_boys : null,
                'max_girls' => $request->max_girls ? (int)$request->max_girls : null,
                'is_active' => true,
                'sort_order' => DynamicTaskCategory::max('sort_order') + 1
            ];

            $category = DynamicTaskCategory::create($sanitizedData);

            // Log the action for audit trail
            Log::info('Category created', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'created_by' => Auth::user()->user_id,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create category', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->user_id,
                'ip_address' => $request->ip()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to create category'], 500);
        }
    }

    /**
     * Update a task category
     */
    public function updateCategory(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $category = DynamicTaskCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:dynamic_task_categories,name,' . $id,
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'max_students' => 'nullable|integer|min:1',
            'max_boys' => 'nullable|integer|min:0',
            'max_girls' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'color_code' => $request->color_code ?? $category->color_code,
                'max_students' => $request->max_students,
                'max_boys' => $request->max_boys,
                'max_girls' => $request->max_girls,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update category'], 500);
        }
    }

    /**
     * Delete a task category
     */
    public function deleteCategory($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $category = DynamicTaskCategory::findOrFail($id);
            
            // Check if category has active assignments
            if ($category->currentAssignments()->count() > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot delete category with active assignments'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete category'], 500);
        }
    }

    /**
     * Store a new task assignment
     */
    public function storeAssignment(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:dynamic_task_categories,id',
            'assignment_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:login_db.pnph_users,user_id',
            'coordinators' => 'nullable|array',
            'coordinators.*' => 'exists:login_db.pnph_users,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Create assignment
            $assignment = DynamicTaskAssignment::create([
                'category_id' => $request->category_id,
                'assignment_name' => $request->assignment_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'current',
                'created_by' => Auth::user()->user_id
            ]);

            // Add members
            foreach ($request->student_ids as $studentId) {
                DynamicTaskMember::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $studentId,
                    'is_coordinator' => in_array($studentId, $request->coordinators ?? []),
                    'assigned_by' => Auth::user()->user_id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'assignment' => $assignment->load('members.student')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create assignment'], 500);
        }
    }

    /**
     * Update an existing assignment
     */
    public function updateAssignment(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $assignment = DynamicTaskAssignment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'assignment_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:pending,current,completed,cancelled',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:login_db.pnph_users,user_id',
            'coordinators' => 'nullable|array',
            'coordinators.*' => 'exists:login_db.pnph_users,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update assignment
            $assignment->update([
                'assignment_name' => $request->assignment_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status
            ]);

            // Remove existing members
            $assignment->members()->delete();

            // Add new members
            foreach ($request->student_ids as $studentId) {
                DynamicTaskMember::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $studentId,
                    'is_coordinator' => in_array($studentId, $request->coordinators ?? []),
                    'assigned_by' => Auth::user()->user_id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'assignment' => $assignment->load('members.student')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update assignment'], 500);
        }
    }

    /**
     * Delete an assignment
     */
    public function deleteAssignment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $assignment = DynamicTaskAssignment::findOrFail($id);
            $assignment->delete(); // This will cascade delete members due to foreign key constraint

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete assignment'], 500);
        }
    }

    /**
     * Get assignment details
     */
    public function getAssignment($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $assignment = DynamicTaskAssignment::with(['category', 'members.student.studentDetail', 'creator'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'assignment' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }
    }

    /**
     * Get available students for assignment
     */
    public function getAvailableStudents(Request $request)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $students = LoginPNUser::where('user_role', 'student')
            ->where('status', 'active')
            ->with('studentDetail')
            ->orderBy('user_fname')
            ->orderBy('user_lname')
            ->get()
            ->map(function($student) {
                return [
                    'user_id' => $student->user_id,
                    'name' => $student->full_name,
                    'gender' => $student->gender,
                    'batch' => $student->studentDetail ? $student->studentDetail->batch : null
                ];
            });

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /**
     * Get category members
     */
    public function getCategoryMembers($categoryId)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        try {
            $category = DynamicTaskCategory::with(['currentAssignments.members.student.studentDetail'])
                ->findOrFail($categoryId);

            $members = [];
            foreach ($category->currentAssignments as $assignment) {
                foreach ($assignment->members as $member) {
                    if ($member->student) {
                        $members[] = [
                            'id' => $member->id,
                            'student_id' => $member->student_id,
                            'name' => trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? '')),
                            'gender' => $member->student->gender,
                            'batch' => $member->student->studentDetail ? $member->student->studentDetail->batch : null,
                            'is_coordinator' => $member->is_coordinator,
                            'comments' => $member->comments,
                            'assignment_id' => $member->assignment_id
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'members' => $members,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }
    }

    /**
     * Check if current user is admin
     */
    private function isAdmin()
    {
        return Auth::check() && Auth::user()->user_role === 'admin';
    }
}
