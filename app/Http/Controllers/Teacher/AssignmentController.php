<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AssignmentSubmission, Course};
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $assignments = $course->assignments()->withCount('submissions')->latest()->get();
        return view('teacher.assignments.index', compact('course', 'assignments'));
    }

    public function create(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $lessons = $course->lessons()->get();
        return view('teacher.assignments.create', compact('course', 'lessons'));
    }

    public function store(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'type'         => ['required', 'in:written,file_upload,quiz,project'],
            'max_score'    => ['required', 'integer', 'min:1', 'max:1000'],
            'due_at'       => ['nullable', 'date', 'after:now'],
            'lesson_id'    => ['nullable', 'integer', 'exists:lessons,id'],
            'allow_late'   => ['boolean'],
            'is_published' => ['boolean'],
        ]);

        $data['teacher_id'] = auth()->id();
        $data['course_id']  = $course->id;
        Assignment::create($data);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Assignment created.');
    }

    public function show(Course $course, Assignment $assignment)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $submissions = $assignment->submissions()->with('student')->latest()->get();
        return view('teacher.assignments.show', compact('course', 'assignment', 'submissions'));
    }

    public function grade(Request $request, Course $course, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);

        $request->validate([
            'score'    => ['required', 'integer', 'min:0', 'max:'.$assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'score'      => $request->score,
            'feedback'   => $request->feedback,
            'status'     => 'graded',
            'graded_at'  => now(),
            'graded_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Submission graded.');
    }
}
