<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionDetail;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PNUser;

class AnalyticsController extends Controller
{
    public function showClassGrades()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.class-grades', [
            'title' => 'Class Grades Analytics',
            'defaultSchool' => $school
        ]);
    }

    public function showSubjectProgress()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.subject-progress', [
            'title' => 'Subject Progress Analytics',
            'defaultSchool' => $school
        ]);
    }

    public function showSubjectIntervention()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.subject-intervention', [
            'title' => 'Subject Intervention Analytics',
            'defaultSchool' => $school
        ]);
    }

    public function showClassProgress()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();

        return view('training.analytics.class-progress', [
            'title' => 'Class Progress Analytics',
            'defaultSchool' => $school
        ]);
    }

    /**
     * Get all schools for AJAX dropdown
     */
    public function getSchools()
    {
        Log::info('Training Analytics: getSchools method called.');
        $schools = School::select('school_id as id', 'name')->get();
        Log::info('Training Analytics: Schools fetched: ' . $schools->toJson());
        return response()->json($schools);
    }

    /**
     * Get classes for a specific school
     */
    public function getClassesBySchool($schoolId)
    {
        $classes = ClassModel::where('school_id', $schoolId)
            ->select('class_id as id', 'class_name as name')
            ->orderBy('class_name')
            ->get();
        return response()->json($classes);
    }

    /**
     * Get submissions for a specific school and class
     */
    public function getClassSubmissions($schoolId, $classId)
    {
        $submissions = GradeSubmission::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($submission) {
                $hasGrades = DB::table('grade_submission_subject')
                    ->where('grade_submission_id', $submission->id)
                    ->whereNotNull('grade')
                    ->exists();

                return [
                    'id' => $submission->id,
                    'label' => sprintf(
                        '%s | %s | %s',
                        $submission->semester,
                        $submission->term,
                        $submission->academic_year
                    ),
                    'status' => $submission->status,
                    'has_incomplete_grades' => DB::table('grade_submission_subject')
                        ->where('grade_submission_id', $submission->id)
                        ->whereNull('grade')
                        ->exists(),
                    'has_grades' => $hasGrades
                ];
            });
            
        \Log::info('Training Submissions query:', [
            'school_id' => $schoolId,
            'class_id' => $classId,
            'count' => $submissions->count(),
            'submissions' => $submissions->toArray()
        ]);
        
        return response()->json($submissions);
    }

    public function fetchClassGrades(Request $request)
    {
        try {
            $schoolId = $request->query('school_id');
            $classId = $request->query('class_id');
            $submissionId = $request->query('submission_id');

            Log::info('Training fetchClassGrades called with:', [
                'school_id' => $schoolId,
                'class_id' => $classId,
                'submission_id' => $submissionId
            ]);

            if (!$schoolId || !$classId || !$submissionId) {
                Log::warning('Training fetchClassGrades: Missing required parameters');
                return response()->json([]);
            }

            // Get school passing grade
            $school = School::where('school_id', $schoolId)->first();
            if (!$school) {
                return response()->json([]);
            }

            // Get the GradeSubmission by id with eager loading
            $gradeSubmission = GradeSubmission::with(['classModel'])
                ->where('id', $submissionId)
                ->where('school_id', $schoolId)
                ->where('grade_submissions.class_id', $classId)
                ->first();
            
            if (!$gradeSubmission) {
                return response()->json([
                    'error' => 'Submission not found',
                    'submission_status' => 'not_found'
                ]);
            }

            // Get all detailed grades for this submission (include all grades, not just approved)
            $grades = DB::table('grade_submission_subject')
                ->join('pnph_users', 'grade_submission_subject.user_id', '=', 'pnph_users.user_id')
                ->leftJoin('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->whereNotNull('grade_submission_subject.grade')
                ->select(
                    'student_details.student_id as student_id',
                    'pnph_users.user_fname',
                    'pnph_users.user_lname',
                    'student_details.gender',
                    'subjects.name as subject_name',
                    'grade_submission_subject.grade',
                    'grade_submission_subject.status',
                    'grade_submission_subject.student_status',
                    'pnph_users.user_id'
                )
                ->get();
            
            // Group grades by student (using user_id for grouping)
            $groupedGrades = $grades->groupBy(function($item) {
                return $item->user_id;
            });

            $studentResults = [];

            // Get all unique subjects for this submission
            $allSubjects = $grades->pluck('subject_name')->unique()->sort()->values();

            foreach ($groupedGrades as $userId => $studentGrades) {
                $student = $studentGrades->first();
                $student_name = $student->user_fname . ' ' . $student->user_lname;
                $student_id_display = $student->student_id ?? $userId;

                $grades_data = [];
                $total_grade = 0;
                $graded_subjects_count = 0;
                $failed_subjects_count = 0; // Count of failed subjects
                $has_incomplete = false;
                $has_pending = false;
                $has_rejected = false;

                // Create grades array in the order of subjects
                foreach ($allSubjects as $subjectName) {
                    $subjectGrade = $studentGrades->where('subject_name', $subjectName)->first();

                    if ($subjectGrade) {
                        $gradeValue = $subjectGrade->grade;
                        $gradeStatus = $subjectGrade->status;

                        // Determine remarks based on status and grade
                        if ($gradeStatus === 'pending' || $gradeStatus === 'pending_approval' || $gradeStatus === 'submitted') {
                            $has_pending = true;
                        } elseif ($gradeStatus === 'approved') {
                            if (is_numeric($gradeValue)) {
                                $numeric_grade = (float) $gradeValue;
                                $total_grade += $numeric_grade;
                                $graded_subjects_count++;

                                if ($numeric_grade >= $school->passing_grade_min && $numeric_grade <= $school->passing_grade_max) {
                                    // Passed
                                } else {
                                    $failed_subjects_count++; // Count failed subjects
                                }
                            } else {
                                // Non-numeric approved grades (INC, DR, NC)
                                if ($gradeValue === 'INC') {
                                    $has_incomplete = true;
                                } else if ($gradeValue === 'DR' || $gradeValue === 'NC') {
                                    $failed_subjects_count++; // NC and DR are failing grades
                                }
                            }
                        } elseif ($gradeStatus === 'rejected') {
                            $has_rejected = true;
                        }

                        $grades_data[] = [
                            'grade' => $gradeValue ?? '-',
                            'status' => $gradeStatus
                        ];
                    } else {
                        // No grade for this subject
                        $grades_data[] = [
                            'grade' => '-',
                            'status' => 'pending'
                        ];
                        $has_pending = true;
                    }
                }

                $average_grade = $graded_subjects_count > 0 ? $total_grade / $graded_subjects_count : null;

                // Determine overall status based on failed subjects count
                if ($has_pending) {
                    $overall_status = 'Not yet Approved';
                } elseif ($has_rejected) {
                    $overall_status = 'Rejected';
                } elseif ($failed_subjects_count >= 3) {
                    $overall_status = 'Failed'; // 3 or more failed subjects = Failed
                } elseif ($failed_subjects_count >= 1 && $failed_subjects_count <= 2) {
                    $overall_status = 'Conditional'; // 1-2 failed subjects = Conditional
                } elseif ($has_incomplete) {
                    $overall_status = 'Incomplete Submission';
                } else {
                    $overall_status = 'Passed';
                }

                $studentResults[] = [
                    'student_id' => $student_id_display,
                    'full_name' => $student_name,
                    'grades' => $grades_data,
                    'average' => $average_grade,
                    'status' => $overall_status,
                ];
            }

            $response = [
                'students' => $studentResults,
                'subjects' => $allSubjects->toArray(),
                'school' => [
                    'name' => $school->name,
                    'passing_grade_min' => $school->passing_grade_min,
                    'passing_grade_max' => $school->passing_grade_max
                ],
                'submission' => [
                    'term' => $gradeSubmission->term,
                    'semester' => $gradeSubmission->semester,
                    'academic_year' => $gradeSubmission->academic_year,
                ],
                'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class'
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error in Training fetchClassGrades: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An internal server error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function fetchSubjectProgressData(Request $request)
    {
        try {
            $schoolId = $request->input('school_id');
            $classId = $request->input('class_id');
            $submissionId = $request->input('submission_id');

            // Get school passing grade
            $school = School::find($schoolId);
            if (!$school) {
                return response()->json(['error' => 'School not found'], 404);
            }

            // Get the GradeSubmission by id with eager loading
            $gradeSubmission = GradeSubmission::with(['classModel', 'subjects'])
                ->where('id', $submissionId)
                ->where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->first();

            if (!$gradeSubmission) {
                return response()->json([
                    'error' => 'Submission not found',
                    'submission_status' => 'not_found'
                ]);
            }

            // Get all grades for this submission
            $gradesRaw = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->select(
                    'grade_submission_subject.*',
                    'subjects.name as subject_name'
                )
                ->get();

            // Group grades by subject
            $subjectResults = [];
            $groupedGrades = $gradesRaw->groupBy('subject_name');

            foreach ($groupedGrades as $subjectName => $grades) {
                $passed = 0;
                $failed = 0;
                $inc = 0;
                $dr = 0;
                $nc = 0;
                $hasSubmittedGrades = false;
                $hasApprovedGrades = false;
                $remarks = '';

                foreach ($grades as $grade) {
                    // Only count as submitted if grade is not null/empty
                    if (!is_null($grade->grade) && $grade->grade !== '' && $grade->grade !== '0') {
                        $hasSubmittedGrades = true; // Student has submitted grades for this subject

                        if ($grade->status === 'approved') {
                            $hasApprovedGrades = true; // Training has approved some grades

                            if ($grade->grade === 'INC') {
                                $inc++;
                            } elseif ($grade->grade === 'DR') {
                                $dr++;
                            } elseif ($grade->grade === 'NC') {
                                $nc++;
                            } elseif (is_numeric($grade->grade)) {
                                if ($grade->grade >= $school->passing_grade_min && $grade->grade <= $school->passing_grade_max) {
                                    $passed++;
                                } else {
                                    $failed++;
                                }
                            }
                        }
                    }
                }

                // Determine remarks based on submission and approval status
                if (!$hasSubmittedGrades) {
                    // No students have submitted grades for this subject
                    $remarks = 'No Grades Submitted';
                } elseif ($hasSubmittedGrades && !$hasApprovedGrades) {
                    // Students have submitted grades but training hasn't approved any yet
                    $remarks = 'No Approved Grades';
                } else {
                    // Training has approved some grades, check if intervention is needed
                    if ($failed > 0 || $inc > 0 || $dr > 0 || $nc > 0) {
                        $remarks = 'Need Intervention';
                    } else {
                        $remarks = 'No Need Intervention';
                    }
                }

                $subjectResults[] = [
                    'subject' => $subjectName,
                    'passed' => $passed,
                    'failed' => $failed,
                    'inc' => $inc,
                    'dr' => $dr,
                    'nc' => $nc,
                    'remarks' => $remarks
                ];
            }

            return response()->json([
                'subjects' => $subjectResults,
                'submission' => [
                    'term' => $gradeSubmission->term,
                    'semester' => $gradeSubmission->semester,
                    'academic_year' => $gradeSubmission->academic_year,
                ],
                'school' => [
                    'name' => $school->name,
                    'passing_grade_min' => $school->passing_grade_min,
                    'passing_grade_max' => $school->passing_grade_max
                ],
                'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Training fetchSubjectProgressData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An internal server error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function fetchSubjectInterventionData(Request $request)
    {
        try {
            $schoolId = $request->input('school_id');
            $classId = $request->input('class_id');
            $submissionId = $request->input('submission_id');

            // Get school passing grade
            $school = School::find($schoolId);
            if (!$school) {
                return response()->json(['error' => 'School not found'], 404);
            }

            // Get the GradeSubmission by id with eager loading
            $gradeSubmission = GradeSubmission::with(['classModel', 'subjects'])
                ->where('id', $submissionId)
                ->where('school_id', $schoolId)
                ->where('grade_submissions.class_id', $classId)
                ->first();

            if (!$gradeSubmission) {
                return response()->json([
                    'error' => 'Submission not found',
                    'submission_status' => 'not_found'
                ]);
            }

            // Get all grades for this submission
            $gradesRaw = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->select(
                    'grade_submission_subject.*',
                    'subjects.name as subject_name'
                )
                ->get();

            // Group grades by subject
            $subjectResults = [];
            $groupedGrades = $gradesRaw->groupBy('subject_name');

            foreach ($groupedGrades as $subjectName => $grades) {
                $passed = 0;
                $failed = 0;
                $inc = 0;
                $dr = 0;
                $nc = 0;
                $hasSubmittedGrades = false;
                $hasApprovedGrades = false;
                $remarks = '';

                foreach ($grades as $grade) {
                    // Only count as submitted if grade is not null/empty
                    if (!is_null($grade->grade) && $grade->grade !== '' && $grade->grade !== '0') {
                        $hasSubmittedGrades = true; // Student has submitted grades for this subject

                        if ($grade->status === 'approved') {
                            $hasApprovedGrades = true; // Training has approved some grades

                            if ($grade->grade === 'INC') {
                                $inc++;
                            } elseif ($grade->grade === 'DR') {
                                $dr++;
                            } elseif ($grade->grade === 'NC') {
                                $nc++;
                            } elseif (is_numeric($grade->grade)) {
                                if ($grade->grade >= $school->passing_grade_min && $grade->grade <= $school->passing_grade_max) {
                                    $passed++;
                                } else {
                                    $failed++;
                                }
                            }
                        }
                    }
                }

                // Determine remarks based on submission and approval status
                if (!$hasSubmittedGrades) {
                    // No students have submitted grades for this subject
                    $remarks = 'No Grades Submitted';
                } elseif ($hasSubmittedGrades && !$hasApprovedGrades) {
                    // Students have submitted grades but training hasn't approved any yet
                    $remarks = 'No Approved Grades';
                } else {
                    // Training has approved some grades, check if intervention is needed
                    if ($failed > 0 || $inc > 0 || $dr > 0 || $nc > 0) {
                        $remarks = 'Need Intervention';
                    } else {
                        $remarks = 'No Need Intervention';
                    }
                }

                $subjectResults[] = [
                    'subject' => $subjectName,
                    'passed' => $passed,
                    'failed' => $failed,
                    'inc' => $inc,
                    'dr' => $dr,
                    'nc' => $nc,
                    'remarks' => $remarks
                ];
            }

            return response()->json([
                'subjects' => $subjectResults,
                'submission' => [
                    'term' => $gradeSubmission->term,
                    'semester' => $gradeSubmission->semester,
                    'academic_year' => $gradeSubmission->academic_year,
                ],
                'school' => [
                    'name' => $school->name,
                    'passing_grade_min' => $school->passing_grade_min,
                    'passing_grade_max' => $school->passing_grade_max
                ],
                'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Training fetchSubjectInterventionData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An internal server error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function fetchClassProgressData(Request $request)
    {
        try {
            $schoolId = $request->input('school_id');
            $classId = $request->input('class_id');
            $submissionId = $request->input('submission_id');

            Log::info('Training Fetching class progress data:', [
                'school_id' => $schoolId,
                'class_id' => $classId,
                'submission_id' => $submissionId
            ]);

            // Get school passing grade
            $school = School::find($schoolId);
            if (!$school) {
                Log::warning('Training School not found for ID: ' . $schoolId);
                return response()->json(['error' => 'School not found'], 404);
            }

            // Get the GradeSubmission by id with eager loading
            $gradeSubmission = GradeSubmission::with(['classModel'])
                ->where('id', $submissionId)
                ->where('school_id', $schoolId)
                ->where('class_id', $classId)
                ->first();

            if (!$gradeSubmission) {
                Log::warning('Training Submission not found:', [
                    'submission_id' => $submissionId,
                    'school_id' => $schoolId,
                    'class_id' => $classId
                ]);
                return response()->json([
                    'error' => 'Submission not found',
                    'submission_status' => 'not_found'
                ]);
            }

            // Get the class record first to get the auto-increment ID
            $classRecord = ClassModel::where('class_id', $classId)->first();
            if (!$classRecord) {
                Log::warning('Training Class not found for class_id: ' . $classId);
                return response()->json([
                    'error' => 'Class not found',
                    'students' => [],
                    'school' => [
                        'name' => $school->name,
                        'passing_grade_min' => $school->passing_grade_min,
                        'passing_grade_max' => $school->passing_grade_max
                    ],
                    'submission' => [
                        'term' => $gradeSubmission->term,
                        'semester' => $gradeSubmission->semester,
                        'academic_year' => $gradeSubmission->academic_year,
                    ],
                    'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class'
                ]);
            }

            // Get students for this class using the auto-increment ID
            $students = PNUser::select('pnph_users.user_id', 'pnph_users.user_fname', 'pnph_users.user_lname')
                ->join('class_student', 'pnph_users.user_id', '=', 'class_student.user_id')
                ->where('class_student.class_id', $classRecord->id) // Use auto-increment ID
                ->where('pnph_users.user_role', 'Student')
                ->where('pnph_users.status', 'active')
                ->orderBy('pnph_users.user_lname')
                ->orderBy('pnph_users.user_fname')
                ->get();

            Log::info('Training Students found:', [
                'count' => $students->count(),
                'class_id' => $classId
            ]);

            if ($students->isEmpty()) {
                Log::warning('Training No students found for the given criteria', [
                    'school_id' => $schoolId,
                    'class_id' => $classId
                ]);
                return response()->json([
                    'error' => 'No students found for this class',
                    'students' => [],
                    'school' => [
                        'name' => $school->name,
                        'passing_grade_min' => $school->passing_grade_min,
                        'passing_grade_max' => $school->passing_grade_max
                    ],
                    'submission' => [
                        'term' => $gradeSubmission->term,
                        'semester' => $gradeSubmission->semester,
                        'academic_year' => $gradeSubmission->academic_year,
                    ],
                    'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class'
                ]);
            }

            // Get grades for all students in this submission
            $grades = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->whereIn('grade_submission_subject.user_id', $students->pluck('user_id'))
                ->select(
                    'grade_submission_subject.user_id',
                    'grade_submission_subject.grade',
                    'grade_submission_subject.status',
                    'subjects.name as subject_name'
                )
                ->get();

            // Process student data and aggregate for pie chart
            $passedCount = 0;
            $failedCount = 0;
            $conditionalCount = 0; // Add conditional count
            $pendingCount = 0;
            $noGradesCount = 0;
            $totalStudents = $students->count();

            foreach ($students as $student) {
                $studentGrades = $grades->where('user_id', $student->user_id);

                $totalSubjects = $studentGrades->count();
                $passedSubjects = 0;
                $failedSubjects = 0;
                $pendingSubjects = 0;
                $totalGradePoints = 0;
                $gradedSubjects = 0;
                $hasSubmittedGrades = false;
                $hasApprovedGrades = false;
                $hasIncomplete = false;

                // Check if student has submitted any grades at all
                if ($totalSubjects == 0) {
                    // Student hasn't submitted any grades at all
                    $noGradesCount++;
                    continue;
                }

                // Check if student has actually submitted grades (not just empty records)
                $actualGradeCount = 0;
                foreach ($studentGrades as $grade) {
                    // Only count as submitted if grade is not null/empty
                    if (!is_null($grade->grade) && $grade->grade !== '' && $grade->grade !== '0') {
                        $actualGradeCount++;
                        $hasSubmittedGrades = true;

                        if ($grade->status === 'approved') {
                            $hasApprovedGrades = true;
                            if (is_numeric($grade->grade)) {
                                $gradeValue = floatval($grade->grade);
                                $totalGradePoints += $gradeValue;
                                $gradedSubjects++;

                                if ($gradeValue >= $school->passing_grade_min && $gradeValue <= $school->passing_grade_max) {
                                    $passedSubjects++;
                                } else {
                                    $failedSubjects++;
                                }
                            } else {
                                // Non-numeric approved grades (INC, DR, NC)
                                if ($grade->grade === 'INC') {
                                    $hasIncomplete = true;
                                } elseif ($grade->grade === 'DR' || $grade->grade === 'NC') {
                                    $failedSubjects++; // NC and DR are failing grades
                                }
                            }
                        } elseif ($grade->status === 'pending' || $grade->status === 'pending_approval' || $grade->status === 'submitted') {
                            $pendingSubjects++;
                        }
                    }
                }

                // Determine overall status for this student using conditional logic
                if (!$hasSubmittedGrades || $actualGradeCount == 0) {
                    // Student hasn't submitted any actual grades (only empty records or no records)
                    $noGradesCount++;
                } elseif ($hasSubmittedGrades && !$hasApprovedGrades && $pendingSubjects > 0) {
                    // Student has submitted grades but none are approved yet (all are pending approval by training)
                    $pendingCount++;
                } elseif ($hasApprovedGrades && $failedSubjects >= 3) {
                    // Student has approved grades and 3 or more failed subjects = Failed
                    $failedCount++;
                } elseif ($hasApprovedGrades && $failedSubjects >= 1 && $failedSubjects <= 2) {
                    // Student has approved grades and 1-2 failed subjects = Conditional
                    $conditionalCount++;
                } elseif ($hasApprovedGrades && $passedSubjects > 0 && $failedSubjects == 0) {
                    // Student has approved grades and all are passed
                    $passedCount++;
                } elseif ($hasIncomplete) {
                    // Student has incomplete grades
                    $pendingCount++;
                } elseif ($hasSubmittedGrades && $pendingSubjects > 0) {
                    // Student has submitted grades that are pending
                    $pendingCount++;
                } else {
                    // Default case - if student has submitted but no clear status
                    $pendingCount++;
                }
            }

            // Calculate percentages
            $passedPercentage = $totalStudents > 0 ? ($passedCount / $totalStudents) * 100 : 0;
            $failedPercentage = $totalStudents > 0 ? ($failedCount / $totalStudents) * 100 : 0;
            $conditionalPercentage = $totalStudents > 0 ? ($conditionalCount / $totalStudents) * 100 : 0;
            $pendingPercentage = $totalStudents > 0 ? ($pendingCount / $totalStudents) * 100 : 0;
            $noGradesPercentage = $totalStudents > 0 ? ($noGradesCount / $totalStudents) * 100 : 0;

            // Prepare data for the pie chart
            $chartData = [
                'labels' => ['Passed', 'Failed', 'Conditional', 'Pending', 'No Grades Submitted'],
                'data' => [
                     round($passedPercentage, 2),
                     round($failedPercentage, 2),
                     round($conditionalPercentage, 2),
                     round($pendingPercentage, 2),
                     round($noGradesPercentage, 2)
                ],
                 'counts' => [
                    'Passed' => $passedCount,
                    'Failed' => $failedCount,
                    'Conditional' => $conditionalCount,
                    'Pending' => $pendingCount,
                    'No Grades Submitted' => $noGradesCount
                 ],
                 'total_students' => $totalStudents,
                 'class_name' => $gradeSubmission->classModel->class_name ?? 'Unknown Class',
                 'submission_details' => [
                    'semester' => $gradeSubmission->semester,
                    'term' => $gradeSubmission->term,
                    'academic_year' => $gradeSubmission->academic_year,
                 ]
            ];

            return response()->json($chartData);

        } catch (\Exception $e) {
            Log::error('Error in Training fetchClassProgressData: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An internal server error occurred: ' . $e->getMessage()], 500);
        }
    }
}
