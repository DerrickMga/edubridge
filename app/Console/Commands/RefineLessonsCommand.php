<?php
namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use App\Services\GptService;
use App\Services\CurriculumContextService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * curriculum:refine-lessons
 * ──────────────────────────
 * Uses GPT-4o + ZIMSEC syllabus PDFs to regenerate a comprehensive,
 * syllabus-aligned lesson plan for every course, replacing the sparse
 * seed topics with properly titled and described lessons.
 *
 * Usage:
 *   php artisan curriculum:refine-lessons
 *   php artisan curriculum:refine-lessons --subject="Mathematics"
 *   php artisan curriculum:refine-lessons --subject="Biology" --tier=a_level
 *   php artisan curriculum:refine-lessons --lessons=20
 *   php artisan curriculum:refine-lessons --dry-run
 */
class RefineLessonsCommand extends Command
{
    protected $signature = 'curriculum:refine-lessons
                            {--subject=  : Limit to a specific subject name}
                            {--tier=     : o_level or a_level (default: both)}
                            {--lessons=20 : Target number of lessons per course}
                            {--dry-run   : Show what would be generated without saving}';

    protected $description = 'Regenerate ZIMSEC-aligned lesson plans using GPT-4o + syllabus PDFs';

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
        $targetLessons = max(10, (int) $this->option('lessons'));
        $dryRun        = $this->option('dry-run');

        $this->info($dryRun ? '🔍 DRY RUN — no changes will be saved.' : '📚 Refining lesson plans with GPT-4o + ZIMSEC syllabuses...');
        $this->newLine();

        $query = Course::published()->with('lessons');

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

            $syllabusContext = $this->curriculum->getSyllabusText($course->subject, $tier);

            try {
                $lessons = $this->generateLessons(
                    subject:  $course->subject,
                    level:    $course->grade_level ?? 'O-Level',
                    syllabus: $syllabusContext,
                    count:    $targetLessons,
                );

                if (empty($lessons)) {
                    $bar->advance();
                    $failed++;
                    continue;
                }

                if ($dryRun) {
                    $this->newLine();
                    $count = count($lessons);
                    $this->line("<fg=cyan>{$course->title}</> — {$count} lessons generated:");
                    foreach ($lessons as $l) {
                        $this->line("  <fg=yellow>{$l['order']}.</> {$l['title']} ({$l['duration_minutes']} min)");
                        $this->line("     <fg=gray>{$l['description']}</>");
                    }
                } else {
                    $this->saveLessons($course, $lessons);
                }

                $updated++;
            } catch (\Throwable $e) {
                Log::warning("RefineLessons: failed for course {$course->id} ({$course->subject}): " . $e->getMessage());
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

    // ─────────────────────────────────────────────────────────────────────────

    private function generateLessons(string $subject, string $level, string $syllabus, int $count): array
    {
        $syllabusSection = $syllabus
            ? "Use the following official ZIMSEC syllabus excerpt as your source of truth:\n\n{$syllabus}\n\n"
            : "Use your expert knowledge of the ZIMSEC {$level} {$subject} syllabus.\n\n";

        $prompt = <<<PROMPT
You are a ZIMSEC curriculum specialist. Generate a comprehensive, exam-aligned lesson plan for:

Subject: {$subject}
Level: ZIMSEC {$level}
Target: exactly {$count} lessons

{$syllabusSection}Requirements:
- Cover ALL major examinable ZIMSEC syllabus topics — leave nothing out
- Lessons must be ordered logically: foundational concepts first, then intermediate, then advanced
- Each lesson title must be specific and match real ZIMSEC syllabus topic names (not generic)
- Each description must be 2-3 sentences: what students will learn, key formulas/theorems/skills, and the exam focus
- Duration: 30–60 minutes per lesson based on topic complexity
- Do NOT include lessons about exam technique, revision, or "introduction to the course" — only content lessons
- Descriptions should mention specific ZIMSEC-relevant examples (Zimbabwean context where applicable)

Respond with ONLY valid JSON — no markdown, no explanation outside the JSON.
Format:
[
  {
    "title": "...",
    "description": "...",
    "duration_minutes": 45,
    "order": 1
  },
  ...
]
PROMPT;

        $response = $this->gpt->chat(
            [['role' => 'user', 'content' => $prompt]],
            'You are an expert ZIMSEC curriculum specialist. Always respond with valid JSON only. Never include markdown code fences or explanatory text outside the JSON array.'
        );

        return $this->parseJsonLessons($response);
    }

    private function parseJsonLessons(string $response): array
    {
        // Strip markdown fences
        $json = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $json = preg_replace('/\s*```$/m', '', $json);
        $json = trim($json);

        $data = json_decode($json, true);

        if (! is_array($data)) {
            if (preg_match('/\[[\s\S]+\]/m', $json, $match)) {
                $data = json_decode($match[0], true);
            }
        }

        if (! is_array($data)) {
            Log::warning('RefineLessons: failed to parse GPT response as JSON', [
                'response' => substr($response, 0, 500),
            ]);
            return [];
        }

        $valid = [];
        foreach (array_values($data) as $i => $l) {
            if (! isset($l['title'], $l['description'])) continue;
            $valid[] = [
                'title'            => trim($l['title']),
                'description'      => trim($l['description']),
                'duration_minutes' => max(30, min(90, (int) ($l['duration_minutes'] ?? 45))),
                'order'            => $i + 1,
            ];
        }

        return $valid;
    }

    private function saveLessons(Course $course, array $lessons): void
    {
        DB::transaction(function () use ($course, $lessons) {
            // Detach any resources from lesson-specific association before deleting
            // (moves them to course-level so they aren't orphaned)
            Resource::where('course_id', $course->id)
                ->whereNotNull('lesson_id')
                ->update(['lesson_id' => null]);

            // Delete existing lessons
            $course->lessons()->delete();

            // Insert refined lessons
            foreach ($lessons as $l) {
                Lesson::create([
                    'course_id'        => $course->id,
                    'title'            => $l['title'],
                    'description'      => $l['description'],
                    'order'            => $l['order'],
                    'status'           => 'published',
                    'duration_seconds' => $l['duration_minutes'] * 60,
                ]);
            }
        });
    }
}
