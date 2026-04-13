<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\Student;

$studentId = 'STU2026009';
$student = Student::where('student_id', $studentId)->first();

if (!$student) {
    echo "Student not found: $studentId\n";
    exit;
}

echo "Student: " . $student->name . " (ID: " . $student->id . "), Class: " . $student->class . "\n";

$notifications = Notification::where('student_id', $student->id)->get();

echo "Total notifications for this student: " . $notifications->count() . "\n";

foreach ($notifications as $n) {
    echo sprintf(
        "ID: %d | Type: %s | Sender: %s | Class: %s | Message: %s\n",
        $n->id,
        $n->type,
        $n->sender_type,
        $n->class,
        substr($n->message, 0, 50)
    );
}
