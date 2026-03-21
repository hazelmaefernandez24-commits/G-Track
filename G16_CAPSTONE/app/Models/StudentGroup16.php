<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGroup16 extends Model
{
    protected $table = 'student_group16';

    protected $fillable = [
        'name',
        'gender',
        'batch'
    ];

    /**
     * Get the assignment members for this student
     */
    public function assignmentMembers()
    {
        return $this->hasMany(AssignmentMember::class, 'student_group16_id', 'id');
    }

    /**
     * Get the user details from Login database
     */
    public function user()
    {
        return $this->belongsTo(PNUser::class, 'id', 'user_id');
    }

    /**
     * Get the student details from Login database
     */
    public function studentDetail()
    {
        return $this->belongsTo(StudentDetail::class, 'id', 'user_id');
    }
}
