<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $casts = [
        'status' => 'boolean',
    ];

    protected $fillable = [
    'student_id', 
    'first_name',
    'middle_initial',
    'last_name',
    'name', 
    'email', 
    'gender', 
    'class', 
    'status', 
    'battery_level', 
    'signal_status', 
    'last_update', 
    'contact',
    'sos_status',
    'latitude',
    'longitude',
    'profile_picture',
    'student_class_id',
];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function batchClass()
    {
        return $this->belongsTo(BatchClass::class, 'class', 'name');
    }

    public function studentClass()
    {
        return $this->belongsTo(BatchClass::class, 'student_class_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(StudentStatus::class);
    }

    public function latestStatus()
    {
        return $this->hasOne(StudentStatus::class)->latestOfMany('recorded_at');
    }

    public function getStatusAttribute(): bool
    {
        return $this->currentStatus()?->status === 'online';
    }

    public function getNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_initial ? $this->middle_initial . '.' : null,
            $this->last_name,
        ])));
    }

    public function getClassAttribute(): ?string
    {
        if ($this->relationLoaded('studentClass')) {
            $year = $this->studentClass?->year;
        } else {
            $year = $this->studentClass()->value('year');
        }

        return $year === null ? null : (string) $year;
    }

    public function getSosStatusAttribute(): string
    {
        return $this->currentStatus()?->sos_status ?? 'safe';
    }

    public function getLastUpdateAttribute(): mixed
    {
        return $this->currentStatus()?->recorded_at;
    }

    public function recordStatus(?string $status = null, ?string $sosStatus = null): StudentStatus
    {
        $current = $this->currentStatus();

        return $this->statusHistory()->create([
            'status' => $status ?? $current?->status ?? 'offline',
            'sos_status' => $sosStatus ?? $current?->sos_status ?? 'safe',
            'recorded_at' => now(),
        ]);
    }

    private function currentStatus(): ?StudentStatus
    {
        if ($this->relationLoaded('latestStatus')) {
            return $this->getRelation('latestStatus');
        }

        return $this->latestStatus()->first();
    }
}
