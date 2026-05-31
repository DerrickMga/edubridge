<?php
namespace App\Services;

use App\Models\Conversation;
use App\Models\LearningMemory;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

/**
 * CompanionService — dual-model AI study companion.
 *
 * Routes between Chiedza (GPT/persona) and GPT-4o based on:
 *  - user preference stored in conversation metadata
 *  - question type (creative/explanatory → Chiedza, structured/maths → GPT)
 *  - fallback if primary model errors
 *
 * Adaptive learning: builds a per-student memory of topics, misconceptions,
 * and learning style from conversation history, injected as context.
 */
class CompanionService
{
    public function __construct(
        private ClaudeService            $chiedza,    // Chiedza persona (GPT-4o)
        private GptService               $gpt,        // OpenAI GPT-4o
        private CurriculumContextService $curriculum, // ZIMSEC syllabus context
    ) {}

    const MODEL_CHIEDZA = 'chiedza'; // Chiedza persona (GPT-4o)
    const MODEL_GPT     = 'gpt';     // OpenAI GPT-4o
    const MODEL_AUTO    = 'auto';

    /**
     * Send a message and get a reply, updating learning memory.
     *
     * @param  Conversation $conversation
     * @param  string       $userMessage
     * @param  string       $model  'chiedza'|'gpt'|'auto'
     * @return array{content: string, model_used: string, tokens_hint: int}
     */
    public function respond(Conversation $conversation, string $userMessage, string $model = self::MODEL_AUTO): array
    {
        // 1. Build conversation history (last 20 messages for context window)
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->take(20)
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // 2. Build adaptive system prompt from learning memory
        $system = $this->buildSystemPrompt($conversation);

        // 3. Pick model
        $modelUsed = $this->pickModel($model, $userMessage);

        // 4. Call AI with fallback
        $reply = $this->callWithFallback($modelUsed, $history, $system);

        // 5. Update learning memory asynchronously (non-fatal)
        try {
            $this->updateLearningMemory($conversation, $userMessage, $reply);
        } catch (\Throwable $e) {
            Log::warning('CompanionService: learning memory update failed: ' . $e->getMessage());
        }

        return [
            'content'     => $reply,
            'model_used'  => $modelUsed,
            'tokens_hint' => strlen($reply) / 4, // rough token estimate
        ];
    }

    /**
     * Generate a personalised study plan for a student.
     */
    public function generateStudyPlan(Conversation $conversation, string $subject, string $level, int $weeks, array $topics): array
    {
        $memory = $this->getMemorySummary($conversation);
        $extra  = $memory ? " The student's learning profile: {$memory}" : '';

        // Study plans are best from GPT (structured output)
        return $this->gpt->generateStudyPlan($subject . $extra, $level, $weeks, $topics);
    }

    /**
     * Generate an advanced, highly-detailed study plan with worked examples,
     * practice questions, textbook references, and YouTube search queries.
     */
    public function generateAdvancedStudyPlan(
        string $subject,
        string $level,
        int    $weeks,
        array  $topics,
        string $examBoard  = 'ZIMSEC',
        string $memoryHint = '',
    ): array {
        return $this->gpt->generateAdvancedStudyPlan($subject, $level, $weeks, $topics, $examBoard, $memoryHint);
    }

    /**
     * Summarise a lesson — usable by teachers or students.
     */
    public function summariseLesson(string $content, string $subject = ''): array
    {
        return $this->gpt->summariseLesson($content, $subject);
    }

