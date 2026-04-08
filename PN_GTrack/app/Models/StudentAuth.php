<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class StudentAuth extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'student_id',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
