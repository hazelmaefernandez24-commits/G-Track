<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternshipSchedule extends Model
{
    protected $table = 'internships';
    protected $fillable = [
        'school_id',
        'class_id',
        'student_id',
        'company',
        'time_in',
        'time_out',
        'time_of_duty',
        'start_date',
        'end_date',
    ];

    public static function getInternSchedule($studentId)
    {
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentId)
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->first();
    }
}
