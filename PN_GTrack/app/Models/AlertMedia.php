<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertMedia extends Model
{
    protected $fillable = ['emergency_alert_id', 'file_path', 'mime_type'];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(SosAlert::class, 'emergency_alert_id');
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}
