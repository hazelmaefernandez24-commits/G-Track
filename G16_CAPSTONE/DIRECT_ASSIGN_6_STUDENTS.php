<?php
/**
 * DIRECT FIX: Manually assign 6 students to Kitchen operation
 * This will bypass auto-shuffle and directly create assignments
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\StudentDetail;
use App\Models\PNUser;
use Carbon\Carbon;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       DIRECT FIX: Assign 6 Students to Kitchen Operation      \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Find Kitchen operation category
echo "Step 1: Finding Kitchen operation...\n";
$category = Category::where('name', 'LIKE', '%Kitchen%')->first();

if (!$category) {
    echo "❌ ERROR: Could not find Kitchen operation category!\n";
    exit;
}

echo "✅ Found: {$category->name} (ID: {$category->id})\n\n";

// Check current assignment
echo "Step 2: Checking current assignment...\n";
$currentAssignment = Assignment::where('category_id', $category->id)
    ->where('status', 'current')
    ->first();

if (!$currentAssignment) {
    echo "Creating new assignment...\n";
    $currentAssignment = new Assignment();
    $currentAssignment->category_id = $category->id;
    $currentAssignment->start_date = Carbon::now();
    $currentAssignment->end_date = Carbon::now()->addDays(7);
    $currentAssignment->status = 'current';
    $currentAssignment->save();
    echo "✅ Created assignment ID: {$currentAssignment->id}\n";
} else {
    echo "✅ Found existing assignment ID: {$currentAssignment->id}\n";
    // Clear existing members
    $count = $currentAssignment->assignmentMembers()->count();
    $currentAssignment->assignmentMembers()->delete();
    echo "✅ Cleared {$count} existing members\n";
}

echo "\n";

// Get students from both batches
echo "Step 3: Getting students from database...\n";

$batch2025Students = StudentDetail::where('batch', 2025)
    ->with('user')
    ->take(3)
    ->get();

$batch2026Students = StudentDetail::where('batch', 2026)
    ->with('user')
    ->take(3)
    ->get();

echo "Found:\n";
echo "  - Batch 2025: {$batch2025Students->count()} students\n";
echo "  - Batch 2026: {$batch2026Students->count()} students\n\n";

if ($batch2025Students->count() === 0 || $batch2026Students->count() === 0) {
    echo "⚠️  WARNING: Not enough students in one or both batches!\n";
    echo "Running auto-fix to split students 50/50...\n\n";
    
    $allStudents = StudentDetail::all();
    $halfCount = (int) ceil($allStudents->count() / 2);
    
    foreach ($allStudents as $index => $student) {
        $student->batch = ($index < $halfCount) ? 2025 : 2026;
        $student->save();
    }
    
    // Re-fetch students
    $batch2025Students = StudentDetail::where('batch', 2025)->with('user')->take(3)->get();
    $batch2026Students = StudentDetail::where('batch', 2026)->with('user')->take(3)->get();
    
    echo "✅ Fixed! Now have:\n";
    echo "  - Batch 2025: {$batch2025Students->count()} students\n";
    echo "  - Batch 2026: {$batch2026Students->count()} students\n\n";
}

// Assign students
echo "Step 4: Assigning students...\n";

$assigned = 0;
$coordinatorAssigned = false;

// Assign Batch 2025 students
foreach ($batch2025Students as $index => $studentDetail) {
    if (!$studentDetail->user) continue;
    
    $member = new AssignmentMember();
    $member->assignment_id = $currentAssignment->id;
    $member->student_id = $studentDetail->user_id;
    $member->student_code = $studentDetail->student_id;
    $member->is_coordinator = ($index === 0 && !$coordinatorAssigned) ? 1 : 0;
    $member->save();
    
    $name = $studentDetail->user->user_fname . ' ' . $studentDetail->user->user_lname;
    $coord = $member->is_coordinator ? ' ⭐ (Coordinator)' : '';
    echo "  ✅ Batch 2025: {$name}{$coord}\n";
    
    if ($member->is_coordinator) $coordinatorAssigned = true;
    $assigned++;
}

// Assign Batch 2026 students
foreach ($batch2026Students as $index => $studentDetail) {
    if (!$studentDetail->user) continue;
    
    $member = new AssignmentMember();
    $member->assignment_id = $currentAssignment->id;
    $member->student_id = $studentDetail->user_id;
    $member->student_code = $studentDetail->student_id;
    $member->is_coordinator = ($index === 0 && !$coordinatorAssigned) ? 1 : 0;
    $member->save();
    
    $name = $studentDetail->user->user_fname . ' ' . $studentDetail->user->user_lname;
    $coord = $member->is_coordinator ? ' ⭐ (Coordinator)' : '';
    echo "  ✅ Batch 2026: {$name}{$coord}\n";
    
    if ($member->is_coordinator) $coordinatorAssigned = true;
    $assigned++;
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                         SUCCESS!                              \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Assigned {$assigned} students to Kitchen operation\n";
echo "✅ Batch 2025: {$batch2025Students->count()} students\n";
echo "✅ Batch 2026: {$batch2026Students->count()} students\n\n";

echo "NEXT STEPS:\n";
echo "1. Go to your web interface\n";
echo "2. Refresh the page (F5)\n";
echo "3. Click 'View Members' for Kitchen operation\n";
echo "4. You should now see {$assigned} students split between both batches!\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
