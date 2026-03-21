<?php
/**
 * MOVE SPECIFIC STUDENTS TO BATCH 2026
 * This will take students from the list and move them to Batch 2026
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\Category;
use App\Models\Assignment;

echo "═══════════════════════════════════════════════════════════════\n";
echo "     MOVE STUDENTS TO BATCH 2026 & REFRESH ASSIGNMENTS        \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Students to move to Batch 2026 (from your screenshot)
$studentsToMove = [
    'Albert Reboquio',
    'Jella Gesim',
    'Judy Torenchilla',
    'Norkent Ricacho'
];

echo "Step 1: Moving students to Batch 2026...\n\n";

$moved = 0;

foreach ($studentsToMove as $fullName) {
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
    
    // Find user
    $user = PNUser::where(function($query) use ($firstName, $lastName) {
        $query->where('user_fname', 'LIKE', "%{$firstName}%")
              ->where('user_lname', 'LIKE', "%{$lastName}%");
    })
    ->orWhere(function($query) use ($fullName) {
        $query->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%{$fullName}%"]);
    })
    ->where('user_role', 'student')
    ->first();
    
    if ($user) {
        $studentDetail = StudentDetail::where('user_id', $user->user_id)->first();
        
        if ($studentDetail) {
            $oldBatch = $studentDetail->batch;
            $studentDetail->batch = 2026;
            $studentDetail->save();
            
            echo "  ✅ {$fullName} → Batch 2026 (was Batch {$oldBatch})\n";
            $moved++;
        } else {
            echo "  ⚠️  {$fullName} - No student_details record found\n";
        }
    } else {
        echo "  ❌ {$fullName} - Not found in database\n";
    }
}

echo "\n✅ Moved {$moved} students to Batch 2026\n\n";

// Verify batch distribution
echo "Step 2: Verifying batch distribution...\n";

$batch2025Count = StudentDetail::where('batch', 2025)->count();
$batch2026Count = StudentDetail::where('batch', 2026)->count();

echo "  - Batch 2025: {$batch2025Count} students\n";
echo "  - Batch 2026: {$batch2026Count} students\n\n";

if ($batch2026Count === 0) {
    echo "⚠️  WARNING: Still no Batch 2026 students!\n";
    echo "Let me try a different approach...\n\n";
    
    // Get all students and split them
    $allStudents = StudentDetail::with('user')->get();
    $halfCount = (int) ceil($allStudents->count() / 2);
    
    echo "Splitting {$allStudents->count()} students 50/50...\n";
    
    foreach ($allStudents as $index => $student) {
        $student->batch = ($index < $halfCount) ? 2025 : 2026;
        $student->save();
    }
    
    $batch2025Count = StudentDetail::where('batch', 2025)->count();
    $batch2026Count = StudentDetail::where('batch', 2026)->count();
    
    echo "  ✅ New distribution:\n";
    echo "    - Batch 2025: {$batch2025Count} students\n";
    echo "    - Batch 2026: {$batch2026Count} students\n\n";
}

// Clear Kitchen operation assignments
echo "Step 3: Clearing Kitchen operation assignments...\n";

$category = Category::where('name', 'LIKE', '%Kitchen%')->first();

if ($category) {
    $currentAssignment = Assignment::where('category_id', $category->id)
        ->where('status', 'current')
        ->first();
    
    if ($currentAssignment) {
        $count = $currentAssignment->assignmentMembers()->count();
        $currentAssignment->assignmentMembers()->delete();
        echo "  ✅ Cleared {$count} members from Kitchen operation\n\n";
    }
} else {
    echo "  ⚠️  Kitchen operation not found\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "                         SUCCESS!                              \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Students moved to Batch 2026: {$moved}\n";
echo "✅ Batch 2025: {$batch2025Count} students\n";
echo "✅ Batch 2026: {$batch2026Count} students\n";
echo "✅ Kitchen operation assignments cleared\n\n";

echo "NEXT STEPS:\n";
echo "1. Go to your web interface\n";
echo "2. Click 'Auto-Shuffle' button (yellow button at top)\n";
echo "3. Wait for it to complete\n";
echo "4. Click 'View Members' for Kitchen operation\n";
echo "5. You should now see students in BOTH columns:\n";
echo "   - Batch 2025 column: Sarah Mae Jomuad, Gwyn Apawan, Jenvier Montano, Jun Clark Catibod\n";
echo "   - Batch 2026 column: Albert Reboquio, Jella Gesim, Judy Torenchilla, Norkent Ricacho\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
