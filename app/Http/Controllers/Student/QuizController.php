<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Quiz, QuizAttempt};
use App\Services\GamificationService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private readonly GamificationService $gamification) {}

    public function show(Quiz $quiz)
    {
        abort_if(!$quiz->is_published, 404);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->count();

        abort_if($attempts >= $quiz->max_attempts, 403, 'Maximum attempts reached.');

        return view('student.quizzes.show', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_if(!$quiz->is_published, 404);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->count();
        abort_if($attempts >= $quiz->max_attempts, 403);

        $request->validate(['answers' => ['required', 'array']]);

        $questions = $quiz->questions;
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
