<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackRoom extends Model
{
    protected $table = 'feedback_room';

    protected $fillable = [
        'room_number',
        'feedback',
        'photo_paths',
        'day',
        'week',
        'month',
        'year',
    ];
}
