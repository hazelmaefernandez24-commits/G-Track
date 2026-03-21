<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'meal_type',
        'date',
        'price',
        'image',
        'is_available',
        'created_by',
        'updated_by',
        'week_cycle',
        'day'
    ];

    protected $attributes = [
        'price' => 0,
        'is_available' => true,
        'category' => 'regular'
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'is_available' => 'boolean'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
} 