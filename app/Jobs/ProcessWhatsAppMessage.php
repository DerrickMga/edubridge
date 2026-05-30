<?php
namespace App\Jobs;

use App\Models\{Conversation, User, WhatsappWebhook};
use App\Services\{ClaudeService, WhatsAppService};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WhatsappWebhook $webhook) {}

    public function handle(ClaudeService $claude, WhatsAppService $whatsapp): void
    {
        if ($this->webhook->type !== 'text' || !$this->webhook->content) {
            $this->webhook->update(['processed' => true]);
            return;
        }

        // Find or create user by phone number
        $user = User::firstOrCreate(
            ['phone' => $this->webhook->from_phone],
            ['name' => $this->webhook->from_phone, 'email' => $this->webhook->from_phone.'@wa.edubridge.co.zw', 'password' => bcrypt(str()->random(32)), 'role' => 'student']
        );

        // Find or create active WhatsApp conversation
        $conversation = $user->conversations()
            ->where('channel', 'whatsapp')
            ->latest()
            ->firstOrCreate(['channel' => 'whatsapp', 'user_id' => $user->id]);

        $conversation->messages()->create(['role' => 'user', 'content' => $this->webhook->content]);

        $history = $conversation->messages()->get()->map(fn($m) => [
            'role' => $m->role, 'content' => $m->content,
        ])->toArray();

        $reply = $claude->chat($history);

        $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);

        // Send reply back via WhatsApp
        $whatsapp->sendText($this->webhook->from_phone, $reply);

        $this->webhook->update(['processed' => true]);
    }
}
