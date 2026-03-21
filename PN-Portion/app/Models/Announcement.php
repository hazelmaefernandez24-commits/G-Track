<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'expiry_date',
        'is_active',
        'is_poll',
        'poll_options',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'is_poll' => 'boolean',
        'poll_options' => 'array',
    ];

    /**
     * Get the user that created the announcement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the poll responses for this announcement.
     */
    public function pollResponses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }

    /**
     * Check if the announcement is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    /**
     * Get poll results as a count of responses per option.
     */
    public function getPollResults(): array
    {
        if (!$this->is_poll) {
            return [];
        }

        $results = [];
        $responses = $this->pollResponses()->get();

        foreach ($this->poll_options as $option) {
            $results[$option] = $responses->where('response', $option)->count();
        }

        return $results;
    }
}
