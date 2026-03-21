<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\DynamicTask;

class DynamicTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing categories or create them
        $kitchenDining = Category::firstOrCreate(['name' => 'Kitchen & Dining']);
        $generalCleaning = Category::firstOrCreate(['name' => 'General Cleaning']);
        $moodPreparation = Category::firstOrCreate(['name' => 'Mood Preparation']);

        // KITCHEN & DINING TASKS (Always together as per requirements)
        $kitchenDiningTasks = [
            [
                'name' => 'Properly stored the basin and pail in their designated areas',
                'description' => 'Ensure all basins and pails are cleaned and stored in their proper designated locations after use.',
                'subtasks' => [
                    'Clean all basins thoroughly with soap and water',
                    'Rinse pails and remove any residue',
                    'Dry all items completely before storage',
                    'Place basins in designated storage area',
                    'Arrange pails in proper order',
                    'Check that storage area is clean and organized'
                ],
                'estimated_duration_minutes' => 15,
                'required_students' => 2,
                'gender_preference' => 'any'
            ],
            [
                'name' => 'Avoid wasting soap during washing',
                'description' => 'Use appropriate amount of soap for washing dishes and utensils to prevent waste.',
                'subtasks' => [
                    'Measure appropriate amount of soap for washing',
                    'Use soap dispenser properly',
                    'Avoid excessive soap usage',
                    'Monitor soap consumption during washing',
                    'Report any soap waste incidents',
                    'Educate others on proper soap usage'
                ],
                'estimated_duration_minutes' => 10,
                'required_students' => 1,
                'gender_preference' => 'any'
            ],
            [
                'name' => 'Cleaned the dishwashing area',
                'description' => 'Thoroughly clean and sanitize the entire dishwashing area including sinks, counters, and surrounding areas.',
                'subtasks' => [
                    'Clear all dishes and utensils from washing area',
                    'Scrub sinks with appropriate cleaning solution',
                    'Wipe down all counter surfaces',
                    'Clean faucets and handles',
                    'Sanitize the entire washing area',
                    'Organize cleaning supplies properly',
                    'Sweep and mop the floor around washing area'
                ],
                'estimated_duration_minutes' => 25,
                'required_students' => 3,
                'gender_preference' => 'mixed'
            ],
            [
                'name' => 'Ensured staff plates, utensils, and other items were properly cleaned and stored in their designated area',
                'description' => 'Complete cleaning and proper storage of all staff dining items according to hygiene standards.',
                'subtasks' => [
                    'Collect all used staff plates and utensils',
                    'Wash plates thoroughly with hot soapy water',
                    'Clean utensils individually and inspect for cleanliness',
                    'Dry all items completely',
                    'Store plates in designated staff storage area',
                    'Arrange utensils in proper containers',
                    'Verify all items are accounted for'
                ],
                'estimated_duration_minutes' => 20,
                'required_students' => 2,
                'gender_preference' => 'any'
            ]
        ];

        // GENERAL CLEANING TASKS
        $generalCleaningTasks = [
            [
                'name' => 'Cleaned the drainage canals',
                'description' => 'Clear and clean all drainage canals to ensure proper water flow and prevent blockages.',
                'subtasks' => [
                    'Remove visible debris from drainage openings',
                    'Clear leaves and organic matter from canals',
                    'Scrub canal walls to remove buildup',
                    'Check for and remove any blockages',
                    'Flush canals with clean water',
                    'Inspect drainage flow after cleaning',
                    'Report any structural damage found'
                ],
                'estimated_duration_minutes' => 45,
                'required_students' => 4,
                'gender_preference' => 'male'
            ],
            [
                'name' => 'Brushed and rinsed the floor of the dishwashing area',
                'description' => 'Deep clean the dishwashing area floor using proper brushing and rinsing techniques.',
                'subtasks' => [
                    'Clear the floor area of all obstacles',
                    'Sweep floor to remove loose debris',
                    'Apply appropriate floor cleaning solution',
                    'Scrub floor thoroughly with stiff brush',
                    'Pay special attention to corners and edges',
                    'Rinse floor completely with clean water',
                    'Ensure proper drainage of rinse water',
                    'Allow floor to air dry completely'
                ],
                'estimated_duration_minutes' => 30,
                'required_students' => 2,
                'gender_preference' => 'any'
            ],
            [
                'name' => 'Brushed the sink',
                'description' => 'Thoroughly clean and sanitize all sinks using appropriate brushing techniques.',
                'subtasks' => [
                    'Remove all items from sink area',
                    'Apply sink cleaning solution',
                    'Scrub sink basin with appropriate brush',
                    'Clean faucet and handles thoroughly',
                    'Remove any stains or buildup',
                    'Rinse sink completely with clean water',
                    'Dry and polish sink surface',
                    'Replace any removed items properly'
                ],
                'estimated_duration_minutes' => 15,
                'required_students' => 1,
                'gender_preference' => 'any'
            ],
            [
                'name' => 'Washed the barrel container',
                'description' => 'Clean and sanitize large barrel containers used for water or waste storage.',
                'subtasks' => [
                    'Empty barrel container completely',
                    'Rinse interior with clean water',
                    'Apply appropriate cleaning solution',
                    'Scrub interior walls thoroughly',
                    'Clean exterior surface of barrel',
                    'Rinse entire container multiple times',
                    'Inspect for cleanliness and damage',
                    'Allow to air dry before use'
                ],
                'estimated_duration_minutes' => 35,
                'required_students' => 3,
                'gender_preference' => 'male'
            ]
        ];

        // Create Kitchen & Dining tasks
        foreach ($kitchenDiningTasks as $index => $taskData) {
            DynamicTask::create(array_merge($taskData, [
                'category_id' => $kitchenDining->id,
                'sort_order' => $index + 1
            ]));
        }

        // Create General Cleaning tasks
        foreach ($generalCleaningTasks as $index => $taskData) {
            DynamicTask::create(array_merge($taskData, [
                'category_id' => $generalCleaning->id,
                'sort_order' => $index + 1
            ]));
        }

        // MOOD PREPARATION TASKS (to be added)
        $moodPreparationTasks = [
            [
                'name' => 'Prepare classroom environment for learning',
                'description' => 'Set up classroom to create a positive learning atmosphere.',
                'subtasks' => [
                    'Arrange desks and chairs properly',
                    'Clean whiteboard and prepare markers',
                    'Check lighting and ventilation',
                    'Organize learning materials',
                    'Set up any required equipment'
                ],
                'estimated_duration_minutes' => 20,
                'required_students' => 2,
                'gender_preference' => 'any'
            ]
        ];

        // Create Mood Preparation tasks
        foreach ($moodPreparationTasks as $index => $taskData) {
            DynamicTask::create(array_merge($taskData, [
                'category_id' => $moodPreparation->id,
                'sort_order' => $index + 1
            ]));
        }
    }
}
