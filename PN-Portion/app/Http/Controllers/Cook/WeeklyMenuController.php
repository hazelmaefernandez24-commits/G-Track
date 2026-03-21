<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Services\LoggingService;
use Illuminate\Support\Facades\Auth;

class WeeklyMenuController extends Controller
{
    /**
     * Display the weekly menu management page
     */
    public function index()
    {
        // Get menus for week 1 & 3
        $week1Menus = $this->getMenusByWeekCycle(1);
        
        // Get menus for week 2 & 4
        $week2Menus = $this->getMenusByWeekCycle(2);
        
        return view('cook.weekly-menu', compact('week1Menus', 'week2Menus'));
    }
    
    /**
     * Update a menu item
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_available' => 'boolean'
        ]);
        
        $menu = Menu::findOrFail($validated['id']);
        
        // Store old values for logging
        $oldValues = [
            'name' => $menu->name,
            'description' => $menu->description,
            'price' => $menu->price,
            'is_available' => $menu->is_available
        ];
        
        // Prepare new values
        $newValues = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'is_available' => $request->has('is_available') ? $validated['is_available'] : $menu->is_available
        ];
        
        // Update the menu
        $menu->update($newValues);
        
        // Log the menu update
        LoggingService::logMenuUpdate(
            'Updated menu item: ' . $menu->name,
            $oldValues,
            $newValues,
            $request->getContent(),
            $request
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully'
        ]);
    }
    
    /**
     * Update multiple menu items for a specific day and week cycle
     */
    public function updateDay(Request $request)
    {
        $validated = $request->validate([
            'week_cycle' => 'required|in:1,2',
            'day_of_week' => 'required|string',
            'breakfast' => 'required|array',
            'breakfast.name' => 'required|string',
            'breakfast.description' => 'nullable|string',
            'breakfast.price' => 'required|numeric|min:0',
            'lunch' => 'required|array',
            'lunch.name' => 'required|string',
            'lunch.description' => 'nullable|string',
            'lunch.price' => 'required|numeric|min:0',
            'dinner' => 'required|array',
            'dinner.name' => 'required|string',
            'dinner.description' => 'nullable|string',
            'dinner.price' => 'required|numeric|min:0',
        ]);
        
        $weekCycle = $validated['week_cycle'];
        $dayOfWeek = $validated['day_of_week'];
        
        // Get current menu items for logging
        $oldMenuItems = $this->getCurrentMenuItems($weekCycle, $dayOfWeek);
        
        // Update breakfast
        $this->updateMealForDay($weekCycle, $dayOfWeek, 'breakfast', $validated['breakfast']);
        
        // Update lunch
        $this->updateMealForDay($weekCycle, $dayOfWeek, 'lunch', $validated['lunch']);
        
        // Update dinner
        $this->updateMealForDay($weekCycle, $dayOfWeek, 'dinner', $validated['dinner']);
        
        // Get updated menu items for logging
        $newMenuItems = $this->getCurrentMenuItems($weekCycle, $dayOfWeek);
        
        // Log the menu update
        LoggingService::logMenuUpdate(
            'Updated menu for ' . ucfirst($dayOfWeek) . ' (Week ' . $weekCycle . ')',
            $oldMenuItems,
            $newMenuItems,
            $request->getContent(),
            $request
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Menu for ' . ucfirst($dayOfWeek) . ' updated successfully'
        ]);
    }
    
    /**
     * Helper method to get menus by week cycle
     */
    private function getMenusByWeekCycle($weekCycle)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        
        $menus = [];
        
        foreach ($days as $day) {
            $menus[$day] = [];
            
            foreach ($mealTypes as $mealType) {
                $menu = Menu::where('week_cycle', $weekCycle)
                    ->where('day', ucfirst($day))
                    ->where('meal_type', $mealType)
                    ->first();
                
                $menus[$day][$mealType] = $menu ?? null;
            }
        }
        
        return $menus;
    }
    
    /**
     * Helper method to get current menu items for a specific day and week cycle
     */
    private function getCurrentMenuItems($weekCycle, $dayOfWeek)
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        $menuItems = [];
        
        foreach ($mealTypes as $mealType) {
            $menu = Menu::where('week_cycle', $weekCycle)
                ->where('day', ucfirst($dayOfWeek))
                ->where('meal_type', $mealType)
                ->first();
            
            if ($menu) {
                $menuItems[$mealType] = [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'description' => $menu->description,
                    'price' => $menu->price,
                    'is_available' => $menu->is_available
                ];
            } else {
                $menuItems[$mealType] = null;
            }
        }
        
        return $menuItems;
    }
    
    /**
     * Helper method to update a meal for a specific day and week cycle
     */
    private function updateMealForDay($weekCycle, $dayOfWeek, $mealType, $mealData)
    {
        $menu = Menu::where('week_cycle', $weekCycle)
            ->where('day', ucfirst($dayOfWeek))
            ->where('meal_type', $mealType)
            ->first();
        
        if ($menu) {
            // Update existing menu item
            $menu->update([
                'name' => $mealData['name'],
                'description' => $mealData['description'] ?? null,
                'price' => $mealData['price'],
            ]);
        } else {
            // Create new menu item
            Menu::create([
                'name' => $mealData['name'],
                'description' => $mealData['description'] ?? null,
                'day' => ucfirst($dayOfWeek),
                'meal_type' => $mealType,
                'week_cycle' => $weekCycle,
                'price' => $mealData['price'],
                'is_available' => true,
                'created_by' => Auth::id()
            ]);
        }
    }
    
    /**
     * Update weekly menu for multiple days
     */
    public function updateWeekly(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'week_cycle' => 'required|in:1,2',
            'selected_days' => 'required|string',
        ]);
        
        $weekCycle = $validated['week_cycle'];
        $selectedDays = json_decode($validated['selected_days'], true);
        
        if (!is_array($selectedDays) || empty($selectedDays)) {
            return response()->json([
                'success' => false,
                'message' => 'No days selected for update'
            ]);
        }
        
        // Process each day's menu data
        $updatedDays = [];
        $errors = [];
        
        foreach ($selectedDays as $day) {
            try {
                // Process breakfast
                if ($request->has($day . '_breakfast_name')) {
                    $breakfastData = [
                        'name' => $request->input($day . '_breakfast_name'),
                        'description' => $request->input($day . '_breakfast_ingredients'),
                        'price' => 0, // Default price or get from request if available
                    ];
                    $this->updateMealForDay($weekCycle, $day, 'breakfast', $breakfastData);
                }
                
                // Process lunch
                if ($request->has($day . '_lunch_name')) {
                    $lunchData = [
                        'name' => $request->input($day . '_lunch_name'),
                        'description' => $request->input($day . '_lunch_ingredients'),
                        'price' => 0, // Default price or get from request if available
                    ];
                    $this->updateMealForDay($weekCycle, $day, 'lunch', $lunchData);
                }
                
                // Process dinner
                if ($request->has($day . '_dinner_name')) {
                    $dinnerData = [
                        'name' => $request->input($day . '_dinner_name'),
                        'description' => $request->input($day . '_dinner_ingredients'),
                        'price' => 0, // Default price or get from request if available
                    ];
                    $this->updateMealForDay($weekCycle, $day, 'dinner', $dinnerData);
                }
                
                $updatedDays[] = $day;
            } catch (\Exception $e) {
                $errors[] = 'Error updating ' . ucfirst($day) . ': ' . $e->getMessage();
            }
        }
        
        // Log the menu update
        LoggingService::logMenuUpdate(
            'Updated weekly menu for Week ' . ($weekCycle == 1 ? '1 & 3' : '2 & 4'),
            ['days' => $selectedDays],
            ['updated_days' => $updatedDays],
            $request->getContent(),
            $request
        );
        
        if (empty($updatedDays)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update any days',
                'errors' => $errors
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Weekly menu updated successfully for ' . count($updatedDays) . ' day(s)',
            'updated_days' => $updatedDays,
            'errors' => $errors
        ]);
    }
}
