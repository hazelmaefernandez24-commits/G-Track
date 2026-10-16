<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class BroadcastNotification extends Notification
{
    protected $table = 'notifications';

    protected static function booted(): void
    {
        static::addGlobalScope('broadcast_notifications', fn (Builder $query) => $query->where('type', 'broadcast'));
        static::creating(function (self $broadcast): void {
            $broadcast->type = 'broadcast';
        });
    }
}
