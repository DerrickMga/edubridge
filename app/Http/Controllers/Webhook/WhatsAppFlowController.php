<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AssignmentSubmission;
use App\Models\LiveSession;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppFlowController
 *
 * Handles end-to-end encrypted WhatsApp Flow data-exchange requests.
 *
 * Meta sends:  { encrypted_flow_data, encrypted_aes_key, initial_vector }
 *
 * Decryption:
 *   1. RSA OAEP (PKCS1 OAEP SHA-256): decrypt encrypted_aes_key → $aesKey
 *   2. AES-128-GCM: decrypt encrypted_flow_data (last 16 bytes = auth tag)
 *
 * Response encryption:
 *   1. Flip last byte of IV
 *   2. AES-128-GCM encrypt JSON response
 *   3. Return base64(ciphertext + tag) with Content-Type: text/plain
 */
class WhatsAppFlowController extends Controller
{
    public function handle(Request $request)
    {
        // ── Validate input ────────────────────────────────────────────────────
        try {
            $body = $request->validate([
                'encrypted_flow_data' => 'required|string',
                'encrypted_aes_key'   => 'required|string',
                'initial_vector'      => 'required|string',
            ]);
        } catch (\Exception $e) {
            Log::warning('WhatsApp flow: invalid request', ['error' => $e->getMessage()]);
            return response('Bad Request', 400);
        }

        // ── Load & validate private key ───────────────────────────────────────
        $rawPrivateKey = config('services.whatsapp.flow_private_key', '');
        if (empty($rawPrivateKey)) {
            Log::error('WhatsApp flow: WHATSAPP_FLOW_PRIVATE_KEY is not configured');
            return response('Server Error', 500);
        }

        // Normalize escaped newlines from .env storage
        $privateKeyPem = str_replace(['\\n', '\n'], "\n", $rawPrivateKey);
        $privateKey    = openssl_pkey_get_private($privateKeyPem);

        if (!$privateKey) {
            Log::error('WhatsApp flow: failed to parse private key');
            return response('Server Error', 500);
        }

        // ── Decrypt AES key (RSA OAEP) ────────────────────────────────────────
        $encryptedAESKey   = base64_decode($body['encrypted_aes_key']);
        $initialVector     = base64_decode($body['initial_vector']);
        $encryptedFlowData = base64_decode($body['encrypted_flow_data']);

        $decrypted = openssl_private_decrypt(
            $encryptedAESKey,
            $aesKey,
            $privateKey,
            OPENSSL_PKCS1_OAEP_PADDING
        );

        if (!$decrypted || empty($aesKey)) {
            Log::error('WhatsApp flow: AES key decryption failed');
            return response('Unauthorized', 401);
        }

        // ── Decrypt flow payload (AES-128-GCM) ───────────────────────────────
        $authTag    = substr($encryptedFlowData, -16);
        $ciphertext = substr($encryptedFlowData, 0, -16);

        $decryptedData = openssl_decrypt(
            $ciphertext,
            'aes-128-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $initialVector,
            $authTag
        );

        if ($decryptedData === false) {
            Log::error('WhatsApp flow: GCM decryption failed');
            return response('Unauthorized', 401);
        }

        $payload = json_decode($decryptedData, true);

        Log::info('WhatsApp flow: request', [
            'action'     => $payload['action']     ?? 'unknown',
            'screen'     => $payload['screen']     ?? '-',
            'flow_token' => $payload['flow_token'] ?? '-',
        ]);

        // ── Route to handler ──────────────────────────────────────────────────
        $action = $payload['action'] ?? '';

        $response = match ($action) {
            'ping'          => $this->handlePing(),
            'INIT'          => $this->handleInit($payload),
            'data_exchange' => $this->handleDataExchange($payload),
            default         => tap(['data' => ['error' => 'unknown_action']], function () use ($action) {
                Log::warning('WhatsApp flow: unknown action', ['action' => $action]);
            }),
        };

        // ── Encrypt response ──────────────────────────────────────────────────
        // Flip last byte of IV (Meta protocol requirement)
        $responseIV              = $initialVector;
        $lastIndex               = strlen($responseIV) - 1;
        $responseIV[$lastIndex]  = chr(ord($responseIV[$lastIndex]) ^ 0xFF);

        $encryptedResponse = openssl_encrypt(
            json_encode($response),
            'aes-128-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $responseIV,
            $responseTag
        );

        return response(base64_encode($encryptedResponse . $responseTag), 200)
            ->header('Content-Type', 'text/plain');
    }

    // ─── Handlers ─────────────────────────────────────────────────────────────

    /** Health check — called when Meta verifies or tests the endpoint. */
    protected function handlePing(): array
    {
        return ['data' => ['status' => 'active']];
    }

