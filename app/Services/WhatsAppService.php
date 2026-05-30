<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $token;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiUrl        = 'https://graph.facebook.com/v19.0/'.$this->phoneNumberId.'/messages';
    }

    public function sendText(string $to, string $body): void
    {
        $response = Http::withToken($this->token)->post($this->apiUrl, [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['body' => $body],
        ]);

        if ($response->failed()) {
            Log::error('WhatsApp send failed', ['to' => $to, 'error' => $response->body()]);
        }
    }

    public function sendTemplate(string $to, string $templateName, array $components = []): void
    {
        Http::withToken($this->token)->post($this->apiUrl, [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => ['name' => $templateName, 'language' => ['code' => 'en'], 'components' => $components],
        ]);
    }
}
