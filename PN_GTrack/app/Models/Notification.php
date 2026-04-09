<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'message',
        'read',
        'battery_level',
        'signal_status',
        'location',
        'latitude',
        'longitude',
        'media_url',
        'class',
        'status',
    ];

   
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}