<?php
/**
 * FORCE FIX: Directly update database to create Batch 2026 students
 * This will split your students 50/50 between batches
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FORCE FIX: Creating Batch 2026 Students ===\n\n";

try {
    // Get all students from student_details
    $allStudents = DB::connection('login')
        ->table('student_details')
        ->get();
    
    if ($allStudents->isEmpty()) {
        echo "❌ ERROR: No students found in student_details table!\n";
        echo "Please check your database connection.\n";
        exit;
    }
    
    echo "Found {$allStudents->count()} total students\n\n";
    
    // Count current batches
    $batch2025 = DB::connection('login')
        ->table('student_details')
        ->where('batch', 2025)
        ->count();
    
    $batch2026 = DB::connection('login')
        ->table('student_details')
        ->where('batch', 2026)
        ->count();
    
    echo "Current state:\n";
    echo "- Batch 2025: {$batch2025} students\n";
    echo "- Batch 2026: {$batch2026} students\n\n";
    
    if ($batch2026 > 0) {
        echo "✅ You already have Batch 2026 students!\n";
        echo "The problem might be that they're already assigned to other categories.\n\n";
        
        // Show which students are in batch 2026
        $students2026 = DB::connection('login')
            ->table('student_details')
            ->join('pn_users', 'student_details.user_id', '=', 'pn_users.user_id')
            ->where('student_details.batch', 2026)
            ->select('student_details.student_id', 'pn_users.user_fname', 'pn_users.user_lname', 'pn_users.gender')
            ->get();
        
        echo "Batch 2026 students:\n";
        foreach ($students2026 as $s) {
            echo "  - {$s->student_id}: {$s->user_fname} {$s->user_lname} ({$s->gender})\n";
        }
        
        exit;
    }
    
    // Split students 50/50
    echo "Splitting students 50/50 between batches...\n\n";
    
    $halfCount = (int) ceil($allStudents->count() / 2);
    $count2025 = 0;
    $count2026 = 0;
    
    foreach ($allStudents as $index => $student) {
        if ($index < $halfCount) {
            // First half -> Batch 2025
            DB::connection('login')
                ->table('student_details')
                ->where('user_id', $student->user_id)
                ->update(['batch' => 2025]);
            $count2025++;
        } else {
            // Second half -> Batch 2026
            DB::connection('login')
                ->table('student_details')
                ->where('user_id', $student->user_id)
                ->update(['batch' => 2026]);
            $count2026++;
        }
    }
    
    echo "✅ SUCCESS!\n";
    echo "- Updated {$count2025} students to Batch 2025\n";
    echo "- Updated {$count2026} students to Batch 2026\n\n";
    
    // Verify
    $newBatch2025 = DB::connection('login')
        ->table('student_details')
        ->where('batch', 2025)
        ->count();
    
    $newBatch2026 = DB::connection('login')
        ->table('student_details')
        ->where('batch', 2026)
        ->count();
    
    echo "Verification:\n";
    echo "- Batch 2025: {$newBatch2025} students ✅\n";
    echo "- Batch 2026: {$newBatch2026} students ✅\n\n";
    
    if ($newBatch2026 > 0) {
        echo "🎉 SUCCESS! Now you have students in both batches!\n\n";
        echo "NEXT STEPS:\n";
        echo "1. Go to your web interface\n";
        echo "2. Click 'Auto-Shuffle' button\n";
        echo "3. Wait for it to complete\n";
        echo "4. Click 'View Members'\n";
        echo "5. You should now see students in BOTH Batch 2025 and Batch 2026!\n";
    } else {
        echo "❌ Something went wrong. Please check your database manually.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END FORCE FIX ===\n";
