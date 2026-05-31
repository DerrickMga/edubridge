<?php

namespace App\Services;

use App\Models\LiveSession;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SessionAiReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SessionAiService
{
    private string $endpoint;
    private string $apiKey;
    private string $deployment;

    public function __construct()
    {
        $this->endpoint   = rtrim(config('services.azure_ai.endpoint', ''), '/');
        $this->apiKey     = config('services.azure_ai.key', '');
        $this->deployment = env('AZURE_AI_DEPLOYMENT', 'gpt-4o');
    }

    /**
     * Process a completed live session:
     * 1. Fetch Zoom transcript (if available)
     * 2. Call Azure AI to summarise, extract action items, generate quiz
     * 3. Auto-create a Quiz + QuizQuestions in the DB
     * 4. Persist the SessionAiReport
     *
     * Returns the SessionAiReport model.
     */
    public function process(LiveSession $session): SessionAiReport
    {
        $report = SessionAiReport::firstOrNew(['live_session_id' => $session->id]);

        try {
            // ── 1. Gather context ─────────────────────────────────────────
            $transcript    = null;
            $zoomParticipants = 0;

            if ($session->meeting_id && $session->provider === 'Zoom') {
                /** @var ZoomService $zoom */
                $zoom = app(ZoomService::class);
                $transcript       = $zoom->getTranscript($session->meeting_id);
                $participants     = $zoom->getMeetingParticipants($session->meeting_id);
                $zoomParticipants = count($participants);
            }

            // Fall back to tracking count if Zoom API didn't return participants
            $attendeesCount = $zoomParticipants > 0
                ? $zoomParticipants
                : $session->attendances()->count();

            // ── 2. Build AI prompt ────────────────────────────────────────
            $context = $this->buildContext($session, $transcript, $attendeesCount);
            $aiData  = $this->callAzureAi($context, $session);

            // ── 3. Auto-create Quiz ───────────────────────────────────────
            $quizId = null;
            if (! empty($aiData['quiz_questions'])) {
                $quizId = $this->createQuiz($session, $aiData['quiz_questions']);
            }

            // ── 4. Persist report ─────────────────────────────────────────
            $report->fill([
                'quiz_id'             => $quizId,
                'attendees_count'     => $attendeesCount,
                'summary'             => $aiData['summary'] ?? null,
                'action_items'        => $aiData['action_items'] ?? [],
                'transcript_excerpt'  => $transcript ? mb_substr($transcript, 0, 2000) : null,
                'error'               => null,
                'processed_at'        => now(),
            ])->save();

            // Update live_session attendees_count for finance reference
            $session->update(['attendees_count' => $attendeesCount]);

        } catch (\Throwable $e) {
            Log::error('SessionAiService: ' . $e->getMessage(), [
                'session_id' => $session->id,
            ]);
            $report->fill([
                'error'        => $e->getMessage(),
                'processed_at' => now(),
            ])->save();
        }

        return $report;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildContext(LiveSession $session, ?string $transcript, int $attendees): string
    {
        $course = $session->course?->title ?? 'Unknown course';
        $date   = $session->scheduled_at->format('D d M Y g:i a');

        $context = <<<TEXT
        Session: {$session->title}
        Course: {$course}
        Date: {$date}
        Duration: {$session->duration_minutes} minutes
        Attendees: {$attendees}
        TEXT;

        if ($transcript) {
            // Trim to ~6000 chars to stay within token budget
            $context .= "\n\nTranscript (excerpt):\n" . mb_substr(strip_tags($transcript), 0, 6000);
        }

        return $context;
    }

    private function callAzureAi(string $context, LiveSession $session): array
    {
        if (empty($this->endpoint) || empty($this->apiKey)) {
            // Return sensible stub when Azure AI is not yet configured
            return $this->stubResponse($session);
        }

        $url = "{$this->endpoint}/openai/deployments/{$this->deployment}/chat/completions?api-version=2024-08-01-preview";

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'messages' => [
                [
                    'role'    => 'system',
                    'content' => <<<SYSTEM
You are an EduBridge AI Session Observer. Given a lesson session context, produce a JSON object with these exact keys:
- "summary": 2-4 sentence paragraph summarising what was taught
- "action_items": array of 3-5 concise strings, each a clear follow-up task for students
- "quiz_questions": array of 5 objects, each with:
    "question" (string), "options" (array of 4 strings), "correct_answer" (the exact correct option string), "explanation" (string)
Respond ONLY with valid JSON, no markdown fences.
SYSTEM
                ],
                [
                    'role'    => 'user',
                    'content' => $context,
                ],
            ],
            'max_tokens'  => 1200,
            'temperature' => 0.4,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Azure AI call failed: ' . $response->body());
        }

        $text = $response->json('choices.0.message.content', '{}');

        // Strip markdown fences if model adds them despite instructions
        $text = preg_replace('/^```json\s*/i', '', trim($text));
        $text = preg_replace('/```\s*$/', '', $text);

        $data = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Azure AI returned invalid JSON: ' . $text);
        }

        return $data;
    }

    private function createQuiz(LiveSession $session, array $questions): int
    {
        $quiz = Quiz::create([
            'course_id'           => $session->course_id,
            'lesson_id'           => null,
            'teacher_id'          => $session->teacher_id,
            'title'               => 'AI Quiz — ' . $session->title,
            'description'         => 'Auto-generated by EduBridge AI after the live session on ' . $session->scheduled_at->format('d M Y'),
            'time_limit_minutes'  => 15,
            'pass_percentage'     => 50,
            'max_attempts'        => 3,
            'show_answers_after'  => true,
            'is_published'        => false, // teacher reviews before publishing
        ]);

        foreach ($questions as $i => $q) {
            QuizQuestion::create([
                'quiz_id'        => $quiz->id,
                'type'           => 'mcq',
                'question'       => $q['question'],
                'options'        => $q['options'] ?? [],
                'correct_answer' => $q['correct_answer'],
                'explanation'    => $q['explanation'] ?? null,
                'points'         => 1,
                'sort_order'     => $i,
            ]);
        }

        return $quiz->id;
    }

    private function stubResponse(LiveSession $session): array
    {
        return [
            'summary'        => "The session '{$session->title}' covered key curriculum content for {$session->course?->title}. The teacher delivered {$session->duration_minutes} minutes of instruction.",
            'action_items'   => [
                'Review today\'s session notes',
                'Complete the assigned practice exercises',
                'Attempt the AI-generated quiz for this session',
                'Prepare questions for the next session',
            ],
            'quiz_questions' => [],
        ];
    }
}
