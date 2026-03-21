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

    public static function get_all_logs($batch, $group, $type, $date_time_out, $date_time_in, $status, $fullname)
    {
        $query = self::query()
            ->with('studentDetail')
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

        // if ($date_time_out || $date_time_in) {
        //     if ($date_time_out) {
        //         $query->where('date_time_out', '<=', $date_time_out);
        //     }
        //     if ($date_time_in) {
        //         $query->where('date_time_in', '>=', $date_time_in);
        //     }
        // }

        if ($type) {
            $query->where('schedule_type', $type);
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
                'date_time_out' => $data['date_time_out'],
            ],
            $data
        );
    }

    public static function getStudentLogRecord($studentID, $schedule)
    {
        return self::where('student_id', $studentID)
            ->whereBetween('created_at', [$schedule->start_date, $schedule->end_date])
            ->where(function ($query) {
                $query->whereNotNull('time_in')
                    ->orWhereNotNull('time_out');
            })
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

    public static function getReport($month = null, $batch = null, $group = null)
    {
        $query = self::query()
            ->where('is_deleted', 0)
            ->with('student_details');

        if ($month) {
            $query->whereYear('date_time_out', substr($month, 0, 4))
                ->whereMonth('date_time_out', substr($month, 5, 2));
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
