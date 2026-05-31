<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Discussion, Course, Lesson};
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /** Lesson-level discussion thread */
    public function index(Request $request, Lesson $lesson)
    {
        $student = $request->user();
        $enrolled = $student->enrollments()->where('course_id', $lesson->course_id)->exists();
        abort_if(!$enrolled && !$student->isAdmin(), 403);

        $threads = Discussion::where('lesson_id', $lesson->id)
            ->whereNull('parent_id')
            ->with(['author', 'replies.author'])
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        return view('student.discussions.index', compact('lesson', 'threads'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $student = $request->user();
        $enrolled = $student->enrollments()->where('course_id', $lesson->course_id)->exists();
        abort_if(!$enrolled && !$student->isAdmin(), 403);

        $data = $request->validate([
            'body'      => ['required', 'string', 'min:3', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:discussions,id'],
        ]);

        Discussion::create([
            'course_id' => $lesson->course_id,
            'lesson_id' => $lesson->id,
            'author_id' => $student->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body'      => $data['body'],
        ]);

        return back()->with('success', 'Comment posted.');
    }

    public function destroy(Request $request, Discussion $discussion)
    {
        $user = $request->user();
        abort_if($discussion->author_id !== $user->id && !$user->isAdmin(), 403);
        $discussion->delete();
        return back()->with('success', 'Comment removed.');
    }
}
