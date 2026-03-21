<?php
namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\GradeSubmission;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Student;

class GradeSubmissionController extends Controller
{


    public function create(Request $request)
    {
        $schools = School::all();
        $classes = [];
        $subjects = [];
    
        if ($request->has('school_id')) {
            $classes = ClassModel::where('school_id', $request->school_id)->get();
            $subjects = Subject::where('school_id', $request->school_id)->get();
        }
    
        return view('training.grade-submissions.create', compact('schools', 'classes', 'subjects'));
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|string|exists:schools,school_id',
            'class_id' => 'required|string|exists:classes,class_id',
            'semester' => 'required|string',
            'term' => 'required|string',
            'academic_year' => 'required|string',
            'subject_ids' => 'required|array',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Create the grade submission
            $gradeSubmission = GradeSubmission::create([
                'school_id' => $validated['school_id'],
                'class_id' => $validated['class_id'],
                'semester' => $validated['semester'],
                'term' => $validated['term'],
                'academic_year' => $validated['academic_year'],
            ]);
    
            // Attach subjects to the grade submission
            $gradeSubmission->subjects()->attach($validated['subject_ids']);
    
            // Fetch all students in the selected class
            $students = Student::where('class_id', $validated['class_id'])->get();
    
            // Create notifications for all students
            foreach ($students as $student) {
                Notification::create([
                    'user_id' => $student->user_id,
                    'grade_submission_id' => $gradeSubmission->id,
                    'is_read' => false,
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('training.grade-submissions.index')
                ->with('success', 'Grade submission created successfully, and students have been notified.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating grade submission: ' . $e->getMessage());
        }
    }









    public function index()
    {
        // Fetch all grade submissions from the database
        $gradeSubmissions = GradeSubmission::with(['students', 'subjects'])->get();

        return view('training.grade-submissions.index', compact('gradeSubmissions'));
    }

    public function monitor()
    {
        // Fetch all grade submissions from the database
        $gradeSubmissions = GradeSubmission::with(['students', 'subjects'])->get();

        // Get all schools
        $schools = School::all();
        
        // Get classes grouped by school
        $classesBySchool = $schools->map(function($school) {
            return ClassModel::where('school_id', $school->school_id)
                ->select('class_id', 'class_name')
                ->get()
                ->pluck('class_name', 'class_id');
        });

        // Get unique filter options (semester, term, academic year combinations)
        $filterOptions = $gradeSubmissions->map(function($submission) {
            return $submission->semester . ' ' . $submission->term . ' ' . $submission->academic_year;
        })->unique()->sortDesc()->toArray();

        return view('training.grade-submissions.monitor', compact(
            'gradeSubmissions',
            'schools',
            'classesBySchool',
            'filterOptions'
        ));
    }

    public function getSubjectsBySchoolAndClass(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer|exists:schools,school_id',
            'class_id' => 'required|integer|exists:classes,class_id',
        ]);
        
        // Fetch subjects that belong to the selected school and are associated with the selected class
        $subjects = Subject::where('school_id', $request->school_id)
            ->whereHas('classes', function ($query) use ($request) {
                $query->where('class_id', $request->class_id);
            })
            ->get(['id', 'name', 'offer_code']); // Fetch only necessary fields
    
        return response()->json($subjects);
    }
}
