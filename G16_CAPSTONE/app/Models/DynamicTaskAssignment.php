<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTaskAssignment extends Model
{
    use HasFactory;

    protected $connection = 'login_db'; // Use Login database connection
    protected $table = 'dynamic_task_assignments';

    protected $fillable = [
        'category_id',
        'assignment_name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Get the category that owns this assignment
     */
    public function category()
    {
        return $this->belongsTo(DynamicTaskCategory::class, 'category_id');
    }

    /**
     * Get the members for this assignment
     */
    public function members()
    {
        return $this->hasMany(DynamicTaskMember::class, 'assignment_id');
    }

    /**
     * Get the admin who created this assignment
     */
    public function creator()
    {
        return $this->belongsTo(LoginPNUser::class, 'created_by', 'user_id');
    }

    /**
     * Scope for current assignments
     */
    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }

    /**
     * Scope for active assignments (current or pending)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['current', 'pending']);
    }

    /**
     * Get coordinators for this assignment
     */
    public function getCoordinators()
    {
        return $this->members()->where('is_coordinator', true)->with('student')->get();
    }

    /**
     * Get regular members (non-coordinators) for this assignment
     */
    public function getRegularMembers()
    {
        return $this->members()->where('is_coordinator', false)->with('student')->get();
    }

    /**
     * Check if assignment is currently active
     */
    public function isActive()
    {
        $now = now()->toDateString();
        return $this->status === 'current' && 
               $this->start_date <= $now && 
               $this->end_date >= $now;
    }

    /**
     * Get student count by gender
     */
    public function getStudentCountsByGender()
    {
        $counts = ['boys' => 0, 'girls' => 0, 'total' => 0];
        
        foreach ($this->members as $member) {
            if ($member->student) {
                $counts['total']++;
                $gender = $member->student->gender;
                if ($gender === 'M' || $gender === 'Male') {
                    $counts['boys']++;
                } elseif ($gender === 'F' || $gender === 'Female') {
                    $counts['girls']++;
                }
            }
        }
        
        return $counts;
    }
}
