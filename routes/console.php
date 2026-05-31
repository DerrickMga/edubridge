<?php

use App\Jobs\ProcessSessionAiReport;
use App\Models\Assignment;
use App\Models\LiveSession;
use App\Console\Commands\SyncZoomRecordings;
use App\Notifications\AssignmentDueNotification;
use App\Notifications\LiveSessionReminderNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Live Session Reminders — runs every minute, notifies 30 min before start
|--------------------------------------------------------------------------
*/
Schedule::call(function () {
    $windowStart = now()->addMinutes(29);
    $windowEnd   = now()->addMinutes(31);

    LiveSession::with(['course.enrollments'])
        ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
        ->whereIn('status', ['scheduled', 'ongoing'])
        ->each(function (LiveSession $session) {
            $session->course->enrollments->each(
                fn ($student) => $student->notify(new LiveSessionReminderNotification($session))
            );
        });
})->everyMinute()->name('live-session-reminders')->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Assignment Due Reminders — runs every 30 minutes
| Notifies students 24 hours before and again 2 hours before due_at
|--------------------------------------------------------------------------
*/
Schedule::call(function () {
    // 24-hour window
    Assignment::with(['course.enrollments'])
        ->where('is_published', true)
        ->whereBetween('due_at', [now()->addHours(23)->addMinutes(45), now()->addHours(24)->addMinutes(15)])
        ->each(function (Assignment $assignment) {
            $assignment->course->enrollments->each(
                fn ($student) => $student->notify(new AssignmentDueNotification($assignment))
            );
        });

    // 2-hour window
    Assignment::with(['course.enrollments'])
        ->where('is_published', true)
        ->whereBetween('due_at', [now()->addHours(1)->addMinutes(45), now()->addHours(2)->addMinutes(15)])
        ->each(function (Assignment $assignment) {
            $assignment->course->enrollments->each(
                fn ($student) => $student->notify(new AssignmentDueNotification($assignment))
            );
        });
})->everyThirtyMinutes()->name('assignment-due-reminders')->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| AI Session Reports — runs every 5 min, processes ended sessions
| Picks up sessions that ended >15 min ago with no AI report yet
|--------------------------------------------------------------------------
*/
Schedule::call(function () {
    LiveSession::where('scheduled_at', '<', now()->subMinutes(15))
        ->whereNotIn('status', ['cancelled'])
        ->whereDoesntHave('aiReport')
        ->with(['course', 'teacher', 'sessionLog'])
        ->chunk(10, function ($sessions) {
            foreach ($sessions as $session) {
                ProcessSessionAiReport::dispatch($session);
            }
        });
})->everyFiveMinutes()->name('session-ai-reports')->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Zoom Recording Sync — runs every 30 min
| Polls Zoom API for completed cloud recordings for any Zoom session that
| ended > 15 minutes ago but has no recording linked yet.
| (Complement to the real-time webhook — catches anything the webhook missed.)
|--------------------------------------------------------------------------
*/
Schedule::command(SyncZoomRecordings::class)
    ->everyThirtyMinutes()
    ->name('zoom-sync-recordings')
    ->withoutOverlapping()
    ->runInBackground();

