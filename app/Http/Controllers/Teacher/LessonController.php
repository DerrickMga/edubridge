<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function create(Course $course)
    {
        $this->authorize('update', $course);
        return view('teacher.lessons.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'video_url'        => 'nullable|url|max:500',
            'youtube_video_id' => 'nullable|string|max:50',
            'duration_seconds' => 'nullable|integer|min:0',
            'order'            => 'nullable|integer|min:0',
            'status'           => 'in:draft,published',
        ]);
        $validated['order'] = $validated['order'] ?? ($course->lessons()->max('order') + 1);
        $course->lessons()->create($validated);
        return redirect()->route('teacher.courses.show', $course)->with('success', 'Lesson added.');
    }

    public function edit(Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);
        return view('teacher.lessons.edit', ['lesson' => $lesson, 'course' => $lesson->course]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);
        $lesson->update($request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'video_url'        => 'nullable|url|max:500',
            'youtube_video_id' => 'nullable|string|max:50',
            'duration_seconds' => 'nullable|integer|min:0',
            'order'            => 'nullable|integer|min:0',
            'status'           => 'in:draft,published',
        ]));
        return redirect()->route('teacher.courses.show', $lesson->course)->with('success', 'Lesson updated.');
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorize('update', $lesson->course);
        $course = $lesson->course;
        $lesson->delete();
        return redirect()->route('teacher.courses.show', $course)->with('success', 'Lesson deleted.');
    }
}
