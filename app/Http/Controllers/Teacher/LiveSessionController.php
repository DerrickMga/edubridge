<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use App\Services\ZoomService;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function __construct(private ZoomService $zoom) {}

    public function create(Request $request)
    {
        $courses = $request->user()->courses()->orderBy('title')->get();
        return view('teacher.live-sessions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'        => ['required', 'integer', 'exists:courses,id'],
            'title'            => ['required', 'string', 'max:255'],
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'provider'         => ['required', 'in:Zoom,Meet,Calendly,Other'],
            'meeting_url'      => ['nullable', 'url', 'max:500'],
            'meeting_id'       => ['nullable', 'string', 'max:100'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        abort_if($course->teacher_id !== $request->user()->id, 403);

        $data['teacher_id'] = $request->user()->id;
        $data['status']     = 'scheduled';

        // Auto-create Zoom meeting if provider is Zoom and no URL given
        if ($data['provider'] === 'Zoom' && empty($data['meeting_url'])) {
            try {
                $meeting = $this->zoom->createMeeting(
                    $data['title'],
                    \Carbon\Carbon::parse($data['scheduled_at'])->utc()->toIso8601String(),
                    (int) $data['duration_minutes']
                );
                $data['meeting_id']  = $meeting['meeting_id'];
                $data['meeting_url'] = $meeting['join_url'];
                $data['start_url']   = $meeting['start_url'];
            } catch (\Throwable $e) {
                return back()->withInput()
                    ->withErrors(['zoom' => 'Could not create Zoom meeting: ' . $e->getMessage()]);
            }
        }

        LiveSession::create($data);

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Live session scheduled! Students will see it in their dashboards.');
    }

    public function edit(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        $courses = auth()->user()->courses()->orderBy('title')->get();
        return view('teacher.live-sessions.edit', compact('liveSession', 'courses'));
    }

    public function update(Request $request, LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);

        $data = $request->validate([
            'course_id'        => ['required', 'integer', 'exists:courses,id'],
            'title'            => ['required', 'string', 'max:255'],
            'scheduled_at'     => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'provider'         => ['required', 'in:Zoom,Meet,Calendly,Other'],
            'meeting_url'      => ['nullable', 'url', 'max:500'],
            'meeting_id'       => ['nullable', 'string', 'max:100'],
            'status'           => ['required', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        if ((int) $data['course_id'] !== $liveSession->course_id) {
            $course = Course::findOrFail($data['course_id']);
            abort_if($course->teacher_id !== auth()->id(), 403);
        }

        // Sync with Zoom API when provider is Zoom
        if ($data['provider'] === 'Zoom') {
            $existingMeetingId = $liveSession->meeting_id;

            if ($existingMeetingId) {
                // Update the existing meeting
                try {
                    $this->zoom->updateMeeting(
                        $existingMeetingId,
                        $data['title'],
                        \Carbon\Carbon::parse($data['scheduled_at'])->utc()->toIso8601String(),
                        (int) $data['duration_minutes']
                    );
                    // Keep existing meeting_id and url unless manually overridden
                    $data['meeting_id']  = $data['meeting_id']  ?: $existingMeetingId;
                    $data['meeting_url'] = $data['meeting_url'] ?: $liveSession->meeting_url;
                } catch (\Throwable $e) {
                    return back()->withInput()
                        ->withErrors(['zoom' => 'Could not update Zoom meeting: ' . $e->getMessage()]);
                }
            } elseif (empty($data['meeting_url'])) {
                // Create a new meeting if none exists
                try {
                    $meeting = $this->zoom->createMeeting(
                        $data['title'],
                        \Carbon\Carbon::parse($data['scheduled_at'])->utc()->toIso8601String(),
                        (int) $data['duration_minutes']
                    );
                    $data['meeting_id']  = $meeting['meeting_id'];
                    $data['meeting_url'] = $meeting['join_url'];
                    $data['start_url']   = $meeting['start_url'];
                } catch (\Throwable $e) {
                    return back()->withInput()
                        ->withErrors(['zoom' => 'Could not create Zoom meeting: ' . $e->getMessage()]);
                }
            }
        }

        $liveSession->update($data);

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Session updated.');
    }

    public function destroy(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);

        // Delete from Zoom if it was auto-created
        if ($liveSession->provider === 'Zoom' && $liveSession->meeting_id) {
            try {
                $this->zoom->deleteMeeting($liveSession->meeting_id);
            } catch (\Throwable) {
                // Non-fatal — still delete locally
            }
        }

        $liveSession->delete();
        return redirect()->route('teacher.dashboard')
            ->with('success', 'Session cancelled.');
    }
}

    public function create(Request $request)
    {
        $courses = $request->user()->courses()->orderBy('title')->get();
        return view('teacher.live-sessions.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'        => ['required', 'integer', 'exists:courses,id'],
            'title'            => ['required', 'string', 'max:255'],
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'provider'         => ['required', 'in:Zoom,Meet,Calendly,Other'],
            'meeting_url'      => ['nullable', 'url', 'max:500'],
            'meeting_id'       => ['nullable', 'string', 'max:100'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        abort_if($course->teacher_id !== $request->user()->id, 403);

        $data['teacher_id'] = $request->user()->id;
        $data['status']     = 'scheduled';

        LiveSession::create($data);

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Live session scheduled! Students will see it in their dashboards.');
    }

    public function edit(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        $courses = auth()->user()->courses()->orderBy('title')->get();
        return view('teacher.live-sessions.edit', compact('liveSession', 'courses'));
    }

    public function update(Request $request, LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);

        $data = $request->validate([
            'course_id'        => ['required', 'integer', 'exists:courses,id'],
            'title'            => ['required', 'string', 'max:255'],
            'scheduled_at'     => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'provider'         => ['required', 'in:Zoom,Meet,Calendly,Other'],
            'meeting_url'      => ['nullable', 'url', 'max:500'],
            'meeting_id'       => ['nullable', 'string', 'max:100'],
            'status'           => ['required', 'in:scheduled,ongoing,completed,cancelled'],
        ]);

        if ((int) $data['course_id'] !== $liveSession->course_id) {
            $course = Course::findOrFail($data['course_id']);
            abort_if($course->teacher_id !== auth()->id(), 403);
        }

        $liveSession->update($data);

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Session updated.');
    }

    public function destroy(LiveSession $liveSession)
    {
        abort_if($liveSession->teacher_id !== auth()->id(), 403);
        $liveSession->delete();
        return redirect()->route('teacher.dashboard')
            ->with('success', 'Session cancelled.');
    }
}
