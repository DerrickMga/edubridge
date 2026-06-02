<?php
namespace App\Jobs;

use App\Models\{Announcement, Assignment, AssignmentSubmission, Certificate, Conversation, Course, LiveSession, LessonProgress, User, WhatsAppFlow, WhatsappWebhook};
use App\Notifications\WelcomeNotification;
use App\Services\{ClaudeService, WhatsAppFlowService, WhatsAppService};
use Illuminate\Support\Facades\Password;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WhatsappWebhook $webhook) {}

    public function handle(ClaudeService $claude, WhatsAppService $whatsapp, WhatsAppFlowService $flowService): void
    {
        $phone = $this->webhook->from_phone;
        $type  = $this->webhook->type;

        // ── Interactive message (button/list/flow reply) ───────────────────────
        if ($type === 'interactive') {
            $this->handleInteractive($this->webhook->content, $phone, $whatsapp, $flowService);
            $this->webhook->update(['processed' => true]);
            return;
        }

        // ── Flow completion (nfm_reply) ────────────────────────────────────────
        if ($type === 'nfm_reply') {
            $this->handleFlowCompletion(json_decode($this->webhook->content ?? '{}', true), $phone, $whatsapp);
            $this->webhook->update(['processed' => true]);
            return;
        }

        // ── Text message ──────────────────────────────────────────────────────
        if ($type !== 'text' || !$this->webhook->content) {
            $this->webhook->update(['processed' => true]);
            return;
        }

        $text = trim(strtolower($this->webhook->content));

        // Keyword routing — check before falling through to AI
        if ($this->handleKeyword($text, $phone, $whatsapp, $flowService)) {
            $this->webhook->update(['processed' => true]);
            return;
        }

        // ── AI fallback (Chiedza) ─────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name'     => $phone,
                'email'    => $phone . '@wa.edubridge.co.zw',
                'password' => bcrypt(str()->random(32)),
                'role'     => 'student',
            ]
        );

        $conversation = $user->conversations()
            ->where('channel', 'whatsapp')
            ->latest()
            ->firstOrCreate(['channel' => 'whatsapp', 'user_id' => $user->id]);

        $conversation->messages()->create(['role' => 'user', 'content' => $this->webhook->content]);

        $history = $conversation->messages()->get()->map(fn ($m) => [
            'role' => $m->role, 'content' => $m->content,
        ])->toArray();

        try {
            $reply = $claude->chat($history);
        } catch (\Throwable $e) {
            Log::error('Chiedza AI (fallback) failed', ['error' => $e->getMessage()]);
            $reply = "I'm Chiedza, your AI study companion. I'm having a little trouble right now — please try again in a moment, or reply *STUDENT* to open your hub.";
        }

        $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);

        $whatsapp->sendText($phone, $reply);

        $this->webhook->update(['processed' => true]);
    }

    // ─── Keyword routing ──────────────────────────────────────────────────────

    /**
     * Route common keywords to flows or menus.
     * Returns true if handled (stop further processing).
     */
    private function handleKeyword(string $text, string $phone, WhatsAppService $whatsapp, WhatsAppFlowService $flowService): bool
    {
        $greetings = ['hi', 'hello', 'hey', 'menu', 'start', 'help', 'hie', 'mhoro'];
        $register  = ['register', 'join', 'signup', 'sign up', 'create account', 'enroll'];
        $student   = ['student', 'student hub', 'my courses', 'courses', 'learn', 'study'];
        $teacher   = ['teacher', 'teacher hub', 'tutor', 'teach', 'my class'];
        $grades    = ['grades', 'pending grades', 'submissions'];
        $askPrefix = 'ask ';

        if (in_array($text, $greetings, true)) {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                if ($user->role === 'teacher') {
                    $this->sendTeacherFlow($phone, $user->id, $flowService);
                } else {
                    $this->sendStudentFlow($phone, $user->id, $flowService);
                }
            } else {
                $flowService->sendMainMenu($phone);
            }
            return true;
        }

        // "ASK <question>" — forward to Chiedza AI and reply via text
        if (str_starts_with($text, $askPrefix)) {
            $question = trim(substr($this->webhook->content, strlen($askPrefix)));
            if (!empty($question)) {
                $user = User::firstOrCreate(
                    ['phone' => $phone],
                    ['name' => $phone, 'email' => $phone . '@wa.edubridge.co.zw', 'password' => bcrypt(str()->random(32)), 'role' => 'student']
                );
                $conversation = $user->conversations()->firstOrCreate(['channel' => 'whatsapp', 'user_id' => $user->id]);
                $conversation->messages()->create(['role' => 'user', 'content' => $question]);
                $history = $conversation->messages()->get()->map(fn($m) => ['role' => $m->role, 'content' => $m->content])->toArray();
                try {
                    $reply = app(\App\Services\ClaudeService::class)->chat($history);
                } catch (\Throwable $e) {
                    Log::error('Chiedza AI (ASK) failed', ['error' => $e->getMessage()]);
                    $reply = "I'm having a little trouble right now. Please try again in a moment, or visit " . config('app.url') . " for help.";
                }
                $conversation->messages()->create(['role' => 'assistant', 'content' => $reply]);
                app(WhatsAppService::class)->sendText($phone, "*Chiedza AI*\n\n" . $reply);
                return true;
            }
        }

        foreach ($register as $kw) {
            if (str_contains($text, $kw)) {
                $this->sendRegisterFlow($phone, $flowService);
                return true;
            }
        }

        foreach ($student as $kw) {
            if (str_contains($text, $kw)) {
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->sendStudentFlow($phone, $user->id, $flowService);
                } else {
                    $flowService->sendMainMenu($phone);
                }
                return true;
            }
        }

        foreach ($teacher as $kw) {
            if (str_contains($text, $kw)) {
                $user = User::where('phone', $phone)->first();
                if ($user) {
                    $this->sendTeacherFlow($phone, $user->id, $flowService);
                } else {
                    $flowService->sendMainMenu($phone);
                }
                return true;
            }
        }

        foreach ($grades as $kw) {
            if (str_contains($text, $kw)) {
                $user = User::where('phone', $phone)->first();
                if ($user && $user->role === 'teacher') {
                    $pending = AssignmentSubmission::whereIn(
                        'assignment_id',
                        Assignment::whereIn('course_id', $user->taughtCourses()->pluck('courses.id'))->pluck('id')
                    )->where('status', 'submitted')->count();
                    app(WhatsAppService::class)->sendText(
                        $phone,
                        "*Pending Grades*\n\n{$pending} submission" . ($pending !== 1 ? 's' : '') . " awaiting grading.\n\nGrade at: " . config('app.url') . "/teacher/courses"
                    );
                } else {
                    app(WhatsAppService::class)->sendText($phone, "Reply *TEACHER* to open your teacher hub.");
                }
                return true;
            }
        }

        return false;
    }

    // ─── Interactive message handler ──────────────────────────────────────────

    private function handleInteractive(string $content, string $phone, WhatsAppService $whatsapp, WhatsAppFlowService $flowService): void
    {
        $data = json_decode($content, true);
        $type = $data['type'] ?? '';

        // List / button reply
        $replyId = match ($type) {
            'list_reply'   => $data['list_reply']['id']   ?? null,
            'button_reply' => $data['button_reply']['id'] ?? null,
            default        => null,
        };

        if (!$replyId) {
            Log::info('WhatsApp interactive: no reply id', ['type' => $type]);
            return;
        }

        $user = User::where('phone', $phone)->first();

        match (true) {
            str_starts_with($replyId, 'intent_register') => $this->sendRegisterFlow($phone, $flowService),
            str_starts_with($replyId, 'intent_student')  => $user
                ? $this->sendStudentFlow($phone, $user->id, $flowService)
                : $flowService->sendMainMenu($phone),
            str_starts_with($replyId, 'intent_teacher')  => $user
                ? $this->sendTeacherFlow($phone, $user->id, $flowService)
                : $flowService->sendMainMenu($phone),
            str_starts_with($replyId, 'intent_otp')      => $whatsapp->sendText($phone, 'Visit ' . config('app.url') . '/phone-verify to verify your number.'),
            default => $whatsapp->sendText($phone, 'Tap *MENU* to see options, or type your question for Chiedza AI 🤖'),
        };
    }

    // ─── Flow completion (nfm_reply) ─────────────────────────────────────────

    /**
     * Dispatch to the right handler based on what's in the form payload.
     * - Registration: has full_name + email
     * - Teacher announcement: has course_id + message
     * - Student course selection: has course_id only
     */
    private function handleFlowCompletion(array $data, string $phone, WhatsAppService $whatsapp): void
    {
        $responseJson = $data['response_json'] ?? '{}';
        $form         = json_decode($responseJson, true) ?? [];

        // Registration flow
        if (!empty($form['full_name']) && !empty($form['email'])) {
            $this->handleRegistrationCompletion($form, $phone, $whatsapp);
            return;
        }

        // Teacher announcement (chiedza_announce flow)
        if (!empty($form['course_id']) && !empty($form['message'])) {
            $this->handleAnnouncementCompletion($form, $phone, $whatsapp);
            return;
        }

        // Hub menu selection (student or teacher hub — returns menu_action)
        if (!empty($form['menu_action'])) {
            $user = User::where('phone', $phone)->first();
            $this->handleHubMenuSelection($form['menu_action'], $phone, $user, $whatsapp);
            return;
        }

        // Legacy: old student course selection
        if (!empty($form['course_id'])) {
            $courseId = $form['course_id'];
            $url      = config('app.url') . '/student/courses/' . $courseId;
            $whatsapp->sendText($phone, "Here's your course link:\n{$url}\n\nReply *ASK* followed by any question to chat with Chiedza AI.");
            return;
        }

        Log::info('WhatsApp flow: unrecognised nfm_reply', ['form_keys' => array_keys($form)]);
    }

    private function handleRegistrationCompletion(array $form, string $phone, WhatsAppService $whatsapp): void
    {
        $fullName   = trim($form['full_name'] ?? '');
        $email      = trim($form['email']     ?? '');
        $role       = $form['role']            ?? 'student';
        $gradeLevel = $form['grade_level']     ?? null;

        if (empty($fullName) || empty($email)) {
            $whatsapp->sendText($phone, "We couldn't complete your registration — some details were missing. Please try again at " . config('app.url'));
            return;
        }

        $isNew = !User::where('phone', $phone)->exists();

        $user = User::updateOrCreate(
            ['phone' => $phone],
            [
                'name'              => $fullName,
                'email'             => $email,
                'role'              => in_array($role, ['student', 'teacher']) ? $role : 'student',
                'password'          => bcrypt(str()->random(32)),
                'phone_verified_at' => now(),
            ]
        );

        Log::info('WhatsApp flow: registration completed', ['user_id' => $user->id, 'role' => $role, 'is_new' => $isNew]);

        // ── Only send welcome messages on first registration ──────────────────
        if ($isNew) {
            // Send welcome email + in-app notification
            try {
                $user->notify(new WelcomeNotification($user));
            } catch (\Throwable $e) {
                Log::error('WhatsApp registration: welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }

            // Generate a one-time set-password link
            $token       = Password::broker()->createToken($user);
            $setPassUrl  = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));
            $hubKeyword  = $user->role === 'teacher' ? '*TEACHER*' : '*STUDENT*';
            $hubLabel    = $user->role === 'teacher' ? 'Teaching Hub' : 'Student Hub';
            $appUrl      = config('app.url');

            $whatsapp->sendText(
                $phone,
                "✅ *Welcome to EduBridge, {$fullName}!*\n\n"
                . "Your account is ready 🎉\n\n"
                . "📧 We've sent a welcome email to *{$email}* with everything you need to know.\n\n"
                . "🔐 *Set your password:*\n{$setPassUrl}\n\n"
                . "_(Link expires in 60 minutes — you can also use Forgot Password at {$appUrl}/forgot-password)_\n\n"
                . "👇 Reply {$hubKeyword} right now to open your {$hubLabel} here on WhatsApp."
            );
        }

        // ── Push them into their hub flow ─────────────────────────────────────
        $flowService = app(WhatsAppFlowService::class);
        if ($user->role === 'teacher') {
            $this->sendTeacherFlow($phone, $user->id, $flowService);
        } else {
            $this->sendStudentFlow($phone, $user->id, $flowService);
        }
    }

    private function handleAnnouncementCompletion(array $form, string $phone, WhatsAppService $whatsapp): void
    {
        $courseId = (int) ($form['course_id'] ?? 0);
        $message  = trim($form['message'] ?? '');

        if (!$courseId || empty($message)) {
            $whatsapp->sendText($phone, "Could not post announcement — course or message was missing.");
            return;
        }

        $teacher = User::where('phone', $phone)->first();
        $course  = Course::find($courseId);

        if (!$course || !$teacher) {
            $whatsapp->sendText($phone, "Could not post announcement — course not found.");
            return;
        }

        $announcement = Announcement::create([
            'course_id' => $courseId,
            'user_id'   => $teacher->id,
            'title'     => 'Announcement from ' . $teacher->name,
            'content'   => $message,
        ]);

        $studentCount = $course->enrollments()->count();

        Log::info('WhatsApp flow: announcement posted', [
            'announcement_id' => $announcement->id,
            'course_id'       => $courseId,
            'teacher_id'      => $teacher->id,
        ]);

        $whatsapp->sendText(
            $phone,
            "Announcement posted to *{$course->title}*!\n\n"
            . "{$studentCount} student" . ($studentCount !== 1 ? 's' : '') . " will be notified.\n\n"
            . "Reply *GRADES* to see pending submissions."
        );
    }

    // ─── Flow launchers ───────────────────────────────────────────────────────

    private function sendRegisterFlow(string $phone, WhatsAppFlowService $flowService): void
    {
        $flow = WhatsAppFlow::findByKey('chiedza_register');

        if ($flow?->isPublished()) {
            $flowService->sendFlowMessage(
                $phone,
                $flow->meta_flow_id,
                'Create Account ✍️',
                "Welcome to *EduBridge* 🎓\n\nCreate your free account in 30 seconds.",
                'WELCOME',
                'menu:new_user'
            );
        } else {
            app(WhatsAppService::class)->sendText(
                $phone,
                "Register at: " . config('app.url') . "/register\n\nOr reply with your name and we'll get you started!"
            );
        }
    }

    private function sendStudentFlow(string $phone, int $userId, WhatsAppFlowService $flowService): void
    {
        $flow = WhatsAppFlow::findByKey('chiedza_student');

        if (!$flow?->isPublished()) {
            app(WhatsAppService::class)->sendText($phone, "Access your courses at: " . config('app.url') . "/student/courses");
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            app(WhatsAppService::class)->sendText($phone, "Access your courses at: " . config('app.url') . "/student/courses");
            return;
        }

        $enrollmentCount = $user->enrollments()->count();
        $xp              = $user->xpEvents()->sum('xp') ?? 0;

        $nextSession = LiveSession::whereHas('course.enrollments', fn($q) => $q->where('user_id', $userId))
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->first();
        $nextSessionText = $nextSession
            ? $nextSession->title . ' — ' . $nextSession->scheduled_at->format('D j M, g:ia')
            : 'No upcoming sessions';

        $initData = [
            'student_name'  => $user->name,
            'xp_points'     => number_format($xp) . ' XP',
            'courses_count' => $enrollmentCount . ' course' . ($enrollmentCount !== 1 ? 's' : '') . ' enrolled',
            'next_session'  => $nextSessionText,
        ];

        $flowService->sendFlowMessage(
            $phone,
            $flow->meta_flow_id,
            'Open Student Hub',
            "*EduBridge Student Hub*\n\n{$user->name} — {$enrollmentCount} course" . ($enrollmentCount !== 1 ? 's' : '') . "  |  " . number_format($xp) . " XP",
            'DASHBOARD',
            "student:{$userId}",
            $initData
        );
    }

    private function sendTeacherFlow(string $phone, int $userId, WhatsAppFlowService $flowService): void
    {
        $flow = WhatsAppFlow::findByKey('chiedza_teacher');

        if (!$flow?->isPublished()) {
            app(WhatsAppService::class)->sendText($phone, "Access your teacher dashboard at: " . config('app.url') . "/teacher/courses");
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            app(WhatsAppService::class)->sendText($phone, "Access your teacher dashboard at: " . config('app.url') . "/teacher/courses");
            return;
        }

        $courseIds = $user->taughtCourses()->pluck('courses.id');
        $courseCount = $courseIds->count();

        $sessionsToday = LiveSession::whereIn('course_id', $courseIds)
            ->whereDate('scheduled_at', today())
            ->count();

        $pendingGrades = AssignmentSubmission::whereIn(
            'assignment_id',
            Assignment::whereIn('course_id', $courseIds)->pluck('id')
        )->where('status', 'submitted')->count();

        $initData = [
            'teacher_name'   => $user->name,
            'courses_count'  => $courseCount . ' course' . ($courseCount !== 1 ? 's' : ''),
            'sessions_today' => $sessionsToday . ' session' . ($sessionsToday !== 1 ? 's' : '') . ' today',
            'pending_grades' => $pendingGrades . ' pending grade' . ($pendingGrades !== 1 ? 's' : ''),
        ];

        $flowService->sendFlowMessage(
            $phone,
            $flow->meta_flow_id,
            'Open Teacher Hub',
            "*EduBridge Teacher Hub*\n\n{$user->name} — {$courseCount} course" . ($courseCount !== 1 ? 's' : '') . "  |  {$pendingGrades} pending grades",
            'DASHBOARD',
            "teacher:{$userId}",
            $initData
        );
    }

    private function handleHubMenuSelection(string $action, string $phone, ?User $user, WhatsAppService $whatsapp): void
    {
        if (!$user) {
            $whatsapp->sendText($phone, "Please register first. Reply *JOIN* to create your account.");
            return;
        }

        $appUrl = config('app.url');

        switch ($action) {
            case 'browse':
                $whatsapp->sendText($phone,
                    "🎓 *Browse Courses*\n\nExplore all available courses:\n{$appUrl}/courses\n\n"
                    . "Once enrolled, your courses will appear in your Student Hub.\nReply *STUDENT* to open your hub."
                );
                break;

            case 'learn':
                $courses = $user->enrollments()->get();
                if ($courses->isEmpty()) {
                    $whatsapp->sendText($phone,
                        "📚 *My Learning*\n\nYou haven't enrolled in any courses yet.\n\nBrowse and enroll at:\n{$appUrl}/courses"
                    );
                } else {
                    $lines = $courses->map(fn($c) => "• {$c->title}\n  {$appUrl}/student/courses/{$c->id}")->join("\n\n");
                    $whatsapp->sendText($phone, "📚 *Your Courses*\n\n{$lines}\n\nReply *ASK [question]* to chat with Chiedza AI.");
                }
                break;

            case 'ai':
                $whatsapp->sendText($phone,
                    "🤖 *Chiedza AI*\n\nAsk me anything about your studies!\n\nType:\n*ASK* followed by your question\n\n_Example: ASK What is photosynthesis?_"
                );
                break;

            case 'progress':
                $xp        = $user->xpEvents()->sum('xp') ?? 0;
                $completed = LessonProgress::where('user_id', $user->id)->where('completed', true)->count();
                $total     = LessonProgress::where('user_id', $user->id)->count();
                $certCount = Certificate::where('student_id', $user->id)->count();
                $whatsapp->sendText($phone,
                    "📊 *Your Progress*\n\n"
                    . "⭐ XP Earned: *" . number_format($xp) . " XP*\n"
                    . "📖 Lessons Completed: *{$completed}* / {$total}\n"
                    . "🏆 Certificates: *{$certCount}*\n\n"
                    . "View full analytics:\n{$appUrl}/student/progress"
                );
                break;

            case 'sessions':
                if ($user->role === 'teacher') {
                    $upcoming = LiveSession::whereIn('course_id', $user->taughtCourses()->pluck('courses.id'))
                        ->where('scheduled_at', '>', now())->orderBy('scheduled_at')->take(3)->get();
                    $lines = $upcoming->isEmpty()
                        ? 'No upcoming sessions.'
                        : $upcoming->map(fn($s) => "• *{$s->title}*\n  " . $s->scheduled_at->format('D j M, g:ia'))->join("\n\n");
                    $whatsapp->sendText($phone, "📅 *Live Sessions*\n\n{$lines}\n\nManage at:\n{$appUrl}/teacher/sessions");
                } else {
                    $sessions = LiveSession::whereHas('course.enrollments', fn($q) => $q->where('user_id', $user->id))
                        ->where('scheduled_at', '>', now())->orderBy('scheduled_at')->take(5)->get();
                    if ($sessions->isEmpty()) {
                        $whatsapp->sendText($phone, "📅 *Upcoming Sessions*\n\nNo upcoming live sessions yet.\n\nCheck back soon, or visit:\n{$appUrl}/student/sessions");
                    } else {
                        $lines = $sessions->map(fn($s) => "• *{$s->title}*\n  " . $s->scheduled_at->format('D j M, g:ia'))->join("\n\n");
                        $whatsapp->sendText($phone, "📅 *Upcoming Live Sessions*\n\n{$lines}\n\nJoin at: {$appUrl}/student/sessions");
                    }
                }
                break;

            case 'assignments':
                $pending = Assignment::whereIn('course_id', $user->enrollments()->pluck('courses.id'))
                    ->where('due_date', '>=', now())
                    ->whereDoesntHave('submissions', fn($q) => $q->where('user_id', $user->id))
                    ->orderBy('due_date')->take(5)->get();
                if ($pending->isEmpty()) {
                    $whatsapp->sendText($phone, "📝 *Assignments*\n\nNo pending assignments. Great work!\n\nView all at:\n{$appUrl}/student/assignments");
                } else {
                    $lines = $pending->map(fn($a) => "• *{$a->title}*\n  Due: " . $a->due_date->format('D j M'))->join("\n\n");
                    $whatsapp->sendText($phone, "📝 *Pending Assignments*\n\n{$lines}\n\nSubmit at:\n{$appUrl}/student/assignments");
                }
                break;

            case 'certificates':
                $certs = Certificate::where('student_id', $user->id)->with('course')->get();
                if ($certs->isEmpty()) {
                    $whatsapp->sendText($phone,
                        "🏆 *Certificates*\n\nYou haven't earned any certificates yet.\n\nComplete a course to earn your first certificate!\n{$appUrl}/student/courses"
                    );
                } else {
                    $lines = $certs->map(fn($c) => "• {$c->course->title}")->join("\n");
                    $whatsapp->sendText($phone, "🏆 *Your Certificates*\n\n{$lines}\n\nDownload at:\n{$appUrl}/student/certificates");
                }
                break;

            case 'announce':
                $flowService = app(WhatsAppFlowService::class);
                $this->sendTeacherAnnouncementFlow($phone, $user->id, $flowService);
                break;

            case 'courses':
                $courses = $user->taughtCourses()->get();
                if ($courses->isEmpty()) {
                    $whatsapp->sendText($phone,
                        "📚 *My Courses*\n\nYou haven't created any courses yet.\n\nGet started at:\n{$appUrl}/teacher/courses"
                    );
                } else {
                    $lines = $courses->map(fn($c) => "• *{$c->title}*\n  {$appUrl}/teacher/courses/{$c->id}")->join("\n\n");
                    $whatsapp->sendText($phone, "📚 *My Courses*\n\n{$lines}");
                }
                break;

            case 'grades':
                $pending = AssignmentSubmission::whereIn(
                    'assignment_id',
                    Assignment::whereIn('course_id', $user->taughtCourses()->pluck('courses.id'))->pluck('id')
                )->where('status', 'submitted')->count();
                $whatsapp->sendText($phone,
                    "✅ *Grade Submissions*\n\n*{$pending}* submission" . ($pending !== 1 ? 's' : '') . " awaiting grading.\n\nGrade at:\n{$appUrl}/teacher/submissions"
                );
                break;

            case 'material':
                $whatsapp->sendText($phone,
                    "📎 *Add Learning Material*\n\nUpload lessons, resources and quizzes:\n{$appUrl}/teacher/courses"
                );
                break;

            case 'analytics':
                $whatsapp->sendText($phone,
                    "📊 *Student Analytics*\n\nView detailed student progress and engagement:\n{$appUrl}/teacher/analytics"
                );
                break;

            default:
                $whatsapp->sendText($phone, "Reply *MENU* to see your options.");
        }
    }

    private function sendTeacherAnnouncementFlow(string $phone, int $userId, WhatsAppFlowService $flowService): void
    {
        $flow = WhatsAppFlow::findByKey('chiedza_announce');

        if (!$flow?->isPublished()) {
            app(WhatsAppService::class)->sendText(
                $phone,
                "To post an announcement, visit: " . config('app.url') . "/teacher/courses"
            );
            return;
        }

        $user = User::find($userId);
        if (!$user) return;

        $courses = $user->taughtCourses()->get()->map(fn($c) => [
            'id'    => (string) $c->id,
            'title' => $c->title,
        ])->values()->toArray();

        if (empty($courses)) {
            app(WhatsAppService::class)->sendText(
                $phone,
                "You don't have any courses yet. Create your first course at: " . config('app.url') . "/teacher/courses"
            );
            return;
        }

        $flowService->sendFlowMessage(
            $phone,
            $flow->meta_flow_id,
            'Post Announcement',
            "📢 *Post Announcement*\n\nSelect a course and write your announcement.",
            'ANNOUNCE_FORM',
            "announce:{$userId}",
            ['courses' => $courses]
        );
    }
}
