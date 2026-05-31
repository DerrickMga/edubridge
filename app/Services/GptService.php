<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GptService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.openai.api_key', '');
        $this->model   = (string) config('services.openai.companion_model', 'gpt-4o');
        $this->baseUrl = (string) config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Send a conversation to GPT-4o via OpenAI Chat Completions API.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    /**
     * Send a pre-built messages array directly to GPT-4o (supports vision content blocks).
     *
     * @param  array  $messages   Full messages array, each item being ['role' => ..., 'content' => ...]
     */
    public function chatWithMessages(array $messages, int $maxTokens = 2000): string
    {
        if (empty($this->apiKey)) {
            return json_encode(['status' => 'error', 'reason' => 'OpenAI API key not configured.']);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model'      => 'gpt-4o',
                'messages'   => $messages,
                'max_tokens' => $maxTokens,
            ]);

        if ($response->failed()) {
            Log::error('GptService chatWithMessages: OpenAI failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'OpenAI request failed (' . $response->status() . '): ' . $response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }

    /**
     * Analyse an image using GPT-4o vision.
     *
     * @param  string  $base64     Raw base64 of the image (no data-URI prefix)
     * @param  string  $mimeType   e.g. "image/png"
     * @param  string  $question   The student's question about the image
     */
    public function vision(string $base64, string $mimeType, string $question, string $system = null): string
    {
        if (empty($this->apiKey)) {
            return "GPT-4o vision is not yet configured. Please add OPENAI_API_KEY to your environment.";
        }

        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = [
            'role'    => 'user',
            'content' => [
                [
                    'type'      => 'image_url',
                    'image_url' => [
                        'url'    => "data:{$mimeType};base64,{$base64}",
                        'detail' => 'high',
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => $question,
                ],
            ],
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model'       => 'gpt-4o',   // always use full gpt-4o for vision
                'messages'    => $messages,
                'max_tokens'  => 2000,
            ]);

        if ($response->failed()) {
            Log::error('GptService vision: OpenAI failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'OpenAI vision request failed ('.$response->status().'): '.$response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }

    /**
     * Generate a study plan as structured JSON.
     */
    public function generateStudyPlan(string $subject, string $level, int $weeks, array $topics): array
    {
        $system = <<<SYS
You are an expert education planner for Zimbabwean students. Generate a detailed,
week-by-week study plan. Respond ONLY with valid JSON matching:
{
  "title": "string",
  "subject": "string",
  "level": "string",
  "weeks": [ { "week": 1, "theme": "string", "goals": ["string"], "activities": ["string"], "resources": ["string"] } ]
}
SYS;

        $userMsg = "Create a {$weeks}-week study plan for {$subject} at {$level} level. "
                 . "Cover these topics: " . implode(', ', $topics);

        $raw = $this->chat([['role' => 'user', 'content' => $userMsg]], $system);

        // Strip markdown fences if present
        $json = preg_replace('/^```json\s*|\s*```$/', '', trim($raw));
        $data = json_decode($json, true);

        return $data ?? ['error' => 'Could not parse study plan', 'raw' => $raw];
    }

    /**
     * Summarise a lesson transcript or notes.
     */
    public function summariseLesson(string $content, string $subject = ''): array
    {
        $system = <<<SYS
You are an expert educator creating concise lesson summaries for teachers and students.
Respond ONLY with valid JSON matching:
{
  "title": "string",
  "summary": "string (2-3 paragraphs)",
  "key_points": ["string"],
  "vocabulary": [{"term": "string", "definition": "string"}],
  "homework_suggestions": ["string"]
}
SYS;

        $subjectHint = $subject ? " The subject is {$subject}." : '';
        $userMsg = "Summarise the following lesson content.{$subjectHint}\n\n{$content}";

        $raw = $this->chat([['role' => 'user', 'content' => $userMsg]], $system);
        $json = preg_replace('/^```json\s*|\s*```$/', '', trim($raw));
        $data = json_decode($json, true);

        return $data ?? ['summary' => $raw, 'key_points' => [], 'vocabulary' => []];
    }

    /**
     * Generate an advanced, deeply-detailed study plan with daily breakdown,
     * examples, practice questions, textbook references, and YouTube search queries.
     *
     * @return array  Structured plan with `weeks[]`, each containing `days[]`,
     *               `key_concepts[]`, `practice_questions[]`, `textbook_refs[]`,
     *               `youtube_queries[]`, and `common_mistakes[]`.
     */
    public function generateAdvancedStudyPlan(
        string $subject,
        string $level,
        int    $weeks,
        array  $topics,
        string $examBoard  = 'ZIMSEC',
        string $memoryHint = ''
    ): array {
        $topicList  = implode(', ', $topics);
        $memNote    = $memoryHint ? "\n\nStudent learning profile: {$memoryHint}" : '';

        $system = <<<SYS
You are an expert {$examBoard} curriculum planner and master educator for {$subject} at {$level} level.
Generate a comprehensive, highly detailed advanced study plan.
Every day must have specific, actionable content — not vague instructions.
Include real example problems with worked solutions where relevant.
Textbook references should use common {$examBoard} {$level} {$subject} textbooks (e.g. "Longman Mathematics for O-Level Chapter 5 pp.88-95").
YouTube search queries must be specific enough to find high-quality educational videos.
Respond ONLY with valid JSON — no markdown fences — matching EXACTLY this structure:
{
  "title": "string",
  "subject": "string",
  "level": "string",
  "exam_board": "string",
  "total_weeks": number,
  "overview": "2-3 sentence overview of the plan",
  "weeks": [
    {
      "week": 1,
      "theme": "string",
      "overview": "string",
      "goals": ["string"],
      "key_concepts": ["string"],
      "common_mistakes": ["string — what students typically get wrong"],
      "days": [
        {
          "day": "Monday",
          "focus": "string — specific topic for this day",
          "duration_minutes": 90,
          "objectives": ["string"],
          "content_summary": "string — 2-3 sentence explanation of what to study",
          "worked_examples": [
            {
              "question": "string — actual exam-style question",
              "solution": "string — full step-by-step worked solution",
              "marks": 5
            }
          ],
          "practice_questions": [
            {
              "question": "string — exam-style question",
              "hint": "string — hint without giving away the answer",
              "difficulty": "easy|medium|hard"
            }
          ],
          "textbook_refs": [
            {
              "book": "string — textbook title and edition",
              "chapter": "string",
              "pages": "string — e.g. pp.45-52",
              "topic_in_book": "string"
            }
          ],
          "youtube_queries": [
            {
              "query": "string — specific YouTube search query",
              "purpose": "string — what the student will learn from this video",
              "duration_hint": "string — e.g. under 10 min"
            }
          ]
        }
      ],
      "weekly_self_assessment": ["string — question to test if goals were met"],
      "revision_tips": ["string"]
    }
  ],
  "exam_strategy": {
    "time_management": "string",
    "common_exam_mistakes": ["string"],
    "mark_scheme_tips": ["string"]
  }
}
SYS;

        $userMsg = "Create a {$weeks}-week advanced study plan for {$subject} at {$level} level on: {$topicList}.{$memNote}";

        $raw = $this->chat([['role' => 'user', 'content' => $userMsg]], $system, maxTokens: 8000);

        // Strip any accidental markdown fences
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
        $data = json_decode($json, true);

        if (! $data) {
            return ['error' => 'Could not parse advanced study plan', 'raw' => substr($raw, 0, 500)];
        }

        return $data;
    }

    /**
     * Wrapper that accepts an optional maxTokens parameter.
     */
    public function chat(array $messages, string $system = null, int $maxTokens = 4000): string
    {
        if (empty($this->apiKey)) {
            Log::warning('GptService: OpenAI API key not configured.');
            return "GPT-4o is not yet configured. Please add OPENAI_API_KEY to your environment.";
        }

        $payload = $messages;
        if ($system !== null) {
            array_unshift($payload, ['role' => 'system', 'content' => $system]);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post($this->baseUrl . '/chat/completions', [
                'model'       => $this->model,
                'messages'    => $payload,
                'temperature' => 0.7,
                'max_tokens'  => $maxTokens,
            ]);

        if ($response->failed()) {
            Log::error('GptService: OpenAI failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'OpenAI request failed ('.$response->status().'): '.$response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }
}
