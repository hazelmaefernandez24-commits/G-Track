<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'inventory_id',
        'quantity_per_serving',
        'unit'
    ];

    protected $casts = [
        'quantity_per_serving' => 'decimal:3'
    ];

    /**
     * Get the meal that owns this ingredient
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * Get the inventory item
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    /**
     * Calculate total quantity needed for given servings
     */
    public function calculateTotalQuantity($servings)
    {
        return $this->quantity_per_serving * $servings;
    }
}
