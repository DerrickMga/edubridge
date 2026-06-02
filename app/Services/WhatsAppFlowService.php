<?php

namespace App\Services;

use App\Models\WhatsAppFlow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppFlowService
 *
 * Manages WhatsApp Flow lifecycle (create, upload, publish, send)
 * against the ChiedzaByKMG WABA.
 */
class WhatsAppFlowService
{
    private string $token;
    private string $wabaId;
    private string $phoneNumberId;
    private string $base;

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token');
        $this->wabaId        = config('services.whatsapp.waba_id');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->base          = 'https://graph.facebook.com/v19.0';
    }

    // ─── Flow lifecycle ───────────────────────────────────────────────────────

    /** List all flows for the WABA. */
    public function listFlows(): array
    {
        return $this->get("{$this->wabaId}/flows", [
            'fields' => 'id,name,status,categories,validation_errors,data_api_version',
        ]);
    }

    /** Create a new flow and return Meta's flow ID. */
    public function createFlow(string $name, array $categories = ['EDUCATION']): array
    {
        return $this->post("{$this->wabaId}/flows", [
            'name'       => $name,
            'categories' => $categories,
        ]);
    }

    /** Upload / replace the flow JSON asset on an existing flow. */
    public function uploadFlowJson(string $flowId, array $flowJson): array
    {
        $response = Http::withToken($this->token)
            ->timeout(30)
            ->attach(
                'file',
                json_encode($flowJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'flow.json',
                ['Content-Type' => 'application/json']
            )
            ->post("{$this->base}/{$flowId}/assets", [
                'name'       => 'flow.json',
                'asset_type' => 'FLOW_JSON',
            ]);

        $body = $response->json() ?? [];
        if ($response->failed()) {
            Log::error('WhatsApp flow JSON upload failed', ['flow_id' => $flowId, 'body' => $body]);
            return ['success' => false, 'error' => $body['error']['message'] ?? 'upload failed', 'detail' => $body['error']['error_data'] ?? null];
        }
        return array_merge(['success' => true], $body);
    }

    /** Set the data-exchange endpoint URI on a flow (for non-static flows). */
    public function setEndpointUri(string $flowId, string $endpointUri): array
    {
        return $this->post($flowId, ['endpoint_uri' => $endpointUri]);
    }

    /** Upload the RSA-2048 public key for flow encryption to the phone number. */
    public function uploadPublicKey(string $publicKeyPem): array
    {
        // Key uploads go to the phone number ID, not the WABA ID
        return $this->post("{$this->phoneNumberId}/whatsapp_business_encryption", [
            'business_public_key' => $publicKeyPem,
        ]);
    }

    /** Publish a DRAFT flow (makes it live and immutable). */
    public function publishFlow(string $flowId): array
    {
        return $this->post("{$flowId}/publish");
    }

    /** Deprecate a published flow. */
    public function deprecateFlow(string $flowId): array
    {
        return $this->post("{$flowId}/deprecate");
    }

    // ─── Sending ──────────────────────────────────────────────────────────────

    /**
     * Send a WhatsApp Flow message to trigger an interactive flow.
     *
     * @param string $to          E.164 phone number
     * @param string $flowId      Meta flow ID
     * @param string $ctaLabel    Button label on the message (max 20 chars)
     * @param string $bodyText    Message body text
     * @param string $screenId    Initial screen to open (e.g. 'DASHBOARD')
     * @param string $flowToken   Token passed back to our endpoint (encode user context here)
     * @param array  $initData    Initial data for the first screen
     */
    public function sendFlowMessage(
        string $to,
        string $flowId,
        string $ctaLabel,
        string $bodyText,
        string $screenId = 'WELCOME',
        string $flowToken = 'default',
        array  $initData = []
    ): bool {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->e164($to),
            'type'              => 'interactive',
            'interactive'       => [
                'type'   => 'flow',
                'header' => ['type' => 'text', 'text' => 'EduBridge 🎓'],
                'body'   => ['text' => $bodyText],
                'footer' => ['text' => 'Powered by Chiedza AI'],
                'action' => [
                    'name'       => 'flow',
                    'parameters' => [
                        'flow_message_version' => '3',
                        'flow_token'           => $flowToken,
                        'flow_id'              => $flowId,
                        'flow_cta'             => $ctaLabel,
                        'flow_action'          => 'navigate',
                        'flow_action_payload'  => array_filter([
                            'screen' => $screenId,
                            'data'   => empty($initData) ? null : $initData,
                        ], fn($v) => $v !== null),
                    ],
                ],
            ],
        ];

        $response = Http::withToken($this->token)
            ->withBody(json_encode($payload), 'application/json')
            ->post("{$this->base}/{$this->phoneNumberId}/messages");

        if ($response->failed()) {
            Log::error('WhatsApp flow message send failed', ['to' => $to, 'flow_id' => $flowId, 'error' => $response->body()]);
            return false;
        }
        Log::info('WhatsApp flow message sent', ['to' => $to, 'flow_id' => $flowId]);
        return true;
    }

    /**
     * Send a 3-button interactive menu (main entry point for unrecognised users).
     */
    public function sendMainMenu(string $to): void
    {
        $registerFlow = WhatsAppFlow::findByKey('chiedza_register');
        $studentFlow  = WhatsAppFlow::findByKey('chiedza_student');
        $teacherFlow  = WhatsAppFlow::findByKey('chiedza_teacher');

        // If flows are published, send flow CTAs; otherwise fall back to button replies
        if ($registerFlow?->isPublished() && $studentFlow?->isPublished()) {
            $sent = $this->sendFlowMessage(
                $to,
                $registerFlow->meta_flow_id,
                'Join EduBridge ✍️',
                "Welcome to *EduBridge* 🎓\n\nZimbabwe's AI-powered learning platform.\n\nAre you a student or teacher? Tap below to get started.",
                'WELCOME',
                'menu:new_user'
            );
            if ($sent) return;
            // fall through to list-menu fallback if flow message failed
        }

        // Fallback: simple interactive button menu
        $response = Http::withToken($this->token)
            ->post("{$this->base}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => $this->e164($to),
                'type'              => 'interactive',
                'interactive'       => [
                    'type'   => 'list',
                    'body'   => ['text' => "Welcome to *EduBridge* 🎓\n\nZimbabwe's AI-powered learning platform.\n\nHow can I help you today?"],
                    'footer' => ['text' => 'Powered by Chiedza AI'],
                    'action' => [
                        'button'   => 'Choose an option',
                        'sections' => [
                            [
                                'title' => 'Get Started',
                                'rows'  => [
                                    ['id' => 'intent_register', 'title' => '✍️ Join EduBridge',     'description' => 'Create a free account'],
                                    ['id' => 'intent_student',  'title' => '📚 Student Hub',        'description' => 'Courses, AI tutor & progress'],
                                    ['id' => 'intent_teacher',  'title' => '🎓 Teacher Hub',        'description' => 'Courses, announcements & grades'],
                                    ['id' => 'intent_otp',      'title' => '🔐 Verify My Number',   'description' => 'Confirm your WhatsApp OTP'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('WhatsApp menu send failed', ['to' => $to, 'error' => $response->body()]);
            // Last-resort text fallback
            app(WhatsAppService::class)->sendText($to,
                "Welcome to *EduBridge* 🎓\n\nReply with:\n• *REGISTER* — Create account\n• *STUDENT* — Student hub\n• *TEACHER* — Teacher hub\n• *HELP* — Get help"
            );
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function get(string $path, array $params = []): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->base}/{$path}", $params);
        return $response->json() ?? [];
    }

    private function post(string $path, array $body = []): array
    {
        $response = Http::withToken($this->token)
            ->post("{$this->base}/{$path}", $body);
        return $response->json() ?? [];
    }

    private function e164(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        return '+' . ltrim($digits, '+');
    }
}
