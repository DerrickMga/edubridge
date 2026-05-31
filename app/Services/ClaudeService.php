<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * ClaudeService — powers the "Chiedza" AI study companion persona.
 *
 * Backed by OpenAI GPT-4o. Chiedza is a warm, culturally-aware Zimbabwean
 * study companion who understands the ZIMSEC curriculum deeply.
 */
class ClaudeService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    /**
     * Chiedza's core identity, prepended to every system prompt.
     */
    private const CHIEDZA_PERSONA = "You are Chiedza, a warm and encouraging AI study companion built for Zimbabwean students on the EduBridge platform. You have deep knowledge of the ZIMSEC curriculum (O-Level and A-Level), Zimbabwean history, culture, and everyday life. You speak clearly and simply, adapt to each student's level, and always encourage. When a student is struggling, you break things down step-by-step and use relatable Zimbabwean examples where helpful. You are patient, positive, and genuinely invested in every student's success.";

    public function __construct()
    {
        $this->apiKey  = config('services.openai.api_key', '');
        $this->model   = config('services.openai.companion_model', 'gpt-4o');
        $this->baseUrl = config('services.openai.base_url', 'https://api.openai.com/v1');
    }

    /**
     * Chat as Chiedza — GPT-4o with a Zimbabwean study companion persona.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null): string
    {
        if (empty($this->apiKey)) {
            Log::warning('ClaudeService (Chiedza): OpenAI API key not configured.');
            return "I'm Chiedza, your AI study companion. I'm not fully set up yet — please contact your administrator.";
        }

        // Chiedza persona + optional caller system context
        $chiedzaSystem = self::CHIEDZA_PERSONA;
        if ($system) {
            $chiedzaSystem .= "\n\n" . $system;
        }

        $payload = $messages;
        array_unshift($payload, ['role' => 'system', 'content' => $chiedzaSystem]);

        $response = Http::withToken($this->apiKey)
            ->timeout(45)
            ->post($this->baseUrl . '/chat/completions', [
                'model'       => $this->model,
                'messages'    => $payload,
                'temperature' => 0.8,
                'max_tokens'  => 2000,
            ]);

        if ($response->failed()) {
            Log::error('ClaudeService (Chiedza): OpenAI request failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'Chiedza (OpenAI) request failed (' . $response->status() . '): ' . $response->body()
            );
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }
}
