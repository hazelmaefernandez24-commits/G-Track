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
                'class' => '2026',
                'contact' => '09123456789',
                'battery_level' => 85,
                'signal_status' => 'Strong',
                'status' => true
            ]
        );

        StudentAuth::firstOrCreate(
            ['email' => 'hazelmae.fernandez@example.com'],
            [
                'student_id' => 'STU2026009',
                'password' => Hash::make('#hazel2006')
            ]
        );

        // Sample Student 2
        $student2 = Student::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'student_id' => 'STU2027001',
                'name' => 'John Doe',
                'gender' => 'Male', 
                'class' => '2027',
                'contact' => '09223334444',
                'battery_level' => 45,
                'signal_status' => 'Strong',
                'status' => true
            ]
        );

        // Sample Student 3
        $student3 = Student::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'student_id' => 'STU2028005',
                'name' => 'Jane Smith',
                'gender' => 'Female', 
                'class' => '2028',
                'contact' => '09334445555',
                'battery_level' => 12,
                'signal_status' => 'Weak',
                'status' => true,
                'sos_status' => 'help'
            ]
        );

        // Sample Student 4
        $student4 = Student::firstOrCreate(
            ['email' => 'mike.ross@example.com'],
            [
                'student_id' => 'STU2026012',
                'name' => 'Mike Ross',
                'gender' => 'Male', 
                'class' => '2026',
                'contact' => '09445556666',
                'battery_level' => 98,
                'signal_status' => 'Strong',
                'status' => false
            ]
        );
    }
}
