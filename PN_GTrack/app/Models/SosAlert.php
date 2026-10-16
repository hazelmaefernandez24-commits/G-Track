<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SosAlert extends Notification
{
    protected $table = 'notifications';

    protected static function booted(): void
    {
        static::addGlobalScope('sos_alerts', fn (Builder $query) => $query->where('type', 'sos'));
        static::creating(function (self $alert): void {
            $alert->type = 'sos';
        });
    }
}
