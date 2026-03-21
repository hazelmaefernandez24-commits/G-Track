<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'user_id',
        'response',
    ];

    /**
     * Get the announcement that owns the response.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    /**
     * Get the user that submitted the response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
}
