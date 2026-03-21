<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeveritySeeder extends Seeder 
{
    public function run(): void
    {
       DB::table('severities')->insert([
        ['severity_name' => 'Low', 'max_infraction' => 4, 'is_active' => true],
        ['severity_name' => 'Medium', 'max_infraction' => 3, 'is_active' => true],
        ['severity_name' => 'High', 'max_infraction' => 2, 'is_active' => true],
        ['severity_name' => 'Very High', 'max_infraction' => 1, 'is_active' => true],
    ]);
    }
}
