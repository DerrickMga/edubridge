<?php

namespace App\Services;

use App\Models\GoogleToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Google Meet meeting management via the Google Calendar API.
 *
 * Creating an event with conferenceDataVersion=1 and a createRequest
 * instructs Google to provision a Meet room and attach it to the event.
 * This works with regular Gmail and Workspace accounts — no Workspace
 * subscription is required.
 *
 * The teacher must have connected their Google account (via the YouTube
 * OAuth flow) with the `calendar.events` scope.
 */
class GoogleMeetService
{
    private const CALENDAR_API = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';

    public function __construct(private readonly YouTubeService $youtube) {}

    // ── Auth check ──────────────────────────────────────────────────────────

    /**
     * Returns true if the user has a Google token that includes calendar scope.
     */
    public function isConnected(User $user): bool
    {
        $token = GoogleToken::where('user_id', $user->id)->first();

        return $token && str_contains((string) $token->scope, 'calendar');
    }

    // ── Meeting lifecycle ───────────────────────────────────────────────────

    /**
     * Create a Google Meet-enabled Calendar event.
     *
     * @return array{join_url: string, event_id: string|null, event_url: string|null}
     *
     * @throws RuntimeException
     */
    public function createMeeting(
        User   $teacher,
        string $title,
        string $startIso,       // UTC ISO-8601, e.g. 2025-06-01T14:00:00Z
        int    $durationMinutes,
    ): array {
        $accessToken = $this->resolveToken($teacher);

        $start = \Carbon\Carbon::parse($startIso)->utc();
        $end   = $start->copy()->addMinutes($durationMinutes);

        $resp = Http::withToken($accessToken)
            ->post(self::CALENDAR_API . '?conferenceDataVersion=1', [
                'summary'        => $title,
                'start'          => ['dateTime' => $start->toRfc3339String(), 'timeZone' => 'UTC'],
                'end'            => ['dateTime' => $end->toRfc3339String(),   'timeZone' => 'UTC'],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId'             => Str::uuid()->toString(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
            ]);

        if (! $resp->ok()) {
            throw new RuntimeException('Google Calendar API error: ' . $resp->body());
        }

        $data = $resp->json();

        // Prefer the video entry point (the Meet join URL)
        $joinUrl = collect($data['conferenceData']['entryPoints'] ?? [])
            ->firstWhere('entryPointType', 'video')['uri'] ?? null;

        // Fallback: hangoutLink on older responses
        $joinUrl ??= $data['hangoutLink'] ?? null;

        if (! $joinUrl) {
            throw new RuntimeException(
                'Google Meet link was not returned. '
                . 'Ensure the Google account supports Meet (Gmail or Workspace).'
            );
        }

        return [
            'join_url'  => $joinUrl,
            'event_id'  => $data['id'] ?? null,
            'event_url' => $data['htmlLink'] ?? null,
        ];
    }

    /**
     * Update title/time on an existing Calendar event (best-effort).
     */
    public function updateMeeting(
        User   $teacher,
        string $eventId,
        string $title,
        string $startIso,
        int    $durationMinutes,
    ): void {
        try {
            $accessToken = $this->resolveToken($teacher);

            $start = \Carbon\Carbon::parse($startIso)->utc();
            $end   = $start->copy()->addMinutes($durationMinutes);

            Http::withToken($accessToken)
                ->patch(self::CALENDAR_API . "/{$eventId}", [
                    'summary' => $title,
                    'start'   => ['dateTime' => $start->toRfc3339String(), 'timeZone' => 'UTC'],
                    'end'     => ['dateTime' => $end->toRfc3339String(),   'timeZone' => 'UTC'],
                ]);
        } catch (\Throwable) {
            // Non-fatal — keep the existing Meet link
        }
    }

    /**
     * Delete a Calendar event (best-effort — does not throw).
     */
    public function deleteEvent(User $teacher, string $eventId): void
    {
        try {
            $accessToken = $this->resolveToken($teacher);
            Http::withToken($accessToken)
                ->delete(self::CALENDAR_API . "/{$eventId}");
        } catch (\Throwable) {
            // Best-effort
        }
    }

    // ── Private helpers ─────────────────────────────────────────────────────

    /**
     * Retrieve a fresh access token for the teacher, verifying calendar scope.
     *
     * @throws RuntimeException
     */
    private function resolveToken(User $teacher): string
    {
        $token = GoogleToken::where('user_id', $teacher->id)->first();

        if (! $token) {
            throw new RuntimeException(
                'Connect your Google account first. '
                . 'Go to your dashboard and click "Connect Google / YouTube".'
            );
        }

        if (! str_contains((string) $token->scope, 'calendar')) {
            throw new RuntimeException(
                'Calendar access not yet granted. '
                . 'Please reconnect your Google account to enable automatic Meet creation.'
            );
        }

        return $this->youtube->freshAccessToken($token);
    }
}
