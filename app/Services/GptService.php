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
        $memNote    = $memoryHint ? "\n\nStudent learning profile (personalise difficulty and pacing accordingly): {$memoryHint}" : '';

        $system = <<<SYS
You are a world-class curriculum designer and master educator specialising in {$subject} at {$level} level.
Your study plans are used globally by serious students preparing for high-stakes examinations.

CRITICAL RULES — follow every one exactly:

1. CONTENT QUALITY
   - Every day must have SPECIFIC, ACTIONABLE content — never vague platitudes like "study the topic".
   - Worked examples must be genuine exam-quality problems with complete, step-by-step solutions.
   - Practice questions must vary in difficulty across each week. Hard questions should challenge top students.
   - Use LaTeX notation for ALL mathematics: inline math with \( ... \) and display math with \[ ... \].
   - Common mistakes must be specific pitfalls students actually make, not generic advice.

2. TEXTBOOK REFERENCES
   - Reference real, widely-used textbooks for {$subject} at {$level} (e.g. Bostock & Chandler, Sadler & Thorning, Longman, Cambridge International AS & A Level series).
   - Include chapter title and specific page range.
   - Pages field must be ONLY the number range (e.g. "88-95"), never include "pp." — that is added by the UI.

3. YOUTUBE QUERIES — THIS IS THE MOST IMPORTANT SECTION
   - Each query must find REAL, watchable educational videos that exist on YouTube.
   - Queries must be UNIVERSAL and concept-focused — never include exam board names (no "ZIMSEC", no "Cambridge", no "IGCSE" unless the concept itself is unique to that board).
   - Write queries the way a student would search: "[concept] explained", "[technique] step by step", "[concept] tutorial with examples", "how to solve [problem type]", "visualising [concept]".
   - Provide EXACTLY 2 queries per day with DIFFERENT angles:
     * Query 1: Conceptual introduction / visual explanation (e.g. "integration by substitution explained visually")
     * Query 2: Worked examples / exam technique (e.g. "integration by substitution exam problems walkthrough")
   - Each query should be 4–8 words, specific enough to return relevant results.

4. OUTPUT FORMAT
   - Respond with ONLY valid JSON. No markdown fences. No prose before or after.
   - Match EXACTLY this structure:

{
  "title": "string",
  "subject": "string",
  "level": "string",
  "exam_board": "string",
  "total_weeks": number,
  "overview": "2-3 sentence overview",
  "weeks": [
    {
      "week": 1,
      "theme": "string",
      "overview": "string — what this week builds towards",
      "goals": ["string — measurable learning outcome"],
      "key_concepts": ["string"],
      "common_mistakes": ["string — specific mistake students make"],
      "days": [
        {
          "day": "Monday",
          "focus": "string — precise topic for this day",
          "duration_minutes": 90,
          "objectives": ["string — by end of session student can..."],
          "content_summary": "string — 3-4 sentence explanation of what to study and why",
          "worked_examples": [
            {
              "question": "string — real exam-style question using LaTeX for math",
              "solution": "string — full numbered step-by-step solution using LaTeX",
              "marks": 5
            }
          ],
          "practice_questions": [
            {
              "question": "string — exam-style question using LaTeX",
              "hint": "string — nudge without giving away the answer",
              "difficulty": "easy|medium|hard"
            }
          ],
          "textbook_refs": [
            {
              "book": "string — full textbook title",
              "chapter": "string — chapter number and name",
              "pages": "string — number range only e.g. 88-95",
              "topic_in_book": "string"
            }
          ],
          "youtube_queries": [
            {
              "query": "string — universal concept-focused search query, 4-8 words",
              "purpose": "string — one sentence: what the student gains from this video",
              "duration_hint": "string — e.g. under 15 min"
            }
          ]
        }
      ],
      "weekly_self_assessment": ["string — reflective question to gauge understanding"],
      "revision_tips": ["string — concrete technique, not generic advice"]
    }
  ],
  "exam_strategy": {
    "time_management": "string — specific advice for this subject's exam format",
    "common_exam_mistakes": ["string — specific to this subject and level"],
    "mark_scheme_tips": ["string — how to maximise marks on this type of question"]
  }
}
SYS;

        $userMsg = <<<MSG
Create a {$weeks}-week advanced study plan.
Subject: {$subject}
Level: {$level}
Exam board: {$examBoard}
Topics to cover: {$topicList}
{$memNote}

Remember: YouTube queries must be universal (no exam board names), concept-focused, and written as a student would naturally search.
MSG;

        $raw = $this->chatJson([['role' => 'user', 'content' => $userMsg]], $system, maxTokens: 8000);

        // chatJson guarantees valid JSON from the API, but strip fences defensively
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($raw));
        $data = json_decode($json, true);

        if (! $data) {
            return ['error' => 'Could not parse advanced study plan', 'raw' => substr($raw, 0, 500)];
        }

        return $data;
    }

    /**
     * Like chat() but forces JSON output and uses a lower temperature for precision.
     * Uses GPT-4o's response_format: json_object mode.
     */
    public function chatJson(array $messages, string $system = null, int $maxTokens = 8000): string
    {
        if (empty($this->apiKey)) {
            Log::warning('GptService: OpenAI API key not configured.');
            return '{"error":"OpenAI API key not configured"}';
        }

        $payload = $messages;
        if ($system !== null) {
            array_unshift($payload, ['role' => 'system', 'content' => $system]);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post($this->baseUrl . '/chat/completions', [
                'model'           => $this->model,
                'messages'        => $payload,
                'temperature'     => 0.3,
                'max_tokens'      => $maxTokens,
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            Log::error('GptService: chatJson failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException(
                'OpenAI JSON request failed ('.$response->status().'): '.$response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '{}');
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
