<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class StudentMessage extends Notification
{
    protected $table = 'notifications';

    protected static function booted(): void
    {
        static::addGlobalScope('student_messages', fn (Builder $query) => $query->whereIn('type', ['student_message', 'admin_reply']));
        static::creating(function (self $message): void {
            if (!$message->type) {
                $message->type = 'student_message';
            }
        });
    }
}
