<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSubmissionSubject extends Model
{
    protected $table = 'grade_submission_subject';
    
    protected $fillable = [
        'grade_submission_id',
        'grade',
        'status',
        'user_id',
        'subject_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function gradeSubmission()
    {
        return $this->belongsTo(GradeSubmission::class);
    }

    public function user()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }
}
