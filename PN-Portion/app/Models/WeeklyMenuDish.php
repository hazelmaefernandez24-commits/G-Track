<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WeeklyMenuDish extends Model
{
    use HasFactory;

    protected $fillable = [
        'dish_name',
        'description',
        'day_of_week',
        'meal_type',
        'week_cycle',
        'created_by'
    ];

    protected $casts = [
        'week_cycle' => 'integer'
    ];

    /**
     * Get the user who created this dish
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the ingredients for this dish
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'weekly_menu_dish_ingredients', 'weekly_menu_dish_id', 'inventory_id')
                    ->withPivot('quantity_used', 'unit')
                    ->withTimestamps();
    }

    /**
     * Deduct ingredients from inventory when dish is created
     */
    public function deductIngredientsFromInventory()
    {
        foreach ($this->ingredients as $ingredient) {
            $inventoryItem = $ingredient;
            $quantityUsed = $ingredient->pivot->quantity_used;

            if ($inventoryItem->quantity >= $quantityUsed) {
                $previousQuantity = $inventoryItem->quantity;
                $inventoryItem->quantity -= $quantityUsed;
                $inventoryItem->last_updated_by = $this->created_by;
                $inventoryItem->save();

                // Log inventory history
                InventoryHistory::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => $this->created_by,
                    'action_type' => 'weekly_menu_creation',
                    'quantity_change' => -$quantityUsed,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $inventoryItem->quantity,
                    'notes' => "Used for weekly menu dish: {$this->dish_name}"
                ]);
            }
        }
    }

    /**
     * Check if dish can be prepared with current inventory
     */
    public function canBePrepared()
    {
        foreach ($this->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity_used;
            if ($ingredient->quantity < $requiredQuantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get missing ingredients
     */
    public function getMissingIngredients()
    {
        $missing = [];
        foreach ($this->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity_used;
            $availableQuantity = $ingredient->quantity;

            if ($availableQuantity < $requiredQuantity) {
                $missing[] = [
                    'name' => $ingredient->name,
                    'required' => $requiredQuantity,
                    'available' => $availableQuantity,
                    'shortage' => $requiredQuantity - $availableQuantity,
                    'unit' => $ingredient->pivot->unit
                ];
            }
        }
        return $missing;
    }

    /**
     * Scopes
     */
    public function scopeWeekCycle($query, $weekCycle)
    {
        return $query->where('week_cycle', $weekCycle);
    }

    public function scopeDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeMealType($query, $mealType)
    {
        return $query->where('meal_type', $mealType);
    }
}
