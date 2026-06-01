<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        $user = auth()->user();
        $access = app(\App\Services\LessonAccessService::class);
        abort_unless($access->canAccess($user, $lesson), 403, 'This lesson is locked. Either you need to enrol, or it unlocks on a later date.');

        $course  = $lesson->course()->with('teacher')->first();
        $lessons = $course->lessons()->where('status', 'published')->orderBy('order')->get();
        $idx     = $lessons->search(fn($l) => $l->id === $lesson->id);
        $prev    = $idx > 0 ? $lessons[$idx - 1] : null;
        $next    = ($idx !== false && $idx < $lessons->count() - 1) ? $lessons[$idx + 1] : null;

        $lessonResources = $lesson->resources()->get();
        $courseResources = $course->resources()->whereNull('lesson_id')->get();

        return view('student.lessons.show', compact('lesson', 'course', 'lessons', 'prev', 'next', 'lessonResources', 'courseResources'));
    }
}
