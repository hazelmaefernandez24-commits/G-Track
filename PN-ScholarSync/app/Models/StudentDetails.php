<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDetails extends Model
{
    use HasFactory;

    protected $table = 'student_details';

    protected $fillable = [
        'user_id',
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
    ];

    /**
     * Get the user that owns the student details.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the violations for the student.
     */
    public function violations()
    {
        return $this->hasMany(Violation::class, 'student_id', 'student_id');
    }
}
