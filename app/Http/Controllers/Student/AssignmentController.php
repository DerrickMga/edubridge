<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AssignmentSubmission, Course};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /** All assignments across enrolled courses */
    public function index(Request $request)
    {
        $student = $request->user();

        $courseIds = $student->enrollments()->pluck('courses.id');

        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->with(['course', 'lesson'])
            ->withCount('submissions')
            ->orderBy('due_at')
            ->get()
            ->map(function ($a) use ($student) {
                $a->my_submission = $a->submissionFor($student->id);
                return $a;
            });

        $pending   = $assignments->filter(fn($a) => is_null($a->my_submission) && (!$a->due_at || $a->due_at->isFuture()));
        $submitted = $assignments->filter(fn($a) => !is_null($a->my_submission));
        $overdue   = $assignments->filter(fn($a) => is_null($a->my_submission) && $a->due_at && $a->due_at->isPast());

        return view('student.assignments.index', compact('pending', 'submitted', 'overdue'));
    }

    /** Show one assignment and the student's submission */
    public function show(Request $request, Assignment $assignment)
    {
        $student = $request->user();

        // Must be enrolled in the course
        $enrolled = $student->enrollments()->where('course_id', $assignment->course_id)->exists();
        abort_if(!$enrolled && !$student->isAdmin(), 403, 'You are not enrolled in this course.');
        abort_if(!$assignment->is_published, 404);

        $assignment->load(['course', 'lesson']);
        $submission = $assignment->submissionFor($student->id);

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    /** Submit / resubmit an assignment */
    public function store(Request $request, Assignment $assignment)
    {
        $student = $request->user();

        $enrolled = $student->enrollments()->where('course_id', $assignment->course_id)->exists();
        abort_if(!$enrolled, 403);
        abort_if(!$assignment->is_published, 404);

        // Late submission check
        if ($assignment->due_at && $assignment->due_at->isPast() && !$assignment->allow_late) {
            return back()->with('error', 'The deadline for this assignment has passed.');
        }

        $data = $request->validate([
            'content'     => ['nullable', 'string', 'max:10000'],
            'file'        => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,png,jpg,jpeg,txt,zip'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store(
                'submissions/' . $student->id,
                'private'
            );
        }

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'content'      => $data['content'] ?? null,
                'file_path'    => $filePath,
                'submitted_at' => now(),
                'status'       => 'submitted',
            ]
        );

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'Assignment submitted successfully.');
    }
}
