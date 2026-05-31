<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSessionAiReport;
use App\Models\LiveSession;
use App\Models\SessionLog;
use App\Models\TeacherPaymentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionLogController extends Controller
{
    /**
     * Store a teacher's post-session log.
     * Also auto-creates a pending TeacherPaymentItem.
     * If the AI report hasn't been generated yet, dispatch the job now.
     */
    public function store(Request $request, LiveSession $liveSession): \Illuminate\Http\RedirectResponse
    {
        abort_if($liveSession->teacher_id !== $request->user()->id, 403);
        abort_if($liveSession->sessionLog()->exists(), 422, 'You have already submitted a log for this session.');

        $data = $request->validate([
            'actual_duration_minutes' => ['required', 'integer', 'min:1', 'max:720'],
            'actual_student_count'    => ['required', 'integer', 'min:0', 'max:2000'],
            'notes'                   => ['nullable', 'string', 'max:2000'],
        ]);

        [$log, $total] = DB::transaction(function () use ($data, $liveSession, $request) {
            $log = SessionLog::create([
                'live_session_id'         => $liveSession->id,
                'teacher_id'              => $request->user()->id,
                'actual_duration_minutes' => $data['actual_duration_minutes'],
                'actual_student_count'    => $data['actual_student_count'],
                'notes'                   => $data['notes'] ?? null,
                'submitted_at'            => now(),
            ]);

            // Auto-create payment item
            $rate  = $request->user()->hourly_rate_usd ?? 15.00;
            $hours = round($data['actual_duration_minutes'] / 60, 2);
            $total = round($hours * $rate, 2);

            TeacherPaymentItem::firstOrCreate(
                ['live_session_id' => $liveSession->id],
                [
                    'teacher_id'       => $request->user()->id,
                    'session_log_id'   => $log->id,
                    'hours_logged'     => $hours,
                    'student_count'    => $data['actual_student_count'],
                    'hourly_rate_usd'  => $rate,
                    'total_usd'        => $total,
                    'status'           => 'pending',
                ]
            );

            return [$log, $total];
        });

        // Dispatch AI processing if not done yet (outside transaction)
        if (! $liveSession->aiReport) {
            ProcessSessionAiReport::dispatch($liveSession)->onQueue('default');
        }

        return back()->with('success', 'Session log submitted! Your payment claim of $' . number_format($total, 2) . ' is pending admin approval.');
    }
}
