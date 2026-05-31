<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LiveSession;
use App\Services\CompanionService;
use Illuminate\Http\Request;

/**
 * AI tools for teachers — lesson summarization, note generation,
 * and a teacher-side AI companion.
 */
class AiToolsController extends Controller
{
    public function __construct(private CompanionService $companion) {}

    /**
     * GET teacher/ai-tools
     * Landing page for teacher AI tools.
     */
    public function index(Request $request)
    {
        return view('teacher.ai-tools', [
            'recentSessions' => LiveSession::where('teacher_id', $request->user()->id)
                ->where('scheduled_at', '<', now())
                ->with('course')
                ->orderByDesc('scheduled_at')
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * POST teacher/ai-tools/summarise
     * Summarise a lesson from pasted content or a lesson ID.
     */
    public function summarise(Request $request)
    {
        $data = $request->validate([
            'content'    => 'required_without:lesson_id|nullable|string|max:20000',
            'lesson_id'  => 'required_without:content|nullable|integer|exists:lessons,id',
            'subject'    => 'nullable|string|max:100',
        ]);

        $content = $data['content'] ?? '';

        if (! empty($data['lesson_id'])) {
            $lesson = Lesson::findOrFail($data['lesson_id']);
            $this->authorize('update', $lesson->course);
            $content = $lesson->description . "\n\n" . ($lesson->content ?? '');
            $data['subject'] = $data['subject'] ?? $lesson->course->subject ?? '';
        }

        $summary = $this->companion->summariseLesson($content, $data['subject'] ?? '');

        if ($request->expectsJson()) {
            return response()->json(['summary' => $summary]);
        }

        return back()->with('ai_summary', $summary);
    }

    /**
     * POST teacher/ai-tools/notes
     * Generate structured notes on a topic.
     */
    public function generateNotes(Request $request)
    {
        $data = $request->validate([
            'topic'   => 'required|string|max:500',
            'subject' => 'nullable|string|max:100',
            'level'   => 'nullable|string|max:50',
        ]);

        $notes = $this->companion->generateNotes(
            $data['topic'],
            $data['level'] ?? '',
            $data['subject'] ?? '',
        );

        if ($request->expectsJson()) {
            return response()->json(['notes' => $notes]);
        }

        return back()->with('ai_notes', $notes);
    }

    /**
     * POST teacher/ai-tools/study-plan
     * Generate a study plan for a course/cohort.
     */
    public function studyPlan(Request $request)
    {
        $data = $request->validate([
            'subject'  => 'required|string|max:100',
            'level'    => 'required|string|max:50',
            'weeks'    => 'required|integer|min:1|max:16',
            'topics'   => 'required|string', // comma-separated
        ]);

        $topics = array_filter(array_map('trim', explode(',', $data['topics'])));

        // Use a dummy conversation context (no memory for teacher plans)
        $plan = app(\App\Services\GptService::class)->generateStudyPlan(
            $data['subject'],
            $data['level'],
            (int) $data['weeks'],
            $topics,
        );

        return response()->json(['plan' => $plan]);
    }
}
