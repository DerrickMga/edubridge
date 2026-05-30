<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $enrollments = $request->user()->enrollments()
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

        return view('student.dashboard', compact('enrollments', 'upcomingSessions'));
    }
}
