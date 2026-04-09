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

Route::get('/dashboard', function () {

  
    $latestLocations = DB::table('locations')
        ->select('student_id', DB::raw('MAX(recorded_at) as last_seen'))
        ->groupBy('student_id')
        ->get();

    $onlineCount = 0;
    $offlineCount = 0;
    $latestUpdate = null;

    foreach ($latestLocations as $loc) {
        $lastSeen = Carbon::parse($loc->last_seen);

        if (!$latestUpdate || $lastSeen->gt($latestUpdate)) {
            $latestUpdate = $lastSeen;
        }

        if ($lastSeen->diffInMinutes(now()) <= 10) {
            $onlineCount++;
        } else {
            $offlineCount++;
        }
    }

    if (!$latestUpdate) {
        $latestTime = 'No updates yet';
        $latestDate = '';
    } else {
        $latestTime = $latestUpdate->format('g:i:s A');
        $latestDate = $latestUpdate->format('n/j/Y');
    }


    $broadcastCount = 0;
$sosCount = 0;

if (Schema::hasTable('notifications')) {

   
    $broadcastCount = DB::table('notifications')
        ->where('read', false)
        ->count();

    // ONLY unread SOS alerts
    $sosCount = DB::table('notifications')
        ->where('type', 'sos')
        ->where('read', false)
        ->count();
}

    return view('dashboard', compact(
        'onlineCount',
        'offlineCount',
        'latestTime',
        'latestDate',
        'broadcastCount',
        'sosCount'
    ));
});

Route::get('/notifications', [NotificationController::class, 'index']);

Route::post('/notifications/send', [NotificationController::class, 'send']);

Route::post('/notifications/{id}/acknowledge', [NotificationController::class, 'acknowledge']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
Route::post('/notifications/{id}/reply', [NotificationController::class, 'reply']);

Route::get('/dashboard', [App\Http\Controllers\DeviceController::class, 'index']);
