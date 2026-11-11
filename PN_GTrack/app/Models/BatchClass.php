<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchClass extends Model
{
    protected $table = 'student_classes';

    protected $fillable = [
        'year',
    ];

    /**
     * Get students enrolled in this class/batch.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'student_class_id');
    }

    public function getNameAttribute(): string
    {
        return (string) $this->year;
    }
}
