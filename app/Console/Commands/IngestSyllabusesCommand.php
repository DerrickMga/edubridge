<?php
namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * curriculum:ingest-syllabuses
 * ─────────────────────────────
 * Copies ZIMSEC syllabus PDFs from resources/curriculum/zimsec/syllabuses/
 * into public storage and creates Resource records linked to matching courses.
 *
 * Usage:
 *   php artisan curriculum:ingest-syllabuses
 *   php artisan curriculum:ingest-syllabuses --force
 */
class IngestSyllabusesCommand extends Command
{
    protected $signature = 'curriculum:ingest-syllabuses
                            {--force : Re-ingest even if already processed}';

    protected $description = 'Add ZIMSEC syllabus PDFs as course resources';

    /**
     * Maps filename → array of [subject, tier|null].
     * tier = 'a_level' | 'o_level' | null (both).
     */
    private const SYLLABUS_MAP = [
        'Accounting.pdf'                      => [['Accounting',        null]],
        'Additional-Mathematics.pdf'          => [['Further Mathematics','a_level']],
        'Additional-Mathematics-O-level.pdf'  => [['Mathematics',       'o_level']],
        'Agriculture.pdf'                     => [['Agriculture',       'a_level']],
        'Agriculture-Forms-1-4.pdf'           => [['Agriculture',       'o_level']],
        'Biology.pdf'                         => [['Biology',           null]],
        'Business-Enterprise.pdf'             => [['Business Studies',  'o_level']],
        'Business-Enterprise-and-Skills.pdf'  => [['Business Studies',  'o_level']],
        'Business-Studies.pdf'                => [['Business Studies',  null]],
        'Chemistry.pdf'                       => [['Chemistry',         null]],
        'Chemistry-Forms-3-4.pdf'             => [['Chemistry',         'o_level']],
        'COMBINED-SCIENCE1.pdf'               => [['Combined Science',  'o_level']],
        'Commerce-Form-1-4.pdf'               => [['Commerce',          'o_level']],
        'Commercial-Studies-Form-1-4.pdf'     => [['Commerce',          'o_level']],
        'Communication-Skills.pdf'            => [['English Language',  'o_level']],
        'Computer-Science.pdf'                => [['Computer Science',  null]],
        'Computer-Science-O-level-Syllabus.pdf' => [['Computer Science','o_level']],
        'CROP-SCIENCE-SYLLABUS.pdf'           => [['Agriculture',       null]],
    ];

    /** Files in the directory we intentionally skip (no matching course). */
    private const SKIP = [
        'Animal-Science-A-level.pdf',
        'Arts-Syllabus-Forms-1-4.pdf',
        'Art-syllabus.pdf',
        'Building-Technology.pdf',
        'Building-Technology-1.pdf',
        'Dance.pdf',
        'Dance-syllabus-Forms-1-4.pdf',
        'Design-Technology.pdf',
    ];

    public function handle(): int
    {
        $force = $this->option('force');

        $uploader = User::where('email', 'curriculum@edubridge.co.zw')->first();
        if (! $uploader) {
            $this->error('Curriculum teacher account not found. Run CurriculumSeeder first.');
            return self::FAILURE;
        }

        $syllabusDir = base_path('resources/curriculum/zimsec/syllabuses');
        if (! is_dir($syllabusDir)) {
            $this->error("Syllabuses directory not found: {$syllabusDir}");
            return self::FAILURE;
        }

        $files  = glob($syllabusDir . '/*.pdf');
        $total  = 0;
        $skipped = 0;

        $this->info('Processing ' . count($files) . ' syllabus PDF(s)...');
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $bar->advance();

            if (in_array($filename, self::SKIP, true)) {
                $skipped++;
                continue;
            }

            $mappings = self::SYLLABUS_MAP[$filename] ?? null;
            if (! $mappings) {
                Log::info("IngestSyllabuses: no course mapping for {$filename} — skipped.");
                $skipped++;
                continue;
            }

            foreach ($mappings as [$subject, $tier]) {
                $total += $this->ingestForCourses($filePath, $filename, $subject, $tier, $uploader, $force);
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Done. {$total} resource record(s) created/updated. {$skipped} file(s) skipped (no matching course).");

        return self::SUCCESS;
    }

    private function ingestForCourses(
        string $filePath,
        string $filename,
        string $subject,
        ?string $tier,
        User $uploader,
        bool $force
    ): int {
        $query = Course::where('subject', $subject);

        if ($tier === 'a_level') {
            $query->where('grade_level', 'like', '%A-Level%');
        } elseif ($tier === 'o_level') {
            $query->where('grade_level', 'like', '%O-Level%');
        }

        $courses = $query->get();

        if ($courses->isEmpty()) {
            Log::info("IngestSyllabuses: no courses found for subject='{$subject}' tier='{$tier}' (file={$filename})");
            return 0;
        }

        // Copy file to public storage once
        $storagePath = 'curriculum/syllabuses/' . $filename;
        if ($force || ! Storage::disk('public')->exists($storagePath)) {
            try {
                Storage::disk('public')->put($storagePath, file_get_contents($filePath));
            } catch (\Throwable $e) {
                Log::warning("IngestSyllabuses: storage failed for {$filename}: " . $e->getMessage());
                return 0;
            }
        }

        $fileSize = filesize($filePath) ?: 0;
        $count    = 0;

        foreach ($courses as $course) {
            $levelLabel = str_contains($course->grade_level ?? '', 'A-Level') ? 'A-Level' : 'O-Level';
            $title      = $this->buildTitle($filename, $subject, $levelLabel);

            if (! $force && Resource::where('storage_path', $storagePath)
                    ->where('course_id', $course->id)
                    ->exists()) {
                continue;
            }

            Resource::updateOrCreate(
                ['storage_path' => $storagePath, 'course_id' => $course->id],
                [
                    'lesson_id'       => null,
                    'uploaded_by'     => $uploader->id,
                    'title'           => $title,
                    'description'     => "Official ZIMSEC {$levelLabel} {$subject} syllabus document.",
                    'type'            => 'document',
                    'mime_type'       => 'application/pdf',
                    'file_size_bytes' => $fileSize,
                    'is_downloadable' => true,
                    'sort_order'      => 0,
                ]
            );

            $count++;
        }

        return $count;
    }

    private function buildTitle(string $filename, string $subject, string $level): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords(strtolower($name));

        // Use a clean title if it looks redundant
        $clean = "ZIMSEC {$level} {$subject} — Official Syllabus";

        // Flag supplementary resources clearly
        if (str_contains(strtolower($name), 'additional')) {
            $clean = "ZIMSEC {$level} Additional Mathematics Syllabus (supplementary)";
        } elseif (str_contains(strtolower($name), 'form')) {
            $clean = "ZIMSEC {$subject} Forms 1–4 Syllabus";
        } elseif (str_contains(strtolower($name), 'crop')) {
            $clean = "ZIMSEC Crop Science Syllabus (Agriculture reference)";
        }

        return $clean;
    }
}
