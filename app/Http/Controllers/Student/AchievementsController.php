<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Badge, StudentStat, XpEvent};

class AchievementsController extends Controller
{
    public function index()
    {
        $student      = auth()->user();
        $stat         = StudentStat::firstOrCreate(['user_id' => $student->id]);
        $earnedBadges = $student->badges()->get()->keyBy('slug');
        $allBadges    = Badge::all();
        $recentXp     = XpEvent::where('user_id', $student->id)
                            ->latest()
                            ->take(20)
                            ->get();

        return view('student.achievements', compact('stat', 'earnedBadges', 'allBadges', 'recentXp'));
    }
}
