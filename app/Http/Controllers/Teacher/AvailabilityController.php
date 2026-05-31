<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAvailability;
use App\Models\TeacherTimeOff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $windows = TeacherAvailability::where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')->orderBy('start_time')->get();
        $timeOff = TeacherTimeOff::where('teacher_id', $teacher->id)
            ->orderByDesc('starts_at')->limit(50)->get();

        return view('teacher.availability.index', compact('teacher', 'windows', 'timeOff'));
    }

    public function storeWindow(Request $request)
    {
        $data = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);
        $data['teacher_id'] = $request->user()->id;
        TeacherAvailability::create($data);

        return back()->with('success', 'Availability window added.');
    }

    public function destroyWindow(Request $request, TeacherAvailability $window)
    {
        abort_if($window->teacher_id !== $request->user()->id, 403);
        $window->delete();
        return back()->with('success', 'Window removed.');
    }

    public function storeTimeOff(Request $request)
    {
        $data = $request->validate([
            'starts_at' => 'required|date',
            'ends_at'   => 'required|date|after:starts_at',
            'reason'    => 'nullable|string|max:255',
        ]);
        $data['teacher_id'] = $request->user()->id;
        $data['status']     = 'requested';
        TeacherTimeOff::create($data);

        return back()->with('success', 'Time-off request submitted.');
    }

    public function destroyTimeOff(Request $request, TeacherTimeOff $timeOff)
    {
        abort_if($timeOff->teacher_id !== $request->user()->id, 403);
        $timeOff->delete();
        return back()->with('success', 'Time-off entry removed.');
    }

    /** Toggle the teacher's availability status (online|busy|away|offline). */
    public function setStatus(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:online,busy,away,offline',
            'accepts_assignments' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $user->availability_status = $data['status'];
        if (array_key_exists('accepts_assignments', $data)) {
            $user->accepts_assignments = (bool) $data['accepts_assignments'];
        }
        $user->last_seen_at = now();
        $user->saveQuietly();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $user->availability_status,
                'accepts_assignments' => $user->accepts_assignments,
                'is_online' => $user->isOnline(),
            ]);
        }
        return back()->with('success', "Status set to {$user->availability_status}.");
    }
}
