<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\CompanionService;
use Illuminate\Http\Request;

class CompanionController extends Controller
{
    public function __construct(private CompanionService $companion) {}

    public function index(Request $request)
    {
        $conversations = $request->user()->conversations()
            ->orderByDesc('updated_at')->get();
        return view('student.companion', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $conversation->load('messages', 'learningMemory');
        return view('student.companion-chat', compact('conversation'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'         => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:50',
            'preferred_model' => 'nullable|in:chiedza,claude,gpt,auto',
        ]);

        $conversation = $request->user()->conversations()->create([
            'title'           => 'New conversation',
            'channel'         => 'web',
            'subject'         => $data['subject'] ?? null,
            'level'           => $data['level'] ?? null,
            'preferred_model' => $data['preferred_model'] ?? 'auto',
        ]);

        return redirect()->route('student.companion.show', $conversation);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $request->validate(['message' => 'required|string|max:4000', 'model' => 'nullable|in:chiedza,claude,gpt,auto']);

        $model = $request->input('model', $conversation->preferred_model ?? 'auto');

        // Save user message
        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $request->message,
        ]);

        // Get AI reply via CompanionService
        $result = $this->companion->respond($conversation, $request->message, $model);

        $message = $conversation->messages()->create([
            'role'       => 'assistant',
            'model_used' => $result['model_used'],
            'content'    => $result['content'],
        ]);

        $conversation->touch();

        // Auto-update conversation title from first exchange
        if ($conversation->messages()->count() <= 3 && $conversation->title === 'New conversation') {
            $snippet = substr($request->message, 0, 50);
            $conversation->update(['title' => $snippet]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => $message,
                'model_used' => $result['model_used'],
            ]);
        }

        return back();
    }

    /**
     * Generate a personalised study plan (AJAX).
     */
    public function studyPlan(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $data = $request->validate([
            'subject' => 'required|string|max:100',
            'level'   => 'required|string|max:50',
            'weeks'   => 'required|integer|min:1|max:16',
            'topics'  => 'required|array|min:1',
            'topics.*'=> 'string|max:100',
        ]);

        $plan = $this->companion->generateStudyPlan(
            $conversation,
            $data['subject'],
            $data['level'],
            (int) $data['weeks'],
            $data['topics'],
        );

        // Store as a conversation message so it's in the history
        $conversation->messages()->create([
            'role'       => 'assistant',
            'model_used' => 'gpt',
            'content'    => "📅 **Study Plan Generated**\n\n" . json_encode($plan, JSON_PRETTY_PRINT),
            'metadata'   => ['type' => 'study_plan', 'plan' => $plan],
        ]);

        return response()->json(['plan' => $plan]);
    }

    /**
     * Generate study notes on a topic.
     */
    public function notes(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $data = $request->validate([
            'topic'   => 'required|string|max:500',
            'subject' => 'nullable|string|max:100',
            'level'   => 'nullable|string|max:50',
        ]);

        $notes = $this->companion->generateNotes(
            $data['topic'],
            $data['level'] ?? $conversation->level ?? '',
            $data['subject'] ?? $conversation->subject ?? '',
        );

        $message = $conversation->messages()->create([
            'role'       => 'assistant',
            'model_used' => 'chiedza',
            'content'    => $notes,
            'metadata'   => ['type' => 'notes', 'topic' => $data['topic']],
        ]);

        return response()->json(['notes' => $notes, 'message' => $message]);
    }

    /**
     * Update conversation preferences (model, subject, level).
     */
    public function updatePreferences(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $data = $request->validate([
            'preferred_model' => 'nullable|in:chiedza,claude,gpt,auto',
            'subject'         => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:50',
        ]);
        $conversation->update($data);
        return response()->json(['ok' => true]);
    }
}
