<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\StudentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{

    public function dashboard()
    {
        $schoolsCount = \App\Models\School::count();
        $classesCount = \App\Models\ClassModel::count();
        
        // Get active students count only (consistent with educator dashboard)
        $studentsCount = PNUser::where('user_role', 'Student')
            ->where('status', 'active')
            ->count();

        // Get gender distribution from student_details table (only active students)
        $maleCount = \App\Models\StudentDetail::whereHas('user', function($query) {
                $query->where('user_role', 'Student')->where('status', 'active');
            })
            ->where('gender', 'Male')
            ->count();
        $femaleCount = \App\Models\StudentDetail::whereHas('user', function($query) {
                $query->where('user_role', 'Student')->where('status', 'active');
            })
            ->where('gender', 'Female')
            ->count();
        
        // Get students count by batch (only active students)
        $batchCounts = StudentDetail::select('batch')
            ->selectRaw('count(*) as count')
            ->whereHas('user', function($query) {
                $query->where('user_role', 'Student')->where('status', 'active');
            })
            ->groupBy('batch')
            ->orderBy('batch')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->batch => $item->count];
            });

        // Get gender distribution by batch (only active students)
        $genderByBatch = [];
        $studentsByGenderByBatch = [];
        foreach ($batchCounts->keys() as $batch) {
            $male = StudentDetail::where('batch', $batch)
                ->where('gender', 'Male')
                ->whereHas('user', function($query) {
                    $query->where('user_role', 'Student')->where('status', 'active');
                })
                ->count();
            $female = StudentDetail::where('batch', $batch)
                ->where('gender', 'Female')
                ->whereHas('user', function($query) {
                    $query->where('user_role', 'Student')->where('status', 'active');
                })
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

        // Get recent items
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

        return view('training.dashboard', [
            'title' => 'Training Dashboard',
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


    public function index(Request $request)
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
    // Pass the role to the view to conditionally show "Edit" button
    $userRole = Auth::user()->user_role;

    return view('training.students-info', compact('students', 'batches', 'userRole'))->with('title', 'Students Info');
}





    public function edit($user_id)
    {
        $student = PNUser::with('studentDetail')
            ->where('user_id', $user_id)
            ->firstOrFail();
        return view('training.edit-student', compact('student'));
    }




    public function view($user_id)
    {
        $student = PNUser::with('studentDetail')
            ->where('user_id', $user_id)
            ->firstOrFail();

            
        return view('training.view-student', compact('student'));
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

        // ajdaijbdawi

        // diri na part ha

        // Generate the student ID
        $studentId = $request->batch . $request->group . $request->student_number . $request->training_code;

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
    
        return redirect()->route('training.students.index')->with('success', 'Student updated successfully.');
    }






    public function destroy($user_id)
    {
        try {
            $user = PNUser::findOrFail($user_id);
            $user->update(['status' => 'inactive']);
            return redirect()->route('training.students.index')
                ->with('success', 'Student deactivated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deactivating student: ' . $e->getMessage());
        }
    }









    public function getStudentsList()
    {
        try {
            $students = PNUser::where('user_role', 'Student')
                ->where('status', 'active')
                ->with('studentDetail')
                ->get()
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
                })
                ->filter(function ($student) {
                    return $student['batch'] !== null;
                });

            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}









