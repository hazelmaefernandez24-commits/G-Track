<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\StudentDetail;
use App\Models\Intervention;
use App\Models\Subject;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EducatorController extends Controller
{
    public function dashboard()
    {
        // Similar logic as Training, but here we fetch the data for Educator's view
        $schoolsCount = \App\Models\School::count();
        $classesCount = \App\Models\ClassModel::count();
        $studentsCount = PNUser::where('user_role', 'Student')->where('status', 'active')->count();
        
        // Get gender distribution from student_details table
        $maleCount = \App\Models\StudentDetail::where('gender', 'Male')->count();
        $femaleCount = \App\Models\StudentDetail::where('gender', 'Female')->count();
        
        // Get students count by batch
        $batchCounts = StudentDetail::select('batch')
            ->selectRaw('count(*) as count')
            ->groupBy('batch')
            ->orderBy('batch')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->batch => $item->count];
            });

        // Get gender distribution by batch
        $genderByBatch = [];
        $studentsByGenderByBatch = [];
        foreach ($batchCounts->keys() as $batch) {
            $male = StudentDetail::where('batch', $batch)
                ->where('gender', 'Male')
                ->count();
            $female = StudentDetail::where('batch', $batch)
                ->where('gender', 'Female')
                ->count();
            $genderByBatch[$batch] = [
                'male' => $male,
                'female' => $female
            ];
            $studentsByGenderByBatch[$batch] = [
                'male' => $male,
                'female' => $female
            ];
        }

        // Get recent items for educator dashboard
        $recentStudents = PNUser::where('user_role', 'Student')
            ->where('status', 'active')
            ->with('studentDetail')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentSchools = School::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentClasses = ClassModel::with('school')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('educator.dashboard', [
            'title' => 'Educator Dashboard',
            'schoolsCount' => $schoolsCount,
            'classesCount' => $classesCount,
            'studentsCount' => $studentsCount,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'batchCounts' => $batchCounts,
            'genderByBatch' => $genderByBatch,
            'studentsByGenderByBatch' => $studentsByGenderByBatch,
            'recentStudents' => $recentStudents,
            'recentSchools' => $recentSchools,
            'recentClasses' => $recentClasses
        ]);
    }

    


    public function studentsInfo(Request $request)
{
    // Get all unique batch numbers to display in the dropdown
    $batches = StudentDetail::distinct()->pluck('batch');
    
    // Get students, filter by batch if a batch is selected, or filter by N/A student_id if selected
    $students = PNUser::where('user_role', 'Student')
        ->where('status', 'active')
        ->with('studentDetail')
        ->when($request->has('batch') && $request->batch != '', function ($query) use ($request) {
            if ($request->batch === 'N/A') {
                // Filter for students with no student_id or empty student_id
                return $query->whereDoesntHave('studentDetail', function($q) {
                    $q->whereNotNull('student_id')->where('student_id', '!=', '');
                });
            } else {
                // Filter by batch
                return $query->whereHas('studentDetail', function ($q) use ($request) {
                    $q->where('batch', $request->batch);
                });
            }
        })
        ->paginate(10);

    // Get the role of the currently logged-in user
    $userRole = Auth::user()->user_role;

    // Return the educator version of the student info view
    return view('educator.students-info', compact('students', 'batches', 'userRole'));
}



public function index(Request $request)
{
    $batches = StudentDetail::distinct()->pluck('batch');

    $students = PNUser::where('user_role', 'Student')
        ->where('status', 'active')
        ->with('studentDetail')
        ->when($request->has('batch') && $request->batch != '', function ($query) use ($request) {
            return $query->whereHas('studentDetail', function ($q) use ($request) {
                $q->where('batch', $request->batch);
            });
        })
        ->paginate(10);

    $userRole = Auth::user()->user_role;

    return view('educator.students-info', compact('students', 'batches', 'userRole'))->with('title', 'Students Info');
}

public function viewStudent($user_id)
{
    $student = PNUser::where('user_id', $user_id)
        ->where('user_role', 'Student')
        ->with('studentDetail')
        ->firstOrFail();

    return view('educator.view-student', compact('student'));
}

