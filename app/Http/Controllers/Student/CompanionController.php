<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\StudentNotebook;
use App\Services\CompanionService;
use App\Services\YouTubeSearchService;
use Illuminate\Http\Request;

class CompanionController extends Controller
{
    public function __construct(
        private CompanionService      $companion,
        private YouTubeSearchService  $youtube,
    ) {}

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

    /**
     * Save generated notes to the student's notebook.
     */
    public function saveNotes(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'topic'   => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:100',
            'level'   => 'nullable|string|max:50',
            'content' => 'required|string|max:100000',
            'tags'    => 'nullable|array',
            'tags.*'  => 'string|max:50',
        ]);

        $notebook = StudentNotebook::create([
            'user_id'         => $request->user()->id,
            'conversation_id' => $conversation->id,
            'type'            => 'notes',
            'title'           => $data['title'],
            'topic'           => $data['topic'] ?? null,
            'subject'         => $data['subject'] ?? $conversation->subject,
            'level'           => $data['level']   ?? $conversation->level,
            'content'         => $data['content'],
            'tags'            => $data['tags'] ?? [],
        ]);

        return response()->json(['ok' => true, 'notebook_id' => $notebook->id]);
    }

    /**
     * Save a study plan to the student's notebook.
     */
    public function saveStudyPlan(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'subject' => 'nullable|string|max:100',
            'level'   => 'nullable|string|max:50',
            'type'    => 'nullable|in:study_plan,advanced_plan',
            'content' => 'required',  // JSON string or array
            'youtube_videos' => 'nullable|array',
            'tags'    => 'nullable|array',
            'tags.*'  => 'string|max:50',
        ]);

        $content = is_array($data['content'])
            ? json_encode($data['content'])
            : $data['content'];

        $notebook = StudentNotebook::create([
            'user_id'         => $request->user()->id,
            'conversation_id' => $conversation->id,
            'type'            => $data['type'] ?? 'study_plan',
            'title'           => $data['title'],
            'subject'         => $data['subject'] ?? $conversation->subject,
            'level'           => $data['level']   ?? $conversation->level,
            'content'         => $content,
            'youtube_videos'  => $data['youtube_videos'] ?? null,
            'tags'            => $data['tags'] ?? [],
        ]);

        return response()->json(['ok' => true, 'notebook_id' => $notebook->id]);
    }

    /**
     * Generate an advanced, week-by-week study plan with daily breakdown,
     * worked examples, practice questions, textbook refs, and YouTube videos.
     */
    public function advancedStudyPlan(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $data = $request->validate([
            'subject'    => 'required|string|max:100',
            'level'      => 'required|string|max:50',
            'weeks'      => 'required|integer|min:1|max:16',
            'topics'     => 'required|array|min:1',
            'topics.*'   => 'string|max:100',
            'exam_board' => 'nullable|string|max:50',
        ]);

        // Build memory hint for personalisation
        $memory = \App\Models\LearningMemory::where('conversation_id', $conversation->id)->first();
        $memHint = $memory?->summary ?? '';

        // Generate the advanced plan (GPT-4o, up to 8000 tokens)
        $plan = $this->companion->generateAdvancedStudyPlan(
            $data['subject'],
            $data['level'],
            (int) $data['weeks'],
            $data['topics'],
            $data['exam_board'] ?? 'ZIMSEC',
            $memHint,
        );

        // Resolve YouTube videos for each week's queries
        if (! isset($plan['error']) && isset($plan['weeks'])) {
            foreach ($plan['weeks'] as &$week) {
                foreach ($week['days'] ?? [] as &$day) {
                    if (! empty($day['youtube_queries'])) {
                        $day['youtube_results'] = $this->youtube->resolveQueries($day['youtube_queries']);
                    }
                }
                unset($day);
            }
            unset($week);
        }

        // Store in conversation history
        $conversation->messages()->create([
            'role'       => 'assistant',
            'model_used' => 'gpt',
            'content'    => "🗺️ **Advanced Study Plan Generated** — {$data['subject']} · {$data['level']} · {$data['weeks']} weeks",
            'metadata'   => ['type' => 'advanced_plan', 'plan' => $plan],
        ]);

        return response()->json(['plan' => $plan]);
    }
}

