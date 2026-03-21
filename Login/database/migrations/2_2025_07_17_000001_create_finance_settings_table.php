<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value');
            $table->string('setting_type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // payment_reminders, notifications, auto_detection, general
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default settings - mura sa phone settings nga naa default values
        $defaultSettings = [
            // Payment Reminder Settings Category
            [
                'setting_key' => 'payment_reminder_first_after_months',
                'setting_value' => '2',
                'setting_type' => 'integer',
                'description' => 'Send first reminder after X months without payment',
                'category' => 'payment_reminders'
            ],
            [
                'setting_key' => 'payment_reminder_follow_up_interval',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'Follow-up reminder interval in months',
                'category' => 'payment_reminders'
            ],
            [
                'setting_key' => 'payment_reminder_max_reminders',
                'setting_value' => '5',
                'setting_type' => 'integer',
                'description' => 'Maximum number of reminders to send',
                'category' => 'payment_reminders'
            ],
            [
                'setting_key' => 'payment_reminder_auto_enabled',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Enable automatic payment reminders',
                'category' => 'payment_reminders'
            ],

            // Notification Method Settings Category
            [
                'setting_key' => 'notification_method_email',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Send notifications via email',
                'category' => 'notification_methods'
            ],
            [
                'setting_key' => 'notification_method_dashboard',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Show notifications in student dashboard',
                'category' => 'notification_methods'
            ],
            [
                'setting_key' => 'notification_method_sms',
                'setting_value' => 'false',
                'setting_type' => 'boolean',
                'description' => 'Send notifications via SMS (future feature)',
                'category' => 'notification_methods'
            ],
            [
                'setting_key' => 'notification_sender_name',
                'setting_value' => 'Finance Department',
                'setting_type' => 'string',
                'description' => 'Name to display as notification sender',
                'category' => 'notification_methods'
            ],

            // Monthly Reminder Settings Category
            [
                'setting_key' => 'monthly_reminder_enabled',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Enable monthly reminders on first day of month',
                'category' => 'monthly_reminders'
            ],
            [
                'setting_key' => 'monthly_reminder_day',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'Day of month to send monthly reminders (1-31)',
                'category' => 'monthly_reminders'
            ],
            [
                'setting_key' => 'monthly_reminder_time',
                'setting_value' => '08:00',
                'setting_type' => 'string',
                'description' => 'Time to send monthly reminders (HH:MM format)',
                'category' => 'monthly_reminders'
            ],

            // Auto Detection Settings Category
            [
                'setting_key' => 'auto_detect_overdue_enabled',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Automatically detect overdue students',
                'category' => 'auto_detection'
            ],
            [
                'setting_key' => 'auto_detect_threshold_months',
                'setting_value' => '3',
                'setting_type' => 'integer',
                'description' => 'Months without payment to consider overdue',
                'category' => 'auto_detection'
            ],
            [
                'setting_key' => 'auto_detect_scan_frequency',
                'setting_value' => 'daily',
                'setting_type' => 'string',
                'description' => 'How often to scan for overdue students (daily, weekly, monthly)',
                'category' => 'auto_detection'
            ],

            // General Finance Settings Category
            [
                'setting_key' => 'finance_department_email',
                'setting_value' => 'finance@pnphilippines.com',
                'setting_type' => 'string',
                'description' => 'Finance department contact email',
                'category' => 'general'
            ],
            [
                'setting_key' => 'payment_grace_period_days',
                'setting_value' => '7',
                'setting_type' => 'integer',
                'description' => 'Grace period in days before marking payment overdue',
                'category' => 'general'
            ],
            [
                'setting_key' => 'currency_symbol',
                'setting_value' => '₱',
                'setting_type' => 'string',
                'description' => 'Currency symbol to display',
                'category' => 'general'
            ],
            [
                'setting_key' => 'enable_audit_logging',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'description' => 'Log all finance setting changes for audit',
                'category' => 'general'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('finance_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_settings');
    }
};
