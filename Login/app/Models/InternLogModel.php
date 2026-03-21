<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternLogModel extends Model
{
    protected $table = 'intern_log';
    protected $fillable = [
        'student_id',
        'date',
        'time_out',
        'time_out_remark',
        'time_out_consideration',
        'time_out_reason',
        'time_out_absent_validation',
        'monitor_logged_out',
        'time_in',
        'time_in_remark',
        'educator_consideration',
        'time_in_reason',
        'time_in_absent_validation',
        'monitor_logged_in',
        'is_manual_entry',
        'manual_entry_type',
        'manual_entry_reason',
        'manual_entry_monitor',
        'manual_entry_timestamp',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'consideration',
        'reason',
        'monitor_name',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at,',
        'is_deleted'
    ];

    public static function saveData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => $data['date'],
            ],
            $data
        );
    }

    public static function getStudentLogRecord($studentID)
    {
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('date', $currentDate)
            ->where(function ($query) {
                $query->whereNotNull('time_in')
                    ->orWhereNotNull('time_out');
            })
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
