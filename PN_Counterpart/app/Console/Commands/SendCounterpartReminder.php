<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CustomNotification;
use App\Models\StudentDetails;
use App\Models\FinanceSetting;
use Carbon\Carbon;

class SendCounterpartReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counterpart:remind-students {--test-date= : Test with a specific date (YYYY-MM-DD)} {--dry-run : Show what would happen without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly counterpart payment reminder to students with outstanding balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get settings from database - using dynamic general settings
        $monthlySettings = FinanceSetting::getMonthlyReminderSettings();
        $notificationSettings = FinanceSetting::getNotificationMethodSettings();
        $reminderSettings = FinanceSetting::getPaymentReminderSettings();

        // Check if monthly reminders are enabled
        if (!$monthlySettings['enabled']) {
            $this->info('Monthly reminders are disabled in settings.');
            return;
        }

        // Allow testing with a specific date
        $testDate = $this->option('test-date');
        $dryRun = $this->option('dry-run');
        $currentDate = $testDate ? Carbon::parse($testDate) : Carbon::now();
        $currentMonth = $currentDate->format('F Y');
        $remindersSent = 0;

        if ($testDate) {
            $this->info("🧪 Testing mode: Using date {$testDate}");
        }
        
        if ($dryRun) {
            $this->info("🔍 Dry run mode: No notifications will be sent");
        }

        // Check if today is the configured reminder day
        $reminderDay = $monthlySettings['day'];
        if ($currentDate->day != $reminderDay) {
            $this->info("Today is not the configured reminder day ({$reminderDay}). Skipping.");
            return;
        }

        $this->info("📅 Sending monthly reminders for {$currentMonth}");
        $this->info("🧠 Using smart detection logic to identify students needing reminders...");

        // Get all students with their payment details
        $students = User::where('user_role', 'student')
            ->with('studentDetails.batchInfo')
            ->get();

        // Apply smart detection logic
        $studentsNeedingReminders = $this->applySmartDetection($students, $currentDate);

