<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class LessonTrackerController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();

        // All courses this teacher is responsible for (primary or co-teacher)
        $courses = Course::where(function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id)
              ->orWhereHas('teachers', fn ($sq) => $sq->where('users.id', $teacher->id));
        })
        ->withCount(['lessons', 'enrollments'])
        ->orderBy('title')
        ->get();

        // All live sessions for this teacher
        $allSessions = LiveSession::where('teacher_id', $teacher->id)
            ->with(['course', 'sessionLog', 'paymentItem', 'attendances'])
            ->withCount('attendances')
            ->orderByDesc('scheduled_at')
            ->get();

        // Filter by course if requested
        $courseFilter = $request->input('course');
        $sessions = $courseFilter
            ? $allSessions->where('course_id', (int) $courseFilter)->values()
            : $allSessions;

        // Summary stats (always across all sessions)
        $now = now();
        $stats = [
            'total_sessions'   => $allSessions->count(),
            'completed'        => $allSessions->where('status', 'completed')->count(),
            'upcoming'         => $allSessions->where('status', 'scheduled')->filter(fn ($s) => $s->scheduled_at >= $now)->count(),
            'unlogged'         => $allSessions->filter(fn ($s) => $s->scheduled_at < $now && $s->status !== 'cancelled' && ! $s->sessionLog)->count(),
            'total_attendance' => $allSessions->sum('attendances_count'),
            'total_hours'      => round($allSessions->filter(fn ($s) => $s->sessionLog)->sum(fn ($s) => $s->sessionLog->actual_duration_minutes / 60), 1),
        ];

        // Per-course completion breakdown for the sidebar summary
        $courseStats = $courses->map(function ($c) use ($allSessions) {
            $cs = $allSessions->where('course_id', $c->id);
            $total = $cs->count();
            $done  = $cs->where('status', 'completed')->count();
            return [
                'course'           => $c,
                'sessions_total'   => $total,
                'sessions_done'    => $done,
                'pct_done'         => $total > 0 ? round(($done / $total) * 100) : 0,
                'total_attendance' => $cs->sum('attendances_count'),
                'unlogged'         => $cs->filter(fn ($s) => $s->scheduled_at < now() && $s->status !== 'cancelled' && ! $s->sessionLog)->count(),
            ];
        });

        return view('teacher.lessons.tracker', compact(
            'courses', 'sessions', 'allSessions', 'stats', 'courseStats', 'courseFilter'
        ));
    }

    /** PATCH: update teacher notes on a live session. */
    public function updateNotes(Request $request, LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== $request->user()->id, 403);
        $data = $request->validate(['teacher_notes' => 'nullable|string|max:2000']);
        $liveSession->update(['teacher_notes' => $data['teacher_notes'] ?? null]);
        return back()->with('success', 'Session notes updated.');
    }

    /** PATCH: mark a live session as completed. */
    public function markComplete(Request $request, LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== $request->user()->id, 403);
        abort_if(in_array($liveSession->status, ['completed', 'cancelled']), 422, 'Session already finalised.');
        $liveSession->update(['status' => 'completed']);
        return back()->with('success', 'Session marked as completed.');
    }
}
