<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedSchedule extends Model
{
    protected $fillable = [
        'assignment_id',
        'category_name',
        'schedule_date',
        'student_id',
        'student_name',
        'task_title',
        'task_description',
        'batch',
        'task_status',
        'start_date',
        'end_date',
        'rotation_frequency',
        'schedule_data'
    ];

    protected $casts = [
        'schedule_data' => 'array',
        'schedule_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
