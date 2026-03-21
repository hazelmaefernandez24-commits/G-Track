<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CashierController;
use App\Http\Middleware\SubsystemAuth;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'dashboard']);
Route::get('/login', function () {
    return redirect()->to(env('MAIN_SYSTEM_URL') . '/');
})->name('login');
Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::middleware(['auth', 'role:student'])->group(function () {
    });
Route::middleware([SubsystemAuth::class])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.studentDashboard');

    Route::get('/student', function () {
        $student = session('user');
        return view('student.studentUpload', compact('student'));
    })->name('student.studentUpload');
    Route::get('/student/payments', [StudentController::class, 'paymentHistory'])->name('student.studentPayments');

    Route::get('/student/payment', [StudentController::class, 'paymentForm'])->name('student.paymentForm');
    Route::post('/student/payments/upload', [StudentController::class, 'uploadPaymentProof'])->name('student.uploadPaymentProof');
    Route::get('/student/profile', [StudentController::class, 'profile'])->name('student.profile');

});

Route::get('/student/dashboard/payments', [StudentController::class, 'getDashboardPayments'])
    ->name('student.dashboard.payments');

Route::middleware([SubsystemAuth::class, 'role:student'])->group(function () {
    Route::get('/student/notifications', [StudentController::class, 'notifications'])
        ->name('student.notifications');
    Route::delete('/student/notifications/{notification}', [StudentController::class, 'deleteNotification'])
        ->name('student.notifications.delete');
});

