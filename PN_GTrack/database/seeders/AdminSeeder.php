<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
        ['email' => 'hazelmaefernandez@gmail.com'],
        [
            'name' => 'Admin',
            'password' => Hash::make('123456'),
        ]
    );

         User::updateOrCreate(
        ['email' => 'secondadmin@example.com'],
        [
            'name' => 'Second Admin',
            'password' => Hash::make('ambutnalang'),
        ]
    );
    }
}
