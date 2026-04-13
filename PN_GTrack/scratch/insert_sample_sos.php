<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

$studentId = 'STU2026009';
$student = Student::where('student_id', $studentId)->first();

if ($student) {
    // 1. Create the SOS Notification
    $notificationId = DB::table('notifications')->insertGetId([
        'student_id' => $student->id,
        'class' => $student->class,
        'type' => 'sos',
        'sender_type' => 'student',
        'message' => 'EMERGENCY: I need immediate assistance near the main gate. My device is running low on battery.',
        'latitude' => '10.3157',
        'longitude' => '123.8854',
        'location' => 'University Main Gate, Cebu City',
        'battery_level' => 15,
        'signal_status' => 'Strong',
        'read' => false,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // 2. Update the Student's Status
    $student->sos_status = 'help';
    $student->last_update = now()->format('M d, Y h:i A');
    $student->save();

    echo "Sample SOS alert inserted successfully!\n";
    echo "Student: " . $student->name . "\n";
    echo "Notification ID: " . $notificationId . "\n";
} else {
    echo "Error: Student with ID $studentId not found.\n";
}
