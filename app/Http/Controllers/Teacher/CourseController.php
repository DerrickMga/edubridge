<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = $request->user()->courses()->withCount(['lessons','enrollments'])->paginate(20);
        return view('teacher.courses.index', compact('courses'));
    }

    public function create() { return view('teacher.courses.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject'     => 'required|string|max:100',
            'grade_level' => 'required|string|max:20',
            'price_usd'   => 'numeric|min:0',
            'price_zwg'   => 'numeric|min:0',
        ]);
        $course = $request->user()->courses()->create($validated);
        return redirect()->route('teacher.courses.show', $course)->with('success', 'Course created.');
    }

    public function show(Course $course)
    {
        $this->authorize('update', $course);
        $course->load(['lessons','liveSessions']);
        return view('teacher.courses.show', compact('course'));
    }

    public function edit(Course $course)  { $this->authorize('update', $course); return view('teacher.courses.edit', compact('course')); }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $course->update($request->validate([
            'title' => 'required|string|max:255', 'description' => 'nullable|string',
            'subject' => 'required|string|max:100', 'grade_level' => 'required|string|max:20',
            'price_usd' => 'numeric|min:0', 'price_zwg' => 'numeric|min:0',
            'status' => 'in:draft,published,archived',
        ]));
        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return redirect()->route('teacher.courses.index')->with('success', 'Course deleted.');
    }
}
