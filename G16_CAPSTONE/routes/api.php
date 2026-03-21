<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RotationScheduleController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Minimal public API surface used by the roomtask blade and client JS.
| Place these inside your API middleware group as needed.
|
*/

// rotation schedules (real implementation lives here)
Route::apiResource('rotation-schedules', RotationScheduleController::class);

// helper: latest persisted schedule for a room (used by client)
Route::get('rotation-schedules/latest/{room}', [RotationScheduleController::class, 'latest']);

// Task / room endpoints used by the roomtask view and JS
Route::prefix('tasks')->group(function () {
    // Get canonical base templates (room + optional day)
    Route::get('base-templates', [TaskController::class, 'getBaseTemplates'])->name('tasks.base_templates');

    // Apply or generate schedule (frontend expects route('tasks.schedule'))
    Route::post('schedule', [TaskController::class, 'scheduleTasks'])->name('tasks.schedule');

    // Get valid students list for assignment/autocomplete
    Route::get('valid-students', [TaskController::class, 'getValidStudentsList'])->name('tasks.valid_students');

    // Reassign students (bulk / fast reassignment)
    Route::post('reassign', [TaskController::class, 'reassignStudents'])->name('tasks.reassign');
});

// Task status / history endpoints
Route::post('get-task-statuses', [TaskController::class, 'getTaskStatuses'])->name('tasks.get_statuses');
Route::post('update-task-status', [TaskController::class, 'updateTaskStatus'])->name('tasks.update_status');
Route::post('mark-day-complete', [TaskController::class, 'markDayComplete'])->name('tasks.mark_day_complete');

// Room and dashboard helpers
Route::get('room/details/{room}', [TaskController::class, 'getRoomDetails'])->name('room.details');
Route::get('dashboard/room-data', [TaskController::class, 'getDashboardRoomData'])->name('dashboard.room_data');
Route::get('dashboard/room-assignments', [TaskController::class, 'getRoomAssignmentsForDashboard'])->name('dashboard.assignments');

// Week completion check used by client-side prompt
Route::post('check-week-completion', [TaskController::class, 'apiCheckWeekCompletion'])->name('tasks.check_week_completion');