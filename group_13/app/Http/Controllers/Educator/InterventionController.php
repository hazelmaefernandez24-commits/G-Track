<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Models\PNUser;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Intervention;
use App\Models\Subject;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterventionController extends Controller
{
    /**
     * Display the intervention management page
     */
    public function index(Request $request)
    {
        try {
            // First, automatically create/update intervention records based on current grade data
            $this->createInterventionsFromGradeData();

            // Get all interventions with relationships
            $query = Intervention::with([
                'subject',
                'school',
                'classModel',
                'gradeSubmission',
                'educatorAssigned'
            ]);

            // Apply filters if provided
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('submission_id')) {
                $query->where('grade_submission_id', $request->submission_id);
            }

            // Get interventions ordered by most recent with pagination
            $interventions = $query->orderBy('created_at', 'desc')->paginate(5);

            // Append query parameters to pagination links
            $interventions->appends(request()->query());

            // Get filter options
            $schools = \App\Models\School::orderBy('name')->get();
            $classes = \App\Models\ClassModel::orderBy('class_name')->get();
            $subjects = \App\Models\Subject::orderBy('name')->get();

            // Get submissions for dropdown
            $submissions = GradeSubmission::with(['school', 'classModel'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($submission) {
                    return [
                        'id' => $submission->id,
                        'display_name' => $submission->semester . ' - ' . $submission->term . ' (' . $submission->academic_year . ')',
                        'school_name' => $submission->school->name ?? 'Unknown School',
                        'class_name' => $submission->classModel->class_name ?? 'Unknown Class',
                        'semester' => $submission->semester,
                        'term' => $submission->term,
                        'academic_year' => $submission->academic_year
                    ];
                });

            return view('educator.intervention', compact(
                'interventions',
                'schools',
                'classes',
                'subjects',
                'submissions'
            ))->with('title', 'Intervention Status');

        } catch (\Exception $e) {
            Log::error('Educator Intervention Index Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return view('educator.intervention', [
                'title' => 'Intervention Status',
                'interventions' => collect()->paginate(5),
                'schools' => \App\Models\School::all(),
                'classes' => collect(),
                'subjects' => \App\Models\Subject::all(),
                'submissions' => collect()
            ])->withErrors(['error' => 'Failed to load intervention data']);
        }
    }

    /**
     * Automatically create/update intervention records based on grade data analysis
     * Uses the same logic as Subject Intervention Analytics to include all subjects that need intervention
     */
    private function createInterventionsFromGradeData()
    {
        try {
            // Get all grade submissions (not just approved ones)
            $gradeSubmissions = GradeSubmission::with(['school', 'classModel', 'subjects'])
                ->whereIn('status', ['pending', 'submitted', 'approved'])
                ->get();

            foreach ($gradeSubmissions as $submission) {
                $school = $submission->school;

                if (!$school) continue;

                // Get the school's grading criteria
                $passingGradeMin = $school->passing_grade_min ?? 1.0;
                $passingGradeMax = $school->passing_grade_max ?? 3.0;

                // Get all subjects for this class
                $classSubjects = $submission->subjects;

                foreach ($classSubjects as $subject) {
                    $subjectId = $subject->id;

                    // Get all grades for this subject in this submission
                    $allGrades = GradeSubmissionSubject::where('grade_submission_id', $submission->id)
                        ->where('subject_id', $subjectId)
                        ->get();

                    // Get only approved grades for this subject
                    $approvedGrades = $allGrades->where('status', 'approved');

                    $passed = 0;
                    $failed = 0;
                    $inc = 0;
                    $dr = 0;
                    $nc = 0;
                    $totalStudents = 0;

                    // Check if there are any submitted grades (approved or not)
                    $hasSubmittedGrades = $allGrades->count() > 0;
                    $hasApprovedGrades = $approvedGrades->count() > 0;

                    // Count approved grades only
                    foreach ($approvedGrades as $grade) {
                        $totalStudents++;
                        $gradeValue = strtoupper(trim($grade->grade));

                        if ($gradeValue === 'INC') {
                            $inc++;
                        } elseif ($gradeValue === 'DR') {
                            $dr++;
                        } elseif ($gradeValue === 'NC') {
                            $nc++;
                        } elseif (is_numeric($gradeValue)) {
                            $numericGrade = floatval($gradeValue);

                            // Use the school's grading criteria
                            if ($numericGrade >= $passingGradeMin && $numericGrade <= $passingGradeMax) {
                                $passed++;
                            } else {
                                $failed++;
                            }
                        } else {
                            // Handle other grade formats as needing intervention
                            $failed++;
                        }
                    }

                    // Determine intervention need based on Subject Intervention Analytics logic
                    // ONLY create interventions for approved grades with "Need Intervention" status
                    $needsIntervention = false;
                    $interventionReason = '';
                    $studentCount = 0;

                    // Only process if there are approved grades
                    if ($hasApprovedGrades) {
                        $totalGrades = $passed + $failed + $inc + $dr + $nc;

                        if ($totalGrades > 0) {
                            // If any student has Failed, INC, DR, or NC, mark as 'Need Intervention'
                            if ($failed > 0 || $inc > 0 || $dr > 0 || $nc > 0) {
                                $needsIntervention = true;
                                $interventionReason = 'Need Intervention';
                                $studentCount = $failed + $inc + $dr + $nc;
                            }
                        }
                    }
                    // If no approved grades yet, don't create intervention records

                    if ($needsIntervention) {
                        // Check if intervention already exists
                        $existingIntervention = Intervention::where([
                            'subject_id' => $subjectId,
                            'school_id' => $submission->school_id,
                            'class_id' => $submission->class_id,
                            'grade_submission_id' => $submission->id
                        ])->first();

                        if (!$existingIntervention) {
                            // Create new intervention record
                            Intervention::create([
                                'subject_id' => $subjectId,
                                'school_id' => $submission->school_id,
                                'class_id' => $submission->class_id,
                                'grade_submission_id' => $submission->id,
                                'student_count' => $studentCount,
                                'status' => 'pending',
                                'remarks' => null, // Leave empty until educator updates
                                'created_by' => Auth::user()->user_id ?? 'system'
                            ]);
                        } else {
                            // Update student count but preserve existing intervention details
                            // Only update remarks if it's still the default intervention reason
                            $updateData = [
                                'student_count' => $studentCount,
                                'updated_by' => Auth::user()->user_id ?? 'system'
                            ];

                            // Don't update remarks - preserve educator-inputted intervention details
                            // Remarks should only be updated by educators through the update form

                            $existingIntervention->update($updateData);
                        }
                    } else {
                        // Remove intervention if it no longer needs intervention
                        Intervention::where([
                            'subject_id' => $subjectId,
                            'school_id' => $submission->school_id,
                            'class_id' => $submission->class_id,
                            'grade_submission_id' => $submission->id
                        ])->delete();
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in createInterventionsFromGradeData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw the error, just log it so the main page still loads
        }
    }



    /**
     * Get classes for a specific school (AJAX)
     */
    public function getClasses(Request $request)
    {
        try {
            $classes = \App\Models\ClassModel::where('school_id', $request->school_id)
                ->orderBy('class_name')
                ->get();

            return response()->json($classes);
        } catch (\Exception $e) {
            Log::error('Educator Get Classes Error', [
                'school_id' => $request->school_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load classes'], 500);
        }
    }

    /**
     * Get submissions for a specific school and class (AJAX)
     */
    public function getSubmissions(Request $request)
    {
        try {
            $query = GradeSubmission::with(['school', 'classModel']);

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            $submissions = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function($submission) {
                    return [
                        'id' => $submission->id,
                        'display_name' => $submission->semester . ' - ' . $submission->term . ' (' . $submission->academic_year . ')',
                        'semester' => $submission->semester,
                        'term' => $submission->term,
                        'academic_year' => $submission->academic_year
                    ];
                });

            return response()->json($submissions);

        } catch (\Exception $e) {
            Log::error('Educator Get Submissions Error', [
                'school_id' => $request->school_id,
                'class_id' => $request->class_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load submissions'], 500);
        }
    }

    /**
     * Show the intervention update form
     */
    public function update($id)
    {
        $intervention = Intervention::with(['subject', 'school', 'classModel', 'educatorAssigned'])
            ->findOrFail($id);

        // Get all educators for assignment dropdown
        $educators = PNUser::where('user_role', 'Educator')
            ->where('status', 'active')
            ->get();

        return view('educator.intervention-update', compact('intervention', 'educators'));
    }

    /**
     * Update intervention status and assignment
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,done',
            'intervention_date' => 'nullable|date',
            'educator_assigned' => 'nullable|exists:pnph_users,user_id',
            'intervention_details' => 'required|string|max:1000'
        ], [
            'intervention_details.required' => 'Please provide details about the interventions you implemented.',
            'intervention_details.max' => 'Intervention details cannot exceed 1000 characters.'
        ]);

        $intervention = Intervention::findOrFail($id);

        $intervention->update([
            'status' => $request->status,
            'intervention_date' => $request->intervention_date,
            'educator_assigned' => $request->educator_assigned,
            'remarks' => $request->intervention_details, // Map intervention_details to remarks column
            'updated_by' => Auth::user()->user_id
        ]);

        return redirect()->route('educator.intervention')
            ->with('success', 'Intervention details updated successfully.');
    }

    /**
     * Create test data for demonstration (temporary method)
     */
    public function createTestData()
    {
        try {
            DB::beginTransaction();

            // Get first available school and class
            $school = \App\Models\School::first();
            $class = \App\Models\ClassModel::first();
            $subject = \App\Models\Subject::first();
            $students = \App\Models\PNUser::where('user_role', 'student')->take(3)->get();

            if (!$school || !$class || !$subject || $students->count() < 1) {
                return response()->json(['error' => 'Missing required data (school, class, subject, or students)']);
            }

            // Create a grade submission
            $submission = GradeSubmission::create([
                'school_id' => $school->school_id,
                'class_id' => $class->class_id,
                'semester' => '1st',
                'term' => 'Prelim',
                'academic_year' => '2024-2025',
                'status' => 'approved'
            ]);

            // Create some failing grades to trigger intervention
            // Use the school's grading system
            $failingGrade = ($school->passing_grade_min <= 5.0) ? '4.5' : '65'; // 4.5 for 1-5 system, 65 for 0-100 system

            foreach ($students as $index => $student) {
                $grade = '';
                switch ($index) {
                    case 0:
                        $grade = $failingGrade; // Failing grade
                        break;
                    case 1:
                        $grade = 'INC'; // Incomplete
                        break;
                    case 2:
                        $grade = 'DR'; // Dropped
                        break;
                    default:
                        $grade = 'NC'; // No Credit
                        break;
                }

                GradeSubmissionSubject::create([
                    'grade_submission_id' => $submission->id,
                    'subject_id' => $subject->id,
                    'user_id' => $student->user_id,
                    'grade' => $grade,
                    'status' => 'approved'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Test data created successfully',
                'details' => [
                    'submission_id' => $submission->id,
                    'school' => $school->name,
                    'class' => $class->class_name ?? 'N/A',
                    'subject' => $subject->name,
                    'students_count' => $students->count(),
                    'grading_system' => $school->passing_grade_min <= 5.0 ? '1.0-5.0' : '0-100'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create test data: ' . $e->getMessage()]);
        }
    }


}
