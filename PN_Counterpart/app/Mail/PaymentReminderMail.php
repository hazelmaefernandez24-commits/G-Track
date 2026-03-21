<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $reminderType;
    public $senderName;
    public $reminderData;
    public $detailedMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($student, $reminderType = 'monthly', $senderName = 'Finance Department', $reminderData = [], $detailedMessage = null)
    {
        $this->student = $student;
        $this->reminderType = $reminderType;
        $this->senderName = $senderName;
        $this->reminderData = $reminderData;
        $this->detailedMessage = $detailedMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->reminderType === 'monthly'
            ? 'Monthly Payment Reminder - PN Philippines'
            : 'Payment Reminder - PN Philippines';

        // Get finance department email from settings
        $financeEmail = \App\Models\FinanceSetting::get('finance_department_email', 'finance@pnphilippines.com');

        return new Envelope(
            subject: $subject,
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($financeEmail, 'Finance Department'),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
