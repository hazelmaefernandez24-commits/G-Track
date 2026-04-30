<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = \App\Models\Student::all();
        
        foreach ($students as $index => $student) {
            // Create a location near Cebu City (10.3157, 123.8854)
            \App\Models\Location::create([
                'student_id' => $student->id,
                'latitude' => 10.3157 + (rand(-100, 100) / 10000),
                'longitude' => 123.8854 + (rand(-100, 100) / 10000),
                'recorded_at' => now()->subMinutes(rand(1, 60)),
                'sos_status' => $student->sos_status ?? 'safe'
            ]);
        }
    }
}
