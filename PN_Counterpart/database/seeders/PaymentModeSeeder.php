<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMode;

class PaymentModeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $paymentModes = [
            ['name' => 'GCash'],
            ['name' => 'Cash'],
            ['name' => 'Bank Transfer'],
        ];

        foreach ($paymentModes as $mode) {
            PaymentMode::updateOrCreate(
                ['name' => $mode['name']],
                $mode
            );
        }
    }
}
