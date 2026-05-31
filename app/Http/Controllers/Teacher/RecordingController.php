<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\UploadRecordingToYouTubeJob;
use App\Models\{LiveSession, Recording};
use App\Services\{RecordingService, YouTubeService, ZoomService};
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecordingController extends Controller
{
    public function __construct(
        private readonly RecordingService $service,
        private readonly ZoomService $zoom,
    ) {}

    public function index(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        $recordings = $liveSession->recordings()->latest()->get();
        return view('teacher.recordings.index', compact('liveSession', 'recordings'));
    }

    public function store(Request $request, LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);

        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'source_type'  => ['required', 'in:upload,external'],
            'external_url' => ['required_if:source_type,external', 'nullable', 'url', 'max:500'],
            'source'       => ['required_if:source_type,external', 'nullable', 'in:zoom,google_meet,youtube'],
            'file'         => ['required_if:source_type,upload', 'nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:2048000'],
        ]);

        if ($request->source_type === 'upload' && $request->hasFile('file')) {
            $recording = $this->service->storeUpload($liveSession, $request->file('file'), $request->title);
        } else {
            $recording = $this->service->linkExternal(
                $liveSession,
                $request->external_url,
                $request->title,
                $request->source ?? 'zoom'
            );
        }

        return back()->with('success', 'Recording added: '.$recording->title);
    }

    /**
     * Manually trigger a Zoom cloud recording sync for one session.
     * Shows up as a "Sync from Zoom" button on the recordings index page.
     */
    public function syncFromZoom(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        abort_if(! in_array($liveSession->provider, ['Zoom', 'zoom']), 422, 'Session is not a Zoom session.');
        abort_if(! $liveSession->meeting_id, 422, 'No Zoom meeting ID linked to this session.');

        $data  = $this->zoom->getRecordingFiles($liveSession->meeting_id);
        $files = $data['recording_files'] ?? [];

        $created = 0;
        foreach ($files as $file) {
            if (($file['status'] ?? '') !== 'completed') continue;
            if (($file['file_type'] ?? '') !== 'MP4') continue;

            $url = $file['play_url'] ?? $file['download_url'] ?? null;
            if (! $url) continue;
            if (Recording::where('external_url', $url)->exists()) continue;

            $durationSec = 0;
            if (! empty($file['recording_start']) && ! empty($file['recording_end'])) {
                $durationSec = (int) Carbon::parse($file['recording_end'])
                    ->diffInSeconds(Carbon::parse($file['recording_start']));
            }

            Recording::create([
                'live_session_id' => $liveSession->id,
                'course_id'       => $liveSession->course_id,
                'teacher_id'      => $liveSession->teacher_id,
                'title'           => $liveSession->title . ' — Recording',
                'external_url'    => $url,
                'source'          => 'zoom',
                'duration_seconds'=> $durationSec,
                'file_size_bytes' => $file['file_size'] ?? 0,
                'status'          => 'available',
                'is_public'       => true,
            ]);

            $created++;
        }

        if ($created === 0 && empty($files)) {
            return back()->with('warning', 'No Zoom cloud recordings found yet. Recordings usually appear 15–30 minutes after the session ends.');
        }

        return back()->with('success', $created > 0
            ? "{$created} recording(s) synced from Zoom."
            : 'All recordings were already synced — nothing new to add.');
    }

    public function destroy(LiveSession $liveSession, Recording $recording)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        abort_if($recording->live_session_id !== $liveSession->id, 404);
        $this->service->delete($recording);
        return back()->with('success', 'Recording deleted.');
    }

    public function uploadToYouTube(Request $request, LiveSession $liveSession, Recording $recording, YouTubeService $youtube)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        abort_if($recording->live_session_id !== $liveSession->id, 404);

        if ($recording->hasYouTube()) {
            return back()->with('warning', 'This recording is already on YouTube.');
        }

        if (! $youtube->tokenForUser($request->user())) {
            return back()->withErrors(['youtube' => 'Connect a YouTube channel first.']);
        }

        $validated = $request->validate([
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'privacy'   => ['nullable', 'in:public,unlisted,private'],
        ]);

        $updates = ['youtube_status' => 'queued', 'youtube_error' => null];
        if (! empty($validated['lesson_id'])) {
            $updates['lesson_id'] = $validated['lesson_id'];
        }
        if (! empty($validated['privacy'])) {
            $updates['youtube_privacy'] = $validated['privacy'];
        }
        $recording->update($updates);

        UploadRecordingToYouTubeJob::dispatch($recording->id);

        return back()->with('success', 'Upload queued — the recording will appear on YouTube once processing finishes.');
    }
}
