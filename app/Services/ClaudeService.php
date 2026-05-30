<?php
namespace App\Services;

use Anthropic\Anthropic;

class ClaudeService
{
    private Anthropic $client;

    public function __construct()
    {
        $this->client = Anthropic::client(config('services.anthropic.api_key'));
    }

    public function chat(array $messages, string $system = null): string
    {
        $system ??= <<<PROMPT
You are Chiedza, EduBridge's AI learning companion for Zimbabwean students.
You help students understand their school subjects (O-level and A-level curriculum),
answer questions, explain concepts clearly, and encourage them.
Be warm, supportive, and culturally aware. Use simple English.
Keep answers concise and focused on learning.
PROMPT;

        $response = $this->client->messages()->create([
            'model'      => config('services.anthropic.model', 'claude-3-5-haiku-20241022'),
            'max_tokens' => 1024,
            'system'     => $system,
            'messages'   => $messages,
        ]);

        return $response->content[0]->text ?? '';
    }
}
