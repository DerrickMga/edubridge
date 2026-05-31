<?php

namespace App\Services;

use App\Models\LiveSession;
use App\Models\TeacherAvailability;
use App\Models\TeacherShift;
use App\Models\TeacherTimeOff;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Picks the best available teacher for a session/shift while load-balancing
 * weekly hours so that all teachers enrolled on a course get hours over time.
 */
class ShiftAssignmentService
{
    /**
     * Rank eligible teachers for a given time slot (and optionally a course).
     *
     * Returns a Collection keyed by user_id with shape:
     *   ['teacher' => User, 'weekly_hours' => float, 'score' => float, 'reasons' => [...]]
     */
    public function rankCandidates(CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $courseId = null): Collection
    {
        $candidates = $this->candidateTeachers($courseId);

        $dow = (int) $startsAt->dayOfWeek;
        $start = $startsAt->format('H:i:s');
        $end   = $endsAt->format('H:i:s');

        $weekStart = $startsAt->copy()->startOfWeek();
        $weekEnd   = $startsAt->copy()->endOfWeek();

        return $candidates->map(function (User $t) use ($startsAt, $endsAt, $dow, $start, $end, $weekStart, $weekEnd) {
            $reasons = [];
            $score = 100.0;

            // Hard filters
            if (! $t->is_active || ! $t->accepts_assignments) {
                return null;
            }

            // Time-off conflict
            $onLeave = TeacherTimeOff::where('teacher_id', $t->id)
                ->where('status', 'approved')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();
            if ($onLeave) return null;

            // Existing shift conflict
            $conflict = TeacherShift::where('teacher_id', $t->id)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();
            if ($conflict) return null;

            // Availability window match (+10 if matched, soft penalty if not)
            $availMatch = TeacherAvailability::where('teacher_id', $t->id)
                ->where('day_of_week', $dow)
                ->where('start_time', '<=', $start)
                ->where('end_time', '>=', $end)
                ->exists();

            if ($availMatch) {
                $score += 25;
                $reasons[] = 'within declared availability';
            } else {
                $score -= 15;
                $reasons[] = 'outside declared availability';
            }

            // Load balance — fewer hours this week wins
            $weeklyHours = (float) TeacherShift::where('teacher_id', $t->id)
                ->whereBetween('starts_at', [$weekStart, $weekEnd])
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->get()
                ->sum(fn (TeacherShift $s) => $s->duration_hours);

            // Penalise each existing hour this week
            $score -= $weeklyHours * 2.5;

            // Online preference (small bump)
            if ($t->isOnline()) {
                $score += 3;
                $reasons[] = 'online now';
            }

            return [
                'teacher'      => $t,
                'weekly_hours' => round($weeklyHours, 2),
                'score'        => round($score, 2),
                'reasons'      => $reasons,
            ];
        })->filter()->sortByDesc('score')->values();
    }

    public function bestCandidate(CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $courseId = null): ?User
    {
        $top = $this->rankCandidates($startsAt, $endsAt, $courseId)->first();
        return $top ? $top['teacher'] : null;
    }

    /**
     * Create a TeacherShift from a LiveSession, optionally auto-picking the teacher.
     */
    public function shiftFromLiveSession(LiveSession $session, ?int $teacherId = null, ?int $assignedBy = null, bool $auto = false): ?TeacherShift
    {
        $startsAt = $session->scheduled_at;
        $endsAt   = $session->scheduled_at->copy()->addMinutes((int) ($session->duration_minutes ?? 60));

        $teacherId = $teacherId ?? $session->teacher_id;

        if (! $teacherId && $auto) {
            $picked = $this->bestCandidate($startsAt, $endsAt, $session->course_id);
            if (! $picked) return null;
            $teacherId = $picked->id;
        }

        if (! $teacherId) return null;

        // Prevent duplicate shift for the same session+teacher
        $existing = TeacherShift::where('live_session_id', $session->id)
            ->where('teacher_id', $teacherId)
            ->first();
        if ($existing) return $existing;

        $teacher = User::find($teacherId);

        return TeacherShift::create([
            'teacher_id'                => $teacherId,
            'course_id'                 => $session->course_id,
            'live_session_id'           => $session->id,
            'title'                     => $session->title ?? 'Live session',
            'starts_at'                 => $startsAt,
            'ends_at'                   => $endsAt,
            'status'                    => 'scheduled',
            'auto_assigned'             => $auto,
            'hourly_rate_usd_snapshot'  => $teacher?->hourly_rate_usd,
            'assigned_by'               => $assignedBy,
        ]);
    }

    /**
     * Mark in-progress / completed / missed based on the wall clock and
     * (when applicable) the linked LiveSession.status.
     */
    public function closeOutDueShifts(): int
    {
        $now = now();
        $touched = 0;

        // Move scheduled→in_progress when window has started
        $touched += TeacherShift::where('status', 'scheduled')
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>', $now)
            ->update(['status' => 'in_progress']);

        // Close out shifts whose end has passed
        TeacherShift::whereIn('status', ['scheduled', 'in_progress'])
            ->where('ends_at', '<=', $now)
            ->with('liveSession')
            ->chunkById(100, function ($shifts) use (&$touched) {
                foreach ($shifts as $shift) {
                    $linked = $shift->liveSession;
                    $isMissed = $linked && $linked->status === 'cancelled';
                    $shift->status = $isMissed ? 'missed' : 'completed';
                    if (! $isMissed) {
                        $shift->hours_worked = $shift->hours_worked ?? $shift->duration_hours;
                        $shift->computePayout();
                    }
                    $shift->save();
                    $touched++;
                }
            });

        return $touched;
    }

    /**
     * Eligible teachers = users with role=teacher who are active, who accept
     * assignments, and (if a course id is given) who are tied to the course
     * either via courses.teacher_id or the course_teacher pivot.
     */
    protected function candidateTeachers(?int $courseId): Collection
    {
        $q = User::query()
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->where('accepts_assignments', true);

        if ($courseId) {
            $q->where(function ($q) use ($courseId) {
                $q->whereHas('taughtCourses', fn ($qq) => $qq->where('courses.id', $courseId))
                  ->orWhereHas('courses', fn ($qq) => $qq->where('courses.id', $courseId));
            });
        }

        return $q->get();
    }
}
