<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    protected $table = 'task_templates';

    protected $fillable = [
        'area',
        'description',
        'is_fixed',
        'is_active',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'is_active' => 'boolean',
    ];
}

