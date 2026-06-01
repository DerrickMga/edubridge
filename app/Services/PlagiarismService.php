<?php

namespace App\Services;

use App\Models\AssignmentSimilarityFlag;
use App\Models\AssignmentSubmission;

class PlagiarismService
{
    /** Threshold (0..100) above which we record a flag. */
    public const THRESHOLD = 70.0;

    /**
     * Compare a submission's text content against every other submission for the same assignment,
     * record similarity flag rows where similarity >= THRESHOLD. Idempotent (uses unique pair).
     *
     * Returns array of flags (each: [id, similarity_score]).
     */
    public function checkSubmission(AssignmentSubmission $submission): array
    {
        if (! $submission->content) return [];

        $textA = $this->normalize($submission->content);
        $tokensA = $this->shingles($textA);
        if (count($tokensA) < 6) return [];

        $peers = AssignmentSubmission::where('assignment_id', $submission->assignment_id)
            ->where('id', '!=', $submission->id)
            ->whereNotNull('content')
            ->get(['id', 'content']);

        $created = [];
        foreach ($peers as $peer) {
            $tokensB = $this->shingles($this->normalize($peer->content));
            if (count($tokensB) < 6) continue;
            $score = $this->jaccard($tokensA, $tokensB) * 100;
            if ($score >= self::THRESHOLD) {
                $flag = AssignmentSimilarityFlag::updateOrCreate(
                    [
                        'submission_id' => $submission->id,
                        'compared_submission_id' => $peer->id,
                    ],
                    ['similarity_score' => round($score, 2)]
                );
                $created[] = ['id' => $flag->id, 'peer_id' => $peer->id, 'score' => round($score, 2)];
            }
        }
        return $created;
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(strip_tags($s));
        return preg_replace('/[^a-z0-9\s]/u', ' ', $s);
    }

    /** 5-word shingles for fuzzy similarity. */
    private function shingles(string $text, int $k = 5): array
    {
        $words = preg_split('/\s+/', trim($text));
        if (count($words) < $k) return [];
        $out = [];
        for ($i = 0; $i <= count($words) - $k; $i++) {
            $out[] = implode(' ', array_slice($words, $i, $k));
        }
        return array_unique($out);
    }

    private function jaccard(array $a, array $b): float
    {
        $setA = array_flip($a);
        $setB = array_flip($b);
        $intersect = count(array_intersect_key($setA, $setB));
        $union = count($setA) + count($setB) - $intersect;
        return $union > 0 ? $intersect / $union : 0.0;
    }
}
