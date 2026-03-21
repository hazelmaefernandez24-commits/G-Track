<!-- use Barryvdh\DomPDF\Facade\Pdf;

public function downloadReceipt($paymentId)
{
    $payment = Payment::findOrFail($paymentId);
    $student = $payment->student;

    $pdf = Pdf::loadView('receipt', compact('payment', 'student'));

    return $pdf->download('acknowledgement_receipt.pdf');
} -->
