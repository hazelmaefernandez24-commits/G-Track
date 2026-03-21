<?php
/**
 * Quick Fix: Assign students to Batch 2026
 * This script will automatically assign half of your students to Batch 2026
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentDetail;
use Illuminate\Support\Facades\DB;

echo "=== BATCH 2026 FIX SCRIPT ===\n\n";

// Check current state
$batch2025Count = StudentDetail::where('batch', 2025)->count();
$batch2026Count = StudentDetail::where('batch', 2026)->count();
$noBatchCount = StudentDetail::whereNull('batch')->orWhere('batch', '!=', 2025)->where('batch', '!=', 2026)->count();
$totalStudents = StudentDetail::count();

echo "Current state:\n";
echo "- Batch 2025: {$batch2025Count} students\n";
echo "- Batch 2026: {$batch2026Count} students\n";
echo "- No batch/Other: {$noBatchCount} students\n";
echo "- Total: {$totalStudents} students\n\n";

if ($batch2026Count > 0) {
    echo "✅ You already have Batch 2026 students! No fix needed.\n";
    echo "If auto-shuffle still doesn't work, the students might be already assigned to other categories.\n";
    exit;
}

// Option 1: Check if there are students with student_id starting with "2026"
$students2026ById = StudentDetail::where('student_id', 'LIKE', '2026%')->get();

if ($students2026ById->count() > 0) {
    echo "Found {$students2026ById->count()} students with student_id starting with '2026'.\n";
    echo "Updating these students to batch=2026...\n";
    
    foreach ($students2026ById as $student) {
        $student->batch = 2026;
        $student->save();
        echo "  ✓ Updated {$student->student_id}\n";
    }
    
    echo "\n✅ SUCCESS! Updated {$students2026ById->count()} students to Batch 2026.\n";
} else {
    // Option 2: Split existing students 50/50 between batches
    echo "No students found with '2026' in their ID.\n";
    echo "Will split existing students 50/50 between Batch 2025 and Batch 2026...\n\n";
    
    $allStudents = StudentDetail::all();
    $halfCount = (int) ceil($allStudents->count() / 2);
    
    echo "Total students: {$allStudents->count()}\n";
    echo "Will assign {$halfCount} to Batch 2025 and " . ($allStudents->count() - $halfCount) . " to Batch 2026\n\n";
    
    $count2025 = 0;
    $count2026 = 0;
    
    foreach ($allStudents as $index => $student) {
        if ($index < $halfCount) {
            $student->batch = 2025;
            $count2025++;
        } else {
            $student->batch = 2026;
            $count2026++;
        }
        $student->save();
    }
    
    echo "✅ SUCCESS!\n";
    echo "- Assigned {$count2025} students to Batch 2025\n";
    echo "- Assigned {$count2026} students to Batch 2026\n";
}

echo "\n=== VERIFICATION ===\n";
$newBatch2025 = StudentDetail::where('batch', 2025)->count();
$newBatch2026 = StudentDetail::where('batch', 2026)->count();

echo "New state:\n";
echo "- Batch 2025: {$newBatch2025} students ✅\n";
echo "- Batch 2026: {$newBatch2026} students ✅\n\n";

if ($newBatch2026 > 0) {
    echo "🎉 SUCCESS! You now have students in both batches!\n";
    echo "\nNext steps:\n";
    echo "1. Go back to your web interface\n";
    echo "2. Click 'Auto-Shuffle' button\n";
    echo "3. Click 'View Members' to see students in BOTH batches\n";
} else {
    echo "❌ Something went wrong. Please check your database manually.\n";
}

echo "\n=== END FIX SCRIPT ===\n";
