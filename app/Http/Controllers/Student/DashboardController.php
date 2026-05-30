<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{LiveSession, StudentStat, Badge};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        $enrollments = $student->enrollments()
            ->with([
                'lessons' => fn ($q) => $q->where('status', 'published')->orderBy('order'),
                'teacher',
            ])
            ->get();

        $upcomingSessions = LiveSession::whereIn('course_id', $enrollments->pluck('id'))
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->with('course')
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        $stat         = StudentStat::firstOrCreate(['user_id' => $student->id]);
        $recentBadges = $student->badges()->orderByPivot('earned_at', 'desc')->take(4)->get();
        $rank         = StudentStat::where('xp', '>', $stat->xp ?? 0)->count() + 1;

        return view('student.dashboard', compact(
            'enrollments', 'upcomingSessions', 'stat', 'recentBadges', 'rank'
        ));
    }
}
