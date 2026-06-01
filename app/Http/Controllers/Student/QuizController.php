<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Course, Quiz, QuizAttempt};
use App\Services\GamificationService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private readonly GamificationService $gamification) {}

    /** List all published quizzes for an enrolled course */
    public function index(Request $request, Course $course)
    {
        $student = $request->user();
        $enrolled = $student->enrollments()->where('course_id', $course->id)->exists();
        abort_if(!$enrolled && !$student->isAdmin(), 403);

        $quizzes = $course->quizzes()
            ->where('is_published', true)
            ->with('lesson')
            ->get()
            ->map(function ($q) use ($student) {
                $q->my_attempts = QuizAttempt::where('quiz_id', $q->id)
                    ->where('student_id', $student->id)->get();
                return $q;
            });

        return view('student.quizzes.index', compact('course', 'quizzes'));
    }

    public function show(Quiz $quiz)
    {
        abort_if(!$quiz->is_published, 404);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->count();

        abort_if($attempts >= $quiz->max_attempts, 403, 'Maximum attempts reached.');

        // Question selection: bank + randomisation
        $questions = $quiz->questions()->orderBy('sort_order')->get();
        if ($quiz->randomize) {
            $questions = $questions->shuffle();
            if ($quiz->questions_per_attempt && $quiz->questions_per_attempt > 0) {
                $questions = $questions->take($quiz->questions_per_attempt);
            }
            // Persist the picked IDs for this attempt window so submit() scores the same set
            session()->put("quiz_set_{$quiz->id}", $questions->pluck('id')->all());
        }

        return view('student.quizzes.show', compact('quiz', 'questions'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_if(!$quiz->is_published, 404);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->count();
        abort_if($attempts >= $quiz->max_attempts, 403);

        $request->validate(['answers' => ['required', 'array']]);

        $allQuestions = $quiz->questions;
        if ($quiz->randomize && ($pickedIds = session()->pull("quiz_set_{$quiz->id}"))) {
            $questions = $allQuestions->whereIn('id', $pickedIds)->values();
        } else {
            $questions = $allQuestions;
        }
        $score     = 0;
        $maxScore  = $questions->sum('points');

        foreach ($questions as $q) {
            $answer = $request->answers[$q->id] ?? null;
            if ($answer && strtolower(trim($answer)) === strtolower(trim($q->correct_answer))) {
                $score += $q->points;
            }
        }

        $passed  = $maxScore > 0 && ($score / $maxScore) * 100 >= $quiz->pass_percentage;
        $attempt = QuizAttempt::create([
            'quiz_id'        => $quiz->id,
            'student_id'     => auth()->id(),
            'attempt_number' => $attempts + 1,
            'answers'        => $request->answers,
            'score'          => $score,
            'max_score'      => $maxScore,
            'passed'         => $passed,
            'started_at'     => now()->subMinutes(1),
            'completed_at'   => now(),
        ]);

        // Award gamification XP
        $this->gamification->onQuizAttempt(auth()->user(), $quiz, $attempt);

        return redirect()->route('student.quizzes.result', [$quiz, $attempt]);
    }

    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        abort_if($attempt->student_id !== auth()->id(), 403);
        return view('student.quizzes.result', compact('quiz', 'attempt'));
    }
}
