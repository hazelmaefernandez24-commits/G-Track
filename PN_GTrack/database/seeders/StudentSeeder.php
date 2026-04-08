<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentAuth;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Student 1
        $student1 = Student::firstOrCreate(
            ['email' => 'hazelmae.fernandez@example.com'],
            [
                'student_id' => 'STU2026009',
                'name' => 'Hazel Fernandez',
                'gender' => 'Female', 
                'class' => '2026'
            ]

        );

        StudentAuth::firstOrCreate(
            ['email' => 'hazelmae.fernandez@example.com'],
            [
                'student_id' => 'STU2026009',
                'password' => Hash::make('#hazel2006')
            ]
        );

    
    }
}
