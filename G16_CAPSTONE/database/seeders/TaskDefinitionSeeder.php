<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskDefinition;
use App\Models\Category;

class TaskDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories for task assignment
        $kitchenCategory = Category::where('name', 'LIKE', '%Kitchen%')->first();
        $diningCategory = Category::where('name', 'LIKE', '%Dining%')->first();
        $officeCategory = Category::where('name', 'LIKE', '%Office%')->first();
        
        // Kitchen tasks
        if ($kitchenCategory) {
            $kitchenTasks = [
                [
                    'task_name' => 'Chopping Vegetables',
                    'task_description' => 'Prepare vegetables by chopping them into appropriate sizes for cooking',
                    'estimated_duration' => 30,
                    'difficulty_level' => 'easy'
                ],
                [
                    'task_name' => 'Cooking Rice',
                    'task_description' => 'Cook rice for the meal service, ensuring proper water ratio and timing',
                    'estimated_duration' => 45,
                    'difficulty_level' => 'medium'
                ],
                [
                    'task_name' => 'Preparing Main Course',
                    'task_description' => 'Cook the main viand/dish for the meal service',
                    'estimated_duration' => 60,
                    'difficulty_level' => 'hard'
                ],
                [
                    'task_name' => 'Kitchen Cleaning',
                    'task_description' => 'Clean and sanitize kitchen area after meal preparation',
                    'estimated_duration' => 30,
                    'difficulty_level' => 'easy'
                ],
                [
                    'task_name' => 'Ingredient Preparation',
                    'task_description' => 'Organize and prepare all ingredients before cooking time',
                    'estimated_duration' => 20,
                    'difficulty_level' => 'easy'
                ]
            ];

            foreach ($kitchenTasks as $task) {
                TaskDefinition::create(array_merge($task, [
                    'category_id' => $kitchenCategory->id,
                    'is_active' => true
                ]));
            }
        }

        // Dining tasks
        if ($diningCategory) {
            $diningTasks = [
                [
                    'task_name' => 'Table Setup',
                    'task_description' => 'Set up dining tables with proper utensils and arrangements',
                    'estimated_duration' => 20,
                    'difficulty_level' => 'easy'
                ],
                [
                    'task_name' => 'Food Service',
                    'task_description' => 'Serve food to students during meal times',
                    'estimated_duration' => 45,
                    'difficulty_level' => 'medium'
                ],
                [
                    'task_name' => 'Dining Area Cleaning',
                    'task_description' => 'Clean dining area after meal service',
                    'estimated_duration' => 25,
                    'difficulty_level' => 'easy'
                ]
            ];

            foreach ($diningTasks as $task) {
                TaskDefinition::create(array_merge($task, [
                    'category_id' => $diningCategory->id,
                    'is_active' => true
                ]));
            }
        }

        // Office tasks
        if ($officeCategory) {
            $officeTasks = [
                [
                    'task_name' => 'Office Cleaning',
                    'task_description' => 'Clean and organize office spaces',
                    'estimated_duration' => 30,
                    'difficulty_level' => 'easy'
                ],
                [
                    'task_name' => 'Document Organization',
                    'task_description' => 'Organize and file documents properly',
                    'estimated_duration' => 40,
                    'difficulty_level' => 'medium'
                ],
                [
                    'task_name' => 'Meeting Room Setup',
                    'task_description' => 'Prepare meeting rooms for conferences and meetings',
                    'estimated_duration' => 15,
                    'difficulty_level' => 'easy'
                ]
            ];

            foreach ($officeTasks as $task) {
                TaskDefinition::create(array_merge($task, [
                    'category_id' => $officeCategory->id,
                    'is_active' => true
                ]));
            }
        }

        $this->command->info('Task definitions seeded successfully!');
    }
}
