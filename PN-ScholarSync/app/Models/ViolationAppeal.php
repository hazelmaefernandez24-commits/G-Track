<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ViolationAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_id',
        'student_id',
        'student_reason',
        'status',
        'admin_response',
        'appeal_date',
        'admin_decision_date',
        'reviewed_by',
        'additional_evidence'
    ];

    protected $casts = [
        'appeal_date' => 'datetime',
        'admin_decision_date' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';

    /**
     * Get all possible status values
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DENIED => 'Denied'
        ];
    }

    /**
     * Check if appeal is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if appeal is approved
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if appeal is denied
     */
    public function isDenied()
    {
        return $this->status === self::STATUS_DENIED;
    }

    /**
     * Relationship with Violation
     */
    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    /**
     * Relationship with Student (through StudentDetails)
     */
    public function student()
    {
        return $this->hasOneThrough(
            User::class,
            StudentDetails::class,
            'student_id', // Foreign key on student_details table
            'user_id',    // Foreign key on pnph_users table
            'student_id', // Local key on violation_appeals table
            'user_id'     // Local key on student_details table
        );
    }

    /**
     * Relationship with StudentDetails
     */
    public function studentDetails()
    {
        return $this->belongsTo(StudentDetails::class, 'student_id', 'student_id');
    }

    /**
     * Relationship with Admin who reviewed the appeal
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    /**
     * Scope for pending appeals
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved appeals
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for denied appeals
     */
    public function scopeDenied($query)
    {
        return $query->where('status', self::STATUS_DENIED);
    }

    /**
     * Get formatted appeal date
     */
    public function getFormattedAppealDateAttribute()
    {
        return $this->appeal_date ? $this->appeal_date->format('M d, Y g:i A') : null;
    }

    /**
     * Get formatted decision date
     */
    public function getFormattedDecisionDateAttribute()
    {
        return $this->admin_decision_date ? $this->admin_decision_date->format('M d, Y g:i A') : null;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_DENIED => 'bg-danger',
            default => 'bg-secondary'
        };
    }
}
