<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_id',
        'student_id',
        'company',
        'days',
        'time_of_duty',
        'time_in',
        'time_out',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'array',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }

    public function classModel()
    {
        // internships.class_id stores the classes primary key (id)
        return $this->belongsTo(ClassModel::class, 'class_id', 'id');
    }

    public function student()
    {
        // student_id references PNUser.user_id
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }
}


