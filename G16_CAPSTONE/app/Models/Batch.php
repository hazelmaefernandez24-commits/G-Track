<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StudentDetail;

class Batch extends Model
{
    protected $table = 'batches';
    protected $fillable = [
        'year',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students()
    {
    // Return student details (Login) that reference this batch
    return $this->hasMany(StudentDetail::class, 'batch', 'year');
    }

    // Get display name (e.g., "Batch 2025" or custom name)
    public function getDisplayNameAttribute()
    {
        return $this->name ?: "Batch {$this->year}";
    }

    // Get active batches only
    public static function active()
    {
        return static::where('is_active', true)->orderBy('year');
    }

    // Get all batches ordered by year
    public static function ordered()
    {
        return static::orderBy('year');
    }
}