<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyConfiguration extends Model
{
    protected $fillable = [
        'penalty_code',
        'display_name',
        'short_label',
        'badge_class',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get all active penalty configurations ordered by sort order
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get penalty display name by code
     */
    public static function getDisplayName($code)
    {
        $config = self::where('penalty_code', $code)->first();
        return $config ? $config->display_name : $code;
    }

    /**
     * Get penalty badge class by code
     */
    public static function getBadgeClass($code)
    {
        $config = self::where('penalty_code', $code)->first();
        return $config ? $config->badge_class : 'bg-secondary';
    }

    /**
     * Get all penalty codes as array for dropdowns
     */
    public static function getForDropdown()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('display_name', 'penalty_code')
            ->toArray();
    }
}
