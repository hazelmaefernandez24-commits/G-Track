<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMenuUpdate extends Model
{
    protected $fillable = [
        'menu_date',
        'meal_type',
        'meal_name',
        'ingredients',
        'status',
        'estimated_portions',
        'actual_portions',
        'updated_by'
    ];

    protected $casts = [
        'menu_date' => 'date',
        'estimated_portions' => 'integer',
        'actual_portions' => 'integer'
    ];

    // Relationships
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->where('menu_date', $date);
    }

    public function scopeForMealType($query, $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    public function scopeToday($query)
    {
        return $query->where('menu_date', today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'planned' => 'bg-secondary',
            'preparing' => 'bg-warning',
            'ready' => 'bg-success',
            'served' => 'bg-info'
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    public function getPortionDifferenceAttribute()
    {
        return $this->actual_portions - $this->estimated_portions;
    }

    public function updateStatus($status, $userId)
    {
        $this->update([
            'status' => $status,
            'updated_by' => $userId
        ]);
    }

    public function updatePortions($estimated, $actual, $userId)
    {
        $this->update([
            'estimated_portions' => $estimated,
            'actual_portions' => $actual,
            'updated_by' => $userId
        ]);
    }
}
