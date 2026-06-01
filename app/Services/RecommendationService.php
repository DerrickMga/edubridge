<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * "Students who enrolled in this course also enrolled in…"
     * Returns top-N other courses by overlap count.
     */
    public function alsoEnrolled(Course $course, int $limit = 4): Collection
    {
        $userIds = Enrollment::where('course_id', $course->id)->pluck('user_id');
        if ($userIds->isEmpty()) {
            return $this->popularExcluding([$course->id], $limit);
        }

        $courseIds = Enrollment::whereIn('user_id', $userIds)
            ->where('course_id', '!=', $course->id)
            ->select('course_id', DB::raw('COUNT(*) as overlap'))
            ->groupBy('course_id')
            ->orderByDesc('overlap')
            ->limit($limit)
            ->pluck('course_id');

        if ($courseIds->isEmpty()) {
            return $this->popularExcluding([$course->id], $limit);
        }

        return Course::whereIn('id', $courseIds)->where('status', 'published')->get()
            ->sortBy(fn ($c) => $courseIds->search($c->id))->values();
    }

    /** Personalised for a logged-in user: subject overlap + popularity fallback. */
    public function forUser(int $userId, int $limit = 6): Collection
    {
        $enrolledIds = Enrollment::where('user_id', $userId)->pluck('course_id')->all();
        $subjects = Course::whereIn('id', $enrolledIds)->pluck('subject')->unique()->filter()->values();

        $q = Course::query()->where('status', 'published')->whereNotIn('id', $enrolledIds);
        if ($subjects->isNotEmpty()) {
            $q->whereIn('subject', $subjects);
        }
        $byTopic = $q->orderByDesc('average_rating')->orderByDesc('reviews_count')->limit($limit)->get();

        if ($byTopic->count() >= $limit) return $byTopic;

        $padding = $this->popularExcluding(array_merge($enrolledIds, $byTopic->pluck('id')->all()), $limit - $byTopic->count());
        return $byTopic->concat($padding);
    }

    private function popularExcluding(array $excludeIds, int $limit): Collection
    {
        return Course::where('status', 'published')
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->orderByDesc('reviews_count')->orderByDesc('average_rating')
            ->limit($limit)->get();
    }
}
