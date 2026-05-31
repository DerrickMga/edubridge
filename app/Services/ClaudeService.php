<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ClaudeService
{
    private string $endpoint;
    private string $apiKey;
    private string $apiVersion;

    public function __construct()
    {
        $this->endpoint   = config('services.azure_ai.endpoint', '');
        $this->apiKey     = config('services.azure_ai.key', '');
        $this->apiVersion = config('services.azure_ai.api_version', '2025-01-01');
    }

    /**
     * Send a conversation to the Azure AI ChiedzaEdu agent.
     * Uses the OpenAI Responses API with the required api-version query param.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null): string
    {
        if (empty($this->endpoint) || empty($this->apiKey)) {
            Log::warning('ClaudeService: Azure AI not configured, returning stub.');
            return "I'm Chiedza, your AI study companion. Azure AI is not yet configured — please contact your administrator.";
        }

        $input = $messages;
        if ($system !== null) {
            array_unshift($input, ['role' => 'system', 'content' => $system]);
        }

        // Build URL with required api-version query parameter
        $url = $this->endpoint . (str_contains($this->endpoint, '?') ? '&' : '?')
             . 'api-version=' . $this->apiVersion;

        $response = Http::withToken($this->apiKey)
            ->timeout(45)
            ->post($url, ['input' => $input]);

        if ($response->failed()) {
            Log::error('ClaudeService: Azure AI failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'Azure AI request failed ('.$response->status().'): '.$response->body()
            );
        }

        $body = $response->json();

        // OpenAI Responses API v1: output[0].content[0].text
        $text = data_get($body, 'output.0.content.0.text');

        // Fallback: standard chat completions format
        if (empty($text)) {
            $text = data_get($body, 'choices.0.message.content');
        }

        return (string) ($text ?? '');
    }
}
