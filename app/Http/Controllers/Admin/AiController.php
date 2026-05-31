<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Conversation, Message};
use App\Services\{CompanionService, GptService, AnthropicService, ClaudeService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiController extends Controller
{
    public function __construct(
        private CompanionService $companion,
        private GptService       $gpt,
        private AnthropicService $anthropic,
        private ClaudeService    $chiedza,
    ) {}

    /**
     * AI Tools dashboard page.
     */
    public function index()
    {
        $month = Carbon::now()->subDays(30);

        $stats = [
            'total_conversations' => Conversation::count(),
            'total_messages'      => Message::count(),
            'week_conversations'  => Conversation::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'week_messages'       => Message::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        // Daily AI message volume for last 14 days
        $dailyActivity = Message::where('role', 'assistant')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->select(DB::raw("DATE(created_at) as day"), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $activityLabels = [];
        $activityData   = [];
        for ($i = 13; $i >= 0; $i--) {
            $key = Carbon::now()->subDays($i)->format('Y-m-d');
            $activityLabels[] = Carbon::now()->subDays($i)->format('d M');
            $activityData[]   = $dailyActivity[$key] ?? 0;
        }

        // Model breakdown last 30 days
        $modelStats = Message::where('role', 'assistant')
            ->where('created_at', '>=', $month)
            ->select('model_used', DB::raw('count(*) as total'))
            ->groupBy('model_used')
            ->pluck('total', 'model_used')
            ->toArray();

        // Most active students using AI
        $topAiUsers = Conversation::select('user_id', DB::raw('count(*) as conv_count'))
            ->groupBy('user_id')
            ->orderByDesc('conv_count')
            ->take(10)
            ->with('user')
            ->get();

        // Recent conversations
        $recentConversations = Conversation::with(['user', 'messages' => function ($q) {
            $q->latest()->take(1);
        }])->latest()->take(20)->get();

        return view('admin.ai-tools', compact(
            'stats', 'activityLabels', 'activityData',
            'modelStats', 'topAiUsers', 'recentConversations'
        ));
    }

    /**
     * Test AI prompt — calls any of the three models.
     */
    public function testPrompt(Request $request)
    {
        $request->validate([
            'model'   => 'required|in:chiedza,claude,gpt',
            'system'  => 'nullable|string|max:2000',
            'message' => 'required|string|max:4000',
        ]);

        $model   = $request->input('model');
        $system  = $request->input('system') ?: 'You are an AI assistant for EduBridge, an online education platform.';
        $message = $request->input('message');
        $messages = [['role' => 'user', 'content' => $message]];

        try {
            $response = match ($model) {
                'chiedza' => $this->chiedza->chat($messages, $system),
                'claude'  => $this->anthropic->chat($messages, $system),
                'gpt'     => $this->gpt->chat($messages, $system),
            };

            return response()->json(['response' => $response, 'model' => $model]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Broadcast an AI-crafted announcement to all students.
     */
    public function broadcastDraft(Request $request)
    {
        $request->validate([
            'topic'   => 'required|string|max:500',
            'tone'    => 'required|in:formal,friendly,motivational',
            'model'   => 'required|in:chiedza,claude,gpt',
        ]);

        $topic = $request->input('topic');
        $tone  = $request->input('tone');
        $model = $request->input('model');

        $system = "You are the EduBridge admin assistant. Write a concise platform announcement for students. Tone: {$tone}. Keep it under 200 words. Use simple Markdown.";
        $messages = [['role' => 'user', 'content' => "Write an announcement about: {$topic}"]];

        try {
            $draft = match ($model) {
                'chiedza' => $this->chiedza->chat($messages, $system),
                'claude'  => $this->anthropic->chat($messages, $system),
                'gpt'     => $this->gpt->chat($messages, $system),
            };

            return response()->json(['draft' => $draft, 'model' => $model]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate a course description with AI.
     */
    public function generateCourseDescription(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:200',
            'subject' => 'required|string|max:100',
            'level'   => 'required|string|max:100',
            'model'   => 'required|in:chiedza,claude,gpt',
        ]);

        $system = 'You are an expert curriculum designer for EduBridge. Write compelling course descriptions. Use markdown with bullet points for key learning outcomes.';
        $messages = [[
            'role'    => 'user',
            'content' => "Write a course description for:\nTitle: {$request->title}\nSubject: {$request->subject}\nLevel: {$request->level}\n\nInclude: overview paragraph, 4-5 learning outcomes, who this course is for.",
        ]];

        try {
            $model = $request->input('model');
            $response = match ($model) {
                'chiedza' => $this->chiedza->chat($messages, $system),
                'claude'  => $this->anthropic->chat($messages, $system),
                'gpt'     => $this->gpt->chat($messages, $system),
            };

            return response()->json(['description' => $response, 'model' => $model]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate a quiz with AI.
     */
    public function generateQuiz(Request $request)
    {
        $request->validate([
            'topic'     => 'required|string|max:200',
            'questions' => 'required|integer|min:3|max:15',
            'level'     => 'required|string|max:100',
            'model'     => 'required|in:chiedza,claude,gpt',
        ]);

        $system = 'You are a professional exam writer for EduBridge. Output quiz questions as valid JSON array only — no markdown code fences, no extra text. Format: [{"question": "...", "options": ["A", "B", "C", "D"], "answer": "A", "explanation": "..."}]';
        $messages = [[
            'role'    => 'user',
            'content' => "Generate {$request->questions} multiple-choice quiz questions on the topic: {$request->topic} for {$request->level} level students. Return JSON array only.",
        ]];

        try {
            $model = $request->input('model');
            $raw = match ($model) {
                'chiedza' => $this->chiedza->chat($messages, $system),
                'claude'  => $this->anthropic->chat($messages, $system),
                'gpt'     => $this->gpt->chat($messages, $system),
            };

            // Strip markdown fences if present
            $json = preg_replace('/^```json?\s*/m', '', $raw);
            $json = preg_replace('/```\s*$/m', '', $json);
            $questions = json_decode(trim($json), true);

            return response()->json(['questions' => $questions ?? [], 'raw' => $raw, 'model' => $model]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
