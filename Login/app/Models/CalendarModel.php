<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CalendarModel extends Model
{
    protected $table = 'calendar_events';
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'category',
        'semester',
        'academic_year',
        'is_active',
        'created_at',
        'updated_at'
    ];

    public static function get_all_events()
    {
        return self::where('is_active', true)
            ->with('schedules')
            ->get()
            ->map(function ($event) {
                $schedule = $event->schedules->first(); // assuming one schedule per event
                $hasSchedule = !is_null($schedule);
                return [
                    'id'          => $event->id,
                    'title'       => $event->title,
                    'start'       => $event->start_date,
                    'end'         => $event->end_date ? Carbon::parse($event->end_date)->endOfDay() : null,
                    'description' => $event->description,
                    'color'       => $hasSchedule ? '#16a34a' : '#f97316',
                    'time_out'    => $hasSchedule ? $schedule->time_out : null,
                    'time_in'     => $hasSchedule ? $schedule->time_in : null,
                ];
            });
    }

    public function schedules()
    {
        return $this->hasMany(EventSchedule::class, 'calendar_events_id');
    }
}
