<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GradeSubmission;
use App\Models\School;
use Illuminate\Support\Facades\DB;

// Find Cebu Institute University
$school = School::where('name', 'Cebu Institute University')->first();

if (!$school) {
    echo "Cebu Institute University not found\n";
    exit(1);
}

echo "=== All Submissions for Cebu Institute University ===\n";

// List all submissions for this school
$submissions = GradeSubmission::where('school_id', $school->id)
    ->orderBy('academic_year', 'desc')
    ->orderBy('term')
    ->get();

if ($submissions->isEmpty()) {
    echo "No submissions found for Cebu Institute University\n";
    exit(1);
}

foreach ($submissions as $sub) {
    echo "ID: " . $sub->id . 
         " | Term: " . $sub->term . 
         " | Year: " . $sub->academic_year . 
         " | Status: " . $sub->status . "\n";
}

echo "\n";

echo "=== Delete Submission ===\n";
echo "Enter the ID of the submission you want to delete: ";
$handle = fopen ("php://stdin","r");
$submissionId = trim(fgets($handle));

if (!is_numeric($submissionId)) {
    echo "Invalid submission ID\n";
    exit(1);
}

$submission = GradeSubmission::find($submissionId);

if (!$submission) {
    echo "Submission not found\n";
    exit(1);
}

echo "\nYou are about to delete the following submission:\n";
echo "ID: " . $submission->id . "\n";
echo "School: " . $school->name . "\n";
echo "Term: " . $submission->term . " " . $submission->academic_year . "\n";
echo "Status: " . $submission->status . "\n\n";

echo "Are you sure you want to delete this submission? (yes/no): ";
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'yes') {
    echo "Aborting!\n";
    exit;
}

// Delete the submission
$submission->delete();

echo "\nSubmission deleted successfully!\n";
