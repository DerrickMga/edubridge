<?php

namespace App\Jobs;

use App\Models\LiveSession;
use App\Models\User;
use App\Services\SessionAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessSessionAiReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public LiveSession $session) {}

    public function handle(SessionAiService $aiService): void
    {
        $report = $aiService->process($this->session);

        // Notify admin once the report is ready
        $this->notifyAdmin($report);
    }

    private function notifyAdmin($report): void
    {
        $session = $this->session->load(['course', 'teacher', 'sessionLog']);
        $adminEmail = 'info@kmgvitallinks.co.uk';

        $subject  = "[EduBridge] AI Report Ready — {$session->title}";
        $quizNote = $report->quiz_id
            ? "An AI quiz ({$report->quiz_id}) has been created and is awaiting teacher review."
            : 'No quiz was generated for this session.';

        $lines = [
            "Session: {$session->title}",
            "Course: " . ($session->course?->title ?? '—'),
            "Teacher: " . ($session->teacher?->name ?? '—'),
            "Date: " . $session->scheduled_at->format('D d M Y g:i a'),
            "Duration: {$session->duration_minutes} min",
            "Attendees tracked: {$report->attendees_count}",
            '',
            "Summary:",
            $report->summary ?? '(not available)',
            '',
            "Action Items:",
        ];

        foreach ($report->action_items ?? [] as $item) {
            $lines[] = "  • {$item}";
        }

        $lines[] = '';
        $lines[] = $quizNote;
        $lines[] = '';

        if ($session->sessionLog) {
            $lines[] = "Teacher reported: {$session->sessionLog->actual_duration_minutes} min, {$session->sessionLog->actual_student_count} students.";
        } else {
            $lines[] = "⚠ Teacher has not submitted their session log yet.";
        }

        $lines[] = '';
        $lines[] = "Review at: " . config('app.url') . "/admin/session-reports/{$session->id}";

        $body = implode("\n", $lines);

        try {
            Mail::raw($body, function ($msg) use ($adminEmail, $subject) {
                $msg->to($adminEmail)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
        } catch (\Throwable) {
            // Non-fatal — report is still saved
        }
    }
}
