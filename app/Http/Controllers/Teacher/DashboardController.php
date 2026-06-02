<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\SettlementRequest;
use App\Models\TeacherPaymentItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $courses = $request->user()->courses()
            ->withCount(['enrollments', 'lessons'])
            ->get();

        // Total approved/paid earnings from sessions (TeacherPaymentItem)
        $userId = $request->user()->id;
        $earnings = TeacherPaymentItem::where('teacher_id', $userId)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('total_usd');

        // Pending (not yet approved) earnings
        $pendingEarnings = TeacherPaymentItem::where('teacher_id', $userId)
            ->where('status', 'pending')
            ->sum('total_usd');

        // Available balance (earned minus settled/in-flight)
        $totalSettled  = SettlementRequest::where('teacher_id', $userId)->where('status', 'paid')->sum('amount_usd');
        $inFlight      = SettlementRequest::where('teacher_id', $userId)->whereIn('status', ['pending', 'approved', 'processing'])->sum('amount_usd');
        $availableBalance = max(0, $earnings - $totalSettled - $inFlight);

        $upcoming = LiveSession::where('teacher_id', $request->user()->id)
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->with('course')
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        // Past sessions that need a log submitted
        $pastSessions = LiveSession::where('teacher_id', $request->user()->id)
            ->where('scheduled_at', '<', now())
            ->whereNotIn('status', ['cancelled'])
            ->with(['course', 'sessionLog', 'aiReport', 'paymentItem'])
            ->orderByDesc('scheduled_at')
            ->take(20)
            ->get();

        $pendingPayments = TeacherPaymentItem::where('teacher_id', $request->user()->id)
            ->where('status', 'approved')
            ->sum('total_usd');

        $totalStudents = $courses->sum('enrollments_count');

        return view('teacher.dashboard', compact(
            'courses', 'earnings', 'pendingEarnings', 'availableBalance',
            'upcoming', 'totalStudents', 'pastSessions', 'pendingPayments'
        ));
    }
}
