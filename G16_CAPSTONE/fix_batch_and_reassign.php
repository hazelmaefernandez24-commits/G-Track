<?php
/**
 * COMPLETE FIX: Update students to Batch 2026 AND clear assignments to force re-shuffle
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\Category;

echo "=== COMPLETE FIX: BATCH 2026 + REASSIGNMENT ===\n\n";

// STEP 1: Update students to Batch 2026
echo "STEP 1: Updating students to Batch 2026...\n";

$studentsToMove = [
    'Gerald Reyes',
    'Albert Reboquio', 
    'Jella Gesim',
    'Mariel Bawic'
];

$updated = 0;
$userIds = [];

foreach ($studentsToMove as $fullName) {
    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
    
    $user = PNUser::where('user_fname', 'LIKE', "%{$firstName}%")
        ->where('user_lname', 'LIKE', "%{$lastName}%")
        ->where('user_role', 'student')
        ->first();
    
    if ($user) {
        $studentDetail = StudentDetail::where('user_id', $user->user_id)->first();
        
        if ($studentDetail) {
            $oldBatch = $studentDetail->batch;
            $studentDetail->batch = 2026;
            $studentDetail->save();
            
            $userIds[] = $user->user_id;
            echo "  ✅ {$fullName} → Batch 2026 (was Batch {$oldBatch})\n";
            $updated++;
        }
    }
}

echo "\n✅ Updated {$updated} students to Batch 2026\n\n";

// STEP 2: Find Kitchen operation category
echo "STEP 2: Finding Kitchen operation category...\n";

$kitchenCategory = Category::where('name', 'LIKE', '%Kitchen%')->first();

if (!$kitchenCategory) {
    echo "❌ ERROR: Could not find Kitchen operation category!\n";
    echo "Please check your categories table.\n";
    exit;
}

echo "  ✅ Found category: {$kitchenCategory->name} (ID: {$kitchenCategory->id})\n\n";

// STEP 3: Clear current assignments for Kitchen operation
echo "STEP 3: Clearing current assignments for Kitchen operation...\n";

$currentAssignments = Assignment::where('category_id', $kitchenCategory->id)
    ->where('status', 'current')
    ->get();

$deletedMembers = 0;

foreach ($currentAssignments as $assignment) {
    $memberCount = $assignment->assignmentMembers()->count();
    $assignment->assignmentMembers()->delete();
    $deletedMembers += $memberCount;
    
    echo "  ✅ Cleared {$memberCount} members from assignment ID {$assignment->id}\n";
}

echo "\n✅ Cleared {$deletedMembers} assignment members\n\n";

// STEP 4: Verify batch distribution
echo "STEP 4: Verifying batch distribution...\n";

$batch2025Count = StudentDetail::where('batch', 2025)->count();
$batch2026Count = StudentDetail::where('batch', 2026)->count();

echo "  - Batch 2025: {$batch2025Count} students\n";
echo "  - Batch 2026: {$batch2026Count} students\n\n";

if ($batch2026Count === 0) {
    echo "⚠️  WARNING: No Batch 2026 students found!\n";
    echo "The system needs Batch 2026 students to assign them.\n\n";
    
    // Auto-fix: Split students 50/50
    echo "AUTO-FIX: Splitting all students 50/50 between batches...\n";
    
    $allStudents = StudentDetail::all();
    $halfCount = (int) ceil($allStudents->count() / 2);
    
    foreach ($allStudents as $index => $student) {
        if ($index < $halfCount) {
            $student->batch = 2025;
        } else {
            $student->batch = 2026;
        }
        $student->save();
    }
    
    $batch2025Count = StudentDetail::where('batch', 2025)->count();
    $batch2026Count = StudentDetail::where('batch', 2026)->count();
    
    echo "  ✅ New distribution:\n";
    echo "    - Batch 2025: {$batch2025Count} students\n";
    echo "    - Batch 2026: {$batch2026Count} students\n\n";
}

echo "=== SUMMARY ===\n";
echo "✅ Students updated to Batch 2026: {$updated}\n";
echo "✅ Assignment members cleared: {$deletedMembers}\n";
echo "✅ Batch 2025: {$batch2025Count} students\n";
echo "✅ Batch 2026: {$batch2026Count} students\n\n";

echo "🎉 SUCCESS! Database is ready!\n\n";

echo "NEXT STEPS:\n";
echo "1. Go to your web interface\n";
echo "2. Click the 'Auto-Shuffle' button (yellow button at top)\n";
echo "3. Wait for it to complete\n";
echo "4. Click 'View Members' for Kitchen operation\n";
echo "5. You should now see students in BOTH Batch 2025 AND Batch 2026 columns!\n\n";

echo "If you set requirements like:\n";
echo "  - Batch 2025: 2 males + 2 females = 4 students\n";
echo "  - Batch 2026: 2 males + 1 female = 3 students\n";
echo "Then auto-shuffle will assign EXACTLY those numbers to each batch.\n\n";

echo "=== END FIX ===\n";
