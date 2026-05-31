<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Models\User;

class StudentProgressController extends Controller
{
    /** Per-course student progress table. */
    public function show(Course $course)
    {
        $isOwner = $course->teacher_id === auth()->id();
        $isCoTeacher = $course->teachers()->where('users.id', auth()->id())->exists();
        abort_unless($isOwner || $isCoTeacher, 403);

        $students = $course->enrollments()->orderBy('name')->get();
        $totalLessons = $course->lessons()->count();

        $progressByStudent = LessonProgress::where('course_id', $course->id)
            ->where('completed', true)
            ->selectRaw('student_id, COUNT(*) as done, MAX(completed_at) as last_at')
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        $rows = $students->map(function (User $s) use ($progressByStudent, $totalLessons) {
            $r = $progressByStudent->get($s->id);
            $done = (int) ($r->done ?? 0);
            return (object) [
                'student'  => $s,
                'done'     => $done,
                'total'    => $totalLessons,
                'percent'  => $totalLessons > 0 ? (int) round($done * 100 / $totalLessons) : 0,
                'last_at'  => $r->last_at ?? null,
            ];
        });

        return view('teacher.progress.show', compact('course', 'rows', 'totalLessons'));
    }

    /** Drill-down to a single student. */
    public function student(Course $course, User $student)
    {
        abort_unless($course->teacher_id === auth()->id()
            || $course->teachers()->where('users.id', auth()->id())->exists(), 403);

        $lessons = $course->lessons()->get();
        $progress = LessonProgress::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->get()->keyBy('lesson_id');

        $submissions = \App\Models\AssignmentSubmission::whereIn('assignment_id', $course->assignments()->pluck('id'))
            ->where('student_id', $student->id)->with('assignment')->get();
        $attempts = \App\Models\QuizAttempt::whereIn('quiz_id', $course->quizzes()->pluck('id'))
            ->where('student_id', $student->id)->with('quiz')->latest('completed_at')->get();

        return view('teacher.progress.student', compact('course', 'student', 'lessons', 'progress', 'submissions', 'attempts'));
    }
}