    /**
     * Generate concise study notes from a topic or pasted content.
     */
    public function generateNotes(string $topic, string $level = '', string $subject = ''): string
    {
        $levelHint = $level ? " at {$level} level" : '';
        $subjHint  = $subject ? " for {$subject}" : '';
        $system = 'You are an expert tutor for Zimbabwean ZIMSEC students. '
                . 'Create clear, well-structured study notes with headings and bullet points.';

        $msg = "Create study notes{$subjHint}{$levelHint} on: {$topic}";

        try {
            return $this->gpt->chat([['role' => 'user', 'content' => $msg]], $system);
        } catch (\Throwable) {
            return $this->chiedza->chat([['role' => 'user', 'content' => $msg]], $system);
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function pickModel(string $preference, string $message): string
    {
        if ($preference === self::MODEL_CHIEDZA) return self::MODEL_CHIEDZA;
        if ($preference === self::MODEL_GPT)     return self::MODEL_GPT;

        // Auto-routing heuristics
        $lower = strtolower($message);
        $mathKeywords = ['calculate', 'solve', 'equation', 'formula', 'proof', 'derive',
                         'integrate', 'differentiate', 'graph', 'algebra', 'statistics'];
        foreach ($mathKeywords as $kw) {
            if (str_contains($lower, $kw)) return self::MODEL_GPT;
        }

        // Default to Chiedza for explanatory/contextual questions
        return self::MODEL_CHIEDZA;
    }

    private function callWithFallback(string &$modelUsed, array $history, string $system): string
    {
        $call = function(string $model) use ($history, $system): string {
            return match($model) {
                self::MODEL_GPT => $this->gpt->chat($history, $system),
                default         => $this->chiedza->chat($history, $system), // chiedza
            };
        };

        $fallbacks = [self::MODEL_GPT, self::MODEL_CHIEDZA];
        // Remove primary model so fallback doesn't repeat it
        $fallbacks = array_values(array_filter($fallbacks, fn($m) => $m !== $modelUsed));

        try {
            return $call($modelUsed);
        } catch (\Throwable $e) {
            Log::warning("CompanionService: primary model ({$modelUsed}) failed, trying fallback. " . $e->getMessage());
            foreach ($fallbacks as $fb) {
                try {
                    $modelUsed = $fb;
                    return $call($fb);
                } catch (\Throwable) {}
            }
            Log::error('CompanionService: all models failed.');
            return "I'm sorry, I'm having trouble responding right now. Please try again in a moment.";
        }
    }

    private function buildSystemPrompt(Conversation $conversation): string
    {
        $base = <<<SYS
You are an expert AI study companion for EduBridge — Zimbabwe's leading online learning platform.
You help students (primarily preparing for ZIMSEC O-Level and A-Level exams) understand concepts,
practise problems, build study plans, and stay motivated.
You are warm, patient, encouraging, and always accurate.
When you don't know something, say so clearly and suggest where the student can find the answer.
Keep responses concise and well-structured with bullet points or numbered steps where helpful.
SYS;

        // Inject ZIMSEC syllabus context if conversation has a subject
        try {
            $meta    = is_array($conversation->metadata) ? $conversation->metadata : [];
            $subject = $meta['subject'] ?? null;
            $tier    = $meta['tier'] ?? 'o_level';
            if ($subject) {
                $syllabusCtx = $this->curriculum->buildSystemContext($subject, $tier);
                if ($syllabusCtx) {
                    $base .= $syllabusCtx;
                }
            }
        } catch (\Throwable) {
            // Non-fatal — proceed without syllabus context
        }

        $memory = $this->getMemorySummary($conversation);
        if ($memory) {
            $base .= "\n\n## Student Learning Profile\n{$memory}";
        }

        return trim($base);
    }

    private function getMemorySummary(Conversation $conversation): string
    {
        try {
            $mem = LearningMemory::where('conversation_id', $conversation->id)->first();
            return $mem?->summary ?? '';
        } catch (\Throwable) {
            return '';
        }
    }

    private function updateLearningMemory(Conversation $conversation, string $userMsg, string $reply): void
    {
        // Every 5 messages, regenerate the learning memory summary
        $count = $conversation->messages()->count();
        if ($count % 5 !== 0) return;

        $recentMessages = $conversation->messages()
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => "{$m->role}: {$m->content}")
            ->implode("\n");

        $prompt = "Based on this conversation snippet, write a brief (3-5 sentence) student learning profile. "
                . "Note: their apparent level, subjects of interest, recurring questions, any misconceptions, "
                . "and preferred explanation style. Be factual and concise.\n\n{$recentMessages}";

        try {
            $summary = $this->gpt->chat([['role' => 'user', 'content' => $prompt]]);

            LearningMemory::updateOrCreate(
                ['conversation_id' => $conversation->id],
                [
                    'user_id'    => $conversation->user_id,
                    'summary'    => $summary,
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('CompanionService: could not update learning memory: ' . $e->getMessage());
        }
    }
}
