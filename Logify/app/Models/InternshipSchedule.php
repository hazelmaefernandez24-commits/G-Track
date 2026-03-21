<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternshipSchedule extends Model
{
    protected $table = 'internships';
    protected $casts = [
        'days' => 'array',
    ];
    protected $fillable = [
        'school_id',
        'class_id',
        'student_id',
        'company',
        'days',
        'time_in',
        'time_out',
        'time_of_duty',
        'start_date',
        'end_date',
    ];

    public static function getInternSchedule($studentId, $date)
    {
        $carbonDate = Carbon::parse($date);
        $today = $carbonDate->format('l');
        $formattedDate = $carbonDate->format('Y-m-d');

        return self::where('student_id', $studentId)
            ->whereJsonContains('days', $today)
            ->where(function ($query) use ($formattedDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $formattedDate);
            })
            ->where(function ($query) use ($formattedDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $formattedDate);
            })
            ->first();
    }

    public function intern_log()
    {
        return $this->hasMany(InternLogModel::class, 'student_id', 'student_id');
    }

    public function student_detail()
    {
        return $this->hasOne(StudentDetail::class, 'student_id', 'student_id');
    }
}
