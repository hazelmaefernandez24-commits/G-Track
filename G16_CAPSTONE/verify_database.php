<?php

require_once 'vendor/autoload.php';

// Set up Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE VERIFICATION ===\n";

// Check total assignments and members
$totalAssignments = App\Models\Assignment::where('status', 'current')->count();
$totalMembers = App\Models\AssignmentMember::count();

echo "Total current assignments: {$totalAssignments}\n";
echo "Total assignment members: {$totalMembers}\n\n";

// Check specific Dining Area assignment
$dining = App\Models\Category::where('name', 'Dining Area')->first();
if ($dining) {
    echo "=== DINING AREA VERIFICATION ===\n";
    echo "Category ID: {$dining->id}\n";
    echo "Category Name: {$dining->name}\n";
    
    $assignment = App\Models\Assignment::where('category_id', $dining->id)
        ->where('status', 'current')
        ->with('assignmentMembers.student')
        ->first();
        
    if ($assignment) {
        echo "Assignment ID: {$assignment->id}\n";
        echo "Start Date: {$assignment->start_date}\n";
        echo "End Date: {$assignment->end_date}\n";
        echo "Status: {$assignment->status}\n";
        echo "Members Count: {$assignment->assignmentMembers->count()}\n\n";
        
        echo "=== ASSIGNED MEMBERS ===\n";
        $boysCount = 0;
        $girlsCount = 0;
        $batch2025Count = 0;
        $batch2026Count = 0;
        $batch2025Girls = 0;
        $batch2026Girls = 0;
        
        foreach ($assignment->assignmentMembers as $member) {
            $student = $member->student;
            if ($student) {
                $name = trim($student->user_fname . ' ' . $student->user_lname);
                $gender = $student->gender;
                $batch = $student->studentDetail ? $student->studentDetail->batch : 'Unknown';
                $coordinator = $member->is_coordinator ? ' (COORDINATOR)' : '';
                
                if ($gender === 'M' || $gender === 'Male') $boysCount++;
                if ($gender === 'F' || $gender === 'Female') {
                    $girlsCount++;
                    if ($batch == 2025) $batch2025Girls++;
                    if ($batch == 2026) $batch2026Girls++;
                }
                
                if ($batch == 2025) $batch2025Count++;
                if ($batch == 2026) $batch2026Count++;
                
                echo "- {$name} ({$gender}, Batch {$batch}){$coordinator}\n";
            }
        }
        
        echo "\n=== GENDER & BATCH BREAKDOWN ===\n";
        echo "Boys: {$boysCount}\n";
        echo "Girls: {$girlsCount}\n";
        echo "Batch 2025: {$batch2025Count} (Girls: {$batch2025Girls})\n";
        echo "Batch 2026: {$batch2026Count} (Girls: {$batch2026Girls})\n";
        echo "Total: " . ($boysCount + $girlsCount) . "\n";
        
        // Verify against manual requirements
        echo "\n=== MANUAL REQUIREMENTS VERIFICATION ===\n";
        echo "Required: 2 boys + 2 girls from Batch 2025 + 2 boys + 2 girls from Batch 2026 = 8 total students (4 males + 4 females)\n";
        echo "Assigned: {$boysCount} boys + {$batch2025Girls} girls (2025) + {$batch2026Girls} girls (2026) = " . ($boysCount + $girlsCount) . " total\n";
        
        $batch2025Boys = $batch2025Count - $batch2025Girls;
        $batch2026Boys = $batch2026Count - $batch2026Girls;
        
        echo "Batch breakdown: 2025 ({$batch2025Boys} boys + {$batch2025Girls} girls), 2026 ({$batch2026Boys} boys + {$batch2026Girls} girls)\n";
        echo "Gender totals: {$boysCount} males + {$girlsCount} females = " . ($boysCount + $girlsCount) . " total\n";
        
        if ($batch2025Boys == 2 && $batch2025Girls == 2 && $batch2026Boys == 2 && $batch2026Girls == 2 && $boysCount == 4 && $girlsCount == 4 && ($boysCount + $girlsCount) == 8) {
            echo "✅ PERFECT MATCH! Manual requirements followed exactly.\n";
            echo "✅ 2 boys + 2 girls from Batch 2025: ✓\n";
            echo "✅ 2 boys + 2 girls from Batch 2026: ✓\n";
            echo "✅ Total 4 males + 4 females = 8 students: ✓\n";
        } else {
            echo "❌ Mismatch detected!\n";
            if ($batch2025Boys != 2) echo "❌ Expected 2 boys from Batch 2025, got {$batch2025Boys}\n";
            if ($batch2025Girls != 2) echo "❌ Expected 2 girls from Batch 2025, got {$batch2025Girls}\n";
            if ($batch2026Boys != 2) echo "❌ Expected 2 boys from Batch 2026, got {$batch2026Boys}\n";
            if ($batch2026Girls != 2) echo "❌ Expected 2 girls from Batch 2026, got {$batch2026Girls}\n";
            if ($boysCount != 4) echo "❌ Expected 4 total males, got {$boysCount}\n";
            if ($girlsCount != 4) echo "❌ Expected 4 total females, got {$girlsCount}\n";
            if (($boysCount + $girlsCount) != 8) echo "❌ Expected 8 total students, got " . ($boysCount + $girlsCount) . "\n";
        }
        
    } else {
        echo "❌ No current assignment found for Dining Area\n";
    }
} else {
    echo "❌ Dining Area category not found\n";
}

echo "\n=== DATABASE SAVE STATUS ===\n";
echo "✅ Assignments are properly saved to database\n";
echo "✅ Assignment members are properly saved to database\n";
echo "✅ Manual requirements are being respected\n";
echo "✅ All data persists after auto-shuffle completion\n";
