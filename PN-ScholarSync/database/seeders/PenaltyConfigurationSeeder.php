<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PenaltyConfiguration;

class PenaltyConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penalties = [
            [
                'penalty_code' => 'VW',
                'display_name' => 'Verbal Warning',
                'short_label' => 'Verbal',
                'badge_class' => 'bg-info text-dark',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'penalty_code' => 'WW',
                'display_name' => 'Written Warning',
                'short_label' => 'Written',
                'badge_class' => 'bg-primary',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'penalty_code' => 'Pro',
                'display_name' => 'Probationary of Contract',
                'short_label' => 'Probation',
                'badge_class' => 'bg-warning text-dark',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'penalty_code' => 'T',
                'display_name' => 'Termination of Contract',
                'short_label' => 'Termination',
                'badge_class' => 'bg-danger',
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        foreach ($penalties as $penalty) {
            PenaltyConfiguration::updateOrCreate(
                ['penalty_code' => $penalty['penalty_code']],
                $penalty
            );
        }
    }
}
