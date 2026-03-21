<?php

namespace Database\Seeders;

use App\Models\InternshipSchedule;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InternScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'school_id'    => 1,
                'class_id'     => 101,
                'student_id'   => '2025010033C1',
                'company'      => 'TechCorp Inc.',
                'days'         => ['Monday', 'Tuesday', 'Thursday', 'Friday', 'Saturday'],
                'time_in'      => '08:00',
                'time_out'     => '17:00',
                'time_of_duty' => '8 hours',
                'start_date'   => Carbon::today()->toDateString(),
                'end_date'     => Carbon::today()->addMonths(3)->toDateString(),
            ],
            [
                'school_id'    => 1,
                'class_id'     => 101,
                'student_id'   => '2025010035C1',
                'company'      => 'SoftDev Solutions',
                'days'         => ['Monday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'time_in'      => '09:00',
                'time_out'     => '16:00',
                'time_of_duty' => '7 hours',
                'start_date'   => Carbon::today()->toDateString(),
                'end_date'     => Carbon::today()->addMonths(3)->toDateString(),
            ],
            [
                'school_id'    => 2,
                'class_id'     => 202,
                'student_id'   => '2025010031C1',
                'company'      => 'DesignHub Studio',
                'days'         => ['Tuesday','Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'time_in'      => '10:00',
                'time_out'     => '18:00',
                'time_of_duty' => '8 hours',
                'start_date'   => Carbon::today()->toDateString(),
                'end_date'     => Carbon::today()->addMonths(2)->toDateString(),
            ],
        ];

        foreach ($schedules as $schedule) {
            InternshipSchedule::create([
                'school_id'     => $schedule['school_id'],
                'class_id'      => $schedule['class_id'],
                'student_id'    => $schedule['student_id'],
                'company'       => $schedule['company'],
                'days'          => json_encode($schedule['days']), // JSON instead
                'time_in'       => $schedule['time_in'],
                'time_out'      => $schedule['time_out'],
                'time_of_duty'  => $schedule['time_of_duty'],
                'start_date'    => $schedule['start_date'],
                'end_date'      => $schedule['end_date'],
            ]);
        }
    }
}
