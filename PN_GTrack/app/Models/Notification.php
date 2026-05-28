<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'subject',
        'message',
        'read',
        'battery_level',
        'signal_status',
        'location',
        'latitude',
        'longitude',
        'media_url',
        'video_url',
        'audio_url',
        'sender_type',
        'sender_name',
        'reply_to_id',
        'class',
        'status',
    ];

    // The original notification this reply is responding to
    public function replyTo()
    {
        return $this->belongsTo(Notification::class, 'reply_to_id');
    }

    // All admin/student replies to this notification
    public function replies()
    {
        return $this->hasMany(Notification::class, 'reply_to_id');
    }

   
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}