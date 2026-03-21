<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentDetails;
use Illuminate\Support\Facades\Hash;

class NewUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'user_id' => 'ADMIN001',
            'user_fname' => 'Admin',
            'user_lname' => 'User',
            'user_email' => 'admin@example.com',
            'user_role' => 'admin',
            'user_password' => Hash::make('password123'),
            'status' => 'active',
            'is_temp_password' => false,
        ]);

        // Create cook user
        User::create([
            'user_id' => 'COOK001',
            'user_fname' => 'Cook',
            'user_lname' => 'User',
            'user_email' => 'cook1',
            'user_role' => 'cook',
            'user_password' => Hash::make('123'),
            'status' => 'active',
            'is_temp_password' => false,
        ]);

        // Create kitchen user
        User::create([
            'user_id' => 'KITCHEN001',
            'user_fname' => 'Kitchen',
            'user_lname' => 'Staff',
            'user_email' => 'kitchen1',
            'user_role' => 'kitchen',
            'user_password' => Hash::make('123'),
            'status' => 'active',
            'is_temp_password' => false,
        ]);

        // Create student user
        User::create([
            'user_id' => 'STUDENT001',
            'user_fname' => 'Student',
            'user_lname' => 'User',
            'user_email' => 'student1',
            'user_role' => 'student',
            'user_password' => Hash::make('123'),
            'status' => 'active',
            'is_temp_password' => false,
        ]);

        // Create student details
        StudentDetails::create([
            'user_id' => 'STUDENT001',
            'student_id' => 'STU2024001',
            'batch' => '2024-A',
            'group' => 'G1',
            'student_number' => '2024001',
            'training_code' => 'TC',
        ]);
    }
}
