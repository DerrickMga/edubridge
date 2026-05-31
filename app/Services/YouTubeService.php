<?php

namespace App\Services;

use App\Models\GoogleToken;
use App\Models\Recording;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Minimal YouTube Data API v3 client.
 *
 * Uses OAuth 2.0 (Authorization Code with refresh token) and the
 * resumable upload protocol to push recordings to a channel without
 * pulling in the full google/apiclient dependency.
 */
class YouTubeService
{
    public const SCOPES = [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube.readonly',
    ];

    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected string $apiKey;

    public function __construct()
    {
        $this->clientId     = (string) config('services.google.client_id');
        $this->clientSecret = (string) config('services.google.client_secret');
        $this->redirectUri  = (string) config('services.google.redirect');
        $this->apiKey       = (string) config('services.google.api_key', '');
    }

    // ── Public data (API key) ──────────────────────────────────────────────

    /**
     * Extract a YouTube video ID from any youtube.com or youtu.be URL.
     * Returns null if the URL is not a recognisable YouTube link.
     */
    public static function extractVideoId(string $url): ?string
    {
        // youtu.be/<id>
        if (preg_match('#youtu\.be/([A-Za-z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }
        // youtube.com/watch?v=<id>  or  /embed/<id>  or  /v/<id>  or  /shorts/<id>
        if (preg_match('#(?:v=|embed/|/v/|/shorts/)([A-Za-z0-9_-]{11})#', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Fetch public metadata for a YouTube video using the server API key.
     *
     * Returns an array with keys: id, title, description, duration_seconds,
     * thumbnail_url, channel_title  — or null on failure / not configured.
     */
    public function fetchVideoDetails(string $videoIdOrUrl): ?array
    {
        $videoId = strlen($videoIdOrUrl) === 11
            ? $videoIdOrUrl
            : self::extractVideoId($videoIdOrUrl);

        if (! $videoId || $this->apiKey === '') {
            return null;
        }

        $resp = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'id'   => $videoId,
            'part' => 'snippet,contentDetails',
            'key'  => $this->apiKey,
        ]);

        if (! $resp->ok()) {
            Log::warning('YouTube fetchVideoDetails failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            return null;
        }

        $item = $resp->json('items.0');
        if (! $item) {
            return null;
        }

        $snippet        = $item['snippet'] ?? [];
        $contentDetails = $item['contentDetails'] ?? [];

        return [
            'id'               => $videoId,
            'title'            => $snippet['title'] ?? null,
            'description'      => $snippet['description'] ?? null,
            'thumbnail_url'    => $snippet['thumbnails']['high']['url']
                               ?? $snippet['thumbnails']['medium']['url']
                               ?? $snippet['thumbnails']['default']['url']
                               ?? null,
            'channel_title'    => $snippet['channelTitle'] ?? null,
            'published_at'     => $snippet['publishedAt'] ?? null,
            'duration_seconds' => self::iso8601DurationToSeconds($contentDetails['duration'] ?? ''),
            'embed_url'        => 'https://www.youtube.com/embed/' . $videoId,
            'watch_url'        => 'https://www.youtube.com/watch?v=' . $videoId,
        ];
    }

    /**
     * Convert ISO 8601 duration (e.g. PT1H23M45S) to total seconds.
     */
    public static function iso8601DurationToSeconds(string $duration): int
    {
        if (! preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $m)) {
            return 0;
        }
        return ((int)($m[1] ?? 0)) * 3600
             + ((int)($m[2] ?? 0)) * 60
             + ((int)($m[3] ?? 0));
    }

    // ── OAuth ──────────────────────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->clientSecret !== '' && $this->redirectUri !== '';
    }

    public function authorizationUrl(string $state): string
    {
        $params = http_build_query([
            'client_id'              => $this->clientId,
            'redirect_uri'           => $this->redirectUri,
            'response_type'          => 'code',
            'scope'                  => implode(' ', self::SCOPES),
            'access_type'            => 'offline',
            'include_granted_scopes' => 'true',
            'prompt'                 => 'consent',
            'state'                  => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.$params;
    }

    public function exchangeCode(string $code): array
    {
        $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        if (! $resp->ok()) {
            throw new RuntimeException('Google token exchange failed: '.$resp->body());
        }

        return $resp->json();
    }

    public function storeTokensForUser(User $user, array $tokens): GoogleToken
    {
        $token = GoogleToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token'  => $tokens['access_token'] ?? '',
                'refresh_token' => $tokens['refresh_token'] ?? optional(GoogleToken::where('user_id', $user->id)->first())->refresh_token,
                'scope'         => $tokens['scope'] ?? null,
                'expires_at'    => isset($tokens['expires_in'])
                    ? now()->addSeconds((int) $tokens['expires_in'] - 60)
                    : null,
            ]
        );

        // Best-effort: cache channel info
        try {
            $channel = $this->fetchOwnChannel($token);
            if ($channel) {
                $token->update([
                    'channel_id'    => $channel['id'] ?? null,
                    'channel_title' => $channel['snippet']['title'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('YouTube channel lookup failed', ['err' => $e->getMessage()]);
        }

        return $token->fresh();
    }

    public function freshAccessToken(GoogleToken $token): string
    {
        if (! $token->isExpired() && ! empty($token->access_token)) {
            return $token->access_token;
        }

        if (empty($token->refresh_token)) {
            throw new RuntimeException('No refresh token available. The user must re-authorise YouTube access.');
        }

        $resp = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $token->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $resp->ok()) {
            throw new RuntimeException('Refresh token failed: '.$resp->body());
        }

        $data = $resp->json();
        $token->update([
            'access_token' => $data['access_token'] ?? $token->access_token,
            'expires_at'   => isset($data['expires_in'])
                ? now()->addSeconds((int) $data['expires_in'] - 60)
                : null,
        ]);

        return $data['access_token'] ?? $token->access_token;
    }

    public function tokenForUser(User $user): ?GoogleToken
    {
        return GoogleToken::where('user_id', $user->id)->first();
    }

    protected function fetchOwnChannel(GoogleToken $token): ?array
    {
        $access = $this->freshAccessToken($token);

        $resp = Http::withToken($access)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'snippet',
            'mine' => 'true',
        ]);

        if (! $resp->ok()) {
            return null;
        }

        return $resp->json('items.0');
    }

    // ── Resumable upload ───────────────────────────────────────────────────

    /**
     * Upload a recording to YouTube via resumable upload protocol.
     *
     * Returns: ['video_id' => 'xxx', 'url' => 'https://youtu.be/xxx']
     */
    public function uploadRecording(Recording $recording): array
    {
        $teacher = $recording->teacher;
        $token   = $this->tokenForUser($teacher);
        if (! $token) {
            throw new RuntimeException('Teacher has not connected a YouTube channel.');
        }

        $access   = $this->freshAccessToken($token);
        $source   = $this->resolveSource($recording);
        $size     = $source['size'];
        $stream   = $source['stream'];
        $mime     = $source['mime'];

        try {
            $title       = mb_substr($recording->title, 0, 95);
            $description = $this->buildDescription($recording);
            $privacy     = $recording->youtube_privacy ?: 'unlisted';

            $metadata = [
                'snippet' => [
                    'title'       => $title,
                    'description' => $description,
                    'tags'        => array_filter([
                        'EduBridge',
                        optional($recording->course)->subject,
                        optional($recording->course)->grade_level,
                    ]),
                    'categoryId'  => '27', // Education
                ],
                'status'  => [
                    'privacyStatus' => $privacy,
                    'selfDeclaredMadeForKids' => false,
                    'embeddable'    => true,
                ],
            ];

            // Step 1: initiate resumable session
            $init = Http::withToken($access)
                ->withHeaders([
                    'X-Upload-Content-Length' => (string) $size,
                    'X-Upload-Content-Type'   => $mime,
                    'Content-Type'            => 'application/json; charset=UTF-8',
                ])
                ->withBody(json_encode($metadata), 'application/json')
                ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status');

            if (! $init->successful()) {
                throw new RuntimeException('YouTube resumable session init failed: '.$init->body());
            }

            $uploadUrl = $init->header('Location');
            if (! $uploadUrl) {
                throw new RuntimeException('YouTube did not return a resumable upload URL.');
            }

            // Step 2: stream the bytes in chunks (PUT). For simplicity we
            // send in a single PUT if size is reasonable; otherwise chunk.
            $body = stream_get_contents($stream);
            if ($body === false) {
                throw new RuntimeException('Could not read source stream for upload.');
            }

            $put = Http::withToken($access)
                ->timeout(60 * 30) // 30 min cap for big files
                ->withHeaders([
                    'Content-Length' => (string) strlen($body),
                    'Content-Type'   => $mime,
                ])
                ->withBody($body, $mime)
                ->put($uploadUrl);

            if (! $put->successful()) {
                throw new RuntimeException('YouTube upload PUT failed ('.$put->status().'): '.$put->body());
            }

            $videoId = $put->json('id');
            if (! $videoId) {
                throw new RuntimeException('YouTube upload returned no video id: '.$put->body());
            }

            return [
                'video_id' => $videoId,
                'url'      => 'https://youtu.be/'.$videoId,
            ];
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
            if (! empty($source['tmp']) && is_file($source['tmp'])) {
                @unlink($source['tmp']);
            }
        }
    }

    /**
     * Resolve the source bytes for a recording — either local/S3 stored path
     * or an external URL we need to download first (e.g. Zoom).
     *
     * Returns ['stream' => resource, 'size' => int, 'mime' => string, 'tmp' => ?string]
     */
    protected function resolveSource(Recording $recording): array
    {
        if ($recording->storage_path) {
            $disk = config('filesystems.default');
            $size = Storage::disk($disk)->size($recording->storage_path);
            $stream = Storage::disk($disk)->readStream($recording->storage_path);
            if (! $stream) {
                throw new RuntimeException('Could not open recording stream from storage.');
            }
            return [
                'stream' => $stream,
                'size'   => $size,
                'mime'   => Storage::disk($disk)->mimeType($recording->storage_path) ?: 'video/mp4',
                'tmp'    => null,
            ];
        }

        if (! $recording->external_url) {
            throw new RuntimeException('Recording has no source to upload.');
        }

        // Download to a temp file. For Zoom download_url we need a bearer; we
        // accept either a publicly downloadable URL or, if it looks like Zoom,
        // add the Zoom access token.
        $tmp = tempnam(sys_get_temp_dir(), 'rec_');
        $req = Http::sink($tmp)->timeout(60 * 20);

        if (str_contains($recording->external_url, 'zoom.us')) {
            $req = $req->withToken(app(ZoomService::class)->accessToken());
        }

        $resp = $req->get($recording->external_url);
        if (! $resp->successful()) {
            @unlink($tmp);
            throw new RuntimeException('Failed to download recording bytes: HTTP '.$resp->status());
        }

        $size = filesize($tmp) ?: 0;
        if ($size <= 0) {
            @unlink($tmp);
            throw new RuntimeException('Downloaded recording is empty.');
        }

        $stream = fopen($tmp, 'rb');
        if (! $stream) {
            @unlink($tmp);
            throw new RuntimeException('Could not open downloaded recording.');
        }

        return [
            'stream' => $stream,
            'size'   => $size,
            'mime'   => $resp->header('Content-Type') ?: 'video/mp4',
            'tmp'    => $tmp,
        ];
    }

    protected function buildDescription(Recording $recording): string
    {
        $course = $recording->course;
        $parts  = [
            $recording->title,
            $course ? "Course: {$course->title}" : null,
            $course?->subject ? "Subject: {$course->subject}" : null,
            $course?->grade_level ? "Level: {$course->grade_level}" : null,
            "Recorded on EduBridge — https://edubridge.co.zw",
        ];

        return trim(implode("\n", array_filter($parts)));
    }
}
