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
        $payload = $request->all();
        Log::channel('daily')->info('WhatsApp webhook received', $payload);

        $entries = data_get($payload, 'entry', []);

        foreach ($entries as $entry) {
            foreach (data_get($entry, 'changes', []) as $change) {
                $messages = data_get($change, 'value.messages', []);
                foreach ($messages as $msg) {
                    $webhook = WhatsappWebhook::create([
                        'from_phone' => data_get($msg, 'from'),
                        'message_id' => data_get($msg, 'id'),
                        'type'       => data_get($msg, 'type', 'text'),
                        'content'    => data_get($msg, 'text.body'),
                        'payload'    => $msg,
                    ]);
                    ProcessWhatsAppMessage::dispatch($webhook);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
