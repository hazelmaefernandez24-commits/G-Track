<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSubmissionNotification extends Notification
{
    use Queueable;

    private $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (is_array($this->payment)) {
            return ['database'];
        }
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if (is_array($this->payment)) {
            return (new MailMessage)
                ->subject('Important: Batch Payment Update')
                ->greeting('Dear Batch ' . $this->payment['batch_year'])
                ->line('Good day Students!')
                ->line('')
                ->line('Please be informed that the payable amount for Parents\' Counterpart for your batch has been updated from ₱' . number_format($this->payment['old_amount'] ?? 0, 2) . ' to ₱' . number_format($this->payment['new_amount'], 2) . '.')
                ->line('')
                ->line('Kindly review the updated details at your earliest convenience. Should you have any questions or require further clarification, please do not hesitate to reach out to the finance office.')
                ->line('')
                ->line('We appreciate you staying up-to-date with this adjustment!')
                ->salutation('Best regards,<br>Finance Department');
        }

        return (new MailMessage)
            ->subject('Payment Submission Confirmation')
            ->greeting('Hello!')
            ->line('Your payment has been successfully processed.')
            ->line('')
            ->line('💰 Amount: ₱' . number_format($this->payment->amount ?? $this->payment['new_amount'], 2))
            ->line('📅 Date: ' . ($this->payment->payment_date ?? $this->payment['payment_date']))
            ->line('🔢 Reference Number: ' . ($this->payment->reference_number ?? 'N/A'))
            ->line('📊 Status: ' . ($this->payment->status ?? 'Updated'))
            ->line('')
            ->action('View Payment History', url('/student/payments'))
            ->line('')
            ->line('Thank you for your payment!')
            ->salutation('Best regards,<br>Finance Department');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if (is_array($this->payment)) {
            return [
                'message' => "Dear Batch " . $this->payment['batch_year'] . "\n\n" .
                           "Good day Students! Please be informed that the payable amount for Parents' Counterpart for your batch has been updated from <strong>₱" . 
                           number_format($this->payment['old_amount'] ?? 0, 2) . "</strong> to <strong>₱" . 
                           number_format($this->payment['new_amount'], 2) . "</strong>.\n\n" .
                           "Kindly review the updated details at your earliest convenience. Should you have any questions or require further clarification, please do not hesitate to reach out to the finance office.\n\n" .
                           "We appreciate you staying up-to-date with this adjustment!\n\n" .
                           "Best regards,\nFinance Department",
                'type' => 'batch_update',
                'batch_year' => $this->payment['batch_year'],
                'new_amount' => $this->payment['new_amount'],
                'old_amount' => $this->payment['old_amount'] ?? 0,
                'status' => 'Updated',
                'payment_date' => $this->payment['payment_date']
            ];
        }

        return [
            'message' => 'Your payment of <strong>₱' . number_format($this->payment->amount ?? $this->payment['new_amount'], 2) . '</strong> has been submitted successfully.',
            'amount' => $this->payment->amount ?? $this->payment['new_amount'],
            'payment_date' => $this->payment->payment_date ?? $this->payment['payment_date'],
            'reference_number' => $this->payment->reference_number ?? 'N/A',
            'status' => $this->payment->status ?? 'Updated'
        ];
    }
}
