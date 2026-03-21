<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\CustomNotification;

class CashierController extends Controller
{
    public function dashboard()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access dashboard.');
        }

        // Find local user to verify cashier role
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || $localUser->user_role !== 'cashier') {
            return back()->with('error', 'Access denied. Cashier role required.');
        }

        // Redirect to finance dashboard with cashier role indicator
        return redirect()->route('finance.financeDashboard')->with('user_role', 'cashier');
    }

    public function payments()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access payments.');
        }

        // Find local user to verify cashier role
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || $localUser->user_role !== 'cashier') {
            return back()->with('error', 'Access denied. Cashier role required.');
        }

        // Redirect to finance payments with cashier role indicator
        return redirect()->route('finance.financePayments')->with('user_role', 'cashier');
    }

    public function reports()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access reports.');
        }

        // Find local user to verify cashier role
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || $localUser->user_role !== 'cashier') {
            return back()->with('error', 'Access denied. Cashier role required.');
        }

        // Redirect to finance reports with cashier role indicator
        return redirect()->route('finance.financeReports')->with('user_role', 'cashier');
    }

    public function notifications()
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to access notifications.');
        }

        // Find local user to verify cashier role
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || $localUser->user_role !== 'cashier') {
            return back()->with('error', 'Access denied. Cashier role required.');
        }

        // Get all payments for cashier to see (excluding "Added by Finance" ones)
        $pendingPayments = Payment::with(['student.user'])
            ->where('status', '!=', 'Added by Finance')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get notifications for cashier
        $notifications = CustomNotification::where('user_id', $localUser->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Mark all notifications as read (parity with finance notifications page)
        CustomNotification::where('user_id', $localUser->user_id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return view('finance.notifications', compact('pendingPayments', 'notifications'))->with('user_role', 'cashier');
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $student = session('user');

        if (!$student) {
            return redirect()->route('login')->with('error', 'Please log in to verify payments.');
        }

        // Find local user to verify cashier role
        $localUser = User::where('user_email', $student['user_email'])->first();
        if (!$localUser || $localUser->user_role !== 'cashier') {
            return back()->with('error', 'Access denied. Cashier role required.');
        }

        $request->validate([
            'status' => 'required|in:Approved,Declined',
            'remarks' => 'nullable|string|max:500',
        ]);

        $oldStatus = $payment->status;
        $payment->status = $request->status;
        $payment->remarks = $request->remarks;
        $payment->verified_by = $localUser->user_id;
        $payment->verified_at = now();
        $payment->save();

        // Get student details
        $studentDetails = $payment->student;
        if (!$studentDetails) {
            return back()->with('error', 'Student details not found.');
        }

        // Find the student user
        $studentUser = User::where('user_role', 'student')
            ->whereHas('studentDetails', function ($q) use ($studentDetails) {
                $q->where('student_id', $studentDetails->student_id);
            })->first();

        // Check notification settings
        $notificationSettings = \App\Models\FinanceSetting::getNotificationMethodSettings();
        $studentAccountNotificationsEnabled = $notificationSettings['student_account'] ?? true;
        $dashboardNotificationsEnabled = $notificationSettings['dashboard'] ?? true;
        
        if ($request->status === 'Approved') {
            // Notify finance users for final approval ONLY if dashboard notifications are enabled
            if ($dashboardNotificationsEnabled) {
                $financeUsers = User::where('user_role', 'finance')->get();
                foreach ($financeUsers as $financeUser) {
                    CustomNotification::create([
                        'user_id' => $financeUser->user_id,
                        'type' => 'payment_cashier_approved',
                        'title' => 'Payment Verified by Cashier',
                        'message' => 'Payment of ₱' . number_format($payment->amount, 2) . ' by ' . $studentDetails->first_name . ' ' . $studentDetails->last_name . ' has been verified by cashier and requires final finance approval.',
                        'is_read' => 0
                    ]);
                }
            }

            // Update payment status to "Cashier Approved" for finance review
            $payment->status = 'Cashier Verified';
            $payment->save();

            // Notify student ONLY if student account notifications are enabled
            if ($studentUser && $studentAccountNotificationsEnabled) {
                CustomNotification::create([
                    'user_id' => $studentUser->user_id,
                    'type' => 'payment_cashier_verified',
                    'title' => 'Payment Verified by Cashier',
                    'message' => 'Your payment of ₱' . number_format($payment->amount, 2) . ' has been verified by cashier and is now pending final finance approval.',
                    'is_read' => 0
                ]);
            }

            return back()->with('success', 'Payment verified by cashier. Sent to finance for final approval.');
        } else {
            // Payment declined by cashier - ONLY send notification if student account notifications are enabled
            if ($studentUser && $studentAccountNotificationsEnabled) {
                CustomNotification::create([
                    'user_id' => $studentUser->user_id,
                    'type' => 'payment_declined',
                    'title' => 'Payment Declined',
                    'message' => 'Your payment of ₱' . number_format($payment->amount, 2) . ' has been declined by cashier. Reason: ' . ($request->remarks ?: 'No reason provided'),
                    'is_read' => 0
                ]);
            }

            return back()->with('success', 'Payment declined. Student has been notified.');
        }
    }
} 