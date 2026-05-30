<?php
namespace App\Services;

use App\Models\{User, Badge, StudentStat, XpEvent};
use Illuminate\Support\Facades\DB;

class GamificationService
{
    // ── XP values ─────────────────────────────────────────────────────────
    const XP_LESSON_COMPLETE  = 10;
    const XP_QUIZ_PASS        = 20;
    const XP_QUIZ_PERFECT     = 15; // bonus on top of XP_QUIZ_PASS
    const XP_QUIZ_FIRST_PASS  = 5;  // bonus for passing on first attempt
    const XP_COURSE_COMPLETE  = 50;
    const XP_STREAK_DAILY     = 5;

    // ── Award XP ──────────────────────────────────────────────────────────
    public function awardXp(User $student, string $eventType, int $xp, string $description, $reference = null): StudentStat
    {
        return DB::transaction(function () use ($student, $eventType, $xp, $description, $reference) {
            // Log event
            $event = XpEvent::create([
                'user_id'        => $student->id,
                'event_type'     => $eventType,
                'xp'             => $xp,
                'description'    => $description,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference?->id,
            ]);

            // Update or create stats
            $stat = StudentStat::firstOrCreate(['user_id' => $student->id]);
            $stat->xp    += $xp;
            $stat->level  = StudentStat::levelFromXp($stat->xp);
            $stat->save();

            // Check badges after XP update
            $this->checkBadges($student, $stat->fresh());

            return $stat->fresh();
        });
    }

    // ── Update daily streak ───────────────────────────────────────────────
    public function touchStreak(User $student): void
    {
        $stat  = StudentStat::firstOrCreate(['user_id' => $student->id]);
        $today = today();
        $last  = $stat->last_active_date;

        if ($last && $last->eq($today)) return; // already counted today

        if ($last && $last->diffInDays($today) === 1) {
            $stat->streak_days++;
        } else {
            $stat->streak_days = 1; // reset
        }

        $stat->longest_streak  = max($stat->longest_streak, $stat->streak_days);
        $stat->last_active_date = $today;
        $stat->save();

        // Award streak XP (once per day)
        $this->awardXp($student, 'streak', self::XP_STREAK_DAILY,
            "Day {$stat->streak_days} learning streak 🔥");
    }

    // ── Lesson completed ──────────────────────────────────────────────────
    public function onLessonComplete(User $student, $lesson): StudentStat
    {
        $this->touchStreak($student);
        return $this->awardXp($student, 'lesson_complete', self::XP_LESSON_COMPLETE,
            "Completed lesson: {$lesson->title}", $lesson);
    }

    // ── Quiz attempt ──────────────────────────────────────────────────────
    public function onQuizAttempt(User $student, $quiz, $attempt): ?StudentStat
    {
        if (!$attempt->passed) return null;

        $xp   = self::XP_QUIZ_PASS;
        $desc = "Passed quiz: {$quiz->title}";

        if ($attempt->score_percentage === 100) {
            $xp   += self::XP_QUIZ_PERFECT;
            $desc .= ' (Perfect score! 💯)';
        }

        if ($attempt->attempt_number === 1) {
            $xp   += self::XP_QUIZ_FIRST_PASS;
            $desc .= ' (First attempt!)';
        }

        $this->touchStreak($student);
        return $this->awardXp($student, 'quiz_pass', $xp, $desc, $quiz);
    }

    // ── Course completed ──────────────────────────────────────────────────
    public function onCourseComplete(User $student, $course): StudentStat
    {
        return $this->awardXp($student, 'course_complete', self::XP_COURSE_COMPLETE,
            "Completed course: {$course->title} 🎓", $course);
    }

    // ── Badge checking ────────────────────────────────────────────────────
    public function checkBadges(User $student, StudentStat $stat): void
    {
        $earned = $student->badges()->pluck('slug')->toArray();

        foreach (Badge::all() as $badge) {
            if (in_array($badge->slug, $earned)) continue;

            if ($this->meetsBadgeCondition($student, $stat, $badge->slug)) {
                $student->badges()->attach($badge->id, ['earned_at' => now()]);
            }
        }
    }

    private function meetsBadgeCondition(User $student, StudentStat $stat, string $slug): bool
    {
        return match($slug) {
            'first-lesson'    => $student->xpEvents()->where('event_type', 'lesson_complete')->count() >= 1,
            'bookworm'        => $student->xpEvents()->where('event_type', 'lesson_complete')->count() >= 10,
            'scholar'         => $student->xpEvents()->where('event_type', 'lesson_complete')->count() >= 25,
            'streak-3'        => $stat->streak_days >= 3 || $stat->longest_streak >= 3,
            'streak-7'        => $stat->streak_days >= 7 || $stat->longest_streak >= 7,
            'quiz-pass'       => $student->xpEvents()->where('event_type', 'quiz_pass')->count() >= 1,
            'quiz-perfect'    => $student->xpEvents()->where('event_type', 'quiz_pass')
                                         ->where('description', 'like', '%Perfect score%')->count() >= 1,
            'quiz-master'     => $student->xpEvents()->where('event_type', 'quiz_pass')->count() >= 5,
            'course-complete' => $student->xpEvents()->where('event_type', 'course_complete')->count() >= 1,
            'xp-100'          => $stat->xp >= 100,
            'xp-500'          => $stat->xp >= 500,
            'xp-1000'         => $stat->xp >= 1000,
            default           => false,
        };
    }
}
