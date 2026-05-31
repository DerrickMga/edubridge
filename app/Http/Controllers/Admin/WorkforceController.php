<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\TeacherShift;
use App\Models\User;
use App\Services\ShiftAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkforceController extends Controller
{
    public function __construct(private readonly ShiftAssignmentService $assigner) {}

    /** Overview dashboard: weekly hours per teacher + presence + counts. */
    public function index(Request $request)
    {
        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get();

        $rows = $teachers->map(function (User $t) use ($weekStart, $weekEnd) {
            $shifts = TeacherShift::where('teacher_id', $t->id)
                ->whereBetween('starts_at', [$weekStart, $weekEnd])
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->get();
            $hours = $shifts->sum(fn ($s) => $s->duration_hours);

            return (object) [
                'teacher'             => $t,
                'is_online'           => $t->isOnline(),
                'status'              => $t->availability_status,
                'accepts'             => $t->accepts_assignments,
                'last_seen'           => $t->last_seen_at,
                'weekly_hours'        => round($hours, 2),
                'weekly_payout'       => round($hours * (float) ($t->hourly_rate_usd ?? 0), 2),
                'upcoming_shifts'     => $shifts->where('status', 'scheduled')->count(),
                'completed_shifts'    => $shifts->where('status', 'completed')->count(),
            ];
        });

        $totals = [
            'teachers'        => $teachers->count(),
            'online_now'      => $rows->where('is_online', true)->count(),
            'weekly_hours'    => round($rows->sum('weekly_hours'), 2),
            'weekly_payout'   => round($rows->sum('weekly_payout'), 2),
            'unassigned'      => LiveSession::whereNull('teacher_id')
                ->where('scheduled_at', '>=', now())
                ->count(),
        ];

        return view('admin.workforce.index', compact('rows', 'totals', 'weekStart', 'weekEnd'));
    }

    /** Weekly rota grid: all shifts in selectable week. */
    public function rota(Request $request)
    {
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $shifts = TeacherShift::with('teacher', 'course', 'liveSession')
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($s) => $s->teacher_id);

        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        $days = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        return view('admin.workforce.rota', compact('shifts', 'teachers', 'days', 'weekStart', 'weekEnd'));
    }

    /** Auto-assign queued / unassigned live sessions to best-fit teachers. */
    public function autoAssign(Request $request)
    {
        $horizonDays = (int) $request->input('days', 14);
        $sessions = LiveSession::whereNull('teacher_id')
            ->orWhereDoesntHave('teacher')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDays($horizonDays))
            ->whereIn('status', ['scheduled'])
            ->get();

        $created = 0;
        $skipped = 0;
        foreach ($sessions as $session) {
            $shift = $this->assigner->shiftFromLiveSession($session, null, $request->user()->id, true);
            if ($shift) {
                if (! $session->teacher_id) {
                    $session->teacher_id = $shift->teacher_id;
                    $session->saveQuietly();
                }
                $created++;
            } else {
                $skipped++;
            }
        }

        return back()->with('success', "Auto-assign complete: {$created} assigned, {$skipped} could not be matched.");
    }

    /** Create a shift manually. */
    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'teacher_id'      => 'required|exists:users,id',
            'course_id'       => 'nullable|exists:courses,id',
            'live_session_id' => 'nullable|exists:live_sessions,id',
            'title'           => 'required|string|max:200',
            'starts_at'       => 'required|date',
            'ends_at'         => 'required|date|after:starts_at',
            'notes'           => 'nullable|string',
        ]);

        $teacher = User::find($data['teacher_id']);
        TeacherShift::create($data + [
            'status'                   => 'scheduled',
            'auto_assigned'            => false,
            'assigned_by'              => $request->user()->id,
            'hourly_rate_usd_snapshot' => $teacher->hourly_rate_usd,
        ]);

        return back()->with('success', 'Shift created.');
    }

    public function destroyShift(TeacherShift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift removed.');
    }

    /** Suggest candidates for a given window (AJAX). */
    public function suggest(Request $request)
    {
        $data = $request->validate([
            'starts_at' => 'required|date',
            'ends_at'   => 'required|date|after:starts_at',
            'course_id' => 'nullable|exists:courses,id',
        ]);
        $ranked = $this->assigner->rankCandidates(
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['ends_at']),
            $data['course_id'] ?? null,
        )->take(10)->map(fn ($r) => [
            'teacher_id'   => $r['teacher']->id,
            'name'         => $r['teacher']->name,
            'is_online'    => $r['teacher']->isOnline(),
            'weekly_hours' => $r['weekly_hours'],
            'score'        => $r['score'],
            'reasons'      => $r['reasons'],
        ]);

        return response()->json(['candidates' => $ranked]);
    }
}
