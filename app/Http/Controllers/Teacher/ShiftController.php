<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $upcoming = TeacherShift::with('course', 'liveSession')
            ->where('teacher_id', $teacher->id)
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $past = TeacherShift::with('course', 'liveSession')
            ->where('teacher_id', $teacher->id)
            ->where('ends_at', '<', now())
            ->orderByDesc('starts_at')
            ->limit(50)
            ->get();

        $totals = [
            'this_week_hours'  => $this->hoursBetween($teacher->id, now()->startOfWeek(), now()->endOfWeek()),
            'this_month_hours' => $this->hoursBetween($teacher->id, now()->startOfMonth(), now()->endOfMonth()),
            'pending_payout'   => TeacherShift::where('teacher_id', $teacher->id)
                ->where('status', 'completed')
                ->sum('payout_amount_usd'),
        ];

        return view('teacher.shifts.index', compact('upcoming', 'past', 'totals'));
    }

    protected function hoursBetween(int $teacherId, $start, $end): float
    {
        return (float) TeacherShift::where('teacher_id', $teacherId)
            ->whereBetween('starts_at', [$start, $end])
            ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
            ->get()
            ->sum(fn (TeacherShift $s) => $s->duration_hours);
    }
}
