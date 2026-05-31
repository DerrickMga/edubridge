<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * YouTubeSearchService — searches YouTube Data API v3 for educational videos.
 *
 * Uses the GOOGLE_API_KEY (server-side API key), not OAuth.
 * Returns a cleaned array of { videoId, title, channel, thumbnail, url, duration_hint }.
 */
class YouTubeSearchService
{
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = (string) config('services.google.api_key', '');
    }

    /**
     * Search YouTube for educational videos matching a query.
     *
     * @param  string  $query      Search string (e.g. "ZIMSEC O-Level Algebra quadratic equations")
     * @param  int     $maxResults Maximum number of results to return (1–10)
     * @return array<array{videoId:string,title:string,channel:string,thumbnail:string,url:string}>
     */
    public function search(string $query, int $maxResults = 3): array
    {
        if (empty($this->apiKey)) {
            Log::warning('YouTubeSearchService: GOOGLE_API_KEY not configured.');
            return [];
        }

        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/search', [
                'key'         => $this->apiKey,
                'q'           => $query,
                'part'        => 'snippet',
                'type'        => 'video',
                'maxResults'  => min($maxResults, 10),
                'safeSearch'  => 'moderate',
                'relevanceLanguage' => 'en',
            ]);

            if ($response->failed()) {
                Log::warning('YouTubeSearchService: search failed', [
                    'status' => $response->status(),
                    'query'  => $query,
                ]);
                return [];
            }

            $items = $response->json('items', []);

            return collect($items)->map(fn($item) => [
                'videoId'   => data_get($item, 'id.videoId', ''),
                'title'     => data_get($item, 'snippet.title', ''),
                'channel'   => data_get($item, 'snippet.channelTitle', ''),
                'thumbnail' => data_get($item, 'snippet.thumbnails.medium.url', ''),
                'url'       => 'https://www.youtube.com/watch?v=' . data_get($item, 'id.videoId', ''),
                'published' => data_get($item, 'snippet.publishedAt', ''),
            ])->filter(fn($v) => ! empty($v['videoId']))->values()->toArray();

        } catch (\Throwable $e) {
            Log::error('YouTubeSearchService: exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Resolve multiple queries at once (for study plans).
     * Returns a map of query => results[].
     *
     * @param  array<array{query:string,purpose:string}>  $queries
     * @return array
     */
    public function resolveQueries(array $queries): array
    {
        $resolved = [];

        foreach ($queries as $item) {
            $q = is_array($item) ? ($item['query'] ?? '') : (string) $item;
            if (! $q) continue;

            $results = $this->search($q, 2);

            $resolved[] = array_merge(
                is_array($item) ? $item : ['query' => $q, 'purpose' => ''],
                ['videos' => $results]
            );
        }

        return $resolved;
    }
}
