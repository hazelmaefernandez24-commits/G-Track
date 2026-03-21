<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDetail extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
        'gender'
    ];

    // Relationship with PNUser model
    public function user()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }

    public function student()
{
    return $this->belongsTo(Student::class, 'student_id', 'user_id');
}
} 