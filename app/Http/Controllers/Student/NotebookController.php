<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentNotebook;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');

        $query = $request->user()->notebooks()->latest('is_pinned')->latest();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $notebooks = $query->paginate(20)->withQueryString();

        $counts = [
            'all'           => $request->user()->notebooks()->count(),
            'notes'         => $request->user()->notebooks()->where('type', 'notes')->count(),
            'study_plan'    => $request->user()->notebooks()->where('type', 'study_plan')->count(),
            'advanced_plan' => $request->user()->notebooks()->where('type', 'advanced_plan')->count(),
        ];

        return view('student.notebook.index', compact('notebooks', 'counts', 'type'));
    }

    public function show(StudentNotebook $notebook)
    {
        abort_if($notebook->user_id !== auth()->id(), 403);
        return view('student.notebook.show', compact('notebook'));
    }

    public function destroy(StudentNotebook $notebook)
    {
        abort_if($notebook->user_id !== auth()->id(), 403);
        $notebook->delete();
        return back()->with('success', 'Deleted from notebook.');
    }

    public function pin(StudentNotebook $notebook)
    {
        abort_if($notebook->user_id !== auth()->id(), 403);
        $notebook->update(['is_pinned' => ! $notebook->is_pinned]);
        return response()->json(['is_pinned' => $notebook->is_pinned]);
    }

    public function update(Request $request, StudentNotebook $notebook)
    {
        abort_if($notebook->user_id !== auth()->id(), 403);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'tags'  => 'nullable|array',
            'tags.*'=> 'string|max:50',
        ]);
        $notebook->update($data);
        return response()->json(['ok' => true]);
    }
}
