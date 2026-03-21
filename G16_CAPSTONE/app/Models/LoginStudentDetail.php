<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginStudentDetail extends Model
{
    protected $connection = 'login_db'; // Use Login database connection
    protected $table = 'student_details';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'batch',
        'student_id',
        'course',
        'year_level',
        'section',
        'contact_number',
        'address',
        'emergency_contact',
        'emergency_contact_number',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'batch' => 'integer',
        'year_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this student detail
     */
    public function user()
    {
        return $this->belongsTo(LoginPNUser::class, 'user_id', 'user_id');
    }

    /**
     * Scope for specific batch
     */
    public function scopeBatch($query, $batch)
    {
        return $query->where('batch', $batch);
    }

    /**
     * Get active batches
     */
    public static function getActiveBatches()
    {
        return self::select('batch')
            ->distinct()
            ->whereNotNull('batch')
            ->orderBy('batch', 'desc')
            ->get()
            ->map(function($item) {
                return (object)[
                    'year' => $item->batch,
                    'display_name' => 'Batch ' . $item->batch
                ];
            });
    }
}
