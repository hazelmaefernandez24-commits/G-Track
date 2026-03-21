<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'category',
        'semester',
        'academic_year',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    // Accessors
    public function getCategoryColorAttribute()
    {
        $colors = [
            'school_activity' => '#3498db',  // Blue
            'holiday' => '#e74c3c',         // Red
            'examination' => '#f39c12',     // Orange
            'deadline' => '#e67e22',        // Dark Orange
            'vacation' => '#27ae60',        // Green
            'special' => '#9b59b6',         // Purple
        ];

        return $colors[$this->category] ?? '#95a5a6'; // Default gray
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            'school_activity' => 'School Activity',
            'holiday' => 'Holiday',
            'examination' => 'Examination',
            'deadline' => 'Deadline',
            'vacation' => 'Vacation',
            'special' => 'Special Event',
        ];

        return $labels[$this->category] ?? 'Other';
    }
}
