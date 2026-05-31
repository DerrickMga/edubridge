<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /** Teacher's assigned courses */
    public function index(Request $request)
    {
        $courses = Course::where('teacher_id', $request->user()->id)
            ->withCount(['lessons', 'enrollments'])
            ->orderBy('title')
            ->paginate(20);

        return view('teacher.courses.index', compact('courses'));
    }

    /** Browse all available platform courses to claim */
    public function browse(Request $request)
    {
        $user = $request->user();

        $courses = Course::whereNotIn('status', ['archived'])
            ->withCount(['lessons', 'enrollments'])
            ->orderByRaw("CASE WHEN teacher_id = ? THEN 0 WHEN teacher_id IS NULL THEN 1 ELSE 2 END", [$user->id])
            ->orderBy('grade_level')
            ->orderBy('title')
            ->get();

        return view('teacher.courses.browse', compact('courses'));
    }

    /** Assign this teacher to an available course */
    public function claim(Request $request, Course $course)
    {
        $this->authorize('claim', $course);

        $course->update(['teacher_id' => $request->user()->id]);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', "You are now teaching {$course->title}.");
    }

    /** Release this teacher's assignment from a course */
    public function release(Course $course)
    {
        $this->authorize('update', $course);

        $course->update(['teacher_id' => null]);

        return redirect()->route('teacher.courses.index')
            ->with('success', 'Course released back to the available pool.');
    }

    public function show(Course $course)
    {
        $this->authorize('update', $course);
        $course->load(['lessons', 'liveSessions', 'announcements']);
        return view('teacher.courses.show', compact('course'));
    }

    /** Only description and status are teacher-editable; title/subject/grade are canonical */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        return view('teacher.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $course->update($request->validate([
            'description' => 'nullable|string|max:3000',
            'status'      => 'required|in:draft,published,archived',
        ]));

        return back()->with('success', 'Course updated.');
    }
}
