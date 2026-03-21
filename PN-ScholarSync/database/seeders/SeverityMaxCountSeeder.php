<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeverityMaxCount;
use App\Models\Severity;

class SeverityMaxCountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all severities
        $severities = Severity::all();

        // Default configuration for each severity
        $defaultConfigs = [
            'Low' => [
                'max_count' => 1,
                'base_penalty' => 'VW',
                'escalated_penalty' => 'WW',
                'description' => 'Low severity violations: Minor infractions like dress code violations, tardiness, or minor disruptions.'
            ],
            'Medium' => [
                'max_count' => 1,
                'base_penalty' => 'WW',
                'escalated_penalty' => 'Pro',
                'description' => 'Medium severity violations: Behavioral issues like disrespectful behavior, cheating, or skipping classes.'
            ],
            'High' => [
                'max_count' => 1,
                'base_penalty' => 'Pro',
                'escalated_penalty' => 'T',
                'description' => 'High severity violations: Serious misconduct like bullying, vandalism, or fighting.'
            ],
            'Very High' => [
                'max_count' => 1,
                'base_penalty' => 'T',
                'escalated_penalty' => 'T',
                'description' => 'Very high severity violations: Severe violations like violence, weapons, or criminal activity.'
            ]
        ];

        foreach ($severities as $severity) {
            $config = $defaultConfigs[$severity->severity_name] ?? [
                'max_count' => 3,
                'base_penalty' => 'VW',
                'escalated_penalty' => 'WW',
                'description' => 'Default configuration for ' . $severity->severity_name . ' severity violations.'
            ];

            SeverityMaxCount::updateOrCreate(
                ['severity_id' => $severity->id],
                [
                    'severity_name' => $severity->severity_name,
                    'max_count' => $config['max_count'],
                    'base_penalty' => $config['base_penalty'],
                    'escalated_penalty' => $config['escalated_penalty'],
                    'description' => $config['description']
                ]
            );
        }
    }
}
