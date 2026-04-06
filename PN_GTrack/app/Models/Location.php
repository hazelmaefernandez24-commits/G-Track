<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['student_id', 'latitude', 'longitude', 'recorded_at', 'sos_status'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
