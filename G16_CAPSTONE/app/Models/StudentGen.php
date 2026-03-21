<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGen extends Model
{
      protected $table = 'student_group16'; // <-- Add this line

      protected $fillable = [
        'name',
        'gender',
        'batch', // Use 'batch' instead of 'class_year' for consistency with your DB
    ];

    public function assignmentMembers()
    {
        return $this->hasMany(AssignmentMember::class);
    }

    public function batchModel()
    {
        return $this->belongsTo(Batch::class, 'batch', 'year');
    }

    // Get available batches for dropdowns (all active batches, even without students)
    public static function getAvailableBatches()
    {
    return \App\Models\StudentDetail::select('batch')->distinct()->orderBy('batch')->pluck('batch')->toArray();
    }

    // Get batches that have students (for display purposes)
    public static function getBatchesWithStudents()
    {
        return \App\Models\StudentDetail::select('batch')->distinct()->orderBy('batch')->get()->map(function($r){
            return (object)['year' => $r->batch, 'display_name' => (string)$r->batch];
        });
    }

    public function student()
    {
    return $this->student_group16();
    }
}
