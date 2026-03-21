<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubmissionDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grade_submission_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grade_submission_id',
        'student_id',
        'subject_id',
        'grade',
    ];

    /**
     * Get the grade submission that owns the detail.
     */
    public function gradeSubmission()
    {
        return $this->belongsTo(GradeSubmission::class);
    }

    /**
     * Get the student associated with the grade detail.
     */
    public function student()
    {
        return $this->belongsTo(PNUser::class, 'student_id', 'user_id');
    }

    /**
     * Get the subject associated with the grade detail.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
} 