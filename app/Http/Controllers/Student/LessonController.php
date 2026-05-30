<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        $user = auth()->user();
        $enrolled = $user->enrollments()->where('course_id', $lesson->course_id)->exists();
        abort_if(!$enrolled && !$user->isAdmin(), 403, 'Enrol in this course to access lessons.');

        $course  = $lesson->course()->with('teacher')->first();
        $lessons = $course->lessons()->where('status', 'published')->orderBy('order')->get();
        $idx     = $lessons->search(fn($l) => $l->id === $lesson->id);
        $prev    = $idx > 0 ? $lessons[$idx - 1] : null;
        $next    = ($idx !== false && $idx < $lessons->count() - 1) ? $lessons[$idx + 1] : null;

        return view('student.lessons.show', compact('lesson', 'course', 'lessons', 'prev', 'next'));
    }
}
