<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * GroqService — ultra-fast inference via Groq Cloud API.
 *
 * Groq uses an OpenAI-compatible API. Free tier is generous:
 *   - llama-3.3-70b-versatile  (best quality, reasoning)
 *   - llama-3.1-8b-instant     (fastest, good for quick answers)
 *   - gemma2-9b-it              (Google Gemma 2 — strong at language/instruction)
 *   - mixtral-8x7b-32768        (Mistral MoE — large context)
 *
 * Sign up & get a free API key: https://console.groq.com
 */
class GroqService
{
    private string $apiKey;
    private string $model;
    private const BASE_URL = 'https://api.groq.com/openai/v1';

    /** Models available on Groq free tier */
    public const MODELS = [
        'llama-3.3-70b-versatile' => 'Llama 3.3 70B (best quality)',
        'llama-3.1-8b-instant'    => 'Llama 3.1 8B (fastest)',
        'gemma2-9b-it'            => 'Gemma 2 9B',
        'mixtral-8x7b-32768'      => 'Mixtral 8x7B (32k context)',
    ];

    public function __construct()
    {
        $this->apiKey = (string) config('services.groq.api_key', '');
        $this->model  = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Chat completion via Groq.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null, int $maxTokens = 2000): string
    {
        if (empty($this->apiKey)) {
            Log::warning('GroqService: GROQ_API_KEY not configured.');
            throw new RuntimeException('Groq API key not configured.');
        }

        $payload = $messages;
        if ($system !== null) {
            array_unshift($payload, ['role' => 'system', 'content' => $system]);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(45)
            ->post(self::BASE_URL . '/chat/completions', [
                'model'       => $this->model,
                'messages'    => $payload,
                'temperature' => 0.75,
                'max_tokens'  => $maxTokens,
            ]);

        if ($response->failed()) {
            Log::error('GroqService: request failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'Groq request failed (' . $response->status() . '): ' . $response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }
}
