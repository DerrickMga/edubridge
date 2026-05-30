<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Course, Quiz, QuizAttempt, QuizQuestion};
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function create(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $lessons = $course->lessons()->get();
        return view('teacher.quizzes.create', compact('course', 'lessons'));
    }

    public function store(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);

        $data = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'lesson_id'           => ['nullable', 'integer', 'exists:lessons,id'],
            'time_limit_minutes'  => ['nullable', 'integer', 'min:1', 'max:180'],
            'pass_percentage'     => ['required', 'integer', 'min:1', 'max:100'],
            'max_attempts'        => ['required', 'integer', 'min:1', 'max:10'],
            'show_answers_after'  => ['boolean'],
            'is_published'        => ['boolean'],
            'questions'           => ['required', 'array', 'min:1'],
            'questions.*.question'       => ['required', 'string'],
            'questions.*.type'           => ['required', 'in:mcq,true_false,short_answer'],
            'questions.*.correct_answer' => ['required', 'string'],
            'questions.*.options'        => ['nullable', 'array'],
            'questions.*.points'         => ['nullable', 'integer', 'min:1'],
        ]);

        $quiz = Quiz::create([
            'course_id'          => $course->id,
            'lesson_id'          => $data['lesson_id'] ?? null,
            'teacher_id'         => auth()->id(),
            'title'              => $data['title'],
            'description'        => $data['description'] ?? null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'pass_percentage'    => $data['pass_percentage'],
            'max_attempts'       => $data['max_attempts'],
            'show_answers_after' => $data['show_answers_after'] ?? true,
            'is_published'       => $data['is_published'] ?? false,
        ]);

        foreach ($data['questions'] as $i => $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz->id,
                'type'           => $q['type'],
                'question'       => $q['question'],
                'options'        => $q['options'] ?? null,
                'correct_answer' => $q['correct_answer'],
                'explanation'    => $q['explanation'] ?? null,
                'points'         => $q['points'] ?? 1,
                'sort_order'     => $i,
            ]);
        }

        return redirect()->route('teacher.courses.show', $course)
            ->with('success', 'Quiz created with '.count($data['questions']).' questions.');
    }

    public function index(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $quizzes = Quiz::where('course_id', $course->id)
            ->withCount(['attempts', 'attempts as passed_count' => fn ($q) => $q->where('passed', true)])
            ->with('questions')
            ->latest()
            ->get();
        return view('teacher.quizzes.index', compact('course', 'quizzes'));
    }

    public function show(Course $course, Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403);
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with('student')
            ->latest('completed_at')
            ->get();
        $stats = [
            'total'      => $attempts->count(),
            'passed'     => $attempts->where('passed', true)->count(),
            'avg_pct'    => $attempts->count()
                ? (int) round($attempts->avg(fn ($a) => $a->score_percentage))
                : 0,
        ];
        return view('teacher.quizzes.show', compact('course', 'quiz', 'attempts', 'stats'));
    }

    public function togglePublish(Course $course, Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403);
        $quiz->update(['is_published' => !$quiz->is_published]);
        return back()->with('success', $quiz->is_published ? 'Quiz published.' : 'Quiz unpublished.');
    }

}
