<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'admin_id',
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

    /**
     * Scope a query to only include SOS notifications that have a valid video feed.
     */
    public function scopeWithValidVideo($query)
    {
        return $query->where(function ($videoQ) {
            $videoQ->where(function ($vq) {
                $vq->whereNotNull('video_url')->where('video_url', '!=', '');
            })->orWhere(function ($mediaQ) {
                $mediaQ->whereNotNull('media_url')
                       ->where('media_url', '!=', '')
                       ->where('media_url', 'not like', '%.mp3')
                       ->where('media_url', 'not like', '%.wav');
            });
        });
    }

    /**
     * Scope a query to exclude any SOS alerts that lack a valid video feed.
     * Non-SOS types pass through without restriction.
     */
    public function scopeExcludeIncompleteSos($query)
    {
        return $query->where(function ($q) {
            $q->where('type', '!=', 'sos')
              ->orWhere(function ($sosQ) {
                  $sosQ->where('type', 'sos')
                       ->where(function ($videoQ) {
                           $videoQ->where(function ($vq) {
                               $vq->whereNotNull('video_url')->where('video_url', '!=', '');
                           })->orWhere(function ($mediaQ) {
                               $mediaQ->whereNotNull('media_url')
                                      ->where('media_url', '!=', '')
                                      ->where('media_url', 'not like', '%.mp3')
                                      ->where('media_url', 'not like', '%.wav');
                           });
                       });
              });
        });
    }
}