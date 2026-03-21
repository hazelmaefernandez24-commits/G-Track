<?php

namespace App\Http\Controllers;

use App\Models\GradeSubmission;
use App\Models\GradeSubmissionProof;
use App\Models\GradeSubmissionSubject;
use App\Models\PNUser;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{

    public function grades(Request $request)
    {
        $user = Auth::user();
        $filterKey = $request->query('filter_key');

        // Get the student's school information to determine grading system
        $studentSchool = null;
        $studentClass = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.class_id')
            ->join('schools', 'classes.school_id', '=', 'schools.school_id')
            ->where('class_student.user_id', $user->user_id)
            ->select('schools.*')
            ->first();

        if ($studentClass) {
            $studentSchool = $studentClass;
        }

        // For filter dropdown - only include submissions where student has actually submitted grades
        $filterOptions = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id)
                  ->whereNotNull('grade_submission_subject.grade')
                  ->where('grade_submission_subject.grade', '!=', '');
        })
        ->select(
            'semester',
            'term',
            'academic_year',
            DB::raw("CONCAT('Semester: ', semester, ' | Term: ', term, ' | Year: ', academic_year) AS filter_key")
        )
        ->distinct()
        ->orderBy('academic_year', 'desc')
        ->orderBy('semester', 'desc')
        ->orderBy('term', 'desc')
        ->pluck('filter_key')
        ->values()
        ->all();

        // Get all grade submissions for the user
        $gradeSubmissionsQuery = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id)
                  ->whereNotNull('grade_submission_subject.grade')
                  ->where('grade_submission_subject.grade', '!=', '');
        })
        ->with(['classModel'])
        ->orderBy('academic_year', 'desc')
        ->orderBy('semester', 'desc')
        ->orderBy('term', 'desc');

        // Apply submission filter if provided
        if ($filterKey) {
            // Parse the filter key to extract semester, term, and academic year
            $parts = explode(' | ', $filterKey);
            if (count($parts) === 3) {
                $semester = str_replace('Semester: ', '', $parts[0]);
                $term = str_replace('Term: ', '', $parts[1]);
                $academicYear = str_replace('Year: ', '', $parts[2]);

                $gradeSubmissionsQuery->where('semester', $semester)
                                    ->where('term', $term)
                                    ->where('academic_year', $academicYear);
            }
        }

        // Paginate the submissions (10 per page)
        $gradeSubmissions = $gradeSubmissionsQuery->paginate(10);

        // Load subjects with grades for each submission (user-specific) with pagination
        $gradeSubmissions->getCollection()->transform(function ($submission) use ($user, $request) {
            // Get pagination parameter for this specific submission
            $submissionPage = $request->get('submission_' . $submission->id . '_page', 1);

            // Get total count for this submission
            $totalSubjects = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $submission->id)
                ->where('grade_submission_subject.user_id', $user->user_id)
                ->whereNotNull('grade_submission_subject.grade')
                ->where('grade_submission_subject.grade', '!=', '')
                ->count();

            // Get paginated subjects for this submission
            $userSubjects = DB::table('grade_submission_subject')
                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                ->where('grade_submission_subject.grade_submission_id', $submission->id)
                ->where('grade_submission_subject.user_id', $user->user_id)
                ->whereNotNull('grade_submission_subject.grade')
                ->where('grade_submission_subject.grade', '!=', '')
                ->select(
                    'subjects.*',
                    'grade_submission_subject.grade',
                    'grade_submission_subject.status',
                    'grade_submission_subject.id as pivot_id'
                )
                ->offset(($submissionPage - 1) * 10)
                ->limit(10)
                ->get();

            // Convert to collection and add student_submission data
            $submission->subjects = $userSubjects->map(function ($subject) {
                $subject->student_submission = (object) [
                    'grade' => $subject->grade,
                    'status' => $subject->status,
                    'id' => $subject->pivot_id
                ];
                return $subject;
            });

            // Add pagination info to submission
            $submission->pagination = (object) [
                'current_page' => (int) $submissionPage,
                'total' => $totalSubjects,
                'per_page' => 10,
                'last_page' => max(1, ceil($totalSubjects / 10)),
                'has_more_pages' => $submissionPage < ceil($totalSubjects / 10),
                'has_previous_pages' => $submissionPage > 1
            ];

            return $submission;
        });

        // Get total grade status counts for the status cards (unfiltered)
        $statusCounts = [
            'pass' => 0,
            'fail' => 0,
            'inc' => 0,
            'nc' => 0,
            'dr' => 0
        ];

        // Get all subjects for the user (unfiltered) for status cards with school grading info
        $allSubjects = DB::table('grade_submission_subject')
            ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
            ->join('grade_submissions', 'grade_submission_subject.grade_submission_id', '=', 'grade_submissions.id')
            ->leftJoin('schools', 'grade_submissions.school_id', '=', 'schools.school_id')
            ->where('grade_submission_subject.user_id', $user->user_id)
            ->whereNotNull('grade_submission_subject.grade')
            ->where('grade_submission_subject.grade', '!=', '')
            ->select(
                'grade_submission_subject.status',
                'grade_submission_subject.grade',
                'schools.passing_grade_min',
                'schools.passing_grade_max'
            )
            ->get();

        // Count subjects by status using school's grading system
        foreach ($allSubjects as $subject) {
            $status = strtolower($subject->status ?? '');
            $grade = $subject->grade ?? null;

            // Only count approved grades
            if ($status !== 'approved') {
                continue;
            }

            // Get school's passing grade range (fallback to default if not available)
            $passingMin = $subject->passing_grade_min ?? ($studentSchool->passing_grade_min ?? 1.0);
            $passingMax = $subject->passing_grade_max ?? ($studentSchool->passing_grade_max ?? 3.0);

            if (is_numeric($grade)) {
                $gradeValue = floatval($grade);
                // Check if grade is within the school's passing range
                if ($gradeValue >= $passingMin && $gradeValue <= $passingMax) {
                    $statusCounts['pass']++;
                } else {
                    $statusCounts['fail']++;
                }
            } elseif (strtoupper($grade) === 'INC') {
                $statusCounts['inc']++;
            } elseif (strtoupper($grade) === 'NC') {
                $statusCounts['nc']++;
            } elseif (strtoupper($grade) === 'DR') {
                $statusCounts['dr']++;
            }
        }

        return view('student.grades', compact(
            'gradeSubmissions',
            'filterOptions',
            'filterKey',
            'statusCounts',
            'studentSchool'
        ))->with('title', 'My Grades');
    }

    protected function getSortedTerms()
    {
        $terms = DB::table('grade_submissions')
            ->distinct()
            ->pluck('term')
            ->filter()
            ->sortBy(function($term) {
                $order = [
                    'prelim' => 1,
                    'midterm' => 2,
                    'semi-final' => 3,
                    'final' => 4
                ];
                return $order[strtolower($term)] ?? 999;
            })
            ->values();
            
        return $terms;
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $filterKey = $request->query('filter_key');

        // Get all grade submissions for the user
        $gradeSubmissionsQuery = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id);
        })
        ->with([
            'classModel',
            'subjects',
            'students' => function($query) use ($user) {
                $query->where('grade_submission_subject.user_id', $user->user_id);
            },
            'proofs' => function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            }
        ])
        ->orderBy('created_at', 'desc');

        // Apply submission filter if provided
        if ($filterKey) {
            // Parse the filter key to extract semester, term, and academic year
            $parts = explode(' | ', $filterKey);
            if (count($parts) === 3) {
                $semester = str_replace('Semester: ', '', $parts[0]);
                $term = str_replace('Term: ', '', $parts[1]);
                $academicYear = str_replace('Year: ', '', $parts[2]);

                $gradeSubmissionsQuery->where('semester', $semester)
                                    ->where('term', $term)
                                    ->where('academic_year', $academicYear);
            }
        }

        $gradeSubmissions = $gradeSubmissionsQuery->get();

        // Gather all subjects for the user
        $allSubjects = collect();
        foreach ($gradeSubmissions as $submission) {
            foreach ($submission->students as $student) {
                if ($student->pivot) {
                    $allSubjects->push($student->pivot);
                }
            }
        }

        $statusCounts = [
            'pass' => 0,
            'fail' => 0,
            'inc' => 0,
            'nc' => 0,
            'dr' => 0
        ];

        // Get student's school information for grading system
        $studentSchool = null;
        $studentClass = DB::table('class_student')
            ->join('classes', 'class_student.class_id', '=', 'classes.class_id')
            ->join('schools', 'classes.school_id', '=', 'schools.school_id')
            ->where('class_student.user_id', $user->user_id)
            ->select('schools.*')
            ->first();

        if ($studentClass) {
            $studentSchool = $studentClass;
        }

        foreach ($allSubjects as $subject) {
            $status = strtolower($subject->status ?? '');
            $grade = $subject->grade ?? null;

            // Only count grades that have been approved by training
            if ($status !== 'approved') {
                continue;
            }

            if (is_numeric($grade)) {
                $gradeValue = floatval($grade);
                // Use school's grading system or fallback to default
                $passingMin = $studentSchool->passing_grade_min ?? 1.0;
                $passingMax = $studentSchool->passing_grade_max ?? 3.0;

                // Check if grade is within the school's passing range
                if ($gradeValue >= $passingMin && $gradeValue <= $passingMax) {
                    $statusCounts['pass']++;
                } else {
                    $statusCounts['fail']++;
                }
            } elseif (strtoupper($grade) === 'INC') {
                $statusCounts['inc']++;
            } elseif (strtoupper($grade) === 'NC') {
                $statusCounts['nc']++;
            } elseif (strtoupper($grade) === 'DR') {
                $statusCounts['dr']++;
            }
        }

        // For filter dropdown - only include submissions where student has actually submitted grades
        $filterOptions = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id)
                  ->whereNotNull('grade_submission_subject.grade')
                  ->where('grade_submission_subject.grade', '!=', '');
        })
        ->select(
            'semester',
            'term',
            'academic_year',
            DB::raw("CONCAT('Semester: ', semester, ' | Term: ', term, ' | Year: ', academic_year) AS filter_key")
        )
        ->distinct()
        ->orderBy('academic_year', 'desc')
        ->orderBy('semester', 'desc')
        ->orderBy('term', 'desc')
        ->pluck('filter_key')
        ->values()
        ->all();

        // Transform the data to avoid the ambiguous user_id issue
        $gradeSubmissions->each(function ($submission) use ($user) {
            $submission->subjects->each(function ($subject) use ($submission, $user) {
                $studentSubmission = DB::table('grade_submission_subject')
                    ->where('grade_submission_id', $submission->id)
                    ->where('user_id', $user->user_id)
                    ->where('subject_id', $subject->id)
                    ->first();
                $subject->student_submission = $studentSubmission;
            });
        });

        // Get approved subjects with grades for display
        $subjectsWithGrades = collect();
        foreach ($allSubjects as $subject) {
            $status = strtolower($subject->status ?? '');
            $grade = $subject->grade ?? null;

            // Only include approved grades
            if ($status === 'approved' && $grade !== null) {
                // Get subject details
                $subjectDetails = DB::table('subjects')
                    ->where('id', $subject->subject_id)
                    ->first();

                if ($subjectDetails) {
                    $subjectWithGrade = (object) [
                        'subject_code' => $subjectDetails->offer_code,
                        'subject_name' => $subjectDetails->name,
                        'grade' => $grade,
                        'status' => $status
                    ];
                    $subjectsWithGrades->push($subjectWithGrade);
                }
            }
        }

        return view('student.dashboard', compact(
            'gradeSubmissions',
            'filterOptions',
            'filterKey',
            'statusCounts',
            'subjectsWithGrades'
        ))->with('title', 'Student Dashboard');
    }

    public function showSubmissionForm($submissionId)
    {
        $user = Auth::user();

        // Fetch the grade submission and eager load classModel and students
        $gradeSubmission = GradeSubmission::where('id', $submissionId)
            ->whereHas('students', function($query) use ($user) {
                $query->where('grade_submission_subject.user_id', $user->user_id);
            })
            ->with([
                'classModel',
                'subjects',
                'students' => function($query) use ($user) {
                    $query->where('grade_submission_subject.user_id', $user->user_id);
                },
                'proofs' => function($query) use ($user) {
                    $query->where('user_id', $user->user_id);
                }
            ])
            ->firstOrFail();

        // Check if the submission is approved - if yes, don't allow editing
        if ($gradeSubmission->status === 'approved') {
            return redirect()->route('student.dashboard')->with('error', 'This submission has already been approved and cannot be modified.');
        }

        // Get all subjects and their grades for this student
        $subjects = DB::table('subjects')
            ->join('grade_submission_subject as gss', 'subjects.id', '=', 'gss.subject_id')
            ->where('gss.grade_submission_id', $submissionId)
            ->where('gss.user_id', $user->user_id)
            ->select('subjects.*', 'gss.grade', 'gss.status')
            ->get();

        // If no subjects found, try getting them directly from the grade submission
        if ($subjects->isEmpty()) {
            $subjects = $gradeSubmission->subjects;
        }

        // Get the latest proof if it exists
        $proof = $gradeSubmission->proofs->first();

        return view('student.submission_form', compact('gradeSubmission', 'subjects', 'proof'))->with('title', 'Submit Grades');
    }

    public function submitGrades(Request $request, $submissionId)
    {
        $user = Auth::user();

        \Log::info('Starting grade submission process:', [
            'submission_id' => $submissionId,
            'user_id' => $user->user_id
        ]);

        // Find the grade submission and verify the student is associated
        $gradeSubmission = GradeSubmission::where('id', $submissionId)
            ->whereHas('students', function($query) use ($user) {
                $query->where('grade_submission_subject.user_id', $user->user_id);
            })
            ->with(['proofs' => function($query) use ($user) {
                $query->where('user_id', $user->user_id);
            }])
            ->firstOrFail();

        // Check if submission is already approved
        if ($gradeSubmission->status === 'approved') {
            return redirect()->route('student.dashboard')->with('error', 'This submission has already been approved and cannot be modified.');
        }

        \Log::info('Found grade submission:', [
            'submission_id' => $gradeSubmission->id,
            'school_id' => $gradeSubmission->school_id,
            'class_id' => $gradeSubmission->class_id
        ]);

        // Enhanced validation with better error messages
        try {
            $validated = $request->validate([
                'grades' => 'required|array',
                'grades.*' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Check if it's a valid numeric grade (1.0-5.0)
                        if (is_numeric($value)) {
                            $value = floatval($value);
                            if ($value < 1.0 || $value > 5.0) {
                                $fail('The numeric grade must be between 1.0 and 5.0.');
                            }
                        }
                        // Check if it's a valid special grade (INC, NC, DR)
                        else if (!in_array(strtoupper($value), ['INC', 'NC', 'DR'])) {
                            $fail('The grade must be between 1.0-5.0 or one of: INC, NC, DR.');
                        }
                    },
                ],
                'proof' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['proof']),
                'file_info' => $request->hasFile('proof') ? [
                    'original_name' => $request->file('proof')->getClientOriginalName(),
                    'size' => $request->file('proof')->getSize(),
                    'mime_type' => $request->file('proof')->getMimeType(),
                    'extension' => $request->file('proof')->getClientOriginalExtension(),
                ] : 'No file uploaded'
            ]);

            return back()->withErrors($e->errors())->withInput()->with('error', 'Please check your file and try again. Make sure the file is under 10MB and is a valid format (PDF, DOC, DOCX, JPG, JPEG, PNG).');
        }

        \Log::info('Validated grades:', [
            'grades' => $validated['grades']
        ]);

        try {
            DB::beginTransaction();

            // Get the grade submission
            $gradeSubmission = GradeSubmission::findOrFail($submissionId);
            
            // Update grades and status in the pivot table
            foreach ($validated['grades'] as $subjectId => $grade) {
                // If it's a numeric grade and doesn't have a decimal point, add .0
                if (is_numeric($grade) && strpos($grade, '.') === false) {
                    $grade = floatval($grade) . '.0';
                }
                
                // Update or create the grade submission subject record
                $result = DB::table('grade_submission_subject')
                    ->updateOrInsert(
                        [
                            'grade_submission_id' => $submissionId,
                            'subject_id' => $subjectId,
                            'user_id' => $user->user_id
                        ],
                        [
                            'grade' => $grade,
                            'status' => 'submitted',
                            'updated_at' => now()
                        ]
                    );
            }
            
            // Update the main grade submission status to 'submitted' if it was 'rejected' or 'pending'
            if (in_array($gradeSubmission->status, ['rejected', 'pending', 'submitted'])) {
                $gradeSubmission->update(['status' => 'submitted']);
                
                // Also update all related grade_submission_subject records to 'submitted'
                DB::table('grade_submission_subject')
                    ->where('grade_submission_id', $submissionId)
                    ->where('user_id', $user->user_id)
                    ->update(['status' => 'submitted']);
            }

            // Log the submission
            \Log::info('Grade submission updated:', [
                'submission_id' => $submissionId,
                'user_id' => $user->user_id,
                'subjects_updated' => count($validated['grades'])
            ]);

            // Handle file upload - store in student-specific folder
            $file = $request->file('proof');

            if (!$file || !$file->isValid()) {
                throw new \Exception('Invalid file upload. Please try again.');
            }

            \Log::info('File upload details:', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError()
            ]);

            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

            // Get student details for folder name
            $student = PNUser::with('studentDetail')->findOrFail($user->user_id);
            $studentId = $student->studentDetail->student_id ?? $student->user_id; // Fallback to user_id if student_id is not available
            $folderName = $student->user_lname . '_' . $studentId;
            $folderName = preg_replace('/[^a-zA-Z0-9_]/', '_', $folderName); // Sanitize folder name

            // Ensure the directory exists
            $fullPath = storage_path("app/public/proofs/{$folderName}");
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            $filePath = $file->storeAs("proofs/{$folderName}", $fileName, 'public');

            if (!$filePath) {
                throw new \Exception('Failed to store file. Please try again.');
            }

            // Create or update the proof record
            $proof = GradeSubmissionProof::updateOrCreate(
                [
                    'grade_submission_id' => $submissionId,
                    'user_id' => $user->user_id
                ],
                [
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'status' => 'pending'
                ]
            );

            \Log::info('Proof uploaded:', [
                'proof_id' => $proof->id,
                'file_path' => $filePath
            ]);

            // Verify the grades were stored
            $storedGrades = DB::table('grade_submission_subject')
                ->where('grade_submission_id', $submissionId)
                ->where('user_id', $user->user_id)
                ->get();

            \Log::info('Stored grades verification:', [
                'count' => $storedGrades->count(),
                'grades' => $storedGrades->toArray()
            ]);

            DB::commit();

            return redirect()->route('student.dashboard')->with('success', 'Grades and proof submitted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            \Log::error('Database error submitting grades: ' . $e->getMessage());
            return redirect()->route('student.dashboard')->with('error', 'Database error: ' . $e->getMessage());
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error submitting grades: ' . $e->getMessage());
            return redirect()->route('student.dashboard')->with('error', 'Validation error: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error submitting grades: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'submission_id' => $submissionId,
                'stack_trace' => $e->getTraceAsString()
            ]);

            // Provide more specific error messages
            $errorMessage = 'An error occurred while submitting your grades.';

            if (strpos($e->getMessage(), 'file') !== false || strpos($e->getMessage(), 'upload') !== false) {
                $errorMessage = 'Failed to upload proof file. Please check your file and try again. Make sure the file is under 10MB and is a valid format.';
            } elseif (strpos($e->getMessage(), 'validation') !== false) {
                $errorMessage = 'Please check your grades and proof file. Make sure all fields are filled correctly.';
            } elseif (strpos($e->getMessage(), 'database') !== false) {
                $errorMessage = 'Database error occurred. Please try again later.';
            }

            return back()->withInput()->with('error', $errorMessage);
        }
    }

    public function viewSubmission($submissionId)
    {
        $user = Auth::user();

        // Get the grade submission
        $gradeSubmission = GradeSubmission::where('id', $submissionId)
            ->whereHas('students', function($query) use ($user) {
                $query->where('grade_submission_subject.user_id', $user->user_id);
            })
            ->with([
                'classModel',
                'subjects'
            ])
            ->firstOrFail();

        // Get the student's subject entries for this submission
        $studentSubjectEntries = DB::table('grade_submission_subject')
            ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
            ->where('grade_submission_id', $submissionId)
            ->where('user_id', $user->user_id)
            ->select(
                'subjects.name as subject_name',
                'grade_submission_subject.grade',
                'grade_submission_subject.status'
            )
            ->get();

        // Get the proof for this submission
        $proof = GradeSubmissionProof::where('grade_submission_id', $submissionId)
            ->where('user_id', $user->user_id)
            ->first();

        return view('student.view_submission', compact('gradeSubmission', 'studentSubjectEntries', 'proof'))->with('title', 'View Submission');
    }

    public function submissionsList(Request $request)
    {
        $user = Auth::user();
        $filterKey = $request->query('filter_key');

        $gradeSubmissionsQuery = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id);
        })
        ->with([
            'classModel',
            'subjects',
            'students' => function($query) use ($user) {
                $query->where('grade_submission_subject.user_id', $user->user_id);
            }
        ])
        ->orderBy('created_at', 'desc');

        if ($filterKey) {
            $gradeSubmissionsQuery->where(DB::raw("CONCAT(semester, ' ', term, ' ', academic_year)"), $filterKey);
        }

        $gradeSubmissions = $gradeSubmissionsQuery->get();

        // For filter dropdown
        $filterOptions = GradeSubmission::whereHas('students', function($query) use ($user) {
            $query->where('grade_submission_subject.user_id', $user->user_id);
        })
        ->select(DB::raw("CONCAT(semester, ' ', term, ' ', academic_year) AS filter_key"))
        ->distinct()
        ->pluck('filter_key')
        ->sortDesc()
        ->values()
        ->all();

        return view('student.grade_submissions_list', compact('gradeSubmissions', 'filterOptions', 'filterKey'))->with('title', 'Grade Submissions');
    }
} 