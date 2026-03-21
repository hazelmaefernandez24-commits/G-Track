<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Intervention;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InterventionController extends Controller
{
    /**
     * Display intervention list for training (view-only)
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
            $schools = School::orderBy('name')->get();
            $classes = ClassModel::orderBy('class_name')->get();
            $subjects = Subject::orderBy('name')->get();

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

            Log::info('Training Intervention Index', [
                'total_interventions' => $interventions->count(),
                'filters_applied' => $request->only(['school_id', 'class_id', 'subject_id', 'status'])
            ]);

            return view('training.intervention.index', compact(
                'interventions',
                'schools',
                'classes',
                'subjects',
                'submissions'
            ))->with('title', 'Intervention Status');

        } catch (\Exception $e) {
            Log::error('Training Intervention Index Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to load interventions: ' . $e->getMessage());
        }
    }

    /**
     * Get classes for a specific school (AJAX)
     */
    public function getClasses($school_id)
    {
        try {
            $classes = ClassModel::where('school_id', $school_id)
                ->orderBy('class_name')
                ->get(['class_id', 'class_name']);

            return response()->json($classes);

        } catch (\Exception $e) {
            Log::error('Training Get Classes Error', [
                'school_id' => $school_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load classes'], 500);
        }
    }

    /**
     * Get subjects for a specific school and class (AJAX)
     */
    public function getSubjects(Request $request)
    {
        try {
            // Get subjects that have interventions for the selected school/class
            $query = Subject::whereHas('interventions', function($q) use ($request) {
                if ($request->filled('school_id')) {
                    $q->where('school_id', $request->school_id);
                }
                if ($request->filled('class_id')) {
                    $q->where('class_id', $request->class_id);
                }
            });

            $subjects = $query->orderBy('name')->get(['id', 'name']);

            return response()->json($subjects);

        } catch (\Exception $e) {
            Log::error('Training Get Subjects Error', [
                'school_id' => $request->school_id,
                'class_id' => $request->class_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load subjects'], 500);
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
            Log::error('Training Get Submissions Error', [
                'school_id' => $request->school_id,
                'class_id' => $request->class_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load submissions'], 500);
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
}
