<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GradeSubmission extends Model
{
    use HasFactory;

    // If the table name is not the plural of the model name, you can specify it here
    // protected $table = 'grade_submissions';

    // Define fillable properties
    protected $fillable = [
        'school_id', 
        'class_id', 
        'semester', 
        'term', 
        'academic_year', 
        'status'
    ];

    // Define any relationships (if needed)
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'grade_submission_subject', 'grade_submission_id', 'subject_id');
    }

    public function students()
    {
        return $this->belongsToMany(PNUser::class, 'grade_submission_subject', 'grade_submission_id', 'user_id')
            ->withPivot('grade', 'status')
            ->withTimestamps();
    }

    public function studentGrades()
    {
        return $this->hasMany(DB::table('grade_submission_subject'), 'grade_submission_id');
    }

    public function proofs()
    {
        return $this->hasMany(GradeSubmissionProof::class)->with('student');
    }
}

