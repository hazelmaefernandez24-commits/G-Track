<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $pdf;
    public $monthsText;

    public function __construct($payment, $pdf, $monthsText = null)
    {
        $this->payment = $payment;
        $this->pdf = $pdf;
        $this->monthsText = $monthsText;
    }

    public function build()
    {
        return $this->subject('Counterpart Payment')
                    ->view('emails.payment-receipt')
                    ->with(['monthsText' => $this->monthsText])
                    ->attachData($this->pdf, 'payment_receipt.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}