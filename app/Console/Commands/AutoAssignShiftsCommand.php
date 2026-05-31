<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Services\ShiftAssignmentService;
use Illuminate\Console\Command;

class AutoAssignShiftsCommand extends Command
{
    protected $signature = 'shifts:auto-assign {--days=14 : Look-ahead window in days}';
    protected $description = 'Auto-assign teachers to upcoming live sessions and close out finished shifts.';

    public function handle(ShiftAssignmentService $assigner): int
    {
        $horizon = (int) $this->option('days');

        // 1. Close out shifts whose window has ended.
        $closed = $assigner->closeOutDueShifts();
        $this->info("Closed {$closed} due shifts.");

        // 2. Materialise shifts for upcoming live sessions that don't have one yet.
        $sessions = LiveSession::whereIn('status', ['scheduled'])
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDays($horizon))
            ->get();

        $created = 0;
        $autoAssigned = 0;
        $skipped = 0;
        foreach ($sessions as $session) {
            $auto = ! $session->teacher_id;
            $shift = $assigner->shiftFromLiveSession($session, null, null, $auto);
            if (! $shift) { $skipped++; continue; }
            $created++;
            if ($auto && $shift->auto_assigned) {
                $autoAssigned++;
                if (! $session->teacher_id) {
                    $session->teacher_id = $shift->teacher_id;
                    $session->saveQuietly();
                }
            }
        }

        $this->info("Created/synced {$created} shifts ({$autoAssigned} auto-picked teachers). Skipped {$skipped}.");
        return self::SUCCESS;
    }
}
