<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DamageReport extends Model
{
    use HasFactory;

    protected $connection = 'login';
    protected $table = 'damage_reports';

    protected $fillable = [
        'location',
        'priority',
        'item_damaged',
        'description',
        'photo_path',
        'reporter_contact',
        'reported_by',
        'reporter_name',
        'status',
        'reported_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes'
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationship with the user who reported the damage
    public function reporter()
    {
        return $this->belongsTo(PNUser::class, 'reported_by');
    }

    // Relationship with the user who resolved the damage
    public function resolver()
    {
        return $this->belongsTo(PNUser::class, 'resolved_by');
    }

    // Scope for pending reports
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for resolved reports
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    // Scope for high priority reports
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Get photo URL
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return null;
    }

    // Get priority badge class
    public function getPriorityBadgeClassAttribute()
    {
        switch ($this->priority) {
            case 'high':
                return 'bg-danger';
            case 'medium':
                return 'bg-warning';
            case 'low':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'resolved':
                return 'bg-success';
            case 'in_progress':
                return 'bg-warning';
            case 'pending':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
    }
}
