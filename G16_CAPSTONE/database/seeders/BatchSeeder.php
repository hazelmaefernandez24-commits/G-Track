<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Batch;
use Carbon\Carbon;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        // Create initial batches
        $batches_group16 = [
            ['year' => 2025, 'name' => 'Batch 2025', 'is_active' => true],
            ['year' => 2026, 'name' => 'Batch 2026', 'is_active' => true],
        ];

        foreach ($batches_group16 as $batch) {
            Batch::updateOrCreate(
                ['year' => $batch['year']],
                [
                    'name' => $batch['name'],
                    'is_active' => $batch['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
    }
}
