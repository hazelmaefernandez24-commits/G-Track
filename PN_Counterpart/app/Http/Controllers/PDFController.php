<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PDFController; // Add this line
use Illuminate\Support\Facades\Route;

class PDFController extends Controller
{
    public function generatePaymentReceipt(Payment $payment)
    {
        $student = $payment->student;
        
        $pdf = PDF::loadView('pdf.payment-receipt', compact('payment', 'student'));
        
        return $pdf->download('payment_receipt_' . $payment->payment_id . '.pdf');
    }
}