<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SosAlertController;
use App\Http\Controllers\StudentMessageController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\ClassManagementController;
use App\Http\Controllers\AdminManagementController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// --- PUBLIC ROUTES ---
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Added GET for convenience if needed

Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1')
    ->name('reset-password.store');

// --- PROTECTED ROUTES (Requires Login) ---
Route::middleware(['auth:admin'])->group(function () {
    Route::put('/account/profile', [AuthController::class, 'updateProfile'])
        ->name('account.profile.update');
    Route::put('/account/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:5,1')
        ->name('account.password.update');
    
    Route::get('/dashboard', [DeviceController::class, 'index'])->name('dashboard');
    Route::get('/tracking', [DeviceController::class, 'tracking']);
    Route::get('/activity', [DeviceController::class, 'activity']);
    Route::get('/students/{id}/history', [StudentManagementController::class, 'history'])->name('students.history');

    // Notifications
    Route::get('/notifications/{screen?}', [NotificationController::class, 'index'])
        ->whereIn('screen', ['alerts', 'messages', 'broadcasts'])
        ->name('notifications.screen');
    Route::post('/notifications/send', [BroadcastController::class, 'send']);
    Route::post('/notifications/delete-all-sos-archives', [SosAlertController::class, 'deleteArchives'])->name('notifications.delete-all-sos');
    Route::post('/notifications/{id}/acknowledge', [SosAlertController::class, 'acknowledge']);
    Route::post('/notifications/{id}/resolve', [SosAlertController::class, 'resolve']);
    Route::get('/notifications/{id}/read', [StudentMessageController::class, 'read']);
    Route::post('/notifications/{id}/reply', [StudentMessageController::class, 'reply']);

    // Messenger
    Route::get('/messages/{student_id}/json', [StudentMessageController::class, 'getMessagesJson']);
    Route::post('/messages/new/{student_id}', [StudentMessageController::class, 'sendMessageAjax']);
    Route::delete('/messages/{student_id}', [StudentMessageController::class, 'deleteConversation']);

    // All students JSON for modal
    Route::get('/students/all/json', [StudentMessageController::class, 'allStudentsJson']);

    // --- MANAGEMENT ROUTES (Main Admin Only) ---
    Route::middleware(['admin.role:main'])->prefix('admin')->group(function () {
        Route::resource('students', StudentManagementController::class);
        Route::resource('classes', ClassManagementController::class);
        Route::resource('admins', AdminManagementController::class);
    });
});
