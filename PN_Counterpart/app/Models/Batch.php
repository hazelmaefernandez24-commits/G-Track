<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = ['batch_year', 'total_due'];

    /**
     * Get students in this batch
     */
    public function studentDetails()
    {
        return $this->hasMany(StudentDetails::class, 'batch', 'batch_year');
    }

    /**
     * Get payments for this batch through student details
     */
    public function payments()
    {
        return $this->hasManyThrough(
            Payment::class,
            StudentDetails::class,
            'batch', // Foreign key on student_details table
            'student_id', // Foreign key on payments table
            'batch_year', // Local key on batches table
            'student_id' // Local key on student_details table
        );
    }

    /**
     * Sync batches from existing student details
     */
    public static function syncFromStudentDetails()
    {
        // Get distinct batch years from student_details
        $uniqueBatches = StudentDetails::distinct()->pluck('batch')->filter();

        // Create batch records for any missing batch years
        foreach ($uniqueBatches as $batchYear) {
            self::firstOrCreate(
                ['batch_year' => $batchYear],
                ['total_due' => 0] // Default total_due
            );
        }
    }
}
