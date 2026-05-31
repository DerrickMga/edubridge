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
    public function chat(array $messages, string $system = null): string
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
            ->timeout(45)
            ->post($this->baseUrl . '/chat/completions', [
                'model'       => $this->model,
                'messages'    => $payload,
                'temperature' => 0.7,
                'max_tokens'  => 4000,
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
}
