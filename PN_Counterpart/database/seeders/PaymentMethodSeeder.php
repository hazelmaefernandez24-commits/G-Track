<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'GCash',
                'account_name' => 'PN Systems',
                'account_number' => '09123456789',
                'description' => 'Pay via GCash mobile wallet',
                'is_active' => true,
            ],
            [
                'name' => 'Cash',
                'account_name' => null,
                'account_number' => null,
                'description' => 'Cash payment at the office',
                'is_active' => true,
            ],
            [
                'name' => 'Bank Transfer',
                'account_name' => 'PN Systems Inc.',
                'account_number' => '1234567890',
                'description' => 'Bank transfer payment',
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name']],
                $method
            );
        }
    }
}