        foreach ($studentsNeedingReminders as $student) {
            // Skip if student doesn't have student details
            if (!$student->studentDetails) {
                continue;
            }

            $studentDetails = $student->studentDetails;
            $remainingBalance = $studentDetails->remaining_balance;

            // Only send reminder if student has outstanding balance
            if ($remainingBalance > 0) {
                $senderName = $notificationSettings['sender_name'];

                // Enforce notification method toggles: skip if all disabled
                if (!$notificationSettings['dashboard'] && !$notificationSettings['email'] && !$notificationSettings['student_account']) {
                    continue; // Skip if all notification methods are disabled
                }

                // Enforce max reminders and follow-up interval using CustomNotification without schema changes
                $maxReminders = $reminderSettings['max_reminders'] ?? 5;
                $followUpInterval = $reminderSettings['follow_up_interval'] ?? 1; // months

                // Total reminders of type payment_reminder ever sent to this user
                $totalReminders = \App\Models\CustomNotification::where('user_id', $student->user_id)
                    ->where('type', 'payment_reminder')
                    ->count();
                if ($totalReminders >= $maxReminders) {
                    $this->line("Skipping {$student->user_fname} {$student->user_lname}: max reminders ({$maxReminders}) reached.");
                    continue;
                }

                // Prevent duplicates within follow-up interval
                $lastReminder = \App\Models\CustomNotification::where('user_id', $student->user_id)
                    ->where('type', 'payment_reminder')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($lastReminder) {
                    $lastSentAt = Carbon::parse($lastReminder->created_at);
                    if ($lastSentAt->gt($currentDate->copy()->subMonths($followUpInterval))) {
                        $this->line("Skipping {$student->user_fname} {$student->user_lname}: follow-up interval ({$followUpInterval} month/s) not yet elapsed.");
                        continue;
                    }
                }

                // Check if any notification method is enabled
                if (!$notificationSettings['dashboard'] && !$notificationSettings['email'] && !$notificationSettings['student_account']) {
                    continue; // Skip if all notification methods are disabled
                }

                // Get unpaid months for more detailed message
                $unpaidMonths = $this->getUnpaidMonths($studentDetails->student_id, $currentDate);
                $unpaidMonthsCount = count($unpaidMonths);
                
                // Create improved message with new template format
                $studentName = $student->user_fname . ' ' . $student->user_lname;
                $message = "<strong>Dear {$studentName},</strong><br><br>";
                
                if ($unpaidMonthsCount > 0) {
                    // Focus on unpaid months with new template format
                    $monthsText = $unpaidMonthsCount === 1 ? 'month' : 'months';
                    
                    // Format month range for better readability
                    if ($unpaidMonthsCount == 1) {
                        $monthRange = $unpaidMonths[0];
                    } elseif ($unpaidMonthsCount <= 4) {
                        $monthRange = "from " . $unpaidMonths[0] . " to " . end($unpaidMonths);
                    } else {
                        $monthRange = "from " . $unpaidMonths[0] . " to " . end($unpaidMonths);
                    }
                    
                    $message .= "We've noticed that your parent counterpart payment has not been settled for the past {$unpaidMonthsCount} {$monthsText}—{$monthRange}. This is a gentle reminder to arrange payment for these {$monthsText}.<br><br>";
                    
                    // Show specific unpaid months
                    $unpaidMonthsList = implode(', ', $unpaidMonths);
                    $message .= "<strong>Unpaid Months:</strong> <span style='color: #d9534f; font-weight: bold;'>{$unpaidMonthsList}</span><br>";
                    $message .= "<strong>Outstanding Balance:</strong> ₱" . number_format($remainingBalance, 2) . "<br><br>";
                    
                    $message .= "Please inform your parent or guardian about these unpaid months and make arrangements to settle the balance as soon as possible. Your cooperation ensures the continued strength and service of PN Philippines.<br><br>";
                } else {
                    // Fallback if no specific unpaid months found
                    $message .= "We've noticed that your parent counterpart payment has an outstanding balance. This is a gentle reminder to arrange payment.<br><br>";
                    $message .= "<strong>Outstanding Balance:</strong> ₱" . number_format($remainingBalance, 2) . "<br><br>";
                    $message .= "Please inform your parent or guardian about the outstanding balance and make arrangements to settle it as soon as possible. Your cooperation ensures the continued strength and service of PN Philippines.<br><br>";
                }
                
                $message .= "Best regards,<br>";
                $message .= "<strong>Finance Department</strong>";

                // Skip actual sending if dry-run mode
                if (!$dryRun) {
                    // Create single notification (combines dashboard and account notification)
                    if ($notificationSettings['dashboard'] || $notificationSettings['student_account']) {
                        CustomNotification::create([
                            'user_id' => $student->user_id,
                            'title' => 'Monthly Counterpart Payment Reminder',
                            'message' => $message,
                            'type' => 'payment_reminder', // Single type for both dashboard and notifications page
                            'is_read' => 0
                        ]);
                        $this->info("Payment reminder notification created for: {$student->user_fname} {$student->user_lname}");
                    }

                    // 3. Send email notification if enabled
                    if ($notificationSettings['email'] && $student->user_email) {
                        try {
                            $reminderData = [
                                'current_month' => $currentMonth,
                                'batch' => $studentDetails->batch
                            ];

                            $reminderData['months_unpaid'] = $unpaidMonths;
                        
                            \Mail::to($student->user_email)->send(new \App\Mail\PaymentReminderMail(
                                $student,
                                'monthly',
                                $senderName,
                                $reminderData,
                                $message // Pass the same detailed message used for notifications
                            ));

                            $this->info("Email sent to: {$student->user_email}");
                        } catch (\Exception $e) {
                            $this->error("Failed to send email to {$student->user_email}: " . $e->getMessage());
                        }
                    }
                } else {
                    // Dry run - just show what would happen
                    $this->line("🔍 Would send notifications to: {$student->user_fname} {$student->user_lname} ({$student->user_email})");
                    $this->line("   Balance: ₱" . number_format($remainingBalance, 2));
                    $this->line("   Unpaid months: " . implode(', ', $unpaidMonths));
                    $this->line("   Message preview: " . strip_tags(substr($message, 0, 100)) . "...");
                    $this->line("");
                }

                $remindersSent++;
            }
        }

        $this->info("✅ Smart reminders sent to {$remindersSent} students who really need them!");

