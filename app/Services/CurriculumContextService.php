<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * CurriculumContextService
 * ─────────────────────────
 * Extracts text from ZIMSEC syllabus PDFs stored in
 * resources/curriculum/zimsec/syllabuses/ and returns it
 * as a context snippet for injection into AI system prompts.
 *
 * Results are cached for 24 hours (file cache) to avoid
 * re-parsing PDFs on every request.
 */
class CurriculumContextService
{
    /** Max characters of syllabus text to include in AI context */
    const MAX_CHARS = 6000;

    /** Map subject name (lowercase) → PDF filename (without extension) */
    private const SYLLABUS_MAP = [
        'mathematics'                  => ['Mathematics', 'Additional-Mathematics-O-level'],
        'further mathematics'          => ['Additional-Mathematics'],
        'english language'             => [],
        'english literature'           => [],
        'biology'                      => ['Biology'],
        'chemistry'                    => ['Chemistry', 'Chemistry-Forms-3-4'],
        'physics'                      => [],
        'combined science'             => ['COMBINED-SCIENCE1'],
        'geography'                    => [],
        'history'                      => [],
        'shona'                        => [],
        'ndebele'                      => [],
        'business studies'             => ['Business-Studies'],
        'commerce'                     => ['Commerce-Form-1-4'],
        'accounting'                   => ['Accounting'],
        'computer science'             => ['Computer-Science', 'Computer-Science-O-level-Syllabus'],
        'agriculture'                  => ['Agriculture', 'Agriculture-Forms-1-4'],
        'religious & moral education'  => [],
        'food & nutrition'             => [],
        'textile technology & design'  => [],
        'economics'                    => [],
    ];

    private string $syllabusDir;

    public function __construct()
    {
        $this->syllabusDir = base_path('resources/curriculum/zimsec/syllabuses');
    }

    /**
     * Get syllabus context text for a subject (and optionally level).
     * Returns empty string if no syllabus is available.
     */
    public function getSyllabusText(string $subject, string $tier = 'o_level'): string
    {
        $key = 'syllabus:' . Str::slug($subject) . ':' . $tier;

        return Cache::remember($key, 86400, function () use ($subject, $tier) {
            $pdfs = $this->findSyllabusPdfs($subject, $tier);
            if (empty($pdfs)) return '';

            $text = '';
            foreach ($pdfs as $pdf) {
                $text .= $this->extractPdfText($pdf);
                if (strlen($text) >= self::MAX_CHARS) break;
            }

            return Str::limit(trim($text), self::MAX_CHARS, '...');
        });
    }

    /**
     * Build a formatted context block suitable for injection into
     * an AI system prompt.
     */
    public function buildSystemContext(string $subject, string $tier = 'o_level'): string
    {
        $text = $this->getSyllabusText($subject, $tier);
        if (empty($text)) return '';

        $level = $tier === 'a_level' ? 'A-Level' : 'O-Level';
        return <<<CTX

## ZIMSEC Syllabus Context — {$subject} ({$level})
The following is an extract from the official ZIMSEC syllabus. Use it to ensure
accuracy in your responses about topics, assessment objectives, and content:

{$text}
CTX;
    }

    /**
     * Return all subjects that have a syllabus PDF available.
     */
    public function availableSubjects(): array
    {
        $available = [];
        foreach (self::SYLLABUS_MAP as $subject => $files) {
            foreach ($files as $file) {
                if (file_exists($this->syllabusDir . '/' . $file . '.pdf')) {
                    $available[] = $subject;
                    break;
                }
            }
        }
        return array_unique($available);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function findSyllabusPdfs(string $subject, string $tier): array
    {
        $normalized = strtolower(trim($subject));
        $files      = self::SYLLABUS_MAP[$normalized] ?? [];

        // Also try direct match: subject name → filename
        if (empty($files)) {
            $direct = str_replace([' ', '&'], ['-', 'and'], ucwords($subject));
            $files  = [$direct];
        }

        $paths = [];
        foreach ($files as $file) {
            $path = $this->syllabusDir . '/' . $file . '.pdf';
            if (file_exists($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function extractPdfText(string $path): string
    {
        try {
            $parser   = new \Smalot\PdfParser\Parser();
            $pdf      = $parser->parseFile($path);
            $text     = $pdf->getText();

            // Clean up whitespace
            $text = preg_replace('/\s{3,}/', "\n", $text);
            $text = preg_replace('/\n{3,}/', "\n\n", trim($text));

            return $text;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                "CurriculumContextService: failed to parse PDF {$path}: " . $e->getMessage()
            );
            return '';
        }
    }
}
