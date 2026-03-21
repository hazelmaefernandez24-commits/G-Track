<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenPollResponse extends Model
{
    protected $fillable = [
        'poll_id',
        'student_id',
        'will_eat',
        'notes',
        'responded_at'
    ];

    protected $casts = [
        'will_eat' => 'boolean',
        'responded_at' => 'datetime'
    ];

    // Relationships
    public function poll(): BelongsTo
    {
        return $this->belongsTo(KitchenMenuPoll::class, 'poll_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Scopes
    public function scopeWillEat($query)
    {
        return $query->where('will_eat', true);
    }

    public function scopeWontEat($query)
    {
        return $query->where('will_eat', false);
    }

    public function scopeForPoll($query, $pollId)
    {
        return $query->where('poll_id', $pollId);
    }
}
