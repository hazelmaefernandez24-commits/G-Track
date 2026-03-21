<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_year',
        'gender',
        'total_count',
        'allocated_count',
        'allocation_date'
    ];

    protected $casts = [
        'allocation_date' => 'date'
    ];

    /**
     * Get available students count
     */
    public function getAvailableCountAttribute()
    {
        return $this->total_count - $this->allocated_count;
    }

    /**
     * Check if allocation is full
     */
    public function getIsFullAttribute()
    {
        return $this->allocated_count >= $this->total_count;
    }

    /**
     * Get allocation percentage
     */
    public function getAllocationPercentageAttribute()
    {
        if ($this->total_count == 0) {
            return 0;
        }
        return round(($this->allocated_count / $this->total_count) * 100, 1);
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('allocation_date', $date);
    }

    /**
     * Scope for specific batch and gender
     */
    public function scopeForBatchGender($query, $batch, $gender)
    {
        return $query->where('batch_year', $batch)->where('gender', $gender);
    }

    /**
     * Allocate students
     */
    public function allocateStudents($count)
    {
        if ($this->available_count >= $count) {
            $this->allocated_count += $count;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Deallocate students
     */
    public function deallocateStudents($count)
    {
        if ($this->allocated_count >= $count) {
            $this->allocated_count -= $count;
            $this->save();
            return true;
        }
        return false;
    }
}
