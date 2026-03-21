<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $primaryKey = 'school_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_id',
        'name',
        'department',
        'course',
        'semester_count',
        'term_type',
        'terms',
        'passing_grade_min',
        'passing_grade_max',
        'failing_grade_min', // Added
        'failing_grade_max'  // Added
    ];

    protected $casts = [
        'semester_count' => 'integer',
        'term_type' => 'string',
        'terms' => 'array',
        'passing_grade_min' => 'decimal:1',
        'passing_grade_max' => 'decimal:1',
        'failing_grade_min' => 'decimal:1', // Added
        'failing_grade_max' => 'decimal:1'  // Added
    ];

    public function getRouteKeyName()
    {
        return 'school_id';
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id', 'school_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'school_id', 'school_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'school_id', 'school_id');
    }
}