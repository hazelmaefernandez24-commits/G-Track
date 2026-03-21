<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Educator extends Model
{
    protected $fillable = [
        'user_id',
        'fname',
        'lname',
        // Add other fields as needed
    ];
}
