<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * AnthropicService — calls Claude via Microsoft Azure AI Foundry.
 *
 * Azure AI Foundry Responses API (Claude models):
 *   POST {endpoint}/responses?api-version={version}
 *   Payload: { model, instructions, input: [{role, content}] }
 *   Response path: output[0].content[0].text
 *
 * Ported from KMG VitalBot (AzureFoundryService.php).
 */
class AnthropicService
{
    private string $apiKey;
    private string $endpoint;
    private string $model;
    private string $apiVersion;

    public function __construct()
    {
        $this->endpoint   = rtrim((string) config('services.anthropic.endpoint', ''), '/');
        $this->apiKey     = (string) config('services.anthropic.api_key', '');
        $this->model      = (string) config('services.anthropic.model', 'claude-opus-4-7');
        $this->apiVersion = (string) config('services.anthropic.api_version', '2024-10-21');
    }

    /**
     * Send a conversation to Claude via Azure AI Foundry Responses API.
     *
     * @param  array<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, string $system = null): string
    {
        if (empty($this->apiKey) || empty($this->endpoint)) {
            Log::warning('AnthropicService: Azure Foundry endpoint or key not configured.');
            return 'Claude AI is not configured. Please add ANTHROPIC_ENDPOINT and ANTHROPIC_API_KEY to your .env file.';
        }

        // Filter to user/assistant messages for the input array
        $input = array_values(array_filter($messages, fn($m) => in_array($m['role'], ['user', 'assistant'])));

        if (empty($input)) {
            $input = [['role' => 'user', 'content' => 'Hello']];
        }

        $payload = [
            'model' => $this->model,
            'input' => $input,
        ];

        if (!empty($system)) {
            $payload['instructions'] = $system;
        }

        $url = $this->endpoint . '/responses?api-version=' . $this->apiVersion;

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(60)
        ->post($url, $payload);

        if ($response->failed()) {
            Log::error('AnthropicService: Azure Foundry request failed', [
                'status'   => $response->status(),
                'endpoint' => $url,
                'body'     => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException(
                'Azure AI Foundry (Claude) request failed (' . $response->status() . '): ' . $response->body()
            );
        }

        // Responses API path: output[0].content[0].text
        $text = data_get($response->json(), 'output.0.content.0.text', '');

        if (empty($text)) {
            Log::warning('AnthropicService: Empty text in response', ['body' => substr($response->body(), 0, 300)]);
        }

        return (string) $text;
    }
}

