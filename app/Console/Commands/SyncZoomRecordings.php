<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Models\Recording;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncZoomRecordings extends Command
{
    protected $signature = 'zoom:sync-recordings
                            {--session= : Only sync a specific live session ID}
                            {--force    : Re-sync even if recordings already exist}';

    protected $description = 'Fetch completed Zoom cloud recordings and auto-populate Recording records';

    public function __construct(private readonly ZoomService $zoom)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = LiveSession::whereIn('provider', ['Zoom', 'zoom'])
            ->whereNotNull('meeting_id')
            ->where('scheduled_at', '<=', now()->subMinutes(15)); // must have had time to end

        if (! $this->option('force')) {
            $query->whereDoesntHave('recordings', fn ($q) => $q->where('source', 'zoom'));
        }

        if ($id = $this->option('session')) {
            $query->where('id', $id);
        }

        $sessions = $query->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions to sync.');
            return self::SUCCESS;
        }

        $this->info("Syncing recordings for {$sessions->count()} session(s)...");

        $synced = 0;

        foreach ($sessions as $session) {
            try {
                $data  = $this->zoom->getRecordingFiles($session->meeting_id);
                $files = $data['recording_files'] ?? [];

                if (empty($files)) {
                    $this->line("  · Session #{$session->id} ({$session->title}): no cloud recordings yet.");
                    continue;
                }

                foreach ($files as $file) {
                    // Only process completed MP4 files
                    if (($file['status'] ?? '') !== 'completed') continue;
                    if (($file['file_type'] ?? '') !== 'MP4') continue;

                    $url = $file['play_url'] ?? $file['download_url'] ?? null;
                    if (! $url) continue;

                    // Avoid exact-URL duplicates
                    if (! $this->option('force') && Recording::where('external_url', $url)->exists()) {
                        continue;
                    }

                    $durationSec = 0;
                    if (! empty($file['recording_start']) && ! empty($file['recording_end'])) {
                        $durationSec = (int) Carbon::parse($file['recording_end'])
                            ->diffInSeconds(Carbon::parse($file['recording_start']));
                    }

                    Recording::create([
                        'live_session_id' => $session->id,
                        'course_id'       => $session->course_id,
                        'teacher_id'      => $session->teacher_id,
                        'title'           => $session->title . ' — Recording',
                        'external_url'    => $url,
                        'source'          => 'zoom',
                        'duration_seconds'=> $durationSec,
                        'file_size_bytes' => $file['file_size'] ?? 0,
                        'status'          => 'available',
                        'is_public'       => true,
                    ]);

                    $mins = intdiv($durationSec, 60);
                    $this->line("  ✓ Session #{$session->id}: linked recording ({$mins}m)");
                    $synced++;
                }

                // Mark session completed if it was still 'scheduled' and recordings are in
                if ($session->status === 'scheduled' && ! empty($files)) {
                    $session->update(['status' => 'completed']);
                }

            } catch (\Throwable $e) {
                Log::error("SyncZoomRecordings: session #{$session->id} — {$e->getMessage()}");
                $this->warn("  ✗ Session #{$session->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. {$synced} recording(s) created.");

        return self::SUCCESS;
    }
}
