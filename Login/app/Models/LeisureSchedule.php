<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeisureSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'student_id',
        'gender',
        'time_out',
        'time_in',
        'start_date',
        'valid_until',
        'logout_grace_period',
        'login_grace_period',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted',
    ];

    public function getTimeInAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getTimeOutAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public static function getIrregLeisureSchedule($studentId)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentId)
            ->where('day_of_week', $today)
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $currentDate);
            })->where('is_deleted', false)
            ->first();
    }

    public static function getRegLeisureSchedule($gender)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        return self::where('gender', $gender)
            ->where('day_of_week', $today)
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $currentDate);
            })->where('is_deleted', false)
            ->first();
    }
}
