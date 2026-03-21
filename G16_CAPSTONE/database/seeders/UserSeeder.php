<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Clear the table before inserting (CAUTION: this deletes all users!)
        // DB::table('pnph_users')->truncate();
        DB::table('student_details')->delete(); // Optional: clear dependent table first
        DB::table('pnph_users')->delete();

        DB::table('pnph_users')->insert([
            [
                'user_id' => 'I2025001',
                'user_fname' => 'Marilyn',
                'user_lname' => 'Avila',
                'user_mInitial' => null,
                'user_suffix' => null,
                'gender' => 'F',
                'user_email' => 'inspector1@gmail.com',
                'user_role' => 'inspector',
                'user_password' => Hash::make('password123'),
                'status' => 'active',
                'is_temp_password' => true,
                'token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 'I2025002',
                'user_fname' => 'Catherine',
                'user_lname' => 'Cuyos',
                'user_mInitial' => null,
                'user_suffix' => null,
                'gender' => 'F',
                'user_email' => 'inspector2@gmail.com',
                'user_role' => 'inspector',
                'user_password' => Hash::make('password123'),
                'status' => 'active',
                'is_temp_password' => true,
                'token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 'E2025001',
                'user_fname' => 'Jean Marrie',
                'user_lname' => 'Tumulak',
                'user_mInitial' => null,
                'user_suffix' => null,
                'gender' => 'F',
                'user_email' => 'educator1@gmail.com',
                'user_role' => 'educator',
                'user_password' => Hash::make('password123'),
                'status' => 'active',
                'is_temp_password' => true,
                'token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 'E2025002',
                'user_fname' => 'Faire Yubel',
                'user_lname' => 'Yaun',
                'user_mInitial' => null,
                'user_suffix' => null,
                'gender' => 'F', // <-- Fix this
                'user_email' => 'educator2@gmail.com',
                'user_role' => 'educator',
                'user_password' => Hash::make('password123'),
                'status' => 'active',
                'is_temp_password' => true,
                'token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => '2025010001C1',
                'user_fname' => 'Geralyn',
                'user_lname' => 'Monares',
                'user_mInitial' => 'C',
                'user_suffix' => null,
                'gender' => 'F', // <-- Fix this
                'user_email' => 'student1@gmail.com',
                'user_role' => 'student',
                'user_password' => Hash::make('password123'),
                'status' => 'active',
                'is_temp_password' => true,
                'token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}

