<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{StudentStat, User};

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaders = StudentStat::with('user')
            ->orderByDesc('xp')
            ->take(50)
            ->get();

        $myStat = StudentStat::firstOrCreate(['user_id' => auth()->id()]);
        $myRank = StudentStat::where('xp', '>', $myStat->xp ?? 0)->count() + 1;

        return view('student.leaderboard', compact('leaders', 'myRank', 'myStat'));
    }
}
