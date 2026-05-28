<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $casts = [
        'status' => 'boolean',
    ];

    protected $fillable = [
    'student_id', 
    'first_name',
    'middle_initial',
    'last_name',
    'name', 
    'email', 
    'gender', 
    'class', 
    'status', 
    'battery_level', 
    'signal_status', 
    'last_update', 
    'contact',
    'sos_status',
    'latitude',
    'longitude'
];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
