<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\GradeSubmission;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\PNUser;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    
    // Get the first available school, class, subject, and student
    $school = School::first();
    if (!$school) {
        throw new Exception('No schools found. Please create a school first.');
    }
    
    $class = ClassModel::first();
    if (!$class) {
        throw new Exception('No classes found. Please create a class first.');
    }
    
    $subject = Subject::first();
    if (!$subject) {
        throw new Exception('No subjects found. Please create a subject first.');
    }
    
    $student = PNUser::where('user_role', 'student')->first();
    if (!$student) {
        throw new Exception('No students found. Please create a student user first.');
    }
    
    // Create the submission
    $submission = new GradeSubmission();
    $submission->school_id = $school->school_id;
    $submission->class_id = $class->class_id;
    $submission->semester = '1st';
    $submission->term = 'Prelim';
    $submission->academic_year = '2024-2025';
    $submission->status = 'approved';
    $submission->save();
    
    // Attach subject and student
    $submission->subjects()->attach($subject->id);
    $submission->students()->attach($student->user_id, [
        'grade' => 2.5,
        'status' => 'approved'
    ]);
    
    DB::commit();
    
    echo "Test submission created successfully! ID: " . $submission->id . "\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
