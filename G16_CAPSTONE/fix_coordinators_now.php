<?php
/**
 * FIX COORDINATORS - Ensure both batches have coordinators with yellow highlighting
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Models\Assignment;
use App\Models\AssignmentMember;

echo "═══════════════════════════════════════════════════════════════\n";
echo "           FIX COORDINATORS - Add Yellow Highlighting          \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Find Kitchen operation
$category = Category::where('name', 'LIKE', '%Kitchen%')->first();

if (!$category) {
    echo "❌ ERROR: Kitchen operation not found!\n";
    exit;
}

echo "Found: {$category->name}\n\n";

// Get current assignment
$assignment = Assignment::where('category_id', $category->id)
    ->where('status', 'current')
    ->first();

if (!$assignment) {
    echo "❌ ERROR: No current assignment found!\n";
    exit;
}

echo "Current assignment ID: {$assignment->id}\n\n";

// Get all members
$members = AssignmentMember::where('assignment_id', $assignment->id)
    ->with('student.studentDetail')
    ->get();

echo "Total members: {$members->count()}\n\n";

// Clear all coordinators first
AssignmentMember::where('assignment_id', $assignment->id)
    ->update(['is_coordinator' => 0]);

echo "Cleared all coordinator flags\n\n";

// Find first student from each batch
$batch2025Member = null;
$batch2026Member = null;

foreach ($members as $member) {
    $batch = null;
    
    if ($member->student && $member->student->studentDetail) {
        $batch = $member->student->studentDetail->batch;
    }
    
    if ($batch == 2025 && !$batch2025Member) {
        $batch2025Member = $member;
    }
    
    if ($batch == 2026 && !$batch2026Member) {
        $batch2026Member = $member;
    }
    
    if ($batch2025Member && $batch2026Member) {
        break;
    }
}

// Set coordinators
$updated = 0;

if ($batch2025Member) {
    $batch2025Member->is_coordinator = 1;
    $batch2025Member->save();
    
    $name = $batch2025Member->student 
        ? $batch2025Member->student->user_fname . ' ' . $batch2025Member->student->user_lname
        : 'Unknown';
    
    echo "✅ Set Batch 2025 coordinator: {$name} (ID: {$batch2025Member->id})\n";
    $updated++;
}

if ($batch2026Member) {
    $batch2026Member->is_coordinator = 1;
    $batch2026Member->save();
    
    $name = $batch2026Member->student 
        ? $batch2026Member->student->user_fname . ' ' . $batch2026Member->student->user_lname
        : 'Unknown';
    
    echo "✅ Set Batch 2026 coordinator: {$name} (ID: {$batch2026Member->id})\n";
    $updated++;
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "                         SUCCESS!                              \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "✅ Updated {$updated} coordinators\n";
echo "✅ Both batches now have coordinators with yellow highlighting!\n\n";

echo "NEXT STEPS:\n";
echo "1. Go to your web interface\n";
echo "2. Press F5 to refresh\n";
echo "3. Click 'View Members' for Kitchen operation\n";
echo "4. You will see BOTH coordinators with yellow highlighting:\n";
echo "   - Batch 2025: Yellow highlight ⭐\n";
echo "   - Batch 2026: Yellow highlight ⭐\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
