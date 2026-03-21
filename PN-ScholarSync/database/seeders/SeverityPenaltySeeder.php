<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeverityPenaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('severity_penalties')->insert([
            // LOW (severity_id = 1)
            ['severity_id' => 1, 'infraction_number' => 1, 'penalty' => 'Verbal Warning', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 1, 'infraction_number' => 2, 'penalty' => 'Written Warning', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 1, 'infraction_number' => 3, 'penalty' => 'Probationary', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 1, 'infraction_number' => 4, 'penalty' => 'Termination', 'created_at' => now(), 'updated_at' => now()],

            // MEDIUM (severity_id = 2)
            ['severity_id' => 2, 'infraction_number' => 1, 'penalty' => 'Written Warning', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 2, 'infraction_number' => 2, 'penalty' => 'Probationary', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 2, 'infraction_number' => 3, 'penalty' => 'Termination', 'created_at' => now(), 'updated_at' => now()],

            // HIGH (severity_id = 3)
            ['severity_id' => 3, 'infraction_number' => 1, 'penalty' => 'Probationary', 'created_at' => now(), 'updated_at' => now()],
            ['severity_id' => 3, 'infraction_number' => 2, 'penalty' => 'Termination', 'created_at' => now(), 'updated_at' => now()],

            // VERY HIGH (severity_id = 4)
            ['severity_id' => 4, 'infraction_number' => 1, 'penalty' => 'Termination', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
