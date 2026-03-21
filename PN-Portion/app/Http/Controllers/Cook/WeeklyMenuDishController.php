<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\WeeklyMenuDish;
use App\Models\Inventory;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WeeklyMenuDishController extends Controller
{
    /**
     * Display weekly menu dishes management page
     */
    public function index()
    {
        // Get all inventory items for ingredient selection
        $inventoryItems = Inventory::orderBy('name')->get();

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

        return view('cook.weekly-menu-dishes.index', compact(
            'inventoryItems', 
            'week1Dishes', 
            'week2Dishes',
            'today',
            'currentWeek',
            'todaysDishes'
        ));
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

    /**
     * Store a new weekly menu dish
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dish_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meal_type' => 'required|string|in:breakfast,lunch,dinner',
            'week_cycle' => 'required|integer|in:1,2',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.inventory_id' => 'required|exists:inventory,id',
            'ingredients.*.quantity_used' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Check if dish already exists for this slot
            $existingDish = WeeklyMenuDish::where('week_cycle', $request->week_cycle)
                ->where('day_of_week', $request->day_of_week)
                ->where('meal_type', $request->meal_type)
                ->first();

            if ($existingDish) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'A dish already exists for this time slot. Please delete it first or use the update function.')
                    ->withInput();
            }

            // Check if all ingredients are available in sufficient quantity
            $insufficientIngredients = [];
            foreach ($request->ingredients as $ingredientData) {
                $inventoryItem = Inventory::find($ingredientData['inventory_id']);
                if ($inventoryItem->quantity < $ingredientData['quantity_used']) {
                    $insufficientIngredients[] = [
                        'name' => $inventoryItem->name,
                        'available' => $inventoryItem->quantity,
                        'required' => $ingredientData['quantity_used'],
                        'unit' => $ingredientData['unit']
                    ];
                }
            }

            if (!empty($insufficientIngredients)) {
                DB::rollBack();
                $errorMessage = 'Insufficient inventory for the following ingredients: ';
                foreach ($insufficientIngredients as $item) {
                    $errorMessage .= "{$item['name']} (Available: {$item['available']} {$item['unit']}, Required: {$item['required']} {$item['unit']}), ";
                }
                return redirect()->back()
                    ->with('error', rtrim($errorMessage, ', '))
                    ->withInput();
            }

            // Create the dish
            $dish = WeeklyMenuDish::create([
                'dish_name' => $request->dish_name,
                'description' => $request->description,
                'day_of_week' => $request->day_of_week,
                'meal_type' => $request->meal_type,
                'week_cycle' => $request->week_cycle,
                'created_by' => Auth::user()->user_id
            ]);

            // Attach ingredients and deduct from inventory
            foreach ($request->ingredients as $ingredientData) {
                // Attach ingredient to dish
                $dish->ingredients()->attach($ingredientData['inventory_id'], [
                    'quantity_used' => $ingredientData['quantity_used'],
                    'unit' => $ingredientData['unit']
                ]);

                // Deduct from inventory
                $inventoryItem = Inventory::find($ingredientData['inventory_id']);
                $previousQuantity = $inventoryItem->quantity;
                $inventoryItem->quantity -= $ingredientData['quantity_used'];
                $inventoryItem->last_updated_by = Auth::user()->user_id;
                $inventoryItem->save();

                // Log inventory history
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => Auth::user()->user_id,
                    'action_type' => 'weekly_menu_creation',
                    'quantity_change' => -$ingredientData['quantity_used'],
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Used for weekly menu dish: {$dish->dish_name} ({$request->day_of_week} {$request->meal_type})"
                ]);
            }

            DB::commit();

            return redirect()->route('cook.weekly-menu-dishes.index')
                ->with('success', 'Weekly menu dish created successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create dish: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show a specific dish
     */
    public function show(WeeklyMenuDish $weeklyMenuDish)
    {
        $weeklyMenuDish->load('ingredients', 'creator');
        
        // If it's an AJAX request, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($weeklyMenuDish);
        }
        
        return view('cook.weekly-menu-dishes.show', compact('weeklyMenuDish'));
    }

    /**
     * Update a weekly menu dish
     */
    public function update(Request $request, WeeklyMenuDish $weeklyMenuDish)
    {
        $validator = Validator::make($request->all(), [
            'dish_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.inventory_id' => 'required|exists:inventory,id',
            'ingredients.*.quantity_used' => 'required|numeric|min:0.01',
            'ingredients.*.unit' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // First, restore the previously used ingredients back to inventory
            foreach ($weeklyMenuDish->ingredients as $ingredient) {
                $inventoryItem = $ingredient;
                $previousQuantity = $inventoryItem->quantity;
                $quantityToRestore = $ingredient->pivot->quantity_used;
                
                $inventoryItem->quantity += $quantityToRestore;
                $inventoryItem->last_updated_by = Auth::user()->user_id;
                $inventoryItem->save();

                // Log the restoration
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => Auth::user()->user_id,
                    'action_type' => 'weekly_menu_update_restore',
                    'quantity_change' => $quantityToRestore,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Restored from updating dish: {$weeklyMenuDish->dish_name}"
                ]);
            }

            // Detach old ingredients
            $weeklyMenuDish->ingredients()->detach();

            // Check if new ingredients are available
            $insufficientIngredients = [];
            foreach ($request->ingredients as $ingredientData) {
                $inventoryItem = Inventory::find($ingredientData['inventory_id']);
                if ($inventoryItem->quantity < $ingredientData['quantity_used']) {
                    $insufficientIngredients[] = [
                        'name' => $inventoryItem->name,
                        'available' => $inventoryItem->quantity,
                        'required' => $ingredientData['quantity_used'],
                        'unit' => $ingredientData['unit']
                    ];
                }
            }

            if (!empty($insufficientIngredients)) {
                DB::rollBack();
                $errorMessage = 'Insufficient inventory for the following ingredients: ';
                foreach ($insufficientIngredients as $item) {
                    $errorMessage .= "{$item['name']} (Available: {$item['available']} {$item['unit']}, Required: {$item['required']} {$item['unit']}), ";
                }
                return redirect()->back()
                    ->with('error', rtrim($errorMessage, ', '))
                    ->withInput();
            }

            // Update dish details
            $weeklyMenuDish->update([
                'dish_name' => $request->dish_name,
                'description' => $request->description
            ]);

            // Attach new ingredients and deduct from inventory
            foreach ($request->ingredients as $ingredientData) {
                // Attach ingredient to dish
                $weeklyMenuDish->ingredients()->attach($ingredientData['inventory_id'], [
                    'quantity_used' => $ingredientData['quantity_used'],
                    'unit' => $ingredientData['unit']
                ]);

                // Deduct from inventory
                $inventoryItem = Inventory::find($ingredientData['inventory_id']);
                $previousQuantity = $inventoryItem->quantity;
                $inventoryItem->quantity -= $ingredientData['quantity_used'];
                $inventoryItem->last_updated_by = Auth::user()->user_id;
                $inventoryItem->save();

                // Log inventory history
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => Auth::user()->user_id,
                    'action_type' => 'weekly_menu_update',
                    'quantity_change' => -$ingredientData['quantity_used'],
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Used for updated dish: {$weeklyMenuDish->dish_name}"
                ]);
            }

            DB::commit();

            return redirect()->route('cook.weekly-menu-dishes.index')
                ->with('success', 'Weekly menu dish updated successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update dish: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a weekly menu dish
     */
    public function destroy(WeeklyMenuDish $weeklyMenuDish)
    {
        DB::beginTransaction();
        try {
            // Restore ingredients back to inventory
            foreach ($weeklyMenuDish->ingredients as $ingredient) {
                $inventoryItem = $ingredient;
                $previousQuantity = $inventoryItem->quantity;
                $quantityToRestore = $ingredient->pivot->quantity_used;
                
                $inventoryItem->quantity += $quantityToRestore;
                $inventoryItem->last_updated_by = Auth::user()->user_id;
                $inventoryItem->save();

                // Log the restoration
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => Auth::user()->user_id,
                    'action_type' => 'weekly_menu_deletion',
                    'quantity_change' => $quantityToRestore,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Restored from deleting dish: {$weeklyMenuDish->dish_name}"
                ]);
            }

            // Delete the dish
            $weeklyMenuDish->delete();

            DB::commit();

            return redirect()->route('cook.weekly-menu-dishes.index')
                ->with('success', 'Weekly menu dish deleted successfully! Ingredients have been restored to inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete dish: ' . $e->getMessage());
        }
    }

    /**
     * Get available inventory items (AJAX)
     */
    public function getAvailableInventory()
    {
        $items = Inventory::orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_type' => $item->item_type ?? $item->category,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'status' => $item->status
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Check ingredient availability (AJAX)
     */
    public function checkIngredientAvailability(Request $request)
    {
        $ingredients = $request->input('ingredients', []);
        $results = [];

        foreach ($ingredients as $ingredientData) {
            $inventoryItem = Inventory::find($ingredientData['inventory_id']);
            if ($inventoryItem) {
                $results[] = [
                    'inventory_id' => $inventoryItem->id,
                    'name' => $inventoryItem->name,
                    'available' => $inventoryItem->quantity,
                    'required' => $ingredientData['quantity_used'],
                    'unit' => $ingredientData['unit'],
                    'sufficient' => $inventoryItem->quantity >= $ingredientData['quantity_used']
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
}
