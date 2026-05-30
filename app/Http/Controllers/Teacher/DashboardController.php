<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $courses = $request->user()->courses()
            ->withCount(['enrollments', 'lessons'])
            ->get();

        $earnings = $request->user()->payments()
            ->where('status', 'paid')
            ->sum('amount');

        $upcoming = LiveSession::where('teacher_id', $request->user()->id)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->with('course')
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        $totalStudents = $courses->sum('enrollments_count');

        return view('teacher.dashboard', compact('courses', 'earnings', 'upcoming', 'totalStudents'));
    }
}
