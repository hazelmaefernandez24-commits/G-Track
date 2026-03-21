<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationHistory extends Model
{
    protected $table = 'notification_history';

    protected $fillable = [
        'student_id',
        'batch',
        'action_type',
        'log_type',
        'is_late',
        'timing_status',
        'is_read',
        'activity_timestamp'
    ];

    protected $casts = [
        'is_late' => 'boolean',
        'is_read' => 'boolean',
        'activity_timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the student detail associated with this notification
     */
    public function studentDetail(): BelongsTo
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }

    /**
     * Get the student user information through student detail
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }

    /**
     * Create a new notification record
     */
    public static function createNotification($studentId, $batch, $actionType, $logType, $isLate = false, $timingStatus = null)
    {
        // If timing status is not provided, determine it from isLate for backward compatibility
        if ($timingStatus === null) {
            $timingStatus = $isLate ? 'Late' : 'On Time';
        }

        return self::create([
            'student_id' => $studentId,
            'batch' => $batch,
            'action_type' => $actionType,
            'log_type' => $logType,
            'is_late' => $isLate,
            'timing_status' => $timingStatus,
            'is_read' => false,
            'activity_timestamp' => now()
        ]);
    }

    /**
     * Get unread notifications count
     */
    public static function getUnreadCount()
    {
        return self::where('is_read', false)->count();
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead()
    {
        return self::where('is_read', false)->update(['is_read' => true]);
    }
}
