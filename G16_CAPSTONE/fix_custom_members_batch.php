<?php

/**
 * Fix Custom Members Batch Information
 * This script updates existing custom members (those without student_id) 
 * to include batch information in their student_code field.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AssignmentMember;
use App\Models\Assignment;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Custom Members Batch Information ===\n\n";

// Get all custom members (those without student_id)
$customMembers = AssignmentMember::whereNull('student_id')
    ->whereNotNull('student_name')
    ->get();

echo "Found " . $customMembers->count() . " custom members without student_id\n\n";

$fixedCount = 0;
$skippedCount = 0;

foreach ($customMembers as $member) {
    // Skip if already has batch info in student_code
    if ($member->student_code && preg_match('/^BATCH_\d{4}$/', $member->student_code)) {
        echo "✓ Skipping {$member->student_name} - already has batch info: {$member->student_code}\n";
        $skippedCount++;
        continue;
    }
    
    // Get the assignment to find other members and determine batch
    $assignment = Assignment::with('assignmentMembers.student.studentDetail')->find($member->assignment_id);
    
    if (!$assignment) {
        echo "✗ Could not find assignment for {$member->student_name}\n";
        continue;
    }
    
    // Try to determine batch by looking at other members in the same assignment
    $batchCounts = [2025 => 0, 2026 => 0];
    
    foreach ($assignment->assignmentMembers as $otherMember) {
        if ($otherMember->id === $member->id) continue; // Skip self
        
        // Try to get batch from other member
        $batch = null;
        if ($otherMember->student && $otherMember->student->studentDetail) {
            $batch = $otherMember->student->studentDetail->batch;
        }
        
        if (!$batch && $otherMember->student_code) {
            if (preg_match('/^(20\d{2})/', $otherMember->student_code, $matches)) {
                $batch = (int)$matches[1];
            }
        }
        
        if ($batch && isset($batchCounts[$batch])) {
            $batchCounts[$batch]++;
        }
    }
    
    // Determine which batch this custom member likely belongs to
    // Strategy: If there are more 2025 members, assume 2025; otherwise 2026
    $determinedBatch = null;
    
    if ($batchCounts[2025] > 0 && $batchCounts[2026] === 0) {
        $determinedBatch = 2025;
    } elseif ($batchCounts[2026] > 0 && $batchCounts[2025] === 0) {
        $determinedBatch = 2026;
    } elseif ($batchCounts[2025] >= $batchCounts[2026]) {
        $determinedBatch = 2025;
    } else {
        $determinedBatch = 2026;
    }
    
    // Get category name for display
    $category = Category::find($assignment->category_id);
    $categoryName = $category ? $category->name : "Unknown";
    
    // Update the member with batch info
    if ($determinedBatch) {
        $newStudentCode = "BATCH_{$determinedBatch}";
        $member->student_code = $newStudentCode;
        $member->save();
        
        echo "✓ Fixed: {$member->student_name} in '{$categoryName}' -> {$newStudentCode} (based on {$batchCounts[2025]} x 2025 members, {$batchCounts[2026]} x 2026 members)\n";
        $fixedCount++;
    } else {
        echo "✗ Could not determine batch for {$member->student_name} in '{$categoryName}'\n";
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: {$fixedCount} custom members\n";
echo "Skipped: {$skippedCount} custom members (already had batch info)\n";
echo "Total processed: " . $customMembers->count() . "\n";
echo "\nDone! Custom members should now appear in Edit Members modal.\n";
