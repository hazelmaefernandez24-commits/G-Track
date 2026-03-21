<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryHistory extends Model
{
    protected $table = 'inventory_history';

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'action_type',
        'quantity_change',
        'previous_quantity',
        'new_quantity',
        'notes'
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2'
    ];

    public function item()
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('inventory_item_id', $itemId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
} 