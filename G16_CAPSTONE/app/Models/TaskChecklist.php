<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    protected $fillable = [
        'task_category',
        'task_description',
        'week_start_date',
        'week1_status',
        'week2_status',
        'week1_remarks',
        'week2_remarks'
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week1_status' => 'array',
        'week2_status' => 'array'
    ];
}