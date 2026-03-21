<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GoingHomeModel extends Model
{
    protected $table = 'going_home';

     public $timestamps = false;

    protected $fillable = [
        'student_id',
        'schedule_name',
        'date_time_out',
        'time_out',
        'time_out_remarks',
        'time_out_reason',
        'time_out_consideration',
        'date_time_in',
        'time_in',
        'time_in_remarks',
        'time_in_reason',
        'time_in_consideration',
        'is_manual_entry',
        'manual_entry_type',
        'manual_entry_reason',
        'manual_entry_monitor',
        'manual_entry_timestamp',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted'
    ];

    public static function saveData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date_time_out' => $data['date_time_out'],
            ],
            $data
        );
    }

    public static function getStudentLogRecord($studentID)
    {
        return self::where('student_id', $studentID)
            ->latest('created_at')
            ->first();
    }

    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }

    public function getTimeInAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getTimeOutAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }
}
