<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSessionAiReport;
use App\Models\LiveSession;
use App\Models\SessionAiReport;

class SessionReportController extends Controller
{
    public function index()
    {
        $sessions = LiveSession::with([
            'course', 'teacher', 'sessionLog', 'aiReport', 'paymentItem', 'attendances',
        ])
        ->whereIn('status', ['completed', 'ongoing', 'scheduled'])
        ->where('scheduled_at', '<', now())
        ->orderByDesc('scheduled_at')
        ->paginate(25);

        return view('admin.session-reports.index', compact('sessions'));
    }

    public function show(LiveSession $session)
    {
        $session->load(['course', 'teacher', 'sessionLog', 'aiReport.quiz.questions', 'paymentItem', 'attendances.user']);

        return view('admin.session-reports.show', compact('session'));
    }

    /**
     * Manually trigger AI processing for a session (admin re-run).
     */
    public function reprocess(LiveSession $session)
    {
        // Remove existing report so the job creates a fresh one
        $session->aiReport()?->delete();
        ProcessSessionAiReport::dispatch($session)->onQueue('default');

        return back()->with('success', 'AI report re-processing dispatched.');
    }
}
