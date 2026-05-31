<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ZoomService
{
    private string $accountId;
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl = 'https://api.zoom.us/v2';
    private string $tokenUrl = 'https://zoom.us/oauth/token';

    public function __construct()
    {
        $this->accountId    = config('services.zoom.account_id');
        $this->clientId     = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
    }

    // ── Auth ────────────────────────────────────────────────────────────────

    /**
     * Get a valid S2S OAuth bearer token, cached for 55 minutes.
     */
    public function accessToken(): string
    {
        return Cache::remember('zoom_access_token', 55 * 60, function () {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post($this->tokenUrl, [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId,
                ]);

            if ($response->failed()) {
                throw new RuntimeException('Zoom token request failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    // ── Meetings ─────────────────────────────────────────────────────────────

    /**
     * Create a Zoom meeting and return [meeting_id, join_url, start_url].
     *
     * @param  string   $topic         Meeting title
     * @param  string   $startTime     ISO-8601 datetime (UTC)  e.g. "2026-06-01T10:00:00Z"
     * @param  int      $durationMins  Duration in minutes
     * @param  string   $timezone      e.g. "Africa/Harare"
     * @return array{meeting_id: string, join_url: string, start_url: string}
     */
    public function createMeeting(
        string $topic,
        string $startTime,
        int    $durationMins,
        string $timezone = 'Africa/Harare'
    ): array {
        $response = Http::withToken($this->accessToken())
            ->post("{$this->baseUrl}/users/me/meetings", [
                'topic'      => $topic,
                'type'       => 2,                 // Scheduled meeting
                'start_time' => $startTime,
                'duration'   => $durationMins,
                'timezone'   => $timezone,
                'settings'   => [
                    'host_video'        => true,
                    'participant_video'  => true,
                    'join_before_host'  => false,
                    'waiting_room'      => true,
                    'auto_recording'    => 'cloud',  // auto-record to Zoom cloud
                    'mute_upon_entry'   => true,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Zoom createMeeting failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'meeting_id' => (string) $data['id'],
            'join_url'   => $data['join_url'],
            'start_url'  => $data['start_url'],
        ];
    }

    /**
     * Update a scheduled meeting's time/duration/title.
     */
    public function updateMeeting(
        string $meetingId,
        string $topic,
        string $startTime,
        int    $durationMins,
        string $timezone = 'Africa/Harare'
    ): void {
        $response = Http::withToken($this->accessToken())
            ->patch("{$this->baseUrl}/meetings/{$meetingId}", [
                'topic'      => $topic,
                'start_time' => $startTime,
                'duration'   => $durationMins,
                'timezone'   => $timezone,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Zoom updateMeeting failed: ' . $response->body());
        }
    }

    /**
     * Delete a scheduled Zoom meeting.
     */
    public function deleteMeeting(string $meetingId): void
    {
        Http::withToken($this->accessToken())
            ->delete("{$this->baseUrl}/meetings/{$meetingId}");
        // Ignore 404 — meeting may already be gone
    }

    // ── Recordings ───────────────────────────────────────────────────────────

    /**
     * Get cloud recording download URL for a meeting.
     * Returns null if recordings aren't available yet.
     */
    public function getRecordingUrl(string $meetingId): ?string
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl}/meetings/{$meetingId}/recordings");

        if ($response->failed()) {
            return null;
        }

        $files = $response->json('recording_files', []);

        // Prefer MP4 shared view recording
        foreach ($files as $file) {
            if (($file['file_type'] ?? '') === 'MP4' && ($file['status'] ?? '') === 'completed') {
                return $file['play_url'] ?? $file['download_url'] ?? null;
            }
        }

        return null;
    }

    /**
     * Fetch the plain-text transcript (VTT file) for a cloud-recorded meeting.
     * Returns the raw text content, or null if no transcript is available yet.
     */
    public function getTranscript(string $meetingId): ?string
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl}/meetings/{$meetingId}/recordings");

        if ($response->failed()) {
            return null;
        }

        $files = $response->json('recording_files', []);

        foreach ($files as $file) {
            if (($file['file_type'] ?? '') === 'TRANSCRIPT' && ($file['status'] ?? '') === 'completed') {
                $downloadUrl = $file['download_url'] ?? null;
                if (! $downloadUrl) {
                    continue;
                }
                // Zoom requires the access token on transcript download requests
                $text = Http::withToken($this->accessToken())
                    ->get($downloadUrl)
                    ->body();
                return $text ?: null;
            }
        }

        return null;
    }

    /**
     * Get participant list for a past meeting.
     * Returns an array of participant records.
     */
    public function getMeetingParticipants(string $meetingId): array
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl}/past_meetings/{$meetingId}/participants", [
                'page_size' => 300,
            ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json('participants', []);
    }

    /**
     * Get all cloud recording files for a meeting.
     *
     * Returns an array with keys:
     *   uuid, meeting_id, topic, start_time, duration (minutes),
     *   total_size (bytes), recording_files (array of file records)
     *
     * Each recording_file record contains:
     *   id, file_type (MP4|M4A|TIMELINE|TRANSCRIPT|CHAT|CC|CSV),
     *   file_size, play_url, download_url, status, recording_start, recording_end
     */
    public function getRecordingFiles(string $meetingId): array
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl}/meetings/{$meetingId}/recordings");

        if ($response->failed()) {
            return [];
        }

        return [
            'uuid'            => $response->json('uuid'),
            'meeting_id'      => $response->json('id'),
            'topic'           => $response->json('topic'),
            'start_time'      => $response->json('start_time'),
            'duration'        => $response->json('duration'),   // minutes
            'total_size'      => $response->json('total_size'), // bytes
            'recording_files' => $response->json('recording_files', []),
        ];
    }

    /**
     * Get scheduled or past meeting details.
     *
     * Returns the raw Zoom API response including: id, uuid, topic,
     * start_time, duration, status, created_at, settings, etc.
     * Returns an empty array if the meeting is not found.
     */
    public function getMeetingDetails(string $meetingId): array
    {
        $response = Http::withToken($this->accessToken())
            ->get("{$this->baseUrl}/meetings/{$meetingId}");

        if ($response->failed()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * Delete a cloud recording for a meeting.
     * $action: 'trash' (default, recoverable) or 'delete' (permanent).
     */
    public function deleteCloudRecording(string $meetingId, string $action = 'trash'): void
    {
        Http::withToken($this->accessToken())
            ->delete("{$this->baseUrl}/meetings/{$meetingId}/recordings", [
                'action' => $action,
            ]);
        // Non-fatal — ignore errors
    }
}

