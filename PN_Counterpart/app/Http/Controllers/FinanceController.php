<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\StudentDetails;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\CustomNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    public function profile()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access profile.');
        }

        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || !$localUser->finance) {
            return back()->with('error', 'Finance details not found. Please contact administrator.');
        }

        $finance = $localUser->finance;
        return view('finance.profile', compact('finance'));
    }

    public function managePayments(Request $request)
    {
        $studentBatchYears = \App\Models\StudentDetails::distinct()->pluck('batch');
        foreach ($studentBatchYears as $batch) {
            if ($batch) {
                \App\Models\Batch::firstOrCreate(['batch_year' => $batch]);
            }
        }

        $batchYear = $request->input('batch_year');

        $studentsQuery = User::where('user_role', 'student')
            ->with(['studentDetails.payments', 'studentDetails.batchInfo']);

        if ($batchYear) {
            $studentsQuery->whereHas('studentDetails', function ($q) use ($batchYear) {
                $q->where('batch', $batchYear);
            });
        }

        $students = $studentsQuery->get()
            ->map(function ($user) {
                $studentDetails = $user->studentDetails;
                if ($studentDetails) {
                    $studentDetails->first_name = $user->user_fname;
                    $studentDetails->last_name  = $user->user_lname;
                    $studentDetails->email      = $user->user_email;
                    $studentDetails->batch_year_value = $studentDetails->batch;

                    // Use only Approved and Added by Finance for paid totals to keep consistent with enforcement
                    $totalPaid = $studentDetails->payments
                        ->whereIn('status', ['Approved', 'Added by Finance'])
                        ->sum('amount') ?? 0;
                    $studentDetails->total_paid = $totalPaid;

                    $totalDue = optional($studentDetails->batchInfo)->total_due ?? 0;
                    $studentDetails->remaining_balance = max(0, $totalDue - $totalPaid);
                }
                return $studentDetails;
            })
            ->filter()
            ->sortBy('last_name');

        $batches = Batch::all();

        $dynamicPaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();

        $paymentMethods = collect([
            (object)[
                'id' => 'cash',
                'name' => 'Cash',
                'account_name' => null,
                'account_number' => null,
                'is_standard' => true
            ]
        ])->merge($dynamicPaymentMethods);

        $matrixData = $this->generateMatrixData($students);

        return view('finance.financePayments', compact('students', 'batches', 'paymentMethods', 'matrixData'));
    }
    private function generateMatrixData($students)
    {
        $matrixData = [];
        $currentYear = now()->year;
        $currentMonth = now()->month;

        foreach ($students as $studentDetails) {
            if (!$studentDetails) {
                continue;
            }
            $batchYear = $studentDetails->batch;
            $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);

            if ($paymentStartSettings === null) {
                $startMonth = 1;
                $startYear = $currentYear;
            } else {
                $startMonth = $paymentStartSettings['start_month'];
                $startYear = $paymentStartSettings['start_year'];
            }

            $startDate = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);

            // Determine monthly fee with batch-specific override (used for month range sizing)
            $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
            $batchSettings = json_decode($batchSettingsJson, true) ?: [];
            $monthlyFee = $batchSettings['monthly_default'] ?? 500;     
            if (!$monthlyFee || $monthlyFee <= 0) { $monthlyFee = 500; }

            // Use total_due to determine number of months to show
            $totalDue = optional($studentDetails->batchInfo)->total_due ?? 0;
            $monthsRequired = $monthlyFee > 0 ? (int) ceil($totalDue / $monthlyFee) : 12;
            $maxMonths = max(12, $monthsRequired);

            $monthRange = [];
            for ($i = 0; $i < $maxMonths; $i++) {
                $monthDate = $startDate->copy()->addMonths($i);
                $monthRange[] = [
                    'month' => $monthDate->month,
                    'year' => $monthDate->year,
                    'display' => $monthDate->format('M Y'),
                    'is_current' => $monthDate->month == $currentMonth && $monthDate->year == $currentYear
                ];
            }
            
            $studentData = [
                'student' => [
                    'student_id' => $studentDetails->student_id,
                    'first_name' => $studentDetails->first_name,
                    'last_name' => $studentDetails->last_name,
                    'display_name' => $studentDetails->last_name . ', ' . $studentDetails->first_name,
                    'batch' => $studentDetails->batch,
                    'total_paid' => $studentDetails->total_paid ?? 0,
                    'remaining_balance' => $studentDetails->remaining_balance ?? 0,
                    'total_due' => optional($studentDetails->batchInfo)->total_due ?? 0,
                    'payment_start_month' => $startMonth,
                    'payment_start_year' => $startYear
                ],
                'monthly_data' => [],
                'month_range' => $monthRange
            ];

            foreach ($monthRange as $monthInfo) {
                $monthKey = $monthInfo['month'] . '_' . $monthInfo['year'];
                $studentData['monthly_data'][$monthKey] = [
                    'total' => 0,
                    'payments' => [],
                    'is_paid' => false,
                    'is_current_month' => $monthInfo['is_current'],
                    'display' => $monthInfo['display'],
                    'month' => $monthInfo['month'],
                    'year' => $monthInfo['year']
                ];
            }
            $payments = $studentDetails->payments->filter(function($payment) {
                return in_array($payment->status, ['Approved', 'Added by Finance']);
            });

            $this->calculatePaidMonths($studentDetails, $payments->sum('amount'));

            // monthlyFee already computed above; reuse here

            $sortedPayments = $payments->sortBy(function($payment) {
                return \Carbon\Carbon::parse($payment->payment_date);
            });

            $totalPaid = $sortedPayments->sum('amount'); 
            $remainingAmount = $totalPaid;
            

            
            $sortedMonthlyData = collect($studentData['monthly_data'])->sortBy(function($monthData, $monthKey) {
                return $monthData['year'] * 10000 + $monthData['month'];
            });

            foreach ($sortedMonthlyData as $monthKey => $monthData) {
                if ($remainingAmount <= 0) break;

                if ($monthData['year'] < $startYear ||
                    ($monthData['year'] == $startYear && $monthData['month'] < $startMonth)) {
                    continue;
                }

                if ($monthData['is_paid']) {
                    continue;
                }

                if ($remainingAmount >= $monthlyFee) {
                    $studentData['monthly_data'][$monthKey]['is_paid'] = true;
                    $studentData['monthly_data'][$monthKey]['status'] = 'paid';
                    $studentData['monthly_data'][$monthKey]['total'] = $monthlyFee;
                    $remainingAmount -= $monthlyFee;
                } else {
                    $studentData['monthly_data'][$monthKey]['is_paid'] = false;
                    $studentData['monthly_data'][$monthKey]['status'] = 'partial';
                    $studentData['monthly_data'][$monthKey]['total'] = $remainingAmount;
                    $studentData['monthly_data'][$monthKey]['remaining_balance'] = $monthlyFee - $remainingAmount;
                    $remainingAmount = 0;
                }
            }

            foreach ($sortedPayments as $payment) {
                $paymentDate = \Carbon\Carbon::parse($payment->payment_date);
                $paymentMonth = $paymentDate->month;
                $paymentYear = $paymentDate->year;
                $paymentAmount = $payment->amount;

                $monthKey = $paymentMonth . '_' . $paymentYear;

                if (isset($studentData['monthly_data'][$monthKey])) {
                    if (!isset($studentData['monthly_data'][$monthKey]['payments'])) {
                        $studentData['monthly_data'][$monthKey]['payments'] = [];
                    }
                    $studentData['monthly_data'][$monthKey]['payments'][] = [
                        'amount' => $paymentAmount,
                        'date' => $payment->payment_date,
                        'mode' => $payment->payment_mode ?? 'Cash'
                    ];
                }
            }

            $matrixData[] = $studentData;
        }

        usort($matrixData, function($a, $b) {
            return strcasecmp(
                strtolower($a['student']['last_name']), 
                strtolower($b['student']['last_name'])
            );
        });

        return $matrixData;
    }

          

    // ==========================================
    // GENERAL SETTINGS METHODS (NEW)
    // ==========================================

    /**
     * Show the general settings page
     */
    public function settings()
    {
        return view('finance.settings');
    }

    public function loadSettings(Request $request)
    {
        $category = $request->query('category', null);

        if ($category === 'general') {
            $settings = \App\Models\FinanceSetting::getGeneralSettingsCached();
            return response()->json(['general' => $settings]);
        }

        try {
            $settings = [
                'payment_reminders' => \App\Models\FinanceSetting::getPaymentReminderSettings(),
                'notification_methods' => \App\Models\FinanceSetting::getNotificationMethodSettings(),
                'monthly_reminders' => \App\Models\FinanceSetting::getMonthlyReminderSettings(),
                'matrix_settings' => [
                    'advance_payment_enabled' => \App\Models\FinanceSetting::get('matrix_advance_payment_enabled', true),
                ],
                'general' => \App\Models\FinanceSetting::getGeneralSettings()
            ];  

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading finance settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings'
            ], 500);
        }
    }

    /**
     * Save finance settings from the phone-style settings modal
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'payment_reminder_first_after_months' => 'nullable|integer|min:1|max:12',
            'payment_reminder_follow_up_interval' => 'nullable|integer|min:1|max:6',
            'payment_reminder_max_reminders' => 'nullable|integer|min:1|max:10',
            'payment_reminder_enabled' => 'nullable|boolean',
            'monthly_reminder_day' => 'nullable|integer|min:1|max:31',
            'monthly_reminder_time' => 'nullable|date_format:H:i',
            'notification_method_email' => 'nullable|boolean',
            'notification_method_dashboard' => 'nullable|boolean',
            'notification_method_student_account' => 'nullable|boolean',
            'notification_method_sms' => 'nullable|boolean',
            'notification_sender_name' => 'nullable|string|max:100',
            'matrix_advance_payment_enabled' => 'nullable|boolean',
            'matrix_monthly_fee' => 'nullable|numeric|min:100|max:10000',
            'payment_grace_period_months' => 'nullable|integer|min:1|max:12',
            'finance_department_email' => 'nullable|email',
            'batch_settings' => 'nullable|array'
        ]);

        try {
            $settings = $request->all();
            
            // Debug logging
            \Log::info('Settings received in controller:', $settings);
            
            // Handle batch settings separately
            $batchSettings = $settings['batch_settings'] ?? [];
            unset($settings['batch_settings']);
            
            // Save regular settings
            foreach ($settings as $key => $value) {
                // For boolean values, save them even if they are false
                // For other values, only save if they are not null or empty
                if (is_bool($value) || ($value !== null && $value !== '')) {
                    \App\Models\FinanceSetting::set($key, $value);
                }
            }
            
            // Save batch-specific overrides (preserve existing matrix logic)
            if (!empty($batchSettings)) {
                \App\Models\FinanceSetting::set('batch_specific_settings', json_encode($batchSettings));
            }

            // Clear cache after updating settings
            \Cache::forget('finance_settings_general');
            \Cache::forget('finance_settings_payment_reminders');
            \Cache::forget('finance_settings_notification_methods');
            \Cache::forget('finance_settings_monthly_reminders');
            \Cache::forget('finance_batch_settings');

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving finance settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings'
            ], 500);
        }
    }

    /**
     * Get setting category based on key
     */
    private function getSettingCategory($key)
    {
        if (str_starts_with($key, 'payment_reminder_')) return 'payment_reminders';
        if (str_starts_with($key, 'notification_')) return 'notification_methods';
        if (str_starts_with($key, 'monthly_reminder_')) return 'monthly_reminders';

        if (str_starts_with($key, 'matrix_')) return 'matrix_settings';
        return 'general';
    }

    public function getAvailableBatches(Request $request)
    {
        try {
            // Use the same logic as the original code - get batches from StudentDetails model
            $studentBatchYears = \App\Models\StudentDetails::distinct()->pluck('batch');
            
            // Filter out null/empty values and create batch objects
            $existingBatches = collect();
            foreach ($studentBatchYears as $batch) {
                if ($batch) {
                    $existingBatches->push((object)['batch_year' => $batch]);
                }
            }
            
            // Sort by batch year
            $existingBatches = $existingBatches->sortBy('batch_year');
            
            return response()->json([
                'success' => true,
                'batches' => $existingBatches->values()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading available batches: ' . $e->getMessage());
            
            // Return default batches as fallback
            $currentYear = date('Y');
            $defaultBatches = [
                (object)['batch_year' => $currentYear - 1],
                (object)['batch_year' => $currentYear],
                (object)['batch_year' => $currentYear + 1]
            ];
            
            return response()->json([
                'success' => true,
                'batches' => $defaultBatches,
                'message' => 'Using default batches due to database error'
            ]);
        }
    }

    public function getBatchSettings(Request $request, $batchYear)
    {
        try {
            // Get batch-specific settings
            $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
            $batchSettings = json_decode($batchSettingsJson, true) ?: [];
            
            // Get payment start settings using original logic format
            $paymentStartMonth = \App\Models\FinanceSetting::get("batch_{$batchYear}_payment_start_month", null);
            $paymentStartYear = \App\Models\FinanceSetting::get("batch_{$batchYear}_payment_start_year", null);
            $enablePaymentStart = \App\Models\FinanceSetting::get("batch_{$batchYear}_enable_payment_start", false);
            
            // Get total_due from Batch model (preserve original logic)
            $batch = \App\Models\Batch::where('batch_year', $batchYear)->first();
            $totalDue = $batch ? $batch->total_due : 0;
            
            // Merge all settings
            $settings = array_merge($batchSettings, [
                'total_amount' => $batchSettings['total_amount'] ?? $totalDue,
                'start_month' => $batchSettings['start_month'] ?? $paymentStartMonth ?? 1,
                'start_year' => $batchSettings['start_year'] ?? $paymentStartYear ?? date('Y'),
                'monthly_default' => $batchSettings['monthly_default'] ?? 500,
                'enable_payment_start' => $enablePaymentStart
            ]);
            
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading batch settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load batch settings'
            ], 500);
        }
    }

    public function saveBatchSettings(Request $request, $batchYear)
    {
        $request->validate([
            'total_amount' => 'nullable|numeric|min:0',
            'start_month' => 'nullable|integer|min:1|max:12',
            'start_year' => 'nullable|integer|min:1900',
            'monthly_default' => 'nullable|numeric|min:100|max:10000'
        ]);

        try {
            // Get existing settings to preserve unchanged values
            $existingSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
            $existingSettings = json_decode($existingSettingsJson, true) ?: [];
            
            // Only update fields that are provided in the request
            $settings = $existingSettings;
            
            if ($request->has('total_amount')) {
                $settings['total_amount'] = $request->total_amount;
            }
            if ($request->has('start_month')) {
                $settings['start_month'] = $request->start_month;
            }
            if ($request->has('start_year')) {
                $settings['start_year'] = $request->start_year;
            }
            if ($request->has('monthly_default')) {
                $settings['monthly_default'] = $request->monthly_default;
            }
            
            $settings['updated_at'] = now();
            
            // Save batch-specific settings (dynamic per batch) - same as original logic
            \App\Models\FinanceSetting::set("batch_{$batchYear}_settings", json_encode($settings));
            
            // Also save payment start settings using original logic format (only if provided)
            if ($request->has('start_month') && $request->start_month) {
                \App\Models\FinanceSetting::set("batch_{$batchYear}_payment_start_month", $request->start_month);
            }
            if ($request->has('start_year') && $request->start_year) {
                \App\Models\FinanceSetting::set("batch_{$batchYear}_payment_start_year", $request->start_year);
            }
            if (($request->has('start_month') && $request->start_month) || ($request->has('start_year') && $request->start_year)) {
                \App\Models\FinanceSetting::set("batch_{$batchYear}_enable_payment_start", true);
            }
            
            // Update Batch model total_due if provided (preserve original logic)
            if ($request->has('total_amount') && $request->total_amount) {
                $batch = \App\Models\Batch::where('batch_year', $batchYear)->first();
                if ($batch) {
                    $batch->total_due = $request->total_amount;
                    $batch->save();
                } else {
                    // Create batch if it doesn't exist
                    \App\Models\Batch::create([
                        'batch_year' => $batchYear,
                        'total_due' => $request->total_amount
                    ]);
                }
            }
            
            // Clear cache
            \Cache::forget("finance_batch_{$batchYear}_settings");

            return response()->json([
                'success' => true,
                'message' => "Batch {$batchYear} settings saved successfully"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving batch settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save batch settings'
            ], 500);
        }
    }

    public function clearBatchSettings(Request $request, $batchYear)
    {
        try {
            // Remove batch-specific settings
            \App\Models\FinanceSetting::forget("batch_{$batchYear}_settings");
            
            // Clear cache
            \Cache::forget("finance_batch_{$batchYear}_settings");

            return response()->json([
                'success' => true,
                'message' => "Batch {$batchYear} settings cleared successfully"
            ]);
        } catch (\Exception $e) {
            \Log::error('Error clearing batch settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear batch settings'
            ], 500);
        }
    }

    /**
     * Get user-friendly display name for setting
     */
    private function getSettingDisplayName($key)
    {
        $names = [
            'payment_reminder_first_after_months' => 'First Reminder After (Months)',
            'payment_reminder_follow_up_interval' => 'Follow-up Interval (Months)',
            'payment_reminder_max_reminders' => 'Maximum Reminders',
            'payment_reminder_auto_enabled' => 'Auto-Enable Reminders',
            'notification_method_email' => 'Email Notifications',
            'notification_method_dashboard' => 'Dashboard Notifications',
            'notification_method_student_account' => 'Student Account Notifications',
            'notification_method_sms' => 'SMS Notifications',
            'notification_sender_name' => 'Sender Name',
            'monthly_reminder_enabled' => 'Monthly Reminders Enabled',
            'monthly_reminder_day' => 'Reminder Day of Month',
            'monthly_reminder_time' => 'Reminder Time',

            'matrix_monthly_fee' => 'Monthly Fee Amount',
            'matrix_advance_payment_enabled' => 'Advance Payment Logic',

        ];

        return $names[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Get setting description
     */
    private function getSettingDescription($key)
    {
        $descriptions = [
            'payment_reminder_first_after_months' => 'Send first reminder after this many months of no payment',
            'payment_reminder_follow_up_interval' => 'Time between follow-up reminders',
            'payment_reminder_max_reminders' => 'Maximum number of reminders to send per student',
            'payment_reminder_auto_enabled' => 'Automatically enable reminders for new students',
            'notification_method_email' => 'Send notifications via email',
            'notification_method_dashboard' => 'Show notifications as modal popup in student dashboard',
            'notification_method_student_account' => 'Send notifications to student account notifications page',
            'notification_method_sms' => 'Send notifications via SMS (future feature)',
            'notification_sender_name' => 'Name shown as sender in notifications',
            'monthly_reminder_enabled' => 'Enable first-day-of-month reminders',
            'monthly_reminder_day' => 'Day of month to send reminders (1-31)',
            'monthly_reminder_time' => 'Time of day to send reminders (HH:MM)',

            'matrix_monthly_fee' => 'Default monthly fee for payment calculations',
            'matrix_advance_payment_enabled' => 'Allow advance payments to cover multiple months',

            'finance_department_email' => 'Email address for finance department communications',
            'payment_grace_period_months' => 'Grace period before sending payment reminders',
            'enable_audit_logging' => 'Log all finance setting changes for audit purposes',
        ];

        return $descriptions[$key] ?? '';
    }

    /**
     * Get batch-specific counterpart payment start settings
     */
    /**
     * Get audit logs for finance settings changes
     */
    public function getAuditLogs()
    {
        try {
            // Check if audit logging is enabled
            $auditEnabled = \App\Models\FinanceSetting::get('enable_audit_logging', true);

            if (!$auditEnabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Audit logging is currently disabled. Enable it in General Settings to start logging changes.'
                ]);
            }

            // Read the Laravel log file and extract finance setting updates
            $logFile = storage_path('logs/laravel.log');

            if (!file_exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'logs' => [],
                    'message' => 'No log file found yet. Make some settings changes to generate logs.'
                ]);
            }

            $logs = [];
            $logContent = file_get_contents($logFile);

            // Parse log entries for finance setting updates
            preg_match_all('/\[([^\]]+)\] local\.INFO: Finance setting updated: ([^=]+) = ([^\s]+) ({[^}]+})/', $logContent, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $timestamp = $match[1];
                $settingKey = trim($match[2]);
                $newValue = trim($match[3]);

                // Parse the JSON data
                $jsonData = json_decode($match[4], true);

                $logs[] = [
                    'timestamp' => $timestamp,
                    'setting_key' => $settingKey,
                    'old_value' => $jsonData['old_value'] ?? 'N/A',
                    'new_value' => $jsonData['new_value'] ?? $newValue,
                    'user' => $jsonData['user'] ?? 'System'
                ];
            }

            // Sort by timestamp (newest first)
            usort($logs, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Limit to last 50 entries for performance
            $logs = array_slice($logs, 0, 50);

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total' => count($logs)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching audit logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch audit logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment history view
     * Students are sorted alphabetically by last name for consistent display
     */
    public function paymentHistory(Request $request)
    {
        // Get batches that actually exist in student_details table
        $existingBatchYears = \App\Models\StudentDetails::distinct()->pluck('batch')->filter();

        // Create batch records for any missing batch years
        foreach ($existingBatchYears as $batchYear) {
            Batch::firstOrCreate(
                ['batch_year' => $batchYear],
                ['total_due' => 0] // Default total_due
            );
        }

        // Get all batches that have students
        $batches = Batch::whereIn('batch_year', $existingBatchYears)->orderBy('batch_year', 'desc')->get();

        // Get all users with student role and their student details
        $students = User::where('user_role', 'student')
            ->with(['studentDetails.payments' => function ($query) {
                $query-> orderBY('created_at', 'desc');
            }])
            ->get()
            ->map(function ($user) {
                // Create a student-like object for compatibility with existing views
                 $studentDetails = $user->studentDetails;
                if ($studentDetails) {
                    $studentDetails->first_name = $user->user_fname;
                    $studentDetails->last_name = $user->user_lname;
                    $studentDetails->email = $user->user_email;
                    $studentDetails->batch_year = $studentDetails->batch;
                    $studentDetails->display_name = $user->user_lname . ', ' . $user->user_fname; // Format: "Last Name, First Name"
                }
                return $studentDetails;
            })
            ->filter() // Remove null values
            ->sortBy('last_name'); // Sort students alphabetically by last name (case-insensitive)

        return view('finance.payment-history', compact('batches', 'students'));
    }

    public function getPaymentHistory($studentId)
    {
        $payments = Payment::where('student_id', $studentId)
            ->where(function($q) {
                $q->whereIn('status', ['Approved', 'Added by Finance'])
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('verified_by')
                         ->where('status', '!=', 'Declined');
                  });
            })
            ->orderBy('payment_date', 'desc')
            ->get();

        // Fetch the student's details from StudentDetails
        $studentDetails = \App\Models\StudentDetails::with('batchInfo')->where('student_id', $studentId)->first();
        if (!$studentDetails) {
            abort(404, 'Student not found');
        }

        // Get the user information
        $user = User::where('user_id', $studentDetails->user_id)->first();

        // Create a student-like object for compatibility
        $student = $studentDetails;
        $student->first_name = $user->user_fname;
        $student->last_name = $user->user_lname;
        $student->email = $user->user_email;
        $student->batch_year = $studentDetails->batch;
        $student->display_name = $user->user_lname . ', ' . $user->user_fname; // Format: "Last Name, First Name"

        // Calculate the total paid (only approved or added by finance)
        $totalPaid = $payments->sum('amount');

        // Fetch the student's total due
        $totalDue = $studentDetails->batchInfo->total_due ?? 0;

        // Calculate the remaining balance
        $remainingBalance = max(0, $totalDue - $totalPaid);

        // Fetch all students for the edit form dropdown
        $students = User::where('user_role', 'student')
            ->with('studentDetails')
            ->get()
            ->map(function ($user) {
                $studentDetails = $user->studentDetails;
                if ($studentDetails) {
                    $studentDetails->first_name = $user->user_fname;
                    $studentDetails->last_name = $user->user_lname;
                    $studentDetails->display_name = $user->user_lname . ', ' . $user->user_fname; // Format: "Last Name, First Name"
                }
                return $studentDetails;
            })
            ->filter()
            ->sortBy('last_name'); // Sort students alphabetically by last name (case-insensitive)

        return view('finance.payment-history', compact('payments', 'totalPaid', 'totalDue', 'remainingBalance', 'student', 'students'));
    }

    public function downloadStudentPaymentHistory($studentId)
    {
        $payments = Payment::where('student_id', $studentId)
            ->where(function($q) {
                $q->whereIn('status', ['Approved', 'Added by Finance'])
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('verified_by')
                         ->where('status', '!=', 'Declined');
                  });
            })
            ->orderBy('payment_date', 'desc')
            ->get();

        // Fetch the student's details from StudentDetails
        $studentDetails = \App\Models\StudentDetails::with('batchInfo')->where('student_id', $studentId)->first();
        if (!$studentDetails) {
            abort(404, 'Student not found');
        }

        // Get user details for the student
        $user = User::where('user_id', $studentDetails->user_id)->first();
        if (!$user) {
            abort(404, 'User details not found');
        }

        // Calculate totals
        $totalPaid = $payments->sum('amount');
        $totalDue = $studentDetails->batchInfo ? $studentDetails->batchInfo->total_due : 0;
        $remainingBalance = $totalDue - $totalPaid;

        // Prepare student data
        $student = (object) [
            'student_id' => $studentDetails->student_id,
            'first_name' => $user->user_fname,
            'last_name' => $user->user_lname,
            'batch' => $studentDetails->batch,
            'total_paid' => $totalPaid,
            'total_due' => $totalDue,
            'remaining_balance' => $remainingBalance
        ];

        // Generate PDF
        $pdf = Pdf::loadView('finance.partials.student-payment-history-pdf', compact('payments', 'student'));

        $filename = 'payment_history_' . $studentDetails->student_id . '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function addPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string'
        ]);

        // Enforce payable quota before creating payment
        $studentDetailsCheck = \App\Models\StudentDetails::with('batchInfo')->where('student_id', $request->student_id)->first();
        if (!$studentDetailsCheck) {
            return redirect()->back()->with('error', 'Student details not found for the provided student ID.');
        }

        $totalDue = optional($studentDetailsCheck->batchInfo)->total_due ?? 0;
        $totalPaidSoFar = \App\Models\Payment::where('student_id', $request->student_id)
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->sum('amount');
        $remaining = max(0, $totalDue - $totalPaidSoFar);

        if ($remaining <= 0) {
            return redirect()->back()->with('error', 'Payment not allowed. The student has reached the payable amount (Fully Paid).');
        }
        if ($request->amount > $remaining) {
            return redirect()->back()->with('error', 'Payment not allowed. The amount exceeds the remaining balance of ₱' . number_format($remaining, 2) . '.');
        }

        // Create the payment record
        $payment = new Payment();
        $payment->student_id = $request->student_id;
        $payment->amount = $request->amount;
        $payment->payment_date = $request->payment_date;
        $payment->payment_mode = $request->payment_mode;
        
        // Determine role and status: Finance adds are recorded immediately; Cashier adds require finance verification
        $sessionUser = session('user');
        $localUser = $sessionUser ? User::where('user_email', $sessionUser['user_email'])->first() : null;
        $isCashier = $localUser && $localUser->user_role === 'cashier';

        if ($isCashier) {
            $payment->status = 'Cashier Verified';
            // Do not set verified_by here; final approval will set it
        } else {
            $payment->status = 'Added by Finance';
            $payment->verified_by = $sessionUser ? $sessionUser['user_id'] : null;
        }

        $payment->save();

        // Fetch the student associated with the payment
        $studentDetails = \App\Models\StudentDetails::where('student_id', $payment->student_id)->first();

        if (!$studentDetails) {
            return redirect()->back()->with('error', 'Student details not found for the provided student ID.');
        }

        $user = User::where('user_id', $studentDetails->user_id)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found for the student.');
        }

        // If cashier added the payment, send to finance for final approval and skip receipt generation
        if (isset($isCashier) && $isCashier) {
            // Check notification settings for dashboard and student account
            $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
            $dashboardNotificationsEnabled = $notificationSettings['dashboard'] ?? true;
            $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;

            // Notify finance users for final approval (dashboard notification)
            if ($dashboardNotificationsEnabled) {
                $financeUsers = User::where('user_role', 'finance')->get();
                foreach ($financeUsers as $financeUser) {
                    CustomNotification::create([
                        'user_id' => $financeUser->user_id,
                        'type' => 'payment_cashier_added',
                        'title' => 'Payment Added by Cashier',
                        'message' => 'Payment of ₱' . number_format($payment->amount, 2) . ' for ' . $user->user_fname . ' ' . $user->user_lname . ' has been added by cashier and requires final finance approval.',
                        'is_read' => 0
                    ]);
                }
            }

            // Notify student that payment is pending final approval (student account notification)
            if ($studentAccountNotificationsEnabled) {
                CustomNotification::create([
                    'user_id' => $user->user_id,
                    'type' => 'payment_cashier_added_pending',
                    'title' => 'Payment Recorded by Cashier (Pending Approval)',
                    'message' => 'Dear <strong>' . $user->user_fname . ' ' . $user->user_lname . '</strong>, <br>Your payment of <strong>₱' . number_format($payment->amount, 2) . '</strong> has been recorded by the cashier and is pending final finance approval.',
                ]);
            }

            return redirect()->back()->with('payment_added', true);
        }

        // Create receipts directory if it doesn't exist
        $receiptDir = storage_path('app/public/receipts');
        if (!file_exists($receiptDir)) {
            mkdir($receiptDir, 0755, true);
        }

        // Calculate which months this payment covers
        $paidMonths = $this->calculatePaidMonths($studentDetails, $payment->amount);
        $monthsText = $this->formatMonthsForNotification($paidMonths);

        // Generate the PDF receipt
        $pdf = Pdf::loadView('pdf.payment_receipt', ['payment' => $payment, 'student' => $studentDetails, 'user' => $user]);
        $filename = 'receipts/receipt_' . $payment->payment_id . '.pdf';
        $pdf->save(storage_path('app/public/' . $filename));

        // Check notification settings for all notification methods
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
        $emailNotificationsEnabled = $notificationSettings['email'] ?? true;
        $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;
        
        // Send email with receipt if user exists, has email, AND email notifications are enabled
        if ($user && $user->user_email && $emailNotificationsEnabled) {
            Mail::to($user->user_email)->send(new \App\Mail\ReceiptMail($payment, $pdf->output(), $monthsText));
        }

        // Store the notification in the custom table ONLY if student account notifications are enabled
        if ($user && $studentAccountNotificationsEnabled) {
            CustomNotification::create([
                'user_id' => $user->user_id,
                'type' => 'payment_receipt',
                'title' => 'Counterpart Payment',
                'message' => 'Dear <strong>' . $user->user_fname . ' ' . $user->user_lname . '</strong>, <br>
                    Your payment of <strong>₱' . number_format($payment->amount, 2) . '</strong> for <strong>' . $monthsText . '</strong> has been successfully processed and recorded. Click here to view your receipt. <br>
                    Let us know if you have any questions!<br><br>
                    Best regards,<br>
                    Finance Department',
                'receipt_path' => $filename,
            ]);
        }

        return redirect()->back()->with('payment_added', true);
    }

    public function updateBatch(Request $request)
    {
        $request->validate([
            'batch_year' => 'required|string',
            'total_due' => 'nullable|numeric|min:0',
            'payment_start_month' => 'nullable|integer|min:1|max:12',
            'payment_start_year' => 'nullable|integer|min:2020|max:2050',
            'enable_payment_start' => 'nullable|boolean',
        ]);

        $batch = Batch::where('batch_year', $request->batch_year)->first();
        $oldAmount = $batch->total_due;
        
        // Only update total_due if a new value is provided
        if ($request->filled('total_due')) {
            $batch->update(['total_due' => $request->total_due]);
        }
        
        // Handle payment start settings
        $batchYear = $request->batch_year;
        
        if ($request->boolean('enable_payment_start')) {
            // User wants to set payment start date
            if ($request->filled('payment_start_month') && $request->filled('payment_start_year')) {
                // Save payment start month
                \App\Models\FinanceSetting::set(
                    "batch_counterpart_payment_start_month_{$batchYear}",
                    $request->payment_start_month,
                    'integer',
                    "Starting month for Batch {$batchYear} counterpart payments (1-12)",
                    'matrix_settings'
                );
                
                // Save payment start year
                \App\Models\FinanceSetting::set(
                    "batch_counterpart_payment_start_year_{$batchYear}",
                    $request->payment_start_year,
                    'integer',
                    "Starting year for Batch {$batchYear} counterpart payments",
                    'matrix_settings'
                );
            }
        } else {
            // User doesn't want to set payment start date - remove any existing settings
            \App\Models\FinanceSetting::where('setting_key', "batch_counterpart_payment_start_month_{$batchYear}")->delete();
            \App\Models\FinanceSetting::where('setting_key', "batch_counterpart_payment_start_year_{$batchYear}")->delete();
        }

        // Check notification settings
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
        $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;
        
        // Get all students in this batch
        $students = User::where('user_role', 'student')
            ->whereHas('studentDetails', function ($query) use ($request) {
                $query->where('batch', $request->batch_year);
            })
            ->with('studentDetails')
            ->get();

        // Create notifications for each student ONLY if student account notifications are enabled
        if ($studentAccountNotificationsEnabled) {
            foreach ($students as $student) {
                CustomNotification::create([
                    'user_id' => $student->user_id,
                    'type' => 'batch_update',
                    'title' => 'Batch Payment Update',
                    'message' => 'Dear Students of Batch <strong>' . $request->batch_year . '</strong>,<br><br>' .
                        'Please be informed that the payable amount for Counterpart Payment for your batch has been updated.<br>' .
                        '<strong>Previous Amount:</strong> ₱' . number_format($oldAmount, 2) . '<br>' .
                        '<strong>New Amount:</strong> ₱' . number_format($request->total_due, 2) . '<br><br>' .
                        'Kindly review the updated details at your earliest convenience.<br><br>' .
                        'Should you have any questions or require further clarification, please do not hesitate to reach out to the finance office.<br><br>' .
                        'Best regards,<br>' .
                        'Finance Department',
                ]);
            }
        }

        $studentCount = $students->count();
        
        // Build appropriate message based on what was updated
        $messageParts = [];
        
        if ($request->filled('total_due')) {
            $messageParts[] = "Batch amount updated";
        }
        
        if ($request->boolean('enable_payment_start') && $request->filled('payment_start_month') && $request->filled('payment_start_year')) {
            $messageParts[] = "Payment start date set to " . date('F Y', mktime(0, 0, 0, $request->payment_start_month, 1, $request->payment_start_year));
        } elseif (!$request->boolean('enable_payment_start')) {
            $messageParts[] = "Payment start date removed";
        }
        
        $message = implode(", ", $messageParts) . " successfully!";
        
        if ($studentCount > 0 && $request->filled('total_due')) {
            $message .= " Notifications sent to {$studentCount} student(s).";
        }

        return redirect()->route('finance.financePayments')->with('success', $message);
    }
    
    /**
     * Get payment start settings for a specific batch
     */
    public function getBatchPaymentStartSettings($batchYear)
    {
        try {
            $startMonth = \App\Models\FinanceSetting::get("batch_counterpart_payment_start_month_{$batchYear}", '');
            $startYear = \App\Models\FinanceSetting::get("batch_counterpart_payment_start_year_{$batchYear}", '');

            // Also get the processed settings (what the generateMatrixData method would use)
            $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);

            return response()->json([
                'success' => true,
                'start_month' => $startMonth,
                'start_year' => $startYear,
                'debug_info' => [
                    'raw_batch_settings' => [
                        'start_month' => $startMonth,
                        'start_year' => $startYear
                    ],
                    'processed_batch_settings' => $paymentStartSettings,
                    'note' => 'Only batch-specific settings are used, no global fallback'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load batch payment start settings'
            ], 500);
        }
    }

    /**
     * Notifications view
     * Students are sorted alphabetically by last name for consistent display
     */
    public function notifications()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access notifications.');
        }

        // Find local user to get finance details
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || !in_array($localUser->user_role, ['finance', 'cashier'])) {
            return back()->with('error', 'Access denied. Finance or Cashier role required.');
        }

        // Get payments that need review based on user role
        $userRole = $localUser->user_role;
        
        if ($userRole === 'cashier') {
            // Cashiers see all payments except "Added by Finance" ones
            $pendingPayments = Payment::with(['student.user'])
                ->where('status', '!=', 'Added by Finance')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Finance sees all payments except "Added by Finance" ones
            $pendingPayments = Payment::with(['student.user'])
                ->where('status', '!=', 'Added by Finance')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get notifications for user
        $notifications = CustomNotification::where('user_id', $localUser->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Mark all notifications as read
        CustomNotification::where('user_id', $localUser->user_id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return view('finance.notifications', compact('pendingPayments', 'notifications'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:Approved,Declined',
        ]);

        $student = session('user');

        // Update the payment status
        $payment->update([
            'status' => $request->status,
            'verified_by' => $student ? $student['user_id'] : null,
            'verified_at' => now(),
        ]);

        // Fetch the student and user associated with the payment
        $studentDetails = \App\Models\StudentDetails::where('student_id', $payment->student_id)->first();

        if (!$studentDetails) {
            return redirect()->back()->with('error', 'Student details not found for the payment.');
        }

        $user = User::where('user_id', $studentDetails->user_id)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found for the student.');
        }

        if ($request->status === 'Approved') {
            // Create receipts directory if it doesn't exist
            $receiptDir = storage_path('app/public/receipts');
            if (!file_exists($receiptDir)) {
                mkdir($receiptDir, 0755, true);
            }

            // Generate the receipt
            $pdf = Pdf::loadView('pdf.payment_receipt', ['payment' => $payment, 'student' => $studentDetails, 'user' => $user]);
            $filename = 'receipts/receipt_' . $payment->payment_id . '.pdf';
            $pdf->save(storage_path('app/public/' . $filename));

            // Calculate which months this payment covers for approved payments
            $paidMonths = $this->calculatePaidMonths($studentDetails, $payment->amount);
            $monthsText = $this->formatMonthsForNotification($paidMonths);
            
            // Check notification settings for all notification methods
            $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
            $emailNotificationsEnabled = $notificationSettings['email'] ?? true;
            $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;

            // Send email with receipt if user exists, has email, AND email notifications are enabled
            if ($user && $user->user_email && $emailNotificationsEnabled) {
                Mail::to($user->user_email)->send(new \App\Mail\ReceiptMail($payment, $pdf->output(), $monthsText));
            }

            // Store the notification in the custom table ONLY if student account notifications are enabled
            if ($user && $studentAccountNotificationsEnabled) {
                CustomNotification::create([
                    'user_id' => $user->user_id,
                    'type' => 'payment_verification',
                    'title' => 'Counterpart Payment Approved and Recorded',
                    'message' => 'Dear <strong>' . $user->user_fname . ' ' . $user->user_lname . '</strong>, <br>
                     Your uploaded proof of payment has been reviewed and approved. Your payment amount of <strong>₱' . number_format($payment->amount, 2) . '</strong> for <strong>' . $monthsText . '</strong> has been successfully recorded. Click here to view your receipt. <br>
                    Let us know if you have any questions!<br><br>
                    Best regards,<br>
                    Finance Department',
                    'receipt_path' => $filename,
                ]);
            }

            return redirect()->back()->with('success', 'Payment added to ' . $user->user_fname . ' ' . $user->user_lname . ' successfully.');
        } elseif ($request->status === 'Declined') {
            // Update payment with remarks
            // Get the current user from session (since this subsystem uses session-based auth)
            $currentUser = session('user');
            $payment->update([
                'remarks' => $request->remarks,
                'status' => 'Declined',
                'verified_by' => $currentUser ? $currentUser['user_id'] : null,
                'verified_at' => now(),
            ]);

            if ($user) {
                $reason = $request->remarks;
                
                // Check notification settings for declined payments
                $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
                $emailNotificationsEnabled = $notificationSettings['email'] ?? true;
                $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;

                // Send student account notification only if enabled
                if ($studentAccountNotificationsEnabled) {
                    CustomNotification::create([
                        'user_id' => $user->user_id,  // Changed from $user->id to $user->user_id
                        'type' => 'payment_verification',
                        'title' => 'Uploaded Proof of Payment Declined',
                        'message' => 'Dear <strong>' . $user->user_fname . ' ' . $user->user_lname . '</strong>, <br>
                         We have reviewed your proof of payment of <strong>₱' . number_format($payment->amount, 2) . '</strong> for <strong>Counterpart Payment</strong>
                        and unfortunately, it has been declined for the following reason: <strong>' . e($reason) . '</strong>.<br>
                        To complete the verification process, kindly please resubmit a corrected proof at your earliest convenience.<br><br>
                        Best regards,<br>
                        Finance Department',
                    ]);
                }

                // Send email notification only if enabled
                if ($user->user_email && $emailNotificationsEnabled) {
                    Mail::to($user->user_email)->send(new \App\Mail\PaymentDeclinedMail($payment, $reason));
                }
            }
        }

        return redirect()->back()->with('success', 'A declined payment notification has been sent to ' . $user->user_fname . ' ' . $user->user_lname . ' successfully.');

        // return redirect()->route('finance.notifications')->with('success', 'Payment has been ' . strtolower($request->status) . '.');
    }

    /**
     * Finance dashboard view
     * Students are sorted alphabetically by last name for consistent display
     */
    public function dashboard()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access dashboard.');
        }

        $batches = Batch::all();

        // Get dynamic payment methods from database
        $dynamicPaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();

        // Always include Cash as a standard payment method, plus dynamic methods
        $paymentMethods = collect([
            (object)[
                'id' => 'cash',
                'name' => 'Cash',
                'account_name' => null,
                'account_number' => null,
                'is_standard' => true
            ]
        ])->merge($dynamicPaymentMethods);

        // If no dynamic payment methods exist, add some fallback options
        if ($dynamicPaymentMethods->isEmpty()) {
            $paymentMethods = $paymentMethods->merge([
                (object)['name' => 'GCash'],
                (object)['name' => 'Bank Transfer'],
            ]);
        }

        $students = User::where('user_role', 'student')
            ->with('studentDetails')
            ->get()
            ->map(function ($user) {
                $studentDetails = $user->studentDetails;
                if ($studentDetails) {
                    $studentDetails->first_name = $user->user_fname;
                    $studentDetails->last_name = $user->user_lname;
                    $studentDetails->email = $user->user_email;
                    $studentDetails->display_name = $user->user_lname . ', ' . $user->user_fname; // Format: "Last Name, First Name"
                }
                return $studentDetails;
            })
            ->filter()
            ->sortBy('last_name'); // Sort students alphabetically by last name (case-insensitive)
        return view('finance.financeDashboard', compact('batches', 'students', 'student', 'paymentMethods'));
    }

    public function getMonthlyPayments(Request $request)
    {
        $query = Payment::query();

        $query->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply payment mode filter (dashboard version - combines all accounts of same type)
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;

            // For dashboard, use case-insensitive payment mode filter (combines all accounts)
            // This ensures all GCash/gcash accounts are combined, all PayMaya accounts are combined, etc.
            $query->whereRaw('LOWER(payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply year filter
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Apply month filter
        if ($request->has('month') && $request->month) {
            $query->whereMonth('payment_date', $request->month);
        }

        $payments = $query->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->get();

        return response()->json($payments);
    }

    public function monthlyPayments(Request $request)
    {
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply payment mode filter (dashboard version - combines all accounts of same type)
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;

            // For dashboard, use case-insensitive payment mode filter (combines all accounts)
            $query->whereRaw('LOWER(payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply year filter
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Apply month filter
        if ($request->has('month') && $request->month) {
            $query->whereMonth('payment_date', $request->month);
        }

        $data = $query->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->get();

        return response()->json($data);
    }

    public function getYearlyPayments(Request $request)
    {
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply payment mode filter (dashboard version - combines all accounts of same type)
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;

            // For dashboard, use case-insensitive payment mode filter (combines all accounts)
            $query->whereRaw('LOWER(payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply year filter
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Apply month filter
        if ($request->has('month') && $request->month) {
            $query->whereMonth('payment_date', $request->month);
        }

        $payments = $query->selectRaw('YEAR(payment_date) as year, SUM(amount) as total')
            ->groupByRaw('YEAR(payment_date)')
            ->orderByRaw('YEAR(payment_date)')
            ->get();

        return response()->json($payments);
    }

    public function getBatchDifferentiatedMonthlyPayments(Request $request)
    {
        $query = Payment::query()
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance']);

        // Apply payment mode filter
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;
            $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('payments.payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('payments.payment_date', '<=', $request->end_date);
        }

        // Apply year filter if no date range
        if (!$request->has('start_date') && !$request->has('end_date')) {
            if ($request->has('year') && $request->year) {
                $query->whereYear('payments.payment_date', $request->year);
            } else {
                $query->whereYear('payments.payment_date', now()->year);
            }
        }

        $data = $query->selectRaw('
                student_details.batch as batch_year,
                YEAR(payments.payment_date) as year,
                MONTH(payments.payment_date) as month,
                SUM(payments.amount) as total
            ')
            ->groupByRaw('student_details.batch, YEAR(payments.payment_date), MONTH(payments.payment_date)')
            ->orderByRaw('student_details.batch, YEAR(payments.payment_date), MONTH(payments.payment_date)')
            ->get();

        return response()->json($data);
    }

    public function getBatchDifferentiatedYearlyPayments(Request $request)
    {
        $query = Payment::query()
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance']);

        // Apply payment mode filter
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;
            $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('payments.payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('payments.payment_date', '<=', $request->end_date);
        }

        $data = $query->selectRaw('
                student_details.batch as batch_year,
                YEAR(payments.payment_date) as year,
                SUM(payments.amount) as total
            ')
            ->groupByRaw('student_details.batch, YEAR(payments.payment_date)')
            ->orderByRaw('student_details.batch, YEAR(payments.payment_date)')
            ->get();

        return response()->json($data);
    }

    public function getYearlyTrendsByMonth(Request $request)
    {
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter if specified
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply payment mode filter
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;
            $query->whereRaw('LOWER(payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply date range filter if specified
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $data = $query->selectRaw('
                YEAR(payment_date) as year,
                MONTH(payment_date) as month,
                SUM(amount) as total
            ')
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->get();

        return response()->json($data);
    }

    public function getSummaryData(Request $request)
    {
        // Base query with approved payments
        $baseQuery = Payment::whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter to base query
        if ($request->has('batch_year') && $request->batch_year) {
            $baseQuery->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply payment mode filter to base query (dashboard version - combines all accounts of same type)
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;

            // For dashboard, use case-insensitive payment mode filter (combines all accounts)
            $baseQuery->whereRaw('LOWER(payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Monthly collected (current month or filtered month)
        $monthlyQuery = clone $baseQuery;
        if ($request->has('month') && $request->month) {
            $monthlyQuery->whereMonth('payment_date', $request->month);
        } else {
            $monthlyQuery->whereMonth('payment_date', now()->month);
        }
        if ($request->has('year') && $request->year) {
            $monthlyQuery->whereYear('payment_date', $request->year);
        } else {
            $monthlyQuery->whereYear('payment_date', now()->year);
        }
        $monthlyCollected = $monthlyQuery->sum('amount');

        // Yearly collected (current year or filtered year)
        $yearlyQuery = clone $baseQuery;
        if ($request->has('year') && $request->year) {
            $yearlyQuery->whereYear('payment_date', $request->year);
        } else {
            $yearlyQuery->whereYear('payment_date', now()->year);
        }
        $yearlyCollected = $yearlyQuery->sum('amount');

        // Overall collected (with filters applied)
        $overallCollected = (clone $baseQuery)->sum('amount');

        return response()->json([
            'monthly_collected' => $monthlyCollected,
            'yearly_collected' => $yearlyCollected,
            'overall_collected' => $overallCollected,
        ]);
    }

    public function getTargetVsAccomplishment(Request $request)
    {
        // Get target amounts from batch settings with active student counts per month
        // Dynamic target calculation: Past months keep original targets, future months use current active count
        $currentDate = now();
        
        // Get actual collections
        $collectionsQuery = Payment::query()
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance']);

        // Apply year filter if specified
        if ($request->has('year') && $request->year) {
            $collectionsQuery->whereYear('payments.payment_date', $request->year);
        }

        $collections = $collectionsQuery->selectRaw('
                student_details.batch as batch_year,
                YEAR(payments.payment_date) as year,
                MONTH(payments.payment_date) as month,
                SUM(payments.amount) as actual_collection
            ')
            ->groupByRaw('student_details.batch, YEAR(payments.payment_date), MONTH(payments.payment_date)')
            ->get();

        // Get all batches and their students
        $batches = Batch::with(['studentDetails.user'])->get();

        // Combine targets and collections
        $result = [];
        
        // Generate data for 24 months (previous year + current year)
        $currentYear = now()->year;
        $startYear = $currentYear - 1;
        $endYear = $currentYear;
        
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $monthDate = \Carbon\Carbon::create($year, $month, 1);
                
                // Initialize monthly totals
                $totalMonthlyTarget = 0;
                $totalActiveStudents = 0;
                $totalStudents = 0;
                
                // Calculate combined target from all batches for this month
                foreach ($batches as $batch) {
                    // Count active students for this month with dynamic adjustment
                    $activeStudentCount = 0;
                    $totalStudentCount = $batch->studentDetails->count();
                    
                    foreach ($batch->studentDetails as $studentDetail) {
                        $user = $studentDetail->user;
                        
                        if (!$user) {
                            continue; // Skip if no user record
                        }
                        
                        // Dynamic student counting based on kick-out logic
                        // When students are kicked out, they are erased from records for that month onwards
                        if ($monthDate->isPast()) {
                            // For PAST months: Count student as active if they were there at that time
                            // Even if they're kicked out now, they count for past months (before kick-out)
                            // This preserves historical accuracy for completed months
                            $wasActiveInPast = ($user->status !== 'inactive');
                            $activeStudentCount += $wasActiveInPast ? 1 : 0;
                            
                        } else {
                            // For CURRENT and FUTURE months: Only count currently active students
                            // Kicked out students are completely erased from records from kick-out month onwards
                            // This creates immediate dynamic adjustment where targets reflect actual student count
                            $isActive = ($user->status === 'active');
                            $activeStudentCount += $isActive ? 1 : 0;
                        }
                    }
                    
                    // Calculate monthly target for this batch based on active students
                    $batchMonthlyTarget = 0;
                    if ($activeStudentCount > 0) {
                        $batchMonthlyTarget = ($batch->total_due * $activeStudentCount) / 12;
                    }
                    
                    // Add to monthly totals
                    $totalMonthlyTarget += $batchMonthlyTarget;
                    $totalActiveStudents += $activeStudentCount;
                    $totalStudents += $totalStudentCount;
                }
                
                // Get combined actual collections for this month (all batches)
                $monthlyCollection = $collections->where('year', $year)
                    ->where('month', $month)
                    ->sum('actual_collection');
                
                // Create single entry per month with combined totals
                $result[] = [
                    'batch_year' => 'ALL_BATCHES', // Indicator for combined data
                    'year' => $year,
                    'month' => $month,
                    'target_amount' => $totalMonthlyTarget,
                    'actual_collection' => $monthlyCollection,
                    'active_student_count' => $totalActiveStudents,
                    'total_student_count' => $totalStudents,
                    'total_combined_target' => $totalMonthlyTarget * 12 // Annual target
                ];
            }
        }

        return response()->json($result);
    }

    public function getAvailableYears()
    {
        // Get years from both payments and batches
        $paymentYears = Payment::selectRaw('YEAR(payment_date) as year')
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->distinct()
            ->pluck('year');

        $batchYears = Batch::selectRaw('batch_year as year')
            ->distinct()
            ->pluck('year');

        // Combine and get unique years
        $allYears = $paymentYears->merge($batchYears)->unique()->sort()->values();

        return response()->json($allYears);
    }

    public function getPaymentModeBreakdown(Request $request)
    {
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply year filter
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Apply month filter
        if ($request->has('month') && $request->month) {
            $query->whereMonth('payment_date', $request->month);
        }

        $paymentModes = $query->selectRaw('payment_mode, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_mode')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json($paymentModes);
    }

    public function getBatchBreakdown(Request $request)
    {
        $query = Payment::query()
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance']);

        // Apply payment mode filter (dashboard version - combines all accounts of same type)
        if ($request->has('payment_mode') && $request->payment_mode) {
            $paymentModeFilter = $request->payment_mode;

            // For dashboard, use case-insensitive payment mode filter (combines all accounts)
            $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentModeFilter]);
        }

        // Apply year filter
        if ($request->has('year') && $request->year) {
            $query->whereYear('payments.payment_date', $request->year);
        }

        // Apply month filter
        if ($request->has('month') && $request->month) {
            $query->whereMonth('payments.payment_date', $request->month);
        }

        $batches = $query->selectRaw('student_details.batch, SUM(payments.amount) as total, COUNT(*) as count')
            ->groupBy('student_details.batch')
            ->orderBy('student_details.batch')
            ->get();

        return response()->json($batches);
    }

    public function getPaymentModeOptions(Request $request)
    {
        // Get unique payment modes with their reference numbers/identifiers
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter if specified
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply year filter if specified
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Get unique payment modes with their identifiers
        $paymentModes = $query->selectRaw('
                payment_mode,
                reference_number,
                COUNT(*) as usage_count,
                SUM(amount) as total_amount
            ')
            ->whereNotNull('payment_mode')
            ->groupBy('payment_mode', 'reference_number')
            ->orderBy('payment_mode')
            ->orderBy('usage_count', 'desc')
            ->get();

        // Format the payment modes for display
        $formattedModes = [];

        foreach ($paymentModes as $mode) {
            $displayName = $mode->payment_mode;
            $filterValue = $mode->payment_mode;

            // If there's a reference number, add identifier
            if ($mode->reference_number) {
                // Show last 3 digits for identification
                $identifier = '***' . substr($mode->reference_number, -3);
                $displayName = $mode->payment_mode . ' (' . $identifier . ')';
                $filterValue = $mode->payment_mode . '|' . $mode->reference_number;
            }

            $formattedModes[] = [
                'display_name' => $displayName,
                'filter_value' => $filterValue,
                'payment_mode' => $mode->payment_mode,
                'reference_number' => $mode->reference_number,
                'usage_count' => $mode->usage_count,
                'total_amount' => $mode->total_amount
            ];
        }

        return response()->json($formattedModes);
    }

    public function getDashboardPaymentModes(Request $request)
    {
        // Get unique payment modes for dashboard (grouped by type, no account details)
        $query = Payment::query()->whereIn('status', ['Approved', 'Added by Finance']);

        // Apply batch filter if specified
        if ($request->has('batch_year') && $request->batch_year) {
            $query->whereHas('studentDetails', function ($q) use ($request) {
                $q->where('batch', $request->batch_year);
            });
        }

        // Apply year filter if specified
        if ($request->has('year') && $request->year) {
            $query->whereYear('payment_date', $request->year);
        }

        // Get payment modes from actual payments (grouped by type to combine duplicates)
        // Use case-insensitive grouping to combine "GCash" and "gcash" etc.
        $paymentModes = $query->selectRaw('
                LOWER(payment_mode) as payment_mode_lower,
                payment_mode,
                COUNT(*) as usage_count,
                SUM(amount) as total_amount
            ')
            ->whereNotNull('payment_mode')
            ->groupByRaw('LOWER(payment_mode), payment_mode')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->groupBy('payment_mode_lower')
            ->map(function ($group) {
                // For each group of same payment mode (case-insensitive), combine the stats
                return (object)[
                    'payment_mode' => $group->first()->payment_mode, // Use the first occurrence's capitalization
                    'usage_count' => $group->sum('usage_count'),
                    'total_amount' => $group->sum('total_amount')
                ];
            })
            ->sortByDesc('total_amount')
            ->values();

        // Get all available payment methods from payment_methods table
        $availablePaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();

        // Create a collection to store unique payment modes
        $dashboardModes = collect();
        $addedModes = []; // Store lowercase versions for case-insensitive comparison
        $modeMapping = []; // Map lowercase to actual display name

        // Always ensure Cash is available as a standard payment method
        $standardPaymentMethods = collect([
            (object)[
                'name' => 'Cash',
                'is_standard' => true
            ]
        ]);

        // First, add payment modes from actual payments (these have usage data)
        foreach ($paymentModes as $mode) {
            $modeName = $mode->payment_mode;
            $lowerModeName = strtolower($modeName);

            if (!in_array($lowerModeName, $addedModes)) {
                $dashboardModes->push([
                    'payment_mode' => $modeName,
                    'display_name' => ucfirst(strtolower($modeName)), // Normalize capitalization
                    'usage_count' => $mode->usage_count,
                    'total_amount' => $mode->total_amount,
                    'has_usage' => true
                ]);
                $addedModes[] = $lowerModeName;
                $modeMapping[$lowerModeName] = $modeName;
            }
        }

        // First, add standard payment methods (like Cash)
        foreach ($standardPaymentMethods as $method) {
            $methodName = $method->name;
            $lowerMethodName = strtolower($methodName);

            if (!in_array($lowerMethodName, $addedModes)) {
                $dashboardModes->push([
                    'payment_mode' => $methodName,
                    'display_name' => $methodName,
                    'usage_count' => 0,
                    'total_amount' => 0,
                    'has_usage' => false,
                    'is_standard' => true
                ]);
                $addedModes[] = $lowerMethodName;
                $modeMapping[$lowerMethodName] = $methodName;
            }
        }

        // Then, add any payment methods that haven't been used yet but are available
        foreach ($availablePaymentMethods as $method) {
            $methodName = $method->name;
            $lowerMethodName = strtolower($methodName);

            if (!in_array($lowerMethodName, $addedModes)) {
                $dashboardModes->push([
                    'payment_mode' => $methodName,
                    'display_name' => $methodName, // Keep original capitalization from payment_methods table
                    'usage_count' => 0,
                    'total_amount' => 0,
                    'has_usage' => false,
                    'is_standard' => false
                ]);
                $addedModes[] = $lowerMethodName;
                $modeMapping[$lowerMethodName] = $methodName;
            }
        }

        // Sort by usage (used payment modes first, then by total amount, then alphabetically)
        $sortedModes = $dashboardModes->sortBy([
            ['has_usage', 'desc'],
            ['total_amount', 'desc'],
            ['display_name', 'asc']
        ])->values();

        return response()->json($sortedModes);
    }

    public function editPayment(Request $request, Payment $payment)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'payment_mode' => 'required|in:cash,gcash,bank_transfer',
                'reference_number' => 'nullable|string'
            ]);

            $payment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePayment($id, Request $request)
    {
        $payment = Payment::findOrFail($id);
        $payment->update([
            'amount' => $request->amount
        ]);

        return response()->json(['success' => true]);
    }

    public function deletePayment($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateReport(Request $request)
    {
        $reportType = $request->input('report_type');
        $batchYear = $request->input('batch_year');
        $paymentMode = $request->input('payment_mode'); // Add payment mode filter
        $year = $request->input('year');
        $month = $request->input('month');
        $monthStart = $request->input('month_start');
        $yearStart = $request->input('year_start');
        $monthEnd = $request->input('month_end');
        $yearEnd = $request->input('year_end');

        // New parameters for student month range
        $studentMonthFrom = $request->input('student_month_from');
        $studentYearFrom = $request->input('student_year_from');
        $studentMonthTo = $request->input('student_month_to');
        $studentYearTo = $request->input('student_year_to');

    // Payment mode filter (e.g. 'GCash', 'UNION BANK', 'Cash')
    $paymentMode = $request->input('payment_mode');

                    if ($reportType === 'total_paid_per_student') {
                // Get batch payable amounts for calculations
                $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

                // Base query for students with their payments - use INNER JOIN to only show students who actually paid
                $query = \App\Models\StudentDetails::with(['batch', 'user'])
                    ->join('payments', function($join) {
                        $join->on('student_details.student_id', '=', 'payments.student_id')
                             ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                    })
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->selectRaw('
                        student_details.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        SUM(payments.amount) as total_paid,
                        MAX(payments.payment_date) as last_payment_date,
                        GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                    ')
                    ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply payment mode filter so downloaded monthly transactions respect selected mode
            if (!empty($paymentMode)) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply payment mode filter for base query (when no specific date join was rebuilt)
            if (!empty($paymentMode)) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply payment mode filter
            if (!empty($paymentMode)) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply date filters - when date filters are applied, we only want students who actually paid
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter - rebuild query with INNER JOIN to get only students who paid
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();

                $query = \App\Models\StudentDetails::query()
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->join('payments', function($join) use ($fromDate, $toDate, $paymentMode) {
                        $join->on('student_details.student_id', '=', 'payments.student_id')
                             ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                             ->whereBetween('payments.payment_date', [$fromDate, $toDate]);

                        // Apply payment mode filter in join if specified
                        if ($paymentMode) {
                            $join->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                        }
                    })
                    ->selectRaw('
                        student_details.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        SUM(payments.amount) as total_paid,
                        MAX(payments.payment_date) as last_payment_date,
                        GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                    ')
                    ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter - rebuild query with INNER JOIN to get only students who paid
                $query = \App\Models\StudentDetails::query()
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->join('payments', function($join) use ($month, $year, $paymentMode) {
                        $join->on('student_details.student_id', '=', 'payments.student_id')
                             ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                             ->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', $year);

                        // Apply payment mode filter in join if specified
                        if ($paymentMode) {
                            $join->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                        }
                    })
                    ->selectRaw('
                        student_details.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        SUM(payments.amount) as total_paid,
                        MAX(payments.payment_date) as last_payment_date,
                        GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                    ')
                    ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } else {
                $dateDescription = 'All Time';
            }

            $data = $query->get();

            // Fix remaining balance calculation for each student
            foreach ($data as $row) {
                $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                $totalPaid = $row->total_paid ?? 0;

                // Ensure remaining balance is calculated correctly
                $remaining = $payable - $totalPaid;

                // Add calculated fields to the row
                $row->payable_amount = $payable;

                // Don't use max(0, $remaining) - show actual remaining balance even if negative
                $row->remaining_balance = $remaining;

                // Track overpayment separately
                if ($remaining < 0) {
                    $row->overpaid_amount = abs($remaining);
                } else {
                    $row->overpaid_amount = 0;
                }


            }

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "This report provides a detailed overview of $batchDescription students who have completed payments to date.";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "This report provides a detailed overview of $batchDescription students who have completed payments from $dateDescription.";
                } else {
                    $description = "This report provides a detailed overview of $batchDescription students who have completed payments for $dateDescription.";
                }
            }

            // Calculate payment mode breakdown for consistent UI
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            // Apply same filters as main query
            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if ($paymentMode) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $paymentModeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
            } elseif ($month && $year) {
                $paymentModeQuery->whereMonth('payments.payment_date', $month)
                               ->whereYear('payments.payment_date', $year);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            // Build per-student per-payment-mode totals so the view can render each method as its own row
            $perTypeQuery = Payment::selectRaw('
                    payments.student_id,
                    pnph_users.user_fname as first_name,
                    pnph_users.user_lname as last_name,
                    student_details.batch as batch_year,
                    payments.payment_mode,
                    SUM(payments.amount) as total_by_mode,
                    MAX(payments.payment_date) as last_payment_date
                ')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                ->groupBy('payments.student_id', 'payments.payment_mode', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

            if ($batchYear) {
                $perTypeQuery->where('student_details.batch', $batchYear);
            }

            if (isset($fromDate) && isset($toDate)) {
                $perTypeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
            } elseif (isset($month) && isset($year) && $month && $year) {
                $perTypeQuery->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', $year);
            }

            if (!empty($paymentMode)) {
                $perTypeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            $perTypeData = $perTypeQuery->orderBy('payments.student_id')->orderBy('payments.payment_mode')->get();

            $html = view('finance.partials.report', compact('data', 'reportType', 'batchPayableAmounts', 'description', 'paymentModeBreakdown', 'perTypeData'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        if ($reportType === 'total_paid_per_year') {
            // Base query for batch payments
            $query = Payment::selectRaw('student_details.batch as batch_year, SUM(amount) as total_paid')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->whereIn('payments.status', ['Approved', 'Added by Finance']);

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply payment mode filter for total_paid_per_year
            if (!empty($paymentMode)) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply payment mode filter
            if (!empty($paymentMode)) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply payment mode filter
            if ($paymentMode) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply date filters
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $query->whereBetween('payments.payment_date', [$fromDate, $toDate]);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter
            $query->whereMonth('payments.payment_date', $month)
                ->whereYear('payments.payment_date', $year);
            if (!empty($paymentMode)) {
              $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } else {
                $dateDescription = 'All Time';
            }

            $query->groupBy('student_details.batch');
            $data = $query->get();

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "Total amount paid of $batchDescription";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "Total amount paid of $batchDescription between $dateDescription";
                } else {
                    $description = "Total amount paid of $batchDescription for the month of $dateDescription";
                }
            }
            // Build per-student per-payment-mode totals for exports/view consistency
            $perTypeQuery = Payment::selectRaw('
                    payments.student_id,
                    pnph_users.user_fname as first_name,
                    pnph_users.user_lname as last_name,
                    student_details.batch as batch_year,
                    payments.payment_mode,
                    SUM(payments.amount) as total_by_mode,
                    MAX(payments.payment_date) as last_payment_date
                ')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                ->groupBy('payments.student_id', 'payments.payment_mode', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

            if ($batchYear) {
                $perTypeQuery->where('student_details.batch', $batchYear);
            }

            if (isset($fromDate) && isset($toDate)) {
                $perTypeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
            } elseif (isset($month) && isset($year) && $month && $year) {
                $perTypeQuery->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', $year);
            }

            if (!empty($paymentMode)) {
                $perTypeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            $perTypeData = $perTypeQuery->orderBy('payments.student_id')->orderBy('payments.payment_mode')->get();

            // Initialize required variables for the template
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // Calculate payment mode breakdown for consistent UI
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            // Apply same filters as main query
            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if ($paymentMode) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $paymentModeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
            } elseif ($month && $year) {
                $paymentModeQuery->whereMonth('payments.payment_date', $month)
                               ->whereYear('payments.payment_date', $year);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            $html = view('finance.partials.report', compact('data', 'reportType', 'description', 'batchPayableAmounts', 'paymentModeBreakdown'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        if ($reportType === 'per_year') {
            // Get year range parameters
            $yearFrom = $request->input('year_from');
            $yearTo = $request->input('year_to');

            // Optional month within a single year
            $perYearMonth = $request->input('month');
            // Optional month within a single year
            $perYearMonth = $request->input('month');

            // Default to current year if no year range specified
            if (!$yearFrom && !$yearTo) {
                $yearFrom = $yearTo = date('Y');
            } elseif ($yearFrom && !$yearTo) {
                $yearTo = $yearFrom;
            } elseif (!$yearFrom && $yearTo) {
                $yearFrom = $yearTo;
            }

            
            $singleYear = ($yearFrom == $yearTo);

            if ($singleYear) {
                
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as payment_year,
                        MONTH(payments.payment_date) as payment_month,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }
                // If user selected a specific month for the single year view, filter by it
                if (!empty($perYearMonth) && is_numeric($perYearMonth)) {
                    $query->whereMonth('payments.payment_date', intval($perYearMonth));
                }
            } else {
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as payment_year,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }
            }

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply year range filter
            if ($yearFrom == $yearTo) {
                $query->whereYear('payments.payment_date', $yearFrom);
                $dateDescription = $yearFrom;
            } else {
                $query->whereBetween(\DB::raw('YEAR(payments.payment_date)'), [$yearFrom, $yearTo]);
                $dateDescription = "$yearFrom - $yearTo";
            }

        if ($singleYear) {
            $query->groupBy('student_details.batch', 'payment_year', 'payment_month')
                ->orderBy('student_details.batch')
                ->orderBy('payment_year')
                ->orderBy('payment_month');
        } else {
            $query->groupBy('student_details.batch', 'payment_year')
                ->orderBy('student_details.batch')
                ->orderBy('payment_year');
        }

            $data = $query->get();

            // Create description
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            $description = "Total amount collected from $batchDescription for year(s) $dateDescription";

            // Get batch payable amounts for reference
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();
            // Calculate payment mode breakdown for consistent PDF using same filters
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if (!empty($paymentMode)) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($singleYear) {
                $paymentModeQuery->whereYear('payments.payment_date', $yearFrom);
                if (!empty($perYearMonth) && is_numeric($perYearMonth)) {
                    $paymentModeQuery->whereMonth('payments.payment_date', intval($perYearMonth));
                }
            } else {
                $paymentModeQuery->whereBetween(\DB::raw('YEAR(payments.payment_date)'), [$yearFrom, $yearTo]);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();
            // Calculate payment mode breakdown for consistent UI using same filters as main query
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if (!empty($paymentMode)) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            if ($singleYear) {
                // single-year view: optionally filter by month
                $paymentModeQuery->whereYear('payments.payment_date', $yearFrom);
                if (!empty($perYearMonth) && is_numeric($perYearMonth)) {
                    $paymentModeQuery->whereMonth('payments.payment_date', intval($perYearMonth));
                }
            } else {
                // year range
                $paymentModeQuery->whereBetween(\DB::raw('YEAR(payments.payment_date)'), [$yearFrom, $yearTo]);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            $html = view('finance.partials.report', compact('data', 'reportType', 'description', 'batchPayableAmounts', 'paymentModeBreakdown'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        if ($reportType === 'total_paid_per_month') {
            // Get batch payable amounts for calculations
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // Base query for daily transaction history - show only students who paid
            $query = Payment::selectRaw('
                    payments.payment_date,
                    student_details.student_id,
                    pnph_users.user_fname as first_name,
                    pnph_users.user_lname as last_name,
                    student_details.batch as batch_year,
                    payments.amount as total_paid,
                    payments.payment_mode
                ')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->whereIn('payments.status', ['Approved', 'Added by Finance']);

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply payment mode filter
            if ($paymentMode) {
                $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }

            // Apply date filters
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $query->whereBetween('payments.payment_date', [$fromDate, $toDate]);

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter
                $query->whereMonth('payments.payment_date', $month)
                      ->whereYear('payments.payment_date', $year);

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } elseif ($year) {
                // Single year filter
                $query->whereYear('payments.payment_date', $year);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }
                $dateDescription = "Year $year";
            } else {
                // Default to current year
                $currentYear = date('Y');
                $query->whereYear('payments.payment_date', $currentYear);
                $dateDescription = "Year $currentYear";
            }

            $query->orderBy('payments.payment_date')->orderBy('student_details.student_id');
            $data = $query->get();

            // Calculate remaining balance for each transaction
            foreach ($data as $row) {
                $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                
                // Get total paid by this student up to this payment date
                $totalPaidUpToDate = Payment::where('student_id', $row->student_id)
                    ->whereIn('status', ['Approved', 'Added by Finance'])
                    ->where('payment_date', '<=', $row->payment_date)
                    ->sum('amount');
                
                $remaining = $payable - $totalPaidUpToDate;
                
                // Add calculated fields to the row
                $row->payable_amount = $payable;
                $row->remaining_balance = $remaining;
            }

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments to date.";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments from $dateDescription.";
                } else {
                    $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments for $dateDescription.";
                }
            }

            // Calculate payment mode breakdown for consistent UI
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            // Apply same filters as main query
            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if ($paymentMode) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $paymentModeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
            } elseif ($month && $year) {
                $paymentModeQuery->whereMonth('payments.payment_date', $month)
                               ->whereYear('payments.payment_date', $year);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            $html = view('finance.partials.report', compact('data', 'reportType', 'batchPayableAmounts', 'description', 'paymentModeBreakdown'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        if ($reportType === 'total_paid_per_year') {
            // Get batch payable amounts for calculations
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // Check if user wants aggregated view (no specific filters = show summary)
            $showAggregated = !$batchYear && !$year;

            if ($showAggregated) {
                // Show aggregated data by batch and year
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as year,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                    ->groupBy('student_details.batch', \DB::raw('YEAR(payments.payment_date)'))
                    ->orderBy('student_details.batch')
                    ->orderBy('year');

                $data = $query->get();
            } else {
                // Show individual student data
                if ($year) {
                    // Use INNER JOIN to show only students who paid in specific year
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->join('payments', function($join) use ($year) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                                 ->whereYear('payments.payment_date', $year);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            SUM(payments.amount) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                } else {
                    // Use LEFT JOIN to show all students
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->leftJoin('payments', function($join) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            COALESCE(SUM(payments.amount), 0) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                }

                // Apply batch filter
                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $data = $query->get();
            }

            // Fix remaining balance calculation only for individual student data
            if (!$showAggregated) {
                foreach ($data as $row) {
                    $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                    $totalPaid = $row->total_paid ?? 0;

                    // Ensure remaining balance is calculated correctly
                    $remaining = $payable - $totalPaid;

                    // Add calculated fields to the row
                    $row->payable_amount = $payable;

                    // Don't use max(0, $remaining) - show actual remaining balance even if negative
                    $row->remaining_balance = $remaining;

                    // Track overpayment separately
                    if ($remaining < 0) {
                        $row->overpaid_amount = abs($remaining);
                    } else {
                        $row->overpaid_amount = 0;
                    }
                }
            }

            // Create description based on filters and data type
            if ($showAggregated) {
                $description = "Payment totals grouped by class batch and year (aggregated view)";
            } else {
                $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
                if ($year) {
                    $description = "$batchDescription students who made payments in year $year (only students with payments shown)";
                } else {
                    $description = "$batchDescription students payment summary (all students including those with zero payments)";
                }
            }

            // Calculate payment mode breakdown for consistent UI
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            // Apply same filters as main query
            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if ($paymentMode) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($year) {
                $paymentModeQuery->whereYear('payments.payment_date', $year);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            $html = view('finance.partials.report', compact('data', 'reportType', 'batchPayableAmounts', 'description', 'paymentModeBreakdown'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        if ($reportType === 'total_paid_per_batch_year') {
            // Get batch payable amounts for calculations
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // For Per Batch Year report, use single year filter (simplified)
            // Check if user wants aggregated view (no specific filters = show summary)
            $showAggregated = !$batchYear && !$year;

            if ($showAggregated) {
                // Show aggregated data by batch and year
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as year,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                    ->groupBy('student_details.batch', \DB::raw('YEAR(payments.payment_date)'))
                    ->orderBy('student_details.batch')
                    ->orderBy('year');

                $data = $query->get();
            } else {
                // Show individual student data
                if ($year) {
                    // Use INNER JOIN to show only students who paid in specific year
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->join('payments', function($join) use ($year) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                                 ->whereYear('payments.payment_date', $year);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            SUM(payments.amount) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                } else {
                    // Use LEFT JOIN to show all students
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->leftJoin('payments', function($join) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            COALESCE(SUM(payments.amount), 0) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                }

                // Apply batch filter
                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                $data = $query->get();
            }

            // Fix remaining balance calculation only for individual student data
            if (!$showAggregated) {
                foreach ($data as $row) {
                    $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                    $totalPaid = $row->total_paid ?? 0;

                    // Ensure remaining balance is calculated correctly
                    $remaining = $payable - $totalPaid;

                    // Add calculated fields to the row
                    $row->payable_amount = $payable;

                    // Don't use max(0, $remaining) - show actual remaining balance even if negative
                    $row->remaining_balance = $remaining;

                    // Track overpayment separately
                    if ($remaining < 0) {
                        $row->overpaid_amount = abs($remaining);
                    } else {
                        $row->overpaid_amount = 0;
                    }
                }
            }

            // Create description based on filters and data type
            if ($showAggregated) {
                $description = "Payment totals grouped by class batch and year (aggregated view)";
            } else {
                $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
                if ($year) {
                    $description = "$batchDescription students who made payments in year $year (only students with payments shown)";
                } else {
                    $description = "$batchDescription students payment summary (all students including those with zero payments)";
                }
            }

            // Calculate payment mode breakdown for consistent UI
            $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

            // Apply same filters as main query
            if ($batchYear) {
                $paymentModeQuery->where('student_details.batch', $batchYear);
            }
            if ($paymentMode) {
                $paymentModeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
            }
            if ($year) {
                $paymentModeQuery->whereYear('payments.payment_date', $year);
            }

            $paymentModeBreakdown = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
                ->groupBy('payment_mode')
                ->pluck('total', 'payment_mode')
                ->toArray();

            $html = view('finance.partials.report', compact('data', 'reportType', 'batchPayableAmounts', 'description', 'paymentModeBreakdown'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid report type']);
    }

    public function downloadReport(Request $request)
    {

        // Copy the exact same logic from generateReport method
        $reportType = $request->input('report_type');
        $batchYear = $request->input('batch_year');
        $year = $request->input('year');
        $month = $request->input('month');
    // Ensure per-year month is available for single-year per_year downloads
    $perYearMonth = $request->input('month');
        $monthStart = $request->input('month_start');
        $yearStart = $request->input('year_start');
        $monthEnd = $request->input('month_end');
        $yearEnd = $request->input('year_end');

        // New parameters for student month range
        $studentMonthFrom = $request->input('student_month_from');
        $studentYearFrom = $request->input('student_year_from');
        $studentMonthTo = $request->input('student_month_to');
        $studentYearTo = $request->input('student_year_to');

        $data = [];
        $description = '';
        $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();
        $modeTotals = [];
    $perTypeData = collect();
    $paymentMode = $request->input('payment_mode');

        if ($reportType === 'total_paid_per_student') {
            // Get batch payable amounts for calculations
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // Base query for students with their payments - use INNER JOIN to only show students who actually paid
            $query = \App\Models\StudentDetails::with(['batch', 'user'])
                ->join('payments', function($join) {
                    $join->on('student_details.student_id', '=', 'payments.student_id')
                         ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                })
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->selectRaw('
                    student_details.student_id,
                    pnph_users.user_fname as first_name,
                    pnph_users.user_lname as last_name,
                    student_details.batch as batch_year,
                    SUM(payments.amount) as total_paid,
                    MAX(payments.payment_date) as last_payment_date,
                    GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                ')
                ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply date filters - when date filters are applied, we only want students who actually paid
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter - rebuild query with INNER JOIN to get only students who paid
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();

                $query = \App\Models\StudentDetails::query()
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->join('payments', function($join) use ($fromDate, $toDate) {
                        $join->on('student_details.student_id', '=', 'payments.student_id')
                             ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                             ->whereBetween('payments.payment_date', [$fromDate, $toDate]);
                    })
                    ->selectRaw('
                        student_details.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        SUM(payments.amount) as total_paid,
                        MAX(payments.payment_date) as last_payment_date,
                        GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                    ')
                    ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                // Apply payment mode filter if provided
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter - rebuild query with INNER JOIN to get only students who paid
                $query = \App\Models\StudentDetails::query()
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->join('payments', function($join) use ($month, $year) {
                        $join->on('student_details.student_id', '=', 'payments.student_id')
                             ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                             ->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', $year);
                    })
                    ->selectRaw('
                        student_details.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        SUM(payments.amount) as total_paid,
                        MAX(payments.payment_date) as last_payment_date,
                        GROUP_CONCAT(DISTINCT payments.payment_mode ORDER BY payments.payment_mode SEPARATOR ", ") as payment_methods
                    ')
                    ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } else {
                $dateDescription = 'All Time';
            }

            $data = $query->get();

            // Fix remaining balance calculation for each student
            foreach ($data as $row) {
                $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                $totalPaid = $row->total_paid ?? 0;

                // Ensure remaining balance is calculated correctly
                $remaining = $payable - $totalPaid;

                // Add calculated fields to the row
                $row->payable_amount = $payable;

                // Don't use max(0, $remaining) - show actual remaining balance even if negative
                $row->remaining_balance = $remaining;

                // Track overpayment separately
                if ($remaining < 0) {
                    $row->overpaid_amount = abs($remaining);
                } else {
                    $row->overpaid_amount = 0;
                }
            }

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "This report provides a detailed overview of $batchDescription students who have completed payments to date.";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "This report provides a detailed overview of $batchDescription students who have completed payments from $dateDescription.";
                } else {
                    $description = "This report provides a detailed overview of $batchDescription students who have completed payments for $dateDescription.";
                }
            }
        }

        if ($reportType === 'total_paid_per_month') {
            // Get batch payable amounts for calculations
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();

            // Base query for daily transaction history - show only students who paid
            $query = Payment::selectRaw('
                    payments.payment_date,
                    student_details.student_id,
                    pnph_users.user_fname as first_name,
                    pnph_users.user_lname as last_name,
                    student_details.batch as batch_year,
                    payments.amount as total_paid,
                    payments.payment_mode
                ')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->whereIn('payments.status', ['Approved', 'Added by Finance']);

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply date filters
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $query->whereBetween('payments.payment_date', [$fromDate, $toDate]);

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter
                $query->whereMonth('payments.payment_date', $month)
                      ->whereYear('payments.payment_date', $year);

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } elseif ($year) {
                // Single year filter
                $query->whereYear('payments.payment_date', $year);
                $dateDescription = "Year $year";
            } else {
                // Default to current year
                $currentYear = date('Y');
                $query->whereYear('payments.payment_date', $currentYear);
                $dateDescription = "Year $currentYear";
            }

            $query->orderBy('payments.payment_date')->orderBy('student_details.student_id');
            $data = $query->get();

            // Calculate remaining balance for each transaction
            foreach ($data as $row) {
                $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                
                // Get total paid by this student up to this payment date
                $totalPaidUpToDate = Payment::where('student_id', $row->student_id)
                    ->whereIn('status', ['Approved', 'Added by Finance'])
                    ->where('payment_date', '<=', $row->payment_date)
                    ->sum('amount');
                
                $remaining = $payable - $totalPaidUpToDate;
                
                // Add calculated fields to the row
                $row->payable_amount = $payable;
                $row->remaining_balance = $remaining;
            }

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments to date.";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments from $dateDescription.";
                } else {
                    $description = "This report provides a detailed daily transaction history of $batchDescription students who have completed payments for $dateDescription.";
                }
            }
        }

        if ($reportType === 'total_paid_per_year') {
            // Base query for batch payments
            $query = Payment::selectRaw('student_details.batch as batch_year, SUM(amount) as total_paid')
                ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                ->whereIn('status', ['Approved', 'Added by Finance']);

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply date filters
            if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                // Month range filter
                $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                $query->whereBetween('payments.payment_date', [$fromDate, $toDate]);

                $dateDescription = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->format('F Y') .
                                 ' to ' . \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->format('F Y');
            } elseif ($month && $year) {
                // Single month filter
                $query->whereMonth('payments.payment_date', $month)
                      ->whereYear('payments.payment_date', $year);

                $dateDescription = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
            } else {
                $dateDescription = 'All Time';
            }

            $query->groupBy('student_details.batch');
            $data = $query->get();

            // Create description based on filters
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            if ($dateDescription === 'All Time') {
                $description = "Total amount paid of $batchDescription";
            } else {
                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $description = "Total amount paid of $batchDescription between $dateDescription";
                } else {
                    $description = "Total amount paid of $batchDescription for the month of $dateDescription";
                }
            }
            // Initialize required variables for the template
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();


        }

        if ($reportType === 'per_year') {
            // Get year range parameters
            $yearFrom = $request->input('year_from');
            $yearTo = $request->input('year_to');

            // Default to current year if no year range specified
            if (!$yearFrom && !$yearTo) {
                $yearFrom = $yearTo = date('Y');
            } elseif ($yearFrom && !$yearTo) {
                $yearTo = $yearFrom;
            } elseif (!$yearFrom && $yearTo) {
                $yearFrom = $yearTo;
            }

            // Base query for batch payments by year
            $singleYear = ($yearFrom == $yearTo);

            if ($singleYear) {
                // If only a single year is requested, show monthly breakdown (month + year)
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as payment_year,
                        MONTH(payments.payment_date) as payment_month,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }
                // If a specific month was selected for the single-year view, apply it here too
                if (!empty($perYearMonth) && is_numeric($perYearMonth)) {
                    $query->whereMonth('payments.payment_date', intval($perYearMonth));
                }
            } else {
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as payment_year,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }
            }

            // Apply batch filter
            if ($batchYear) {
                $query->where('student_details.batch', $batchYear);
            }

            // Apply year range filter
            if ($yearFrom == $yearTo) {
                $query->whereYear('payments.payment_date', $yearFrom);
                $dateDescription = $yearFrom;
            } else {
                $query->whereBetween(\DB::raw('YEAR(payments.payment_date)'), [$yearFrom, $yearTo]);
                $dateDescription = "$yearFrom - $yearTo";
            }

        if ($singleYear) {
            $query->groupBy('student_details.batch', 'payment_year', 'payment_month')
                ->orderBy('student_details.batch')
                ->orderBy('payment_year')
                ->orderBy('payment_month');
        } else {
            $query->groupBy('student_details.batch', 'payment_year')
                ->orderBy('student_details.batch')
                ->orderBy('payment_year');
        }

            $data = $query->get();

            // Create description
            $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
            $description = "Total amount collected from $batchDescription for year(s) $dateDescription";

            // Get batch payable amounts for reference
            $batchPayableAmounts = Batch::pluck('total_due', 'batch_year')->toArray();
        }

        if ($reportType === 'total_paid_per_batch_year') {
            // Simplified: Use single year filter only (no more year range)

            // Check if user wants aggregated view (no specific filters = show summary)
            $showAggregated = !$batchYear && !$year;

            if ($showAggregated) {
                // Show aggregated data by batch and year
                $query = Payment::selectRaw('
                        student_details.batch as batch_year,
                        YEAR(payments.payment_date) as year,
                        SUM(payments.amount) as total_paid
                    ')
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                    ->groupBy('student_details.batch', \DB::raw('YEAR(payments.payment_date)'))
                    ->orderBy('student_details.batch')
                    ->orderBy('year');

                $data = $query->get();
                $description = "Payment totals grouped by class batch and year (aggregated view)";
            } else {
                // Show individual student data (same as generateReport logic)
                if ($year) {
                    // Use INNER JOIN to show only students who paid in specific year
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->join('payments', function($join) use ($year) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                                 ->whereYear('payments.payment_date', $year);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            SUM(payments.amount) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                } else {
                    // Use LEFT JOIN to show all students
                    $query = \App\Models\StudentDetails::query()
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->leftJoin('payments', function($join) {
                            $join->on('student_details.student_id', '=', 'payments.student_id')
                                 ->whereIn('payments.status', ['Approved', 'Added by Finance']);
                        })
                        ->selectRaw('
                            student_details.student_id,
                            pnph_users.user_fname as first_name,
                            pnph_users.user_lname as last_name,
                            student_details.batch as batch_year,
                            COALESCE(SUM(payments.amount), 0) as total_paid
                        ')
                        ->groupBy('student_details.student_id', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');
                }

                // Apply batch filter
                if ($batchYear) {
                    $query->where('student_details.batch', $batchYear);
                }

                // Apply payment mode filter for per-batch-year (individual student data)
                if (!empty($paymentMode)) {
                    $query->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $data = $query->get();

                // Create description for individual student data
                $batchDescription = $batchYear ? "Class $batchYear" : "All Classes";
                if ($year) {
                    $description = "$batchDescription students who made payments in year $year (only students with payments shown)";
                } else {
                    $description = "$batchDescription students payment summary (all students including those with zero payments)";
                }
            }
        }

        // Calculate totals per payment mode with the same filters as the report
        // Get all unique payment modes from the database (apply selected payment_mode if provided)
        // Defensive filter: ensure $data only contains rows matching selected payment mode
        if (!empty($paymentMode) && !empty($data)) {
            $paymentModeLower = strtolower($paymentMode);
            // Ensure we have a collection
            $data = collect($data)->filter(function($row) use ($paymentModeLower) {
                // If row has single payment_mode field (per-transaction rows)
                if (isset($row->payment_mode) && $row->payment_mode) {
                    return strtolower($row->payment_mode) === $paymentModeLower;
                }

                // If row has grouped payment_methods (comma-separated)
                if (isset($row->payment_methods) && $row->payment_methods) {
                    $methods = array_map('trim', explode(',', $row->payment_methods));
                    foreach ($methods as $m) {
                        if (strtolower($m) === $paymentModeLower) {
                            return true;
                        }
                    }
                    return false;
                }

                // Otherwise keep the row (defensive fallback)
                return true;
            })->values();
        }
        $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

        // Apply same filters as the report
        if ($batchYear) {
            $paymentModeQuery->where('student_details.batch', $batchYear);
        }

        if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
            $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
            $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
            $paymentModeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
        } elseif ($month && $year) {
            $paymentModeQuery->whereMonth('payments.payment_date', $month)
                           ->whereYear('payments.payment_date', $year);
        }

        if ($reportType === 'total_paid_per_batch_year') {
            // Simplified: Use single year filter only
            if ($year) {
                $paymentModeQuery->whereYear('payments.payment_date', $year);
            }
        }

        // Compute totals per payment mode with a single grouped query using the same filters
        $modeTotalsQuery = Payment::selectRaw('payments.payment_mode, SUM(payments.amount) as total')
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance']);

        if ($batchYear) {
            $modeTotalsQuery->where('student_details.batch', $batchYear);
        }

        if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
            $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
            $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
            $modeTotalsQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
        } elseif ($month && $year) {
            $modeTotalsQuery->whereMonth('payments.payment_date', $month)
                             ->whereYear('payments.payment_date', $year);
        }

        if ($reportType === 'per_year') {
            $yearFrom = $request->input('year_from');
            $yearTo = $request->input('year_to');
            if (!$yearFrom && !$yearTo) {
                $yearFrom = $yearTo = date('Y');
            } elseif ($yearFrom && !$yearTo) {
                $yearTo = $yearFrom;
            } elseif (!$yearFrom && $yearTo) {
                $yearFrom = $yearTo;
            }

            if ($yearFrom == $yearTo) {
                $modeTotalsQuery->whereYear('payments.payment_date', $yearFrom);
                if (!empty($perYearMonth) && is_numeric($perYearMonth)) {
                    $modeTotalsQuery->whereMonth('payments.payment_date', intval($perYearMonth));
                }
            } else {
                // multi-year range
                $modeTotalsQuery->whereBetween(\DB::raw('YEAR(payments.payment_date)'), [$yearFrom, $yearTo]);
            }
        } elseif ($reportType === 'total_paid_per_batch_year') {
            if ($year) {
                $modeTotalsQuery->whereYear('payments.payment_date', $year);
            }
        }

        if (!empty($paymentMode)) {
            // If a specific payment mode was requested, limit the grouped results to that mode (case-insensitive)
            $modeTotalsQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
        }

        $modeTotals = $modeTotalsQuery->groupBy('payments.payment_mode')
            ->pluck('total', 'payments.payment_mode')
            ->toArray();

        // Provide a computed total to the view for exact parity
        $computedTotal = array_sum($modeTotals);

        // No normalization here — let the view show actual payment_mode/payment_methods from data

        // Build per-student per-payment-mode totals for download (so PDF shows one row per method)
        if ($reportType === 'total_paid_per_student') {
            try {
                $perTypeQuery = Payment::selectRaw("payments.student_id,
                        pnph_users.user_fname as first_name,
                        pnph_users.user_lname as last_name,
                        student_details.batch as batch_year,
                        payments.payment_mode,
                        SUM(payments.amount) as total_by_mode,
                        MAX(payments.payment_date) as last_payment_date")
                    ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
                    ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                    ->whereIn('payments.status', ['Approved', 'Added by Finance'])
                    ->groupBy('payments.student_id', 'payments.payment_mode', 'pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.batch');

                if ($batchYear) {
                    $perTypeQuery->where('student_details.batch', $batchYear);
                }

                if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
                    $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
                    $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
                    $perTypeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
                } elseif ($month && $year) {
                    $perTypeQuery->whereMonth('payments.payment_date', $month)
                                 ->whereYear('payments.payment_date', $year);
                }

                if (!empty($paymentMode)) {
                    $perTypeQuery->whereRaw('LOWER(payments.payment_mode) = LOWER(?)', [$paymentMode]);
                }

                $perTypeData = $perTypeQuery->orderBy('payments.student_id')->orderBy('payments.payment_mode')->get();
            } catch (\Exception $e) {
                \Log::error('Error building perTypeData for downloadReport: ' . $e->getMessage());
                $perTypeData = collect();
            }
        }
    $pdf = Pdf::loadView('finance.partials.report-pdf', compact('data', 'reportType', 'description', 'batchPayableAmounts', 'modeTotals', 'perTypeData', 'computedTotal'));
        return $pdf->download('finance_report.pdf');
    }

    public function reports()
    {
        $batches = Batch::all();

        // Get dynamic payment methods from database (same as used throughout the system)
        $dynamicPaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();

        // Always include Cash as a standard payment method, plus dynamic methods
        $paymentMethods = collect([
            (object)[
                'id' => 'cash',
                'name' => 'Cash',
                'account_name' => null,
                'account_number' => null,
                'is_standard' => true
            ]
        ])->merge($dynamicPaymentMethods);

        // Extract just the names for the dropdown (consistent with how payments are stored)
        $paymentModes = $paymentMethods->pluck('name')->sort()->values();

        return view('finance.financeReports', compact('batches', 'paymentModes'));
    }

    public function getPaymentModeTotals(Request $request)
    {
        $batchYear = $request->input('batch_year');
        $paymentMode = $request->input('payment_mode');
        $year = $request->input('year');
        $month = $request->input('month');
        $studentMonthFrom = $request->input('student_month_from');
        $studentYearFrom = $request->input('student_year_from');
        $studentMonthTo = $request->input('student_month_to');
        $studentYearTo = $request->input('student_year_to');

        // Base query for payment mode totals
        $paymentModeQuery = Payment::whereIn('status', ['Approved', 'Added by Finance'])
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id');

        // Apply batch filter
        if ($batchYear) {
            $paymentModeQuery->where('student_details.batch', $batchYear);
        }

        // Apply date filters
        if ($studentMonthFrom && $studentYearFrom && $studentMonthTo && $studentYearTo) {
            $fromDate = \Carbon\Carbon::create($studentYearFrom, $studentMonthFrom, 1)->startOfMonth();
            $toDate = \Carbon\Carbon::create($studentYearTo, $studentMonthTo, 1)->endOfMonth();
            $paymentModeQuery->whereBetween('payments.payment_date', [$fromDate, $toDate]);
        } elseif ($month && $year) {
            $paymentModeQuery->whereMonth('payments.payment_date', $month)
                           ->whereYear('payments.payment_date', $year);
        }

        // Get totals by payment mode
        $modeTotals = $paymentModeQuery->selectRaw('payment_mode, SUM(amount) as total')
            ->groupBy('payment_mode')
            ->pluck('total', 'payment_mode')
            ->toArray();

        // Calculate overall total
        $overallTotal = array_sum($modeTotals);

        // If a specific payment mode is selected, return only that total
        if ($paymentMode) {
            $filteredTotal = $modeTotals[$paymentMode] ?? 0;
            return response()->json([
                'success' => true,
                'payment_mode' => $paymentMode,
                'total' => $filteredTotal,
                'overall_total' => $overallTotal,
                'mode_totals' => $modeTotals
            ]);
        }

        return response()->json([
            'success' => true,
            'overall_total' => $overallTotal,
            'mode_totals' => $modeTotals
        ]);
    }

    private function generateReceiptAndNotify($payment, $student, $user, $type, $title, $message)
    {
        // Generate the PDF receipt
        $receiptPath = 'receipts/receipt_' . $payment->id . '.pdf';
        $pdf = Pdf::loadView('pdf.payment_receipt', ['payment' => $payment, 'student' => $student]);
        $pdf->save(storage_path('app/public/' . $receiptPath)); // Save the receipt

        // Store the notification in the custom table
        CustomNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'receipt_path' => $receiptPath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getUnreadCount()
    {
        $sessionUser = session('user');
        $totalCount = 0;

        if ($sessionUser) {
            // Base: unread CustomNotifications for this user
            $customUnread = \App\Models\CustomNotification::where('user_id', $sessionUser['user_id'])
                ->where('is_read', 0)
                ->count();

            $totalCount += $customUnread;

            // Add role-based actionable items
            $localUser = \App\Models\User::where('user_email', $sessionUser['user_email'])->first();
            $role = $localUser ? $localUser->user_role : null;

            if ($role === 'cashier') {
                // Payments awaiting cashier verification (student submissions)
                $pendingForCashier = \App\Models\Payment::where('status', 'Pending')->count();
                $totalCount += $pendingForCashier;
            } elseif ($role === 'finance') {
                // Payments verified by cashier and awaiting finance final approval
                $pendingForFinance = \App\Models\Payment::where('status', 'Cashier Verified')->count();
                $totalCount += $pendingForFinance;
            }
        }

        return response()->json(['unread_count' => $totalCount]);
    }

    public function testTargetData()
    {
        // Test method to check data generation
        $targets = DB::table('batches as b')
            ->leftJoin('student_details as sd', 'b.batch_year', '=', 'sd.batch')
            ->selectRaw('
                b.batch_year,
                b.total_due as target_amount,
                COUNT(sd.student_id) as student_count
            ')
            ->groupBy('b.batch_year', 'b.total_due')
            ->get();

        $collections = Payment::query()
            ->join('student_details', 'payments.student_id', '=', 'student_details.student_id')
            ->whereIn('payments.status', ['Approved', 'Added by Finance'])
            ->selectRaw('
                student_details.batch as batch_year,
                YEAR(payments.payment_date) as year,
                MONTH(payments.payment_date) as month,
                SUM(payments.amount) as actual_collection
            ')
            ->groupByRaw('student_details.batch, YEAR(payments.payment_date), MONTH(payments.payment_date)')
            ->get();

        return response()->json([
            'targets' => $targets,
            'collections' => $collections,
            'batches_count' => $targets->count(),
            'collections_count' => $collections->count()
        ]);
    }

    /**
     * Calculate which months a payment covers based on student's payment history and batch settings
     */
    private function calculatePaidMonths($studentDetails, $paymentAmount)
    {
        $batchYear = $studentDetails->batch;
        $monthlyFee = \App\Models\FinanceSetting::get('matrix_monthly_fee', 500);

        // Get batch-specific payment start settings
        $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);
        $currentYear = now()->year;

        if ($paymentStartSettings === null) {
            $startMonth = 1; // January
            $startYear = $currentYear;
        } else {
            $startMonth = $paymentStartSettings['start_month'];
            $startYear = $paymentStartSettings['start_year'];
        }

        // Get all existing payments for this student (EXCLUDING the current payment being processed)
        // We need to subtract the current payment amount to get the previous total
        $totalPayments = $studentDetails->payments()
            ->where('status', 'Added by Finance')
            ->sum('amount');

        // Subtract current payment to get previous payments only
        $existingPayments = $totalPayments - $paymentAmount;

        // Calculate which months are covered by this specific payment
        $previousMonthsPaid = floor($existingPayments / $monthlyFee);
        $newMonthsPaid = floor($paymentAmount / $monthlyFee);

        $currentPaymentMonths = [];
        $startDate = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);

        for ($i = $previousMonthsPaid; $i < $previousMonthsPaid + $newMonthsPaid; $i++) {
            $monthDate = $startDate->copy()->addMonths($i);
            $currentPaymentMonths[] = [
                'month' => $monthDate->month,
                'year' => $monthDate->year,
                'name' => $monthDate->format('F'),
                'display' => $monthDate->format('F Y')
            ];
        }

        return $currentPaymentMonths;
    }

    /**
     * Format months array into a readable string for notifications
     */
    private function formatMonthsForNotification($months)
    {
        if (empty($months)) {
            return 'Counterpart Payment';
        }

        if (count($months) == 1) {
            return 'month of ' . $months[0]['display'];
        }

        if (count($months) == 2) {
            return 'months of ' . $months[0]['display'] . ' and ' . $months[1]['display'];
        }

        // For more than 2 months
        $monthNames = array_map(function($month) {
            return $month['display'];
        }, $months);

        $lastMonth = array_pop($monthNames);
        return 'months of ' . implode(', ', $monthNames) . ' and ' . $lastMonth;
    }

    /**
     * Send payment reminders to students with overdue payments
     * Called automatically based on finance settings or manually by finance staff
     */
    public function sendPaymentReminders()
    {
        try {
            // Check if payment reminders are enabled
            $reminderSettings = \App\Models\FinanceSetting::getPaymentReminderSettings();
            
            if (!$reminderSettings['auto_enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Automatic payment reminders are disabled in settings'
                ]);
            }

            $delayThreshold = $reminderSettings['first_after_months']; // Default: 2 months
            $overdueStudents = $this->getOverdueStudents($delayThreshold);
            
            $remindersSent = 0;
            $errors = [];

            foreach ($overdueStudents as $studentData) {
                try {
                    $this->sendReminderToStudent($studentData);
                    $remindersSent++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to send reminder to {$studentData['student']->first_name} {$studentData['student']->last_name}: " . $e->getMessage();
                    \Log::error('Payment reminder error: ' . $e->getMessage(), [
                        'student_id' => $studentData['student']->student_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Payment reminders sent successfully to {$remindersSent} student(s)",
                'reminders_sent' => $remindersSent,
                'total_overdue' => count($overdueStudents),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment reminder system error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send payment reminders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students with overdue payments based on delay threshold
     */
    private function getOverdueStudents($delayThreshold)
    {
        $overdueStudents = [];
        $currentDate = now();

        // Get all students with their payment details
        $students = User::where('user_role', 'student')
            ->with(['studentDetails.payments', 'studentDetails.batchInfo'])
            ->get();

        foreach ($students as $user) {
            $studentDetails = $user->studentDetails;
            if (!$studentDetails) continue;

            // Add user info to student details for compatibility
            $studentDetails->first_name = $user->user_fname;
            $studentDetails->last_name = $user->user_lname;
            $studentDetails->email = $user->user_email;

            $batchYear = $studentDetails->batch;
            
            // Get payment start settings for this batch
            $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);
            
            if ($paymentStartSettings === null) {
                // Fallback to global counterpart start settings (dynamic)
                $globalStart = \App\Models\FinanceSetting::getCounterpartPaymentStartSettings();
                $startMonth = $globalStart['start_month'];
                $startYear = $globalStart['start_year'];
            } else {
                $startMonth = $paymentStartSettings['start_month'];
                $startYear = $paymentStartSettings['start_year'];
            }

            $startDate = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);
            
            // Skip if payment period hasn't started yet
            if ($currentDate->lt($startDate)) {
                continue;
            }

            // Get batch-specific monthly fee
            $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
            $batchSettings = json_decode($batchSettingsJson, true) ?: [];
            $monthlyFee = $batchSettings['monthly_default'] ?? 500;

            // Calculate unpaid months and delay
            $unpaidMonthsData = $this->calculateUnpaidMonths($studentDetails, $startDate, $monthlyFee, $currentDate);
            
            if ($unpaidMonthsData['delay_months'] >= $delayThreshold) {
                $overdueStudents[] = [
                    'student' => $studentDetails,
                    'unpaid_months' => $unpaidMonthsData['unpaid_months'],
                    'delay_months' => $unpaidMonthsData['delay_months'],
                    'remaining_balance' => $unpaidMonthsData['remaining_balance'],
                    'monthly_fee' => $monthlyFee
                ];
            }
        }

        return $overdueStudents;
    }

    /**
     * Calculate unpaid months for a student
     */
    private function calculateUnpaidMonths($studentDetails, $startDate, $monthlyFee, $currentDate)
    {
        // Get total paid amount (only approved/added by finance)
        $totalPaid = $studentDetails->payments()
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->sum('amount');

        // Calculate how many months should be paid by now
        $monthsSinceStart = $startDate->diffInMonths($currentDate->startOfMonth()) + 1;
        $monthsShouldBePaid = max(0, $monthsSinceStart);

        // Calculate how many months are actually paid
        $monthsPaid = floor($totalPaid / $monthlyFee);

        // Calculate unpaid months
        $unpaidMonthsCount = max(0, $monthsShouldBePaid - $monthsPaid);
        
        // Get total due from batch info
        $totalDue = optional($studentDetails->batchInfo)->total_due ?? ($monthlyFee * 24); // Default 24 months
        $remainingBalance = max(0, $totalDue - $totalPaid);

        // Generate list of unpaid months
        $unpaidMonths = [];
        if ($unpaidMonthsCount > 0) {
            for ($i = $monthsPaid; $i < $monthsShouldBePaid; $i++) {
                $monthDate = $startDate->copy()->addMonths($i);
                $unpaidMonths[] = [
                    'month' => $monthDate->month,
                    'year' => $monthDate->year,
                    'name' => $monthDate->format('F'),
                    'display' => $monthDate->format('F Y')
                ];
            }
        }

        return [
            'unpaid_months' => $unpaidMonths,
            'delay_months' => $unpaidMonthsCount,
            'remaining_balance' => $remainingBalance,
            'months_paid' => $monthsPaid,
            'months_should_be_paid' => $monthsShouldBePaid
        ];
    }

    /**
     * Send reminder notification to a specific student
     */
    private function sendReminderToStudent($studentData)
    {
        $studentDetails = $studentData['student'];
        $unpaidMonths = $studentData['unpaid_months'];
        $delayMonths = $studentData['delay_months'];
        $remainingBalance = $studentData['remaining_balance'];
        $monthlyFee = $studentData['monthly_fee'];

        // Find the user record for notification
        $user = User::where('user_email', $studentDetails->email)->first();
        
        if (!$user) {
            throw new \Exception("User not found for student: {$studentDetails->email}");
        }

        // Respect notification method settings and reminder limits
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
        $reminderSettings = \App\Models\FinanceSetting::getPaymentReminderSettings();

        $dashboardEnabled = $notificationSettings['dashboard'];
        $studentAccountEnabled = $notificationSettings['student_account'];
        $emailEnabled = $notificationSettings['email'];

        // If all methods are disabled, do nothing
        if (!$dashboardEnabled && !$studentAccountEnabled && !$emailEnabled) {
            return;
        }

        // Enforce max reminders and follow-up interval using CustomNotification records
        $maxReminders = $reminderSettings['max_reminders'] ?? 5;
        $followUpInterval = $reminderSettings['follow_up_interval'] ?? 1; // months

        $totalReminders = \App\Models\CustomNotification::where('user_id', $user->user_id)
            ->where('type', 'payment_reminder')
            ->count();
        if ($totalReminders >= $maxReminders) {
            \Log::info('Skipping reminder: max reminders reached', [
                'user_id' => $user->user_id,
                'max_reminders' => $maxReminders
            ]);
            return;
        }

        $lastReminder = \App\Models\CustomNotification::where('user_id', $user->user_id)
            ->where('type', 'payment_reminder')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastReminder) {
            $lastSentAt = \Carbon\Carbon::parse($lastReminder->created_at);
            if ($lastSentAt->gt(now()->copy()->subMonths($followUpInterval))) {
                \Log::info('Skipping reminder: follow-up interval not elapsed', [
                    'user_id' => $user->user_id,
                    'follow_up_interval_months' => $followUpInterval,
                    'last_sent_at' => $lastReminder->created_at,
                ]);
                return;
            }
        }
            
        // Build message including unpaid months and remaining balance
        $studentName = $studentDetails->first_name . ' ' . $studentDetails->last_name;
        $unpaidMonthsText = empty($unpaidMonths)
            ? ''
            : implode(', ', array_map(function($m) { return $m['display']; }, $unpaidMonths));

        $message = "<strong>Dear {$studentName},</strong><br><br>";
        if (!empty($unpaidMonths)) {
            $monthsCount = count($unpaidMonths);
            $monthsLabel = $monthsCount === 1 ? 'month' : 'months';
            $firstMonth = $unpaidMonths[0]['display'];
            $lastMonth = $unpaidMonths[$monthsCount - 1]['display'];
            $rangeText = $monthsCount === 1 ? $firstMonth : "from {$firstMonth} to {$lastMonth}";
            $message .= "We've noticed that your parent counterpart payment has not been settled for the past {$monthsCount} {$monthsLabel}—{$rangeText}. This is a gentle reminder to arrange payment for these {$monthsLabel}.<br><br>";
            $message .= "<strong>Unpaid Months:</strong> <span style='color: #d9534f; font-weight: bold;'>{$unpaidMonthsText}</span><br>";
        } else {
            $message .= "We've noticed that your parent counterpart payment has an outstanding balance. This is a gentle reminder to arrange payment.<br><br>";
        }
        $message .= "<strong>Outstanding Balance:</strong> ₱" . number_format($remainingBalance, 2) . "<br><br>";
        $message .= "Please inform your parent or guardian about these unpaid months and make arrangements to settle the balance as soon as possible. Your cooperation ensures the continued strength and service of PN Philippines.<br><br>";
        $message .= "Best regards,<br><strong>Finance Department</strong>";

        // Create a single CustomNotification for dashboard/student account if enabled
        if ($dashboardEnabled || $studentAccountEnabled) {
            \App\Models\CustomNotification::create([
                'user_id' => $user->user_id,
                'type' => 'payment_reminder',
                'title' => 'Payment Reminder - ' . $delayMonths . ' Month' . ($delayMonths > 1 ? 's' : '') . ' Overdue',
                'message' => $message,
                'is_read' => 0
            ]);
        }

        // Send email if enabled
        if ($emailEnabled && $user->user_email) {
            try {
                $reminderData = [
                    'months_unpaid' => array_map(function($m) { return $m['display']; }, $unpaidMonths),
                ];

                \Mail::to($user->user_email)->send(new \App\Mail\PaymentReminderMail(
                    $user,
                    'overdue',
                    $notificationSettings['sender_name'],
                    $reminderData,
                    $message
                ));

                \Log::info('Payment reminder email sent', [
                    'user_id' => $user->user_id,
                    'email' => $user->user_email,
                    'delay_months' => $delayMonths
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send payment reminder email', [
                    'user_id' => $user->user_id,
                    'email' => $user->user_email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Log the reminder for audit purposes
        \Log::info('Payment reminder sent', [
            'student_id' => $studentDetails->student_id,
            'student_name' => $studentDetails->first_name . ' ' . $studentDetails->last_name,
            'delay_months' => $delayMonths,
            'remaining_balance' => $remainingBalance,
            'unpaid_months_count' => count($unpaidMonths)
        ]);
    }

    /**
     * Manual trigger for payment reminders (for finance staff)
     */
    public function triggerPaymentReminders(Request $request)
    {
        // Validate that user has finance role (add your auth logic here)
        $user = session('user');
        if (!$user || $user['user_role'] !== 'finance') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return $this->sendPaymentReminders();
    }

    /**
     * Get overdue students summary for finance dashboard
     */
    public function getOverdueStudentsSummary()
    {
        try {
            $reminderSettings = \App\Models\FinanceSetting::getPaymentReminderSettings();
            $delayThreshold = $reminderSettings['first_after_months'];
            
            $overdueStudents = $this->getOverdueStudents($delayThreshold);
            
            // Group by delay severity
            $summary = [
                'total_overdue' => count($overdueStudents),
                'moderate_delay' => 0, // 2-2 months
                'severe_delay' => 0,   // 3+ months
                'students' => []
            ];

            foreach ($overdueStudents as $studentData) {
                $delayMonths = $studentData['delay_months'];
                
                if ($delayMonths >= 3) {
                    $summary['severe_delay']++;
                } else {
                    $summary['moderate_delay']++;
                }

                $summary['students'][] = [
                    'student_id' => $studentData['student']->student_id,
                    'name' => $studentData['student']->first_name . ' ' . $studentData['student']->last_name,
                    'batch' => $studentData['student']->batch,
                    'delay_months' => $delayMonths,
                    'remaining_balance' => $studentData['remaining_balance'],
                    'unpaid_months_count' => count($studentData['unpaid_months'])
                ];
            }

            return response()->json([
                'success' => true,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting overdue students summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get overdue students summary'
            ], 500);
        }
    }

    /**
     * Test/Demo endpoint to verify payment reminder functionality
     * Shows what students would receive reminders without actually sending them
     */
    public function testPaymentReminders()
    {
        try {
            $reminderSettings = \App\Models\FinanceSetting::getPaymentReminderSettings();
            $delayThreshold = $reminderSettings['first_after_months'];
            
            $overdueStudents = $this->getOverdueStudents($delayThreshold);
            
            $testResults = [
                'settings' => $reminderSettings,
                'delay_threshold' => $delayThreshold,
                'total_overdue' => count($overdueStudents),
                'preview_notifications' => []
            ];

            foreach ($overdueStudents as $studentData) {
                $studentDetails = $studentData['student'];
                $unpaidMonths = $studentData['unpaid_months'];
                $delayMonths = $studentData['delay_months'];
                $remainingBalance = $studentData['remaining_balance'];
                $monthlyFee = $studentData['monthly_fee'];

                // Format unpaid months text
                $monthsText = $this->formatUnpaidMonthsForPreview($unpaidMonths);
                
                // Generate preview message
                $urgencyLevel = $delayMonths >= 3 ? 'URGENT' : 'IMPORTANT';
                $mainMessage = $delayMonths >= 3 
                    ? "URGENT: You haven't paid for {$delayMonths} months. Your account is significantly overdue for the following months: {$monthsText}. Immediate payment is required to avoid further consequences."
                    : "IMPORTANT: You haven't paid for {$delayMonths} months. Please settle your payment for the following months: {$monthsText}.";

                $testResults['preview_notifications'][] = [
                    'student' => [
                        'id' => $studentDetails->student_id,
                        'name' => $studentDetails->first_name . ' ' . $studentDetails->last_name,
                        'email' => $studentDetails->email,
                        'batch' => $studentDetails->batch
                    ],
                    'notification_preview' => [
                        'urgency_level' => $urgencyLevel,
                        'subject' => $urgencyLevel . ': Payment Reminder - ' . $delayMonths . ' Month' . ($delayMonths > 1 ? 's' : '') . ' Overdue',
                        'main_message' => $mainMessage,
                        'payment_details' => [
                            'remaining_balance' => '₱' . number_format($remainingBalance, 2),
                            'monthly_fee' => '₱' . number_format($monthlyFee, 2),
                            'unpaid_months' => $monthsText,
                            'total_months_overdue' => $delayMonths
                        ]
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment reminder test completed successfully',
                'test_results' => $testResults
            ]);

        } catch (\Exception $e) {
            \Log::error('Error testing payment reminders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to test payment reminders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format unpaid months for preview (same logic as notification)
     */
    private function formatUnpaidMonthsForPreview($unpaidMonths)
    {
        if (empty($unpaidMonths)) {
            return 'N/A';
        }

        if (count($unpaidMonths) == 1) {
            return $unpaidMonths[0]['display'];
        }

        if (count($unpaidMonths) == 2) {
            return $unpaidMonths[0]['display'] . ' and ' . $unpaidMonths[1]['display'];
        }

        // For more than 2 months
        $monthNames = array_map(function($month) {
            return $month['display'];
        }, $unpaidMonths);

        $lastMonth = array_pop($monthNames);
        return implode(', ', $monthNames) . ', and ' . $lastMonth;
    }

    /**
     * Send a real test notification to a specific student or test email
     * This actually sends the notification for realistic testing
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|string',
            'test_email' => 'nullable|email',
            'force_months' => 'nullable|integer|min:2|max:12'
        ]);

        try {
            $user = session('user');
            if (!$user || $user['user_role'] !== 'finance') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $studentId = $request->student_id;
            $testEmail = $request->test_email;
            $forceMonths = $request->force_months ?? 2;

            // If student_id provided, use real student data
            if ($studentId) {
                $studentDetails = \App\Models\StudentDetails::where('student_id', $studentId)->first();
                if (!$studentDetails) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student not found'
                    ]);
                }

                $user = \App\Models\User::where('user_id', $studentDetails->user_id)->first();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found for student'
                    ]);
                }

                $studentDetails->first_name = $user->user_fname;
                $studentDetails->last_name = $user->user_lname;
                $studentDetails->email = $testEmail ?: $user->user_email; // Use test email if provided

                // Calculate real unpaid months data
                $batchYear = $studentDetails->batch;
                $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);
                $currentDate = now();
                
                if ($paymentStartSettings === null) {
                    $startMonth = 1;
                    $startYear = $currentDate->year;
                } else {
                    $startMonth = $paymentStartSettings['start_month'];
                    $startYear = $paymentStartSettings['start_year'];
                }

                $startDate = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);
                $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
                $batchSettings = json_decode($batchSettingsJson, true) ?: [];
                $monthlyFee = $batchSettings['monthly_default'] ?? 500;

                $unpaidMonthsData = $this->calculateUnpaidMonths($studentDetails, $startDate, $monthlyFee, $currentDate);
                
                // Override delay months if force_months is specified
                if ($forceMonths) {
                    $unpaidMonthsData['delay_months'] = $forceMonths;
                    // Generate fake unpaid months for testing
                    $unpaidMonthsData['unpaid_months'] = [];
                    for ($i = 0; $i < $forceMonths; $i++) {
                        $monthDate = $startDate->copy()->addMonths($i);
                        $unpaidMonthsData['unpaid_months'][] = [
                            'month' => $monthDate->month,
                            'year' => $monthDate->year,
                            'name' => $monthDate->format('F'),
                            'display' => $monthDate->format('F Y')
                        ];
                    }
                }

                $unpaidMonths = $unpaidMonthsData['unpaid_months'];
                $delayMonths = $unpaidMonthsData['delay_months'];
                $remainingBalance = $unpaidMonthsData['remaining_balance'];

            } else {
                // Create fake student data for testing
                $studentDetails = new \App\Models\StudentDetails();
                $studentDetails->student_id = 'TEST001';
                $studentDetails->first_name = 'Test';
                $studentDetails->last_name = 'Student';
                $studentDetails->email = $testEmail ?: 'test@example.com';
                $studentDetails->batch = '2024';

                $monthlyFee = 500;
                $remainingBalance = $monthlyFee * $forceMonths;
                $delayMonths = $forceMonths;

                // Generate fake unpaid months
                $unpaidMonths = [];
                $startDate = now()->startOfYear();
                for ($i = 0; $i < $forceMonths; $i++) {
                    $monthDate = $startDate->copy()->addMonths($i);
                    $unpaidMonths[] = [
                        'month' => $monthDate->month,
                        'year' => $monthDate->year,
                        'name' => $monthDate->format('F'),
                        'display' => $monthDate->format('F Y')
                    ];
                }
            }

            // Find or create a user to send notification to
            $targetUser = null;
            if ($studentId) {
                $targetUser = \App\Models\User::where('user_email', $studentDetails->email)->first();
            }

            if (!$targetUser) {
                // Create a temporary user for testing (or use finance user)
                $financeUser = \App\Models\User::where('user_role', 'finance')->first();
                if ($financeUser && $testEmail) {
                    // Temporarily change finance user email for testing
                    $originalEmail = $financeUser->user_email;
                    $financeUser->user_email = $testEmail;
                    $targetUser = $financeUser;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No target user found for notification. Please provide a test_email or valid student_id.'
                    ]);
                }
            }

            // Send the actual notification
            $targetUser->notify(new \App\Notifications\PaymentReminderNotification(
                $studentDetails,
                $unpaidMonths,
                $remainingBalance,
                $monthlyFee,
                $delayMonths
            ));

            // Restore original email if we changed it
            if (isset($originalEmail)) {
                $financeUser->user_email = $originalEmail;
            }

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully!',
                'details' => [
                    'student_name' => $studentDetails->first_name . ' ' . $studentDetails->last_name,
                    'target_email' => $studentDetails->email,
                    'delay_months' => $delayMonths,
                    'unpaid_months_count' => count($unpaidMonths),
                    'remaining_balance' => '₱' . number_format($remainingBalance, 2),
                    'urgency_level' => $delayMonths >= 3 ? 'URGENT' : 'IMPORTANT'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error sending test notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }
}