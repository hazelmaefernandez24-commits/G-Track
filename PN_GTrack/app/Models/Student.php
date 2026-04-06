<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
    'student_id', 
    'name', 
    'email', 
    'gender', 
    'class', 
    'status', 
    'battery_level', 
    'signal_status', 
    'last_update', 
    'contact',
    'sos_status'
];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
