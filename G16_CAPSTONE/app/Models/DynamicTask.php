<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'subtasks',
        'estimated_duration_minutes',
        'required_students',
        'gender_preference',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'subtasks' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the category that owns the task
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the assignments for this task
     */
    public function assignments()
    {
        return $this->hasMany(GeneralTaskAssignment::class, 'task_id');
    }

    /**
     * Scope for active tasks only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for tasks ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get formatted subtasks as HTML list
     */
    public function getFormattedSubtasksAttribute()
    {
        if (!$this->subtasks || !is_array($this->subtasks)) {
            return '';
        }

        $html = '<ul class="subtasks-list">';
        foreach ($this->subtasks as $subtask) {
            $html .= '<li>' . htmlspecialchars($subtask) . '</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Get estimated duration in human readable format
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->estimated_duration_minutes) {
            return 'Not specified';
        }

        $hours = floor($this->estimated_duration_minutes / 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }

        return $minutes . ' minutes';
    }
}
