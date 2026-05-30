<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Lesson, LessonProgress};
use App\Services\{CertificateService, GamificationService};
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(
        private readonly CertificateService $certService,
        private readonly GamificationService $gamification,
    ) {}

    public function markComplete(Request $request, Lesson $lesson)
    {
        $student = $request->user();

        LessonProgress::updateOrCreate(
            ['student_id' => $student->id, 'lesson_id' => $lesson->id],
            [
                'course_id'    => $lesson->course_id,
                'completed'    => true,
                'completed_at' => now(),
            ]
        );

        // Award XP for completing the lesson
        $this->gamification->onLessonComplete($student, $lesson);

        // Try to issue certificate if all lessons done
        $certificate = $this->certService->issue($student, $lesson->course);

        // Award course-complete XP if cert just issued
        if ($certificate && $certificate->wasRecentlyCreated) {
            $this->gamification->onCourseComplete($student, $lesson->course);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'completed'   => true,
                'progress'    => $lesson->course->progressFor($student->id),
                'certificate' => $certificate?->certificate_number,
            ]);
        }

        return back()->with('success', $certificate
            ? 'Lesson complete! You earned a certificate 🎓'
            : 'Lesson marked as complete.');
    }

    public function updateWatchTime(Request $request, Lesson $lesson)
    {
        $request->validate(['seconds' => ['required', 'integer', 'min:0']]);

        LessonProgress::updateOrCreate(
            ['student_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['course_id' => $lesson->course_id, 'watch_seconds' => $request->seconds]
        );

        return response()->json(['ok' => true]);
    }
}
