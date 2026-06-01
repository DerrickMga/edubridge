<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * DeepSeekService — DeepSeek AI via OpenAI-compatible API.
 *
 * Models:
 *   - deepseek-chat     (DeepSeek-V3 — excellent at STEM, coding, reasoning)
 *   - deepseek-reasoner (DeepSeek-R1 — chain-of-thought, best for maths/logic)
 *
 * Very affordable pricing; free tier available.
 * Sign up: https://platform.deepseek.com
 */
class DeepSeekService
{
    private string $apiKey;
    private string $model;
    private const BASE_URL = 'https://api.deepseek.com';

    public function __construct()
    {
        $this->apiKey = (string) config('services.deepseek.api_key', '');
        $this->model  = (string) config('services.deepseek.model', 'deepseek-chat');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Chat completion via DeepSeek.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null, int $maxTokens = 2000): string
    {
        if (empty($this->apiKey)) {
            Log::warning('DeepSeekService: DEEPSEEK_API_KEY not configured.');
            throw new RuntimeException('DeepSeek API key not configured.');
        }

        $payload = $messages;
        if ($system !== null) {
            array_unshift($payload, ['role' => 'system', 'content' => $system]);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post(self::BASE_URL . '/chat/completions', [
                'model'       => $this->model,
                'messages'    => $payload,
                'temperature' => 0.7,
                'max_tokens'  => $maxTokens,
            ]);

        if ($response->failed()) {
            Log::error('DeepSeekService: request failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'DeepSeek request failed (' . $response->status() . '): ' . $response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }
}
