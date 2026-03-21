<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data_type',
        'data_identifier',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    /**
     * Get the user who viewed the data
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mark data as viewed by a user
     */
    public static function markAsViewed(string $userId, string $dataType, string $dataIdentifier): void
    {
        static::updateOrCreate(
            [
                'user_id' => $userId,
                'data_type' => $dataType,
                'data_identifier' => $dataIdentifier
            ],
            [
                'viewed_at' => now()
            ]
        );
    }

    /**
     * Check if user has viewed specific data
     */
    public static function hasViewed(string $userId, string $dataType, string $dataIdentifier): bool
    {
        return static::where('user_id', $userId)
            ->where('data_type', $dataType)
            ->where('data_identifier', $dataIdentifier)
            ->exists();
    }

    /**
     * Get data that user hasn't viewed yet
     */
    public static function getUnviewedData($query, string $userId, string $dataType, string $identifierColumn = 'id')
    {
        $viewedIdentifiers = static::where('user_id', $userId)
            ->where('data_type', $dataType)
            ->pluck('data_identifier')
            ->toArray();

        return $query->whereNotIn($identifierColumn, $viewedIdentifiers);
    }

    /**
     * Clean up old view records (optional, for maintenance)
     */
    public static function cleanupOldViews(int $daysOld = 30): void
    {
        static::where('viewed_at', '<', now()->subDays($daysOld))->delete();
    }
}
