<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BehaviorDataController;
use App\Http\Controllers\EducatorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\ViolationAppealController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\StudentManualController;
use App\Http\Controllers\EducatorManualController;
use App\Http\Controllers\EducatorManualAnalyticsController;


// Route::get('/', function () {
//     return view('main_menu');
// });
// // Auth routes
// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Route::post('/student/logout', [AuthController::class, 'logout'])->name('student.logout');

Route::get('/', [AuthController::class, 'dashboard']);
Route::get('/login', function () {
    return redirect()->to(env('MAIN_SYSTEM_URL'). '/');
})->name('login');
Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout');

// Temporary test route for violator functionality (remove in production)
Route::get('/test-violator', function () {
    // Create a fake authenticated user for testing
    $user = new \App\Models\User();
    $user->user_id = 'test_educator';
    $user->user_fname = 'Test';
    $user->user_lname = 'Educator';
    $user->user_role = 'educator';
    $user->email = 'test@example.com';

    Auth::login($user);

    return redirect()->route('educator.add-violator-form');
});

// Educator Routes
Route::prefix('educator')->middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [EducatorController::class, 'dashboard'])->name('educator.dashboard');
    Route::get('/students-by-batch', [EducatorController::class, 'getStudentsByBatch'])->name('educator.students-by-batch');
    Route::get('/available-batches', [EducatorController::class, 'getAvailableBatches'])->name('educator.available-batches');
    Route::get('/violations/count', [ViolationController::class, 'countViolationsByBatchFilter'])->name('educator.violations-count');
    Route::get('/total-violations/count', [EducatorController::class, 'getTotalViolationsCount'])->name('educator.total-violations-count');
    Route::get('/resolved-violations/count', [EducatorController::class, 'getResolvedViolationsCount'])->name('educator.resolved-violations-count');

    // Violations Listing
    Route::get('/violation', [ViolationController::class, 'index'])->name('educator.violation');

    // Add Violator Form and Submission
    Route::get('/add-violator', [ViolationController::class, 'addViolatorForm'])->name('educator.add-violator-form');
    Route::post('/add-violator', [ViolationController::class, 'addViolatorSubmit'])->name('educator.add-violator');
    // Group violator routes
    Route::get('/add-violator-group', [ViolationController::class, 'addGroupViolatorForm'])->name('educator.add-violator-group-form');
    Route::post('/add-violator-group', [ViolationController::class, 'addGroupViolatorSubmit'])->name('educator.add-violator-group');

    // View students by penalty type
    Route::get('/studentsByPenaltyy/{penalty}', [EducatorController::class, 'studentsByPenalty'])->name('educator.studentsByPenalty');

    // Students Page (All Students)
    Route::get('/students', [App\Http\Controllers\EducatorController::class, 'studentsPage'])->name('educator.students');

    // View Student Profile
    Route::get('/student-profile/{student_id}', [App\Http\Controllers\EducatorController::class, 'showStudentProfile'])->name('educator.student-profile');

    // Edit and Update Violation
    Route::put('/update-violation/{id}', [ViolationController::class, 'updateViolation'])->name('educator.update-violation');

    // Update Violation Status (Simple status update)
    Route::put('/violation/{id}', [ViolationController::class, 'update'])->name('educator.violation.update');

    // View Violation (regular)
    Route::get('/view-violation/{id}', [EducatorController::class, 'viewViolation'])->name('educator.view-violation');
    // View x_status-based violation (from task_histories bridge)
    Route::get('/view-xstatus-violation/{id}', [EducatorController::class, 'viewXStatusViolation'])->name('educator.view-xstatus-violation');
    // View Invalid Violation (G16-based)
    Route::get('/view-invalid-violation/{submission_id}', [EducatorController::class, 'viewInvalidViolation'])->name('educator.view-invalid-violation');
    
    // Update Consequence
    Route::put('/update-consequence/{id}', [ViolationController::class, 'updateConsequence'])->name('educator.update-consequence');

    // New Violation Type Form and Submission
    Route::get('/add-violation', [EducatorController::class, 'showViolationTypeForm'])->name('educator.add-violation');
    Route::post('/add-violation-type', [ViolationController::class, 'storeViolationType'])->name('educator.add-violation-type');

    // API Routes for Form Data
    Route::get('/violation-form-data', [ViolationController::class, 'getFormData'])->name('educator.violation-form-data');
    Route::get('/check-existing-violations', [ViolationController::class, 'checkExistingViolations'])->name('educator.check-existing-violations');



    // Additional routes for the student dashboard
    Route::get('/student-violations', [ViolationController::class, 'studentViolations'])->name('educator.student-violations');

    // Route for filtering students by penalty
    Route::get('/students-by-penalty/{penalty}', [EducatorController::class, 'studentsByPenalty'])->name('educator.students-by-penalty');

    // Behavior routes
    Route::get('/behavior', [EducatorController::class, 'behavior'])->name('educator.behavior');
    Route::get('/active-violations', [EducatorController::class, 'activeViolations'])->name('educator.active-violations');
    Route::get('/resolved-violations', [EducatorController::class, 'resolvedViolations'])->name('educator.resolved-violations');
    Route::get('/behavior-data', [BehaviorDataController::class, 'getBehaviorData'])->name('educator.behavior-data');
    Route::get('/behavior/data', [BehaviorDataController::class, 'getBehaviorData'])->name('educator.behavior-data-alt');
    Route::get('/student-behavior-data/{student_id}', [EducatorController::class, 'getStudentBehaviorData'])->name('educator.student-behavior-data');
    Route::get('/student-behavior/{student_id}', [EducatorController::class, 'viewStudentBehavior'])->name('educator.view-student-behavior');
    Route::post('/clear-behavior-data', [EducatorController::class, 'clearBehaviorData'])->name('educator.clear-behavior-data');
    Route::get('/check-behavior-updates', [EducatorController::class, 'checkBehaviorUpdates'])->name('educator.check-behavior-updates');
    Route::get('/generate-sample-violations', [EducatorController::class, 'generateSampleViolations'])->name('educator.generate-sample-violations');
    Route::get('/violation-report', [EducatorController::class, 'violationReportPage'])->name('educator.violation-report');
    Route::get('/check-existing-violations', [ViolationController::class, 'checkExistingViolations'])->name('check-existing-violations');
    Route::get('/violation-report-data', [EducatorController::class, 'getViolationReportData'])->name('educator.violation-report-data');
    Route::post('/update-action-taken', [EducatorController::class, 'updateActionTaken'])->name('educator.update-action-taken');
    Route::post('/update-remarks', [EducatorController::class, 'updateRemarks'])->name('educator.update-remarks');

    // Task Violation Integration routes
    Route::get('/task-violation-integration', [\App\Http\Controllers\TaskViolationIntegrationController::class, 'index'])->name('educator.task-violation-integration');
    Route::post('/task-violation-integration/sync', [\App\Http\Controllers\TaskViolationIntegrationController::class, 'sync'])->name('educator.task-violation-integration.sync');
    Route::get('/task-violation-integration/preview', [\App\Http\Controllers\TaskViolationIntegrationController::class, 'preview'])->name('educator.task-violation-integration.preview');
    Route::get('/task-violation-integration/invalid-submissions', [\App\Http\Controllers\TaskViolationIntegrationController::class, 'getInvalidSubmissions'])->name('educator.task-violation-integration.invalid-submissions');

    // Invalid Student Catcher routes
    Route::get('/invalid-students', [\App\Http\Controllers\InvalidStudentController::class, 'index'])->name('educator.invalid-students');
    Route::post('/invalid-students/catch', [\App\Http\Controllers\InvalidStudentController::class, 'catch'])->name('educator.invalid-students.catch');
    Route::get('/invalid-students/data', [\App\Http\Controllers\InvalidStudentController::class, 'getData'])->name('educator.invalid-students.data');
    Route::post('/invalid-students/{id}/mark-processed', [\App\Http\Controllers\InvalidStudentController::class, 'markProcessed'])->name('educator.invalid-students.mark-processed');
    Route::get('/invalid-students/{id}', [\App\Http\Controllers\InvalidStudentController::class, 'show'])->name('educator.invalid-students.show');

    // Manual edit routes
    Route::get('/manual/edit', [EducatorController::class, 'editManual'])->name('educator.manual.edit');
    Route::post('/manual/update', [EducatorController::class, 'updateManual'])->name('educator.manual.update');
    Route::post('/manual/delete-category', [EducatorController::class, 'deleteOffenseCategory'])->name('educator.manual.delete-category');
    Route::post('/manual/delete-violation-type', [EducatorController::class, 'deleteViolationType'])->name('educator.manual.delete-violation-type');
    Route::get('/student-manual', [StudentManualController::class, 'index'])->name('student-manual')->middleware('auth');

    // Severity max counts routes
    Route::get('/severity-max-counts', [EducatorController::class, 'getSeverityMaxCounts'])->name('educator.severity-max-counts');
    Route::post('/severity-max-counts/update', [EducatorController::class, 'updateSeverityMaxCounts'])->name('educator.severity-max-counts.update');

    // Notification routes for educators
    Route::get('/notifications', [NotificationController::class, 'index'])->name('educator.notifications');
    Route::get('/notifications/page', [NotificationController::class, 'page'])->name('educator.notifications.page');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('educator.notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('educator.notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('educator.notifications.unread-count');

    // Appeal management routes for educators/admins
    Route::get('/appeals/page', function() {
        $unreadCount = 0;
        if (Auth::check()) {
            try {
                $unreadCount = \App\Models\Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
            } catch (\Exception $e) {
                $unreadCount = 0;
            }
        }
        return view('educator.appeals', compact('unreadCount'));
    })->name('educator.appeals.page');
    Route::get('/appeals', [ViolationAppealController::class, 'getAppealsForAdmin'])->name('educator.appeals');
    Route::post('/appeals/{appeal}/review', [ViolationAppealController::class, 'reviewAppeal'])->name('educator.appeals.review');
    Route::get('/appeals/stats', [ViolationAppealController::class, 'getAppealStats'])->name('educator.appeals.stats');

});

