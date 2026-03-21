<?php

use App\Http\Controllers\VisitorLogController;
use App\Http\Controllers\AcademicLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoingOutLogController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\RoleSwitchController;
use App\Http\Controllers\ManualEntryController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\EducatorController;
use App\Models\Visitor;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\PNUser;
use App\Providers\AppServiceProvider as Gate;
use Illuminate\Support\Facades\Auth as AuthMiddleware;
use App\Http\Controllers\MonitorScheduleController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\EducatorsController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\SubsystemAuth;
use App\Models\StudentDetail;

Route::get('/', [AuthController::class, 'dashboard']);
Route::get('/login', function () {
    return redirect()->to(env('MAIN_SYSTEM_URL'). '/');
})->name('login');
Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout');

// Protected routes (require authentication)
Route::middleware([SubsystemAuth::class])->group(function () {

    // Role switching routes (for educators only)
    // Route::middleware(['can:isEducator'])->group(function () {
        Route::post('/role/switch', [RoleSwitchController::class, 'switchMode'])->name('role.switch');
        Route::get('/role/current-mode', [RoleSwitchController::class, 'getCurrentMode'])->name('role.current-mode');

        Route::get('/educator/dashboard', [EducatorsController::class, 'show'])->name('educator.dashboard');

        Route::get('/educator/academic-monitor/past-logs', [AcademicLogController::class, 'pastLogs'])->name('academic.past_logs');
        Route::post('/educator/academic-monitor/{id}/consideration', [AcademicLogController::class, 'updateConsideration'])->name('academic.update_consideration');
        Route::post('/educator/academic-monitor/{id}/absent-validation', [AcademicLogController::class, 'updateAbsentValidation'])->name('academic.absent-validation');

        // Going Out Monitor Routes
        Route::get('/educator/goingout-monitor/past-logs', [GoingOutLogController::class, 'pastLogs'])->name('goingout.past_logs');
        Route::post('/educator/goingout-monitor/{id}/consideration', [GoingOutLogController::class, 'updateConsideration'])->name('goingout.update_consideration');

        // Going Home & Intern Monitor Routes
        Route::get('/educator/goingout-monitor', [EducatorsController::class, 'goingoutMonitor'])->name('goingout.monitor');
        Route::get('/educator/academic-monitor', [EducatorsController::class, 'academicMonitor'])->name('academic.monitor');
        Route::get('/educator/goinghome-monitor', [EducatorsController::class, 'goinghomeMonitor'])->name('goinghome.monitor');
        Route::get('/educator/intern-monitor', [EducatorsController::class, 'internMonitor'])->name('intern.monitor');
        Route::get('/educator/visitor-monitor', [EducatorsController::class, 'visitorMonitor'])->name('visitor.monitor');

        // Visitor Monitor Routes
        Route::get('/educator/visitor-monitor/past-logs', [VisitorLogController::class, 'pastLogs'])->name('visitor.past_logs');
        Route::post('/educator/visitor-monitor/{id}/accept', [VisitorLogController::class, 'accept'])->name('visitor.accept');
        Route::post('/educator/visitor-monitor/{id}/reject', [VisitorLogController::class, 'reject'])->name('visitor.reject');
        Route::post('/educator/visitor-monitor/{id}/consideration', [VisitorLogController::class, 'updateConsideration'])->name('visitor.update_consideration');

        // Visitor Manual Entry Approval Routes
        Route::post('/educator/visitor-monitor/{id}/approve-manual-entry', [VisitorLogController::class, 'approveManualEntry'])->name('visitor.approve_manual_entry');
        Route::post('/educator/visitor-monitor/{id}/reject-manual-entry', [VisitorLogController::class, 'rejectManualEntry'])->name('visitor.reject_manual_entry');

        // Dashboard Data Routes
        Route::get('/educator/today-attendance', [EducatorsController::class, 'getTodayAttendance'])->name('educator.today-attendance');
        Route::get('/educator/goingout-attendance', [EducatorsController::class, 'getGoingOutAttendance'])->name('educator.goingout-attendance');
        Route::get('/educator/logs-data', [EducatorsController::class, 'getLogsData'])->name('educator.logs.data');
        Route::get('/educator/goingout-loginout-data', [EducatorsController::class, 'getGoingOutLogInOutData'])->name('educator.goingout-loginout-data');
        Route::get('/educator/late-students-by-batch', [EducatorsController::class, 'getLateStudentsByBatch'])->name('educator.late-students-by-batch');
        Route::get('/educator/time-inout-by-batch', [EducatorsController::class, 'getTimeInOutByBatch'])->name('educator.time-inout-by-batch');
        Route::get('/educator/absent-students-by-batch', [EducatorsController::class, 'getLineGraph'])->name('educator.absent-students-by-batch');
        Route::get('/educator/student-data', [EducatorsController::class, 'getStudentData'])->name('educator.student-data');
        Route::get('/educator/recent-activities', [EducatorsController::class, 'getRecentActivities'])->name('educator.recent-activities');

        // Late Analytics Routes
        Route::get('/educator/late-analytics', function () {
            return view('user-educator.late-and-analytics');
        })->name('educator.late-analytics');
        Route::get('/educator/academic/report', [EducatorsController::class, 'getAcademicReport'])->name('academic.report');
        Route::get('/educator/leisure/report', [EducatorsController::class, 'getLeisureReport'])->name('leisure.report');
        Route::get('/educator/intern/report', [EducatorsController::class, 'getInternReport'])->name('intern.report');
        Route::get('/educator/going-home/report', [EducatorsController::class, 'getGoingHomeReport'])->name('goinghome.report');
        Route::get('/educator/late-analytics/data', [EducatorsController::class, 'getLateAnalytics'])->name('educator.late-analytics.data');
        Route::get('/educator/student-late-history', [EducatorsController::class, 'getStudentLateHistory'])->name('educator.student-late-history');

        // Manual Entry Approval Routes
        Route::get('/educator/approvals', [ApprovalController::class, 'index'])->name('educator.approvals');
        Route::get('/educator/approvals/details/{id}', [ApprovalController::class, 'getEntryDetails'])->name('educator.approvals.details');
        Route::post('/educator/approvals/approve', [ApprovalController::class, 'approve'])->name('educator.approvals.approve');
        Route::post('/educator/approvals/reject', [ApprovalController::class, 'reject'])->name('educator.approvals.reject');
        Route::post('/educator/approvals/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('educator.approvals.bulk-approve');

        // Visitor Manual Entry Approval Routes
        Route::post('/educator/approvals/visitor/{id}/approve', [ApprovalController::class, 'approveVisitor'])->name('educator.approvals.visitor.approve');
        Route::post('/educator/approvals/visitor/{id}/reject', [ApprovalController::class, 'rejectVisitor'])->name('educator.approvals.visitor.reject');

        // Absent Analytics Routes
        Route::get('/educator/absent-analytics/data', [EducatorsController::class, 'getAbsentAnalytics'])->name('educator.absent-analytics.data');
        Route::get('/educator/student-absent-history', [EducatorsController::class, 'getStudentAbsentHistory'])->name('educator.student-absent-history');

        // Notification Routes
        Route::get('/educator/notifications', [NotificationController::class, 'notificationPage'])->name('educator.notifications');
        Route::get('/educator/notifications/history', [NotificationController::class, 'getNotificationHistory'])->name('educator.notifications.history');
        Route::get('/educator/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('educator.notifications.unread-count');
        Route::post('/educator/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('educator.notifications.mark-all-read');
        Route::get('/educator/notifications/counts', [NotificationController::class, 'getNotificationCounts'])->name('educator.notifications.counts');
        Route::post('/educator/notifications/academic/mark-viewed', [NotificationController::class, 'markAcademicAsViewed'])->name('educator.notifications.academic.mark-viewed');
        Route::post('/educator/notifications/goingout/mark-viewed', [NotificationController::class, 'markGoingOutAsViewed'])->name('educator.notifications.goingout.mark-viewed');
        Route::post('/educator/notifications/visitor/mark-viewed', [NotificationController::class, 'markVisitorAsViewed'])->name('educator.notifications.visitor.mark-viewed');
        Route::post('/educator/notifications/late/mark-viewed', [NotificationController::class, 'markLateAsViewed'])->name('educator.notifications.late.mark-viewed');

        // // Test route for debugging
        // Route::get('/educator/test-notifications', function() {
        //     return response()->json([
        //         'message' => 'Test route working',
        //         'user' => auth()->user() ? auth()->user()->id : 'not authenticated',
        //         'timestamp' => now()
        //     ]);
        // })->name('educator.test-notifications');

        //Monitoring
        Route::get('/monitor/dashboard', [MonitorScheduleController::class, 'dashboard'])
            ->name('monitor.dashboard');

        Route::get('/monitor/get-groups', [MonitorScheduleController::class, 'getGroups'])
            ->name('monitor.get-groups');

        // Monitor dashboard data routes
        Route::get('/monitor/today-attendance', [MonitorController::class, 'getTodayAttendance'])->name('monitor.today-attendance');
        Route::get('/monitor/student-data', [MonitorController::class, 'getStudentData'])->name('monitor.student-data');
        Route::get('/monitor/late-students-by-batch', [MonitorController::class, 'getLateStudentsByBatch'])->name('monitor.late-students-by-batch');
        Route::get('/monitor/goingout-attendance', [MonitorController::class, 'getGoingOutAttendance'])->name('monitor.goingout-attendance');
        Route::get('/monitor/time-inout-by-batch', [MonitorController::class, 'getTimeInOutByBatch'])->name('monitor.time-inout-by-batch');
        Route::get('/monitor/academic-loginout-data', [MonitorController::class, 'getAcademicLogInOutData'])->name('monitor.academic-loginout-data');
        Route::get('/monitor/goingout-loginout-data', [MonitorController::class, 'getGoingOutLogInOutData'])->name('monitor.goingout-loginout-data');
        Route::get('/monitor/absent-students-by-batch', [MonitorController::class, 'getAbsentStudentsByBatch'])->name('monitor.absent-students-by-batch');

        // Monitor log routes
        Route::get('/monitor/academic/logs', [MonitorController::class, 'academicLogs'])->name('monitor.academic.logs');
        Route::get('/monitor/goingout/logs', [MonitorController::class, 'goingoutLogs'])->name('monitor.goingout.logs');
        Route::get('/monitor/visitor/logs', [MonitorController::class, 'visitorLogs'])->name('monitor.visitor.logs');
        Route::get('/monitor/goinghome/logs', [MonitorController::class, 'goinghomeLogs'])->name('monitor.goinghome.logs');
        Route::get('/monitor/intern/logs', [MonitorController::class, 'internLogs'])->name('monitor.intern.logs');

        // Monitor consideration routes

        Route::post('/monitor/academic/consideration', [MonitorController::class, 'setConsiderationAcademic'])->name('monitor.academic.consideration');
        Route::post('/monitor/goingout/consideration', [MonitorController::class, 'setConsiderationGoingOut'])->name('logs.consideration.goingout');
        Route::post('/monitor/intern/consideration', [MonitorController::class, 'setConsiderationIntern'])->name('logs.consideration.intern');
        Route::post('/monitor/goinghome/consideration', [MonitorController::class, 'setConsiderationGoingHome'])->name('logs.consideration.goinghome');
        Route::post('/monitor/intern/absent', [MonitorController::class, 'markAbsentIntern'])->name('monitor.markAbsent.intern');
        Route::post('/monitor/academic/absent', [MonitorController::class, 'markAbsent'])->name('monitor.markAbsent');
        Route::post('/monitor/academic/absent-validation/{id}', [MonitorController::class, 'updateAbsentValidation'])->name('monitor.academic.absent-validation');
        Route::post('/monitor/visitor/consideration/{id}', [MonitorController::class, 'updateVisitorConsideration'])->name('monitor.visitor.consideration');

        // Manual Entry Routes
        Route::get('/monitor/manual-entry', [ManualEntryController::class, 'index'])->name('monitor.manual-entry');
        Route::get('/monitor/manual-entry/logs', [ManualEntryController::class, 'getStudentLogs'])->name('monitor.manual-entry.logs');
        Route::get('/monitor/manual-entry/find-existing', [ManualEntryController::class, 'findExistingLog'])->name('monitor.manual-entry.find-existing');
        Route::post('/monitor/manual-entry/submit', [ManualEntryController::class, 'submitManualEntry'])->name('monitor.manual-entry.submit');
        Route::post('/monitor/internLog/submit', [ManualEntryController::class, 'submitInternManualEntry'])->name('monitor.internLog.submit');
        Route::post('/monitor/AcademicLog/submit', [ManualEntryController::class, 'submitAcademicManualEntry'])->name('monitor.academicLog.submit');
        Route::post('/monitor/LeisureLog/submit', [ManualEntryController::class, 'submitLeisureManualEntry'])->name('monitor.leisureLog.submit');
        Route::post('/monitor/goingHomeLog/submit', [ManualEntryController::class, 'submitGoingHomeManualEntry'])->name('monitor.goingHomeLog.submit');
        Route::post('/monitor/visitor/submit', [ManualEntryController::class, 'submitVisitorManualEntry'])->name('monitor.visitor.submit');

        // Legacy monitor log in/out routes (kept for backward compatibility but deprecated)
        Route::post('/monitor/academic/logout/{id}', [MonitorController::class, 'performAcademicLogout'])->name('monitor.academic.logout');
        Route::post('/monitor/academic/login/{id}', [MonitorController::class, 'performAcademicLogin'])->name('monitor.academic.login');
        Route::post('/monitor/goingout/logout/{id}', [MonitorController::class, 'performGoingoutLogout'])->name('monitor.goingout.logout');
        Route::post('/monitor/goingout/login/{id}', [MonitorController::class, 'performGoingoutLogin'])->name('monitor.goingout.login');

        Route::get('/monitor/schedule/choose/{type}', function ($type) {
            if ($type === 'Irregular') {
                return redirect()->route('monitor.irregular-schedule.select');
            }

            $batches = StudentDetail::distinct()->pluck('batch')->sort();
            $genders = PNUser::distinct()->pluck('gender')->sort();
            $student_ids = StudentDetail::distinct()->pluck('student_id')->sort();
            $students = StudentDetail::all();
            return view('user-monitor.type', compact('type', 'batches', 'genders', 'student_ids', 'students'));
        })->name('monitor.schedule.choose');

        Route::get('/monitor/schedule', [MonitorScheduleController::class, 'show'])
            ->name('monitor.schedule');

        Route::post(
            '/monitor/schedule/store',
            [MonitorScheduleController::class, 'store']
        )
            ->name('monitor.schedule.store');

        Route::post(
            '/monitor/schedule/update-day',
            [MonitorScheduleController::class, 'updateDay']
        )
            ->name('monitor.schedule.update-day');

        Route::delete(
            '/monitor/schedule/delete',
            [MonitorScheduleController::class, 'delete']
        )
            ->name('monitor.schedule.delete');

        Route::patch(
            '/monitor/schedule/update-grace-period',
            [MonitorScheduleController::class, 'updateGracePeriod']
        )
            ->name('monitor.schedule.update-grace-period');

        // Grouped Routes for Irregular Schedules
        Route::prefix('monitor/irregular-schedule')->group(function () {
            Route::get('/select', [MonitorScheduleController::class, 'selectStudent'])
                ->name('monitor.irregular-schedule.select');
            Route::get('/{student_id}', [MonitorScheduleController::class, 'showIrregularSchedule'])
                ->name('monitor.irregular-schedule');
            Route::post('/{student_id}/store', [MonitorScheduleController::class, 'storeIrregularSchedule'])
                ->name('monitor.irregular-schedule.store');
        });

        // Individual Going Out Schedules routes
        Route::prefix('monitor/individual-goingout')->group(function () {
            Route::get('/students', [MonitorScheduleController::class, 'showIndividualGoingOutStudents'])
                ->name('monitor.individual-goingout.students');
            Route::post('/set-schedule', [MonitorScheduleController::class, 'setIndividualGoingOutSchedule'])
                ->name('monitor.individual-goingout.set-schedule');
            Route::get('/date-range-list', [MonitorScheduleController::class, 'getIndividualDateRangeSchedules'])
                ->name('monitor.individual-goingout.date-range-list');
        });

        // Batch Going Out Schedules routes
        Route::prefix('monitor/batch-goingout')->group(function () {
            Route::post('/create', [MonitorScheduleController::class, 'createBatchGoingOutSchedule'])
                ->name('monitor.batch-goingout.create');
            Route::get('/list', [MonitorScheduleController::class, 'getBatchGoingOutSchedules'])
                ->name('monitor.batch-goingout.list');
        });

        // Unique Leisure Schedule routes (for single-day schedules)
        Route::prefix('monitor/unique-leisure')->group(function () {
            Route::get('/students', [MonitorScheduleController::class, 'showUniqueLeisureStudents'])
                ->name('monitor.unique-leisure.students');
            Route::post('/set-schedule', [MonitorScheduleController::class, 'setUniqueLeisureSchedule'])
                ->name('monitor.unique-leisure.set-schedule');
        });

        Route::prefix('monitor/calendar')->group(function () {
            Route::get('/students', [MonitorScheduleController::class, 'showCalendar'])
                ->name('monitor.calendar.students');
            Route::post('/set-schedule', [MonitorScheduleController::class, 'setCalendarSchedule'])
                ->name('monitor.calendar.set-schedule');
        });
});
