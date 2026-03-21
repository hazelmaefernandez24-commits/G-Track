<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentDeclinedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $reason;

    public function __construct($payment, $reason)
    {
        $this->payment = $payment;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Your Uploaded Proof of Payment Declined')
                    ->view('emails.payment-declined')
                    ->with([
                        'payment' => $this->payment,
                        'reason' => $this->reason,
                    ]);
    }
}