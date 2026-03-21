<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSchedule extends Model
{
    use HasFactory;

    protected $connection = 'login';
    protected $table = 'task_schedules';

    protected $fillable = [
        'assignment_id',
        'schedule_date',
        'day_of_week',
        'task_assignments',
        'status',
        'created_by',
        'finalized_at'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'task_assignments' => 'array',
        'finalized_at' => 'datetime'
    ];

    /**
     * Get the assignment that owns this schedule
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Get the creator of this schedule
     */
    public function creator()
    {
        return $this->belongsTo(PNUser::class, 'created_by', 'user_id');
    }

    /**
     * Scope to get schedules by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('schedule_date', $date);
    }

    /**
     * Scope to get active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get schedules by assignment
     */
    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }
}
