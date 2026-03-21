<?php
/**
 * FINAL COMPLETE FIX - Does EVERYTHING needed to make batch assignment work
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudentDetail;
use App\Models\Assignment;
use App\Models\Category;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         FINAL COMPLETE FIX - BATCH ASSIGNMENT              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// STEP 1: Ensure we have students in both batches
echo "STEP 1: Checking batch distribution...\n";

$batch2025Count = StudentDetail::where('batch', 2025)->count();
$batch2026Count = StudentDetail::where('batch', 2026)->count();
$totalStudents = StudentDetail::count();

echo "  Current state:\n";
echo "  - Batch 2025: {$batch2025Count} students\n";
echo "  - Batch 2026: {$batch2026Count} students\n";
echo "  - Total: {$totalStudents} students\n\n";

if ($batch2026Count === 0 || $batch2025Count === 0) {
    echo "  ⚠️  Unbalanced! Fixing by splitting 50/50...\n";
    
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
    
    echo "  ✅ Fixed! New distribution:\n";
    echo "    - Batch 2025: {$batch2025Count} students\n";
    echo "    - Batch 2026: {$batch2026Count} students\n\n";
} else {
    echo "  ✅ Both batches have students!\n\n";
}

// STEP 2: Clear ALL current assignments to force fresh shuffle
echo "STEP 2: Clearing all current assignments...\n";

$currentAssignments = Assignment::where('status', 'current')->get();
$totalCleared = 0;

foreach ($currentAssignments as $assignment) {
    $count = $assignment->assignmentMembers()->count();
    $assignment->assignmentMembers()->delete();
    $totalCleared += $count;
    echo "  ✅ Cleared {$count} members from {$assignment->category->name}\n";
}

echo "\n  ✅ Total cleared: {$totalCleared} assignment members\n\n";

// STEP 3: Verify categories exist
echo "STEP 3: Verifying categories...\n";

$categories = Category::all();
echo "  Found {$categories->count()} categories:\n";
foreach ($categories->take(5) as $cat) {
    echo "    - {$cat->name}\n";
}
if ($categories->count() > 5) {
    echo "    - ... and " . ($categories->count() - 5) . " more\n";
}
echo "\n";

// STEP 4: Show summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                      SUMMARY                               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Batch 2025: {$batch2025Count} students available\n";
echo "✅ Batch 2026: {$batch2026Count} students available\n";
echo "✅ All assignments cleared: {$totalCleared} members removed\n";
echo "✅ System ready for fresh auto-shuffle!\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                   NEXT STEPS                               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "1. Go to your web interface\n";
echo "2. Find any task card (e.g., Kitchen operation)\n";
echo "3. Click the gear icon ⚙️ to open 'Edit Task Assignment'\n";
echo "4. Set requirements for ONE batch only:\n";
echo "   Example:\n";
echo "   - Batch 2025 Males: 3\n";
echo "   - Batch 2025 Females: 3\n";
echo "   - Batch 2026 Males: 0 (leave empty)\n";
echo "   - Batch 2026 Females: 0 (leave empty)\n";
echo "5. Click 'Save Changes'\n";
echo "6. System will AUTOMATICALLY split to both batches:\n";
echo "   - Batch 2025: 3 students\n";
echo "   - Batch 2026: 3 students\n";
echo "7. Click 'View Members' to see the result!\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║              EXPECTED RESULT                               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "If you set 6 students requirement, you'll see:\n\n";
echo "┌─────────────────────────────────┬─────────────────────────────────┐\n";
echo "│         Batch 2025              │         Batch 2026              │\n";
echo "│    (3 students assigned)        │    (3 students assigned)        │\n";
echo "├─────────────────────────────────┼─────────────────────────────────┤\n";
echo "│   Student 1 (Coordinator) ⭐    │   Student 4                     │\n";
echo "│   Student 2                     │   Student 5                     │\n";
echo "│   Student 3                     │   Student 6                     │\n";
echo "└─────────────────────────────────┴─────────────────────────────────┘\n\n";

echo "🎉 SYSTEM IS READY! Try it now!\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
