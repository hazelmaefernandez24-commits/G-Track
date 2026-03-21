<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralTaskAssignment extends Model
{
    use HasFactory;

    protected $table = 'generaltask_assignments';

    protected $fillable = [
        'task_id',
        'batch_year',
        'gender',
        'assigned_count',
        'assignment_date',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
    ];

    /**
     * Get the task that owns the assignment
     */
    public function task()
    {
        return $this->belongsTo(DynamicTask::class, 'task_id');
    }

    /**
     * Get formatted assignment display (for privacy)
     */
    public function getDisplayNameAttribute()
    {
        return "Batch {$this->batch_year} " . ucfirst($this->gender) . "s ({$this->assigned_count})";
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('assignment_date', $date);
    }

    /**
     * Scope for specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for active assignments (not cancelled)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
