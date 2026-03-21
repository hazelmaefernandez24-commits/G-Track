<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTaskMember extends Model
{
    use HasFactory;

    protected $connection = 'login_db'; // Use Login database connection
    protected $table = 'dynamic_task_members';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'is_coordinator',
        'comments',
        'comment_created_at',
        'assigned_by'
    ];

    protected $casts = [
        'is_coordinator' => 'boolean',
        'comment_created_at' => 'datetime'
    ];

    /**
     * Get the assignment that owns this member
     */
    public function assignment()
    {
        return $this->belongsTo(DynamicTaskAssignment::class, 'assignment_id');
    }

    /**
     * Get the student for this member
     */
    public function student()
    {
        return $this->belongsTo(LoginPNUser::class, 'student_id', 'user_id');
    }

    /**
     * Get the admin who assigned this student
     */
    public function assignedBy()
    {
        return $this->belongsTo(LoginPNUser::class, 'assigned_by', 'user_id');
    }

    /**
     * Scope for coordinators only
     */
    public function scopeCoordinators($query)
    {
        return $query->where('is_coordinator', true);
    }

    /**
     * Scope for regular members only
     */
    public function scopeRegularMembers($query)
    {
        return $query->where('is_coordinator', false);
    }

    /**
     * Clean expired comments (older than 30 days)
     */
    public static function cleanExpiredComments()
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        self::where('comment_created_at', '<', $thirtyDaysAgo)
            ->update([
                'comments' => null,
                'comment_created_at' => null
            ]);
    }

    /**
     * Get student's full name
     */
    public function getStudentFullNameAttribute()
    {
        if ($this->student) {
            return trim(($this->student->user_fname ?? '') . ' ' . ($this->student->user_lname ?? ''));
        }
        return 'Unknown Student';
    }

    /**
     * Get student's batch year
     */
    public function getStudentBatchAttribute()
    {
        if ($this->student && $this->student->studentDetail) {
            return $this->student->studentDetail->batch;
        }
        return null;
    }

    /**
     * Get student's gender
     */
    public function getStudentGenderAttribute()
    {
        if ($this->student) {
            return $this->student->gender;
        }
        return null;
    }
}
