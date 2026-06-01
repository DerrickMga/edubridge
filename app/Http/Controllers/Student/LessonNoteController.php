<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonNote;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    public function index(Request $request)
    {
        $notes = $request->user()->lessonNotes()
            ->with('lesson.course')
            ->latest()
            ->paginate(30);
        return view('student.notes.index', compact('notes'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        $course = $lesson->course;
        abort_unless($user->enrollments()->where('courses.id', $course->id)->exists(), 403);

        $data = $request->validate([
            'body'              => 'required|string|max:5000',
            'timestamp_seconds' => 'nullable|integer|min:0|max:86400',
        ]);

        $note = LessonNote::create($data + [
            'user_id'   => $user->id,
            'lesson_id' => $lesson->id,
            'course_id' => $course->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['note' => $note]);
        }
        return back()->with('success', 'Note saved.');
    }

    public function destroy(Request $request, LessonNote $note)
    {
        abort_unless($note->user_id === $request->user()->id, 403);
        $note->delete();
        return back()->with('success', 'Note deleted.');
    }
}
