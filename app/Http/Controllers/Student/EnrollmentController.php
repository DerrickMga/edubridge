<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Enroll the authenticated student in a course.
     *
     * - Free courses → enroll immediately and redirect to student dashboard
     * - Paid courses  → redirect to payment checkout
     */
    public function store(Request $request, Course $course)
    {
        abort_if($course->status !== 'published', 404);

        $student = $request->user();

        // Already enrolled?
        if ($student->enrollments()->where('course_id', $course->id)->exists()) {
            return redirect()->route('student.dashboard')
                ->with('info', "You're already enrolled in \"{$course->title}\".");
        }

        // Prerequisite gate
        if ($course->prerequisite_course_id) {
            $prereqDone = \App\Models\Certificate::where('student_id', $student->id)
                ->where('course_id', $course->prerequisite_course_id)->exists();
            if (! $prereqDone) {
                $prereq = Course::find($course->prerequisite_course_id);
                return redirect()->route('courses.show', $course)
                    ->with('error', 'Complete the prerequisite course first: '.($prereq->title ?? '#'.$course->prerequisite_course_id));
            }
        }

        // Active subscription → enrol immediately
        if ($student->activeSubscription()) {
            $student->enrollments()->attach($course->id, ['status' => 'active', 'access_period' => 'subscription', 'expires_at' => $student->activeSubscription()->expires_at]);
            return redirect()->route('student.dashboard')->with('success', "Enrolled in \"{$course->title}\" via your subscription.");
        }

        // Promotional free-pass active?
        $promoUntil = Carbon::parse(Setting::get('promo_free_until', '2026-08-31'))->endOfDay();
        $inPromo    = now()->lte($promoUntil);

        // Free course OR promo period — enroll directly
        if ($inPromo || (($course->price_usd ?? 0) <= 0 && ($course->price_zwg ?? 0) <= 0)) {
            $pivotData = ['status' => 'active', 'access_period' => 'termly', 'expires_at' => now()->addMonths(3)];
            $student->enrollments()->attach($course->id, $pivotData);

            $msg = $inPromo && (($course->price_usd ?? 0) > 0)
                ? "You've been enrolled in \"{$course->title}\" — completely FREE for 3 months! 🎉"
                : "You've been enrolled in \"{$course->title}\"! Start learning now.";

            return redirect()->route('student.dashboard')->with('success', $msg);
        }

        // Paid course — go to checkout
        return redirect()->route('payments.checkout', $course);
    }

    /**
     * Show a course detail / landing page (public, but enrollment-aware if auth'd).
     */
    public function show(Request $request, Course $course)
    {
        abort_if($course->status !== 'published', 404);

        $course->load(['teacher', 'lessons' => fn($q) => $q->where('status', 'published')->orderBy('order')]);
        $course->loadCount('lessons');

        $isEnrolled = $request->user()
            ? $request->user()->enrollments()->where('course_id', $course->id)->exists()
            : false;

        $isPromo = now()->lte(
            Carbon::parse(Setting::get('promo_free_until', '2026-08-31'))->endOfDay()
        );

        // First incomplete lesson for enrolled students
        $continueLesson = null;
        if ($isEnrolled && $request->user()) {
            $completedIds = \App\Models\LessonProgress::where('student_id', $request->user()->id)
                ->where('course_id', $course->id)
                ->where('completed', true)
                ->pluck('lesson_id');

            $continueLesson = $course->lessons->firstWhere(fn($l) => ! $completedIds->contains($l->id));
        }

        $isPromo = now()->lte(
            Carbon::parse(Setting::get('promo_free_until', '2026-08-31'))->endOfDay()
        );

        return view('courses.show', compact('course', 'isEnrolled', 'continueLesson', 'isPromo'));
    }
}
