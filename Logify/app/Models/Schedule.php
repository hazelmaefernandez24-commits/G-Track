<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';
    protected $table = 'schedules';

    protected $fillable = [
        'student_id',
        'semester_id',
        'gender',
        'batch',
        'pn_group',
        'day_of_week',
        'schedule_type',
        'schedule_name',
        'start_date',
        'end_date',
        'is_batch_schedule',
        'time_in',
        'time_out',
        'grace_period_logout_minutes',
        'grace_period_login_minutes',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted'
    ];

    protected $casts = [
        // 'valid_until' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_batch_schedule' => 'boolean'
    ];

    public static function saveGoingHomeSchedule(array $data) {
        return self::create($data);
    }

    // Mutators to handle time input - store as time format
    public function setTimeInAttribute($value)
    {
        $this->attributes['time_in'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function setTimeOutAttribute($value)
    {
        $this->attributes['time_out'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    // Helper methods to get formatted time for display purposes
    public function getFormattedTimeInAttribute()
    {
        return $this->attributes['time_in'] ? Carbon::parse($this->attributes['time_in'])->format('H:i') : null;
    }

    public function getFormattedTimeOutAttribute()
    {
        return $this->attributes['time_out'] ? Carbon::parse($this->attributes['time_out'])->format('H:i') : null;
    }

    // Helper methods to get raw time values for comparisons
    public function getRawTimeIn()
    {
        return $this->attributes['time_in'];
    }

    public function getRawTimeOut()
    {
        return $this->attributes['time_out'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(PNUser::class, 'created_by', 'user_id');
    }

    // Scopes for filtering by schedule type
    public function scopeAcademic($query)
    {
        return $query->where('schedule_type', 'academic');
    }

    public function scopeGoingOut($query)
    {
        return $query->where('schedule_type', 'going_out');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    // public function scopeActive($query)
    // {
    //     return $query->where(function ($query) {
    //         $query->whereNull('valid_until')
    //             ->orWhere('valid_until', '>=', Carbon::today());
    //     });
    // }

    public function scopeBatchSchedule($query)
    {
        return $query->where('is_batch_schedule', true);
    }

    public function scopeActiveBatchSchedule($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::today();

        return $query->where('is_batch_schedule', true)
            ->where(function ($query) use ($checkDate) {
                $query->where(function ($q) use ($checkDate) {
                    $q->whereNotNull('start_date')
                      ->whereNotNull('end_date')
                      ->where('start_date', '<=', $checkDate)
                      ->where('end_date', '>=', $checkDate);
                })->orWhere(function ($q) {
                    $q->whereNull('start_date')
                      ->whereNull('end_date');
                });
            });
    }

    public static function get_academic_schedule_by_id($student_id, $selected_day)
    {
        // $day = Carbon::parse($selected_day)->format('l');
        return self::query()
            ->where('student_id', $student_id)
            ->where('start_date', '<=', $selected_day)
            ->where('end_date', '>=', $selected_day)
            ->where('schedule_type', 'academic')
            // ->where('day_of_week', $day)
            ->where('is_deleted', 0)
            ->first();
    }

    public static function get_academic_schedule($batch, $group, $selected_day)
    {
        $day = Carbon::parse($selected_day)->format('l');
        return self::query()
            ->where('batch', $batch)
            ->where('pn_group', $group)
            ->where('start_date', '<=', $selected_day)
            ->where('end_date', '>=', $selected_day)
            ->where('schedule_type', 'academic')
            ->where('day_of_week', $day)
            ->where('is_deleted', 0)
            ->first();
    }

    public static function get_goingout_schedule_by_id($student_id, $selected_day)
    {
        return self::query()
            ->where('student_id', $student_id)
            ->where('start_date', '<=', $selected_day)
            ->where('end_date', '>=', $selected_day)
            ->where('schedule_type', 'going_out')
            ->where('is_deleted', 0)
            ->first();
    }

    public static function get_goingout_schedule($gender, $selected_day)
    {
        return self::query()
            ->where('gender', $gender)
            ->where('start_date', '<=', $selected_day)
            ->where('end_date', '>=', $selected_day)
            ->where('schedule_type', 'going_out')
            ->where('is_deleted', 0)
            ->first();
    }

    public static function get_goinghome_schedule_by_id($student_id)
    {
        $today = Carbon::today()->toDateString();

        return self::query()
            ->where('student_id', $student_id)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('schedule_type', 'going_home')
            ->where('is_deleted', 0)
            ->first();
    }

    // public static function get_goinghome_schedule($schedule_name)
    // {
    //     $today = Carbon::today()->toDateString();

    //     return self::query()
    //         ->where('schedule_name', $schedule_name)
    //         ->where('start_date', '<=', $today)
    //         ->where('end_date', '>=', $today)
    //         ->where('schedule_type', 'going_home')
    //         ->where('is_deleted', 0)
    //         ->first();
    // }

    public static function get_all_type()
    {
        return self::where('schedule_type', 'going_home')
            ->where('is_deleted', 0)
            ->distinct()
            ->pluck('schedule_name');
    }
}
