<?php

namespace Database\Seeders;

use App\Models\StudentDetails;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed payment methods and modes first
        $this->call([
            PaymentMethodSeeder::class,
            PaymentModeSeeder::class,
        ]);

        User::create([
            'user_id' => 001,
            'user_fname' => 'Jean Marie',
            'user_lname' => 'Tumulak',
            'user_mInitial' => 'J',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'educator1@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'finance',
            'is_temp_password' => false,
        ]);

        // Create educator user
        User::create([
            'user_id' => 002,
            'user_fname' => 'Angelica',
            'user_lname' => 'Ustaris',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'educator2@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        User::create([
            'user_id' => 003,
            'user_fname' => 'Glaiza',
            'user_lname' => 'Bejec',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'glaiza@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        User::create([
            'user_id' => 004,
            'user_fname' => 'Migay',
            'user_lname' => 'Magallen',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'gender' => 'F',
            'user_email' => 'migay@example.com',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        StudentDetails::create([
            'user_id' => 002,
            'student_id' => 002,
            'batch' => '2023',
            'group' => 'A',
            'student_number' => '002',
            'training_code' => '',
        ]);

        StudentDetails::create([
            'user_id' => 003,
            'student_id' => 003,
            'batch' => '2024',
            'group' => 'A',
            'student_number' => '003',
            'training_code' => '',
        ]);

        StudentDetails::create([
            'user_id' => 004,
            'student_id' => 004,
            'batch' => '2025',
            'group' => 'A',
            'student_number' => '004',
            'training_code' => '',
        ]);
    }
}
