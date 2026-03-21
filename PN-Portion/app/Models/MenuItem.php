<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'inventory_item_id',
        'quantity_required'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id');
    }
}
