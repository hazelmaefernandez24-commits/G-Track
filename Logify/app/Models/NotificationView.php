<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class NotificationView extends Model
{
    protected $fillable = [
        'log_type',
        'last_viewed_at'
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime'
    ];

    /**
     * Get or create a notification view record for a log type
     */
    public static function getOrCreate($logType)
    {
        return self::firstOrCreate(
            ['log_type' => $logType],
            ['last_viewed_at' => null]
        );
    }

    /**
     * Update the last viewed timestamp for a log type
     */
    public static function markAsViewed($logType)
    {
        return self::updateOrCreate(
            ['log_type' => $logType],
            ['last_viewed_at' => now()]
        );
    }

    /**
     * Get the last viewed timestamp for a log type
     */
    public static function getLastViewed($logType)
    {
        $record = self::where('log_type', $logType)->first();
        return $record ? $record->last_viewed_at : null;
    }
}
