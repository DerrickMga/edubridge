<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('teacher')->withCount('enrollments');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($teacher = $request->input('teacher_id')) {
            $query->where('teacher_id', $teacher);
        }

        $courses  = $query->latest()->paginate(20)->withQueryString();
        $teachers = User::role('teacher')->orderBy('name')->get(['id', 'name']);

        return view('admin.courses.index', compact('courses', 'teachers'));
    }

    public function toggleStatus(Course $course)
    {
        $course->status = $course->status === 'published' ? 'draft' : 'published';
        $course->save();

        return back()->with('success', 'Course "' . $course->title . '" is now ' . $course->status . '.');
    }

    /** Force-assign (or unassign) a teacher from the admin panel */
    public function assignTeacher(Request $request, Course $course)
    {
        $data = $request->validate([
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($data['teacher_id']) {
            $teacher = User::findOrFail($data['teacher_id']);
            abort_if(!$teacher->hasRole('teacher') && !$teacher->isAdmin(), 422, 'User is not a teacher.');
        }

        $course->update(['teacher_id' => $data['teacher_id'] ?? null]);

        $name = $data['teacher_id']
            ? User::find($data['teacher_id'])->name
            : 'unassigned';

        return back()->with('success', 'Course "' . $course->title . '" assigned to ' . $name . '.');
    }

    public function destroy(Course $course)
    {
        $title = $course->title;
        $course->delete();

        return back()->with('success', 'Course "' . $title . '" was deleted.');
    }
}