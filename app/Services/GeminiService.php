<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini note-taker service.
 *
 * Used as the first-pass AI observer for live sessions:
 *   session context / Zoom transcript
 *     → Gemini: detailed structured notes (long-form, high recall)
 *     → GPT-4o: validates & structures into report JSON
 *
 * API key from Google AI Studio: https://aistudio.google.com/app/apikey
 * (Different from GOOGLE_API_KEY — that is for YouTube/Calendar APIs)
 */
class GeminiService
{
    private string $apiKey;
    private string $model;
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Take structured educational notes from a session context or transcript.
     *
     * Gemini is excellent at long-form document processing and produces
     * detailed markdown notes that GPT-4o then validates and structures.
     *
     * @param  string  $sessionContext  Session metadata + transcript excerpt
     * @return string  Markdown-formatted notes, or empty string on failure
     */
    public function takeNotes(string $sessionContext): string
    {
        $url = self::BASE_URL . "/{$this->model}:generateContent?key={$this->apiKey}";

        $prompt = <<<PROMPT
You are an expert educational note-taker. Review this live teaching session and produce detailed structured notes that capture everything taught.

Format your output exactly as follows:

## Key Topics Covered
- bullet points of all main topics discussed

## Core Concepts Explained
- each concept with a brief 1-2 sentence explanation

## Examples & Analogies Used
- any examples, analogies, stories, or demonstrations the teacher used

## Student Interaction Points
- questions raised, discussion moments, any misconceptions addressed

## Learning Objectives (inferred)
- what students should know or be able to do after this session

## Gaps or Follow-up Needed
- anything that appeared unfinished, rushed, or would benefit from follow-up

---

Session context:
{$sessionContext}
PROMPT;

        $response = Http::timeout(45)->post($url, [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => [
                'temperature'     => 0.2,
                'maxOutputTokens' => 2048,
            ],
        ]);

        if ($response->failed()) {
            Log::warning('GeminiService: note-taking failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            return '';
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        if (empty($text)) {
            Log::warning('GeminiService: empty notes returned', [
                'model' => $this->model,
                'response' => $response->json(),
            ]);
        }

        return $text;
    }

    /**
     * General-purpose chat completion via Gemini.
     *
     * Converts OpenAI-style message history into Gemini's `contents` format,
     * with optional system instruction.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null, int $maxTokens = 2048): string
    {
        if (empty($this->apiKey)) {
            Log::warning('GeminiService: GEMINI_API_KEY not configured.');
            throw new \RuntimeException('Gemini API key not configured.');
        }

        // Convert OpenAI-style roles to Gemini roles (user/model)
        $contents = [];
        foreach ($messages as $msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        if (empty($contents)) {
            $contents = [['role' => 'user', 'parts' => [['text' => 'Hello']]]];
        }

        $url = self::BASE_URL . "/{$this->model}:generateContent?key={$this->apiKey}";

        $body = [
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => 0.75,
                'maxOutputTokens' => $maxTokens,
            ],
        ];

        if ($system !== null) {
            $body['system_instruction'] = [
                'parts' => [['text' => $system]],
            ];
        }

        $response = Http::timeout(45)->post($url, $body);

        if ($response->failed()) {
            Log::error('GeminiService: chat failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException(
                'Gemini chat failed (' . $response->status() . '): ' . $response->body()
            );
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        if (empty($text)) {
            Log::warning('GeminiService: empty chat response', ['response' => $response->json()]);
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        return $text;
    }
}
