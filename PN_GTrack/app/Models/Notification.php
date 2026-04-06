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
    ];

    // optional relation to student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}