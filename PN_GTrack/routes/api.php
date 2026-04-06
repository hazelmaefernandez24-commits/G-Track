<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\StatusController;


Route::middleware('api')->group(function () {

    // 🔵 AUTH: Using the newly created API methods
    Route::post('/login', [AuthController::class, 'apiLogin']); 
    Route::post('/logout', [AuthController::class, 'apiLogout']);

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
