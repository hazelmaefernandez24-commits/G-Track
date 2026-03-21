<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class NotificationController extends Controller
{
    public function downloadReceipt($notificationId)
    {
        $student = session('user');

        if (!$student) {
            abort(401, 'Unauthorized');
        }

        // Find local user to get notifications
        $localUser = \App\Models\User::where('user_email', $student['user_email'])->first();
        if (!$localUser) {
            abort(404, 'User not found');
        }

        $notification = $localUser->notifications()->findOrFail($notificationId);

        if (!isset($notification->data['receipt_path'])) {
            abort(404, 'Receipt not found.');
        }

        $receiptPath = storage_path('app/public/' . $notification->data['receipt_path']);

        if (!file_exists($receiptPath)) {
            abort(404, 'Receipt file not found.');
        }

        return response()->download($receiptPath, 'receipt.pdf');
    }

    public function generateReceipt($payment)
    {
        $pdf = Pdf::loadView('pdf.payment_receipt', ['payment' => $payment]);
        $filename = 'receipts/receipt_' . $payment->payment_id . '.pdf';
        $pdf->save(storage_path('app/public/' . $filename));

        // Notify the student with the file path
        $user->notify(new \App\Notifications\PaymentReceiptNotification($payment, $filename));
    }
}