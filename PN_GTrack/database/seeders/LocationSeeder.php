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
        \App\Models\Location::create([
            'student_id' => 1,
            'latitude' => 10.3157,
            'longitude' => 123.8854,
            'recorded_at' => now(),
        ]);
        \App\Models\Location::create([
            'student_id' => 2,
            'latitude' => 10.3160,
            'longitude' => 123.8860,
            'recorded_at' => now(),
        ]);
        \App\Models\Location::create([
            'student_id' => 3,
            'latitude' => 10.3140,
            'longitude' => 123.8840,
            'recorded_at' => now(),
        ]);
    }
}
