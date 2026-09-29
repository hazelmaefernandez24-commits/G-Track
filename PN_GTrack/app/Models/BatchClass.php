<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchClass extends Model
{
    protected $table = 'student_classes';

    protected $fillable = [
        'name',
    ];

    /**
     * Get students enrolled in this class/batch.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class', 'name');
    }
}