// API Routes
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('/violation-stats', [ViolationController::class, 'getViolationStatsByPeriod'])->name('api.violation-stats');
    Route::get('/violation-stats-by-batch', [BehaviorDataController::class, 'getViolationStatsByBatch'])->name('api.violation-stats-by-batch');
    Route::get('/violations/count', [ViolationController::class, 'countViolationsByBatchFilter'])->name('api.violations-count');
    Route::get('/students/compliance', [EducatorController::class, 'getStudentComplianceByBatch'])->name('api.students-compliance');
    Route::get('/available-batches', [EducatorController::class, 'getAvailableBatches'])->name('api.available-batches');
    Route::get('/student-violations', [ViolationController::class, 'getStudentViolations'])->name('api.student-violations');
    Route::get('/violation-students', [ViolationController::class, 'getViolationStudents'])->name('api.violation-students');
    Route::get('/violation-types/{categoryId}', [ViolationController::class, 'getViolationTypesByCategory'])->name('api.violation-types');
    // Search students for Select2 in Add Violator (Group) form
    Route::get('/search-students', [ViolationController::class, 'searchStudents'])->name('api.search-students');
});

// Student routes
Route::prefix('student')->middleware(['auth'])->group(function () {
    // Dashboard (keeping for backward compatibility but redirecting to violations)
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');

    // Violation and behavior routes
    Route::get('/violation', [StudentController::class, 'violation'])->name('student.violation');
    Route::get('/check-violation-updates', [StudentController::class, 'checkForViolationUpdates'])->name('student.check-violation-updates');

    // Notification routes for students
    Route::get('/notifications', [NotificationController::class, 'index'])->name('student.notifications');
    Route::get('/notifications/page', [NotificationController::class, 'page'])->name('student.notifications.page');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('student.notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('student.notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('student.notifications.unread-count');

    // Appeal routes for students
    Route::post('/appeal/submit', [ViolationAppealController::class, 'submitAppeal'])->name('student.appeal.submit');

    // For students
    Route::get('/student-manual', [StudentManualController::class, 'index'])->name('student.manual');

    // For educators
    Route::get('/educator-manual', [EducatorManualController::class, 'index'])->name('educator.manual');
    Route::get('/educator/manual/analytics', [EducatorManualAnalyticsController::class, 'index'])->name('educator.manual.analytics');
});

Route::middleware(['auth'])->get('/educator/manual/analytics/data', [EducatorManualAnalyticsController::class, 'getCategoryStudentCounts'])->name('educator.manual.analytics.data');

Route::fallback(function () {
    return redirect('/login');
});
