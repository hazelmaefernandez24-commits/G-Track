<?php

namespace Database\Seeders;

use App\Models\PNUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing test users first
        PNUser::whereIn('user_id', ['100', '101', '102', '103', '123', 'admin', 'student', 'educator', 'training', 'test123'])->delete();

        // Update existing student password for testing
        PNUser::where('user_id', '0001')->update([
            'user_password' => Hash::make('student123'),
            'is_temp_password' => false
        ]);

        // Update existing educator password for testing
        PNUser::where('user_id', '3434')->update([
            'user_password' => Hash::make('educator123'),
            'is_temp_password' => false
        ]);

        // Create simple test users with easy credentials (using numeric IDs)
        PNUser::create([
            'user_id' => '100',
            'user_fname' => 'Admin',
            'user_lname' => 'User',
            'user_mInitial' => 'A',
            'user_suffix' => '',
            'user_email' => 'admin@test.com',
            'user_password' => Hash::make('admin123'),
            'status' => 'active',
            'user_role' => 'admin',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => '101',
            'user_fname' => 'Student',
            'user_lname' => 'Test',
            'user_mInitial' => 'S',
            'user_suffix' => '',
            'user_email' => 'student@test.com',
            'user_password' => Hash::make('student123'),
            'status' => 'active',
            'user_role' => 'student',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => '102',
            'user_fname' => 'Educator',
            'user_lname' => 'Test',
            'user_mInitial' => 'E',
            'user_suffix' => '',
            'user_email' => 'educator@test.com',
            'user_password' => Hash::make('educator123'),
            'status' => 'active',
            'user_role' => 'educator',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => '103',
            'user_fname' => 'Training',
            'user_lname' => 'Master',
            'user_mInitial' => 'T',
            'user_suffix' => '',
            'user_email' => 'training@test.com',
            'user_password' => Hash::make('training123'),
            'status' => 'active',
            'user_role' => 'training',
            'is_temp_password' => false,
        ]);

        PNUser::create([
            'user_id' => '123',
            'user_fname' => 'Simple',
            'user_lname' => 'Test',
            'user_mInitial' => 'T',
            'user_suffix' => '',
            'user_email' => 'simple@test.com',
            'user_password' => Hash::make('123'),
            'status' => 'active',
            'user_role' => 'admin',
            'is_temp_password' => false,
        ]);

        echo "Test users created successfully!\n";
        echo "Login credentials:\n";
        echo "- User ID: 100, Password: admin123 (Admin)\n";
        echo "- User ID: 101, Password: student123 (Student)\n";
        echo "- User ID: 102, Password: educator123 (Educator)\n";
        echo "- User ID: 103, Password: training123 (Training Master)\n";
        echo "- User ID: 123, Password: 123 (Simple Test)\n";
        echo "- User ID: 0001, Password: student123 (Edwardi - Real Student)\n";
        echo "- User ID: 3434, Password: educator123 (Jean - Real Educator)\n";
    }
}
