<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\VisitorLogController;
use App\Http\Controllers\AcademicLogController;
use App\Http\Controllers\LeisureLogController;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

// 🔐 Auth & Login Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 🔄 Password Recovery Routes
Route::get('/forgot-password', function () {
    return view('forgotPassword');
})->name('forgot-password');

Route::post('/forgot-password/verify', [AuthController::class, 'verifyForgotPassword'])->name('forgot-password.verify');
Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('/reset-password/update', [AuthController::class, 'resetPassword'])->name('reset-password.update');

// 🏠 Main Menu Route (Updated)
Route::get('/main-menu', function (Request $request) {
    $token = $request->query('token');

    if (!$token) {
        return redirect()->route('login')->with('error', 'Token missing');
    }

    $accessToken = PersonalAccessToken::findToken($token);

    if (!$accessToken) {
        return redirect()->route('login')->with('error', 'Unauthorized');
    }

    $user = $accessToken->tokenable;

    if (!$user || !isset($user->user_role)) {
        return redirect()->route('login')->with('error', 'User role not found');
    }

    $user_role = strtolower($user->user_role); // Normalize role
    return view('landing-page', compact('token', 'user_role'));
})->name('main-menu');

// 🔧 Change Password
Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('update-password');

// 📋 Logify Routes
Route::get('/logify', function (Request $request) {
    $token = $request->query('token');
    return view('welcome', compact('token'));
});

Route::get('/visitor/dashboard', function () {
    $visitors = VisitorLog::getAllVisitor(10);
    return view('visitor.dashboard', compact('visitors'));
})->name('visitor.dashboard.show');

Route::post('/visitor/dashboard/{id}', [VisitorLogController::class, 'logOut'])->name('visitor.logOut');
Route::get('/visitor_page', [VisitorLogController::class, 'create'])->name('visitor.create');
Route::post('/visitor/add', [VisitorLogController::class, 'store'])->name('visitor.store');

// 🎓 Student Dashboard & Logs
Route::get('/student/dashboard', function () {
    return view('user-student.dashboard');
})->name('student.dashboard');

Route::get('/student/academic', function () {
    $goingout = false;
    $academic = true;
    return view('user-student.logForms', ['academic' => $academic, 'goingout' => $goingout]);
})->name('academicLogForms.show');

Route::get('/student/goingout', function () {
    $goingout = true;
    $academic = false;
    return view('user-student.logForms', ['academic' => $academic, 'goingout' => $goingout]);
})->name('goingOutLogForms.show');

// 🕒 Academic Log Actions
Route::get('/student/academic/logout/show', [AcademicLogController::class, 'logoutForm'])->name('academic.logout.form');
Route::get('/student/academic/login/show', [AcademicLogController::class, 'loginForm'])->name('academic.login.form');
Route::match(['get', 'post'], '/student/academic/logout', [AcademicLogController::class, 'logTimeOut'])->name('academic.logout');
Route::match(['get', 'post'], '/student/academic/login', [AcademicLogController::class, 'logTimeIn'])->name('academic.login');

// 🕒 Going Out Log Actions
Route::get('/student/goingout/logout/show', [LeisureLogController::class, 'logoutForm'])->name('goingout.logout.form');
Route::get('/student/goingout/login/show', [LeisureLogController::class, 'loginForm'])->name('goingout.login.form');
Route::post('/student/goingout/logout', [LeisureLogController::class, 'logTimeOut'])->name('goingout.logout');
Route::post('/student/goingout/login', [LeisureLogController::class, 'logTimeIn'])->name('goingout.login');
