<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDefinition extends Model
{
    use HasFactory;

    protected $connection = 'login';
    protected $table = 'task_definitions';

    protected $fillable = [
        'category_id',
        'task_name',
        'task_description',
        'estimated_duration',
        'difficulty_level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_duration' => 'integer'
    ];

    /**
     * Get the category that owns the task definition
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the task assignments for this task definition
     */
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_definition_id');
    }

    /**
     * Scope to get only active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tasks by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
