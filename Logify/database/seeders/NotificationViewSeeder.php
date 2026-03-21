<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationView;

class NotificationViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize notification views for all log types
        NotificationView::firstOrCreate(['log_type' => 'academic']);
        NotificationView::firstOrCreate(['log_type' => 'goingout']);
        NotificationView::firstOrCreate(['log_type' => 'visitor']);
        NotificationView::firstOrCreate(['log_type' => 'late']);
    }
}
