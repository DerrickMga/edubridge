<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{LiveSession, Recording};
use App\Services\RecordingService;
use Illuminate\Http\Request;

class RecordingController extends Controller
{
    public function __construct(private readonly RecordingService $service) {}

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

    public function destroy(LiveSession $liveSession, Recording $recording)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        abort_if($recording->live_session_id !== $liveSession->id, 404);
        $this->service->delete($recording);
        return back()->with('success', 'Recording deleted.');
    }
}
