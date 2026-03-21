<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('calendar_events')->insert([
            [
                'title' => 'Orientation Day',
                'description' => 'New students orientation and campus tour.',
                'start_date' => '2025-09-01 09:00:00',
                'end_date' => '2025-09-01 12:00:00',
                'category' => 'school_activity', // ✅ matches enum
                'semester' => 'first',           // ✅ matches enum
                'academic_year' => '2025-2026',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Parent-Teacher Meeting',
                'description' => 'Scheduled parent-teacher discussions.',
                'start_date' => '2025-09-10 14:00:00',
                'end_date' => '2025-09-10 16:00:00',
                'category' => 'school_activity', // ✅ academic events fall here
                'semester' => 'first',
                'academic_year' => '2025-2026',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Sports Fest',
                'description' => 'Annual school sports event.',
                'start_date' => '2025-09-20 08:00:00',
                'end_date' => '2025-09-22 17:00:00',
                'category' => 'special', // ✅ changed from Leisure → special
                'semester' => 'first',
                'academic_year' => '2025-2026',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
