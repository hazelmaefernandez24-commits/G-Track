<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RotationSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'rotation_schedules';

    protected $fillable = [
        'room',
        'schedule_map',
        'start_date',
        'end_date',
        'mode',
        'frequency',
        'created_by',
    ];

    protected $casts = [
        // schedule_map stored as JSON in DB, always cast to array when reading in PHP
        'schedule_map' => 'array',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        // frequency kept as integer in migration; cast accordingly
        'frequency' => 'integer',
    ];
}