<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\VisitorLogController;
use App\Http\Controllers\AcademicLogController;
use App\Http\Controllers\GoingHomeLogController;
use App\Http\Controllers\InternLogController;
use App\Http\Controllers\InternLogsController;
use App\Http\Controllers\LeisureLogController;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\RotationScheduleController;

Route::get('/logify', function (Request $request) {
    $token = $request->query('token');
    return view('welcome', compact('token'));
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('forgotPassword');
})->name('forgot-password');

Route::post('/forgot-password/verify', [AuthController::class, 'verifyForgotPassword'])->name('forgot-password.verify');

Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('/reset-password/update', [AuthController::class, 'resetPassword'])->name('reset-password.update');

Route::get('/main-menu', function (Request $request) {
    $user_role = $request->query('user_role');
    $token = $request->query('token');
    if (!$token) {
        return redirect()->route('login')->with('error', 'Token missing');
    }
    $accessToken = PersonalAccessToken::findToken($token);
    if (!$accessToken) {
        return redirect()->route('login')->with('error', 'Unauthorized');
    }
    return view('landing-page', compact('token', 'user_role'));
})->name('main-menu');

Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('update-password');

//logify edits
Route::get('/visitor/dashboard', function () {
   $visitors = VisitorLog::getAllVisitor(10);
    return view('visitor.dashboard', compact('visitors'));
})->name('visitor.dashboard.show');

Route::post('/visitor/dashboard/{id}', [VisitorLogController::class, 'logOut'])->name('visitor.logOut');
Route::get('/visitor_page', [VisitorLogController::class, 'create'])->name('visitor.create');
Route::post('/visitor/add', [VisitorLogController::class, 'store'])->name('visitor.store');

Route::get('/student/dashboard', function () {
    return view('user-student.dashboard');
})->name('student.dashboard');

// Route to view all student users
Route::get('/admin/students', function () {
    $students = \App\Models\PNUser::where('user_role', 'student')
        ->with('studentDetail')
        ->orderBy('user_fname')
        ->get();

    return view('admin.students', compact('students'));
})->name('admin.students');

Route::get('/student/academic', function () {
    $goingout = false;
    $academic = true;
    $intern = false;
    $goinghome = false;
    return view('user-student.logForms', ['academic' => $academic, 'goingout' => $goingout, 'intern' => $intern, 'goinghome' => $goinghome]);
})->name('academicLogForms.show');

Route::get('/student/goingout', [LeisureLogController::class, 'showLogForms'])->name('goingOutLogForms.show');

Route::get('/student/intern', function () {
    $goingout = false;
    $academic = false;
    $intern = true;
    $goinghome = false;
    return view('user-student.logForms', ['academic' => $academic, 'goingout' => $goingout, 'intern' => $intern, 'goinghome' => $goinghome]);
})->name('internLogForms.show');

Route::get('/student/goinghome', function () {
    $goingout = false;
    $academic = false;
    $intern = false;
    $goinghome = true;
    return view('user-student.logForms', ['academic' => $academic, 'goingout' => $goingout, 'intern' => $intern, 'goinghome' => $goinghome]);
})->name('goinghomeLogForms.show');


Route::get('/student/academic/logout/show', [AcademicLogController::class, 'logoutForm'])->name('academic.logout.form');
Route::get('/student/academic/login/show', [AcademicLogController::class, 'loginForm'])->name('academic.login.form');
Route::match(['get', 'post'], '/student/academic/logout', [AcademicLogController::class, 'logTimeOut'])->name('academic.logout');
Route::match(['get', 'post'], '/student/academic/login', [AcademicLogController::class, 'logTimeIn'])->name('academic.login');

Route::get('/student/goingout/logout/show', [LeisureLogController::class, 'logoutForm'])->name('goingout.logout.form');
Route::get('/student/goingout/login/show', [LeisureLogController::class, 'loginForm'])->name('goingout.login.form');
Route::post('/student/goingout/logout',     [LeisureLogController::class, 'logTimeOut'])->name('goingout.logout');
Route::post('/student/goingout/login', [LeisureLogController::class, 'logTimeIn'])->name('goingout.login');

Route::get('/student/intern/logout/show', [InternLogsController::class, 'logoutForm'])->name('intern.logout.form');
Route::get('/student/intern/login/show', [InternLogsController::class, 'loginForm'])->name('intern.login.form');
Route::post('/student/intern/logout',     [InternLogsController::class, 'logTimeOut'])->name('intern.logout');
Route::post('/student/intern/login', [InternLogsController::class, 'logTimeIn'])->name('intern.login');

Route::get('/student/goinghome/logout/show', [GoingHomeLogController::class, 'logoutForm'])->name('goinghome.logout.form');
Route::get('/student/goinghome/login/show', [GoingHomeLogController::class, 'loginForm'])->name('goinghome.login.form');
Route::post('/student/goinghome/logout',     [GoingHomeLogController::class, 'logTimeOut'])->name('goinghome.logout');
Route::post('/student/goinghome/login', [GoingHomeLogController::class, 'logTimeIn'])->name('goinghome.login');

// Persist generated schedule (used by the blade applySchedule() JS)
Route::post('/tasks/schedule', [RotationScheduleController::class, 'store'])
    ->middleware('auth')
    ->name('tasks.schedule');

// Public / cross-subsystem read endpoints
Route::get('/rotation-schedules/latest/{room}', [RotationScheduleController::class, 'latest'])
    ->name('rotation.latest');
Route::get('/rotation-schedules/{room}', [RotationScheduleController::class, 'show'])
    ->name('rotation.show');
