<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\GradeSubmission;
$submission = GradeSubmission::find(3);
if ($submission) { 
    echo "Deleting submission ID: " . $submission->id . " | Term: " . $submission->term . " " . $submission->academic_year . "\n";
    $submission->delete(); 
    echo "Submission deleted successfully!\n"; 
}
else { 
    echo "Submission not found.\n"; 
}
