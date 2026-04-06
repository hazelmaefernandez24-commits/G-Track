<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       \App\Models\Student::firstOrCreate(
    ['email' => 'john@example.com'],
    ['name' => 'John Doe',
     'gender' => 'male', 
     'class' => '2026']
);

\App\Models\Student::firstOrCreate(
    ['email' => 'jane@example.com'],
    ['name' => 'Jane Smith', 
    'gender' => 'female',
     'class' => '2027']
);

\App\Models\Student::firstOrCreate(
    ['email' => 'bob@example.com'],
    ['name' => 'Bob Johnson', 
    'gender' => 'male', 
    'class' => '2026']
);
    }
}
