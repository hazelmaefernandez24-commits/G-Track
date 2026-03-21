<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $studentDetails;
    private $unpaidMonths;
    private $remainingBalance;
    private $monthlyFee;
    private $delayMonths;

    public function __construct($studentDetails, $unpaidMonths, $remainingBalance, $monthlyFee, $delayMonths)
    {
        $this->studentDetails = $studentDetails;
        $this->unpaidMonths = $unpaidMonths;
        $this->remainingBalance = $remainingBalance;
        $this->monthlyFee = $monthlyFee;
        $this->delayMonths = $delayMonths;
    }

    /**
     * Determine the notification delivery channels.
     */
    public function via($notifiable)
    {
        $methods = [];
        
        // Check notification method settings
        if (\App\Models\FinanceSetting::get('notification_method_database', true)) {
            $methods[] = 'database';
        }
        
        if (\App\Models\FinanceSetting::get('notification_method_email', true)) {
            $methods[] = 'mail';
        }
        
        return $methods;
    }

    /**
     * Build the email representation of the notification.
     */
    public function toMail($notifiable)
    {
        $studentName = $this->studentDetails->first_name . ' ' . $this->studentDetails->last_name;
        $monthsText = $this->formatUnpaidMonthsText();
        $urgencyLevel = $this->delayMonths >= 3 ? 'URGENT' : 'IMPORTANT';
        
        $subject = $urgencyLevel . ': Payment Reminder - ' . $this->delayMonths . ' Month' . ($this->delayMonths > 1 ? 's' : '') . ' Overdue';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . $studentName . ',')
            ->line($this->getMainMessage())
            ->line('**Payment Details:**')
            ->line('• Remaining Balance: ₱' . number_format($this->remainingBalance, 2))
            ->line('• Monthly Fee: ₱' . number_format($this->monthlyFee, 2))
            ->line('• Unpaid Months: ' . $monthsText)
            ->line('• Total Months Overdue: ' . $this->delayMonths . ' month' . ($this->delayMonths > 1 ? 's' : ''))
            ->line('Please settle your payment as soon as possible to avoid any inconvenience.')
            ->line('If you have any questions, please contact the Finance Department.')
            ->salutation('Best regards, Finance Department');
    }

    /**
     * Store the notification in the database.
     */
    public function toArray($notifiable)
    {
        $monthsText = $this->formatUnpaidMonthsText();
        
        return [
            'type' => 'payment_reminder',
            'title' => 'Payment Reminder - ' . $this->delayMonths . ' Month' . ($this->delayMonths > 1 ? 's' : '') . ' Overdue',
            'message' => $this->getMainMessage(),
            'student_id' => $this->studentDetails->student_id,
            'remaining_balance' => $this->remainingBalance,
            'monthly_fee' => $this->monthlyFee,
            'unpaid_months' => $monthsText,
            'delay_months' => $this->delayMonths,
            'urgency_level' => $this->delayMonths >= 3 ? 'urgent' : 'important',
            'payment_details' => [
                'remaining_balance' => $this->remainingBalance,
                'monthly_fee' => $this->monthlyFee,
                'unpaid_months_count' => count($this->unpaidMonths),
                'unpaid_months_list' => $this->unpaidMonths
            ]
        ];
    }

    /**
     * Get the main notification message based on delay period
     */
    private function getMainMessage()
    {
        $monthsText = $this->formatUnpaidMonthsText();
        
        if ($this->delayMonths >= 3) {
            return "URGENT: You haven't paid for {$this->delayMonths} months. Your account is significantly overdue for the following months: {$monthsText}. Immediate payment is required to avoid further consequences.";
        } else {
            return "IMPORTANT: You haven't paid for {$this->delayMonths} months. Please settle your payment for the following months: {$monthsText}.";
        }
    }

    /**
     * Format unpaid months into readable text
     */
    private function formatUnpaidMonthsText()
    {
        if (empty($this->unpaidMonths)) {
            return 'N/A';
        }

        if (count($this->unpaidMonths) == 1) {
            return $this->unpaidMonths[0]['display'];
        }

        if (count($this->unpaidMonths) == 2) {
            return $this->unpaidMonths[0]['display'] . ' and ' . $this->unpaidMonths[1]['display'];
        }

        // For more than 2 months
        $monthNames = array_map(function($month) {
            return $month['display'];
        }, $this->unpaidMonths);

        $lastMonth = array_pop($monthNames);
        return implode(', ', $monthNames) . ', and ' . $lastMonth;
    }
}
