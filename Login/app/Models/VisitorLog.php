<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class VisitorLog extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $timestamps = false;
    protected $table = 'visitors';

    // Optional: if you want to cast is_deleted to boolean
    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    protected $fillable = [
        'guard_id',
        'visitor_pass',
        'visitor_name',
        'valid_id',
        'id_number',
        'relationship',
        'purpose',
        'visit_date',
        'time_in',
        'time_out',
        'consideration',
        'reason',
        'monitor_id',
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

    public static function getAllVisitor($perPage)
    {
        return self::orderBy('visit_date', 'desc')
            ->orderBy('time_in', 'desc')->paginate($perPage);
    }

    public static function getUsedPass(){
        return self::where('visit_date', now()->format('Y-m-d'))
            ->whereNull('time_out')
            ->pluck('visitor_pass')
            ->toArray();
    }

    public static function saveData($data) {
        return self::updateOrCreate(
            [
                'visit_date' => $data['visit_date'],
                'valid_id' => $data['valid_id'],
                'id_number' => $data['id_number']
            ],
            $data
        );
    }

    public static function getVisitorID($validID, $idNumber){
        return self::where('valid_id', $validID)
            ->where('id_number', $idNumber)
            ->whereNotNull('time_in')
            ->first();
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
        return Carbon::parse($this->visitor_date)->format('F j, Y');
    }

    // Relationship with StudentDetail
    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }
}
