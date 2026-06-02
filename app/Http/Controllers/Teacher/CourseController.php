<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /** Teacher's courses (via pivot) */
    public function index(Request $request)
    {
        $courses = $request->user()
            ->taughtCourses()
            ->withCount(['lessons', 'enrollments'])
            ->orderBy('subject')
            ->orderBy('title')
            ->paginate(20);

        return view('teacher.courses.index', compact('courses'));
    }

    /** Show the create-course form */
    public function create()
    {
        return view('teacher.courses.create');
    }

    /** Save a new course created by the teacher */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'subject'     => [
                'required', 'string', 'max:100',
                Rule::in([
                    'Mathematics','Further Mathematics','English Language','English Literature',
                    'Physics','Chemistry','Biology','Combined Science',
                    'History','Geography',
                    'Business Studies','Commerce','Accounting','Economics',
                    'Computer Science','Agriculture',
                    'Shona','Ndebele','French','Art','Music','Physical Education',
                ]),
            ],
            'grade_level' => [
                'required', 'string', 'max:100',
                Rule::in(['Form 1','Form 2','Form 3','Form 4 (O-Level)','Form 5 (O-Level)','Lower 6 (A-Level)','Upper 6 (A-Level)']),
            ],
            'description' => 'nullable|string|max:3000',
            'status'      => 'required|in:draft,published',
        ]);

        // Prevent duplicate subject + grade_level combination
        $exists = Course::where('subject', $data['subject'])
            ->where('grade_level', $data['grade_level'])
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'subject' => "A course for {$data['subject']} — {$data['grade_level']} already exists. Use Browse Catalogue to claim it instead.",
            ]);
        }

        $data['teacher_id'] = $request->user()->id;

        $course = Course::create($data);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Course created. Start adding lessons!');
    }

    /** Browse all available platform courses to join */
    public function browse(Request $request)
    {
        $user = $request->user();
        $myIds = $user->taughtCourses()->pluck('courses.id');

        $courses = Course::whereNotIn('status', ['archived'])
            ->withCount(['lessons', 'enrollments', 'teachers'])
            ->orderBy('grade_level')
            ->orderBy('title')
            ->get()
            ->each(fn($c) => $c->isMine = $myIds->contains($c->id));

        return view('teacher.courses.browse', compact('courses'));
    }

    /** Join a course as a co-teacher */
    public function join(Request $request, Course $course)
    {
        $user = $request->user();

        if ($user->taughtCourses()->where('courses.id', $course->id)->exists()) {
            return back()->with('info', 'You are already teaching this course.');
        }

        $user->taughtCourses()->attach($course->id, ['role' => 'co_teacher']);

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', "You have joined {$course->title}. Set your preferred topics below.");
    }

    /** Leave a course (remove from pivot) */
    public function leave(Request $request, Course $course)
    {
        $request->user()->taughtCourses()->detach($course->id);

        return redirect()->route('teacher.courses.index')
            ->with('success', "You have left {$course->title}.");
    }

    /** Update preferred lesson topics for this teacher on this course */
    public function updateTopics(Request $request, Course $course)
    {
        $data = $request->validate([
            'preferred_topics' => 'nullable|array',
            'preferred_topics.*' => 'integer|exists:lessons,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $request->user()->taughtCourses()->updateExistingPivot($course->id, [
            'preferred_topics' => isset($data['preferred_topics']) ? json_encode($data['preferred_topics']) : null,
            'notes'            => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Your topic preferences saved.');
    }

    public function show(Request $request, Course $course)
    {
        // Teachers must be enrolled in the course via pivot OR be admin
        if (!$request->user()->isAdmin() && !$request->user()->taughtCourses()->where('courses.id', $course->id)->exists()) {
            abort(403, 'You are not teaching this course.');
        }
        $course->load(['lessons', 'liveSessions', 'announcements', 'teachers']);

        // This teacher's pivot data (preferred topics, notes)
        $myPivot = $request->user()->taughtCourses()
            ->where('courses.id', $course->id)
            ->first()?->pivot;

        $myTopicIds = $myPivot ? json_decode($myPivot->preferred_topics ?? '[]', true) : [];

        return view('teacher.courses.show', compact('course', 'myPivot', 'myTopicIds'));
    }

    /** Only description and status are teacher-editable */
    public function edit(Request $request, Course $course)
    {
        if (!$request->user()->isAdmin() && !$request->user()->taughtCourses()->where('courses.id', $course->id)->exists()) {
            abort(403);
        }
        return view('teacher.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        if (!$request->user()->isAdmin() && !$request->user()->taughtCourses()->where('courses.id', $course->id)->exists()) {
            abort(403);
        }

        $course->update($request->validate([
            'description' => 'nullable|string|max:3000',
            'status'      => 'required|in:draft,published,archived',
        ]));

        return back()->with('success', 'Course updated.');
    }
}
