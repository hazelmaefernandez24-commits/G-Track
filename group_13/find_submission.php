<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GradeSubmission;
use App\Models\School;
use Illuminate\Support\Facades\DB;

// Find all submissions with school information
$submissions = DB::table('grade_submissions')
    ->leftJoin('schools', 'grade_submissions.school_id', '=', 'schools.id')
    ->select('grade_submissions.*', 'schools.name as school_name')
    ->orderBy('grade_submissions.academic_year', 'desc')
    ->orderBy('grade_submissions.term')
    ->get();

if ($submissions->isEmpty()) {
    echo "No submissions found in the database.\n";
    exit(1);
}

echo "=== All Grade Submissions ===\n";
foreach ($submissions as $sub) {
    echo "ID: " . $sub->id . 
         " | School: " . ($sub->school_name ?? 'N/A') . 
         " | Term: " . $sub->term . 
         " " . $sub->academic_year . 
         " | Status: " . $sub->status . "\n";
}

echo "\nTo delete a submission, create a new file with the following content and run it:";
echo "\n--------------------------------------------------\n";
echo "<?php\n";
echo "require __DIR__.'/vendor/autoload.php';\n";
echo "\$app = require_once __DIR__.'/bootstrap/app.php';\n";
echo "\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n";
echo "\$kernel->bootstrap();\n";
echo "use App\\Models\\GradeSubmission;\n";
echo "\$submission = GradeSubmission::find(INSERT_ID_HERE);\n";
echo "if (\$submission) { \$submission->delete(); echo \"Submission deleted successfully!\\n\"; }\n";
echo "else { echo \"Submission not found.\\n\"; }\n";
echo "--------------------------------------------------\n\n";
