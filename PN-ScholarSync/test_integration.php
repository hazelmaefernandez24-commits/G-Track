<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TASK VIOLATION INTEGRATION TEST ===\n\n";

try {
    $integrationService = new \App\Services\TaskViolationIntegrationService();

    echo "1. Testing G16_CAPSTONE Connection:\n";
    echo "-----------------------------------\n";
    
    // Test connection to G16_CAPSTONE
    try {
        $invalidSubmissions = $integrationService->getInvalidSubmissionsWithStudentNames();
        echo "✅ Successfully connected to G16_CAPSTONE database\n";
        echo "📊 Found " . $invalidSubmissions->count() . " invalid task submissions\n\n";
        
        if ($invalidSubmissions->count() > 0) {
            echo "Sample invalid submissions:\n";
            foreach ($invalidSubmissions->take(5) as $submission) {
                echo "- {$submission['student_name']} ({$submission['student_id']}) - {$submission['task_category']}\n";
            }
            echo "\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Failed to connect to G16_CAPSTONE: " . $e->getMessage() . "\n\n";
        echo "💡 Make sure to configure the G16_CAPSTONE database connection in .env:\n";
        echo "G16_DB_HOST=127.0.0.1\n";
        echo "G16_DB_PORT=3306\n";
        echo "G16_DB_DATABASE=g16_capstone\n";
        echo "G16_DB_USERNAME=root\n";
        echo "G16_DB_PASSWORD=\n\n";
        return;
    }

    echo "2. Testing PN-ScholarSync Violation System:\n";
    echo "------------------------------------------\n";
    
    // Check if violation system is set up
    $offenseCategories = \App\Models\OffenseCategory::count();
    $severities = \App\Models\Severity::count();
    $violationTypes = \App\Models\ViolationType::count();
    
    echo "Offense Categories: {$offenseCategories}\n";
    echo "Severity Levels: {$severities}\n";
    echo "Violation Types: {$violationTypes}\n";
    
    if ($offenseCategories == 0 || $severities == 0) {
        echo "\n⚠️  Violation system needs setup. Creating basic data...\n";
        
        // Create basic severity
        $lowSeverity = \App\Models\Severity::firstOrCreate(
            ['severity_name' => 'Low'],
            ['description' => 'Low severity violations']
        );
        
        // Create Center Tasking category
        $centerTaskingCategory = \App\Models\OffenseCategory::firstOrCreate(
            ['category_name' => 'Center Tasking'],
            ['description' => 'Violations related to center task assignments']
        );
        
        echo "✅ Created basic violation system data\n";
    } else {
        echo "✅ Violation system is properly set up\n";
    }

    echo "\n3. Testing Integration (DRY RUN):\n";
    echo "--------------------------------\n";
    
    if ($invalidSubmissions->count() > 0) {
        echo "🔍 Preview of violations that would be created:\n\n";
        
        foreach ($invalidSubmissions->take(3) as $submission) {
            echo "Student: {$submission['student_name']}\n";
            echo "Task Category: {$submission['task_category']}\n";
            echo "Violation Type: Non-compliance with {$submission['task_category']} task assignment\n";
            echo "Severity: Low\n";
            echo "Penalty: VW (Verbal Warning)\n";
            echo "---\n";
        }
        
        echo "\n💡 To perform actual sync:\n";
        echo "1. Use the web interface: /educator/task-violation-integration\n";
        echo "2. Use the command: php artisan sync:task-violations\n";
        echo "3. Call the service directly: \$integrationService->syncInvalidTaskSubmissions()\n";
        
    } else {
        echo "ℹ️  No invalid submissions found to sync.\n";
        echo "💡 Invalid submissions will appear here when task reports are marked as 'Invalid' in G16_CAPSTONE.\n";
    }

    echo "\n4. Current Violations in PN-ScholarSync:\n";
    echo "---------------------------------------\n";
    
    $existingViolations = \App\Models\Violation::whereNotNull('task_submission_id')->count();
    $totalViolations = \App\Models\Violation::count();
    
    echo "Total violations: {$totalViolations}\n";
    echo "Synced from tasks: {$existingViolations}\n";
    
    if ($existingViolations > 0) {
        echo "\nSample synced violations:\n";
        $sampleViolations = \App\Models\Violation::whereNotNull('task_submission_id')
            ->with(['violationType'])
            ->take(3)
            ->get();
            
        foreach ($sampleViolations as $violation) {
            $violationName = $violation->violationType ? $violation->violationType->violation_name : 'Unknown';
            echo "- Task ID {$violation->task_submission_id}: {$violationName}\n";
        }
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END OF INTEGRATION TEST ===\n";
