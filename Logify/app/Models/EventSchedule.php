<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSchedule extends Model
{
    protected $table = 'event_schedule';

    protected $fillable = [
        'calendar_events_id',
        'time_in',
        'time_out',
        'created_by',
        'updated_by',
        'is_deleted'
    ];

    public $timestamps = true;

    public static function get_schedule($date = null)
    {
        $query = self::query()
            ->with('event')
            ->where('is_deleted', false);

        if ($date) {
            $query->whereHas('event', function ($q) use ($date) {
                $q->where('start_date', '<=', $date)
                  ->where('end_date', '>=', $date);
            });
        }

        return $query->first();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarModel::class, 'calendar_events_id');
    }
}
