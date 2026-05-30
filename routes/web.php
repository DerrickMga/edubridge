<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Student\CompanionController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\LessonController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// ─── Public (landing) ─────────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/about',    fn() => view('about'))->name('about');
Route::get('/courses',  fn() => view('courses.index', ['courses' => \App\Models\Course::published()->with('teacher')->paginate(12)]))->name('courses.index');

// ─── Auth (Breeze) ─────────────────────────────────────────────────────────────
require __DIR__.'/auth.php';

// ─── Redirect after login based on role ───────────────────────────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        default   => redirect()->route('student.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// ─── Student ──────────────────────────────────────────────────────────────────
Route::prefix('student')->name('student.')->middleware(['auth', 'verified', 'role:student,admin'])->group(function () {
    Route::get('dashboard', [StudentDashboard::class, 'index'])->name('dashboard');
    Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

    // AI Companion
    Route::get('companion',                       [CompanionController::class, 'index'])->name('companion.index');
    Route::post('companion',                      [CompanionController::class, 'store'])->name('companion.store');
    Route::get('companion/{conversation}',        [CompanionController::class, 'show'])->name('companion.show');
    Route::post('companion/{conversation}/send',  [CompanionController::class, 'send'])->name('companion.send');
});

// ─── Teacher ──────────────────────────────────────────────────────────────────
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'verified', 'role:teacher,admin'])->group(function () {
    Route::get('dashboard', [TeacherDashboard::class, 'index'])->name('dashboard');
    Route::resource('courses', TeacherCourseController::class);
});

// ─── Admin ────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('dashboard',   [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users',  AdminUserController::class);
});

// ─── Payments ─────────────────────────────────────────────────────────────────
Route::prefix('payments')->name('payments.')->middleware(['auth'])->group(function () {
    Route::get('{course}/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('{course}/initiate',[PaymentController::class, 'initiate'])->name('initiate');
    Route::get('success',           [PaymentController::class, 'success'])->name('success');
    Route::get('cancel',            [PaymentController::class, 'cancel'])->name('cancel');
});
