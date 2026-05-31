<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        // Free course — enroll directly
        if (($course->price_usd ?? 0) <= 0 && ($course->price_zwg ?? 0) <= 0) {
            $student->enrollments()->attach($course->id, ['status' => 'active']);

            return redirect()->route('student.dashboard')
                ->with('success', "You've been enrolled in \"{$course->title}\"! Start learning now.");
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

        // First incomplete lesson for enrolled students
        $continueLesson = null;
        if ($isEnrolled && $request->user()) {
            $completedIds = \App\Models\LessonProgress::where('student_id', $request->user()->id)
                ->where('course_id', $course->id)
                ->where('completed', true)
                ->pluck('lesson_id');

            $continueLesson = $course->lessons->firstWhere(fn($l) => ! $completedIds->contains($l->id));
        }

        return view('courses.show', compact('course', 'isEnrolled', 'continueLesson'));
    }
}
