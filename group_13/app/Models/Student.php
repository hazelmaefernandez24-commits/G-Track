<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'name',
        'email'
    ];

    public function classes()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_student', 'student_id', 'class_id', 'student_id', 'class_id');
    }

    public function studentDetail()
{
    return $this->hasOne(StudentDetail::class, 'student_id', 'user_id');
}
} 