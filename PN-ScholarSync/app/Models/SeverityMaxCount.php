<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeverityMaxCount extends Model
{
    use HasFactory;

    protected $table = 'severity_max_counts';

    protected $fillable = [
        'severity_id',
        'severity_name',
        'max_count',
        'base_penalty',
        'escalated_penalty',
        'description'
    ];

    protected $casts = [
        'max_count' => 'integer'
    ];

    /**
     * Get the severity relationship
     */
    public function severity()
    {
        return $this->belongsTo(Severity::class, 'severity_id');
    }

    /**
     * Get the penalty display name for base penalty
     */
    public function getBasePenaltyDisplayAttribute()
    {
        $penalties = [
            'VW' => 'Verbal Warning',
            'WW' => 'Written Warning',
            'Pro' => 'Probationary of Contract',
            'T' => 'Termination of Contract'
        ];

        return $penalties[$this->base_penalty] ?? $this->base_penalty;
    }

    /**
     * Get the penalty display name for escalated penalty
     */
    public function getEscalatedPenaltyDisplayAttribute()
    {
        $penalties = [
            'VW' => 'Verbal Warning',
            'WW' => 'Written Warning',
            'Pro' => 'Probationary of Contract',
            'T' => 'Termination of Contract'
        ];

        return $penalties[$this->escalated_penalty] ?? $this->escalated_penalty;
    }

    /**
     * Scope to order by severity level
     */
    public function scopeOrderBySeverity($query)
    {
        return $query->orderByRaw("FIELD(severity_name, 'Low', 'Medium', 'High', 'Very High')");
    }
}
