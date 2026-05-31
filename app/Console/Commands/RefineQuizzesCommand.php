<?php
namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\GptService;
use App\Services\CurriculumContextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * curriculum:refine-quizzes
 * ──────────────────────────
 * Uses Claude + ZIMSEC syllabus PDFs to regenerate high-quality MCQ
 * quiz questions for each course, replacing the generic seeded ones.
 *
 * Usage:
 *   php artisan curriculum:refine-quizzes
 *   php artisan curriculum:refine-quizzes --subject="Mathematics"
 *   php artisan curriculum:refine-quizzes --subject="Biology" --tier=a_level
 *   php artisan curriculum:refine-quizzes --dry-run
 *   php artisan curriculum:refine-quizzes --questions=20
 */
class RefineQuizzesCommand extends Command
{
    protected $signature = 'curriculum:refine-quizzes
                            {--subject= : Limit to a specific subject name}
                            {--tier=    : o_level or a_level (default: both)}
                            {--questions=15 : Number of MCQs to generate per quiz}
                            {--dry-run  : Show what would be done without saving}';

    protected $description = 'Regenerate quiz questions using Claude AI + ZIMSEC syllabus PDFs';

    public function __construct(
        private GptService               $gpt,
        private CurriculumContextService $curriculum,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $subjectFilter = $this->option('subject');
        $tierFilter    = $this->option('tier');
        $numQuestions  = (int) $this->option('questions');
        $dryRun        = $this->option('dry-run');

        $this->info($dryRun ? '🔍 DRY RUN — no changes will be saved.' : '🧠 Refining quiz questions with Claude AI...');
        $this->newLine();

        $query = Course::published()->with(['quizzes.questions']);

        if ($subjectFilter) {
            $query->where('subject', $subjectFilter);
        }
        if ($tierFilter) {
            $gradeLabel = $tierFilter === 'a_level' ? 'A-Level' : 'O-Level';
            $query->where('grade_level', 'like', "%{$gradeLabel}%");
        }

        $courses = $query->get();

        if ($courses->isEmpty()) {
            $this->warn('No courses found matching the given filters.');
            return self::SUCCESS;
        }

        $this->info("Found {$courses->count()} course(s) to process.");
        $bar = $this->output->createProgressBar($courses->count());
        $bar->start();

        $updated = 0;
        $failed  = 0;

        foreach ($courses as $course) {
            $tier = str_contains($course->grade_level ?? '', 'A-Level') ? 'a_level' : 'o_level';

            // Get ZIMSEC syllabus context
            $syllabusContext = $this->curriculum->getSyllabusText($course->subject, $tier);

            // Get the first quiz for this course (or skip if no quiz)
            $quiz = $course->quizzes->first();
            if (! $quiz) {
                $bar->advance();
                continue;
            }

            try {
                $questions = $this->generateQuestions(
                    subject:  $course->subject,
                    level:    $course->grade_level ?? 'O-Level',
                    syllabus: $syllabusContext,
                    count:    $numQuestions,
                );

                if (empty($questions)) {
                    $bar->advance();
                    $failed++;
                    continue;
                }

                if (! $dryRun) {
                    $this->saveQuestions($quiz, $questions);
                }

                $updated++;
            } catch (\Throwable $e) {
                Log::warning("RefineQuizzes: failed for course {$course->id} ({$course->subject}): " . $e->getMessage());
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Updated: {$updated} | ❌ Failed: {$failed}");

        if ($dryRun) {
            $this->warn('Dry run complete — nothing was saved.');
        }

        return self::SUCCESS;
    }

    private function generateQuestions(string $subject, string $level, string $syllabus, int $count): array
    {
        $syllabusSection = $syllabus
            ? "Use the following ZIMSEC syllabus excerpt as your source of truth:\n\n{$syllabus}\n\n"
            : "Use your knowledge of the ZIMSEC {$level} {$subject} syllabus.\n\n";

        $prompt = <<<PROMPT
You are a ZIMSEC exam paper writer. Generate exactly {$count} multiple-choice questions (MCQs)
for ZIMSEC {$level} {$subject}.

{$syllabusSection}Requirements:
- Each question must test a specific concept or skill from the syllabus
- Cover a variety of topics (do not repeat the same topic)
- 4 answer options (A, B, C, D) — only one is correct
- Include a brief explanation for the correct answer
- Difficulty: mix of easy (30%), medium (50%), and hard (20%)
- Language: clear, unambiguous, suitable for exam conditions

Respond with ONLY valid JSON — no markdown, no explanation outside the JSON.
Format:
[
  {
    "question": "...",
    "options": ["A. ...", "B. ...", "C. ...", "D. ..."],
    "correct_answer": "A",
    "explanation": "...",
    "topic": "..."
  },
  ...
]
PROMPT;

        $response = $this->gpt->chat(
            [['role' => 'user', 'content' => $prompt]],
            'You are an expert ZIMSEC curriculum specialist and exam writer. Always respond with valid JSON only.'
        );

        return $this->parseJsonQuestions($response);
    }

    private function parseJsonQuestions(string $response): array
    {
        // Strip any markdown code fences if present
        $json = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $json = preg_replace('/\s*```$/m', '', $json);
        $json = trim($json);

        $data = json_decode($json, true);

        if (! is_array($data)) {
            // Try to extract JSON array from response
            if (preg_match('/\[[\s\S]+\]/m', $json, $match)) {
                $data = json_decode($match[0], true);
            }
        }

        if (! is_array($data)) {
            Log::warning('RefineQuizzes: failed to parse Claude response as JSON', [
                'response' => substr($response, 0, 500),
            ]);
            return [];
        }

        return array_filter($data, fn($q) =>
            isset($q['question'], $q['options'], $q['correct_answer']) &&
            is_array($q['options']) &&
            count($q['options']) === 4
        );
    }

    private function saveQuestions(Quiz $quiz, array $questions): void
    {
        DB::transaction(function () use ($quiz, $questions) {
            // Remove old questions
            $quiz->questions()->delete();

            // Insert new ones
            foreach (array_values($questions) as $i => $q) {
                QuizQuestion::create([
                    'quiz_id'        => $quiz->id,
                    'type'           => 'mcq',
                    'question'       => $q['question'],
                    'options'        => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'explanation'    => $q['explanation'] ?? null,
                    'points'         => 1,
                    'sort_order'     => $i + 1,
                ]);
            }
        });
    }
}
