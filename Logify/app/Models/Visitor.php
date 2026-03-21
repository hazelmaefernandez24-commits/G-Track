<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Visitor extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

     public $timestamps = false;

    // Optional: if you want to cast is_deleted to boolean

    protected $table = 'visitors';
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_manual_entry' => 'boolean',
    ];

    protected $fillable = [
        'guard_id',
        'visitor_pass',
        'visitor_name',
        'valid_id',
        'id_number',
        'relationship',
        'visit_date',
        'purpose',
        'time_in',
        'time_out',
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
        'updated_at',
        'is_deleted'
    ];

    public static function get_all_logs($type = null, $date = null, $fullname = null)
    {
        $query = self::query()
            ->where('is_deleted', 0);

        if ($date) {
            $query->where('visit_date',  $date);
        }

        if ($fullname) {
            $query->where('visitor_name', 'LIKE', "%{$fullname}%");
        }

        return $query->orderBy('updated_at', 'desc');
    }

    public function monitor()
    {
        return $this->belongsTo(PNUser::class, 'monitor_id', 'user_id');
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

    // Format date to more readable format
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->visit_date)->format('F j, Y');
    }

    // Relationship with StudentDetail
    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }
}
