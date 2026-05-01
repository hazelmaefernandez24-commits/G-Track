<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Support\Facades\Hash;

class StudentManagementController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return view('admin.students.index', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|unique:students,student_id',
            'name' => 'required',
            'email' => 'required|email|unique:students,email',
            'class' => 'required',
            'gender' => 'required',
            'contact' => 'required',
            'password' => 'required|min:6',
        ]);

        \DB::transaction(function () use ($request) {
            $student = Student::create($request->only(['student_id', 'name', 'email', 'class', 'gender', 'contact']));

            StudentAuth::create([
                'student_id' => $student->student_id,
                'email' => $student->email,
                'password' => Hash::make($request->password),
            ]);
        });

        return redirect()->back()->with('success', 'Student added successfully.');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'student_id' => 'required|unique:students,student_id,' . $id,
            'name' => 'required',
            'email' => 'required|email|unique:students,email,' . $id,
            'class' => 'required',
            'gender' => 'required',
            'contact' => 'required',
        ]);

        \DB::transaction(function () use ($request, $student) {
            $student->update($request->only(['student_id', 'name', 'email', 'class', 'gender', 'contact']));

            if ($request->filled('password') || $student->wasChanged('email')) {
                $studentAuth = StudentAuth::where('student_id', $student->student_id)->first();
                if ($studentAuth) {
                    $updateData = [];
                    if ($request->filled('password')) $updateData['password'] = Hash::make($request->password);
                    if ($student->wasChanged('email')) $updateData['email'] = $student->email;
                    $studentAuth->update($updateData);
                } else {
                    StudentAuth::create([
                        'student_id' => $student->student_id,
                        'email' => $student->email,
                        'password' => Hash::make($request->password ?? 'password123'),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        
        \DB::transaction(function () use ($student) {
            StudentAuth::where('student_id', $student->student_id)->delete();
            $student->delete();
        });

        return redirect()->back()->with('success', 'Student deleted successfully.');
    }
}
