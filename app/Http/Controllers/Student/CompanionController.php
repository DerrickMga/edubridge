<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ClaudeService;
use Illuminate\Http\Request;

class CompanionController extends Controller
{
    public function __construct(private ClaudeService $claude) {}

    public function index(Request $request)
    {
        $conversations = $request->user()->conversations()
            ->orderByDesc('updated_at')->get();
        return view('student.companion', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        return view('student.companion-chat', compact('conversation'));
    }

    public function store(Request $request)
    {
        $conversation = $request->user()->conversations()->create([
            'title'   => 'New conversation',
            'channel' => 'web',
        ]);
        return redirect()->route('student.companion.show', $conversation);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $request->validate(['message' => 'required|string|max:2000']);

        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $request->message,
        ]);

        $history = $conversation->messages()->get()->map(fn($m) => [
            'role'    => $m->role,
            'content' => $m->content,
        ])->toArray();

        $reply = $this->claude->chat($history);

        $message = $conversation->messages()->create([
            'role'    => 'assistant',
            'content' => $reply,
        ]);

        $conversation->touch();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }
        return back();
    }
}