    /**
     * INIT — called when a flow first opens.
     * Use flow_token to identify the user and screen.
     * Token format: "role:userId"  e.g. "student:42", "teacher:7", "menu:new_user"
     */
    protected function handleInit(array $payload): array
    {
        $screen     = $payload['screen']     ?? 'DASHBOARD';
        $flowToken  = $payload['flow_token'] ?? '';

        [$role, $userId] = $this->parseFlowToken($flowToken);

        if ($role === 'student' && $userId) {
            return $this->initStudentDashboard((int) $userId);
        }

        if ($role === 'teacher' && $userId) {
            return $this->initTeacherDashboard((int) $userId);
        }

        // Registration flow or unknown — no initial data needed
        return ['screen' => $screen ?: 'WELCOME', 'data' => []];
    }

    /** Data exchange — screen navigation and actions. */
    protected function handleDataExchange(array $payload): array
    {
        $screen    = $payload['screen']     ?? '';
        $data      = $payload['data']       ?? [];
        $flowToken = $payload['flow_token'] ?? '';

        [$role, $userId] = $this->parseFlowToken($flowToken);

        Log::info('WhatsApp flow: data_exchange', ['screen' => $screen, 'role' => $role, 'userId' => $userId]);

        // The data payload may carry an 'action' field set by the form
        $action = $data['action'] ?? $screen;

        return match ($action) {

            // Student Hub actions
            'courses', 'MY_COURSES'  => $this->loadStudentCourses($userId),
            'course_detail'          => $this->loadStudentCourseDetail($userId, $data['course_id'] ?? null),
            'ask_chiedza', 'chiedza' => $this->loadAskChiedza(),
            'ask_chiedza_submit'     => $this->handleChiedzaQuestion($userId, $data['question'] ?? ''),

            // Teacher Hub actions
            'courses_teacher'        => $this->loadTeacherCourses($userId),
            'course_link'            => $this->loadTeacherCourseLink($userId, $data['course_id'] ?? null),
            'announce', 'POST_ANNOUNCEMENT' => $this->loadAnnouncementForm($userId),
            'post_announcement'      => $this->submitAnnouncement($userId, $data),
            'grades', 'GRADES_QUEUE' => $this->loadGradesQueue($userId),

            // Shared back-to-dashboard
            'dashboard', 'DASHBOARD' => $this->reloadDashboard($role, $userId),

            default => $this->fallback($action),
        };
    }

    // ─── Student screen data ──────────────────────────────────────────────────

    private function initStudentDashboard(int $userId): array
    {
        $user = User::with(['enrollments', 'stat'])->find($userId);

        if (!$user) {
            return ['screen' => 'DASHBOARD', 'data' => [
                'student_name'  => 'Student',
                'courses_count' => '0',
                'xp_points'     => '0 XP',
                'next_session'  => 'No upcoming sessions',
                'actions'       => $this->studentActions(),
            ]];
        }

        $coursesCount = $user->enrollments->count();
        $xp           = $user->stat->xp ?? 0;

        // Next live session
        $nextSession  = LiveSession::where('starts_at', '>', now())
            ->whereHas('course.enrollments', fn ($q) => $q->where('user_id', $userId))
            ->orderBy('starts_at')
            ->first();

        $nextText = $nextSession
            ? $nextSession->title . ' — ' . $nextSession->starts_at->format('D g:ia')
            : 'No upcoming sessions';

        return [
            'screen' => 'DASHBOARD',
            'data'   => [
                'student_name'  => $user->name,
                'courses_count' => (string) $coursesCount,
                'xp_points'     => number_format($xp) . ' XP',
                'next_session'  => $nextText,
                'actions'       => $this->studentActions(),
            ],
        ];
    }

    private function loadStudentCourses(int $userId): array
    {
        $user = User::with('enrollments')->find($userId);

        $courses = ($user?->enrollments ?? collect())->map(function ($course) use ($userId) {
            $progress = $course->lessonProgress()
                ->where('user_id', $userId)
                ->where('is_completed', true)
                ->count();
            $total = $course->lessons()->count();
            $pct   = $total > 0 ? round($progress / $total * 100) : 0;
            return ['id' => (string) $course->id, 'title' => $course->title . " — {$pct}%"];
        })->values()->all();

        if (empty($courses)) {
            $courses = [['id' => '0', 'title' => 'No courses yet — visit edu.kmgvitallinks.co.uk']];
        }

        return ['screen' => 'MY_COURSES', 'data' => ['courses' => $courses]];
    }

