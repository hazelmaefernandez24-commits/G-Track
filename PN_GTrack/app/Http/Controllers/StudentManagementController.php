<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentAuth;
use App\Models\BatchClass;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentManagementController extends Controller
{
    public function index()
    {
        $students = Student::all();
        $classes = BatchClass::orderBy('name', 'asc')->get();
        return view('admin.students.index', compact('students', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email',
            'class' => 'required|exists:student_classes,name',
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
                $request->only(['email', 'class', 'gender', 'contact']),
                [
                    'student_id' => 'PENDING-' . Str::uuid(),
                    'first_name' => $request->first_name,
                    'middle_initial' => $request->middle_initial,
                    'last_name' => $request->last_name,
                    'name' => $fullName,
                ]
            ));

            $classCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $student->class) ?? '') ?: 'CLASS';
            $sequence = $student->id;
            $studentId = 'STU' . $classCode . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            while (Student::where('student_id', $studentId)->exists()) {
                $studentId = 'STU' . $classCode . str_pad((string) ++$sequence, 3, '0', STR_PAD_LEFT);
            }

            $student->update(['student_id' => $studentId]);

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
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email,' . $id,
            'class' => 'required|exists:student_classes,name',
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
                $request->only(['email', 'class', 'gender', 'contact']),
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
        $sosCount = \App\Models\Notification::where('type', 'sos')->where('status', '!=', 'resolved')->withValidVideo()->count();
        
        return view('history', compact('student', 'locations', 'sosCount'));
    }
}
