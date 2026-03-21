<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Severity;
use App\Models\SeverityMaxCount;
use App\Models\PenaltyConfiguration;

echo "Debugging termination detection...\n\n";

// Test with a specific student
$testStudentId = '2025010009C1'; // Change this to test with a specific student

echo "Testing with student: $testStudentId\n\n";

// 1. Check if student has any violations
$allViolations = Violation::where('student_id', $testStudentId)->get();
echo "Total violations for student: " . $allViolations->count() . "\n";

if ($allViolations->count() > 0) {
    echo "Violation details:\n";
    foreach ($allViolations as $violation) {
        echo "- ID: {$violation->id}, Date: {$violation->violation_date}, Penalty: {$violation->penalty}, Status: {$violation->status}, Action Taken: " . ($violation->action_taken ? 'Yes' : 'No') . "\n";
    }
    echo "\n";
}

// 2. Check for termination penalties specifically
$terminationViolations = Violation::where('student_id', $testStudentId)
    ->where('penalty', 'T')
    ->where('status', '!=', 'appeal_approved')
    ->get();

echo "Termination violations (penalty = 'T'): " . $terminationViolations->count() . "\n";
if ($terminationViolations->count() > 0) {
    foreach ($terminationViolations as $violation) {
        echo "- ID: {$violation->id}, Date: {$violation->violation_date}, Status: {$violation->status}\n";
    }
    echo "\n";
}

// 3. Test the termination check logic (same as in the controller)
$hasTermination = Violation::where('student_id', $testStudentId)
    ->where('penalty', 'T')
    ->where('status', '!=', 'appeal_approved')
    ->exists();

echo "Has termination (using controller logic): " . ($hasTermination ? 'YES' : 'NO') . "\n\n";

// 4. Check penalty configuration
echo "Penalty configurations:\n";
$penaltyConfigs = PenaltyConfiguration::where('is_active', true)->orderBy('sort_order')->get();
if ($penaltyConfigs->count() > 0) {
    foreach ($penaltyConfigs as $config) {
        echo "- {$config->penalty_code}: Sort order = {$config->sort_order}\n";
    }
} else {
    echo "No penalty configurations found!\n";
}
echo "\n";

// 5. Check severity configurations
echo "Severity configurations:\n";
$severityConfigs = SeverityMaxCount::all();
if ($severityConfigs->count() > 0) {
    foreach ($severityConfigs as $config) {
        echo "- {$config->severity_name}: Max count = {$config->max_count}, Base penalty = {$config->base_penalty}\n";
    }
} else {
    echo "No severity configurations found!\n";
}
echo "\n";

// 6. Test with a violation type to see what penalty would be calculated
$testViolationType = ViolationType::with('severityRelation')->first();
if ($testViolationType) {
    echo "Testing penalty calculation with violation type: {$testViolationType->violation_name}\n";
    echo "Severity: " . ($testViolationType->severityRelation ? $testViolationType->severityRelation->severity_name : 'NULL') . "\n";
    
    // Simulate the penalty calculation
    $severity = $testViolationType->severityRelation ? $testViolationType->severityRelation->severity_name : 'Low';
    
    // Count existing violations with same severity
    $existingOffenses = Violation::where('student_id', $testStudentId)
        ->where('action_taken', true)
        ->where('status', '!=', 'appeal_approved')
        ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
        ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
        ->where('severities.severity_name', $severity)
        ->count();
    
    $offenseCount = $existingOffenses + 1;
    echo "Offense count for this severity ($severity): $offenseCount\n";
    
    // Get severity configuration
    $severityConfig = SeverityMaxCount::where('severity_name', $severity)->first();
    if ($severityConfig) {
        echo "Severity config - Max count: {$severityConfig->max_count}, Base penalty: {$severityConfig->base_penalty}\n";
    } else {
        echo "No severity configuration found for: $severity\n";
    }
}

echo "\nDebug complete.\n";

