<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\AiController as AdminAiController;
use App\Http\Controllers\Student\AchievementsController;
use App\Http\Controllers\Student\CompanionController;
use App\Http\Controllers\Student\CompanionUploadController;
use App\Http\Controllers\Student\LiveSessionController as StudentLiveSessionController;
use App\Http\Controllers\Student\LeaderboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\LessonController;
use App\Http\Controllers\Student\ProgressController as StudentProgressController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Teacher\AnnouncementController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\LiveSessionController;
use App\Http\Controllers\Teacher\QuizController as TeacherQuizController;
use App\Http\Controllers\Teacher\RecordingController;
use App\Http\Controllers\Teacher\SessionLogController;
use App\Http\Controllers\Admin\SessionReportController as AdminSessionReportController;
use App\Http\Controllers\Admin\TeacherPaymentController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Teacher\ResourceController;
use App\Http\Controllers\Teacher\AiToolsController;
use App\Http\Controllers\Teacher\VerificationController as TeacherVerificationController;
use App\Http\Controllers\Teacher\SettlementController as TeacherSettlementController;
use App\Http\Controllers\Teacher\TransactionController as TeacherTransactionController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\SettlementController as AdminSettlementController;
use App\Http\Controllers\Student\TransactionController as StudentTransactionController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/about',    fn() => view('about'))->name('about');
Route::get('/subjects', function () {
    $subjects = require resource_path('data/subjects.php');
    return view('subjects.index', compact('subjects'));
})->name('subjects.index');
Route::get('/curriculum', fn() => view('curriculum-guide'))->name('curriculum.guide');
Route::get('/courses', function () {
    $query = \App\Models\Course::published()->with('teacher')->withCount('lessons');
    if (request('subject')) {
        $query->where('subject', request('subject'));
    }
    if (request('search')) {
        $query->where(function ($q) {
            $q->where('title', 'like', '%'.request('search').'%')
              ->orWhere('subject', 'like', '%'.request('search').'%');
        });
    }
    $courses = $query->paginate(12);

    // Eager-load current user's enrollments for enrolled badges
    if (auth()->check()) {
        auth()->user()->load('enrollments');
    }

    return view('courses.index', compact('courses'));
})->name('courses.index');

// Public course detail page (enrollment-aware)
Route::get('/courses/{course}', [EnrollmentController::class, 'show'])->name('courses.show');

// Certificate public verification
Route::get('/verify/{number}', function (string $number) {
    $cert = \App\Models\Certificate::where('certificate_number', $number)
        ->with(['student', 'course'])->firstOrFail();
    return view('certificates.verify', compact('cert'));
})->name('certificates.verify');

