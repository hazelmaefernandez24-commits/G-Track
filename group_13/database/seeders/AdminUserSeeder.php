<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pnph_users')->insert([
            'user_id' => '002',
            'user_fname' => 'admin',
            'user_lname' => 'User',
            'user_mInitial' => null,
            'user_suffix' => null,
            'user_email' => 'admin@example.com',
            'user_role' => 'Admin',
            'user_password' => Hash::make('password'),
            'status' => 'active',
            'is_temp_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}