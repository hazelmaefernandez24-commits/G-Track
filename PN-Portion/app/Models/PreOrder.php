<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'date',
        'meal_type',
        'is_attending',
        'is_prepared',
        'notes',
        'special_requests', // For compatibility
    ];

    protected $casts = [
        'date' => 'date',
        'is_attending' => 'boolean',
        'is_prepared' => 'boolean',
    ];

    /**
     * Get the user that owns the pre-order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the menu item for this pre-order.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
