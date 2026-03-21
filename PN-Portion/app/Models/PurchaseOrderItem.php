<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'inventory_id',
        'item_name',
        'quantity_ordered',
        'quantity_delivered',
        'unit',
        'unit_price',
        'total_price',
        'notes'
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_delivered' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Boot method to calculate total price
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->total_price = $item->quantity_ordered * $item->unit_price;
        });
    }

    /**
     * Get the purchase order that owns this item
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the inventory item
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    /**
     * Helper methods
     */
    public function isFullyDelivered()
    {
        return $this->quantity_delivered >= $this->quantity_ordered;
    }

    public function getDeliveryPercentage()
    {
        if ($this->quantity_ordered == 0) return 0;
        return ($this->quantity_delivered / $this->quantity_ordered) * 100;
    }

    public function getRemainingQuantity()
    {
        return $this->quantity_ordered - $this->quantity_delivered;
    }
}
