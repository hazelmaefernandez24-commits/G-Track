<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $primaryKey = 'schedule_id';

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
        'created_by'
    ];

    protected $casts = [
        'valid_until' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_batch_schedule' => 'boolean'
    ];

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

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhere('valid_until', '>=', Carbon::today());
        });
    }

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
                    // Date range schedules
                    $q->whereNotNull('start_date')
                      ->whereNotNull('end_date')
                      ->where('start_date', '<=', $checkDate)
                      ->where('end_date', '>=', $checkDate);
                })->orWhere(function ($q) {
                    // Permanent schedules (no date range)
                    $q->whereNull('start_date')
                      ->whereNull('end_date');
                });
            });
    }

    public static function getRegAcademicSchedule($batch, $group, $semesterId = null)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        $query = self::where('batch', $batch)
            ->where('pn_group', $group)
            ->where('day_of_week', $today)
            ->where('schedule_type', 'academic')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false);

        // Add semester filter if provided
        if ($semesterId !== null) {
            $query->where('semester_id', $semesterId);
        }

        return $query->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function getIrregAcademicSchedule($studentId, $semesterId = null)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        $query = self::where('student_id', $studentId)
            ->where('day_of_week', $today)
            ->where('schedule_type', 'academic')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false);

        // Add semester filter if provided
        if ($semesterId !== null) {
            $query->where('semester_id', $semesterId);
        }

        return $query->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function getIrregLeisureSchedule($studentId)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentId)
            ->where('schedule_type', 'unique_leisure')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false)
            ->first();
    }

    public static function getRegLeisureSchedule($gender)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        return self::where('gender', $gender)
            ->where('day_of_week', $today)
            ->where('schedule_type', 'going_out')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
             ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false)
            ->first();
    }

    public static function getBatchGoingHomeSchedule($batch){
        $currentDate = Carbon::now()->toDateString();

        return self::where('schedule_type', 'going_home')
            ->where('batch', $batch)
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where('is_deleted', false)
            ->first();
    }

    public static function getGoingHomeSchedule($studentId){
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentId)
            ->where('schedule_type', 'going_home')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where('is_deleted', false)
            ->first();
    }
}
