<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ClassModel;
use App\Models\PNUser;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with(['students', 'school'])->get();
        return view('training.classes.index', compact('classes'))->with('title', 'Manage Students - Classes');
    }

    public function create(Request $request)
    {
        $schoolId = $request->query('school');
        $school = School::where('school_id', $schoolId)->firstOrFail();
        
        $students = PNUser::where('user_role', 'student')
            ->with('studentDetail')
            ->get();
        return view('training.classes.create', compact('school', 'students'))->with('title', 'Create Class');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|string|unique:classes,class_id',
                'class_name' => 'required|string',
                'school_id' => 'required|exists:schools,school_id',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'exists:pnph_users,user_id'
            ]);

            // Check if any selected students are already in other classes
            if ($request->has('student_ids') && !empty($request->student_ids)) {
                $studentsAlreadyInClasses = DB::table('class_student')
                    ->join('classes', 'class_student.class_id', '=', 'classes.id')
                    ->join('pnph_users', 'class_student.user_id', '=', 'pnph_users.user_id')
                    ->whereIn('class_student.user_id', $request->student_ids)
                    ->select(
                        'pnph_users.user_fname',
                        'pnph_users.user_lname',
                        'pnph_users.user_id',
                        'classes.class_name',
                        'classes.class_id'
                    )
                    ->get();

                if ($studentsAlreadyInClasses->isNotEmpty()) {
                    $errorMessages = [];
                    foreach ($studentsAlreadyInClasses as $student) {
                        $errorMessages[] = "{$student->user_fname} {$student->user_lname} ({$student->user_id}) is already enrolled in class '{$student->class_name}' ({$student->class_id})";
                    }

                    return back()
                        ->withInput()
                        ->with('error', 'Cannot add students who are already enrolled in other classes:')
                        ->with('student_conflicts', $errorMessages);
                }
            }

            $class = new ClassModel();
            $class->class_id = $validated['class_id'];
            $class->class_name = $validated['class_name'];
            $class->school_id = $validated['school_id'];
            $class->save();

            // If there are student IDs, attach them to the class
            if ($request->has('student_ids')) {
                $class->students()->attach($request->student_ids);
            }

            $message = 'Class created successfully.';
            if ($request->has('student_ids') && !empty($request->student_ids)) {
                $studentCount = count($request->student_ids);
                $message .= " {$studentCount} student(s) have been enrolled in this class.";
            }

            return redirect()
                ->route('training.classes.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating class: ' . $e->getMessage());
        }
    }

    public function edit(ClassModel $class)
    {
        $class->load(['school', 'students']);
        $students = PNUser::where('user_role', 'student')->get();
        return view('training.classes.edit', compact('class', 'students'))->with('title', 'Edit Class');
    }

    public function update(Request $request, ClassModel $class)
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|string|unique:classes,class_id,' . $class->id,
                'class_name' => 'required|string',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'exists:pnph_users,user_id'
            ]);

            // Check if any selected students are already in other classes (excluding current class)
            if ($request->has('student_ids') && !empty($request->student_ids)) {
                $studentsAlreadyInClasses = DB::table('class_student')
                    ->join('classes', 'class_student.class_id', '=', 'classes.id')
                    ->join('pnph_users', 'class_student.user_id', '=', 'pnph_users.user_id')
                    ->whereIn('class_student.user_id', $request->student_ids)
                    ->where('classes.id', '!=', $class->id) // Exclude current class
                    ->select(
                        'pnph_users.user_fname',
                        'pnph_users.user_lname',
                        'pnph_users.user_id',
                        'classes.class_name',
                        'classes.class_id'
                    )
                    ->get();

                if ($studentsAlreadyInClasses->isNotEmpty()) {
                    $errorMessages = [];
                    foreach ($studentsAlreadyInClasses as $student) {
                        $errorMessages[] = "{$student->user_fname} {$student->user_lname} ({$student->user_id}) is already enrolled in class '{$student->class_name}' ({$student->class_id})";
                    }

                    return back()
                        ->withInput()
                        ->with('error', 'Cannot add students who are already enrolled in other classes:')
                        ->with('student_conflicts', $errorMessages);
                }
            }

            $class->update([
                'class_id' => $validated['class_id'],
                'class_name' => $validated['class_name'],
            ]);

            // Get current students for comparison
            $currentStudents = $class->students->pluck('user_id')->toArray();
            $newStudents = $request->student_ids ?? [];

            // Update students
            if ($request->has('student_ids')) {
                $class->students()->sync($request->student_ids);
            } else {
                $class->students()->detach();
            }

            // Create detailed success message
            $addedStudents = array_diff($newStudents, $currentStudents);
            $removedStudents = array_diff($currentStudents, $newStudents);

            $successMessage = 'Class information has been updated successfully.';

            if (!empty($addedStudents)) {
                $successMessage .= ' ' . count($addedStudents) . ' student(s) added to the class.';
            }

            if (!empty($removedStudents)) {
                $successMessage .= ' ' . count($removedStudents) . ' student(s) removed from the class.';
            }

            return redirect()->route('training.schools.show', ['school' => $class->school_id])
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating class: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified class from storage.
     *
     * @param  \App\Models\ClassModel  $class
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClassModel $class)
    {
        try {
            // Store the school ID before deleting the class
            $schoolId = $class->school_id;
            
            // Detach all students from the class
            $class->students()->detach();
            
            // Delete the class
            $class->delete();

            return redirect()->route('training.classes.index')
                ->with('success', 'Class deleted successfully');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error deleting class: ' . $e->getMessage());
        }
    }

    public function show(ClassModel $class)
    {
        $class->load(['school', 'students.studentDetail']);
        return view('training.classes.show', compact('class'))->with('title', 'Class Details');
    }

    public function getStudentsList(Request $request)
    {
        $batch = $request->query('batch_id');
        
        $query = PNUser::where('user_role', 'Student')
            ->where('status', 'active')
            ->with('studentDetail');

        if ($batch) {
            $query->whereHas('studentDetail', function($q) use ($batch) {
                $q->where('batch', $batch);
            });
        }

        $students = $query->get()
            ->map(function ($student) {
                $detail = $student->studentDetail;
                return [
                    'user_id' => $student->user_id,
                    'user_lname' => $student->user_lname,
                    'user_fname' => $student->user_fname,
                    'batch' => $detail->batch,
                    'group' => $detail->group,
                    'student_number' => $detail->student_number,
                    'training_code' => $detail->training_code
                ];
            });

        return response()->json($students);
    }

    public function getClassesBySchool(School $school)
    {
        $classes = $school->classes()->select('class_id', 'class_name', 'batch')->get();
        return response()->json($classes);
    }
}