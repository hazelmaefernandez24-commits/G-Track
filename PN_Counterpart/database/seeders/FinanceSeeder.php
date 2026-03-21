<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update the email for the finance user with the given user_id
        DB::table('finances')->where('user_id', 'FIN2025001')->update([
            'email' => 'gerlieannkatherine.dagaas@gmail.com',
        ]);
    }
}
