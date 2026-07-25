<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StudentManagementController;
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

// --- PROTECTED ROUTES (Requires Login) ---
Route::middleware(['auth:admin'])->group(function () {
    
    Route::get('/dashboard', [DeviceController::class, 'index'])->name('dashboard');
    Route::get('/tracking', [DeviceController::class, 'tracking']);
    Route::get('/activity', [DeviceController::class, 'activity']);
    Route::get('/students/{id}/history', [StudentManagementController::class, 'history'])->name('students.history');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::post('/notifications/delete-all-sos-archives', [NotificationController::class, 'deleteAllSosArchives'])->name('notifications.delete-all-sos');
    Route::post('/notifications/delete-all-blackout-archives', [NotificationController::class, 'deleteAllBlackoutArchives'])->name('notifications.delete-all-blackout');
    Route::post('/notifications/{id}/acknowledge', [NotificationController::class, 'acknowledge']);
    Route::post('/notifications/{id}/resolve', [NotificationController::class, 'resolve']);
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::post('/notifications/{id}/reply', [NotificationController::class, 'reply']);

    // Messenger
    Route::get('/messages/{student_id}/json', [NotificationController::class, 'getMessagesJson']);
    Route::post('/messages/new/{student_id}', [NotificationController::class, 'sendMessageAjax']);
    Route::delete('/messages/{student_id}', [NotificationController::class, 'deleteConversation']);

    // All students JSON for modal
    Route::get('/students/all/json', [NotificationController::class, 'allStudentsJson']);

    // --- MANAGEMENT ROUTES (Main Admin Only) ---
    Route::middleware(['admin.role:main'])->prefix('admin')->group(function () {
        Route::resource('students', StudentManagementController::class);
        Route::resource('admins', AdminManagementController::class);
    });
});