        // Log the activity
        \Log::info("Monthly counterpart reminders sent", [
            'date' => $currentDate->toDateString(),
            'reminders_sent' => $remindersSent,
            'total_students' => $students->count(),
            'test_mode' => $testDate ? true : false
        ]);
    }

    /**
     * Apply smart detection logic to identify students who really need reminders
     * This replaces the complex Auto Detection settings with simple, effective logic
     */
    private function applySmartDetection($students, $currentDate)
    {
        // Get grace period from settings (in months)
        $gracePeriodMonths = \App\Models\FinanceSetting::get('payment_grace_period_months', 2);

        $studentsNeedingReminders = [];
        $smartCriteria = [
            'high_balance' => 0,
            'grace_period_exceeded' => 0,
            'overdue_students' => 0,
            'total_eligible' => 0
        ];

        $this->info("🎯 Using grace period: {$gracePeriodMonths} months");

        foreach ($students as $student) {
            $studentDetails = $student->studentDetails;
            if (!$studentDetails) continue;

            $remainingBalance = $studentDetails->remaining_balance ?? 0;
            $needsReminder = false;
            $reasons = [];

            // Smart Criteria 1: High remaining balance (>= ₱1000)
            if ($remainingBalance >= 1000) {
                $needsReminder = true;
                $reasons[] = "High balance: ₱" . number_format($remainingBalance, 2);
                $smartCriteria['high_balance']++;
            }

            // Smart Criteria 2: Grace period exceeded (configurable months)
            $hasPaymentInGracePeriod = \App\Models\Payment::where('student_id', $studentDetails->student_id)
                ->where('status', 'Added by Finance')
                ->where('payment_date', '>=', $currentDate->copy()->subMonths($gracePeriodMonths))
                ->exists();

            if (!$hasPaymentInGracePeriod && $remainingBalance > 0) {
                $needsReminder = true;
                $reasons[] = "No payment in last {$gracePeriodMonths} months (grace period exceeded)";
                $smartCriteria['grace_period_exceeded']++;
            }

            // Smart Criteria 3: Very overdue (no payment in grace period + 1 month)
            $veryOverdueMonths = $gracePeriodMonths + 1;
            $hasPaymentInVeryOverdue = \App\Models\Payment::where('student_id', $studentDetails->student_id)
                ->where('status', 'Added by Finance')
                ->where('payment_date', '>=', $currentDate->copy()->subMonths($veryOverdueMonths))
                ->exists();

            if (!$hasPaymentInVeryOverdue && $remainingBalance > 0) {
                $needsReminder = true;
                $reasons[] = "No payment in {$veryOverdueMonths}+ months (very overdue)";
                $smartCriteria['overdue_students']++;
            }

            // Add student if they meet any smart criteria
            if ($needsReminder) {
                $studentsNeedingReminders[] = $student;
                $smartCriteria['total_eligible']++;

                $this->line("📋 {$student->user_fname} {$student->user_lname}: " . implode(', ', $reasons));
            }
        }

        // Log smart detection results
        $this->info("🧠 Smart Detection Results:");
        $this->info("   • High balance (≥₱1000): {$smartCriteria['high_balance']} students");
        $this->info("   • Grace period exceeded ({$gracePeriodMonths}+ months): {$smartCriteria['grace_period_exceeded']} students");
        $this->info("   • Very overdue (" . ($gracePeriodMonths + 1) . "+ months): {$smartCriteria['overdue_students']} students");
        $this->info("   • Total students needing reminders: {$smartCriteria['total_eligible']} students");

        return $studentsNeedingReminders;
    }

    /**
     * Get list of unpaid months for a student with improved dynamic tracking
     */
    private function getUnpaidMonths($studentId, $currentDate)
    {
        // Get the student's batch start date or enrollment date
        $studentDetails = \App\Models\StudentDetails::where('student_id', $studentId)->first();
        if (!$studentDetails) {
            return [];
        }

        // Get batch-specific settings or fallback to global settings
        $batchYear = $studentDetails->batch;
        $batchStart = \App\Models\FinanceSetting::getBatchCounterpartPaymentStartSettings($batchYear);
        if ($batchStart === null) {
            $globalStart = \App\Models\FinanceSetting::getCounterpartPaymentStartSettings();
            $startMonth = $globalStart['start_month'];
            $startYear = $globalStart['start_year'];
        } else {
            $startMonth = $batchStart['start_month'];
            $startYear = $batchStart['start_year'];
        }

        // Determine monthly fee (batch override -> global default)
        $batchSettingsJson = \App\Models\FinanceSetting::get("batch_{$batchYear}_settings", '{}');
        $batchSettings = is_array($batchSettingsJson) ? $batchSettingsJson : json_decode($batchSettingsJson, true);
        $monthlyFee = $batchSettings['monthly_default'] ?? \App\Models\FinanceSetting::get('matrix_monthly_fee', 500);

        // If payment period hasn't started yet, nothing is due
        $startDate = Carbon::create($startYear, $startMonth, 1);
        if ($currentDate->lt($startDate)) {
            return [];
        }

        // Get all payments for this student that are approved/added
        $payments = \App\Models\Payment::where('student_id', $studentId)
            ->whereIn('status', ['Approved', 'Added by Finance'])
            ->orderBy('payment_date', 'asc')
            ->get();

        // Start from the (batch/global) payment start date up to current month
        $endDate = $currentDate->copy()->startOfMonth();
        
        // Generate all expected payment months from start to current
        $expectedMonths = [];
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
            $expectedMonths[$tempDate->format('Y-m')] = $tempDate->format('F Y');
            $tempDate->addMonth();
        }
        
        // Create payment coverage map
        $paymentCoverage = [];
        $runningBalance = 0;
        
        // Process payments chronologically and map them to months
        foreach ($payments as $payment) {
            $paymentDate = Carbon::parse($payment->payment_date);
            $runningBalance += $payment->amount;
            
            // Calculate how many months this payment covers
            $monthsCovered = floor($runningBalance / $monthlyFee);
            $runningBalance = $runningBalance % $monthlyFee; // Remainder for next calculation
            
            // Mark months as paid starting from payment date backwards to cover earliest unpaid months
            $currentCoverageDate = $paymentDate->copy()->startOfMonth();
            for ($i = 0; $i < $monthsCovered; $i++) {
                $monthKey = $currentCoverageDate->format('Y-m');
                if (isset($expectedMonths[$monthKey]) && !isset($paymentCoverage[$monthKey])) {
                    $paymentCoverage[$monthKey] = true;
                }
                $currentCoverageDate->subMonth();
            }
        }
        
        // Find unpaid months
        $unpaidMonths = [];
        foreach ($expectedMonths as $monthKey => $monthName) {
            if (!isset($paymentCoverage[$monthKey])) {
                $unpaidMonths[] = $monthName;
            }
        }
        
        return $unpaidMonths;
    }
    
}
                                                                                                                                                                                                                                                            
