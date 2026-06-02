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

    /**
     * Send a 6-digit OTP via WhatsApp.
     * Tries the configured authentication template first;
     * falls back to a plain text message if the template call fails.
     */
    public function sendOtp(string $to, string $code): bool
    {
        $to           = $this->e164($to);
        $templateName = config('services.whatsapp.otp_template', 'otp_verification');

        $response = Http::withToken($this->token)->post($this->apiUrl, [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => 'en_US'],
                'components' => [
                    [
                        'type'       => 'body',
                        'parameters' => [['type' => 'text', 'text' => $code]],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            return true;
        }

        // Fallback: plain-text free-form message
        Log::warning('WhatsApp OTP template failed, falling back to text', [
            'to'    => $to,
            'error' => $response->body(),
        ]);

        $text = "Your EduBridge verification code is: *{$code}*\n\nThis code expires in 10 minutes. Do not share it with anyone.";

        $fallback = Http::withToken($this->token)->post($this->apiUrl, [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'text',
            'text'              => ['body' => $text, 'preview_url' => false],
        ]);

        if ($fallback->failed()) {
            Log::error('WhatsApp OTP send failed entirely', ['to' => $to, 'error' => $fallback->body()]);
            return false;
        }

        return true;
    }

    /** Normalise a phone number to E.164 (digits only, leading +). */
    private function e164(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        return '+' . ltrim($digits, '+');
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
