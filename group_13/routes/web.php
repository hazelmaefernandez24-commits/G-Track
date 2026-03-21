<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PNUserController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AuthController;
use App\Mail\GradeSubmissionNotification;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EducatorController;
use App\Http\Controllers\Training\GradeSubmissionController;
use App\Http\Controllers\Training\CalendarController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthoController;

//API receive
Route::get('/', [AuthController::class, 'dashboard']);
Route::get('/login', function () {
    return redirect()->to(env('MAIN_SYSTEM_URL'). '/');
})->name('login');
Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout');


// Route::get('/', function () {
//     return view('welcome');
// });

// Main menu route
// Route::get('/main-menu', function () {
//     return view('main-menu');
// })->name('main-menu');

// Login Routes
// Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login');

//Forgot Password Routes
// Route::get('/forgot-password', function () {
//     return view('forgotpassword');
// })->name('forgot-password');
// Route::post('/forgot-password/verify', [AuthController::class, 'verifyForgotPassword'])->name('forgot-password.verify');

// Reset password (after forgot password verification)
// Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
// Route::post('/reset-password/update', [AuthController::class, 'resetPassword'])->name('reset-password.update');




Route::middleware('auth')->group(function () {



    // Change Password Routes
    // Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
    // Route::post('/update-password', [AuthController::class, 'updatePassword'])->name('update-password');

    // Logout Route
    // Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('can:admin-access')->group(function () {
        Route::resource('pnph_users', PNUserController::class);
        Route::get('/dashboard', [PNUserController::class, 'dashboard'])->name('dashboard');
    });

    // Educator routes
    Route::prefix('educator')->name('educator.')->middleware('can:educator-access')->group(function () {
        Route::get('/dashboard', [EducatorController::class, 'dashboard'])->name('dashboard');

        Route::get('/students-info', [EducatorController::class, 'index'])->name('educator.students.index');
        Route::get('/students/{user_id}/view', [EducatorController::class, 'viewStudent'])->name('students.view');
        Route::get('/students-info', [EducatorController::class, 'index'])->name('students.index');
        Route::get('/students/{user_id}/edit', [EducatorController::class, 'edit'])->name('students.edit');
        Route::put('/students/{user_id}', [EducatorController::class, 'update'])->name('students.update');

        // Intervention routes
        Route::get('/intervention', [\App\Http\Controllers\Educator\InterventionController::class, 'index'])->name('intervention');
        Route::get('/intervention-data', [\App\Http\Controllers\Educator\InterventionController::class, 'getInterventionData'])->name('intervention-data');
        Route::get('/intervention/{id}/update', [\App\Http\Controllers\Educator\InterventionController::class, 'update'])->name('intervention.update');
        Route::put('/intervention/{id}', [\App\Http\Controllers\Educator\InterventionController::class, 'store'])->name('intervention.store');
        Route::get('/intervention/create-test-data', [\App\Http\Controllers\Educator\InterventionController::class, 'createTestData'])->name('intervention.create-test-data');
        Route::get('/intervention/get-classes', [\App\Http\Controllers\Educator\InterventionController::class, 'getClasses'])->name('intervention.get-classes');
        Route::get('/intervention/get-submissions', [\App\Http\Controllers\Educator\InterventionController::class, 'getSubmissions'])->name('intervention.get-submissions');

        // Analytics routes
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::controller(\App\Http\Controllers\Educator\AnalyticsController::class)->group(function () {
                Route::get('/class-grades', 'showClassGrades')->name('class-grades');
                Route::get('/subject-progress', 'showSubjectProgress')->name('subject-progress');
                Route::get('/subject-intervention', 'showSubjectIntervention')->name('subject-intervention');
                Route::get('/class-progress', 'showClassProgress')->name('class-progress');
                Route::get('/intern-grades-progress', [\App\Http\Controllers\Educator\InternGradesAnalytics::class, 'index'])->name('intern-grades-progress');

                // AJAX endpoints for filtering
                Route::get('/schools', 'getSchools')->name('schools');
                Route::get('/classes/{schoolId}', 'getClassesBySchool')->name('classes');
                Route::get('/class-submissions/{schoolId}/{classId}', 'getClassSubmissions')->name('class-submissions');

                // Data endpoints
                Route::get('/class-grades-data', 'fetchClassGrades')->name('class-grades-data');
                Route::get('/subject-progress-data', 'fetchSubjectProgressData')->name('subject-progress-data');
                Route::get('/subject-intervention-data', 'fetchSubjectInterventionData')->name('subject-intervention-data');
                Route::get('/class-progress-data', 'fetchClassProgressData')->name('class-progress-data');
            });
        });

        // Intern grades analytics routes
        Route::prefix('intern-grades-analytics')->name('intern-grades-analytics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Educator\InternGradesAnalytics::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Educator\InternGradesAnalytics::class, 'getAnalyticsData'])->name('data');
            Route::get('/check-submissions', [\App\Http\Controllers\Educator\InternGradesAnalytics::class, 'checkSubmissions'])->name('check-submissions');
        });

        // Calendar routes (view-only)
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Educator\CalendarController::class, 'index'])->name('index');
            Route::get('/events', [\App\Http\Controllers\Educator\CalendarController::class, 'getEvents'])->name('events');
        });
    });





    // Training routes

    Route::prefix('training')->name('training.')->middleware(['auth', 'can:training-access'])->group(function () {
        Route::get('/dashboard', [TrainingController::class, 'dashboard'])->name('dashboard');
        Route::get('/students-info', [TrainingController::class, 'index'])->name('students-info');

        // Student Information Routes
        Route::get('/students/list', [TrainingController::class, 'getStudentsList'])->name('students.list');
        Route::get('/students', [TrainingController::class, 'index'])->name('students.index');
        Route::get('/students/{user_id}/view', [TrainingController::class, 'view'])->name('students.view');
        Route::get('/students/{user_id}/edit', [TrainingController::class, 'edit'])->name('students.edit');
        Route::put('/students/{user_id}', [TrainingController::class, 'update'])->name('students.update');
        Route::delete('/students/{user_id}', [TrainingController::class, 'destroy'])->name('students.destroy');

        // School Management Routes
        Route::get('/manage-students', [SchoolController::class, 'index'])->name('manage-students');
        Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
        Route::get('/api/students', [SchoolController::class, 'getStudentsList'])->name('api.students.list');

        // Class routes with school context
        Route::get('schools/{school}/classes/create', [ClassController::class, 'create'])->name('classes.create');
        Route::post('schools/{school}/classes', [ClassController::class, 'store'])->name('classes.store');
        Route::get('students/by-batch', [ClassController::class, 'getStudentsList'])->name('students.by-batch');
        Route::get('api/schools/{school}/classes', [ClassController::class, 'getClassesBySchool'])->name('api.classes.by-school');
        Route::get('api/schools/{school}/interns', [\App\Http\Controllers\Training\InternGradeController::class, 'getInternsBySchoolAndClass'])->name('api.interns.by-school');
        Route::resource('classes', ClassController::class)->except(['create', 'store']);


        // Intern grades routes
        Route::prefix('intern-grades')->name('intern-grades.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Training\InternGradeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Training\InternGradeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Training\InternGradeController::class, 'store'])->name('store');
            Route::get('/{internGrade}/edit', [\App\Http\Controllers\Training\InternGradeController::class, 'edit'])->name('edit');
            Route::put('/{internGrade}', [\App\Http\Controllers\Training\InternGradeController::class, 'update'])->name('update');
            Route::delete('/{internGrade}', [\App\Http\Controllers\Training\InternGradeController::class, 'destroy'])->name('destroy');

            // AJAX routes for dynamic data loading
            Route::get('/students/{school_id}', [\App\Http\Controllers\Training\InternGradeController::class, 'getInternsBySchoolAndClass'])->name('students');
        });

        // Analytics routes
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::controller(\App\Http\Controllers\Training\AnalyticsController::class)->group(function () {
                Route::get('/class-grades', 'showClassGrades')->name('class-grades');
                Route::get('/subject-progress', 'showSubjectProgress')->name('subject-progress');
                Route::get('/subject-intervention', 'showSubjectIntervention')->name('subject-intervention');
                Route::get('/class-progress', 'showClassProgress')->name('class-progress');

                // AJAX endpoints for filtering
                Route::get('/schools', 'getSchools')->name('schools');
                Route::get('/classes/{schoolId}', 'getClassesBySchool')->name('classes');
                Route::get('/class-submissions/{schoolId}/{classId}', 'getClassSubmissions')->name('class-submissions');

                // Data endpoints
                Route::get('/class-grades-data', 'fetchClassGrades')->name('class-grades-data');
                Route::get('/subject-progress-data', 'fetchSubjectProgressData')->name('subject-progress-data');
                Route::get('/subject-intervention-data', 'fetchSubjectInterventionData')->name('subject-intervention-data');
                Route::get('/class-progress-data', 'fetchClassProgressData')->name('class-progress-data');
            });
        });

        // Intern grades analytics routes
        Route::prefix('intern-grades-analytics')->name('intern-grades-analytics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Training\InternGradesAnalyticsController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Training\InternGradesAnalyticsController::class, 'getAnalyticsData'])->name('data');
            Route::get('/check-submissions', [\App\Http\Controllers\Training\InternGradesAnalyticsController::class, 'checkSubmissions'])->name('check-submissions');
        });

        // Intervention routes (view-only for training)
        Route::prefix('intervention')->name('intervention.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Training\InterventionController::class, 'index'])->name('index');
            Route::get('/classes/{school_id}', [\App\Http\Controllers\Training\InterventionController::class, 'getClasses'])->name('classes');
            Route::get('/subjects', [\App\Http\Controllers\Training\InterventionController::class, 'getSubjects'])->name('subjects');
            Route::get('/submissions', [\App\Http\Controllers\Training\InterventionController::class, 'getSubmissions'])->name('submissions');
        });

        //Grade submission routes
        Route::controller(GradeSubmissionController::class)->group(function () {
            Route::get('/grade-submissions', 'index')->name('grade-submissions.index');
            Route::get('/grade-submissions/create', 'create')->name('grade-submissions.create');
            Route::post('/grade-submissions', 'store')->name('grade-submissions.store');
            Route::get('/grade-submissions/recent', 'recent')->name('grade-submissions.recent');
            Route::get('/grade-submissions/monitor', 'monitor')->name('grade-submissions.monitor');
            Route::get('/subjects/by-school-and-class', 'getSubjectsBySchoolAndClass')->name('subjects.by-school-class');
            Route::get('/grade-submissions/{gradeSubmission}', 'show')->name('grade-submissions.show');
            Route::delete('/grade-submissions/{gradeSubmission}', 'destroy')->name('grade-submissions.destroy');
            Route::get('/grade-submissions/{gradeSubmission}/students/{student}', 'viewStudentSubmission')->name('grade-submissions.view');
            Route::post('/grade-submissions/{gradeSubmission}/update-status', 'updateStatus')->name('grade-submissions.update-status');
            Route::put('/grade-submissions/{gradeSubmission}/verify', 'verify')->name('grade-submissions.verify');
            Route::put('/grade-submissions/{gradeSubmission}/reject', 'reject')->name('grade-submissions.reject');
            Route::get('/grade-submissions/{gradeSubmission}/proof/{student}', 'viewProof')->name('grade-submissions.view-proof');
            Route::post('/grade-submissions/{gradeSubmission}/proof/{student}/status', 'updateProofStatus')->name('grade-submissions.update-proof-status');

            // Temporary route to fix subject associations for a submission
            Route::get('/grade-submissions/{gradeSubmission}/fix-subjects', 'fixSubmissionSubjects')->name('grade-submissions.fix-subjects');
        });

        // Calendar routes
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/', [CalendarController::class, 'index'])->name('index');
            Route::get('/manage', [CalendarController::class, 'manage'])->name('manage');
            Route::get('/create', [CalendarController::class, 'create'])->name('create');
            Route::post('/', [CalendarController::class, 'store'])->name('store');
            Route::get('/{calendarEvent}/edit', [CalendarController::class, 'edit'])->name('edit');
            Route::put('/{calendarEvent}', [CalendarController::class, 'update'])->name('update');
            Route::delete('/{calendarEvent}', [CalendarController::class, 'destroy'])->name('destroy');
            Route::get('/events', [CalendarController::class, 'getEvents'])->name('events');
        });

        // Internship routes
        Route::prefix('internship')->name('internship.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Training\InternshipController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Training\InternshipController::class, 'store'])->name('store');
            Route::get('/classes/{school}', [\App\Http\Controllers\Training\InternshipController::class, 'getClassesBySchool'])->name('classes');
            Route::get('/students/{class}', [\App\Http\Controllers\Training\InternshipController::class, 'getStudentsByClass'])->name('students');
            Route::get('/{internship}/edit', [\App\Http\Controllers\Training\InternshipController::class, 'edit'])->name('edit');
            Route::put('/{internship}', [\App\Http\Controllers\Training\InternshipController::class, 'update'])->name('update');
            Route::delete('/{internship}', [\App\Http\Controllers\Training\InternshipController::class, 'destroy'])->name('destroy');
        });
    }); // <-- ✅ properly closed here





    // Student routes
    Route::prefix('student')->name('student.')->middleware('can:student-access')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/grades', [StudentController::class, 'grades'])->name('grades');
        Route::get('/grade-submissions/{submissionId}', [StudentController::class, 'showSubmissionForm'])->name('submit-grades.show');
        Route::post('/grade-submissions/{submissionId}', [StudentController::class, 'submitGrades'])->name('submit-grades.store');
        Route::get('/view-submission/{submissionId}', [StudentController::class, 'viewSubmission'])->name('view-submission');
        Route::get('/grade-submissions', [StudentController::class, 'submissionsList'])->name('grade-submissions.list');

        // Calendar routes (view-only)
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\CalendarController::class, 'index'])->name('index');
            Route::get('/events', [\App\Http\Controllers\Student\CalendarController::class, 'getEvents'])->name('events');
        });
    });


});

// Test route for email notifications (remove in production)
Route::get('/test-email', function () {
    // Find a test student and grade submission
    $student = \App\Models\PNUser::where('user_role', 'student')->first();
    $gradeSubmission = \App\Models\GradeSubmission::with(['school', 'classModel', 'subjects'])->first();

    if (!$student || !$gradeSubmission) {
        return response()->json([
            'error' => 'No test data found. Please create a student and grade submission first.',
            'student_found' => $student ? true : false,
            'submission_found' => $gradeSubmission ? true : false
        ]);
    }

    try {
        Mail::to($student->user_email)->send(new GradeSubmissionNotification($student, $gradeSubmission));

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully!',
            'student_email' => $student->user_email,
            'submission_id' => $gradeSubmission->id,
            'note' => 'Check your Laravel logs (storage/logs/laravel.log) to see the email content since MAIL_MAILER is set to "log"'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to send email: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test-email');
