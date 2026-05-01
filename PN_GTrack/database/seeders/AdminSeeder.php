<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'hazelmaefernandez@gmail.com'],
            [
                'staff_id' => 'ADMIN001',
                'password' => Hash::make('123456'),
                'role' => 'main',
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'education@example.com'],
            [
                'staff_id' => 'EDU001',
                'password' => Hash::make('password123'),
                'role' => 'education',
            ]
        );
    }
}
