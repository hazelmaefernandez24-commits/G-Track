<?php

use App\Models\Student;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentId = 'STU2026009';
$student = Student::where('student_id', $studentId)->first();

if ($student) {
    DB::table('notifications')->insert([
        'student_id' => $student->id,
        'class' => $student->class,
        'type' => 'student_message',
        'sender_type' => 'student',
        'message' => 'Hello Admin, I have a question about my tracking status.',
        'read' => false,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Inserted successfully for student: " . $student->name . " (ID: " . $student->id . ")\n";
} else {
    echo "Error: Student with ID $studentId not found.\n";
}
