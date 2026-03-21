<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_category',
        'description',
        'photo_path',
        'status',
        'validated_by',
        'validated_at',
        'admin_notes'
    ];

    protected $casts = [
        'validated_at' => 'datetime'
    ];

    /**
     * Get the student who submitted the task
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }

    /**
     * Get the admin who validated the task
     */
    public function validator()
    {
        return $this->belongsTo(PNUser::class, 'validated_by', 'user_id');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'valid' => 'success',
            'invalid' => 'danger',
            default => 'secondary'
        };
    }
}
