<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatus extends Model
{
    public $timestamps = false;

    protected $fillable = ['student_id', 'status', 'sos_status', 'recorded_at'];

    protected $casts = ['recorded_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
