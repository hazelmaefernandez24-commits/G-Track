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
        'parent_id',
        'class',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Notification::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Notification::class, 'parent_id');
    }

   
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}