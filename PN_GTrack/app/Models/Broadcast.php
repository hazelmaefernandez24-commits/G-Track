<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    protected $fillable = ['admin_id', 'subject', 'message'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(BroadcastRecipient::class);
    }

    public function getSenderNameAttribute(): string
    {
        $admin = $this->admin;

        return $admin
            ? trim(implode(' ', array_filter([
                $admin->first_name,
                $admin->middle_initial ? $admin->middle_initial . '.' : null,
                $admin->last_name,
            ])))
            : 'Admin';
    }

    public function getSenderTypeAttribute(): string
    {
        return 'admin';
    }
}
