<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentAuth;
use App\Models\BatchClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentManagementController extends Controller
{
    private const CSV_HEADERS = [
        'first_name',
        'middle_initial',
        'last_name',
        'email',
        'class',
        'gender',
        'contact',
        'password',
    ];

    public function index()
    {
        $students = Student::all();
        $classes = BatchClass::orderBy('year', 'asc')->get();
        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = BatchClass::orderBy('year', 'asc')->get();
        return view('admin.students.form', compact('classes'));
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $classes = BatchClass::orderBy('year', 'asc')->get();

        return view('admin.students.form', compact('student', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email',
            'class' => 'required|exists:student_classes,year',
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

        $this->createStudentRecord($validated);

        return redirect()->route('students.index')->with('success', 'Student added successfully.');
    }

    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'students_csv' => ['required', 'file', 'max:5120'],
        ]);
        if ($validator->fails()) {
            return redirect()->route('students.create')
                ->withErrors($validator)
                ->withInput();
        }

        $file = $request->file('students_csv');
        if (! in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return redirect()->route('students.create')
                ->withErrors(['students_csv' => 'Upload a CSV file with the required columns listed below.']);
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return redirect()->route('students.create')
                ->withErrors(['students_csv' => 'The uploaded CSV file could not be read.']);
        }

        $headers = fgetcsv($handle, 0, ',', '"', '');
        if ($headers === false) {
            fclose($handle);

            return redirect()->route('students.create')
                ->withErrors(['students_csv' => 'The CSV file is empty or missing its header row.']);
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(static fn ($header) => strtolower(trim((string) $header)), $headers);

        if (
            count($headers) !== count(self::CSV_HEADERS)
            || count(array_unique($headers)) !== count(self::CSV_HEADERS)
            || array_diff(self::CSV_HEADERS, $headers)
        ) {
            fclose($handle);

            return redirect()->route('students.create')
                ->withErrors(['students_csv' => 'The CSV header must contain these columns exactly once: ' . implode(', ', self::CSV_HEADERS) . '.']);
        }

        $rows = [];
        $rowNumber = 1;
        while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rowNumber++;
            if (count(array_filter($values, static fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            if (count($rows) >= 1000) {
                fclose($handle);

                return redirect()->route('students.create')
                    ->withErrors(['students_csv' => 'A CSV import can contain no more than 1,000 student rows. No students were imported.']);
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'values' => $values,
            ];
        }
        fclose($handle);

        if ($rows === []) {
            return redirect()->route('students.create')
                ->withErrors(['students_csv' => 'The CSV file does not contain any student rows.']);
        }

        $imported = 0;
        $rowErrors = [];
        $seenEmails = [];

        foreach ($rows as $row) {
            $values = $row['values'];
            if (count($values) !== count($headers)) {
                $rowErrors[] = 'Row ' . $row['row_number'] . ': The number of values does not match the header.';
                continue;
            }

            $data = array_combine($headers, $values);
            foreach (['first_name', 'middle_initial', 'last_name', 'email', 'class', 'gender', 'contact'] as $field) {
                $data[$field] = trim((string) ($data[$field] ?? ''));
            }
            $data['middle_initial'] = $data['middle_initial'] === '' ? null : $data['middle_initial'];

            $emailKey = strtolower($data['email']);
            $duplicateEmail = $emailKey !== '' && isset($seenEmails[$emailKey]);
            if ($emailKey !== '') {
                $seenEmails[$emailKey] = true;
            }

            $validator = Validator::make($data, [
                'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
                'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
                'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
                'email' => ['required', 'email:rfc', 'unique:students,email', 'unique:student_auths,email'],
                'class' => ['required', 'exists:student_classes,year'],
                'gender' => ['required', 'in:Male,Female'],
                'contact' => ['required', 'digits:11', 'regex:/^09\d{9}$/'],
                'password' => ['required', 'string', 'min:6'],
            ]);

            $errors = $validator->errors()->all();
            if ($duplicateEmail) {
                $errors[] = 'Email is duplicated in this CSV file.';
            }
            if ($errors !== []) {
                $rowErrors[] = 'Row ' . $row['row_number'] . ': ' . implode(' ', $errors);
                continue;
            }

            $this->createStudentRecord($data);
            $imported++;
        }

        return redirect()->route('students.create')->with('csv_import', [
            'imported' => $imported,
            'skipped' => count($rowErrors),
            'errors' => $rowErrors,
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:students,email,' . $id,
            'class' => 'required|exists:student_classes,year',
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
            $class = BatchClass::where('year', $request->class)->firstOrFail();

            $student->update(array_merge(
                $request->only(['email', 'gender', 'contact']),
                [
                    'student_class_id' => $class->id,
                    'first_name' => $request->first_name,
                    'middle_initial' => $request->middle_initial,
                    'last_name' => $request->last_name,
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

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
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

    private function createStudentRecord(array $data): void
    {
        DB::transaction(function () use ($data) {
            $middleInitial = $data['middle_initial'] ?? null;
            $class = BatchClass::where('year', $data['class'])->firstOrFail();

            $student = Student::create(array_merge(
                array_intersect_key($data, array_flip(['email', 'gender', 'contact'])),
                [
                    'student_id' => 'PENDING-' . Str::uuid(),
                    'student_class_id' => $class->id,
                    'first_name' => $data['first_name'],
                    'middle_initial' => $middleInitial,
                    'last_name' => $data['last_name'],
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
                'password' => Hash::make($data['password']),
            ]);
        });
    }

    public function history($id)
    {
        $student = Student::findOrFail($id);
        $locations = $student->locations()->orderBy('recorded_at', 'desc')->get();
        $sosCount = \App\Models\SosAlert::where('status', '!=', 'resolved')->count();
        
        return view('history', compact('student', 'locations', 'sosCount'));
    }
}
