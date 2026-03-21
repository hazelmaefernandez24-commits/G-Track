<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'student_id',
        'amount',
        'payment_date',
        'payment_proof',
        'reference_number',
        'sender_name',
        'payment_mode', // Add this
        'status',
    ];
    // Don't use fillable, use guarded instead to ensure payment_id is protected
    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(StudentDetails::class, 'student_id', 'student_id');
    }

    public function studentDetails()
    {
        return $this->belongsTo(StudentDetails::class, 'student_id', 'student_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public static function calculateTotalPaid($student)
    {
        return $student->payments->where('status', 'Approved')->sum('amount');
    }

    public function scopeApprovedOrAddedByFinance($query)
    {
        return $query->whereIn('status', ['Approved', 'Added by Finance']);
    }
}

