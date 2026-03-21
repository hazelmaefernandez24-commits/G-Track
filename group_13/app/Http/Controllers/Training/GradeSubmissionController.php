<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionProof;
use App\Models\PNUser;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\GradeSubmissionNotification;

class GradeSubmissionController extends Controller
{
    public function index(Request $request)
    {
        // Get current page for school pagination
        $schoolPage = $request->get('school_page', 1);
        $schoolsPerPage = 1; // 1 school per page as requested

        // Get all schools for filtering dropdown (not paginated)
        $allSchools = School::orderBy('name')->get();

        // Get all classes grouped by school for filtering dropdown
        $allClassesBySchool = collect();
        foreach($allSchools as $school) {
            $allClassesBySchool[$school->school_id] = ClassModel::where('school_id', $school->school_id)->get();
        }

        // Apply filters to determine which schools to show
        // Start with only schools that have grade submissions
        $schoolsWithSubmissions = GradeSubmission::distinct('school_id')->pluck('school_id');
        $filteredSchoolIds = $schoolsWithSubmissions;

        // Apply school filter if selected
        if ($request->has('school_id') && $request->school_id) {
            // Only include the selected school if it has submissions
            if ($schoolsWithSubmissions->contains($request->school_id)) {
                $filteredSchoolIds = collect([$request->school_id]);
            } else {
                $filteredSchoolIds = collect(); // Empty if selected school has no submissions
            }
        }

        // Apply class filter if selected (this will determine which school to show)
        if ($request->has('class_id') && $request->class_id) {
            $classSchool = ClassModel::where('class_id', $request->class_id)->first();
            if ($classSchool && $schoolsWithSubmissions->contains($classSchool->school_id)) {
                $filteredSchoolIds = collect([$classSchool->school_id]);
            } else {
                $filteredSchoolIds = collect(); // Empty if class's school has no submissions
            }
        }

        // Get filtered schools for pagination
        $filteredSchools = $allSchools->whereIn('school_id', $filteredSchoolIds);
        $filteredSchoolsCount = $filteredSchools->count();

        // Apply pagination to filtered schools
        $schoolsOffset = ($schoolPage - 1) * $schoolsPerPage;
        $schools = $filteredSchools->skip($schoolsOffset)->take($schoolsPerPage);

        // Create school pagination info based on filtered results
        $schoolPagination = (object)[
            'current_page' => $schoolPage,
            'last_page' => ceil($filteredSchoolsCount / $schoolsPerPage),
            'per_page' => $schoolsPerPage,
            'total' => $filteredSchoolsCount,
            'from' => $schoolsOffset + 1,
            'to' => min($schoolsOffset + $schoolsPerPage, $filteredSchoolsCount),
            'has_pages' => $filteredSchoolsCount > $schoolsPerPage,
            'on_first_page' => $schoolPage == 1,
            'has_more_pages' => $schoolPage < ceil($filteredSchoolsCount / $schoolsPerPage)
        ];

        // Build query for grade submissions (only for current schools)
        $schoolIds = $schools->pluck('school_id')->toArray();
        $query = GradeSubmission::with(['school', 'classModel']);

        // Always filter by current schools
        if (!empty($schoolIds)) {
            $query->whereIn('school_id', $schoolIds);
        }

        // Apply additional filters
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        // Apply semester/term/year filter if selected
        if ($request->has('filter_key') && $request->filter_key) {
            $filter = explode(',', $request->filter_key);
            if (count($filter) === 3) {
                $query->where('semester', $filter[0])
                    ->where('term', $filter[1])
                    ->where('academic_year', $filter[2]);
            }
        }

        // Get submissions for filter options based on current school/class filters
        $submissionFilterQuery = GradeSubmission::with(['students', 'proofs', 'subjects']);

        // Apply school filter to submission options if selected
        if ($request->has('school_id') && $request->school_id) {
            $submissionFilterQuery->where('school_id', $request->school_id);
        }

        // Apply class filter to submission options if selected
        if ($request->has('class_id') && $request->class_id) {
            $submissionFilterQuery->where('class_id', $request->class_id);
        }

        // If no specific school/class filter, show submissions from filtered schools
        if (!$request->has('school_id') && !$request->has('class_id')) {
            $submissionFilterQuery->whereIn('school_id', $filteredSchoolIds);
        }

        $allSubmissions = $submissionFilterQuery->get();

        // Get submissions for current schools (no additional pagination since we're already limiting by school)
        $submissions = $query->with(['students', 'proofs', 'subjects'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group submissions by school
        $submissionsBySchool = $submissions->groupBy('school_id');

        // Add pagination info for students within each submission
        $submissionsWithPagination = collect();
        foreach ($submissionsBySchool as $schoolId => $schoolSubmissions) {
            $paginatedSubmissions = collect();
            foreach ($schoolSubmissions as $submission) {
                // Get current page for this submission
                $currentPage = $request->get('submission_' . $submission->id . '_page', 1);

                // Get students for this submission with pagination
                $studentsQuery = \DB::table('grade_submission_subject')
                    ->join('pnph_users', 'grade_submission_subject.user_id', '=', 'pnph_users.user_id')
                    ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                    ->where('grade_submission_subject.grade_submission_id', '=', $submission->id)
                    ->where('pnph_users.user_role', '=', 'student')
                    ->select('pnph_users.user_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.student_id')
                    ->distinct();

                // Manual pagination for students
                $perPage = 5;

                // Count unique students for this submission only
                $total = \DB::table('grade_submission_subject')
                    ->join('pnph_users', 'grade_submission_subject.user_id', '=', 'pnph_users.user_id')
                    ->where('grade_submission_subject.grade_submission_id', '=', $submission->id)
                    ->where('pnph_users.user_role', '=', 'student')
                    ->distinct('pnph_users.user_id')
                    ->count('pnph_users.user_id');

                // Debug: Log the count for this specific submission
                \Log::info("Submission {$submission->id} student count: {$total}");

                $students = $studentsQuery->skip(($currentPage - 1) * $perPage)
                    ->take($perPage)
                    ->get()
                    ->map(function ($student) {
                        return (object)[
                            'student_id' => $student->student_id,
                            'user_id' => $student->user_id,
                            'name' => $student->user_fname . ' ' . $student->user_lname
                        ];
                    });

                // Create pagination info
                $lastPage = ceil($total / $perPage);
                $submission->students_paginated = $students;
                $submission->students_pagination = (object)[
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'from' => ($currentPage - 1) * $perPage + 1,
                    'to' => min($currentPage * $perPage, $total),
                    'has_pages' => $lastPage > 1,
                    'on_first_page' => $currentPage == 1,
                    'has_more_pages' => $currentPage < $lastPage
                ];

                $paginatedSubmissions->push($submission);
            }
            $submissionsWithPagination[$schoolId] = $paginatedSubmissions;
        }

        $submissionsBySchool = $submissionsWithPagination;

        // Get unique filter options from filtered submissions
        $filterOptions = $allSubmissions->map(function($submission) {
            $termDisplay = ucfirst(str_replace('_', ' ', $submission->term));
            return [
                'value' => $submission->semester . ',' . $submission->term . ',' . $submission->academic_year,
                'display' => $submission->semester . ' ' . $termDisplay . ' ' . $submission->academic_year,
                'school_name' => $submission->school->name ?? 'Unknown School',
                'class_name' => $submission->classModel->class_name ?? 'Unknown Class'
            ];
        })->unique('value')->sortByDesc('value')->values();

        return view('training.grade-submissions.monitor', compact(
            'schools',                  // Current page schools (for display)
            'submissionsBySchool',
            'filterOptions',
            'schoolPagination'          // Pass school pagination info
        ))->with('title', 'Grade Submissions Monitor')
        ->with('allSchools', $allSchools)              // All schools for filtering dropdown
        ->with('allClassesBySchool', $allClassesBySchool)  // All classes for filtering dropdown
        ->with('filter_key', $request->filter_key)
        ->with('school_id', $request->school_id)
        ->with('class_id', $request->class_id);
    }

    public function create(Request $request)
    {
        $schools = School::all();
        $classes = [];
        $subjects = [];
    
        if ($request->has('school_id')) {
            $classes = ClassModel::where('school_id', $request->school_id)->get();
            $subjects = Subject::where('school_id', $request->school_id)->get();
        }
    
        return view('training.grade-submissions.create', compact('schools', 'classes', 'subjects'))->with('title', 'Create Grade Submission');
    }

    public function store(Request $request)
    {
        try {
            // Enhanced validation with custom messages
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,school_id',
                'class_id' => 'required|exists:classes,class_id',
                'semester' => 'required|string|max:50',
                'term' => 'required|string|in:prelim,midterm,semi_final,final',
                'academic_year' => 'required|string|max:20',
                'subject_ids' => 'required|array|min:1',
                'subject_ids.*' => 'exists:subjects,id',
            ], [
                'school_id.required' => 'Please select a school.',
                'school_id.exists' => 'The selected school does not exist.',
                'class_id.required' => 'Please select a class.',
                'class_id.exists' => 'The selected class does not exist.',
                'semester.required' => 'Please enter the semester.',
                'term.required' => 'Please select a term.',
                'term.in' => 'Please select a valid term (Prelim, Midterm, Semi Final, or Final).',
                'academic_year.required' => 'Please enter the academic year.',
                'subject_ids.required' => 'Please select at least one subject.',
                'subject_ids.min' => 'Please select at least one subject.',
                'subject_ids.*.exists' => 'One or more selected subjects do not exist.',
            ]);

            // Check for duplicate grade submission
            $existingSubmission = GradeSubmission::where([
                'school_id' => $validated['school_id'],
                'class_id' => $validated['class_id'],
                'semester' => $validated['semester'],
                'term' => $validated['term'],
                'academic_year' => $validated['academic_year'],
            ])->first();

            if ($existingSubmission) {
                return back()
                    ->withInput()
                    ->with('error', 'A grade submission already exists for this class, semester, term, and academic year combination.');
            }

            DB::beginTransaction();

            // Get class and school information for detailed messages
            $class = ClassModel::where('class_id', $validated['class_id'])->first();
            if (!$class) {
                throw new \Exception('The selected class could not be found. Please refresh the page and try again.');
            }

            $school = School::where('school_id', $validated['school_id'])->first();
            if (!$school) {
                throw new \Exception('The selected school could not be found. Please refresh the page and try again.');
            }

            $students = $class->students()->where('user_role', 'student')->get();
            if ($students->isEmpty()) {
                throw new \Exception("No students are enrolled in class '{$class->class_name}'. Please add students to the class before creating a grade submission.");
            }

            // Create the grade submission
            $gradeSubmission = new GradeSubmission();
            $gradeSubmission->school_id = $validated['school_id'];
            $gradeSubmission->class_id = $validated['class_id'];
            $gradeSubmission->semester = $validated['semester'];
            $gradeSubmission->term = $validated['term'];
            $gradeSubmission->academic_year = $validated['academic_year'];
            $gradeSubmission->status = 'pending';
            $gradeSubmission->save();

            // Initialize grade records for each student-subject combination
            $gradeRecords = [];
            foreach ($students as $student) {
                foreach ($validated['subject_ids'] as $subjectId) {
                    $gradeRecords[] = [
                        'grade_submission_id' => $gradeSubmission->id,
                        'subject_id' => $subjectId,
                        'user_id' => $student->user_id,
                        'status' => 'pending',
                        'grade' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            // Insert all grade records in a single query
            DB::table('grade_submission_subject')->insert($gradeRecords);

            // Attach subjects to the grade submission
            // $gradeSubmission->subjects()->attach($validated['subject_ids']); // Removed as redundant

            DB::commit();

            \Log::info('Grade submission created successfully:', [
                'submission_id' => $gradeSubmission->id,
                'students_count' => $students->count(),
                'subjects_count' => count($validated['subject_ids']),
                'grade_records_count' => count($gradeRecords)
            ]);

            // Send email notifications to students
            try {
                // Load the grade submission with relationships for email
                $gradeSubmission->load(['school', 'classModel', 'subjects']);

                foreach ($students as $student) {
                    // Check if student has a valid email
                    if (!empty($student->user_email) && filter_var($student->user_email, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($student->user_email)->send(new GradeSubmissionNotification($student, $gradeSubmission));

                        \Log::info('Email notification sent:', [
                            'student_id' => $student->user_id,
                            'student_email' => $student->user_email,
                            'submission_id' => $gradeSubmission->id
                        ]);
                    } else {
                        \Log::warning('Invalid email for student:', [
                            'student_id' => $student->user_id,
                            'student_email' => $student->user_email ?? 'null'
                        ]);
                    }
                }

                \Log::info('Email notifications process completed for submission:', [
                    'submission_id' => $gradeSubmission->id,
                    'total_students' => $students->count()
                ]);

            } catch (\Exception $emailException) {
                // Log email errors but don't fail the grade submission creation
                \Log::error('Email notification error:', [
                    'submission_id' => $gradeSubmission->id,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
            }

            // Create detailed success message
            $subjectCount = count($validated['subject_ids']);
            $studentCount = $students->count();
            $termDisplay = ucfirst(str_replace('_', ' ', $validated['term']));

            $successMessage = "Grade submission created successfully! ";
            $successMessage .= "Created for {$school->name} - {$class->class_name} ";
            $successMessage .= "({$validated['semester']} {$termDisplay} {$validated['academic_year']}) ";
            $successMessage .= "with {$subjectCount} subject(s) and {$studentCount} student(s). ";
            $successMessage .= "Students have been notified via email.";

            return redirect()
                ->route('training.grade-submissions.index')
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            // Log validation errors for debugging
            \Log::warning('Grade Submission Validation Error', [
                'errors' => $e->errors(),
                'input' => $request->except(['_token'])
            ]);

            return back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please correct the errors below and try again.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Log detailed error information
            \Log::error('Grade Submission Creation Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'input' => $request->except(['_token']),
                'trace' => $e->getTraceAsString()
            ]);

            // User-friendly error message
            $errorMessage = 'An error occurred while creating the grade submission. ';

            if (str_contains($e->getMessage(), 'students')) {
                $errorMessage .= 'There was an issue with the student enrollment. Please check that students are properly enrolled in the selected class.';
            } elseif (str_contains($e->getMessage(), 'subject')) {
                $errorMessage .= 'There was an issue with the selected subjects. Please refresh the page and try again.';
            } elseif (str_contains($e->getMessage(), 'email')) {
                $errorMessage .= 'The submission was created but there was an issue sending notifications. Students may not have been notified.';
            } else {
                $errorMessage .= 'Please try again. If the problem persists, contact the system administrator.';
            }

            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function show(GradeSubmission $gradeSubmission)
    {
        $gradeSubmission->load(['school', 'classModel', 'subjects', 'students']);
        return view('training.grade-submissions.show', compact('gradeSubmission'));
    }

    public function monitor(GradeSubmission $gradeSubmission)
    {
        // Debug: Log the grade submission details
        \Log::info('Grade Submission Details:', [
            'id' => $gradeSubmission->id,
            'school_id' => $gradeSubmission->school_id,
            'class_id' => $gradeSubmission->class_id
        ]);

        // Load the students and subjects for this grade submission
        $students = $gradeSubmission->students()
            ->where('user_role', 'student')
            ->get();
            
        $subjects = $gradeSubmission->subjects()->get();

        // Debug: Log students and subjects
        \Log::info('Students and Subjects:', [
            'students_count' => $students->count(),
            'subjects_count' => $subjects->count(),
            'student_ids' => $students->pluck('user_id'),
            'subject_ids' => $subjects->pluck('id')
        ]);

        // Get all grades for this submission with proper joins
        $rawGrades = DB::table('grade_submission_subject')
            ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
            ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
            ->select(
                'grade_submission_subject.user_id',
                'grade_submission_subject.subject_id',
                'grade_submission_subject.grade',
                'grade_submission_subject.status',
                'subjects.name as subject_name'
            )
            ->get();

        // Debug: Log raw grades
        \Log::info('Raw Grades from Database:', [
            'count' => $rawGrades->count(),
            'data' => $rawGrades->toArray()
        ]);

        // Organize grades by user_id and subject_id
        $grades = [];
        foreach ($rawGrades as $grade) {
            if (!isset($grades[$grade->user_id])) {
                $grades[$grade->user_id] = [];
            }
            $grades[$grade->user_id][$grade->subject_id] = (object)[
                'grade' => $grade->grade,
                'status' => $grade->status,
                'subject_name' => $grade->subject_name
            ];
        }

        // Debug: Log processed grades
        \Log::info('Processed Grades:', [
            'count' => count($grades),
            'data' => $grades
        ]);

        // If no subjects found, try to load them from the class
        if ($subjects->isEmpty()) {
            \Log::info('No subjects found via grade_submission_subject. Attempting to load from class.');
            $class = $gradeSubmission->classModel;
            if ($class) {
                \Log::info('Class model loaded for fallback.', ['class_id' => $class->class_id]);
                $subjects = $class->subjects()->get();
                \Log::info('Loaded subjects from class:', [
                    'class_id' => $class->class_id,
                    'subjects_count' => $subjects->count(),
                    'subject_ids' => $subjects->pluck('id')
                ]);
            } else {
                \Log::info('Class model not loaded for fallback.');
            }
        }

        // Load proofs for debugging
        $proofs = $gradeSubmission->proofs()->get();
        \Log::info('Loaded proofs:', [
            'count' => $proofs->count(),
            'data' => $proofs->toArray()
        ]);

        return view('training.grade-submissions.monitor', compact(
            'gradeSubmission',
            'students',
            'subjects',
            'grades'
        ));
    }

    public function recent()
    {
        $recentSubmissions = GradeSubmission::with(['school', 'classModel', 'students'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'school_name' => $submission->school->name ?? 'N/A',
                    'class_name' => $submission->classModel->class_name ?? 'N/A',
                    'semester' => $submission->semester,
                    'term' => $submission->term,
                    'academic_year' => $submission->academic_year,
                    'status' => $submission->status,
                    'created_at' => $submission->created_at->format('M d, Y h:i A'),
                    'total_students' => $submission->students->count(),
                    'submitted_count' => $submission->students->where('pivot.status', 'submitted')->count(),
                    'approved_count' => $submission->students->where('pivot.status', 'approved')->count(),
                    'pending_count' => $submission->students->where('pivot.status', 'pending')->count(),
                    'rejected_count' => $submission->students->where('pivot.status', 'rejected')->count()
                ];
            });

        return view('training.grade-submissions.recent', compact('recentSubmissions'))->with('title', 'Recent Grade Submissions');
    }

    public function viewStudentSubmission(GradeSubmission $gradeSubmission, User $student)
    {
        $submissions = $gradeSubmission->students()
            ->where('user_id', $student->user_id)
            ->with('subjects')
            ->get();

        $studentGrades = DB::table('grade_submission_subject')
            ->where('grade_submission_id', $gradeSubmission->id)
            ->where('user_id', $student->user_id)
            ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
            ->select('subjects.name as subject_name', 'grade_submission_subject.grade', 'grade_submission_subject.status')
            ->get();

        return view('training.grade-submissions.view-student', compact('gradeSubmission', 'student', 'studentGrades'));
    }

    public function destroy(GradeSubmission $gradeSubmission)
    {
        try {
            // Get all proofs before deleting
            $proofs = $gradeSubmission->proofs()->get();
            
            // Delete the grade submission (this will trigger cascading deletes for related records)
            $gradeSubmission->delete();
            
            // Delete the proof files from storage
            foreach ($proofs as $proof) {
                // Delete the file if it exists
                if (Storage::disk('public')->exists($proof->file_path)) {
                    Storage::disk('public')->delete($proof->file_path);
                }
                
                // Also try to delete the student's folder if it's empty
                $folderPath = dirname($proof->file_path);
                if (Storage::disk('public')->exists($folderPath)) {
                    // If folder is empty, delete it
                    if (count(Storage::disk('public')->files($folderPath)) === 0) {
                        Storage::disk('public')->deleteDirectory($folderPath);
                    }
                }
            }
            
            return redirect()->route('training.grade-submissions.recent')
                ->with('success', 'Grade submission and all related data deleted successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting grade submission: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the grade submission: ' . $e->getMessage());
        }
    }

    public function getSubjectsBySchoolAndClass(Request $request)
    {
        $schoolId = $request->query('school_id');
        $classId = $request->query('class_id');

        $query = Subject::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($classId) {
            $query->whereHas('classes', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $subjects = $query->select('id', 'name', 'offer_code')->get();

        return response()->json($subjects);
    }

    public function updateStatus(Request $request, GradeSubmission $gradeSubmission)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'student_id' => 'required|exists:pnph_users,user_id',
        ]);

        try {
            $student = $gradeSubmission->students()->where('user_id', $request->student_id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not associated with this grade submission.'], 404);
            }

            // Update the pivot table
            DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->where('user_id', $request->student_id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            // Update the main grade_submissions table status
            $gradeSubmission->status = $request->status;
            $gradeSubmission->save();

            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating grade submission status:' . $e->getMessage());
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }

    public function verify(GradeSubmission $gradeSubmission)
    {
        try {
            // Get all students in the class
            $class = $gradeSubmission->classModel;
            if (!$class) {
                return back()->with('error', 'Class not found for this submission.');
            }
            
            $totalStudents = $class->students()->where('user_role', 'student')->count();
            
            // Get count of students who have submitted all their grades
            $studentsWithCompleteGrades = DB::table('grade_submission_subject')
                ->select('user_id')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->whereNotNull('grade')
                ->groupBy('user_id')
                ->havingRaw('COUNT(DISTINCT subject_id) = ?', [
                    $gradeSubmission->subjects()->count()
                ])
                ->count();

            // Check if all students have submitted all their grades
            if ($studentsWithCompleteGrades < $totalStudents) {
                $missing = $totalStudents - $studentsWithCompleteGrades;
                return back()->with('error', "Cannot approve submission. $missing students have not submitted all their grades yet.");
            }

            // Check for any null grades (shouldn't happen if above check passes, but just to be safe)
            $incompleteGrades = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->whereNull('grade')
                ->exists();

            if ($incompleteGrades) {
                return back()->with('error', 'Cannot approve submission. Some grades are still missing.');
            }

            // Only approve if all grades are uploaded
            $gradeSubmission->update(['status' => 'approved']);

            // Update all related records
            DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->update([
                    'status' => 'approved', 
                    'updated_at' => now()
                ]);

            return redirect()->route('training.grade-submissions.index')
                ->with('success', 'Grade submission verified and approved!');
        } catch (\Exception $e) {
            \Log::error('Error verifying grade submission: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while verifying the grade submission: ' . $e->getMessage());
        }
    }

    public function reject(GradeSubmission $gradeSubmission)
    {
        try {
            $gradeSubmission->update(['status' => 'rejected']);

            DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->update(['status' => 'rejected', 'updated_at' => now()]);

            return redirect()->route('training.grade-submissions.index')
                ->with('success', 'Grade submission rejected!');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while rejecting the grade submission.');
        }
    }

    public function uploadProof(Request $request, GradeSubmission $gradeSubmission, $studentId)
    {
        try {
            $request->validate([
                'proof' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max
            ]);

            $file = $request->file('proof');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Get student details for folder name
            $student = PNUser::with('studentDetail')->findOrFail($studentId);
            $studentId = $student->studentDetail->student_id ?? $student->user_id; // Fallback to user_id if student_id is not available
            $folderName = $student->user_lname . '_' . $studentId;
            $folderName = preg_replace('/[^a-zA-Z0-9_]/', '_', $folderName); // Sanitize folder name
            
            $filePath = $file->storeAs("proofs/{$folderName}", $fileName, 'public');

            // Create or update the proof record
            $proof = GradeSubmissionProof::updateOrCreate(
                [
                    'grade_submission_id' => $gradeSubmission->id,
                    'user_id' => $studentId
                ],
                [
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension()
                ]
            );

            return back()->with('success', 'Proof uploaded successfully!');
        } catch (\Exception $e) {
            \Log::error('Error uploading proof: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while uploading the proof.');
        }
    }

    public function viewProof(GradeSubmission $gradeSubmission, $studentId)
    {
        $proof = $gradeSubmission->proofs()
            ->where('user_id', $studentId)
            ->first();

        if (!$proof) {
            return back()->with('error', 'No proof found for this student.');
        }

        // Get the student's name
        $student = PNUser::where('user_id', $studentId)->first();
        if (!$student) {
            return back()->with('error', 'Student not found.');
        }

        return view('training.grade-submissions.view-proof', compact('gradeSubmission', 'student', 'proof'));
    }

    public function updateProofStatus(Request $request, GradeSubmission $gradeSubmission, $studentId)
    {
        // Enhanced validation with custom messages
        $request->validate([
            'status' => 'required|in:approved,rejected,pending'
        ], [
            'status.required' => 'Please select a status.',
            'status.in' => 'Please select a valid status (Approved, Rejected, or Pending).'
        ]);

        try {
            // Get student information for detailed messages
            $student = PNUser::where('user_id', $studentId)->first();
            if (!$student) {
                return back()->with('error', 'Student not found. Please refresh the page and try again.');
            }

            $proof = $gradeSubmission->proofs()
                ->where('user_id', $studentId)
                ->first();

            if (!$proof) {
                return back()->with('error', "No proof submission found for {$student->user_fname} {$student->user_lname}. The student may not have submitted their grades yet.");
            }

            // Check if status is already the same
            if ($proof->status === $request->status) {
                $statusDisplay = ucfirst($request->status);
                return back()->with('warning', "The status for {$student->user_fname} {$student->user_lname} is already set to '{$statusDisplay}'.");
            }

            // First, get the grade submission subjects to update
            $subjects = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->where('user_id', $studentId)
                ->get();
                
            // Log before update
            \Log::info('Before update', [
                'subjects' => $subjects,
                'new_status' => $request->status
            ]);
                
            // Update status and student_status
            $updated = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->where('user_id', $studentId)
                ->update([
                    'status' => $request->status,
                    'student_status' => $request->status,
                    'updated_at' => now()
                ]);
                
            // Log the update for debugging
            \Log::info('Updated grade status', [
                'grade_submission_id' => $gradeSubmission->id,
                'student_id' => $studentId,
                'status' => $request->status,
                'updated_rows' => $updated,
                'sql' => DB::getQueryLog()
            ]);
            
            // Verify the update
            $updatedSubjects = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->where('user_id', $studentId)
                ->get();
                
            \Log::info('After update', ['subjects' => $updatedSubjects]);

            // Update proof status
            $proof->update([
                'status' => $request->status
            ]);

            // Log the update
            \Log::info('Student grade status updated:', [
                'proof_id' => $proof->id,
                'student_id' => $studentId,
                'student_name' => $student->user_fname . ' ' . $student->user_lname,
                'old_status' => $proof->status,
                'new_status' => $request->status,
                'updated_rows' => $updated,
                'submission_id' => $gradeSubmission->id
            ]);

            // Create detailed success message based on action
            $statusDisplay = ucfirst($request->status);
            $studentName = $student->user_fname . ' ' . $student->user_lname;

            switch ($request->status) {
                case 'approved':
                    $message = "✅ {$studentName}'s grade submission has been approved successfully. The student can now view their approved grades.";
                    break;
                case 'rejected':
                    $message = "❌ {$studentName}'s grade submission has been rejected. The student will need to resubmit their grades.";
                    break;
                case 'pending':
                    $message = "⏳ {$studentName}'s grade submission status has been reset to pending for review.";
                    break;
                default:
                    $message = "Status for {$studentName} has been updated to {$statusDisplay}.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            // Log detailed error information
            \Log::error('Error updating proof status', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'student_id' => $studentId,
                'submission_id' => $gradeSubmission->id,
                'requested_status' => $request->status,
                'trace' => $e->getTraceAsString()
            ]);

            $studentName = $student->user_fname ?? 'Student';
            $errorMessage = "An error occurred while updating {$studentName}'s grade status. ";

            if (str_contains($e->getMessage(), 'database') || str_contains($e->getMessage(), 'connection')) {
                $errorMessage .= 'There was a database connection issue. Please try again.';
            } elseif (str_contains($e->getMessage(), 'permission')) {
                $errorMessage .= 'You do not have permission to perform this action.';
            } else {
                $errorMessage .= 'Please try again. If the problem persists, contact the system administrator.';
            }

            return back()->with('error', $errorMessage);
        }
    }

    // Temporary method to fix subject associations for a grade submission
    public function fixSubmissionSubjects(GradeSubmission $gradeSubmission)
    {
        try {
            DB::beginTransaction();

            // Get the class associated with the submission
            $class = $gradeSubmission->classModel;
            if (!$class) {
                DB::rollBack();
                return back()->with('error', 'Class not found for this grade submission.');
            }

            // Get subjects associated with the class
            $subjects = $class->subjects()->get();
            if ($subjects->isEmpty()) {
                 // If no subjects in class, try subjects linked to submission (might be the issue)
                 $subjects = $gradeSubmission->subjects()->get();
                 if($subjects->isEmpty()) {
                     DB::rollBack();
                     return back()->with('error', 'No subjects found for the associated class or submission.');
                 }
            }

            // Get students associated with the class
            $students = $class->students()->where('user_role', 'student')->get();
            if ($students->isEmpty()) {
                DB::rollBack();
                return back()->with('error', 'No students found in the associated class.');
            }

            // Safely delete existing entries for this submission in the pivot table
            DB::table('grade_submission_subject')
                ->where('grade_submission_id', $gradeSubmission->id)
                ->delete();

            // Re-initialize and insert grade records for each student-subject combination
            $gradeRecords = [];
            foreach ($students as $student) {
                foreach ($subjects as $subject) {
                    $gradeRecords[] = [
                        'grade_submission_id' => $gradeSubmission->id,
                        'subject_id' => $subject->id,
                        'user_id' => $student->user_id,
                        'status' => 'pending', // Reset status to pending
                        'grade' => null, // Reset grade to null
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            // Insert all grade records in a single query
            if (!empty($gradeRecords)) {
                 DB::table('grade_submission_subject')->insert($gradeRecords);
            }

            DB::commit();

            return back()->with('success', 'Subject associations fixed successfully for this grade submission.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error fixing subject associations: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fixing subject associations: ' . $e->getMessage());
        }
    }
} 