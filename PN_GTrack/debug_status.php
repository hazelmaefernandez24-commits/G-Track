<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use Carbon\Carbon;

echo "Current Time (now()): " . now() . "\n";
echo "Timezone: " . config('app.timezone') . "\n\n";

foreach (Student::all() as $s) {
    $diff = now()->diffInMinutes($s->updated_at);
    echo "ID: {$s->student_id}\n";
    echo "  Name: {$s->name}\n";
    echo "  Status (DB): " . ($s->status ? 'ONLINE' : 'OFFLINE') . "\n";
    echo "  Last Update (String): {$s->last_update}\n";
    echo "  Updated At (Timestamp): {$s->updated_at}\n";
    echo "  Diff from now(): {$diff} minutes\n";
    echo "----------------------------\n";
}
