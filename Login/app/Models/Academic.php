<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Academic extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'academics';

     public $timestamps = false;

    // Optional: if you want to cast is_deleted to boolean
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_manual_entry' => 'boolean',
        'manual_entry_timestamp' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected $fillable = [
        'student_id',
        'semester_id',
        'academic_date',
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

    // Relationship to manual entry logs
    public function manualEntryLogs()
    {
        return $this->hasMany(ManualEntryLog::class, 'log_id', 'id')->where('log_type', 'academic');
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

    public static function saveData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'academic_date' => $data['date'],
            ],
            $data
        );
    }

    public static function getStudentLogRecord($studentID, $date)
    {
        $currentDate = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('academic_date', $currentDate)
            ->where(function ($query) {
                $query->whereNotNull('time_in')
                    ->orWhereNotNull('time_out');
            })
            ->first();
    }
}
