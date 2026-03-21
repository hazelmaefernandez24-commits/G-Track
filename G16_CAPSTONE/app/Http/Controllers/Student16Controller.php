<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PNUser;
use App\Models\StudentDetail;
use Illuminate\Support\Str;

class Student16Controller extends Controller
{
    public function index() {
        // Get students from Login users + student details and map to expected shape
        $users = PNUser::where('user_role', 'student')->get();
        $details = StudentDetail::whereIn('user_id', $users->pluck('user_id'))->get()->keyBy('user_id');

        $students = $users->map(function($u) use ($details) {
            $d = $details->get($u->user_id);
            return (object) [
                'id' => $u->user_id,
                'name' => trim($u->user_fname . ' ' . $u->user_lname),
                'gender' => $u->gender,
                'batch' => $d->batch ?? null
            ];
        })->sortBy(function($s) { return $s->batch; })->values();

        // Build batch lists from Login student_details
        $batches_group16 = \App\Models\StudentDetail::select('batch')
            ->distinct()
            ->orderBy('batch')
            ->get()
            ->map(function($r){ return (object)['year' => $r->batch, 'display_name' => (string)$r->batch]; });
        $activeBatches = $batches_group16;
    return view('students.index', compact('students', 'batches_group16', 'activeBatches'));
    }

    public function create() {
        return view('students.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'batch' => 'required|integer',
        ]);

        // Split name into first and last (best-effort)
        $names = preg_split('/\s+/', trim($request->name), 2);
        $first = $names[0] ?? '';
        $last = $names[1] ?? '';

        $user = PNUser::create([
            'user_id' => (string) Str::uuid(),
            'user_fname' => $first,
            'user_lname' => $last,
            'gender' => $request->gender,
            'user_role' => 'student',
            'status' => 'active'
        ]);

        StudentDetail::create([
            'user_id' => $user->user_id,
            // For admin-created students, fallback the canonical student_id to the user_id
            'student_id' => $user->user_id,
            'batch' => $request->batch
        ]);

