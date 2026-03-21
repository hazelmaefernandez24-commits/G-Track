<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'school_id',
        'class_id',
        'grade_submission_id',
        'student_count',
        'status',
        'intervention_date',
        'educator_assigned',
        'remarks',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'intervention_date' => 'date',
        'student_count' => 'integer'
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'school_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'class_id');
    }

    public function gradeSubmission()
    {
        return $this->belongsTo(GradeSubmission::class);
    }

    public function educatorAssigned()
    {
        return $this->belongsTo(PNUser::class, 'educator_assigned', 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(PNUser::class, 'created_by', 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(PNUser::class, 'updated_by', 'user_id');
    }
}
