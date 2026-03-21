<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManualEntryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'log_type',
        'log_id',
        'entry_type',
        'reason',
        'monitor_name',
        'original_data',
        'manual_data',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    protected $casts = [
        'original_data' => 'array',
        'manual_data' => 'array',
        'approved_at' => 'datetime'
    ];

    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id', 'student_id');
    }

    public function academic()
    {
        return $this->belongsTo(Academic::class, 'log_id', 'id')->where('log_type', 'academic');
    }

    public function goingOut()
    {
        return $this->belongsTo(Going_out::class, 'log_id', 'id')->where('log_type', 'going_out');
    }

    // Get the related log record (academic or going_out)
    // public function getLogRecord()
    // {
    //     if ($this->log_type === 'academic') {
    //         return Academic::find($this->log_id);
    //     } elseif ($this->log_type === 'going_out') {
    //         return Going_out::find($this->log_id);
    //     }
    //     return null;
    // }

    // Scope for pending approvals
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for approved entries
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope for rejected entries
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Scope for specific log type
    public function scopeLogType($query, $type)
    {
        return $query->where('log_type', $type);
    }

    // Get formatted entry type for display
    public function getFormattedEntryTypeAttribute()
    {
        return match($this->entry_type) {
            'time_out' => 'Get Out',
            'time_in' => 'Get In',
            'both' => 'Get Out & Get In',
            default => $this->entry_type
        };
    }

    // Get status badge class for UI
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Get formatted status for display
    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }
}
