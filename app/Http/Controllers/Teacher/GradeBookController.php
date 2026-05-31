<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;

class GradeBookController extends Controller
{
    /** Per-course grade book — students × assessments matrix. */
    public function show(Course $course)
    {
        $isOwner = $course->teacher_id === auth()->id();
        $isCoTeacher = $course->teachers()->where('users.id', auth()->id())->exists();
        abort_unless($isOwner || $isCoTeacher, 403);

        $students    = $course->enrollments()->orderBy('name')->get();
        $assignments = $course->assignments()->where('is_published', true)->orderBy('id')->get();
        $quizzes     = $course->quizzes()->where('is_published', true)->orderBy('id')->get();

        $subs = AssignmentSubmission::whereIn('assignment_id', $assignments->pluck('id'))
            ->get()->groupBy(fn ($s) => $s->student_id.'-'.$s->assignment_id);
        $attempts = QuizAttempt::whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()->groupBy(fn ($a) => $a->student_id.'-'.$a->quiz_id);

        return view('teacher.gradebook.show', compact('course', 'students', 'assignments', 'quizzes', 'subs', 'attempts'));
    }
}
