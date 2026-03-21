<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Notifications\PaymentVerificationNotification;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CustomNotification;

class StudentController extends Controller
{
    public function profile()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access your profile.');
        }

        return view('student.profile', compact('student'));
    }

    public function paymentForm()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access payment form.');
        }

        // Get dynamic payment methods from database
        $dynamicPaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();

        // Always include Cash as a standard payment method
        $paymentMethods = collect([
            (object)[
                'id' => 'cash',
                'name' => 'Cash',
                'account_name' => null,
                'account_number' => null,
                'description' => 'Cash payment made in person',
                'qr_image' => null,
                'is_standard' => true
            ]
        ])->merge($dynamicPaymentMethods);

        return view('student.paymentForm', compact('student', 'paymentMethods'));
    }

    public function uploadPaymentProof(Request $request)
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // Get active payment method names for validation
        $activePaymentMethods = \App\Models\PaymentMethod::where('is_active', true)->pluck('name')->toArray();

        // Always include Cash as a valid payment method
        $activePaymentMethods[] = 'Cash';

        // Fallback to default payment methods if none exist in database
        if (count($activePaymentMethods) <= 1) { // Only Cash exists
            $activePaymentMethods = array_merge($activePaymentMethods, ['GCash', 'Bank Transfer']);
        }

        // Validate basic fields first
        $paymentModeLower = strtolower($request->payment_mode ?? '');

        $rules = [
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            // Make reference_number nullable for cash payments, required otherwise
            'reference_number' => $paymentModeLower === 'cash' ? 'nullable|string|max:255' : 'required|string|max:255',
            'payment_mode' => 'required|string|in:' . implode(',', $activePaymentMethods),
        ];

        // Make payment proof and sender name optional for cash payments
        if (strtolower($request->payment_mode) === 'cash') {
            $rules['payment_proof'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
            $rules['sender_name'] = 'nullable|string|max:255';
        } else {
            $rules['payment_proof'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
            $rules['sender_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        // Handle file upload (optional for cash payments)
        $filePath = null;
        if ($request->hasFile('payment_proof')) {
            $filePath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Find local user to get student details
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || !$localUser->studentDetails) {
            return back()->with('error', 'Student details not found. Please contact administrator.');
        }

        $payment = Payment::create([
            'student_id' => $localUser->studentDetails->student_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_proof' => $filePath,
            'reference_number' => $request->reference_number,
            'sender_name' => $request->sender_name ?: null, // Handle empty sender name
            'payment_mode' => $request->payment_mode,
            'status' => 'Pending',
        ]);

        // Notify finance users
        $financeUsers = User::where('user_role', 'finance')->get();
        foreach ($financeUsers as $financeUser) {
            $financeUser->notify(new PaymentVerificationNotification($payment));

            // Create custom notification with appropriate message based on payment mode
            $notificationTitle = strtolower($request->payment_mode) === 'cash' ? 'New Cash Payment Record' : 'New Payment Proof';

            // Handle sender name display (might be null for cash payments)
            $senderInfo = $request->sender_name ? ' (Sender: ' . $request->sender_name . ')' : '';

            $notificationMessage = strtolower($request->payment_mode) === 'cash'
                ? 'Cash payment record submitted by ' . $student['user_fname'] . ' ' . $student['user_lname'] . ' for ₱' . number_format($request->amount, 2) . $senderInfo . '. Student will visit office to complete payment.'
                : 'New payment proof uploaded by ' . $student['user_fname'] . ' ' . $student['user_lname'] . ' for ₱' . number_format($request->amount, 2) . $senderInfo;

            CustomNotification::create([
                'user_id' => $financeUser->user_id,
                'type' => 'payment_submission',
                'title' => $notificationTitle,
                'message' => $notificationMessage,
                'is_read' => 0
            ]);
        }

        // Set appropriate success message based on payment mode
        if (strtolower($request->payment_mode) === 'cash') {
            $successMessage = 'Cash payment record submitted successfully. Please visit the finance office to complete your payment.';
        } else {
            $successMessage = 'Payment proof submitted successfully. You will be notified once it is verified.';
        }

        return redirect()->route('student.paymentForm')->with('success', $successMessage);
    }

    public function notifications()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access notifications.');
        }

        $notifications = CustomNotification::with(['payment' => function($query) {
            $query->select('id', 'amount');
        }])
        ->where('user_id', $student['user_id'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Mark all notifications as read
        CustomNotification::where('user_id', $student['user_id'])
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return view('student.notifications', compact('notifications'));
    }

    public function paymentHistory()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access payment history.');
        }

        // Find local user to get student details
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || !$localUser->studentDetails) {
            return back()->with('error', 'Student details not found. Please contact administrator.');
        }

        $payments = $localUser->studentDetails->payments()->orderBy('payment_date', 'desc')->get();

        // Build matrix data for the student's finance-record matrix view
        $studentDetails = $localUser->studentDetails()->with(['payments', 'batchInfo'])->first();
        // Attach user info for consistent display (same pattern as finance side)
        $studentDetails->first_name = $student['user_fname'];
        $studentDetails->last_name = $student['user_lname'];
        $studentDetails->email = $student['user_email'];
        $studentDetails->display_name = $student['user_lname'] . ', ' . $student['user_fname'];

        $matrixData = $this->buildStudentMatrixData($studentDetails);

        // Matrix monthly fee (batch-specific override or default)
        $batchYear = $studentDetails->batch;
        $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
        $batchSettings = json_decode($batchSettingsJson, true) ?: [];
        $matrixMonthlyFee = $batchSettings['monthly_default'] ?? 500;

        return view('student.studentPayments', compact('payments', 'matrixData', 'matrixMonthlyFee'));
    }

    public function dashboard()
    {
        $sessionUser = session('user');

        if (!$sessionUser) {
            return redirect()->route('login')->with('error', 'Please log in to access your dashboard.');
        }

        // Find local user to get student details with calculated totals
        $localUser = User::where('user_email', $sessionUser['user_email'])->first();
        if (!$localUser || !$localUser->studentDetails) {
            return redirect()->route('login')->with('error', 'Student details not found. Please contact administrator.');
        }

        // Get student details with calculated attributes
        $student = $localUser->studentDetails;

        // Add user info to student object for display
        $student->user_fname = $sessionUser['user_fname'];
        $student->user_lname = $sessionUser['user_lname'];
        $student->user_email = $sessionUser['user_email'];

        // Get payment warning based on current date and student balance
        $paymentWarning = $this->getPaymentWarning($student);

        // Debug: Log if payment warning exists
        \Log::info('Payment Warning Check', [
            'student_id' => $student->student_id,
            'day_of_month' => \Carbon\Carbon::now()->day,
            'remaining_balance' => $student->remaining_balance,
            'has_payment_warning' => $paymentWarning ? 'YES' : 'NO',
            'warning_data' => $paymentWarning
        ]);

        // Create notification ONLY ONCE per month (not every login)
        if ($paymentWarning) {
            $this->createPaymentWarningNotification($localUser->user_id, $paymentWarning, $student);
        }

        // Get notifications for the student
        $notifications = CustomNotification::where('user_id', $localUser->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('student.studentDashboard', compact('student', 'paymentWarning', 'notifications'));
    }

    /**
     * Generate payment warning message based on student's payment status
     * Now uses dynamic settings from General Settings instead of hardcoded values
     */
    private function getPaymentWarning($student)
    {
        $remainingBalance = $student->remaining_balance;

        if ($remainingBalance <= 0) {
            return null; // No warning needed
        }

        // Get dynamic settings from General Settings
        $monthlySettings = \App\Models\FinanceSetting::getMonthlyReminderSettings();
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();

        // Check if monthly reminders are enabled
        if (!$monthlySettings['enabled']) {
            return null; // Monthly reminders disabled in settings
        }

        $currentDate = \Carbon\Carbon::now();
        $dayOfMonth = $currentDate->day;
        $configuredDay = $monthlySettings['day']; // Dynamic day from settings

        \Log::info('Payment Warning Date Check (Dynamic)', [
            'current_date' => $currentDate->toDateString(),
            'day_of_month' => $dayOfMonth,
            'configured_reminder_day' => $configuredDay,
            'remaining_balance' => $remainingBalance,
            'monthly_reminders_enabled' => $monthlySettings['enabled'],
            'condition_check' => $dayOfMonth === $configuredDay ? "MATCHES CONFIGURED DAY {$configuredDay}" : "DOES NOT MATCH CONFIGURED DAY {$configuredDay}"
        ]);

        // Show payment reminder on the CONFIGURED day of the month (dynamic)
        if ($dayOfMonth === (int)$configuredDay) {
            $senderName = $notificationSettings['sender_name'];

            return [
                'type' => 'info',
                'title' => 'Monthly Counterpart Payment Reminder',
                'message' => "<strong>Reminder!</strong><br><br>Today is the configured reminder day of the month. A gentle reminder to kindly settle your counterpart contribution. These timely payments help sustain the support and resources provided through PN Philippines. Thank you!<br><br>From: <strong>{$senderName}</strong>",
                'action_text' => 'Submit Payment',
                'action_url' => route('student.studentUpload')
            ];
        }

        return null; 
    }

    /**
     * Create payment warning notification - only once per month on configured day
     * Now uses dynamic settings from General Settings and supports both email and dashboard
     */
    private function createPaymentWarningNotification($userId, $paymentWarning, $student)
    {
        // Get dynamic settings
        $monthlySettings = \App\Models\FinanceSetting::getMonthlyReminderSettings();
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();

        // Check if any notification method is enabled
        if (!$notificationSettings['dashboard'] && !$notificationSettings['email'] && !$notificationSettings['student_account']) {
            return; // All notification methods disabled in settings
        }

        $currentDate = \Carbon\Carbon::now();
        $currentMonth = $currentDate->format('Y-m');

        // Use the passed student object
        $remainingBalance = $student ? $student->remaining_balance : 0;

        // Get grace period setting and check if student actually needs reminder
        $gracePeriodMonths = \App\Models\FinanceSetting::get('payment_grace_period_months', 2);

        // Check if student has made payment within grace period
        $targetStudentId = $student ? $student->student_id : null;
        $hasPaymentInGracePeriod = \App\Models\Payment::where('student_id', $targetStudentId)
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->where('payment_date', '>=', $currentDate->copy()->subMonths($gracePeriodMonths))
            ->exists();

        // If student has paid within grace period, don't send reminder
        if ($hasPaymentInGracePeriod || $remainingBalance <= 0) {
            \Log::info('Student does not need reminder', [
                'user_id' => $userId,
                'has_recent_payment' => $hasPaymentInGracePeriod,
                'remaining_balance' => $remainingBalance,
                'grace_period_months' => $gracePeriodMonths
            ]);
            return; // Student doesn't need reminder
        }

        // Check if a payment warning notification was already created TODAY (configured reminder day)
        $today = $currentDate->toDateString(); // Get today's date (Y-m-d format)
        $existingNotification = CustomNotification::where('user_id', $userId)
            ->where('type', 'payment_warning')
            ->whereDate('created_at', $today)
            ->first();

        if (!$existingNotification) {
            // Get student full name with debugging
            $studentName = $student ? $student->first_name . ' ' . $student->last_name : 'Student';
            $senderName = $notificationSettings['sender_name'];
            $configuredDay = $monthlySettings['day'];
            $dayText = $this->getOrdinalNumber($configuredDay);

            // Debug log
            \Log::info('Creating notification for student', [
                'student_object' => $student ? 'EXISTS' : 'NULL',
                'student_name' => $studentName,
                'first_name' => $student ? $student->first_name : 'N/A',
                'last_name' => $student ? $student->last_name : 'N/A',
                'email_enabled' => $notificationSettings['email'],
                'dashboard_enabled' => $notificationSettings['dashboard'],
                'student_account_enabled' => $notificationSettings['student_account']
            ]);

            // Compute unpaid months based on batch start and monthly fee (same logic as reminders)
            $batchYear = $student->batch;
            $paymentStartSettings = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);
            if ($paymentStartSettings === null) {
                // Fallback to global counterpart start settings to keep behavior dynamic
                $globalStart = \App\Models\FinanceSetting::getCounterpartPaymentStartSettings();
                $startMonth = $globalStart['start_month'];
                $startYear = $globalStart['start_year'];
            } else {
                $startMonth = $paymentStartSettings['start_month'];
                $startYear = $paymentStartSettings['start_year'];
            }
            $startDate = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);

            // Monthly fee (batch-specific override or default)
            $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
            $batchSettings = json_decode($batchSettingsJson, true) ?: [];
            $monthlyFee = $batchSettings['monthly_default'] ?? 500;

            // If payment period hasn't started yet, do not create a reminder
            if ($currentDate->lt($startDate)) {
                \Log::info('Skipping monthly modal/notification: payment period not started yet', [
                    'user_id' => $userId,
                    'batch' => $batchYear,
                    'start' => $startDate->toDateString(),
                    'today' => $currentDate->toDateString()
                ]);
                return; // Exit without creating notifications or sending email
            }

            $totalPaid = \App\Models\Payment::where('student_id', $student->student_id)
                ->whereIn('status', ['Approved', 'Added by Finance'])
                ->sum('amount');

            $monthsSinceStart = $startDate->diffInMonths($currentDate->startOfMonth()) + 1;
            $monthsShouldBePaid = max(0, $monthsSinceStart);
            $monthsPaid = (int) floor($totalPaid / $monthlyFee);

            $unpaidMonths = [];
            if ($monthsShouldBePaid > $monthsPaid) {
                for ($i = $monthsPaid; $i < $monthsShouldBePaid; $i++) {
                    $monthDate = $startDate->copy()->addMonths($i);
                    $unpaidMonths[] = [
                        'display' => $monthDate->format('F Y'),
                    ];
                }
            }

            // Build unified templated message (same style as email and counterpart reminders)
            $nameForGreeting = $student->user_fname . ' ' . $student->user_lname;
            $unpaidMonthsDisplay = array_map(function($m) { return $m['display']; }, $unpaidMonths);
            $unpaidMonthsCount = count($unpaidMonthsDisplay);

            $message = "<strong>Dear {$nameForGreeting},</strong><br><br>";
            if ($unpaidMonthsCount > 0) {
                $monthsText = $unpaidMonthsCount === 1 ? 'month' : 'months';
                $firstMonth = $unpaidMonthsDisplay[0];
                $lastMonth = $unpaidMonthsDisplay[$unpaidMonthsCount - 1];
                $rangeText = $unpaidMonthsCount === 1 ? $firstMonth : "from {$firstMonth} to {$lastMonth}";
                $message .= "We've noticed that your parent counterpart payment has not been settled for the past {$unpaidMonthsCount} {$monthsText}—{$rangeText}. This is a gentle reminder to arrange payment for these {$monthsText}.<br><br>";
                $message .= "<strong>Unpaid Months:</strong> <span style='color: #d9534f; font-weight: bold;'>" . implode(', ', $unpaidMonthsDisplay) . "</span><br>";
            } else {
                $message .= "We've noticed that your parent counterpart payment has an outstanding balance. This is a gentle reminder to arrange payment.<br><br>";
            }
            $message .= "<strong>Outstanding Balance:</strong> ₱" . number_format($remainingBalance, 2) . "<br><br>";
            $message .= "Please inform your parent or guardian about these unpaid months and make arrangements to settle the balance as soon as possible. Your cooperation ensures the continued strength and service of PN Philippines.<br><br>";
            $message .= "Best regards,<br><strong>Finance Department</strong>";

            // 1. Create dashboard notification if enabled (for modal popup)
            if ($notificationSettings['dashboard']) {
                CustomNotification::create([
                    'user_id' => $userId,
                    'type' => 'payment_warning', // This type triggers dashboard modal
                    'title' => 'Monthly Counterpart Payment Reminder',
                    'message' => $message,
                    'is_read' => 0
                ]);

                \Log::info('Dashboard notification (modal) created for student', ['user_id' => $userId]);
            }

            // 2. Create student account notification if enabled (for notifications page)
            if ($notificationSettings['student_account']) {
                CustomNotification::create([
                    'user_id' => $userId,
                    'type' => 'payment_reminder', // This type goes to notifications page
                    'title' => 'Monthly Counterpart Payment Reminder',
                    'message' => $message,
                    'is_read' => 0
                ]);

                \Log::info('Student account notification (notifications page) created for student', ['user_id' => $userId]);
            }

            // 3. Send email notification if enabled
            if ($notificationSettings['email'] && $student->user_email) {
                try {
                    $reminderData = [
                        'months_unpaid' => $unpaidMonthsDisplay,
                        'configured_day' => $configuredDay,
                        'current_date' => $currentDate->format('F j, Y')
                    ];

                    \Mail::to($student->user_email)->send(new \App\Mail\PaymentReminderMail(
                        $student,
                        'monthly',
                        $senderName,
                        $reminderData,
                        $message
                    ));

                    \Log::info('Email reminder sent to student', [
                        'user_id' => $userId,
                        'email' => $student->user_email,
                        'sender' => $senderName
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send email reminder', [
                        'user_id' => $userId,
                        'email' => $student->user_email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Helper function to get ordinal number (1st, 2nd, 3rd, etc.)
     */
    private function getOrdinalNumber($number)
    {
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }

    public function getDashboardPayments()
    {
        $student = session('user');

        if (!$student) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Find local user to get student details
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || !$localUser->studentDetails) {
            return response()->json(['error' => 'Student details not found'], 404);
        }

        $payments = $localUser->studentDetails->payments()
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->orderBy('payment_date')
            ->get()
            ->groupBy(function($payment) {
                return $payment->payment_date->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'year' => $group->first()->payment_date->format('Y'),
                    'month' => $group->first()->payment_date->format('m'),
                    'total' => $group->sum('amount')
                ];
            })
            ->values();

        return response()->json($payments);
    }

    public function deleteNotification(CustomNotification $notification)
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // Ensure the user can only delete their own notifications
        if ($notification->user_id !== $student['user_id']) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $notification->delete();
        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }

    /**
     * Build matrix data for a single student using the same rules as finance matrix
     */
    private function buildStudentMatrixData($studentDetails)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

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

        // Determine months to display based on payable amount and monthly fee
        // Batch-specific monthly default value (with override)
        $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
        $batchSettings = json_decode($batchSettingsJson, true) ?: [];
        $monthlyFee = $batchSettings['monthly_default'] ?? 500;

        // Calculate total months needed from total_due
        $totalDue = optional($studentDetails->batchInfo)->total_due ?? 0;
        $monthsRequired = $monthlyFee > 0 ? (int) ceil($totalDue / $monthlyFee) : 12;
        // Ensure at least 12 months are shown for UX consistency
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

        $payments = $studentDetails->payments->filter(function ($payment) {
            return in_array($payment->status, ['Approved', 'Added by Finance']);
        });

        // monthlyFee already computed above; reuse here

        // Sort payments by date
        $sortedPayments = $payments->sortBy(function ($payment) {
            return \Carbon\Carbon::parse($payment->payment_date);
        });

        $totalPaid = $sortedPayments->sum('amount');
        $remainingAmount = $totalPaid;

        // Allocate payments sequentially from start date across months
        $sortedMonthlyData = collect($studentData['monthly_data'])->sortBy(function ($monthData, $monthKey) {
            return $monthData['year'] * 10000 + $monthData['month'];
        });

        foreach ($sortedMonthlyData as $monthKey => $monthData) {
            if ($remainingAmount <= 0) break;

            if ($monthData['year'] < $startYear || ($monthData['year'] == $startYear && $monthData['month'] < $startMonth)) {
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

        // Attach payment details to their actual months (for tooltips/details)
        foreach ($sortedPayments as $payment) {
            $paymentDate = \Carbon\Carbon::parse($payment->payment_date);
            $monthKey = $paymentDate->month . '_' . $paymentDate->year;
            if (isset($studentData['monthly_data'][$monthKey])) {
                if (!isset($studentData['monthly_data'][$monthKey]['payments'])) {
                    $studentData['monthly_data'][$monthKey]['payments'] = [];
                }
                $studentData['monthly_data'][$monthKey]['payments'][] = [
                    'amount' => $payment->amount,
                    'date' => $payment->payment_date,
                    'mode' => $payment->payment_mode ?? 'Cash'
                ];
            }
        }

        return $studentData;
    }
}