    private function loadStudentCourseDetail(int $userId, ?string $courseId): array
    {
        $course = \App\Models\Course::find($courseId);

        if (!$course) {
            return ['screen' => 'DASHBOARD', 'data' => $this->initStudentDashboard($userId)['data']];
        }

        $totalLessons    = $course->lessons()->count();
        $completedLessons = $course->lessonProgress()
            ->where('user_id', $userId)
            ->where('is_completed', true)
            ->count();

        $pendingAssignment = $course->assignments()
            ->where('due_date', '>', now())
            ->first();

        $availableQuiz = $course->quizzes()->first();

        $summary  = "✅ {$completedLessons} of {$totalLessons} lessons complete";
        if ($pendingAssignment) {
            $summary .= "\n📝 Assignment due " . $pendingAssignment->due_date->format('D jS M');
        }
        if ($availableQuiz) {
            $summary .= "\n❓ Quiz: {$availableQuiz->title}";
        }
        $pct      = $totalLessons > 0 ? round($completedLessons / $totalLessons * 100) : 0;
        $summary .= "\n📊 Progress: {$pct}%";

        return [
            'screen' => 'COURSE_DETAIL',
            'data'   => [
                'course_title'   => $course->title,
                'course_summary' => $summary,
                'course_url'     => 'https://edu.kmgvitallinks.co.uk/student/courses/' . $course->id,
            ],
        ];
    }

    private function loadAskChiedza(): array
    {
        return ['screen' => 'ASK_CHIEDZA', 'data' => ['prompt_hint' => 'E.g. Explain Newton\'s third law']];
    }

    private function handleChiedzaQuestion(int $userId, string $question): array
    {
        if (empty(trim($question))) {
            return ['screen' => 'ASK_CHIEDZA', 'data' => ['prompt_hint' => 'Please type your question.']];
        }

        // Dispatch AI query synchronously (short timeout) or asynchronously
        try {
            $user    = User::find($userId);
            $service = app(WhatsAppService::class);

            // Use the existing AI service via a quick inline call
            $answer = $this->askChiedzaAi($user, $question);
        } catch (\Throwable $e) {
            Log::error('WhatsApp flow: Chiedza AI error', ['error' => $e->getMessage()]);
            $answer = 'Sorry, I could not answer that right now. Try again or visit edu.kmgvitallinks.co.uk for help.';
        }

        return [
            'screen' => 'CHIEDZA_ANSWER',
            'data'   => ['answer' => $answer],
        ];
    }

    // ─── Teacher screen data ──────────────────────────────────────────────────

    private function initTeacherDashboard(int $userId): array
    {
        $user = User::with('taughtCourses')->find($userId);

        if (!$user) {
            return ['screen' => 'DASHBOARD', 'data' => [
                'teacher_name'   => 'Teacher',
                'courses_count'  => '0',
                'sessions_today' => 'No sessions today',
                'pending_grades' => '0 pending',
                'actions'        => $this->teacherActions(),
            ]];
        }

        $coursesCount = $user->taughtCourses->count();

        $todaySessions = LiveSession::whereHas(
            'course.teachers', fn ($q) => $q->where('users.id', $userId)
        )->whereDate('starts_at', today())->count();

        $pendingGrades = AssignmentSubmission::whereHas(
            'assignment.course.teachers', fn ($q) => $q->where('users.id', $userId)
        )->whereNull('grade')->count();

        return [
            'screen' => 'DASHBOARD',
            'data'   => [
                'teacher_name'   => $user->name,
                'courses_count'  => (string) $coursesCount,
                'sessions_today' => $todaySessions === 0
                    ? 'No sessions today'
                    : "{$todaySessions} session" . ($todaySessions > 1 ? 's' : '') . ' today',
                'pending_grades' => "{$pendingGrades} pending",
                'actions'        => $this->teacherActions(),
            ],
        ];
    }

    private function loadTeacherCourses(int $userId): array
    {
        $user = User::with('taughtCourses')->find($userId);

        $courses = ($user?->taughtCourses ?? collect())->map(function ($course) {
            $students = $course->enrollments()->count();
            return ['id' => (string) $course->id, 'title' => "{$course->title} ({$students} students)"];
        })->values()->all();

        if (empty($courses)) {
            $courses = [['id' => '0', 'title' => 'No courses yet — visit the portal']];
        }

        return ['screen' => 'MY_COURSES', 'data' => ['courses' => $courses]];
    }

    private function loadTeacherCourseLink(int $userId, ?string $courseId): array
    {
        // Return to dashboard with the course URL in a note
        $url = 'https://edu.kmgvitallinks.co.uk/teacher/courses/' . ($courseId ?? '');
        return $this->initTeacherDashboard($userId);
    }

