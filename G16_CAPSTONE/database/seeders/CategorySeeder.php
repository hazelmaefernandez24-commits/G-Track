<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Kitchen Operations Center',
            'Kitchen Dishwashing Station',
            'Kitchen Dining Service Area',
            'Offices Room(s)',
            'Conference Rooms',
            'Ground Floor Common Areas',
            'Rooftop Waste Management Center',
            'Rooftop Laundry Operations'
        ];

        foreach ($categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }
    }
}
