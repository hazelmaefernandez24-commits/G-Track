<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class LeisureLog extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $timestamps = false;

    protected $table = 'leisure_logs';

    protected $fillable = [
        'student_id',
        'date',
        'destination',
        'purpose',
        'time_out',
        'time_out_remark',
        'time_out_consideration',
        'time_out_reason',
        'time_in',
        'time_in_remark',
        'time_in_consideration',
        'time_in_reason',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'is_deleted'
    ];

    public function getTimeInAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public function getTimeOutAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('h:i A') : null;
    }

    public static function getStudentLogRecord($studentID)
    {
        $currentDate = Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->where('date', $currentDate)
            ->orWhereNotNull('time_out')
            ->orderBy('time_out', 'desc')
            ->first();
    }


    public static function saveOutData($data)
    {
        return self::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => $data['date'],
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
                'date' => $data['date'],
                'created_at' => $data['created_at']
            ],
            $data
        );
    }

    public static function countStudentLogs($studentID, $date = null)
    {
        $date = $date ?? Carbon::now()->toDateString();

        return self::where('student_id', $studentID)
            ->whereDate('date', $date)
            ->count();
    }
}
