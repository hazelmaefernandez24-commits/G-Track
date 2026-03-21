<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $fillable = [
        'student_id',
        'meal_id',
        'meal_name',
        'meal_type',
        'meal_date',
        'rating',
        'food_quality',
        'comments',
        'suggestions',
        'is_anonymous'
    ];

    protected $casts = [
        'food_quality' => 'array',
        'meal_date' => 'date',
        'is_anonymous' => 'boolean'
    ];

    /**
     * Get the user that owns the feedback.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the user that owns the feedback (alias for student).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the meal that this feedback is for.
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('meal_date', $date);
    }

    public function scopeForMealType($query, $mealType)
    {
        return $query->where('meal_type', strtolower($mealType));
    }
}
