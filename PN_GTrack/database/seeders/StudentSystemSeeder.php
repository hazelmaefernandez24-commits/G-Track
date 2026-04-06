<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Location;
use Carbon\Carbon;

class StudentSystemSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a Sample Student
        $student = Student::create([
            'student_id'     => '2026-0005',
            'name'           => 'Hazel Rivera',
            'class'          => '2026',
            'gender'         => 'Female',
            'status'         => true, // Online
            'battery_level'  => 85,
            'signal_status'  => 'Strong',
            'last_update'    => Carbon::now()->format('M d, Y h:i A'),
            'contact'        => '0912-345-6789',
            'email'          => 'hazel.rivera@example.com',
            
        ]);

        Location::create([
            'student_id'   => $student->id, // Links to the student above
            'latitude'     => 10.3157,
            'longitude'    => 123.8854,
            'recorded_at'  => Carbon::now(),
            'sos_status'   => 'safe'
        ]);

       
    }
}
