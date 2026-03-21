<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTaskCategory extends Model
{
    use HasFactory;

    protected $connection = 'login_db'; // Use Login database connection
    protected $table = 'dynamic_task_categories';

    protected $fillable = [
        'name',
        'description',
        'color_code',
        'max_students',
        'max_boys',
        'max_girls',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_students' => 'integer',
        'max_boys' => 'integer',
        'max_girls' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * Get the assignments for this category
     */
    public function assignments()
    {
        return $this->hasMany(DynamicTaskAssignment::class, 'category_id');
    }

    /**
     * Get current assignments for this category
     */
    public function currentAssignments()
    {
        return $this->hasMany(DynamicTaskAssignment::class, 'category_id')
                    ->where('status', 'current');
    }

    /**
     * Scope for active categories only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get current student counts for this category
     */
    public function getCurrentStudentCounts()
    {
        $counts = ['boys' => 0, 'girls' => 0, 'total' => 0];
        
        foreach ($this->currentAssignments as $assignment) {
            foreach ($assignment->members as $member) {
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
        }
        
        return $counts;
    }

    /**
     * Get current coordinators for this category
     */
    public function getCurrentCoordinators()
    {
        $coordinators = ['2025' => null, '2026' => null];
        
        foreach ($this->currentAssignments as $assignment) {
            foreach ($assignment->members->where('is_coordinator', true) as $member) {
                if ($member->student && $member->student->studentDetail) {
                    $batch = $member->student->studentDetail->batch;
                    $fullName = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                    
                    if ($batch == 2025 && !$coordinators['2025']) {
                        $coordinators['2025'] = $fullName;
                    } elseif ($batch == 2026 && !$coordinators['2026']) {
                        $coordinators['2026'] = $fullName;
                    }
                }
            }
        }
        
        return $coordinators;
    }
}
