<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Determine the notification delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database']; // Send via email and store in the database
    }

    /**
     * Build the email representation of the notification.
     */
    public function toMail($notifiable)
    {
        $amount = number_format($this->payment->amount, 2);
        $date = $this->payment->payment_date->format('Y-m-d');

        return (new MailMessage)
            ->subject('New Payment Proof Uploaded')
            ->line('A new payment proof has been uploaded by ' . $this->payment->student->first_name . ' ' . $this->payment->student->last_name . '.')
            ->line('Amount: ₱' . $amount)
            ->line('Date: ' . $date)
            ->action('Review Payment', url('/finance/notifications'))
            ->line('Please review and take action.');
    }

    /**
     * Store the notification in the database.
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'A new payment proof has been uploaded by ' . $this->payment->student->first_name . ' ' . $this->payment->student->last_name,
            'payment_id' => $this->payment->payment_id,
            'status' => $this->payment->status,
            'amount' => $this->payment->amount,
            'payment_date' => $this->payment->payment_date,
            'reference_number' => $this->payment->reference_number,
            'remarks' => $this->payment->remarks,
        ];
    }
}