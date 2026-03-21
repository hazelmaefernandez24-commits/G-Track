<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\GradeSubmission;
use App\Models\PNUser;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // Show the Subject Progress Analytics page
    public function showSubjectProgress()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.subject-progress', [
            'defaultSchool' => $school
        ]);
    }
    // Show the Subject Intervention Analytics page
    public function showSubjectIntervention()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.subject-intervention', [
            'defaultSchool' => $school
        ]);
    }
    // Show the Class Grades page
    public function showClassGrades()
    {
        // Get the first school's passing grade range as default
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();
        
        return view('training.analytics.class-grades', [
            'defaultSchool' => $school
        ]);
    }

    // Show the Class Progress page
    public function showClassProgress()
    {
        // Get the first school's passing grade range as default if needed, or just pass view
        $school = School::select('passing_grade_min', 'passing_grade_max')->first();

        return view('training.analytics.class-progress', [
             'defaultSchool' => $school // Optional: pass default school data if needed in frontend
        ]);
    }

    // Get all schools
    public function getSchools()
    {
        $schools = School::select('school_id as id', 'name')->get();
        return response()->json($schools);
    }

    // Get classes for a school
    public function getClassesBySchool($schoolId)
    {
        $classes = ClassModel::where('school_id', $schoolId)
            ->select('class_id as id', 'class_name as name')
            ->get();
        return response()->json($classes);
    }

    // Get terms/semesters for a school
    public function getTermsBySchool($schoolId)
    {
        $school = School::where('school_id', $schoolId)->first();
        $terms = $school ? ($school->terms ?? []) : [];
        return response()->json($terms);
    }

    // Get submissions (semester/term/year) for a school/class
    public function getClassSubmissions($schoolId, $classId)
    {
        $submissions = GradeSubmission::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->orderByDesc('created_at')
            ->get();

        \Log::info('Submissions query:', [
            'school_id' => $schoolId,
            'class_id' => $classId,
            'count' => $submissions->count(),
            'submissions' => $submissions->toArray()
        ]);

        $result = $submissions->map(function($sub) {
            $label = [];
            if (!empty($sub->semester)) $label[] = 'Semester: ' . $sub->semester;
            if (!empty($sub->term)) $label[] = 'Term: ' . $sub->term;
            if (!empty($sub->academic_year)) $label[] = 'Year: ' . $sub->academic_year;

            // Check for incomplete grades - check if any grades exist for this submission
            $hasGrades = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $sub->id)
                ->whereNotNull('grade')
                ->exists();

            $incompleteGrades = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $sub->id)
                ->whereNull('grade')
                ->exists();

            // If no specific fields, use created_at as identifier
            if (empty($label)) {
                $label[] = 'Submission: ' . $sub->created_at->format('Y-m-d H:i:s');
            }

            return [
                'id' => $sub->id,
                'label' => implode(' | ', $label),
                'status' => $sub->status,
                'has_incomplete_grades' => $incompleteGrades,
                'has_grades' => $hasGrades
            ];
        });

        \Log::info('Formatted submissions:', $result->toArray());

        return response()->json($result);
    }

    // Fetch class grades for the selected school, class, and submission
    public function fetchSubjectInterventionData(\Illuminate\Http\Request $request)
    {
        $schoolId = $request->query('school_id');
        $classId = $request->query('class_id');
        $submissionId = $request->query('submission_id');
        
        if (!$schoolId || !$classId || !$submissionId) {
            return response()->json([]);
        }

        $school = School::where('school_id', $schoolId)->first();
        if (!$school) return response()->json([]);

        // Get the GradeSubmission by id
        $gradeSubmission = GradeSubmission::where('id', $submissionId)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->first();

        \Log::info('Looking for submission:', [
            'submission_id' => $submissionId,
            'school_id' => $schoolId,
            'class_id' => $classId,
            'found' => $gradeSubmission ? 'yes' : 'no'
        ]);

        if (!$gradeSubmission) {
            return response()->json([
                'error' => 'Submission not found',
                'submission_status' => 'not_found',
                'debug_info' => [
                    'submission_id' => $submissionId,
                    'school_id' => $schoolId,
                    'class_id' => $classId
                ]
            ]);
        }

        // Get all grades for this submission (include all statuses, not just approved)
        $grades = DB::table('grade_submission_subject')
            ->join('subjects', 'subjects.id', '=', 'grade_submission_subject.subject_id')
            ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
            ->whereNotNull('grade_submission_subject.grade')
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'grade_submission_subject.grade',
                'grade_submission_subject.user_id',
                'grade_submission_subject.status',
                'grade_submission_subject.student_status'
            )
            ->get();

        \Log::info('Grades found for submission:', [
            'submission_id' => $gradeSubmission->id,
            'grades_count' => $grades->count(),
            'grades' => $grades->toArray()
        ]);
            
        // Group grades by subject
        $groupedGrades = $grades->groupBy('subject_name');
        
        $subjectResults = [];
        
        foreach ($groupedGrades as $subjectName => $grades) {
            $passed = 0;
            $failed = 0;
            $inc = 0;
            $dr = 0;
            $nc = 0;
            $pending = false;
            $needIntervention = false;
            
            // Track unique students to avoid counting duplicates
            $processedStudents = [];
            
            // Process each grade for this subject
            foreach ($grades as $grade) {
                $studentId = $grade->user_id;
                
                // Skip if we've already processed this student for this subject
                if (in_array($studentId, $processedStudents)) {
                    continue;
                }
                
                $processedStudents[] = $studentId;
                $gradeValue = $grade->grade;
                
                // Categorize the grade
                if ($gradeValue === 'INC') {
                    $inc++;
                    $pending = true;
                } elseif ($gradeValue === 'DR') {
                    $dr++;
                    $pending = true;
                } elseif ($gradeValue === 'NC') {
                    $nc++;
                    $needIntervention = true;
                } elseif (is_numeric($gradeValue)) {
                    $gradeValue = (float)$gradeValue;
                    if ($gradeValue >= $school->passing_grade_min && $gradeValue <= $school->passing_grade_max) {
                        $passed++;
                    } else {
                        $failed++;
                        $needIntervention = true;
                    }
                }
            }
            
            // Determine remarks based on the new criteria
            $totalGrades = $passed + $failed + $inc + $dr + $nc;
            $remarks = 'No Submission Recorded';
            
            if ($totalGrades > 0) {
                // If any student has Failed, INC, DR, or NC, mark as 'Need Intervention'
                if ($failed > 0 || $inc > 0 || $dr > 0 || $nc > 0) {
                    $remarks = 'Need Intervention';
                } else {
                    // Only mark as 'No Need Intervention' if all students have passed
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
    }
    
    public function fetchSubjectProgressData(\Illuminate\Http\Request $request)
    {
        $schoolId = $request->query('school_id');
        $classId = $request->query('class_id');
        $submissionId = $request->query('submission_id');
        
        if (!$schoolId || !$classId || !$submissionId) {
            return response()->json([]);
        }

        $school = School::where('school_id', $schoolId)->first();
        if (!$school) return response()->json([]);

        // Get the GradeSubmission by id
        $gradeSubmission = GradeSubmission::where('id', $submissionId)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->first();
            
        if (!$gradeSubmission) {
            return response()->json([
                'error' => 'Submission not found',
                'submission_status' => 'not_found'
            ]);
        }

        // Get all grades for this submission (include all statuses, not just approved)
        $grades = DB::table('grade_submission_subject')
            ->join('subjects', 'subjects.id', '=', 'grade_submission_subject.subject_id')
            ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
            ->whereNotNull('grade_submission_subject.grade')
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'grade_submission_subject.grade',
                'grade_submission_subject.status',
                'grade_submission_subject.student_status'
            )
            ->get();
            
        // Group grades by subject
        $groupedGrades = $grades->groupBy('subject_name');
        
        $subjectResults = [];
        
        foreach ($groupedGrades as $subjectName => $grades) {
            $passed = 0;
            $failed = 0;
            $inc = 0;
            $dr = 0;
            $nc = 0;
            
            // Count grades for this subject
            foreach ($grades as $grade) {
                if ($grade->grade === 'INC') {
                    $inc++;
                } elseif ($grade->grade === 'DR') {
                    $dr++;
                } elseif ($grade->grade === 'NC') {
                    $nc++;
                } elseif (is_numeric($grade->grade)) {
                    $gradeValue = (float)$grade->grade;
                    if ($gradeValue >= $school->passing_grade_min && $gradeValue <= $school->passing_grade_max) {
                        $passed++;
                    } else {
                        $failed++;
                    }
                }
            }
            
            $totalStudents = $passed + $failed + $inc + $dr + $nc;
            
            $subjectResults[] = [
                'subject' => $subjectName,
                'passed' => $passed,
                'failed' => $failed,
                'inc' => $inc,
                'dr' => $dr,
                'nc' => $nc,
                'total_students' => $totalStudents
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
    }
    
    public function fetchClassGrades(\Illuminate\Http\Request $request)
    {
        try {
        $schoolId = $request->query('school_id');
        $classId = $request->query('class_id');
        $submissionId = $request->query('submission_id');

        if (!$schoolId || !$classId || !$submissionId) {
                return response()->json(['error' => 'Missing required parameters']);
        }

        $school = School::where('school_id', $schoolId)->first();
            if (!$school) {
                return response()->json(['error' => 'School not found']);
            }

            // Determine the numeric values for special grades based on the school's passing range
            $isLowerGradeBetter = $school->passing_grade_min < $school->passing_grade_max;
            // DR and NC value based on the edge of the failed range
            $drNcNumericValue = 5.0; // DR and NC always result in 5.0 average as per new rule
            $incNumeric = 0;

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

            // Get subjects for this submission - only once and in order
            $subjects = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->select('subjects.name')
                ->distinct()
                ->orderBy('subjects.name')
                ->pluck('name')
                ->toArray();
            
            // Get all students for this class
            $students = DB::table('grade_submission_subject')
                ->join('pnph_users', 'grade_submission_subject.user_id', '=', 'pnph_users.user_id')
                ->leftJoin('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->select('pnph_users.user_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.student_id')
                ->distinct()
            ->get();
            
            // Get all grades for this submission
        $gradesRaw = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                ->select(
                    'grade_submission_subject.*',
                    'grade_submission_subject.student_status as status',
                    'subjects.name as subject_name'
                )
            ->get();
            
            // Group grades by user_id and subject name for easier access
        $gradesByStudent = [];
        foreach ($gradesRaw as $grade) {
            if (!isset($gradesByStudent[$grade->user_id])) {
                $gradesByStudent[$grade->user_id] = [];
            }
                $gradesByStudent[$grade->user_id][$grade->subject_name] = $grade;
        }

        $studentRows = [];
        $hasGrades = false;
        $allGradesComplete = true;
        
        foreach ($students as $student) {
                $studentId = $student->student_id ?? $student->user_id;
            
            // Skip if we've already processed this student
            if (isset($studentRows[$studentId])) {
                continue;
            }
            
            $row = [
                'student_id' => $studentId,
                'full_name' => $student->user_lname . ', ' . $student->user_fname,
                'grades' => [],
                'average' => '',
                'status' => '',
                'has_grades' => false,
                    'user_id' => $student->user_id
                ];
                
                $numericGradesForAvg = []; // Collects numeric and INC (as 0) for average calculation
                $pendingStatusDueToApproval = false; // Flag for non-approved grades
                $hasApprovedDrOrNc = false; // Flag for approved DR or NC grades
                $hasAnyGradeEntry = false; // Flag if the student has any grade entry at all
                
                // Process grades for each subject in order
                foreach ($subjects as $subjectName) {
                    $gradeObj = $gradesByStudent[$student->user_id][$subjectName] ?? null;
                    
                    // If there's no grade object for this subject, it's a missing grade, consider it pending approval.
                    if (!$gradeObj) {
                         $pendingStatusDueToApproval = true; // Missing grade implies pending
                         $row['grades'][] = ['grade' => '', 'status' => 'pending']; // Represent missing grade
                         continue; // Move to the next subject
                    }
                    
                    $hasAnyGradeEntry = true; // Student has at least one grade entry

                    $grade = $gradeObj->grade;
                    $status = $gradeObj->status ?? 'pending';
                    $studentStatus = $gradeObj->student_status ?? $status;
                $effectiveStatus = !empty($studentStatus) ? $studentStatus : $status;
                $normalizedStatus = strtolower($effectiveStatus);
                
                $gradeData = [
                    'grade' => $grade,
                    'status' => $normalizedStatus === 'approved' ? 'approved' : $effectiveStatus
                ];
                
                    // Check if ANY grade is not approved for this student in this submission
                    if ($normalizedStatus !== 'approved') {
                        $pendingStatusDueToApproval = true;
                    }
                    
                    // ONLY process APPROVED grades for DR/NC flag and average calculation
                    if ($normalizedStatus === 'approved') {
                        if (in_array($grade, ['DR', 'NC'])) {
                            $hasApprovedDrOrNc = true; // Found an approved DR or NC
                            // Note: Approved DR/NC don't add to $numericGradesForAvg under this new logic
                        } elseif ($grade === 'INC') {
                            // Use numeric equivalent for INC in average calculation
                            $numericGradesForAvg[] = $incNumeric;
                        } elseif (is_numeric($grade)) {
                             $numericGradesForAvg[] = floatval($grade);
                        } else {
                            // Handle unexpected approved non-numeric grades - they don't contribute to numeric average
                        }
                }
                
                $row['grades'][] = $gradeData;
            }

                // Determine overall status and average based on the new priority rules.
                if ($pendingStatusDueToApproval) {
                    $row['status'] = 'Pending'; // Priority 1: Status is pending if ANY grade is not approved or missing
                    $row['average'] = null; // Average is not finalized if status is pending
                } elseif ($hasApprovedDrOrNc) {
                     // Priority 2: If all grades approved BUT student has an approved DR or NC
                    $row['status'] = 'Failed'; // Automatically Failed
                    $row['average'] = 5.0; // Automatically 5.0 average
                 } elseif (count($numericGradesForAvg) > 0) { 
                    // Priority 3: If all grades approved, no DR/NC, and there are grades for average calculation (numeric or INC)
                    $average = array_sum($numericGradesForAvg)/count($numericGradesForAvg);
                    $row['average'] = number_format($average, 2); // Format average
                    
                    // Check if average is within the school's passing range to determine Passed/Failed
                    $passingMin = floatval($school->passing_grade_min);
                    $passingMax = floatval($school->passing_grade_max);

                    if ($isLowerGradeBetter) {
                         // Lower grade is better (e.g. 1.0-3.0 passed)
                        $row['status'] = ($average >= $passingMin && $average <= $passingMax) ? 'Passed' : 'Failed';
                    } else {
                         // Higher grade is better (e.g. 75-100 passed)
                         $row['status'] = ($average >= $passingMin && $average <= $passingMax) ? 'Passed' : 'Failed';
                    }
                } elseif ($hasAnyGradeEntry) {
                     // Case where all grades are approved, no DR/NC, but no grades contributed to numeric average (e.g., only unexpected approved non-numeric types were present)
                     $row['status'] = 'No Calculable Average'; 
                     $row['average'] = null;
                 }
                else {
                    // Fallback: If no grade entries found for the student for this submission at all
                     $row['status'] = 'No Grades Submitted'; 
                     $row['average'] = null; 
                }

                $studentRows[$studentId] = $row;
            }

        // Convert associative array to indexed array for the response
        $studentRows = array_values($studentRows);
        
            return response()->json([
            'students' => $studentRows,
                'subjects' => $subjects, // Send subjects array only once
            'submission' => [
                    'term' => $gradeSubmission->term,
                    'semester' => $gradeSubmission->semester,
                    'academic_year' => $gradeSubmission->academic_year,
                    'status' => 'individual_status'
                ],
                'term' => $gradeSubmission->term,
                'semester' => $gradeSubmission->semester,
                'academic_year' => $gradeSubmission->academic_year,
            'school' => [
                'name' => $school->name,
                'passing_grade_min' => $school->passing_grade_min,
                'passing_grade_max' => $school->passing_grade_max
            ],
                'class_name' => $gradeSubmission->classModel ? $gradeSubmission->classModel->class_name : ClassModel::where('class_id', $classId)->value('class_name') ?? 'Unknown Class'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in fetchClassGrades: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'school_id' => $schoolId ?? null,
                'class_id' => $classId ?? null,
                'submission_id' => $submissionId ?? null
            ]);
            
            return response()->json([
                'error' => 'An error occurred while fetching grades',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Fetch class progress data for the selected school, class, and submission
    public function fetchClassProgressData(\Illuminate\Http\Request $request)
    {
        try {
            $schoolId = $request->query('school_id');
            $classId = $request->query('class_id');
            $submissionId = $request->query('submission_id');

            if (!$schoolId || !$classId || !$submissionId) {
                return response()->json(['error' => 'Missing required parameters']);
            }

            $school = School::where('school_id', $schoolId)->first();
            if (!$school) {
                return response()->json(['error' => 'School not found']);
            }

            // Reuse the logic from fetchClassGrades to get student statuses
            // We'll just get the raw student data with statuses determined by the existing logic
            $classGradesResponse = $this->fetchClassGrades($request);
            $data = $classGradesResponse->getData(true); // Get the response data as an array

            // Check if there's an error in the fetched grades data
            if (isset($data['error'])) {
                 // If the error is 'Submission not found', return specific status
                if (isset($data['submission_status']) && $data['submission_status'] === 'not_found') {
                     return response()->json(['submission_status' => 'not_found']);
                }
                return response()->json(['error' => $data['error']]);
            }

            $students = $data['students'] ?? [];

            // Count students by status
            $passedCount = 0;
            $failedCount = 0;
            $pendingCount = 0;
            $noGradesCount = 0;
            $totalStudents = count($students);

            foreach ($students as $student) {
                switch ($student['status']) {
                    case 'Passed':
                        $passedCount++;
                        break;
                    case 'Failed':
                        $failedCount++;
                        break;
                    case 'Pending':
                        $pendingCount++;
                        break;
                    case 'No Grades Submitted':
                    case 'No Calculable Average': // Count this as no grades or similar for the chart
                        $noGradesCount++;
                        break;
                    // Add other potential statuses if necessary
                }
            }

            // Calculate percentages
            $passedPercentage = $totalStudents > 0 ? ($passedCount / $totalStudents) * 100 : 0;
            $failedPercentage = $totalStudents > 0 ? ($failedCount / $totalStudents) * 100 : 0;
            $pendingPercentage = $totalStudents > 0 ? ($pendingCount / $totalStudents) * 100 : 0;
             $noGradesPercentage = $totalStudents > 0 ? ($noGradesCount / $totalStudents) * 100 : 0;

            // Prepare data for the pie chart
            $chartData = [
                'labels' => ['Passed', 'Failed', 'Pending', 'No Grades Submitted'],
                'data' => [
                     round($passedPercentage, 2),
                     round($failedPercentage, 2),
                     round($pendingPercentage, 2),
                     round($noGradesPercentage, 2)
                ],
                 'counts' => [
                    'Passed' => $passedCount,
                    'Failed' => $failedCount,
                    'Pending' => $pendingCount,
                    'No Grades Submitted' => $noGradesCount
                 ],
                 'total_students' => $totalStudents,
                 'class_name' => $data['class_name'] ?? 'Unknown Class',
                 'submission_details' => $data['submission'] ?? [] // Include submission details
            ];

            return response()->json($chartData);

        } catch (\Exception $e) {
            \Log::error('Error in fetchClassProgressData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'school_id' => $schoolId ?? null,
                'class_id' => $classId ?? null,
                'submission_id' => $submissionId ?? null
            ]);

            return response()->json([
                'error' => 'An error occurred while fetching progress data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
