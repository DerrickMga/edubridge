<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * YouTubeSearchService — searches YouTube Data API v3 for educational videos.
 *
 * Quality pipeline:
 *  1. Fetch up to 4× the requested results
 *  2. Strip titles containing garbage keywords
 *  3. Fetch contentDetails + statistics for the candidates
 *  4. Filter out YouTube Shorts (< 90 s) and extremely long videos (> 3 h)
 *  5. Score by log10(views) + bonus for educational channel keywords
 *  6. Return the top N scored results
 */
class YouTubeSearchService
{
    private string $apiKey;
    private string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    /** Title substrings that signal non-educational content (case-insensitive) */
    private const TITLE_BLOCKLIST = [
        'reaction', 'reacts to', 'vlog', 'unboxing', 'gaming', 'funny',
        'prank', 'challenge', '#shorts', 'clickbait', 'tiktok', 'compilation',
        'asmr', 'mukbang', 'roast', 'try not to', 'i tried',
    ];

    /** Channel name substrings that boost educational score */
    private const CHANNEL_BOOST = [
        'math', 'maths', 'science', 'learn', 'teach', 'tutor', 'education',
        'academy', 'school', 'class', 'lecture', 'study', 'explain', 'khan',
        'professor', 'dr.', 'dr ', 'physics', 'chemistry', 'biology',
        'english', 'economics', 'geography', 'history',
    ];

    public function __construct()
    {
        $this->apiKey = (string) config('services.google.api_key', '');
    }

    /**
     * Search YouTube and return quality-filtered, scored results.
     *
     * @param  int  $maxResults  How many results to return (after filtering)
     */
    public function search(string $query, int $maxResults = 3): array
    {
        if (empty($this->apiKey)) {
            Log::warning('YouTubeSearchService: GOOGLE_API_KEY not configured.');
            return [];
        }

        try {
            // Fetch a larger pool so we have room to filter down
            $fetchCount = min($maxResults * 4, 15);

            $searchRes = Http::timeout(12)->get($this->baseUrl . '/search', [
                'key'               => $this->apiKey,
                'q'                 => $query,
                'part'              => 'snippet',
                'type'              => 'video',
                'maxResults'        => $fetchCount,
                'safeSearch'        => 'moderate',
                'relevanceLanguage' => 'en',
                'videoEmbeddable'   => 'true',
                'videoDuration'     => 'medium', // 4-20 min — broad but filters sub-minute Shorts
            ]);

            if ($searchRes->failed()) {
                Log::warning('YouTubeSearchService: search failed', [
                    'status' => $searchRes->status(),
                    'query'  => $query,
                ]);
                return [];
            }

            $items = collect($searchRes->json('items', []))
                ->filter(fn($item) => ! empty(data_get($item, 'id.videoId')))
                ->filter(fn($item) => $this->titleIsClean(data_get($item, 'snippet.title', '')))
                ->values();

            if ($items->isEmpty()) {
                return [];
            }

            // Fetch duration + view count for the candidates
            $videoIds   = $items->pluck('id.videoId')->take(12)->implode(',');
            $detailsMap = $this->fetchVideoDetails($videoIds);

            // Score, filter by duration, and rank
            $scored = $items
                ->map(fn($item) => $this->scoreItem($item, $detailsMap))
                ->filter(fn($v) => $v !== null)               // null = filtered out by duration
                ->sortByDesc('score')
                ->values()
                ->take($maxResults)
                ->map(fn($v) => [
                    'videoId'   => $v['videoId'],
                    'title'     => $v['title'],
                    'channel'   => $v['channel'],
                    'thumbnail' => $v['thumbnail'],
                    'url'       => $v['url'],
                    'published' => $v['published'],
                ])
                ->values()
                ->toArray();

            return $scored;

        } catch (\Throwable $e) {
            Log::error('YouTubeSearchService: exception', ['message' => $e->getMessage(), 'query' => $query]);
            return [];
        }
    }

    /**
     * Resolve multiple {query, purpose} objects — used by the study plan generator.
     *
     * @param  array<array{query:string,purpose:string}>  $queries
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

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function titleIsClean(string $title): bool
    {
        $lower = strtolower($title);
        foreach (self::TITLE_BLOCKLIST as $bad) {
            if (str_contains($lower, $bad)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetch contentDetails and statistics for a comma-separated list of video IDs.
     * Returns a map of videoId => details array.
     */
    private function fetchVideoDetails(string $videoIds): array
    {
        try {
            $res = Http::timeout(10)->get($this->baseUrl . '/videos', [
                'key'  => $this->apiKey,
                'id'   => $videoIds,
                'part' => 'contentDetails,statistics',
            ]);

            if ($res->failed()) {
                return [];
            }

            $map = [];
            foreach ($res->json('items', []) as $d) {
                $map[$d['id']] = $d;
            }
            return $map;

        } catch (\Throwable $e) {
            Log::warning('YouTubeSearchService: fetchVideoDetails failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Score a single search item using its metadata + video details.
     * Returns null if the video should be excluded.
     */
    private function scoreItem(array $item, array $detailsMap): ?array
    {
        $vid     = data_get($item, 'id.videoId', '');
        $details = $detailsMap[$vid] ?? null;

        // Parse duration (ISO 8601 → seconds)
        $duration = $this->parseDuration(data_get($details, 'contentDetails.duration', 'PT0S'));

        // Exclude Shorts (< 90 s) and anything over 3 hours
        if ($duration > 0 && ($duration < 90 || $duration > 10800)) {
            return null;
        }

        $views   = (int) data_get($details, 'statistics.viewCount', 0);
        $channel = data_get($item, 'snippet.channelTitle', '');
        $chanLow = strtolower($channel);

        // Base score: logarithmic view count (handles orders-of-magnitude differences gracefully)
        $score = log10(max($views, 100));

        // Educational channel bonus
        foreach (self::CHANNEL_BOOST as $keyword) {
            if (str_contains($chanLow, $keyword)) {
                $score += 2.5;
                break;
            }
        }

        // Recent content slight bonus (published after 2018)
        $published = data_get($item, 'snippet.publishedAt', '');
        if ($published && substr($published, 0, 4) >= '2018') {
            $score += 0.5;
        }

        return [
            'score'     => $score,
            'videoId'   => $vid,
            'title'     => data_get($item, 'snippet.title', ''),
            'channel'   => $channel,
            'thumbnail' => data_get($item, 'snippet.thumbnails.medium.url', ''),
            'url'       => 'https://www.youtube.com/watch?v=' . $vid,
            'published' => $published,
        ];
    }

    /**
     * Parse an ISO 8601 duration string (PT4M30S) into seconds.
     */
    private function parseDuration(string $iso8601): int
    {
        if (! preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $iso8601, $m)) {
            return 0;
        }
        return (int) ($m[1] ?? 0) * 3600
             + (int) ($m[2] ?? 0) * 60
             + (int) ($m[3] ?? 0);
    }
}
