<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert the global counterpart payment start settings (fallback)
        $globalSettings = [
            [
                'setting_key' => 'counterpart_payment_start_month',
                'setting_value' => '1', // January by default
                'setting_type' => 'integer',
                'description' => 'Global starting month for counterpart payments (1-12) - used as fallback for batches without specific settings',
                'category' => 'matrix_settings',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'counterpart_payment_start_year',
                'setting_value' => date('Y'), // Current year by default
                'setting_type' => 'integer',
                'description' => 'Global starting year for counterpart payments - used as fallback for batches without specific settings',
                'category' => 'matrix_settings',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($globalSettings as $setting) {
            DB::table('finance_settings')->insertOrIgnore($setting);
        }

        // Dynamically create batch-specific settings for existing batches
        $existingBatches = DB::table('batches')->pluck('batch_year');
        
        foreach ($existingBatches as $batchYear) {
            $batchSettings = [
                [
                    'setting_key' => "batch_counterpart_payment_start_month_{$batchYear}",
                    'setting_value' => '1', // January by default
                    'setting_type' => 'integer',
                    'description' => "Starting month for Batch {$batchYear} counterpart payments (1-12)",
                    'category' => 'matrix_settings',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'setting_key' => "batch_counterpart_payment_start_year_{$batchYear}",
                    'setting_value' => $batchYear,
                    'setting_type' => 'integer',
                    'description' => "Starting year for Batch {$batchYear} counterpart payments",
                    'category' => 'matrix_settings',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];

            foreach ($batchSettings as $setting) {
                DB::table('finance_settings')->insertOrIgnore($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove global counterpart payment start settings
        DB::table('finance_settings')
            ->whereIn('setting_key', [
                'counterpart_payment_start_month',
                'counterpart_payment_start_year'
            ])
            ->delete();

        // Remove all batch-specific counterpart payment start settings
        DB::table('finance_settings')
            ->where('setting_key', 'like', 'batch_counterpart_payment_start_%')
            ->delete();
    }
};
