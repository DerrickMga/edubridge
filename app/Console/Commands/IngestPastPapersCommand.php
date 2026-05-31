<?php
namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * curriculum:ingest-papers
 * ─────────────────────────
 * Extracts ZIMSEC past-paper ZIP files from
 * resources/curriculum/zimsec/past-papers/ and creates
 * Resource records linked to the matching courses.
 *
 * Usage:
 *   php artisan curriculum:ingest-papers
 *   php artisan curriculum:ingest-papers --force   # re-process already-ingested ZIPs
 *   php artisan curriculum:ingest-papers --tier=a_level
 */
class IngestPastPapersCommand extends Command
{
    protected $signature = 'curriculum:ingest-papers
                            {--tier=both  : a_level, o_level, or both}
                            {--force      : Re-ingest ZIPs even if already processed}';

    protected $description = 'Extract ZIMSEC past-paper ZIPs and attach them as course resources';

    /**
     * ZIMSEC / Cambridge A-level paper code → subject name.
     * Extend as more codes are confirmed.
     */
    private const PAPER_CODE_MAP = [
        '6001' => 'Mathematics',
        '6002' => 'Further Mathematics',
        '6003' => 'Physics',
        '6004' => 'Chemistry',
        '6005' => 'Biology',
        '6006' => 'English Literature',
        '6007' => 'Geography',
        '6008' => 'History',
        '6021' => 'Accounting',
        '6022' => 'Economics',
        '6023' => 'Business Studies',
        '6024' => 'Geography',
        '6025' => 'History',
        '6026' => 'Computer Science',
        '6027' => 'Mathematics',
        '6028' => 'Physics',
        '6029' => 'Chemistry',
        '6030' => 'Biology',
        '6031' => 'Agriculture',
        '6032' => 'Further Mathematics',
        '6033' => 'English Language',
        '6034' => 'Shona',
        '6036' => 'Ndebele',
        '6037' => 'Commerce',
        '6038' => 'Business Studies',
        '6039' => 'English Literature',
        '6040' => 'Mathematics',
        '6042' => 'Physics',
        '6043' => 'Chemistry',
        '6044' => 'Biology',
        '6046' => 'Geography',
        '6047' => 'Economics',
        '6048' => 'Accounting',
        '6049' => 'Computer Science',
        '6053' => 'Agriculture',
        '6055' => 'History',
        '6056' => 'Business Studies',
        '6069' => 'Religious & Moral Education',
        '6070' => 'Further Mathematics',
        '6073' => 'Mathematics',
    ];

    public function handle(): int
    {
        $tier  = $this->option('tier');
        $force = $this->option('force');

        $tiers = $tier === 'both' ? ['a_level', 'o_level'] : [$tier];

        $curriculum = User::where('email', 'curriculum@edubridge.co.zw')->first();
        if (! $curriculum) {
            $this->error('Curriculum teacher account not found. Run CurriculumSeeder first.');
            return self::FAILURE;
        }

        $total = 0;
        foreach ($tiers as $t) {
            $dir = base_path("resources/curriculum/zimsec/past-papers/" . str_replace('_', '-', $t));
            if (! is_dir($dir)) {
                $this->warn("Directory not found: {$dir} — skipping.");
                continue;
            }

            $files = glob($dir . '/*.zip') + glob($dir . '/*.pdf');

            if (empty($files)) {
                $this->info("No files found in {$t}/");
                continue;
            }

            $this->info("Processing " . count($files) . " file(s) in {$t}/...");
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $filePath) {
                $total += $this->processFile($filePath, $t, $curriculum, $force);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("✅ Done. {$total} resource record(s) created/updated.");

        return self::SUCCESS;
    }

    private function processFile(string $filePath, string $tier, User $uploader, bool $force): int
    {
        $filename = basename($filePath);
        $code     = $this->extractCode($filename);
        $subject  = self::PAPER_CODE_MAP[$code] ?? null;

        // Find the matching course
        $levelLabel = $tier === 'a_level' ? 'A-Level' : 'O-Level';
        $course     = null;

        if ($subject) {
            $course = Course::where('subject', $subject)
                ->where('grade_level', 'like', "%{$levelLabel}%")
                ->first();
        }

        // Fall back to first course if subject unknown
        if (! $course) {
            Log::info("IngestPastPapers: no course match for {$filename} (code={$code})");
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'zip') {
            return $this->processZip($filePath, $course, $uploader, $tier, $code, $subject, $force);
        }

        if ($ext === 'pdf') {
            return $this->storePdfResource($filePath, $course, $uploader, $tier, $code, $subject, $force) ? 1 : 0;
        }

        return 0;
    }

    private function processZip(string $zipPath, ?Course $course, User $uploader, string $tier, string $code, ?string $subject, bool $force): int
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            Log::warning("IngestPastPapers: could not open ZIP {$zipPath}");
            return 0;
        }

