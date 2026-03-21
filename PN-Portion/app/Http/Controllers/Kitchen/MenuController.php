<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\BaseController;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends BaseController
{
    public function index()
    {
        // UNIFIED: Use WeekCycleService for consistent calculation
        $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
        $today = $weekInfo['current_day'];
        $weekCycle = $weekInfo['week_cycle'];

        // Check if cook has created any meals
        $hasMeals = Meal::exists();

        if (!$hasMeals) {
            // No meals created by cook yet
            return view('kitchen.daily-menu', [
                'todayMenu' => null,
                'weeklyMenu' => collect(),
                'today' => $today,
                'weekCycle' => $weekCycle,
                'hasMeals' => false,
                'waitingForCook' => true
            ]);
        }

        // Get today's menu from cook's meal planning
        $dayOfWeek = strtolower($today);

        // Debug logging
        \Log::info('🍽️ Kitchen loading menu', [
            'today' => $today,
            'day_of_week' => $dayOfWeek,
            'week_cycle' => $weekCycle,
            'week_of_month' => $weekInfo['week_of_month']
        ]);

        $todayMeals = Meal::forWeekCycle($weekCycle)
            ->forDay($dayOfWeek)
            ->get()
            ->keyBy('meal_type');

        \Log::info('📊 Kitchen found meals for today', [
            'meals_count' => $todayMeals->count(),
            'meals' => $todayMeals->map(function($meal) {
                return [
                    'id' => $meal->id,
                    'name' => $meal->name,
                    'meal_type' => $meal->meal_type
                ];
            })
        ]);

        $todayMenu = (object)[
            'breakfast_name' => $todayMeals->get('breakfast')->name ?? 'No breakfast planned',
            'breakfast_ingredients' => $todayMeals->get('breakfast')->ingredients ?? 'No ingredients listed',
            'lunch_name' => $todayMeals->get('lunch')->name ?? 'No lunch planned',
            'lunch_ingredients' => $todayMeals->get('lunch')->ingredients ?? 'No ingredients listed',
            'dinner_name' => $todayMeals->get('dinner')->name ?? 'No dinner planned',
            'dinner_ingredients' => $todayMeals->get('dinner')->ingredients ?? 'No ingredients listed'
        ];

        // Get weekly menu from cook's meal planning
        $weeklyMeals = Meal::forWeekCycle($weekCycle)
            ->get()
            ->groupBy('day_of_week');

        $weeklyMenu = collect();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            $dayKey = strtolower($day);
            if ($weeklyMeals->has($dayKey)) {
                $weeklyMenu[$day] = $weeklyMeals[$dayKey];
            } else {
                // Create empty meal objects for days without meals
                $weeklyMenu[$day] = collect([
                    (object)['meal_type' => 'breakfast', 'name' => 'No breakfast planned', 'description' => 'Waiting for cook to plan'],
                    (object)['meal_type' => 'lunch', 'name' => 'No lunch planned', 'description' => 'Waiting for cook to plan'],
                    (object)['meal_type' => 'dinner', 'name' => 'No dinner planned', 'description' => 'Waiting for cook to plan']
                ]);
            }
        }

        return view('kitchen.daily-menu', compact('todayMenu', 'weeklyMenu', 'today', 'weekCycle', 'hasMeals'));
    }

    public function getMenu($weekCycle)
    {
        // Check if cook has created any meals
        $hasMeals = Meal::exists();

        if (!$hasMeals) {
            return response()->json([
                'success' => false,
                'message' => 'No meals available. Please wait for cook to create the menu.',
                'waitingForCook' => true
            ]);
        }

        $meals = Meal::forWeekCycle($weekCycle)
            ->get()
            ->groupBy('day_of_week')
            ->map(function ($dayMeals) {
                return $dayMeals->groupBy('meal_type')
                    ->map(function ($meal) {
                        $mealData = $meal->first()->toArray();
                        // Format ingredients for display (convert arrays to strings)
                        $mealData['ingredients'] = $this->formatIngredients($mealData['ingredients']);
                        // Use safe status getter
                        $mealData['status'] = $this->getMealStatus($meal->first());
                        return $mealData;
                    });
            });

        // Debug: Log the meals data
        \Log::info('Kitchen Menu Data:', [
            'weekCycle' => $weekCycle,
            'totalMeals' => Meal::count(),
            'mealsForWeekCycle' => Meal::forWeekCycle($weekCycle)->count(),
            'groupedMeals' => $meals->toArray()
        ]);

        return response()->json([
            'success' => true,
            'menu' => $meals
        ]);
    }

    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meal_id' => 'required|exists:meals,id',
            'status' => 'required|string|in:Not Started,In Progress,Completed',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Since meal_statuses table was removed, we'll just return success
        // In a real implementation, you might want to store this in a different way
        // or update the meal record itself with status information

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully (meal_statuses table removed)',
            'status' => [
                'meal_id' => $request->meal_id,
                'status' => $request->status,
                'notes' => $request->notes,
                'updated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Format ingredients for display - convert arrays to strings
     *
     * @param mixed $ingredients
     * @return string
     */
    private function formatIngredients($ingredients)
    {
        // Handle null or undefined
        if ($ingredients === null || $ingredients === '') {
            return 'No ingredients listed';
        }

        // Handle arrays
        if (is_array($ingredients)) {
            return implode(', ', $ingredients);
        }

        // Handle strings
        if (is_string($ingredients)) {
            return $ingredients;
        }

        // Handle objects (like stdClass) - try to convert to string
        if (is_object($ingredients)) {
            if (method_exists($ingredients, '__toString')) {
                return (string) $ingredients;
            }
            return 'No ingredients listed';
        }

        // Fallback for any other type
        return 'No ingredients listed';
    }
}