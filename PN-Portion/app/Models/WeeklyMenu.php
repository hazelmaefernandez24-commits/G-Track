<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'day_of_week',
        'meal_type',
        'week_cycle',
        'price',
        'is_available',
        'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'week_cycle' => 'integer'
    ];

    /**
     * Get the user who created this menu item
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include menu items for a specific week cycle
     */
    public function scopeWeekCycle($query, $weekCycle)
    {
        return $query->where('week_cycle', $weekCycle);
    }

    /**
     * Scope a query to only include menu items for a specific day
     */
    public function scopeDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope a query to only include menu items for a specific meal type
     */
    public function scopeMealType($query, $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    /**
     * Scope a query to only include available menu items
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
