<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\GradeSubmission;

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// List all grade submissions
$submissions = GradeSubmission::all();

echo "List of all grade submissions:\n";
echo str_repeat('-', 100) . "\n";
echo sprintf("%-5s | %-15s | %-15s | %-10s | %-20s\n", 'ID', 'Term', 'Academic Year', 'Status', 'Created At');
echo str_repeat('-', 100) . "\n";

foreach ($submissions as $sub) {
    echo sprintf("%-5d | %-15s | %-15s | %-10s | %-20s\n", 
        $sub->id, 
        $sub->term, 
        $sub->academic_year,
        $sub->status,
        $sub->created_at
    );
}

echo "\nTo delete a submission, run: php artisan tinker --execute='use App\\Models\\GradeSubmission; GradeSubmission::find(ID)->delete();'\n";
