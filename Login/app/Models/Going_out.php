<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Going_out extends Model
{
    public $timestamps = false;

    protected $table = 'going_outs';

    // Optional: if you want to cast is_deleted to boolean
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_manual_entry' => 'boolean',
        'manual_entry_timestamp' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $fillable = [
        'student_id',
        'going_out_date',
        'session_number',
        'session_status',
        'destination',
        'purpose',
        'time_out',
        'time_out_remark',
        'time_out_consideration',
        'time_out_reason',
        'monitor_logged_out',
        'time_in',
        'time_in_remark',
        'educator_consideration',
        'time_in_reason',
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
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted'
    ];

    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }

    // Get the monitor name who set the time out consideration
    public function getTimeOutMonitorNameAttribute()
    {
        if ($this->time_out_consideration && $this->updated_by) {
            return $this->updated_by;
        }
        return null;
    }

    // Get the monitor name who set the time in consideration
    public function getTimeInMonitorNameAttribute()
    {
        if ($this->educator_consideration && $this->updated_by) {
            return $this->updated_by;
        }
        return null;
    }

    // Format time_out to 12-hour format
    public function getFormattedTimeOutAttribute()
    {
        if (!$this->time_out) {
            return '—';
        }
        return Carbon::createFromFormat('H:i:s', $this->time_out)->format('g:i A');
    }

    // Format time_in to 12-hour format
    public function getFormattedTimeInAttribute()
    {
        if (!$this->time_in) {
            return '—';
        }
        return Carbon::createFromFormat('H:i:s', $this->time_in)->format('g:i A');
    }

    // Format date to more readable format
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->going_out_date)->format('F j, Y');
    }

    // Get the next session number for a student on a specific date
    public static function getNextSessionNumber($studentId, $date)
    {
        $lastSession = self::where('student_id', $studentId)
            ->whereDate('going_out_date', $date)
            ->orderBy('session_number', 'desc')
            ->first();

        return $lastSession ? $lastSession->session_number + 1 : 1;
    }

    // Get the current active session for a student on a specific date
    public static function getCurrentActiveSession($studentId, $date)
    {
        return self::where('student_id', $studentId)
            ->whereDate('going_out_date', $date)
            ->where('session_status', 'active')
            ->orderBy('session_number', 'desc')
            ->first();
    }

    // Check if student can start a new session (must complete previous session first)
    public static function canStartNewSession($studentId, $date)
    {
        $activeSession = self::getCurrentActiveSession($studentId, $date);
        return $activeSession === null; // Can start new session if no active session exists
    }

    // Mark session as completed
    public function markAsCompleted()
    {
        $this->update(['session_status' => 'completed']);
    }

    // Relationship to manual entry logs
    public function manualEntryLogs()
    {
        return $this->hasMany(ManualEntryLog::class, 'log_id', 'id')->where('log_type', 'going_out');
    }

    // Check if this record has pending manual entry approval
    public function hasPendingApproval()
    {
        return $this->approval_status === 'pending';
    }

    // Check if this record is a manual entry
    public function isManualEntry()
    {
        return $this->is_manual_entry === true;
    }

    // Get status badge class for approval status
    public function getApprovalStatusBadgeClassAttribute()
    {
        return match($this->approval_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public static function getStudentLogRecord($studentID, $date)
    {
        $currentDate = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('going_out_date', $currentDate)
            ->orderBy('time_out', 'desc')
            ->first();
    }


    public static function saveOutData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'going_out_date' => $data['date'],
                'time_out' => $data['time_out']
            ],
            $data
        );
    }

    public static function saveInData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'going_out_date' => $data['date'],
                'created_at' => $data['created_at']
            ],
            $data
        );
    }

    public static function countStudentLogs($studentID, $date = null)
    {
        $date = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->whereDate('going_out_date', $date)
            ->count();
    }

    public static function findOrFail($studentID)
    {
        $date = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('is_deleted', 0);
    }
}
