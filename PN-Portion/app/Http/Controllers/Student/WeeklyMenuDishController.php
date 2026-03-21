<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\WeeklyMenuDish;

class WeeklyMenuDishController extends Controller
{
    /**
     * Display weekly menu dishes (View Only for Students)
     */
    public function index()
    {
        // Get dishes for week 1
        $week1Dishes = $this->getDishesByWeekCycle(1);
        
        // Get dishes for week 2
        $week2Dishes = $this->getDishesByWeekCycle(2);

        // Get today's information
        $today = strtolower(now()->format('l')); // e.g., 'monday'
        $currentWeek = $this->getCurrentWeekCycle(); // 1 or 2
        
        // Get today's menu based on current week cycle
        $todaysMenu = $currentWeek == 1 ? $week1Dishes : $week2Dishes;
        $todaysDishes = $todaysMenu[$today] ?? [];

        return view('student.weekly-menu-dishes.index', compact(
            'week1Dishes', 
            'week2Dishes',
            'today',
            'currentWeek',
            'todaysDishes'
        ));
    }

    /**
     * Show dish details
     */
    public function show($id)
    {
        $dish = WeeklyMenuDish::with('ingredients')->findOrFail($id);
        return view('student.weekly-menu-dishes.show', compact('dish'));
    }

    /**
     * Get current week cycle (1 or 2) based on week number
     */
    private function getCurrentWeekCycle()
    {
        $weekNumber = now()->weekOfYear;
        return ($weekNumber % 2 == 0) ? 2 : 1;
    }

    /**
     * Get dishes organized by week cycle
     */
    private function getDishesByWeekCycle($weekCycle)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        
        $dishes = [];
        
        foreach ($days as $day) {
            $dishes[$day] = [];
            
            foreach ($mealTypes as $mealType) {
                $dish = WeeklyMenuDish::with('ingredients')
                    ->where('week_cycle', $weekCycle)
                    ->where('day_of_week', $day)
                    ->where('meal_type', $mealType)
                    ->first();
                
                $dishes[$day][$mealType] = $dish;
            }
        }
        
        return $dishes;
    }
}
