<?php
namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppMessage;
use App\Models\WhatsappWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * WhatsApp Cloud API webhook verification (GET).
     */
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Receive inbound messages from WhatsApp Cloud API (POST).
     */
    public function receive(Request $request)
    {
        // Verify X-Hub-Signature-256 using App Secret
        $appSecret = config('services.whatsapp.app_secret');
        if ($appSecret) {
            $signature = $request->header('X-Hub-Signature-256', '');
            $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);
            if (! hash_equals($expected, $signature)) {
                Log::warning('WhatsApp webhook: invalid signature');
                return response('Forbidden', 403);
            }
        }

        $payload = $request->all();
        Log::channel('daily')->info('WhatsApp webhook received', $payload);

        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            foreach (data_get($entry, 'changes', []) as $change) {
                $messages = data_get($change, 'value.messages', []);
                foreach ($messages as $msg) {
                    $messageId = data_get($msg, 'id');

                    // Deduplicate: Meta retries webhooks when a 200 is slow.
                    // If we've already stored this wamid, silently skip it.
                    if ($messageId && WhatsappWebhook::where('message_id', $messageId)->exists()) {
                        Log::info('WhatsApp webhook: duplicate message_id skipped', ['message_id' => $messageId]);
                        continue;
                    }

                    $msgType = data_get($msg, 'type', 'text');

                    // Flow completions arrive as type=interactive with sub-type nfm_reply.
                    // Normalise early so the job sees type=nfm_reply.
                    if ($msgType === 'interactive' && data_get($msg, 'interactive.type') === 'nfm_reply') {
                        $msgType = 'nfm_reply';
                    }

                    // Extract content based on message type
                    $content = match ($msgType) {
                        'text'        => data_get($msg, 'text.body'),
                        'interactive' => json_encode(data_get($msg, 'interactive', [])),
                        'nfm_reply'   => json_encode(data_get($msg, 'interactive.nfm_reply') ?? data_get($msg, 'nfm_reply', [])),
                        default       => null,
                    };

                    $webhook = WhatsappWebhook::create([
                        'from_phone' => $messageId ? data_get($msg, 'from') : null,
                        'message_id' => $messageId,
                        'type'       => $msgType,
                        'content'    => $content,
                        'payload'    => $msg,
                    ]);
                    ProcessWhatsAppMessage::dispatch($webhook);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
