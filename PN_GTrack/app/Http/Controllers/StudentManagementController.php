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
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email',
            'class' => 'required',
            'gender' => 'required',
            'contact' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
            'password' => 'required|min:6|confirmed',
        ], [
            'first_name.regex' => 'First name must contain letters only.',
            'middle_initial.regex' => 'Middle initial must contain letters only.',
            'last_name.regex' => 'Last name must contain letters only.',
            'email.email' => 'Please enter a valid email address.',
            'contact.digits' => 'Contact number must be valid',
            'contact.regex' => 'Contact number must be valid',
           
        ]);

        \DB::transaction(function () use ($request) {
            $fullName = trim($request->first_name . ($request->middle_initial ? ' ' . $request->middle_initial . '.' : '') . ' ' . $request->last_name);

            $student = Student::create(array_merge(
                $request->only(['student_id', 'email', 'class', 'gender', 'contact']),
                [
                    'first_name' => $request->first_name,
                    'middle_initial' => $request->middle_initial,
                    'last_name' => $request->last_name,
                    'name' => $fullName,
                ]
            ));

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
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email,' . $id,
            'class' => 'required',
            'gender' => 'required',
            'contact' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
            'current_password' => 'nullable|required_with:new_password|min:6',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'first_name.regex' => 'First name must contain letters only.',
            'middle_initial.regex' => 'Middle initial must contain letters only.',
            'last_name.regex' => 'Last name must contain letters only.',
            'email.email' => 'Please enter a valid email address.',
           'contact.digits' => 'Contact number must be valid',
           'contact.regex' => 'Contact number must be valid',
        ]);

        if ($request->filled('new_password')) {
            if (! $request->filled('current_password')) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is required to change password.'])->withInput();
            }

            $studentAuth = StudentAuth::where('student_id', $student->student_id)->first();
            if (! $studentAuth || ! Hash::check($request->current_password, $studentAuth->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
        }

        \DB::transaction(function () use ($request, $student) {
            $fullName = trim($request->first_name . ($request->middle_initial ? ' ' . $request->middle_initial . '.' : '') . ' ' . $request->last_name);

            $student->update(array_merge(
                $request->only(['student_id', 'email', 'class', 'gender', 'contact']),
                [
                    'first_name' => $request->first_name,
                    'middle_initial' => $request->middle_initial,
                    'last_name' => $request->last_name,
                    'name' => $fullName,
                ]
            ));

            if ($request->filled('new_password') || $student->wasChanged('email')) {
                $studentAuth = StudentAuth::where('student_id', $student->student_id)->first();
                if ($studentAuth) {
                    $updateData = [];
                    if ($request->filled('new_password')) $updateData['password'] = Hash::make($request->new_password);
                    if ($student->wasChanged('email')) $updateData['email'] = $student->email;
                    $studentAuth->update($updateData);
                } else {
                    StudentAuth::create([
                        'student_id' => $student->student_id,
                        'email' => $student->email,
                        'password' => Hash::make($request->new_password ?? 'password123'),
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

    public function history($id)
    {
        $student = Student::findOrFail($id);
        $locations = $student->locations()->orderBy('recorded_at', 'desc')->get();
        $sosCount = \DB::table('notifications')->where('type', 'sos')->where('status', '!=', 'resolved')->count();
        
        return view('history', compact('student', 'locations', 'sosCount'));
    }
}
