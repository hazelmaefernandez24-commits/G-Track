<?php
/**
 * IMMEDIATE FIX: Remove duplicate Gwyn Apawan
 * Run this script directly: php fix_gwyn_now.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "           REMOVE DUPLICATE GWYN APAWAN - DIRECT FIX           \n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    // Find all Gwyn Apawan entries
    $gwynEntries = DB::table('assignments_members')
        ->whereRaw('LOWER(TRIM(student_name)) = ?', ['gwyn apawan'])
        ->orderBy('id', 'asc')
        ->get();
    
    echo "Found " . $gwynEntries->count() . " entries for 'Gwyn Apawan'\n\n";
    
    if ($gwynEntries->count() <= 1) {
        echo "✅ No duplicates found! Gwyn Apawan appears only once or not at all.\n";
        exit(0);
    }
    
    // Keep the first one, delete the rest
    $keepId = $gwynEntries->first()->id;
    $deleteIds = $gwynEntries->slice(1)->pluck('id')->toArray();
    
    echo "Keeping entry ID: {$keepId}\n";
    echo "Deleting duplicate IDs: " . implode(', ', $deleteIds) . "\n\n";
    
    // Delete duplicates
    $deleted = DB::table('assignments_members')
        ->whereIn('id', $deleteIds)
        ->delete();
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                         SUCCESS!                              \n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "✅ Deleted {$deleted} duplicate entry/entries\n";
    echo "✅ Gwyn Apawan now appears only ONCE\n\n";
    
    // Verify
    $remaining = DB::table('assignments_members')
        ->whereRaw('LOWER(TRIM(student_name)) = ?', ['gwyn apawan'])
        ->count();
    
    echo "Verification: {$remaining} entry/entries remaining for Gwyn Apawan\n\n";
    
    if ($remaining == 1) {
        echo "🎉 PERFECT! Only 1 Gwyn Apawan entry exists now!\n\n";
    } else {
        echo "⚠️ Warning: Expected 1 entry but found {$remaining}\n\n";
    }
    
    echo "NEXT STEPS:\n";
    echo "1. Go to your web browser\n";
    echo "2. Press F5 to refresh\n";
    echo "3. Click 'View Members' for Kitchen operation\n";
    echo "4. Gwyn Apawan will appear only ONCE! ✅\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════════\n\n";
