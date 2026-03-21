<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

use function Laravel\Prompts\select;

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
        'time_out_monitor_name',
        'time_out_absent_validation',
        'monitor_logged_out',
        'time_in',
        'time_in_remark',
        'educator_consideration',
        'time_in_reason',
        'time_in_monitor_name',
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

    public static function get_all($batch, $group, $date, $status, $fullname)
    {
        $query = self::query()
            ->where('is_deleted', 0);

        if ($batch) {
            $query->whereHas('studentDetail', function ($q) use ($batch) {
                $q->where('batch', $batch);
            });
        }

        if ($group) {
            $query->whereHas('studentDetail', function ($q) use ($group) {
                $q->where('group', $group);
            });
        }

        if ($date){
            $query->where('academic_date', $date);
        }

        if ($status){
            switch($status){
                case 'not_log_out':
                    $query->whereNull('time_out');
                    break;
                case 'not_log_in':
                    $query->whereNull('time_in');
                    break;
                case 'not_logged':
                    $query->whereNull('time_out')
                        ->whereNull('time_in');
                    break;

                case 'late':
                    $query->where(function ($q) {
                        $q->where('time_in_remark', 'Late')
                        ->orWhere('time_out_remark', 'Late');
                    });
                    break;

                case 'absent':
                    $query->where(function ($q) {
                        $q->where('time_in_remark', 'Absent')
                        ->orWhere('time_out_remark', 'Absent');
                    });
                    break;
            }
        }

        if ($fullname) {
            $query->whereHas('studentDetail.user', function ($q) use ($fullname) {
                $q->whereRaw("CONCAT(user_fname, ' ', user_lname) LIKE ?", ["%$fullname%"]);
            });
        }

        return $query->orderBy('updated_at', 'desc');
    }

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

    public static function getReport($month = null, $batch = null, $group = null)
    {
        $query = self::query()
            ->where('is_deleted', 0)
            ->with('student_details');

        if ($month) {
            $query->whereYear('academic_date', substr($month, 0, 4))
                ->whereMonth('academic_date', substr($month, 5, 2));
        }

        if ($batch) {
            $query->whereHas('student_details', function ($q) use ($batch) {
                $q->where('batch', $batch);
            });
        }

        if ($group) {
            $query->whereHas('student_details', function ($q) use ($group) {
                $q->where('group', $group);
            });
        }

        return $query;
    }

    public function student_details()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }
}