require __DIR__.'/auth.php';

Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        default   => redirect()->route('student.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('student')->name('student.')->middleware(['auth', 'verified', 'role:student,admin'])->group(function () {
    Route::get('dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

    // Progress tracking
    Route::post('lessons/{lesson}/complete',    [StudentProgressController::class, 'markComplete'])->name('lessons.complete');
    Route::post('lessons/{lesson}/watch-time',  [StudentProgressController::class, 'updateWatchTime'])->name('lessons.watch-time');

    // Quizzes
    Route::get('quizzes/{quiz}',         [StudentQuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/submit', [StudentQuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('quizzes/{quiz}/result/{attempt}', [StudentQuizController::class, 'result'])->name('quizzes.result');

    // Certificate
    Route::get('courses/{course}/certificate', function (\App\Models\Course $course) {
        $certificate = \App\Models\Certificate::where('student_id', auth()->id())
            ->where('course_id', $course->id)->with(['student', 'course'])->first();
        return view('student.certificate', compact('certificate'));
    })->name('courses.certificate');

    // Gamification
    Route::get('achievements', [AchievementsController::class, 'index'])->name('achievements');
    Route::get('leaderboard',  [LeaderboardController::class, 'index'])->name('leaderboard');

    // Live Session gated join (validates active enrolment before releasing the Zoom URL)
    Route::get('live-sessions/{liveSession}/join', [StudentLiveSessionController::class, 'join'])->name('live-sessions.join');

    // Course enrollment
    Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');

    // Transaction history
    Route::get('transactions', [StudentTransactionController::class, 'index'])->name('transactions.index');

    // AI Companion (AI rate limit applied to generative POST endpoints)
    Route::get('companion',                      [CompanionController::class, 'index'])->name('companion.index');
    Route::post('companion',                     [CompanionController::class, 'store'])->name('companion.store');
    Route::get('companion/{conversation}',       [CompanionController::class, 'show'])->name('companion.show');
    Route::patch('companion/{conversation}/prefs', [CompanionController::class, 'updatePreferences'])->name('companion.prefs');
    Route::post('companion/{conversation}/upload', [CompanionUploadController::class, 'store'])->name('companion.upload');

    Route::middleware('throttle:ai')->group(function () {
        Route::post('companion/{conversation}/send',       [CompanionController::class, 'send'])->name('companion.send');
        Route::post('companion/{conversation}/study-plan', [CompanionController::class, 'studyPlan'])->name('companion.study-plan');
        Route::post('companion/{conversation}/notes',      [CompanionController::class, 'notes'])->name('companion.notes');
    });
});

Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'verified', 'role:teacher,admin'])->group(function () {
    Route::get('dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');

    // Courses (resource controller)
    Route::resource('courses', TeacherCourseController::class);

    // Lessons (shallow nested under courses)
    Route::get('courses/{course}/lessons/create', [TeacherLessonController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/lessons',        [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::get('lessons/{lesson}/edit',            [TeacherLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}',                 [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}',              [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');

    // Live Sessions
    Route::get('live-sessions/create',                [LiveSessionController::class, 'create'])->name('live-sessions.create');
    Route::post('live-sessions',                      [LiveSessionController::class, 'store'])->name('live-sessions.store');
    Route::get('live-sessions/{liveSession}/edit',    [LiveSessionController::class, 'edit'])->name('live-sessions.edit');
    Route::put('live-sessions/{liveSession}',         [LiveSessionController::class, 'update'])->name('live-sessions.update');
    Route::delete('live-sessions/{liveSession}',      [LiveSessionController::class, 'destroy'])->name('live-sessions.destroy');

    // Recordings (nested under live sessions)
    Route::get('live-sessions/{liveSession}/recordings',                        [RecordingController::class, 'index'])->name('recordings.index');
    Route::post('live-sessions/{liveSession}/recordings',                       [RecordingController::class, 'store'])->name('recordings.store');
    Route::delete('live-sessions/{liveSession}/recordings/{recording}',         [RecordingController::class, 'destroy'])->name('recordings.destroy');

    // Content Library (resources)
    Route::get('courses/{course}/resources',               [ResourceController::class, 'index'])->name('resources.index');
    Route::post('courses/{course}/resources',              [ResourceController::class, 'store'])->name('resources.store');
    Route::delete('courses/{course}/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');

    // Assignments
    Route::get('courses/{course}/assignments',                             [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('courses/{course}/assignments/create',                      [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('courses/{course}/assignments',                            [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('courses/{course}/assignments/{assignment}',                [AssignmentController::class, 'show'])->name('assignments.show');
    Route::put('courses/{course}/assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('assignments.grade');

    // Quizzes
    Route::get('courses/{course}/quizzes',                         [TeacherQuizController::class, 'index'])->name('quizzes.index');
    Route::get('courses/{course}/quizzes/create',                  [TeacherQuizController::class, 'create'])->name('quizzes.create');
    Route::post('courses/{course}/quizzes',                        [TeacherQuizController::class, 'store'])->name('quizzes.store');
    Route::get('courses/{course}/quizzes/{quiz}',                  [TeacherQuizController::class, 'show'])->name('quizzes.show');
    Route::patch('courses/{course}/quizzes/{quiz}/toggle',         [TeacherQuizController::class, 'togglePublish'])->name('quizzes.toggle');

    // Announcements
    Route::post('courses/{course}/announcements',              [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('courses/{course}/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Session Hour Logs
    Route::post('live-sessions/{liveSession}/log', [SessionLogController::class, 'store'])->name('sessions.log');

    // KYC Verification
    Route::get('verification',  [TeacherVerificationController::class, 'index'])->name('verification.index');
    Route::post('verification', [TeacherVerificationController::class, 'store'])->name('verification.store');

    // Settlements
    Route::get('settlements',  [TeacherSettlementController::class, 'index'])->name('settlements.index');
    Route::post('settlements', [TeacherSettlementController::class, 'store'])->name('settlements.store');

    // Transactions
    Route::get('transactions', [TeacherTransactionController::class, 'index'])->name('transactions.index');

    // Teacher AI Tools
    Route::get('ai-tools',             [AiToolsController::class, 'index'])->name('ai-tools.index');
    Route::post('ai-tools/summarise',  [AiToolsController::class, 'summarise'])->name('ai-tools.summarise');
    Route::post('ai-tools/notes',      [AiToolsController::class, 'generateNotes'])->name('ai-tools.notes');
    Route::post('ai-tools/study-plan', [AiToolsController::class, 'studyPlan'])->name('ai-tools.study-plan');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('dashboard',  [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class);

    // AI Session Reports
    Route::get('session-reports',                            [AdminSessionReportController::class, 'index'])->name('session-reports.index');
    Route::get('session-reports/{session}',                  [AdminSessionReportController::class, 'show'])->name('session-reports.show');
    Route::post('session-reports/{session}/reprocess',       [AdminSessionReportController::class, 'reprocess'])->name('session-reports.reprocess');

    // Teacher Payments
    Route::get('teacher-payments',                           [TeacherPaymentController::class, 'index'])->name('teacher-payments.index');
    Route::post('teacher-payments/{payment}/approve',        [TeacherPaymentController::class, 'approve'])->name('teacher-payments.approve');
    Route::post('teacher-payments/{payment}/reject',         [TeacherPaymentController::class, 'reject'])->name('teacher-payments.reject');
    Route::post('teacher-payments/{payment}/mark-paid',      [TeacherPaymentController::class, 'markPaid'])->name('teacher-payments.mark-paid');
    Route::post('teacher-payments/rate/{teacher}',           [TeacherPaymentController::class, 'updateRate'])->name('teacher-payments.update-rate');

    // Course Management
    Route::get('courses',                                    [AdminCourseController::class, 'index'])->name('courses.index');
    Route::patch('courses/{course}/toggle-status',           [AdminCourseController::class, 'toggleStatus'])->name('courses.toggle-status');
    Route::delete('courses/{course}',                        [AdminCourseController::class, 'destroy'])->name('courses.destroy');

    // AI Tools
    Route::get('ai-tools',                                   [AdminAiController::class, 'index'])->name('ai-tools');
    Route::post('ai-tools/test-prompt',                      [AdminAiController::class, 'testPrompt'])->name('ai-tools.test');
    Route::post('ai-tools/broadcast-draft',                  [AdminAiController::class, 'broadcastDraft'])->name('ai-tools.broadcast');
    Route::post('ai-tools/generate-description',             [AdminAiController::class, 'generateCourseDescription'])->name('ai-tools.course-desc');
    Route::post('ai-tools/generate-quiz',                    [AdminAiController::class, 'generateQuiz'])->name('ai-tools.quiz');

    // KYC Verifications
    Route::get('verifications',                                  [AdminVerificationController::class, 'index'])->name('verifications.index');
    Route::get('verifications/{verification}',                   [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('verifications/{verification}/approve',          [AdminVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('verifications/{verification}/reject',           [AdminVerificationController::class, 'reject'])->name('verifications.reject');
    Route::get('verifications/{verification}/download/{field}',  [AdminVerificationController::class, 'downloadDocument'])->name('verifications.download');

    // Settlements
    Route::get('settlements',                                    [AdminSettlementController::class, 'index'])->name('settlements.index');
    Route::get('settlements/reconciliation',                     [AdminSettlementController::class, 'reconciliation'])->name('settlements.reconciliation');
    Route::get('settlements/{settlement}',                       [AdminSettlementController::class, 'show'])->name('settlements.show');
    Route::post('settlements/{settlement}/approve',              [AdminSettlementController::class, 'approve'])->name('settlements.approve');
    Route::post('settlements/{settlement}/processing',           [AdminSettlementController::class, 'markProcessing'])->name('settlements.processing');
    Route::post('settlements/{settlement}/paid',                 [AdminSettlementController::class, 'markPaid'])->name('settlements.paid');
    Route::post('settlements/{settlement}/reject',               [AdminSettlementController::class, 'reject'])->name('settlements.reject');

    // Platform Settings
    Route::get('settings/pricing',                           [AdminSettingController::class, 'index'])->name('settings.pricing');
    Route::patch('settings/pricing',                         [AdminSettingController::class, 'update'])->name('settings.pricing.update');
});

Route::middleware('auth')->group(function () {
    Route::get('profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Profile avatar removal
    Route::delete('profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');

    // Notifications
    Route::post('notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read-all');
});

Route::prefix('payments')->name('payments.')->middleware(['auth', 'throttle:payments'])->group(function () {
    Route::get('{course}/checkout',  [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('{course}/initiate', [PaymentController::class, 'initiate'])->name('initiate');
    Route::get('success',            [PaymentController::class, 'success'])->name('success');
    Route::get('cancel',             [PaymentController::class, 'cancel'])->name('cancel');
});

// Payment webhooks (no auth, signature-verified inside controllers)
Route::post('payments/webhook/{provider}', [PaymentController::class, 'webhook'])
    ->name('payments.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
