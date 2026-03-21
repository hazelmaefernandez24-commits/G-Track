<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    protected $table = 'task_histories';
    
    protected $fillable = [
        'room_number',
        'week',
        'month',
        'year',
    'day',
    'status',
    'completed',
    'photo_paths',
    'task_id',
    'assigned_to',
    'task_area',
    'task_description',
    'filter_type'
    ];

    protected $casts = [
        'completed' => 'boolean',
        'photo_paths' => 'array'
    ];

    public function roomTask()
    {
    // TaskHistory.task_id references roomtask.id
    return $this->belongsTo(RoomTask::class, 'task_id');
    }
} 