<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogifyAbsentRecord extends Model
{
    use HasFactory;

    protected $table = 'logify_absent_records';

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'batch',
        'group',
        'month',
        'year',
        'academic_absent_count',
        'sync_batch_id',
        'last_synced_at'
    ];

    protected $casts = [
        'academic_absent_count' => 'integer',
        'last_synced_at' => 'datetime'
    ];

    /**
     * Get the student details associated with this record
     */
    public function studentDetails()
    {
        return $this->belongsTo(StudentDetails::class, 'student_id', 'student_id');
    }

    /**
     * Get violations created from this absent record
     */
    public function violations()
    {
        return $this->hasMany(Violation::class, 'logify_sync_batch_id', 'sync_batch_id');
    }

    /**
     * Scope to get records for a specific month/year
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope to get recent records
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('last_synced_at', '>=', now()->subDays($days));
    }
}
