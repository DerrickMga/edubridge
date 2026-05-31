<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSessionAiReport;
use App\Models\LiveSession;
use App\Models\SessionAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveSessionController extends Controller
{
    /**
     * Gated join endpoint â€” validates the student has an active, paid enrolment
     * before releasing the Zoom / Meet URL.
     *
     * Also logs attendance and, after session ends, dispatches the AI report job.
     */
    public function join(Request $request, LiveSession $liveSession): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // â”€â”€ 1. Enrolment gate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $isOwner = $liveSession->teacher_id === $user->id;

        if (! $isOwner) {
            $enrolled = DB::table('enrollments')
                ->where('user_id', $user->id)
                ->where('course_id', $liveSession->course_id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();

            if (! $enrolled) {
                return redirect()
                    ->route('courses.show', $liveSession->course_id)
                    ->with('error', 'You need an active enrolment to join this live session. Please complete your payment to access it.');
            }
        }

        // â”€â”€ 2. Session state gate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        if ($liveSession->status === 'cancelled') {
            return back()->with('error', 'This live session has been cancelled.');
        }

        if (! $liveSession->meeting_url) {
            return back()->with('error', 'The meeting link is not available yet. Please check back closer to the session time.');
        }

        // â”€â”€ 3. Record attendance (ignore duplicate â€” unique constraint) â”€â”€â”€â”€â”€â”€
        if (! $isOwner) {
            try {
                SessionAttendance::firstOrCreate(
                    ['live_session_id' => $liveSession->id, 'user_id' => $user->id],
                    ['joined_at' => now()],
                );
            } catch (\Throwable) {
                // Non-fatal
            }
        }

        // â”€â”€ 4. Mark session ongoing if it's starting â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        if ($liveSession->status === 'scheduled' && $liveSession->scheduled_at->isPast()) {
            $liveSession->update(['status' => 'ongoing']);
        }

        // â”€â”€ 5. If session has ended and no AI report yet, dispatch the job â”€â”€â”€
        $endTime = $liveSession->scheduled_at->addMinutes($liveSession->duration_minutes);
        if ($endTime->isPast() && ! $liveSession->aiReport) {
            ProcessSessionAiReport::dispatch($liveSession)->onQueue('default');
        }

        // â”€â”€ 6. Hand off â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $url = ($isOwner && $liveSession->start_url)
            ? $liveSession->start_url
            : $liveSession->meeting_url;

        return redirect()->away($url);
    }
}