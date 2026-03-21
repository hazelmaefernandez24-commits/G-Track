<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FinanceSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'category'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get a setting value by key with automatic type casting
     * Mura sa phone settings nga ma-retrieve ang value with correct type
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "finance_setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('setting_key', $key)
                          ->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->setting_value, $setting->setting_type);
        });
    }

    /**
     * Set a setting value with automatic type detection
     * Mura sa phone settings nga ma-save ang value with proper type
     */
    public static function set($key, $value, $type = null, $description = null, $category = 'general')
    {
        if ($type === null) {
            $type = self::detectType($value);
        }

        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => self::prepareValue($value, $type),
                'setting_type' => $type,
                'description' => $description,
                'category' => $category
            ]
        );

        // Clear cache
        Cache::forget("finance_setting_{$key}");

        // Log for audit if enabled
        if (self::get('enable_audit_logging', true)) {
            // Get full user name from session
            $user = session('user');
            $fullName = 'System';

            if ($user) {
                $firstName = $user['user_fname'] ?? '';
                $lastName = $user['user_lname'] ?? '';
                $fullName = trim($firstName . ' ' . $lastName) ?: 'Unknown User';
            }

            Log::info("Finance setting updated: {$key} = {$value}", [
                'setting_key' => $key,
                'old_value' => $setting->getOriginal('setting_value'),
                'new_value' => $value,
                'user' => $fullName
            ]);
        }

        return $setting;
    }

    /**
     * Get all settings by category - mura sa phone settings nga organized by category
     */
    public static function getByCategory($category)
    {
        $cacheKey = "finance_settings_category_{$category}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category) {
            $settings = self::where('category', $category)
                           ->get();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->setting_key] = [
                    'value' => self::castValue($setting->setting_value, $setting->setting_type),
                    'description' => $setting->description,
                    'type' => $setting->setting_type
                ];
            }

            return $result;
        });
    }

    /**
     * Get all settings organized by categories - mura sa complete phone settings
     */
    public static function getAllByCategories()
    {
        $cacheKey = "finance_settings_all_categories";
        
        return Cache::remember($cacheKey, 3600, function () {
            $settings = self::all();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->category][$setting->setting_key] = [
                    'value' => self::castValue($setting->setting_value, $setting->setting_type),
                    'description' => $setting->description,
                    'type' => $setting->setting_type
                ];
            }

            return $result;
        });
    }

    /**
     * Helper methods for specific setting categories
     */
    public static function getPaymentReminderSettings()
    {
        return [
            'first_after_months' => self::get('payment_reminder_first_after_months', 2),
            'follow_up_interval' => self::get('payment_reminder_follow_up_interval', 1),
            'max_reminders' => self::get('payment_reminder_max_reminders', 5),
            'auto_enabled' => self::get('payment_reminder_auto_enabled', true),
        ];
    }

    public static function getNotificationMethodSettings()
    {
        return [
            'email' => self::get('notification_method_email', true),
            'dashboard' => self::get('notification_method_dashboard', true),
            'student_account' => self::get('notification_method_student_account', true),
            'sms' => self::get('notification_method_sms', false),
            'sender_name' => self::get('notification_sender_name', 'Finance Department'),
        ];
    }

    public static function getMonthlyReminderSettings()
    {
        return [
            'enabled' => self::get('monthly_reminder_enabled', true),
            'day' => self::get('monthly_reminder_day', 1),
            'time' => self::get('monthly_reminder_time', '08:00'),
        ];
    }



    public static function getGeneralSettings()
    {
        return [
            'department_email' => self::get('finance_department_email', 'finance@pnphilippines.com'),
            'grace_period_months' => self::get('payment_grace_period_months', 2),
            'audit_logging' => self::get('enable_audit_logging', true),
        ];
    }

    public static function getCounterpartPaymentStartSettings()
    {
        return [
            'start_month' => self::get('counterpart_payment_start_month', 1),
            'start_year' => self::get('counterpart_payment_start_year', date('Y')),
        ];
    }

    public static function getBatchCounterpartPaymentStartSettings($batchYear)
    {
        // 1) New-style keys
        $startMonth = self::get("batch_counterpart_payment_start_month_{$batchYear}", '');
        $startYear = self::get("batch_counterpart_payment_start_year_{$batchYear}", '');

        // 2) Old-style keys used by some UI endpoints
        $legacyStartMonth = self::get("batch_{$batchYear}_payment_start_month", '');
        $legacyStartYear = self::get("batch_{$batchYear}_payment_start_year", '');

        // 3) JSON blob format: batch_{batchYear}_settings with keys start_month/start_year
        $batchSettings = self::get("batch_{$batchYear}_settings", null);
        $jsonStartMonth = null;
        $jsonStartYear = null;
        $jsonEnabled = true;
        if (is_array($batchSettings)) {
            $jsonStartMonth = $batchSettings['start_month'] ?? null;
            $jsonStartYear = $batchSettings['start_year'] ?? null;
            // If UI provides an enable flag, respect it; otherwise treat as enabled
            $jsonEnabled = array_key_exists('enable_payment_start', $batchSettings)
                ? (bool)$batchSettings['enable_payment_start']
                : true;
        }

        // Choose the first available non-empty source, respecting enable flag for JSON
        $resolvedStartMonth = null;
        $resolvedStartYear = null;

        if ($startMonth !== '' || $startYear !== '') {
            $resolvedStartMonth = $startMonth;
            $resolvedStartYear = $startYear;
        } elseif ($legacyStartMonth !== '' || $legacyStartYear !== '') {
            $resolvedStartMonth = $legacyStartMonth;
            $resolvedStartYear = $legacyStartYear;
        } elseif ($jsonEnabled && ($jsonStartMonth !== null || $jsonStartYear !== null)) {
            $resolvedStartMonth = $jsonStartMonth;
            $resolvedStartYear = $jsonStartYear;
        }

        // If still unresolved, return null to allow caller's global fallback behavior
        if ($resolvedStartMonth === null && $resolvedStartYear === null) {
            return null;
        }

        // Apply global fallback per field if missing
        $resolvedStartMonth = $resolvedStartMonth !== null && $resolvedStartMonth !== ''
            ? (int)$resolvedStartMonth
            : (int) self::get('counterpart_payment_start_month', 1);
        $resolvedStartYear = $resolvedStartYear !== null && $resolvedStartYear !== ''
            ? (int)$resolvedStartYear
            : (int) self::get('counterpart_payment_start_year', date('Y'));

        return [
            'start_month' => $resolvedStartMonth,
            'start_year' => $resolvedStartYear,
        ];
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Prepare value for storage
     */
    private static function prepareValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
            case 'array':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Auto-detect value type
     */
    private static function detectType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_array($value)) {
            return 'array';
        }
        return 'string';
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        $categories = ['payment_reminders', 'notification_methods', 'monthly_reminders', 'auto_detection', 'general', 'matrix_settings'];
        
        foreach ($categories as $category) {
            Cache::forget("finance_settings_category_{$category}");
        }
        
        Cache::forget("finance_settings_all_categories");
        
        // Clear individual setting caches
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("finance_setting_{$setting->setting_key}");
        }
    }



    public static function getGeneralSettingsCached()
    {
    return cache()->remember('finance_settings_general', 60, function () {
        return self::where('category', 'general')->get()->keyBy('setting_key');
    });
    }



}
