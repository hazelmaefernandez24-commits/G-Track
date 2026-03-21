<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StudentTaskStatus extends Model
{
    protected $table = 'student_task_status';
    
    protected $fillable = [
        'student_id',
        'task_category',
        'assignment_id',
        'status',
        'notes',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the student associated with this task status
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    /**
     * Update task status and set appropriate timestamps
     */
    public function updateStatus($newStatus, $notes = null)
    {
        $this->status = $newStatus;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        switch ($newStatus) {
            case 'in_progress':
                if (!$this->started_at) {
                    $this->started_at = Carbon::now();
                }
                break;
                
            case 'completed':
                if (!$this->started_at) {
                    $this->started_at = Carbon::now();
                }
                $this->completed_at = Carbon::now();
                break;
                
            case 'not_started':
                $this->started_at = null;
                $this->completed_at = null;
                break;
        }
        
        $this->save();
        return $this;
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass()
    {
        switch ($this->status) {
            case 'completed':
                return 'badge bg-success';
            case 'in_progress':
                return 'badge bg-warning text-dark';
            case 'not_started':
            default:
                return 'badge bg-secondary';
        }
    }

    /**
     * Get status display text
     */
    public function getStatusText()
    {
        switch ($this->status) {
            case 'completed':
                return 'Completed';
            case 'in_progress':
                return 'In Progress';
            case 'not_started':
            default:
                return 'Not Started';
        }
    }

    /**
     * Get or create task status for a student and category
     */
    public static function getOrCreate($studentId, $taskCategory, $assignmentId = null)
    {
        return static::firstOrCreate(
            [
                'student_id' => $studentId,
                'task_category' => $taskCategory
            ],
            [
                'assignment_id' => $assignmentId,
                'status' => 'not_started'
            ]
        );
    }
}
