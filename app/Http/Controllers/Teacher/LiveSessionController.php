<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
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