public function edit($user_id)
{
    $student = PNUser::with('studentDetail')
        ->where('user_id', $user_id)
        ->firstOrFail();
    return view('educator.edit-student', compact('student'));
}

public function update(Request $request, $user_id)
{
    $student = PNUser::where('user_id', $user_id)->firstOrFail();
    $student = PNUser::with('studentDetail')->where('user_id', $user_id)->firstOrFail();

    $request->validate([
        'batch' => 'required|digits:4',
        'gender' => 'required|in:Male,Female',
        'user_email' => 'required|email|unique:pnph_users,user_email,' . $user_id . ',user_id',
    ]);

    // Update the student details
    $student->update($request->only([
        'user_lname',
        'user_fname',
        'user_mInitial',
        'user_suffix',
        'user_email',
    ]));

    $student->studentDetail()->updateOrCreate(
        ['user_id' => $student->user_id],
        [
            'batch' => $request->batch,
            'group' => $request->group,
            'student_number' => $request->student_number,
            'training_code' => $request->training_code,
            'student_id' => $request->batch . $request->group . $request->student_number . $request->training_code,
            'gender' => $request->gender,
        ]
    );

    return redirect()->route('educator.students.index')->with('success', 'Student updated successfully.');
}

    /**
     * Display the intervention management page
     */
    public function intervention()
    {
        // Get all interventions with related data
        $interventions = $this->getInterventionData();

        return view('educator.intervention', compact('interventions'))->with('title', 'Intervention Status');
    }

    /**
     * Show the intervention update form
     */
    public function interventionUpdate($id)
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
    public function interventionStore(Request $request, $id)
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
     * Get intervention data based on subjects that need intervention
     */
    private function getInterventionData()
    {
        // Get all grade submissions with subjects that need intervention
        $gradeSubmissions = GradeSubmission::with(['school', 'classModel', 'subjects'])
            ->where('status', 'approved')
            ->get();

        $interventionData = [];

        foreach ($gradeSubmissions as $submission) {
            $school = $submission->school;

            if (!$school) continue;

            // Get grades for this submission grouped by subject
            $grades = GradeSubmissionSubject::where('grade_submission_id', $submission->id)
                ->with(['subject', 'user'])
                ->get()
                ->groupBy('subject_id');

            foreach ($grades as $subjectId => $subjectGrades) {
                $subject = $subjectGrades->first()->subject;
                if (!$subject) continue;

                $passed = 0;
                $failed = 0;
                $inc = 0;
                $dr = 0;
                $nc = 0;
                $totalStudents = 0;

                foreach ($subjectGrades as $grade) {
                    $totalStudents++;
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

                // Only include subjects that need intervention
                $needsIntervention = ($failed > 0 || $inc > 0 || $dr > 0 || $nc > 0);

                if ($needsIntervention && $totalStudents > 0) {
                    $studentsNeedingIntervention = $failed + $inc + $dr + $nc;

                    // Check if intervention already exists
                    $existingIntervention = Intervention::where([
                        'subject_id' => $subjectId,
                        'school_id' => $submission->school_id,
                        'class_id' => $submission->class_id,
                        'grade_submission_id' => $submission->id
                    ])->first();

                    if (!$existingIntervention) {
                        // Create new intervention record
                        $existingIntervention = Intervention::create([
                            'subject_id' => $subjectId,
                            'school_id' => $submission->school_id,
                            'class_id' => $submission->class_id,
                            'grade_submission_id' => $submission->id,
                            'student_count' => $studentsNeedingIntervention,
                            'status' => 'pending',
                            'remarks' => null, // Leave empty until educator updates
                            'created_by' => Auth::user()->user_id
                        ]);
                    } else {
                        // Update student count but preserve existing intervention details
                        // Only update remarks if it's still the default intervention reason
                        $updateData = [
                            'student_count' => $studentsNeedingIntervention,
                            'updated_by' => Auth::user()->user_id
                        ];

                        // Don't update remarks - preserve educator-inputted intervention details
                        // Remarks should only be updated by educators through the update form

                        $existingIntervention->update($updateData);
                    }

                    $interventionData[] = $existingIntervention->load(['subject', 'school', 'classModel', 'educatorAssigned']);
                }
            }
        }

        return collect($interventionData)->sortBy('created_at');
    }
}