Route::middleware([SubsystemAuth::class])->group(function () {
    Route::get('/finance/dashboard', [FinanceController::class, 'dashboard'])->name('finance.financeDashboard');

    Route::get('/finance/reports', function () {
        return view('finance.financeReports');
    })->name('finance.financeReports');

    Route::get('/finance/profile', [FinanceController::class, 'profile'])->name('finance.profile');

    Route::get('/finance/payments', [FinanceController::class, 'managePayments'])->name('finance.financePayments');
    Route::get('/finance/payments/history/{studentId}', [FinanceController::class, 'getPaymentHistory'])->name('finance.getPaymentHistory');
    Route::get('/finance/payments/history/{studentId}/download', [FinanceController::class, 'downloadStudentPaymentHistory'])->name('finance.downloadStudentPaymentHistory');
    Route::post('/finance/payments/add', [FinanceController::class, 'addPayment'])->name('finance.addPayment');
    Route::post('/finance/payments/verify/{payment}', [FinanceController::class, 'verifyPayment'])->name('finance.verifyPayment');
    Route::put('/finance/payments/edit/{payment}', [FinanceController::class, 'editPayment'])->name('finance.editPayment');
    Route::delete('/finance/payments/delete/{payment}', [FinanceController::class, 'deletePayment'])->name('finance.deletePayment');
    Route::get('/finance/history', [FinanceController::class, 'history'])->name('finance.history');
    Route::get('/finance/payment-history', [FinanceController::class, 'paymentHistory'])->name('finance.payment-history');
    Route::post('/finance/update-batch', [FinanceController::class, 'updateBatch'])->name('finance.updateBatch');
    Route::get('/finance/batch/{batchYear}/payment-start-settings', [FinanceController::class, 'getBatchPaymentStartSettings'])->name('finance.batch.paymentStartSettings');
    Route::get('/finance/debug/batch/{batchYear}/settings', [FinanceController::class, 'debugBatchSettings'])->name('finance.debug.batchSettings');
    Route::post('/finance/fix-batch-2025-settings', [FinanceController::class, 'fixBatch2025Settings'])->name('finance.fix.batch2025');
    Route::get('/finance/fix-batch-2025-settings', [FinanceController::class, 'fixBatch2025Settings'])->name('finance.fix.batch2025.get');
    Route::get('/finance/test-payment-allocation', [FinanceController::class, 'testPaymentAllocation'])->name('finance.test.paymentAllocation');
    Route::get('/finance/notifications', [FinanceController::class, 'notifications'])->name('finance.notifications');
    Route::get('/finance/monthly-payments', [FinanceController::class, 'getMonthlyPayments'])->name('finance.monthly-payments');
    Route::get('/finance/yearly-payments', [FinanceController::class, 'getYearlyPayments'])->name('finance.yearly-payments');
    Route::get('/finance/batch-monthly-payments', [FinanceController::class, 'getBatchDifferentiatedMonthlyPayments'])->name('finance.batch-monthly-payments');
    Route::get('/finance/batch-yearly-payments', [FinanceController::class, 'getBatchDifferentiatedYearlyPayments'])->name('finance.batch-yearly-payments');
    Route::get('/finance/yearly-trends-by-month', [FinanceController::class, 'getYearlyTrendsByMonth'])->name('finance.yearly-trends-by-month');
    Route::get('/finance/target-vs-accomplishment', [FinanceController::class, 'getTargetVsAccomplishment'])->name('finance.target-vs-accomplishment');
    Route::get('/finance/available-years', [FinanceController::class, 'getAvailableYears'])->name('finance.available-years');
    
    Route::get('/finance/reports', function () {
        $batches = \App\Models\Batch::all(); // Fetch all batches
        return view('finance.financeReports', compact('batches'));
    })->name('finance.financeReports');
    Route::post('/finance/reports/generate', [FinanceController::class, 'generateReport'])->name('finance.reports.generate');
    Route::post('/finance/reports/download', [FinanceController::class, 'downloadReport'])->name('finance.downloadReport');
    Route::post('/finance/reports/payment-mode-totals', [FinanceController::class, 'getPaymentModeTotals'])->name('finance.paymentModeTotals');
    Route::get('/finance/reports', [FinanceController::class, 'reports'])->name('finance.financeReports');
    Route::get('/finance/summary-data', [FinanceController::class, 'getSummaryData'])->name('finance.summaryData');
    Route::get('/finance/dashboard-payment-modes', [FinanceController::class, 'getDashboardPaymentModes'])->name('finance.dashboardPaymentModes');
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::get('/payment-methods/{paymentMethod}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    Route::get('/notifications/unread-count', [FinanceController::class, 'getUnreadCount']);

    // Finance Settings Routes (NEW - General Settings System)
    Route::get('/finance/settings', [FinanceController::class, 'settings'])->name('finance.settings');
    Route::get('/finance/settings/load', [FinanceController::class, 'loadSettings'])->name('finance.settings.load');
    Route::post('/finance/settings/save', [FinanceController::class, 'saveSettings'])->name('finance.settings.save');
    Route::get('/finance/batches/available', [FinanceController::class, 'getAvailableBatches'])->name('finance.batches.available');
    Route::get('/finance/batches/{batchYear}/settings', [FinanceController::class, 'getBatchSettings'])->name('finance.batches.get');
    Route::post('/finance/batches/{batchYear}/settings/save', [FinanceController::class, 'saveBatchSettings'])->name('finance.batches.save');
    Route::delete('/finance/batches/{batchYear}/settings/clear', [FinanceController::class, 'clearBatchSettings'])->name('finance.batches.clear');
    Route::get('/finance/audit-logs', [FinanceController::class, 'getAuditLogs'])->name('finance.audit.logs');

    // Payment Reminder Routes
    Route::post('/finance/payment-reminders/send', [FinanceController::class, 'triggerPaymentReminders'])->name('finance.payment-reminders.send');
    Route::get('/finance/overdue-students/summary', [FinanceController::class, 'getOverdueStudentsSummary'])->name('finance.overdue-students.summary');
    Route::get('/finance/payment-reminders/test', [FinanceController::class, 'testPaymentReminders'])->name('finance.payment-reminders.test');
    Route::post('/finance/payment-reminders/send-test', [FinanceController::class, 'sendTestNotification'])->name('finance.payment-reminders.send-test');
    

    // Payment routes with prefix
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::delete('/delete-payment/{id}', [FinanceController::class, 'deletePayment'])->name('deletePayment');
        // ...other finance routes...
    });
});

// Cashier Routes (RBAC - uses same finance views with limited access)
Route::middleware([SubsystemAuth::class])->group(function () {
    Route::get('/cashier/dashboard', [CashierController::class, 'dashboard'])->name('cashier.dashboard');
    Route::get('/cashier/payments', [CashierController::class, 'payments'])->name('cashier.payments');
    Route::get('/cashier/reports', [CashierController::class, 'reports'])->name('cashier.reports');
    Route::get('/cashier/notifications', [CashierController::class, 'notifications'])->name('cashier.notifications');
    Route::post('/cashier/payments/verify/{payment}', [CashierController::class, 'verifyPayment'])->name('cashier.verifyPayment');
});
Route::get('/notifications/{notification}/download-receipt', [NotificationController::class, 'downloadReceipt'])
    ->name('notifications.downloadReceipt');

// Public route for viewing payment methods
Route::get('/view-payment-methods', [PaymentMethodController::class, 'show'])->name('payment-methods.show');

require __DIR__ . '/auth.php';
