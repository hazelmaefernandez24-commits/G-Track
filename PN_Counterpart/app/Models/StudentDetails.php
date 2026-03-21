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

    protected static function booted()
    {
        static::created(function ($student) {
            if ($student->batch) {
                Batch::firstOrCreate([
                    'batch_year' => $student->batch,
                ]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id', 'student_id');
    }

    public function batchInfo()
    {
        return $this->belongsTo(Batch::class, 'batch', 'batch_year');
    }

    // Alias for backward compatibility - returns the same as batchInfo()
    public function batch()
    {
        return $this->batchInfo();
    }

    public function getTotalPaidAttribute()
    {
        // Sum all approved payments (added by finance or approved payment proofs)
        return $this->payments()->whereIn('status', ['Approved', 'Added by Finance'])->sum('amount');
    }

    public function getRemainingBalanceAttribute()
    {
        $totalDue = $this->batchInfo->total_due ?? 0;
        return max(0, $totalDue - $this->total_paid);
    }

// In StudentDetails.php
    protected $casts = [
        'batch' => 'string',
    ];


}
