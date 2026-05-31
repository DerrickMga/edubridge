<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Course, Payment, User, Conversation, Message};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $week  = Carbon::now()->subDays(7);
        $month = Carbon::now()->subDays(30);

        // Core KPIs
        $studentCount   = User::where('role', 'student')->count();
        $teacherCount   = User::where('role', 'teacher')->count();
        $courseCount    = Course::count();
        $publishedCount = Course::where('status', 'published')->count();
        $totalRevenue   = Payment::where('status', 'paid')->sum('amount');
        $monthRevenue   = Payment::where('status', 'paid')->where('created_at', '>=', $month)->sum('amount');

        // Enrollments
        $totalEnrollments = DB::table('enrollments')->count();
        $weekEnrollments  = DB::table('enrollments')->where('created_at', '>=', $week)->count();

        // AI Usage
        $totalConversations = Conversation::count();
        $weekConversations  = Conversation::where('created_at', '>=', $week)->count();
        $totalMessages      = Message::count();
        $weekMessages       = Message::where('created_at', '>=', $week)->count();

        // AI model breakdown (last 30 days)
        $aiModelStats = Message::where('role', 'assistant')
            ->where('created_at', '>=', $month)
            ->select('model_used', DB::raw('count(*) as total'))
            ->groupBy('model_used')
            ->pluck('total', 'model_used')
            ->toArray();

        // New users this week
        $newUsersWeek = User::where('created_at', '>=', $week)->count();

        // Revenue last 6 months (for chart)
        $revenueChart = Payment::where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartLabels = [];
        $chartData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $chartLabels[] = Carbon::now()->subMonths($i)->format('M Y');
            $chartData[]   = round($revenueChart[$key] ?? 0, 2);
        }

        // Top 5 courses by enrollment
        $topCourses = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get();

        // Student leaderboard (top XP)
        $leaderboard = DB::table('student_stats')
            ->join('users', 'users.id', '=', 'student_stats.user_id')
            ->select('users.name', 'student_stats.xp', 'student_stats.level', 'student_stats.streak_days')
            ->orderByDesc('student_stats.xp')
            ->take(5)
            ->get();

        // Pending teacher payments
        $pendingPayments = DB::table('teacher_payment_items')
            ->where('status', 'pending')
            ->count();

        $recentUsers    = User::latest()->take(8)->get();
        $recentPayments = Payment::with(['user', 'course'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'studentCount', 'teacherCount', 'courseCount', 'publishedCount',
            'totalRevenue', 'monthRevenue',
            'totalEnrollments', 'weekEnrollments',
            'totalConversations', 'weekConversations',
            'totalMessages', 'weekMessages',
            'aiModelStats', 'newUsersWeek',
            'chartLabels', 'chartData',
            'topCourses', 'leaderboard',
            'pendingPayments',
            'recentUsers', 'recentPayments'
        ));
    }
}
