<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login'); 
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('welcome');
});

use App\Models\Notification;
use Illuminate\Support\Facades\Schema;

// Remove closure, we use DeviceController for these
Route::get('/tracking', [App\Http\Controllers\DeviceController::class, 'tracking']);
Route::get('/activity', [App\Http\Controllers\DeviceController::class, 'activity']);

Route::get('/notifications', [NotificationController::class, 'index']);

Route::post('/notifications/send', [NotificationController::class, 'send']);

Route::post('/notifications/{id}/acknowledge', [NotificationController::class, 'acknowledge']);
Route::post('/notifications/{id}/resolve', [NotificationController::class, 'resolve']);
Route::get('/notifications/{id}/read', [NotificationController::class, 'read']);
Route::post('/notifications/{id}/reply', [NotificationController::class, 'reply']);

// --- NEW MESSENGER ROUTES ---
Route::get('/messages/{student_id}/json', [NotificationController::class, 'getMessagesJson']);
Route::post('/messages/new/{student_id}', [NotificationController::class, 'sendMessageAjax']);

// --- MANAGEMENT ROUTES (Main Admin Only) ---
Route::middleware(['auth:admin', 'admin.role:main'])->prefix('admin')->group(function () {
    Route::resource('students', \App\Http\Controllers\StudentManagementController::class);
    Route::resource('admins', \App\Http\Controllers\AdminManagementController::class);
});

Route::get('/dashboard', [App\Http\Controllers\DeviceController::class, 'index']);
