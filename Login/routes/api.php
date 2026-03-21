<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\StudentTaskController;
use App\Http\Controllers\RotationScheduleController as LoginRotationScheduleController;

//Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student Task Assignment API Routes
Route::prefix('student')->group(function () {
    Route::get('/my-tasks', [StudentTaskController::class, 'getMyTasks'])->name('api.student.my-tasks');
});

// Task Management API Routes (for admin/coordinator use)
Route::prefix('task-management')->group(function () {
    Route::post('/mark-day-complete', [StudentTaskController::class, 'markDayComplete'])->name('api.mark.day.complete');
    Route::get('/get-assigned-students/{categoryId}', [StudentTaskController::class, 'getAssignedStudents'])->name('api.get.assigned.students');
});

Route::prefix('v1')->group(function () {
    Route::prefix('rotation-schedules')->group(function () {
        // Protected collection / admin actions
        Route::get('/', [RotationScheduleController::class, 'index'])->middleware('auth:sanctum');
        Route::post('/', [RotationScheduleController::class, 'store'])->middleware('auth:sanctum');

        // Public read endpoints (used by other subsystems / student UI)
        Route::get('/latest/{room}', [RotationScheduleController::class, 'latest']);
        Route::get('/{room}', [RotationScheduleController::class, 'show']);

        // Protected item actions
        Route::put('/{id}', [RotationScheduleController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [RotationScheduleController::class, 'destroy'])->middleware('auth:sanctum');
        Route::post('/{id}/restore', [RotationScheduleController::class, 'restore'])->middleware('auth:sanctum');
    });
});

// keep the old paths in Login so callers get a 410 and guidance
Route::apiResource('rotation-schedules', LoginRotationScheduleController::class);

// Fetch the latest active (or latest) rotation schedule for a specific room.
// GET /api/rotation-schedules/{room}/latest
Route::get('rotation-schedules/{room}/latest', [RotationScheduleController::class, 'latest']);

