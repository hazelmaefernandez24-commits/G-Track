<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $connection = 'login';
    protected $table = 'task_assignments';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'task_definition_id',
        'assigned_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'assigned_by'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
    ];

    /**
     * Get the assignment that owns this task assignment
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    /**
     * Get the student assigned to this task
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    /**
     * Get the task definition for this assignment
     */
    public function taskDefinition()
    {
        return $this->belongsTo(TaskDefinition::class, 'task_definition_id');
    }

    /**
     * Get the completion logs for this task assignment
     */
    public function completionLogs()
    {
        return $this->hasMany(TaskCompletionLog::class, 'task_assignment_id');
    }

    /**
     * Scope to get assignments by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('assigned_date', $date);
    }

    /**
     * Scope to get assignments by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get assignments by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
