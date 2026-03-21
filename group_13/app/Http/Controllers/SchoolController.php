<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\StudentDetail;
use App\Models\PNUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('subjects')->paginate(10);
        return view('training.manage-students', compact('schools'))->with('title', 'Manage Students - Schools');
    }

    public function show(School $school)
    {
        $school->load('subjects');
        $classes = ClassModel::where('school_id', $school->school_id)
            ->with('students')
            ->get();
            
        // Ensure terms is an array
        if (is_string($school->terms)) {
            $school->terms = json_decode($school->terms, true) ?? [];
        } elseif (is_null($school->terms)) {
            $school->terms = [];
        }
            
        return view('training.schools.show', compact('school', 'classes'))->with('title', 'School Details');
    }

    public function create()
    {
        $batches = StudentDetail::select('batch')->distinct()->orderBy('batch')->get();
        return view('training.schools.create', compact('batches'))->with('title', 'Create School');
    }

    public function store(Request $request)
    {
        try {
            // Debug the request data
            \Log::info('Form data:', $request->all());
            // Validate the request data
            $validator = \Validator::make($request->all(), [
                'school_id' => 'required|string|unique:schools,school_id',
                'name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'course' => 'required|string|max:255',
                'semester_count' => 'required|integer|min:1',
                'passing_grade_min' => 'required|numeric|between:1,5',
                'passing_grade_max' => 'required|numeric|between:1,5|gte:passing_grade_min',
                'failing_grade_min' => 'required|numeric|between:1,5',
                'failing_grade_max' => 'required|numeric|between:1,5|gte:failing_grade_min',
                'subjects' => 'required|array|min:1',
                'subjects.*.offer_code' => 'required|string',
                'subjects.*.name' => 'required|string',
                'subjects.*.instructor' => 'required|string',
                'subjects.*.schedule' => 'required|string',
                'classes' => 'required|array|min:1',
                'classes.*.class_id' => 'required|string|unique:classes,class_id',
                'classes.*.name' => 'required|string',
                'classes.*.student_ids' => 'required|array|min:1',
                'classes.*.student_ids.*' => 'exists:pnph_users,user_id',
                'terms' => 'required|array|min:1',
                'terms.*' => 'in:prelim,midterm,semi_final,final',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            
            // Debug the validated data
            \Log::info('Validated data:', $validated);
            
            // Start database transaction
            \DB::beginTransaction();

            // Create school
            $school = School::create([
                'school_id' => $validated['school_id'],
                'name' => $validated['name'],
                'department' => $validated['department'],
                'course' => $validated['course'],
                'semester_count' => $validated['semester_count'],
                'passing_grade_min' => $validated['passing_grade_min'],
                'passing_grade_max' => $validated['passing_grade_max'],
                'failing_grade_min' => $validated['failing_grade_min'],
                'failing_grade_max' => $validated['failing_grade_max'],
                'terms' => $validated['terms'],
            ]);
            
            // Add subjects
            foreach ($validated['subjects'] as $subjectData) {
                $school->subjects()->create([
                    'offer_code' => $subjectData['offer_code'],
                    'name' => $subjectData['name'],
                    'instructor' => $subjectData['instructor'],
                    'schedule' => $subjectData['schedule'],
                ]);
            }
            
            // Check for student conflicts across all classes before creating any
            $allStudentIds = [];
            $studentConflicts = [];

            foreach ($validated['classes'] as $classIndex => $classData) {
                if (!empty($classData['student_ids'])) {
                    foreach ($classData['student_ids'] as $studentId) {
                        if (in_array($studentId, $allStudentIds)) {
                            // Find which class this student was already assigned to
                            foreach ($validated['classes'] as $prevIndex => $prevClass) {
                                if ($prevIndex < $classIndex && !empty($prevClass['student_ids']) && in_array($studentId, $prevClass['student_ids'])) {
                                    $student = \App\Models\PNUser::where('user_id', $studentId)->first();
                                    $studentConflicts[] = "{$student->user_fname} {$student->user_lname} ({$studentId}) cannot be assigned to both '{$prevClass['name']}' and '{$classData['name']}'";
                                    break;
                                }
                            }
                        } else {
                            $allStudentIds[] = $studentId;
                        }
                    }
                }
            }

            // Check if any students are already enrolled in existing classes
            if (!empty($allStudentIds)) {
                $studentsAlreadyInClasses = DB::table('class_student')
                    ->join('classes', 'class_student.class_id', '=', 'classes.id')
                    ->join('pnph_users', 'class_student.user_id', '=', 'pnph_users.user_id')
                    ->whereIn('class_student.user_id', $allStudentIds)
                    ->select(
                        'pnph_users.user_fname',
                        'pnph_users.user_lname',
                        'pnph_users.user_id',
                        'classes.class_name',
                        'classes.class_id'
                    )
                    ->get();

                foreach ($studentsAlreadyInClasses as $student) {
                    $studentConflicts[] = "{$student->user_fname} {$student->user_lname} ({$student->user_id}) is already enrolled in class '{$student->class_name}' ({$student->class_id})";
                }
            }

            // If there are conflicts, return with error
            if (!empty($studentConflicts)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot create school due to student enrollment conflicts:',
                        'conflicts' => $studentConflicts
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->with('error', 'Cannot create school due to student enrollment conflicts:')
                    ->with('student_conflicts', $studentConflicts);
            }

            // Add classes and students (only if no conflicts)
            foreach ($validated['classes'] as $classData) {
                $class = $school->classes()->create([
                    'class_id' => $classData['class_id'],
                    'class_name' => $classData['name'],
                    'batch' => $classData['batch'] ?? ''
                ]);

                // Attach students to the class
                if (!empty($classData['student_ids'])) {
                    $class->students()->sync($classData['student_ids']);
                }
            }
            
            // Commit the transaction
            \DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'School created successfully!',
                    'redirect' => route('training.manage-students')
                ]);
            }
            
            return redirect()->route('training.manage-students')
                ->with('success', 'School created successfully!');
                
        } catch (\Exception $e) {
            // Rollback the transaction on error
            $pdo = \DB::getPdo();
            if ($pdo && $pdo->inTransaction()) {
                \DB::rollBack();
            }
            
            // Log the error
            \Log::error('Error creating school: ' . $e->getMessage());
            \Log::error('Exception: ' . get_class($e));
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            // Handle specific exceptions
            $errorMessage = 'An error occurred while creating the school. Please try again.';
            
            if ($e instanceof \Illuminate\Database\QueryException) {
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    if (str_contains($e->getMessage(), 'schools_school_id_unique')) {
                        $errorMessage = 'A school with this ID already exists. Please choose a different school ID.';
                    } elseif (str_contains($e->getMessage(), 'classes_class_id_unique')) {
                        $errorMessage = 'One of the class IDs you entered is already in use. Please use unique class IDs.';
                    }
                }
            }
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()->withInput()->with('error', $errorMessage);
        }
    }

    public function edit(School $school)
    {
        $school->load(['subjects', 'classes.students']);
        $batches = StudentDetail::select('batch')->distinct()->orderBy('batch')->get();
        $existingClasses = $school->classes; // Get existing classes
        return view('training.schools.edit', compact('school', 'batches', 'existingClasses'))->with('title', 'Edit School');
    }

    public function update(Request $request, School $school)
    {
        try {
            \Log::info('Starting school update process');
            \Log::info('Request method: ' . $request->method());
            \Log::info('Request URL: ' . $request->url());
            \Log::info('Request data:', $request->all());
            \Log::info('School being updated:', ['school_id' => $school->school_id]);

            // Basic validation
            $validated = $request->validate([
                'school_id' => 'required|string|exists:schools,school_id',
                'name' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'course' => 'required|string|max:255',
                'semester_count' => 'required|integer|min:1',
                'terms' => 'required|array|min:1',
                'passing_grade_min' => 'required|numeric|between:1,5',
                'passing_grade_max' => 'required|numeric|between:1,5|gte:passing_grade_min',
                'failing_grade_min' => 'required|numeric|between:1,5',
                'failing_grade_max' => 'required|numeric|between:1,5|gte:failing_grade_min',
                'subjects' => 'required|array|min:1',
                'subjects.*.offer_code' => 'required|string',
                'subjects.*.name' => 'required|string',
                'subjects.*.instructor' => 'required|string',
                'subjects.*.schedule' => 'required|string',
                'new_classes' => 'sometimes|array',
                'new_classes.*.class_id' => 'required_with:new_classes|string|unique:classes,class_id',
                'new_classes.*.name' => 'required_with:new_classes|string',
                'new_classes.*.students' => 'array',
                'new_classes.*.students.*' => 'exists:pnph_users,user_id',
            ]);

            \Log::info('Validation passed');

            DB::beginTransaction();

            try {
                // Update school basic info
                $school->update([
                    'name' => $validated['name'],
                    'department' => $validated['department'],
                    'course' => $validated['course'],
                    'semester_count' => $validated['semester_count'],
                    'terms' => $validated['terms'],
                    'passing_grade_min' => $validated['passing_grade_min'],
                    'passing_grade_max' => $validated['passing_grade_max'],
                    'failing_grade_min' => $validated['failing_grade_min'],
                    'failing_grade_max' => $validated['failing_grade_max'],
                ]);

                \Log::info('School basic info updated');

                // Handle subjects
                $existingSubjectIds = $school->subjects()->pluck('id')->toArray();
                $newSubjectIds = [];
                $submittedOfferCodes = [];
                
                // Update or create subjects
                foreach ($validated['subjects'] as $subjectData) {
                    $subject = $school->subjects()->updateOrCreate(
                        ['offer_code' => $subjectData['offer_code']],
                        [
                            'name' => $subjectData['name'],
                            'instructor' => $subjectData['instructor'],
                            'schedule' => $subjectData['schedule']
                        ]
                    );
                    $newSubjectIds[] = $subject->id;
                    $submittedOfferCodes[] = $subjectData['offer_code'];
                }
                
                // Delete subjects that were not in the submitted list
                $school->subjects()
                    ->whereNotIn('offer_code', $submittedOfferCodes)
                    ->delete();
                    
                \Log::info('Subjects updated and old subjects removed');

                // Handle new classes with student conflict validation
                if (isset($validated['new_classes'])) {
                    // Check for student conflicts in new classes
                    $allNewStudentIds = [];
                    $studentConflicts = [];

                    foreach ($validated['new_classes'] as $classIndex => $classData) {
                        if (empty($classData['name'])) {
                            \Log::warning('Skipping class due to missing name', $classData);
                            continue;
                        }

                        $studentIds = isset($classData['students']) && is_array($classData['students']) ? $classData['students'] : [];

                        if (!empty($studentIds)) {
                            foreach ($studentIds as $studentId) {
                                if (in_array($studentId, $allNewStudentIds)) {
                                    // Find which class this student was already assigned to
                                    foreach ($validated['new_classes'] as $prevIndex => $prevClass) {
                                        if ($prevIndex < $classIndex && !empty($prevClass['students']) && in_array($studentId, $prevClass['students'])) {
                                            $student = \App\Models\PNUser::where('user_id', $studentId)->first();
                                            $studentConflicts[] = "{$student->user_fname} {$student->user_lname} ({$studentId}) cannot be assigned to both '{$prevClass['name']}' and '{$classData['name']}'";
                                            break;
                                        }
                                    }
                                } else {
                                    $allNewStudentIds[] = $studentId;
                                }
                            }
                        }
                    }

                    // Check if any students are already enrolled in existing classes
                    if (!empty($allNewStudentIds)) {
                        $studentsAlreadyInClasses = DB::table('class_student')
                            ->join('classes', 'class_student.class_id', '=', 'classes.id')
                            ->join('pnph_users', 'class_student.user_id', '=', 'pnph_users.user_id')
                            ->whereIn('class_student.user_id', $allNewStudentIds)
                            ->select(
                                'pnph_users.user_fname',
                                'pnph_users.user_lname',
                                'pnph_users.user_id',
                                'classes.class_name',
                                'classes.class_id'
                            )
                            ->get();

                        foreach ($studentsAlreadyInClasses as $student) {
                            $studentConflicts[] = "{$student->user_fname} {$student->user_lname} ({$student->user_id}) is already enrolled in class '{$student->class_name}' ({$student->class_id})";
                        }
                    }

                    // If there are conflicts, return with error
                    if (!empty($studentConflicts)) {
                        return back()
                            ->withInput()
                            ->with('error', 'Cannot add new classes due to student enrollment conflicts:')
                            ->with('student_conflicts', $studentConflicts);
                    }

                    // Create new classes only if no conflicts
                    foreach ($validated['new_classes'] as $classData) {
                        if (empty($classData['name'])) {
                            continue;
                        }
                        $class = ClassModel::create([
                            'class_id' => $classData['class_id'],
                            'class_name' => $classData['name'],
                            'school_id' => $school->school_id,
                            'batch' => $classData['batch'] ?? ''
                        ]);
                        $studentIds = isset($classData['students']) && is_array($classData['students']) ? $classData['students'] : [];
                        $class->students()->sync($studentIds);
                    }
                    \Log::info('New classes added');
                }

                DB::commit();
                \Log::info('Update completed successfully');

                $successMessage = 'School information and subjects have been updated successfully.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $successMessage,
                        'redirect' => route('training.manage-students')
                    ]);
                }

                return redirect()->route('training.manage-students')
                    ->with('success', $successMessage);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error updating school: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error updating school: ' . $e->getMessage()
                    ], 500);
                }
                
                return back()
                    ->with('error', 'Error updating school: ' . $e->getMessage())
                    ->withInput();
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error:', $e->errors());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating school: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Request data: ' . json_encode($request->all()));
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating school: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->with('error', 'Error updating school: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(School $school)
    {
        try {
            DB::beginTransaction();
            $school->delete();
            DB::commit();

            return redirect()->route('training.manage-students')
                ->with('success', 'School deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting school: ' . $e->getMessage());
        }
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
                    'batch' => $detail ? $detail->batch : null,
                    'group' => $detail ? $detail->group : null,
                    'student_number' => $detail ? $detail->student_number : null,
                    'training_code' => $detail ? $detail->training_code : null
                ];
            });

        return response()->json($students);
    }
}