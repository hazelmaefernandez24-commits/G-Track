<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StatusController;


Route::middleware('api')->group(function () {

    // 🔵 AUTH: Using the API methods
    Route::post('/login', [AuthController::class, 'apiLogin']); // Admin login
    Route::post('/student/login', [\App\Http\Controllers\StudentController::class, 'apiLogin']); // Student login
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // 📡 STUDENT REAL-TIME STATUS
    Route::post('/student/heartbeat', [\App\Http\Controllers\StudentController::class, 'heartbeat']); // Keep-alive ping
    Route::post('/student/sos',       [\App\Http\Controllers\StudentController::class, 'sendSOS']);   // SOS alert
    Route::post('/student/offline',   [\App\Http\Controllers\StudentController::class, 'goOffline']); // Mark offline on logout

    // 📊 DASHBOARD STATS (polled every 10s by admin dashboard)
    Route::get('/dashboard/stats', [\App\Http\Controllers\DeviceController::class, 'apiStats']);

    // 🟢 LOCATION (These are correct and return JSON)
    Route::get('/location/all', [LocationController::class, 'getAll']);
    Route::post('/location', [LocationController::class, 'update']);
    Route::post('/location/update', [LocationController::class, 'update']);
    Route::post('/location/sos', [LocationController::class, 'setSOS']);

    // 🔵 NOTIFICATIONS: Using the newly created JSON methods
    Route::post('/notifications/send', [NotificationController::class, 'apiSend']);
    Route::get('/notifications/{student_id}', [NotificationController::class, 'apiGet']);

    // 🟡 DEVICE STATUS
    // 'update' is correct (returns JSON)
    Route::post('/device/status', [DeviceController::class, 'update']);
    // 'getAll' method does NOT exist. DeviceController@index returns a dashboard view.
    // Route::get('/device/status', [DeviceController::class, 'getAll']);

    // 🟡 BLACKOUT / STATUS
    // StatusController has 'index' method that returns JSON, not 'getAll'. changed 'getAll' -> 'index'
    Route::get('/status/all', [StatusController::class, 'index']);

    // (If you want a dashboard stats JSON endpoint, you'd create `apiDashboard` in StatusController)
    // Route::get('/dashboard/stats', [StatusController::class, 'apiDashboard']);

});
