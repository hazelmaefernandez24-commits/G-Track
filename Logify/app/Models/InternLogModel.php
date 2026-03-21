<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternLogModel extends Model
{
    protected $table = 'intern_log';
    protected $primaryKey = 'id';
    public $timestamps = true;
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
        'time_out_consideration',
        'time_out_reason',
        'time_out_absent_validation',
        'monitor_logged_out',

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

    public static function get_all_logs($selectedDate, $company, $status, $fullname){
        $query = self::query()
            ->with('internshipSchedule')
            ->with('studentDetail')
            ->where('date', $selectedDate)
            ->where('is_deleted', 0);

        if ($company) {
            $query->whereHas('internshipSchedule', function ($q) use ($company) {
                $q->where('company', $company);
            });
        }

        if($status){
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

    public static function getStudentLogRecord($studentID, $date)
    {
        $currentDate = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('date', $currentDate)
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

    public function internshipSchedule()
    {
        return $this->belongsTo(InternshipSchedule::class, 'student_id', 'student_id');
    }

    public static function getReport($month = null, $batch = null, $group = null)
    {
        $query = self::query()
            ->where('is_deleted', 0)
            ->with('student_details');

        if ($month) {
            $query->whereYear('date', substr($month, 0, 4))
                ->whereMonth('date', substr($month, 5, 2));
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

        $query->selectRaw("
            student_id,
            COUNT(CASE WHEN (time_in_remark = 'Late' OR time_out_remark = 'Late') THEN 1 END) AS total_late,
            COUNT(CASE WHEN (time_in_remark = 'Early' OR time_out_remark = 'Early') THEN 1 END) AS total_early,
            COUNT(CASE WHEN (time_in_remark = 'Absent' OR time_out_remark = 'Absent') THEN 1 END) AS total_absent
        ")
        ->groupBy('student_id');

        return $query;
    }

    public function student_details()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }
}
