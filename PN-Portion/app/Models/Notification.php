<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'read_at',
        'data'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array'
    ];

    protected $appends = ['unread', 'icon', 'action_url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getUnreadAttribute()
    {
        return $this->read_at === null;
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'info' => 'info-circle',
            'warning' => 'exclamation-triangle',
            'success' => 'check-circle',
            default => 'bell'
        };
    }

    public function getActionUrlAttribute()
    {
        return $this->data['action_url'] ?? null;
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }
} 