<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\TeacherAvailability;
use App\Models\TeacherShift;
use App\Models\TeacherTimeOff;
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

        return redirect()->route('admin.workforce.index', ['teacher' => $data['teacher_id']])
            ->with('success', 'Shift created.');
    }

    public function destroyShift(TeacherShift $shift)
    {
        $teacherId = $shift->teacher_id;
        $shift->delete();
        return redirect()->route('admin.workforce.index', ['teacher' => $teacherId])
            ->with('success', 'Shift removed.');
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

    /** Return teacher profile data as JSON for the panel. */
    public function showTeacher(User $teacher)
    {
        $teacher->load([
            'availabilityWindows',
            'timeOff' => fn ($q) => $q->where('ends_at', '>=', now())->orderBy('starts_at'),
        ]);

        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        $weekShifts = TeacherShift::where('teacher_id', $teacher->id)
            ->whereBetween('starts_at', [$weekStart, $weekEnd])
            ->get();

        $upcomingShifts = TeacherShift::where('teacher_id', $teacher->id)
            ->where('starts_at', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $recentShifts = TeacherShift::where('teacher_id', $teacher->id)
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get();

        return response()->json([
            'teacher' => [
                'id'                  => $teacher->id,
                'name'                => $teacher->name,
                'email'               => $teacher->email,
                'hourly_rate_usd'     => $teacher->hourly_rate_usd,
                'timezone'            => $teacher->timezone ?? 'Africa/Harare',
                'accepts_assignments' => (bool) $teacher->accepts_assignments,
                'availability_status' => $teacher->availability_status,
                'is_verified'         => $teacher->is_verified,
                'qualification'       => $teacher->qualification,
            ],
            'this_week' => [
                'hours'     => round($weekShifts->whereIn('status', ['scheduled', 'in_progress', 'completed'])->sum('duration_hours'), 2),
                'payout'    => round($weekShifts->where('status', 'completed')->sum('payout_amount_usd'), 2),
                'upcoming'  => $weekShifts->where('status', 'scheduled')->count(),
                'completed' => $weekShifts->where('status', 'completed')->count(),
            ],
            'availability' => $teacher->availabilityWindows->map(fn ($w) => [
                'id'          => $w->id,
                'day'         => $w->day_of_week,
                'day_name'    => TeacherAvailability::dayName($w->day_of_week),
                'start_time'  => $w->start_time,
                'end_time'    => $w->end_time,
            ])->values(),
            'time_off' => $teacher->timeOff->map(fn ($t) => [
                'id'        => $t->id,
                'starts_at' => $t->starts_at->format('d M Y H:i'),
                'ends_at'   => $t->ends_at->format('d M Y H:i'),
                'reason'    => $t->reason,
                'status'    => $t->status,
            ])->values(),
            'upcoming_shifts' => $upcomingShifts->map(fn ($s) => [
                'id'       => $s->id,
                'title'    => $s->title,
                'starts_at'=> $s->starts_at->format('D d M, H:i'),
                'ends_at'  => $s->ends_at->format('H:i'),
                'status'   => $s->status,
                'hours'    => $s->duration_hours,
            ])->values(),
            'recent_shifts' => $recentShifts->map(fn ($s) => [
                'id'            => $s->id,
                'title'         => $s->title,
                'starts_at'     => $s->starts_at->format('D d M, H:i'),
                'status'        => $s->status,
                'hours_worked'  => $s->hours_worked,
                'duration_hours'=> $s->duration_hours,
                'payout'        => $s->payout_amount_usd,
            ])->values(),
        ]);
    }

    /** Update a teacher's rate, timezone, and assignment preference. */
    public function updateTeacher(Request $request, User $teacher)
    {
        $data = $request->validate([
            'hourly_rate_usd'     => 'required|numeric|min:0|max:9999',
            'accepts_assignments' => 'required|boolean',
            'timezone'            => 'required|string|max:64',
        ]);
        $teacher->update($data);
        return redirect()->route('admin.workforce.index', ['teacher' => $teacher->id])
            ->with('success', "Settings for {$teacher->name} updated — rate: \${$data['hourly_rate_usd']}/hr.");
    }

    /** Update shift status / mark complete with actual hours. */
    public function updateShift(Request $request, TeacherShift $shift)
    {
        $data = $request->validate([
            'status'       => 'required|in:scheduled,in_progress,completed,missed,cancelled',
            'hours_worked' => 'nullable|numeric|min:0|max:24',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $shift->fill([
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $shift->notes,
        ]);

        if ($data['status'] === 'completed') {
            $shift->hours_worked = $data['hours_worked'] ?? $shift->duration_hours;
            $shift->computePayout();
        }

        $shift->save();
        return redirect()->route('admin.workforce.index', ['teacher' => $shift->teacher_id])
            ->with('success', 'Shift updated.');
    }

    /** Add a recurring availability window for a teacher. */
    public function storeAvailability(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:users,id',
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);
        TeacherAvailability::create($data);
        return redirect()->route('admin.workforce.index', ['teacher' => $data['teacher_id']])
            ->with('success', 'Availability window added.');
    }

    /** Remove a recurring availability window. */
    public function destroyAvailability(TeacherAvailability $availability)
    {
        $teacherId = $availability->teacher_id;
        $availability->delete();
        return redirect()->route('admin.workforce.index', ['teacher' => $teacherId])
            ->with('success', 'Availability window removed.');
    }

    /** Add an approved time-off block for a teacher. */
    public function storeTimeOff(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'starts_at'  => 'required|date',
            'ends_at'    => 'required|date|after:starts_at',
            'reason'     => 'nullable|string|max:255',
        ]);
        TeacherTimeOff::create($data + [
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
        ]);
        return redirect()->route('admin.workforce.index', ['teacher' => $data['teacher_id']])
            ->with('success', 'Time off recorded.');
    }

    /** Remove a time-off block. */
    public function destroyTimeOff(TeacherTimeOff $timeOff)
    {
        $teacherId = $timeOff->teacher_id;
        $timeOff->delete();
        return redirect()->route('admin.workforce.index', ['teacher' => $teacherId])
            ->with('success', 'Time off removed.');
    }
}
