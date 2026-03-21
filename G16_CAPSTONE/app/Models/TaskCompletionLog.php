<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCompletionLog extends Model
{
    use HasFactory;

    protected $connection = 'login';
    protected $table = 'task_completion_logs';

    protected $fillable = [
        'task_assignment_id',
        'student_id',
        'started_at',
        'completed_at',
        'completion_status',
        'completion_notes',
        'completion_evidence',
        'quality_rating',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'completion_evidence' => 'array',
        'quality_rating' => 'integer',
        'verified_at' => 'datetime'
    ];

    /**
     * Get the task assignment that owns this log
     */
    public function taskAssignment()
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }

    /**
     * Get the student who completed this task
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    /**
     * Get the verifier of this completion
     */
    public function verifier()
    {
        return $this->belongsTo(PNUser::class, 'verified_by', 'user_id');
    }

    /**
     * Scope to get completed tasks
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_status', 'completed');
    }

    /**
     * Scope to get logs by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}
