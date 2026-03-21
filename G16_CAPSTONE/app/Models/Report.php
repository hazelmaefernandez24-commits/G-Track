<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo_path',
        'report_date',
        'title',
        'comment',
        'status',
        'date_resolved',
        'staff_in_charge',
        'area'
    ];

    protected $casts = [
        'report_date' => 'date',
        'date_resolved' => 'date'
    ];

    // Accessor for photo attribute to return photo_path
    public function getPhotoAttribute()
    {
        return $this->photo_path;
    }

    // Check if report is resolved
    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    // Check if report is active
    public function isActive()
    {
        return $this->status === 'active';
    }

    // Get formatted date resolved
    public function getFormattedDateResolvedAttribute()
    {
        return $this->date_resolved ? $this->date_resolved->format('M d, Y') : 'Not Resolved';
    }

    // Get formatted report date
    public function getFormattedReportDateAttribute()
    {
        return $this->report_date->format('M d, Y');
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'resolved' ? 'badge-success' : 'badge-warning';
    }
}