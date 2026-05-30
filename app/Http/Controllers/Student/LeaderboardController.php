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

        $myRank = StudentStat::where('xp', '>', auth()->user()->xp)->count() + 1;
        $myStat = StudentStat::firstOrCreate(['user_id' => auth()->id()]);

        return view('student.leaderboard', compact('leaders', 'myRank', 'myStat'));
    }
}
