<?php

namespace App\Http\Controllers;

use App\Models\LiveSession;
use App\Models\Recording;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZoomWebhookController extends Controller
{
    /**
     * Entry point for all Zoom webhook events.
     * Registered in Zoom Marketplace → Event Subscriptions → POST {APP_URL}/webhooks/zoom
     *
     * Handles:
     *   endpoint.url_validation — Zoom's one-time challenge to verify the endpoint
     *   recording.completed     — Auto-creates Recording records
     *   meeting.ended           — Marks the LiveSession as completed
     */
    public function handle(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->json()->all();
        $event   = $payload['event'] ?? null;

        // ── URL validation challenge ──────────────────────────────────────────
        // Zoom sends this when you first register or re-verify the endpoint.
        if ($event === 'endpoint.url_validation') {
            $token = config('services.zoom.webhook_secret_token', '');
            $plain = $payload['payload']['plainToken'] ?? '';
            return response()->json([
                'plainToken'     => $plain,
                'encryptedToken' => hash_hmac('sha256', $plain, $token),
            ]);
        }

        // ── Verify HMAC signature on all other events ─────────────────────────
        if (! $this->verifySignature($request)) {
            Log::warning('ZoomWebhook: invalid signature', [
                'ip'    => $request->ip(),
                'event' => $event,
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ── Route to event handlers ───────────────────────────────────────────
        $object = $payload['payload']['object'] ?? [];

        match ($event) {
            'recording.completed' => $this->onRecordingCompleted($object),
            'meeting.ended'       => $this->onMeetingEnded($object),
            default               => null,
        };

        return response()->json(['status' => 'ok']);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Verify Zoom's webhook signature.
     * Zoom signs requests with: v0=HMAC-SHA256("v0:{timestamp}:{raw_body}", secret)
     */
    private function verifySignature(Request $request): bool
    {
        $secret    = config('services.zoom.webhook_secret_token', '');
        $ts        = $request->header('x-zm-request-timestamp');
        $signature = $request->header('x-zm-signature');

        if (! $ts || ! $signature || ! $secret) {
            return false;
        }

        $message  = "v0:{$ts}:" . $request->getContent();
        $expected = 'v0=' . hash_hmac('sha256', $message, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Zoom event: recording.completed
     * Auto-populate Recording records for the matching LiveSession.
     */
    private function onRecordingCompleted(array $object): void
    {
        $meetingId = (string) ($object['id'] ?? '');
        if (! $meetingId) return;

        $session = LiveSession::where('meeting_id', $meetingId)->first();

        if (! $session) {
            Log::info("ZoomWebhook: recording.completed for unknown meeting_id={$meetingId}");
            return;
        }

        $files = $object['recording_files'] ?? [];
        $created = 0;

        foreach ($files as $file) {
            if (($file['file_type'] ?? '') !== 'MP4') continue;
            if (($file['status'] ?? '') !== 'completed') continue;

            $url = $file['play_url'] ?? $file['download_url'] ?? null;
            if (! $url) continue;

            // Avoid duplicates
            if (Recording::where('external_url', $url)->exists()) continue;

            $durationSec = 0;
            if (! empty($file['recording_start']) && ! empty($file['recording_end'])) {
                $durationSec = (int) Carbon::parse($file['recording_end'])
                    ->diffInSeconds(Carbon::parse($file['recording_start']));
            }

            Recording::create([
                'live_session_id' => $session->id,
                'course_id'       => $session->course_id,
                'teacher_id'      => $session->teacher_id,
                'title'           => $session->title . ' — Recording',
                'external_url'    => $url,
                'source'          => 'zoom',
                'duration_seconds'=> $durationSec,
                'file_size_bytes' => $file['file_size'] ?? 0,
                'status'          => 'available',
                'is_public'       => true,
            ]);

            $created++;
        }

        // Auto-mark session as completed
        if (! in_array($session->status, ['completed', 'cancelled'])) {
            $session->update(['status' => 'completed']);
        }

        Log::info("ZoomWebhook: recording.completed — session #{$session->id}, {$created} recording(s) created");
    }

    /**
     * Zoom event: meeting.ended
     * Mark the corresponding LiveSession as completed.
     */
    private function onMeetingEnded(array $object): void
    {
        $meetingId = (string) ($object['id'] ?? '');
        if (! $meetingId) return;

        $updated = LiveSession::where('meeting_id', $meetingId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'completed']);

        if ($updated) {
            Log::info("ZoomWebhook: meeting.ended — meeting_id={$meetingId} marked completed");
        }
    }
}