    private function loadAnnouncementForm(int $userId): array
    {
        $user = User::with('taughtCourses')->find($userId);

        $courses = ($user?->taughtCourses ?? collect())->map(
            fn ($c) => ['id' => (string) $c->id, 'title' => $c->title]
        )->values()->all();

        if (empty($courses)) {
            $courses = [['id' => '0', 'title' => 'No courses']];
        }

        return ['screen' => 'POST_ANNOUNCEMENT', 'data' => ['courses' => $courses]];
    }

    private function submitAnnouncement(int $userId, array $data): array
    {
        $courseId = $data['course_id'] ?? null;
        $message  = trim($data['message'] ?? '');

        if (!$courseId || empty($message)) {
            return ['screen' => 'POST_ANNOUNCEMENT', 'data' => [
                'courses' => $this->loadAnnouncementForm($userId)['data']['courses'] ?? [],
            ]];
        }

        $course = \App\Models\Course::find($courseId);

        // Verify teacher owns this course
        $isTeacher = User::find($userId)?->taughtCourses()
            ->where('courses.id', $courseId)->exists();

        if (!$isTeacher || !$course) {
            return ['screen' => 'SUCCESS', 'data' => ['message' => 'Course not found or access denied.']];
        }

        Announcement::create([
            'course_id' => $courseId,
            'author_id' => $userId,
            'title'     => 'WhatsApp Announcement',
            'body'      => $message,
            'audience'  => 'students',
            'published_at' => now(),
        ]);

        Log::info('WhatsApp flow: announcement posted', ['course_id' => $courseId, 'teacher_id' => $userId]);

        return [
            'screen' => 'SUCCESS',
            'data'   => ['message' => "Announcement posted to all students in {$course->title}."],
        ];
    }

    private function loadGradesQueue(int $userId): array
    {
        $pending = AssignmentSubmission::with(['assignment.course', 'student'])
            ->whereHas('assignment.course.teachers', fn ($q) => $q->where('users.id', $userId))
            ->whereNull('grade')
            ->latest()
            ->take(10)
            ->get();

        $summary = $pending->count() . ' assignment' . ($pending->count() !== 1 ? 's' : '') . ' await grading.';

        $list = $pending->map(
            fn ($s) => '📝 ' . $s->student->name . ' — ' . $s->assignment->title
        )->implode("\n");

        if (empty($list)) {
            $list = '✅ All assignments graded!';
        }

        return [
            'screen' => 'GRADES_QUEUE',
            'data'   => [
                'summary'      => $summary,
                'pending_list' => $list,
                'portal_url'   => 'https://edu.kmgvitallinks.co.uk/teacher/courses',
            ],
        ];
    }

    // ─── Shared ───────────────────────────────────────────────────────────────

    private function reloadDashboard(string $role, int $userId): array
    {
        if ($role === 'teacher') {
            return $this->initTeacherDashboard($userId);
        }
        return $this->initStudentDashboard($userId);
    }

    private function fallback(string $action): array
    {
        Log::warning('WhatsApp flow: unhandled action', ['action' => $action]);
        return ['data' => ['error' => 'unhandled_action']];
    }

    private function parseFlowToken(string $token): array
    {
        if (str_contains($token, ':')) {
            [$role, $userId] = explode(':', $token, 2);
            return [$role, is_numeric($userId) ? (int) $userId : null];
        }
        return ['unknown', null];
    }

    private function studentActions(): array
    {
        return [
            ['id' => 'courses',  'title' => '📚 My Courses'],
            ['id' => 'chiedza',  'title' => '🤖 Ask Chiedza AI'],
        ];
    }

    private function teacherActions(): array
    {
        return [
            ['id' => 'courses_teacher', 'title' => '🎓 My Courses'],
            ['id' => 'announce',        'title' => '📣 Post Announcement'],
            ['id' => 'grades',          'title' => '📝 Pending Grades'],
        ];
    }

    /**
     * Call the Claude/Chiedza AI service for a student question.
     * Reuses the same provider used by ProcessWhatsAppMessage.
     */
    private function askChiedzaAi(?User $user, string $question): string
    {
        $systemPrompt = "You are Chiedza, an AI study tutor for Zimbabwean students on the EduBridge platform. "
            . "You follow the ZIMSEC curriculum. Give clear, concise answers suitable for WhatsApp. "
            . "Limit responses to 300 words. Use numbered lists and bullet points where helpful.";

        $client = \Anthropic::client(config('services.anthropic.api_key'));

        $message = $client->messages()->create([
            'model'      => 'claude-3-5-haiku-20241022',
            'max_tokens' => 600,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $question],
            ],
        ]);

        return $message->content[0]->text ?? 'I could not answer that right now. Please try again.';
    }
}
