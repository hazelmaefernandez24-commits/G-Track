<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSubmissionProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_submission_id',
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'status'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    public function gradeSubmission()
    {
        return $this->belongsTo(GradeSubmission::class);
    }

    public function student()
    {
        return $this->belongsTo(PNUser::class, 'user_id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proof) {
            \Log::info('Creating proof record:', [
                'grade_submission_id' => $proof->grade_submission_id,
                'user_id' => $proof->user_id,
                'file_path' => $proof->file_path,
                'file_name' => $proof->file_name,
                'file_type' => $proof->file_type,
                'status' => $proof->status
            ]);
        });
    }
} 