        $count   = 0;
        $tempDir = sys_get_temp_dir() . '/edubridge_papers_' . Str::random(8);
        @mkdir($tempDir, 0777, true);

        try {
            $zip->extractTo($tempDir);
            $zip->close();

            // Find all PDFs in extracted content
            $pdfs = $this->findPdfsRecursively($tempDir);

            foreach ($pdfs as $pdfPath) {
                if ($this->storePdfResource($pdfPath, $course, $uploader, $tier, $code, $subject, $force)) {
                    $count++;
                }
            }
        } finally {
            $this->deleteDirectory($tempDir);
        }

        return $count;
    }

    private function storePdfResource(string $pdfPath, ?Course $course, User $uploader, string $tier, string $code, ?string $subject, bool $force): bool
    {
        $pdfName    = basename($pdfPath);
        $levelLabel = $tier === 'a_level' ? 'A-Level' : 'O-Level';
        $subjectStr = $subject ?? "Paper {$code}";

        // Storage destination
        $storagePath = "curriculum/past-papers/{$tier}/{$code}/{$pdfName}";

        // Skip if already stored and not forced
        if (! $force && Storage::disk('public')->exists($storagePath)) {
            // Ensure Resource record exists
            if ($course && Resource::where('storage_path', $storagePath)->where('course_id', $course->id)->exists()) {
                return false;
            }
        }

        // Copy to public storage
        try {
            Storage::disk('public')->put(
                $storagePath,
                file_get_contents($pdfPath)
            );
        } catch (\Throwable $e) {
            Log::warning("IngestPastPapers: storage failed for {$pdfPath}: " . $e->getMessage());
            return false;
        }

        // Skip if we cannot match a course — course_id is required
        if (! $course) {
            Log::info("IngestPastPapers: skipping {$pdfName} — no course match for code {$code}");
            return false;
        }

        $title       = $this->buildResourceTitle($pdfName, $subjectStr, $levelLabel);
        $size        = filesize($pdfPath) ?: 0;

        $attributes = [
            'course_id'       => $course?->id,
            'uploaded_by'     => $uploader->id,
            'title'           => $title,
            'description'     => "ZIMSEC {$levelLabel} {$subjectStr} past examination paper.",
            'type'            => 'document',
            'storage_path'    => $storagePath,
            'mime_type'       => 'application/pdf',
            'file_size_bytes' => $size,
            'is_downloadable' => true,
        ];

        Resource::updateOrCreate(
            ['storage_path' => $storagePath],
            $attributes
        );

        return true;
    }

    private function extractCode(string $filename): string
    {
        // e.g. "6001.zip" → "6001", "6039-EXAM-CIRCULAR.pdf" → "6039"
        if (preg_match('/^(\d{4})/', $filename, $m)) {
            return $m[1];
        }
        return pathinfo($filename, PATHINFO_FILENAME);
    }

    private function buildResourceTitle(string $pdfName, string $subject, string $level): string
    {
        // Try to extract year from filename
        if (preg_match('/(19|20)\d{2}/', $pdfName, $m)) {
            return "ZIMSEC {$level} {$subject} — {$m[0]}";
        }
        // Use filename as title hint
        $clean = str_replace(['_', '-'], ' ', pathinfo($pdfName, PATHINFO_FILENAME));
        $clean = preg_replace('/\s+/', ' ', $clean);
        return "ZIMSEC {$level} {$subject} — {$clean}";
    }

    private function findPdfsRecursively(string $dir): array
    {
        $pdfs = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iter as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'pdf') {
                $pdfs[] = $file->getPathname();
            }
        }
        return $pdfs;
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
