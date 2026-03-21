<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_check_id',
        'ingredient_id',
        'current_stock',
        'needs_restock',
        'notes',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'needs_restock' => 'boolean',
    ];

    /**
     * Get the inventory check that owns the item.
     */
    public function inventoryCheck(): BelongsTo
    {
        return $this->belongsTo(InventoryCheck::class);
    }

    /**
     * Get the inventory item for this check item.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'ingredient_id');
    }

    /**
     * Alias for ingredient() to maintain backward compatibility
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'ingredient_id');
    }
}
