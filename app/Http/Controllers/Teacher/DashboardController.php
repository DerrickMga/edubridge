<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $courses  = $request->user()->courses()->withCount('enrollments')->get();
        $earnings = $request->user()->payments()->where('status','paid')->sum('amount');
        $upcoming = \App\Models\LiveSession::where('teacher_id', $request->user()->id)
            ->where('scheduled_at','>=',now())->orderBy('scheduled_at')->take(5)->get();
        return view('teacher.dashboard', compact('courses','earnings','upcoming'));
    }
}
