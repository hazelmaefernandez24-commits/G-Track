<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenMenuPoll extends Model
{
    protected $fillable = [
        'poll_date',
        'meal_type',
        'menu_options',
        'instructions',
        'deadline',
        'is_active',
        'created_by',
        'meal_id'
    ];

    protected $casts = [
        'poll_date' => 'date',
        'deadline' => 'datetime',
        'menu_options' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(KitchenPollResponse::class, 'poll_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Meal::class, 'meal_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the poll is active
     */
    public function isActive()
    {
        return $this->is_active === true;
    }

    public function scopeDraft($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('poll_date', $date);
    }

    public function scopeForMealType($query, $mealType)
    {
        return $query->where('meal_type', $mealType);
    }

    // Helper methods
    public function getTotalResponsesAttribute()
    {
        return $this->responses()->count();
    }

    public function getYesCountAttribute()
    {
        return $this->responses()->where('will_eat', true)->count();
    }

    public function getNoCountAttribute()
    {
        return $this->responses()->where('will_eat', false)->count();
    }

    public function getResponseRateAttribute()
    {
        $totalStudents = User::where('user_role', 'student')->count();
        return $totalStudents > 0 ? ($this->total_responses / $totalStudents) * 100 : 0;
    }

    public function getParticipationRateAttribute()
    {
        $totalStudents = User::where('user_role', 'student')->count();
        return $totalStudents > 0 ? ($this->yes_count / $totalStudents) * 100 : 0;
    }

    /**
     * Get meal name from related meal or menu_options
     */
    public function getMealNameAttribute()
    {
        if ($this->meal) {
            return $this->meal->name;
        }

        if ($this->menu_options && is_array($this->menu_options) && isset($this->menu_options['meal_name'])) {
            return $this->menu_options['meal_name'];
        }

        return 'Unknown Meal';
    }

    /**
     * Get ingredients from related meal or menu_options
     */
    public function getIngredientsAttribute()
    {
        if ($this->meal) {
            return is_array($this->meal->ingredients) ? implode(', ', $this->meal->ingredients) : $this->meal->ingredients;
        }

        if ($this->menu_options && is_array($this->menu_options) && isset($this->menu_options['ingredients'])) {
            return $this->menu_options['ingredients'];
        }

        return null;
    }

    /**
     * Get status based on is_active and deadline
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'draft';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'active';
    }

    public function canBeEdited()
    {
        // Allow editing for draft polls (not sent yet) and active polls (sent but not expired)
        return $this->is_active === false || ($this->is_active === true && !$this->isExpired());
    }

    public function canBeSent()
    {
        return $this->is_active === false;
    }

    public function markAsSent()
    {
        $this->update([
            'is_active' => true
        ]);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function finish()
    {
        $this->update(['status' => 'finished']);
    }

    public function expire()
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Check if the poll has expired based on deadline
     */
    public function isExpired()
    {
        if (!$this->deadline) {
            return false;
        }

        $now = now();
        $pollDate = $this->poll_date->format('Y-m-d');
        $currentDate = $now->format('Y-m-d');

        // If poll date is in the past, it's expired
        if ($pollDate < $currentDate) {
            return true;
        }

        // If poll date is today, check the time
        if ($pollDate === $currentDate) {
            $deadlineTime = $this->deadline instanceof \Carbon\Carbon
                ? $this->deadline->format('H:i:s')
                : \Carbon\Carbon::parse($this->deadline)->format('H:i:s');
            $currentTime = $now->format('H:i:s');

            return $deadlineTime < $currentTime;
        }

        return false;
    }

    /**
     * Scope for expired polls
     */
    public function scopeExpired($query)
    {
        return $query->where('is_active', false)->where('deadline', '<', now());
    }

    /**
     * Scope for finished polls
     */
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }
}
