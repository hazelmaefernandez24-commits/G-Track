<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $receipt;

    public function __construct(Payment $payment, $receiptPath)
    {
        $this->payment = $payment;
        $this->receipt = $receiptPath; // Store the file path, not binary data
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $message = 'Finance has added a payment of ₱' . number_format($this->payment->amount, 2) . '. Click to view the receipt.';
        $message = mb_convert_encoding($message, 'UTF-8', 'auto'); // Ensure UTF-8 encoding

        return [
            'type' => 'payment_receipt',
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_date' => $this->payment->payment_date->format('Y-m-d'),
            'message' => $message,
            'title' => mb_convert_encoding('Payment Receipt Generated', 'UTF-8', 'auto'),
            'status' => $this->payment->status,
            'receipt_path' => $this->receipt, // Store the file path
        ];
    }

    public function downloadReceipt($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);

        if (!isset($notification->data['receipt_path'])) {
            abort(404, 'Receipt not found.');
        }

        $receiptPath = storage_path('app/public/' . $notification->data['receipt_path']);

        if (!file_exists($receiptPath)) {
            abort(404, 'Receipt file not found.');
        }

        return response()->download($receiptPath, 'receipt.pdf');
    }
}