        return redirect('/students');
    }

    public function show($id)
    {
        $user = PNUser::where('user_id', $id)->first();
        if (!$user) return response('Not found', 404);
        $detail = StudentDetail::where('user_id', $user->user_id)->first();
        $data = [
            'id' => $user->user_id,
            'name' => trim($user->user_fname . ' ' . $user->user_lname),
            'gender' => $user->gender,
            'batch' => $detail->batch ?? null
        ];
        return response()->json($data);
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function assignNewTasks()
    {
        return "Assign new tasks page";
    }

    public function shuffleTasks()
    {
        return "Shuffle tasks page";
    }

    // Remove a specific student from all assignments and database
    public function removeStudent($name)
    {
        // Try to find by full name or first name
        $parts = preg_split('/\s+/', $name, 2);
        $first = $parts[0] ?? $name;
        $last = $parts[1] ?? null;

        $query = PNUser::where('user_role', 'student')->where('user_fname', $first);
        if ($last) $query->where('user_lname', $last);
        $student = $query->first();

        if ($student) {
            // Remove any assignment members referencing this id (assignment member column uses student_group16_id)
                \App\Models\AssignmentMember::whereStudentId($student->user_id)->delete();

            StudentDetail::where('user_id', $student->user_id)->delete();
            $student->delete();

            return response()->json(['success' => true, 'message' => "Student {$name} removed successfully"]);
        }

        return response()->json(['success' => false, 'message' => "Student {$name} not found"]);
    }

    // Update student name
    public function updateName(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $user = PNUser::where('user_id', $id)->firstOrFail();
        $oldName = trim($user->user_fname . ' ' . $user->user_lname);
        $names = preg_split('/\s+/', $request->name, 2);
        $user->user_fname = $names[0] ?? $user->user_fname;
        $user->user_lname = $names[1] ?? '';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Student name updated from '{$oldName}' to '" . trim($user->user_fname . ' ' . $user->user_lname) . "'",
            'student' => $user
        ]);
    }

    // Delete student by ID
    public function destroy($id)
    {
        $user = PNUser::where('user_id', $id)->firstOrFail();
        $studentName = trim($user->user_fname . ' ' . $user->user_lname);

        \App\Models\AssignmentMember::whereStudentId($user->user_id)->delete();
        StudentDetail::where('user_id', $user->user_id)->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "Student '{$studentName}' deleted successfully"
        ]);
    }

    // Quick add student (for View Members modal)
    public function quickAdd(Request $request)
    {
    $availableBatches = \App\Models\StudentDetail::select('batch')->distinct()->orderBy('batch')->pluck('batch')->toArray();

        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'batch' => 'required|integer|in:' . implode(',', $availableBatches),
        ]);

        $names = preg_split('/\s+/', trim($request->name), 2);
        $first = $names[0] ?? '';
        $last = $names[1] ?? '';

        $user = PNUser::create([
            'user_id' => (string) Str::uuid(),
            'user_fname' => $first,
            'user_lname' => $last,
            'gender' => $request->gender,
            'user_role' => 'student',
            'status' => 'active'
        ]);

        StudentDetail::create([
            'user_id' => $user->user_id,
            'student_id' => $user->user_id,
            'batch' => $request->batch
        ]);

        return response()->json([
            'success' => true,
            'message' => "Student '" . trim($user->user_fname . ' ' . $user->user_lname) . "' added successfully",
            'student' => $user
        ]);
    }

    // Quick add student and assign to category
    public function quickAddToCategory(Request $request)
    {
    $availableBatches = \App\Models\StudentDetail::select('batch')->distinct()->orderBy('batch')->pluck('batch')->toArray();

        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'batch' => 'required|integer|in:' . implode(',', $availableBatches),
            'category_id' => 'required|exists:categories,id',
        ]);

        $names = preg_split('/\s+/', trim($request->name), 2);
        $first = $names[0] ?? '';
        $last = $names[1] ?? '';

        $user = PNUser::create([
            'user_id' => (string) Str::uuid(),
            'user_fname' => $first,
            'user_lname' => $last,
            'gender' => $request->gender,
            'user_role' => 'student',
            'status' => 'active'
        ]);

        $detail = StudentDetail::create([
            'user_id' => $user->user_id,
            'student_id' => $user->user_id,
            'batch' => $request->batch
        ]);

        // Find current assignment for the category
        $currentAssignment = \App\Models\Assignment::where('category_id', $request->category_id)
            ->where('status', 'current')
            ->first();

        if ($currentAssignment) {
            // Add student to the assignment using canonical student_id from student_details
            // Use safe create to avoid DB constraint errors
            $createData = ['assignment_id' => $currentAssignment->id, 'student_code' => $detail->student_id, 'is_coordinator' => false];

            // Resolve student_code (canonical student_id) to student_group16_id (underlying user_id)
            if ((empty($createData['student_group16_id']) || $createData['student_group16_id'] === null) && !empty($createData['student_code'])) {
                try {
                    $resolved = StudentDetail::where('student_id', $createData['student_code'])->pluck('user_id')->first();
                    if ($resolved) $createData['student_group16_id'] = $resolved;
                } catch (\Exception $e) {
                    \Log::warning('quickAddToCategory failed to resolve student_code', ['student_code' => $createData['student_code'], 'error' => $e->getMessage()]);
                }
            }

            if ((empty($createData['student_code']) || $createData['student_code'] === null) && (empty($createData['student_group16_id']) || $createData['student_group16_id'] === null)) {
                \Log::warning('quickAddToCategory skipped AssignmentMember create: no identifier after resolution', $createData);
            } elseif (empty($createData['student_group16_id'])) {
                \Log::warning('quickAddToCategory cannot create AssignmentMember: student_group16_id unresolved', $createData);
            } else {
                \App\Models\AssignmentMember::create($createData);
            }

            $categoryName = \App\Models\Category::find($request->category_id)->name;
            return response()->json([
                'success' => true,
                'message' => "Student '" . trim($user->user_fname . ' ' . $user->user_lname) . "' added to system and assigned to {$categoryName}",
                'student' => $user
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "Student '" . trim($user->user_fname . ' ' . $user->user_lname) . "' added to system (no current assignment found for category)",
                'student' => $user
            ]);
        }
    }

    // Get all students for deletion
    public function getAllForDeletion()
    {
        $details = StudentDetail::with('user')->get();
        $students = $details->map(function($d) {
            return [
                'id' => $d->user_id,
                'name' => trim($d->user->user_fname . ' ' . $d->user->user_lname),
                'batch' => $d->batch
            ];
        });

        $students2025 = $students->where('batch', 2025)->values();
        $students2026 = $students->where('batch', 2026)->values();

        return response()->json([
            'success' => true,
            'students2025' => $students2025,
            'students2026' => $students2026
        ]);
    }

    // Delete multiple students
    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $ids = $request->student_ids;
        $details = StudentDetail::whereIn('user_id', $ids)->with('user')->get();
        $studentNames = $details->map(function($d){ return trim($d->user->user_fname . ' ' . $d->user->user_lname); })->toArray();

        // Remove from all assignment_members first (due to foreign key constraints)
        \App\Models\AssignmentMember::whereInStudentIds($ids)->delete();

        // Then delete student details and users
        StudentDetail::whereIn('user_id', $ids)->delete();
        PNUser::whereIn('user_id', $ids)->delete();

        $count = count($ids);
        $namesList = implode(', ', $studentNames);

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} student(s) from system: {$namesList}"
        ]);
    }
}