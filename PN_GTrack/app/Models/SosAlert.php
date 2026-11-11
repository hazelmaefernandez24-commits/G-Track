<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SosAlert extends Model
{
    protected $table = 'emergency_alerts';

    protected $fillable = [
        'student_id',
        'location_id',
        'type',
        'description',
        'status',
        'read_at',
        'acknowledged_by_admin_id',
        'acknowledged_at',
        'resolved_by_admin_id',
        'resolved_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('sos_alerts', fn (Builder $query) => $query->where('type', 'sos'));
        static::creating(function (self $alert): void {
            $alert->type = 'sos';
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'acknowledged_by_admin_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }

    public function alertLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(AlertMedia::class, 'emergency_alert_id');
    }

    public function getVideoUrlAttribute(): ?string
    {
        $media = $this->media->first(fn (AlertMedia $item) => str_starts_with($item->mime_type, 'video/'));

        return $media?->url;
    }

    public function getMediaUrlAttribute(): ?string
    {
        return $this->media->first()?->url;
    }

    public function getLocationAttribute(): ?string
    {
        $location = $this->alertLocation;

        return $location ? $location->latitude . ', ' . $location->longitude : null;
    }

    public function getAcknowledgedByNameAttribute(): ?string
    {
        return $this->acknowledgedBy
            ? trim(implode(' ', array_filter([
                $this->acknowledgedBy->first_name,
                $this->acknowledgedBy->middle_initial ? $this->acknowledgedBy->middle_initial . '.' : null,
                $this->acknowledgedBy->last_name,
            ])))
            : null;
    }

    public function getResolvedByNameAttribute(): ?string
    {
        return $this->resolvedBy
            ? trim(implode(' ', array_filter([
                $this->resolvedBy->first_name,
                $this->resolvedBy->middle_initial ? $this->resolvedBy->middle_initial . '.' : null,
                $this->resolvedBy->last_name,
            ])))
            : null;
    }

    public function getReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
